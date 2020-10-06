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
 * Customer discount group table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCustomer_Discount_Group extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'customers.store' => true
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_customer_discount_group';

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
	 * This is an array of customer_ids from
	 * the customer_discount_group_xref table.
	 *
	 * @var  array
	 */
	public $customer_ids;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.customer_discount_group'
		),
		'pim' => array(
			'erp.pim.customer_discount_group'
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
		'customer_ids' => 'Companies'
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
		$this->customer_ids = null;

		parent::reset();
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		// Sanitize customer ids
		$this->customer_ids = array_unique($this->customer_ids, SORT_STRING);

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
			$this->setError(Text::sprintf('COM_REDSHOPB_DISCOUNT_GROUP_CODE_ALREADY_TAKEN', $this->code));

			return false;
		}

		return true;
	}

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
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		$db = $this->getDbo();

		try
		{
			$db->transactionStart();

			if (!parent::store($updateNulls))
			{
				throw new Exception;
			}

			// Store the customers
			if ($this->getOption('customers.store') && !$this->storeCustomerXref())
			{
				throw new Exception;
			}

			// Stores the web service reference if the table is not called from a web service - otherwise the ws function will do it
			if (!$this->getOption('store.ws'))
			{
				$this->storeWSReference();
			}

			$db->transactionCommit();

			return true;
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_REDSHOPB_DEBTOR_ERROR_SAVE_DEBTOR'));

			$db->transactionRollback();

			return false;
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
		if (parent::load($keys, $reset))
		{
			return $this->loadCustomerXref();
		}

		return false;
	}

	/**
	 * Load the customers related to this Customer Discount Group
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadCustomerXref()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('customer_id')
			->from($db->qn('#__redshopb_customer_discount_group_xref'))
			->where('discount_group_id = ' . (int) $this->id);
		$db->setQuery($query);
		$customerId = $db->loadColumn();

		if (!is_array($customerId))
		{
			$customerId = array();
		}

		$this->customer_ids = $customerId;

		return true;
	}

	/**
	 * Store the customer x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeCustomerXref()
	{
		if (!isset($this->customer_ids))
		{
			return true;
		}

		// Delete all items
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_customer_discount_group_xref'))
			->where('discount_group_id = ' . (int) $this->id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		$xrefTable = RedshopbTable::getAdminInstance('Customer_Discount_Group_Xref');

		// Store the new items
		foreach ($this->customer_ids as $customerId)
		{
			$referenceData = array(
				'discount_group_id' => $this->id,
				'customer_id' => $customerId
			);

			// Store new reference for customer group if needed
			if (!$xrefTable->load($referenceData) && !$xrefTable->save($referenceData))
			{
				return false;
			}
		}

		return true;
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
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		if (is_null($pk))
		{
			$pk = $this->id;
		}

		/** @var RedshopbTableProduct_Discount $discountTable */
		$discountTable = RedshopbTable::getAdminInstance('Product_Discount');

		if (!$discountTable->deleteBySalesId($pk, 'debtor_discount_group'))
		{
			$this->setError('Product discount error: ' . $discountTable->getError());

			return false;
		}

		return parent::delete($pk);
	}
}
