<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Department table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableReturn_Order extends RedshopbTable
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_return_orders';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $order_id;

	/**
	 * @var  integer
	 */
	public $order_item_id;

	/**
	 * @var  integer
	 */
	public $quantity;

	/**
	 * @var  string
	 */
	public $comment;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		$isNew          = $this->id ? false : true;
		$db             = $this->_db;
		$app            = Factory::getApplication();
		$orderItemTable = RedshopbTable::getAdminInstance('Order_Item');

		// Make sure order has available
		if (!$orderItemTable->load(array('id' => $this->order_item_id, 'order_id' => $this->order_id)))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_RETURN_ORDER_ERROR_ORDER_NOT_FOUND'), 'error');

			return false;
		}

		// Make sure return amount is less than order amount
		if ($orderItemTable->quantity < $this->quantity)
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_RETURN_ORDER_ERROR_RETURN_AMOUNT_LARGER_THAN_ORDER_AMOUNT'), 'error');

			return false;
		}

		if (!$isNew)
		{
			return parent::store($updateNulls);
		}

		// Start update reference
		try
		{
			$db->transactionStart();

			if (!parent::store($updateNulls))
			{
				throw new Exception($this->getError());
			}

			// Reduce amount of order item
			$orderItemTable->quantity = $orderItemTable->quantity - $this->quantity;

			if (!$orderItemTable->store($updateNulls))
			{
				throw new Exception($orderItemTable->getError());
			}

			// Re-calculate stockroom quantity
			if ($orderItemTable->product_item_id)
			{
				// For product item
				$stockroomProductItemRef = RedshopbTable::getAdminInstance('Stockroom_Product_Item_Xref');

				// Just update stockroom amount if stockroom available
				if ($stockroomProductItemRef->load(
					array('stockroom_id' => $orderItemTable->stockroom_id, 'product_item_id' => $orderItemTable->product_item_id)
				))
				{
					$stockroomProductItemRef->amount += (float) $this->quantity;

					if (!$stockroomProductItemRef->store($updateNulls))
					{
						echo 'Error: stockroomProductItemRef::store()' . $stockroomProductItemRef->getError();

						throw new Exception($stockroomProductItemRef->getError());
					}
				}
			}
			elseif ($orderItemTable->product_id)
			{
				// For product
				$stockroomProductRef = RedshopbTable::getAdminInstance('Stockroom_Product_Xref');

				// Just update stockroom amount if stockroom available
				if ($stockroomProductRef->load(array('stockroom_id' => $orderItemTable->stockroom_id, 'product_id' => $orderItemTable->product_id)))
				{
					$stockroomProductRef->amount += (float) $this->quantity;

					if (!$stockroomProductRef->store($updateNulls))
					{
						throw new Exception($stockroomProductRef->getError());
					}
				}
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
