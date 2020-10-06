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
 * Product discount group table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Discount_Group extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'products.store' => true,
		'product_items.store' => true
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_discount_group';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $code;

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
	 * This is an array of products_ids from
	 * the product_discount_group_xref table.
	 *
	 * @var  array
	 */
	public $product_ids;

	/**
	 * This is an array of products_item_ids from
	 * the product_discount_group_xref table.
	 *
	 * @var  array
	 *
	 * @since 1.12.69
	 */
	public $product_item_ids;

	/**
	 * @var integer
	 */
	public $company_id;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.product_discount_group'
		),
		'pim' => array(
			'erp.pim.product_discount_group'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'company_id' => array(
			'model' => 'Companies'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'product_ids' => 'Products',
		'product_item_ids' => 'Product_Items'
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
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->product_ids      = null;
		$this->product_item_ids = null;

		parent::reset();
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		// Sanitize product ids
		$this->product_ids      = array_unique($this->product_ids, SORT_STRING);
		$this->product_item_ids = array_unique($this->product_item_ids, SORT_STRING);

		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_TITLE_CANNOT_BE_EMPTY'));

			return false;
		}

		// Make sure there is no other customer discount group with the same code or name
		$group = clone $this;

		if ($group->load(array('code' => $this->code)) && $group->id != $this->id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_PRODUCT_GROUP_CODE_ALREADY_TAKEN', $this->code));

			return false;
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
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (parent::store($updateNulls))
		{
			// Store the products
			if ($this->getOption('products.store'))
			{
				if (!$this->storeProductXref())
				{
					return false;
				}
			}

			// Store the products
			if ($this->getOption('product_items.store'))
			{
				if (!$this->storeProductItemXref())
				{
					return false;
				}
			}

			// Stores the web service reference if the table is not called from a web service - otherwise the ws function will do it
			if (!$this->getOption('store.ws'))
			{
				$this->storeWSReference();
			}

			return true;
		}

		return false;
	}

	/**
	 * Store sync table ws reference
	 *
	 * @return  void
	 */
	private function storeWSReference()
	{
		if (isset($this->wsSyncMapPK) && isset($this->wsSyncMapPK['erp']))
		{
			$wsRef      = $this->wsSyncMapPK['erp'][0];
			$syncHelper = new RedshopbHelperSync;

			$currentCode = $syncHelper->findSyncedLocalId($wsRef, $this->id);

			if (!$currentCode || $currentCode != $this->code)
			{
				$syncHelper->deleteSyncedLocalId($wsRef, $this->id);
				$syncHelper->recordSyncedId($wsRef, $this->code, $this->id);
			}
		}
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
	 */
	public function load($keys = null, $reset = true)
	{
		if (parent::load($keys, $reset) && $this->loadProductXref() && $this->loadProductItemXref())
		{
			return true;
		}

		return false;
	}

	/**
	 * Load the product items related to this Product Discount Group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since 1.12.69
	 */
	private function loadProductItemXref()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('product_item_id')
			->from($db->qn('#__redshopb_product_item_discount_group_xref'))
			->where('discount_group_id = ' . (int) $this->id);
		$db->setQuery($query);
		$productItemId = $db->loadColumn();

		if (!is_array($productItemId))
		{
			$productItemId = array();
		}

		$this->product_item_ids = $productItemId;

		return true;
	}

	/**
	 * Load the products related to this Product Discount Group
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadProductXref()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('product_id')
			->from($db->qn('#__redshopb_product_discount_group_xref'))
			->where('discount_group_id = ' . (int) $this->id);
		$db->setQuery($query);
		$productId = $db->loadColumn();

		if (!is_array($productId))
		{
			$productId = array();
		}

		$this->product_ids = $productId;

		return true;
	}

	/**
	 * Store the product item x references
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since 1.12.69
	 */
	private function storeProductItemXref()
	{
		if (!isset($this->product_item_ids))
		{
			return true;
		}

		// Delete all items
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_product_item_discount_group_xref'))
			->where('discount_group_id = ' . (int) $this->id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		$xrefTable = RedshopbTable::getAdminInstance('Product_Item_Discount_Group_Xref');

		// Store the new items
		foreach ($this->product_item_ids as $productItemId)
		{
			if (!$xrefTable->load(
				array(
					'discount_group_id' => $this->id,
					'product_item_id' => $productItemId
				)
			))
			{
				if (!$xrefTable->save(
					array(
						'discount_group_id' => $this->id,
						'product_item_id' => $productItemId
					)
				))
				{
					$this->setError($xrefTable->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Store the product x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeProductXref()
	{
		return RedshopbHelperDatabase::refreshXrefAssociation(
			'Product_Discount_Group',
			'discount_group_id',
			(int) $this->id,
			'product_id',
			'Product_Discount_Group_Xref',
			$this->product_ids
		);
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (parent::bind($src, $ignore))
		{
			if (isset($src['company_id']) && $src['company_id'] == '')
			{
				$this->company_id = null;
				$this->setOption('storeNulls', true);
			}

			return true;
		}

		return false;
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
			/** @var RedshopbTableProduct_Discount $discountTable */
			$discountTable = RedshopbTable::getAdminInstance('Product_Discount');

			if (!$discountTable->deleteByTypeId($ids, 'product_discount_group'))
			{
				throw new Exception($discountTable->getError());
			}

			if (!parent::delete($pk))
			{
				throw new Exception($this->getError());
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
