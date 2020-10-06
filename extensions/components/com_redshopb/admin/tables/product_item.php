<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Product Item table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Item extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_item';

	/**
	 * @var integer
	 */
	public $id = 0;

	/**
	 * @var  string
	 */
	public $sku;

	/**
	 * @var integer
	 */
	public $product_id;

	/**
	 * @var integer
	 */
	public $state = 1;

	/**
	 * @var integer
	 */
	public $discontinued = 0;

	/**
	 * @var float
	 */
	public $stock_upper_level;

	/**
	 * @var float
	 */
	public $stock_lower_level;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.product_item'
		),
		'fengel' => array(
			'fengel.item_related'
		)
	);

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Stockroom_Product_Item_Xref' => 'product_item_id',
		'Field_Data' => array(
			'_key' => array(
				'item_id',
				'subitem_id'
			),
			'_extrajoins' => array(
				'field' => 'field.id = _table.field_id'
			),
			'_conditions' => array(
				'scope' => 'product_item'
			)
		),
		'Product_Price' => 'product_item_id'
	);

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		if (!parent::afterStore($updateNulls))
		{
			return false;
		}

		if (!empty($this->sku))
		{
			return true;
		}

		// Get sku for this item
		$this->sku = RedshopbHelperProduct_Item::getSKU($this->id, true);

		if (empty($this->sku))
		{
			$this->sku = $this->product_id;
		}

		// Make sure there is no other product with the same SKU
		$product    = clone $this;
		$sku        = $this->sku;
		$skuCounter = 0;

		// We will go through the items until we get unique SKU
		while ($product->load(array('sku' => $this->sku)) && $product->id != $this->id)
		{
			$skuCounter++;
			$this->sku = $sku . '-' . $skuCounter;
			$product   = clone $this;
		}

		if ($skuCounter)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_PRODUCT_SKU_ALREADY_TAKEN', $sku));
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks    An optional array of primary key values to update.
	 *                           If not set the instance property value is used.
	 * @param   integer  $value  The value for the discontinued property
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 */
	public function discontinue($pks = null, $value = 1)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks    = ArrayHelper::toInteger($pks);
		$userId = Factory::getUser()->id;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}

			// Nothing to set publishing state on, return false.
			else
			{
				return false;
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set('discontinued = ' . (int) $value);

		// Determine if there is checkin support for the table.
		if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
		{
			$query->where('(checked_out IS NULL OR checked_out = ' . (int) $userId . ')');
			$checkin = true;
		}
		else
		{
			$checkin = false;
		}

		// Build the WHERE clause for the primary keys.
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);
		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the Table instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->discontinued = (int) $value;
		}

		$this->setError('');

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		// Format stock lower and upper due to decimal position
		$this->stock_lower_level = RedshopbHelperProduct::decimalFormat($this->stock_lower_level, $this->product_id);
		$this->stock_upper_level = RedshopbHelperProduct::decimalFormat($this->stock_upper_level, $this->product_id);

		return parent::store($updateNulls);
	}

	/**
	 * [deleteByProductIds description]
	 * @param   string $productIds [description]
	 * @return  boolean           [description]
	 */
	public function deleteByProductIds($productIds)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('pi.id')
			->from($db->qn('#__redshopb_product_item', 'pi'))
			->where('pi.product_id IN (' . $productIds . ')');

		$productItemIds = $db->setQuery($query)->loadColumn();

		if (empty($productItemIds))
		{
			return true;
		}

		$db->transactionStart(true);

		if (!$this->delete($productItemIds))
		{
			$db->transactionRollback(true);

			return false;
		}

		$db->transactionCommit(true);

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		$db  = $this->_db;
		$ids = $pk;

		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($ids))
		{
			// Sanitize input.
			$ids = ArrayHelper::toInteger($ids);
			$ids = RHelperArray::quote($ids);
			$ids = implode(',', $ids);
		}

		$ids = (is_null($ids)) ? $this->$k : $ids;

		// If no primary key is given, return false.
		if ($ids === null)
		{
			return false;
		}

		$db->transactionStart();

		try
		{
			$query = $db->getQuery(true);

			$query->delete($db->qn('#__redshopb_product_price'))
				->where($db->qn('type') . ' = ' . $db->q('product_item'))
				->where($db->qn('type_id') . ' IN(' . $ids . ')');

			$db->setQuery($query)->execute();

			if (!parent::delete($pk))
			{
				throw new Exception($this->getError());
			}

			// Delete product item field data
			$query = $db->getQuery(true)
				->select('fd.id')
				->from($db->qn('#__redshopb_field_data', 'fd'))
				->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON f.id = fd.field_id')
				->where('fd.item_id IN (' . $ids . ')')
				->where('f.scope = ' . $db->q('product_item'));
			$db->setQuery($query);

			$fieldDataIds = $db->loadColumn();

			if ($fieldDataIds)
			{
				$xrefTable = RedshopbTable::getAdminInstance('Field_Data')
					->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

				if (!$xrefTable->delete($fieldDataIds))
				{
					throw new Exception($xrefTable->getError());
				}
			}
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$this->setError($e->getMessage());
			}

			$db->transactionRollback();

			return false;
		}

		$db->transactionCommit();

		return true;
	}
}
