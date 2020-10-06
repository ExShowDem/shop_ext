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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
/**
 * Department table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableDepartment extends RedshopbTableNestedAsset
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_department';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $parent_id;

	/**
	 * @var  integer
	 */
	public $asset_id;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  string
	 */
	public $path = '';

	/**
	 * @var  integer
	 */
	public $address_id;

	/**
	 * @var  integer
	 */
	public $company_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $name2;

	/**
	 * @var  string
	 */
	public $requisition;

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
	 * @var  integer
	 */
	protected $country_id;

	/**
	 * @var  integer|null
	 */
	protected $state_id;

	/**
	 * @var  string
	 */
	protected $address_name;

	/**
	 * @var  string
	 */
	protected $address_name2;

	/**
	 * @var  string
	 */
	protected $address;

	/**
	 * @var  string
	 */
	protected $address2;

	/**
	 * @var  string
	 */
	protected $zip;

	/**
	 * @var  string
	 */
	protected $city;

	/**
	 * @var  string
	 */
	protected $phone;

	/**
	 * @var string
	 */
	public $department_number;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var boolean
	 */
	protected $deleteIfEmptyDefaultAddress = false;

	/**
	 * @var integer
	 */
	protected $oldCompanyId;

	/**
	 * @var interger
	 */
	protected $oldParentId;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Department' => 'parent_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.department'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'parent_id' => array(
				'model' => 'Departments'
		),
		'company_id' => array(
				'model' => 'Companies'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'country_code' => 'Countries'
	);

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'delivery_addresses' => 'Addresses'
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
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		$isNew = (int) $this->id <= 0;

		$addressTable = RedshopbTable::getAdminInstance('Address')
			->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
			->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'))
			->setOption('notSetAddressSeparate', true);

		if ($this->country_id == '' && $this->address == '' && $this->zip == '' && $this->city == '')
		{
			$this->deleteIfEmptyDefaultAddress = true;
		}

		if ($this->deleteIfEmptyDefaultAddress)
		{
			if ($this->address_id)
			{
				if (!$addressTable->delete($this->address_id, true))
				{
					$this->setError($addressTable->getError());

					return false;
				}
			}

			$this->address_id = null;
		}
		else
		{
			$address = array(
				'name' => $this->address_name,
				'name2' => $this->address_name2,
				'id' => $this->address_id,
				'country_id' => $this->country_id,
				'state_id' => $this->state_id,
				'address' => $this->address,
				'address2' => $this->address2,
				'zip' => $this->zip,
				'city' => $this->city,
				'phone' => $this->phone,
				'type' => 2,
				'order' => 8,
				'customer_type' => 'department',
				'customer_id' => (int) $this->id
			);

			if ($address['id'])
			{
				if (!$addressTable->load($address['id']))
				{
					$address['id'] = 0;
				}
			}

			if (!$addressTable->save($address))
			{
				$this->setError($addressTable->getError());

				return false;
			}

			$this->address_id = $addressTable->id;
		}

		$buildACL   = $this->getOption('buildACL', true);
		$rebuildACL = $this->getOption('rebuildACL', false);

		/*
		 * Rebuilds ACL if it's a new record and buildACL option hasn't been shut off,
		 * or if rebuildACL is been turn on intentionally on an existing record
		 */
		if (parent::store($updateNulls))
		{
			if (isset($address) && $addressTable->get('id') && $addressTable->get('customer_id') != $this->get('id'))
			{
				if (!$addressTable->save(array('customer_id' => $this->get('id'))))
				{
					$this->setError($addressTable->getError());

					return false;
				}
			}

			if (($isNew && $buildACL) || $rebuildACL)
			{
				// Rebuilds ACL for the new department or those asked to be rebuilt
				if (!RedshopbHelperACL::rebuildDepartmentACL($this->id))
				{
					return false;
				}
			}

			// Stores the web service reference if the table is not called from a web service - otherwise the ws function will do it
			if (!$this->getOption('store.ws'))
			{
				$this->storeWSReference();
			}

			RedshopbHelperACL::resetSessionLists();

			return true;
		}

		return false;
	}

	/**
	 * Store sync table ws reference
	 *
	 * @return  void
	 */
	protected function storeWSReference()
	{
		if (isset($this->wsSyncMapPK)
			&& isset($this->wsSyncMapPK['erp'])
			&& $this->deleted != 1)
		{
			$wsRef      = $this->wsSyncMapPK['erp'][0];
			$syncHelper = new RedshopbHelperSync;

			$currentDeptNumber = $syncHelper->findSyncedLocalId($wsRef, $this->id);

			if (!$currentDeptNumber || $currentDeptNumber != $this->department_number)
			{
				$syncHelper->deleteSyncedLocalId($wsRef, $this->id);

				if ($this->department_number != '')
				{
					$syncHelper->recordSyncedId($wsRef, $this->department_number, $this->id);
				}
			}
		}
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		$this->name = trim($this->name);

		if (empty($this->parent_id))
		{
			$this->parent_id = null;
		}

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_NAME_CANNOT_BE_EMPTY'));

			return false;
		}

		if ($this->company_id == 0 || $this->company_id == '')
		{
			$this->setError(Text::_('COM_REDSHOPB_DEPARTMENT_COMPANY_NULL'));

			return false;
		}

		if ($this->department_number != '')
		{
			$department     = clone $this;
			$department->id = null;
			$department->reset();

			if ($department->load(
				array(
					'department_number' => $this->department_number,
					'company_id' => $this->company_id,
					'deleted' => 0
				)
			))
			{
				if (!$this->id || $this->id != $department->id)
				{
					$this->setError(Text::_('COM_REDSHOPB_DEPARTMENT_DUPLICATE_DEPARTMENT_NUMBER'));

					return false;
				}
			}
		}

		$department = clone $this;

		if ($department->load($this->id))
		{
			$this->oldCompanyId = $department->company_id;
			$this->oldParentId  = $department->parent_id;
		}

		return true;
	}

	/**
	 * Delete Departments
	 *
	 * @param   string/array  $pk  Array of ids or ids comma separated
	 *
	 * @return boolean
	 */
	public function deleteDepartments($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_department'))
			->where('company_id IN (' . implode(',', $pk) . ')')
			->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
		$db->setQuery($query);

		$departments = $db->loadColumn();

		if ($departments)
		{
			foreach ($departments as $departmentId)
			{
				if ($this->load($departmentId, true))
				{
					if (!$this->delete($departmentId, true))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Get Children Ids
	 *
	 * @param   integer  $pk  The primary key of the node to delete.
	 *
	 * @return  integer|array
	 */
	public function getChildrenIds($pk)
	{
		$k     = $this->_tbl_key;
		$id    = (is_null($pk)) ? $this->$k : $pk;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('node.id')
			->from($db->qn('#__redshopb_department', 'node'))
			->leftJoin(
				$db->qn('#__redshopb_department', 'parent') .
				' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('parent.deleted') . ' = 0 AND' . $db->qn('parent.state') . ' = 1'
			)
			->where('parent.id = ' . (int) $id)
			->where($db->qn('node.deleted') . ' = 0')
			->order('node.lft DESC');
		$db->setQuery($query);

		$results = $db->loadColumn();

		if ($results)
		{
			return $results;
		}

		return $pk;
	}

	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null, $children = true)
	{
		$departments = $pk;

		if (!is_array($departments))
		{
			$departments = array($departments);
		}

		if ($children)
		{
			$departments = $this->getChildrenIds($pk);
		}

		if (!$this->deleteCollectionLinks($departments))
		{
			return false;
		}

		$addressTable = RedshopbTable::getAdminInstance('Address');

		if (!$addressTable->deleteShippingAddresses($departments, 'department'))
		{
			$this->setError($addressTable->getError());

			return false;
		}

		$userTable = RedshopbTable::getAdminInstance('User');

		if (!$userTable->deleteUsers($departments, 'department'))
		{
			$this->setError($userTable->getError());

			return false;
		}

		if (parent::delete($pk, $children))
		{
			if ($this->address_id)
			{
				$addressTable = RedshopbTable::getAdminInstance('Address')
					->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

				if (!$addressTable->delete($this->address_id, true))
				{
					$this->setError($addressTable->getError());

					return false;
				}
			}

			return true;
		}

		return false;
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

		if ($this->id && $this->address_id)
		{
			/** @var RedshopbTableAddress $addressTable */
			$addressTable = RedshopbTable::getAdminInstance('Address');

			if (!$addressTable->load($this->address_id))
			{
				$this->setError($addressTable->getError());

				return false;
			}

			$this->address_name  = $addressTable->name;
			$this->address_name2 = $addressTable->name2;
			$this->address_id    = $addressTable->id;
			$this->country_id    = $addressTable->country_id;
			$this->state_id      = $addressTable->state_id;
			$this->address       = $addressTable->address;
			$this->address2      = $addressTable->address2;
			$this->zip           = $addressTable->zip;
			$this->city          = $addressTable->city;
			$this->phone         = $addressTable->phone;
		}

		return true;
	}

	/**
	 * Overriden asset name
	 *
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_redshopb.department.' . (int) $this->$k;
	}

	/**
	 * Overriden asset title
	 *
	 * @return  string
	 */
	protected function _getAssetTitle()
	{
		return $this->name;
	}

	/**
	 * Overriden to set the right parent ID in asset table
	 *
	 * @param   Table    $table  A Table object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 */
	public function getRedshopbAssetParentId($table = null, $id = null)
	{
		// Initialise variables.
		$assetId = null;
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);

		// This is a department under another department
		if ($this->parent_id > 1)
		{
			// Build the query to get the asset id for the parent department.
			$query->select($db->qn('asset_id'))
				->from($db->qn($this->_tbl))
				->where($db->qn('id') . ' = ' . (int) $this->parent_id);
		}
		else
		{
			// Build the query to get the asset id for the parent company.
			$query->select($db->qn('asset_id'))
				->from($db->qn('#__redshopb_company'))
				->where($db->qn('id') . ' = ' . (int) $this->company_id)
				->where($db->qn('deleted') . ' = 0');
		}

		// Get the asset id from the database.
		$db->setQuery($query);

		$result = $db->loadResult();

		if ($result)
		{
			$assetId = (int) $result;
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Delete departments links to collections.
	 *
	 * @param   array  $departments  Department ids.
	 *
	 * @throws Exception
	 *
	 * @return boolean True on success.
	 */
	protected function deleteCollectionLinks($departments)
	{
		if (!is_array($departments))
		{
			$departments = array($departments);
		}

		$departments = ArrayHelper::toInteger($departments);

		if (empty($departments))
		{
			return true;
		}

		$db = $this->getDbo();
		$db->transactionStart();

		try
		{
			$query = $db->getQuery(true);
			$query->select('DISTINCT ' . $db->qn('collection_id'))
				->from($db->qn('#__redshopb_collection_department_xref'))
				->where($db->qn('department_id') . ' IN (' . implode(',', $departments) . ')');
			$collections     = $db->setQuery($query)->loadColumn();
			$collectionTable = RedshopbTable::getAdminInstance('Collection');

			foreach ($collections as $collection)
			{
				$collectionTable->reset();
				$collectionTable->id = null;
				$collectionTable->setOption('departments.store', true);
				$collectionTable->setOption('departments.load', true);

				if (!$collectionTable->load($collection))
				{
					throw new Exception($collectionTable->getError());
				}

				$collectionDepartments = $collectionTable->get('department_ids');
				$collectionDepartments = ArrayHelper::toInteger($collectionDepartments);
				$collectionDepartments = array_diff($collectionDepartments, $departments);
				$collectionTable->set('department_ids', $collectionDepartments);

				if (empty($collectionDepartments))
				{
					if (!$collectionTable->delete())
					{
						throw new Exception($collectionTable->getError());
					}
				}
				else
				{
					if (!$collectionTable->store())
					{
						throw new Exception($collectionTable->getError());
					}
				}
			}
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			$this->setError($e->getMessage());

			return false;
		}

		$db->transactionCommit();

		return true;
	}
}
