<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Stockroom Product Reference table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6.65
 */
class RedshopbTableStockroom_Product_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_stockroom_product_xref';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $stockroom_id;

	/**
	 * @var  float
	 */
	public $amount;

	/**
	 * @var  integer
	 */
	public $unlimited;

	/**
	 * @var  float
	 */
	public $stock_upper_level;

	/**
	 * @var  float
	 */
	public $stock_lower_level;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.stockroom_product_xref'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'stockroom_id' => array(
			'model' => 'Stockrooms'
		),
		'product_id' => array(
			'model' => 'Products'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'unlimited'
	);

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$this->stockroom_id = (int) $this->stockroom_id;
		$this->product_id   = (int) $this->product_id;

		if (!$this->product_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_STOCKROOM_ERROR_MISSING_PRODUCT_ID'));

			return false;
		}

		if (!$this->stockroom_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_STOCKROOM_ERROR_MISSING_STOCKROOM_ID'));

			return false;
		}

		// We are assuming there can be only one record with the same stockroom/product_id combo
		$xrefId = $this->getXrefId($this->stockroom_id, $this->product_id);

		if (empty($this->id) && $xrefId)
		{
			$this->id = $xrefId;
		}

		// If this is set unlimited. No need for check decimal
		if ($this->unlimited)
		{
			$this->amount            = 0;
			$this->stock_upper_level = 0;
			$this->stock_lower_level = 0;

			return true;
		}

		// Format these number follow decimal position config.
		$this->amount            = RedshopbHelperProduct::decimalFormat($this->amount, $this->product_id);
		$this->stock_lower_level = RedshopbHelperProduct::decimalFormat($this->stock_lower_level, $this->product_id);
		$this->stock_upper_level = RedshopbHelperProduct::decimalFormat($this->stock_upper_level, $this->product_id);

		return true;
	}

	/**
	 * Method to return the xref ID from the stockroom_id and product_id
	 *
	 * @param   int  $stockroomId  primary key of the stockroom
	 * @param   int  $productId    primary key of the product
	 *
	 * @return mixed  id or null if one does not exist
	 */
	private function getXrefId($stockroomId, $productId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->qn($this->_tbl))
			->where($db->qn('stockroom_id') . ' = ' . (int) $stockroomId)
			->where($db->qn('product_id') . ' = ' . (int) $productId);
		$result = $db->setQuery($query)->loadResult();

		return $result;
	}
}
