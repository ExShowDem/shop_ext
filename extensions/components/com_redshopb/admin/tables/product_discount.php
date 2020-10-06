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
/**
 * Product discount table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Discount extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_discount';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $type;

	/**
	 * @var integer
	 */
	public $type_id;

	/**
	 * @var  string
	 */
	public $sales_type;

	/**
	 * @var integer
	 */
	public $sales_id;

	/**
	 * @var  string
	 */
	public $starting_date = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $ending_date = '0000-00-00 00:00:00';

	/**
	 * @var float
	 */
	public $percent = 0.00;

	/**
	 * @var integer
	 */
	public $kind = 0;

	/**
	 * @var float
	 */
	public $quantity_min = null;

	/**
	 * @var float
	 */
	public $quantity_max = null;

	/**
	 * @var float
	 */
	public $total = 0.00;

	/**
	 * @var  integer
	 */
	public $state = 1;

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
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var integer
	 */
	public $currency_id = null;

	/**
	 * @var integer
	 */
	protected $product_id = null;

	/**
	 * @var integer
	 */
	protected $product_discount_group_id = null;

	/**
	 * @var integer
	 */
	protected $company_id = null;

	/**
	 * @var integer
	 */
	protected $customer_discount_group_id = null;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.product_discount'
		),
		'pim' => array(
			'erp.pim.product_discount'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'product_id' => array(
			'model' => 'Products',
			'alias' => 'p.id'
		),
		'product_discount_group_id' => array(
			'model' => 'Product_Discount_Groups',
			'alias' => 'pdg.id'
		),
		'company_id' => array(
			'model' => 'Companies',
			'alias' => 'c.id'
		),
		'customer_discount_group_id' => array(
			'model' => 'Discount_Debtor_Groups',
			'alias' => 'cdg.id'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'currency_code' => 'Currencies'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'state'
	);

	/**
	 * Called before check().
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function beforeCheck()
	{
		if (!parent::beforeCheck())
		{
			return false;
		}

		if (!$this->currency_id)
		{
			$this->currency_id = null;
		}

		if (empty($this->quantity_min))
		{
			$this->quantity_min = null;
		}

		if (empty($this->quantity_max))
		{
			$this->quantity_max = null;
		}

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
		if (!parent::store($updateNulls))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset))
		{
			return false;
		}

		switch ($this->type)
		{
			case 'product':
				$this->product_id = $this->type_id;
				break;

			case 'product_discount_group':
				$this->product_discount_group_id = $this->type_id;
				break;
		}

		switch ($this->sales_type)
		{
			case 'debtor_discount_group':
				$this->customer_discount_group_id = $this->sales_id;
				break;

			case 'debtor':
				$this->company_id = $this->sales_id;
				break;
		}

		return true;
	}

	/**
	 * Method to delete all record matching type_id and type
	 *
	 * @param   string  $typeIds  either comma separated string of product ids or product discount group ids
	 * @param   string  $type     either 'product' or 'product_discount_group'
	 *
	 * @return boolean
	 */
	public function deleteByTypeId($typeIds, $type)
	{
		return $this->deleteByTypes('type_id', 'type', $typeIds, $type);
	}

	/**
	 * Method to delete all record matching type_id and type
	 *
	 * @param   string  $salesIds   either comma separated string of company ids, customer discount group id
	 * @param   string  $salesType  either 'debtor_discount_group' or 'debtor'
	 *
	 * @return boolean
	 */
	public function deleteBySalesId($salesIds, $salesType)
	{
		return $this->deleteByTypes('sales_id', 'sales_type', $salesIds, $salesType);
	}

	/**
	 * Method to delete all record either sales_id/sales_code or type_id/type values
	 *
	 * @param   string  $idField    Name of the id field 'type_id' or 'sales_id'
	 * @param   string  $typeField  Name of the field 'type' or 'sales_code'
	 * @param   string  $ids        comma separated string of company ids, customer_discount_group_id, product ids or product discount group ids
	 * @param   string  $type       either 'debtor_discount_group','debtor','product' or 'product_discount_group'
	 *
	 * @return boolean
	 */
	private function deleteByTypes($idField, $typeField, $ids, $type)
	{
		if (empty($ids))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($this->_tbl)
			->where($db->qn($idField) . ' IN (' . $ids . ')')
			->where($db->qn($typeField) . ' = ' . $db->q($type));

		$realKeys = $db->setQuery($query)->loadColumn();

		if (empty($realKeys))
		{
			return true;
		}

		$db->transactionStart(true);

		if (!$this->delete($realKeys))
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
	 * @param   mixed  $realPk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($realPk = null)
	{
		$pk = $realPk;
		$db = Factory::getDbo();

		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}
		// Try the instance property value
		elseif (empty($pk) && $this->{$k})
		{
			$pk = $db->q($this->{$k});
		}

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (!parent::delete($realPk))
		{
			return false;
		}

		return true;
	}
}
