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

/**
 * ACL Role Type table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6.107
 */
class RedshopbTableAclRoleType extends RedshopbTable
{
	/**
	 * @var  string
	 */
	public $name = '';

	/**
	 * @var  integer
	 */
	public $company_role = 0;

	/**
	 * @var  integer
	 */
	public $allow_access = 0;

	/**
	 * @var  string
	 */
	public $type = null;

	/**
	 * @var  integer
	 */
	public $limited = 0;

	/**
	 * @var  integer
	 */
	public $hidden = 0;

	/**
	 * @var  string
	 */
	public $allowed_rules = null;

	/**
	 * @var  string
	 */
	public $allowed_rules_main_company = null;

	/**
	 * @var  string
	 */
	public $allowed_rules_customers = null;

	/**
	 * @var  string
	 */
	public $allowed_rules_company = null;

	/**
	 * @var  string
	 */
	public $allowed_rules_own_company = null;

	/**
	 * @var  string
	 */
	public $allowed_rules_department = null;

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
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var integer
	 */
	public $id;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;

	/**
	 * Constructor
	 *
	 * @param   JDatabase  $db  A database connector object
	 *
	 * @throws  UnexpectedValueException
	 */
	public function __construct(&$db)
	{
		$this->_tableName = 'redshopb_role_type';
		$this->_tbl_key   = 'id';

		parent::__construct($db);
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

		if ($this->getOption('updateACLSimpleAccess', true))
		{
			$this->updateACLSimpleAccessReference();
		}

		return true;
	}

	/**
	 * Called after check().
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function afterCheck()
	{
		if (empty($this->allowed_rules))
		{
			$this->allowed_rules = null;
		}

		if (empty($this->allowed_rules_main_company))
		{
			$this->allowed_rules_main_company = null;
		}

		if (empty($this->allowed_rules_customers))
		{
			$this->allowed_rules_customers = null;
		}

		if (empty($this->allowed_rules_company))
		{
			$this->allowed_rules_company = null;
		}

		if (empty($this->allowed_rules_own_company))
		{
			$this->allowed_rules_own_company = null;
		}

		if (empty($this->allowed_rules_department))
		{
			$this->allowed_rules_department = null;
		}

		return parent::afterCheck();
	}

	/**
	 * Method for update ACL Simple Access reference for specific role type id
	 *
	 * @throws Exception
	 *
	 * @return  boolean   True on success. False otherwise.
	 */
	protected function updateACLSimpleAccessReference()
	{
		$db = $this->getDbo();

		try
		{
			$db->transactionStart();

			// Clear current ACL Simple Access
			$query = $db->getQuery(true)
				->delete($db->qn('#__redshopb_acl_simple_access_xref'))
				->where($db->qn('role_type_id') . ' = ' . (int) $this->id);
			$db->setQuery($query);

			if (!$db->execute())
			{
				throw new Exception($db->getError());
			}

			// Update Simple ACL Access reference for Allowed Rules
			if (!empty($this->allowed_rules))
			{
				$data = json_decode($this->allowed_rules);
				$this->processACLSimpleAccessReference($data, $this->id, 'global');
			}

			// Update Simple ACL Access reference for Allowed Rules on Main Company
			if (!empty($this->allowed_rules_main_company))
			{
				$data = json_decode($this->allowed_rules_main_company);
				$this->processACLSimpleAccessReference($data, $this->id, 'global');
			}

			// Update Simple ACL Access reference for Allowed Rules for Cusomters Companies
			if (!empty($this->allowed_rules_customers))
			{
				$data = json_decode($this->allowed_rules_customers);
				$this->processACLSimpleAccessReference($data, $this->id, 'company');
			}

			// Update Simple ACL Access reference for Allowed Rules on Own and Children Companies
			if (!empty($this->allowed_rules_company))
			{
				$data = json_decode($this->allowed_rules_company);
				$this->processACLSimpleAccessReference($data, $this->id, 'company');
			}

			// Update Simple ACL Access reference for Allowed Rules on Own Company
			if (!empty($this->allowed_rules_own_company))
			{
				$data = json_decode($this->allowed_rules_own_company);
				$this->processACLSimpleAccessReference($data, $this->id, 'company');
			}

			// Update Simple ACL Access reference for Allowed Rules on Own and Children Departments
			if (!empty($this->allowed_rules_department))
			{
				$data = json_decode($this->allowed_rules);
				$this->processACLSimpleAccessReference($data, $this->id, 'department');
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Return array of available permission list.
	 *
	 * @return  array  List of available permission.
	 */
	protected function getACLSimpleAccessPermissionTree()
	{
		$permission = array(
			'redshopb.company.manage'       => array('redshopb.company.manage', 'redshopb.company.manage.own', 'redshopb.company.view'),
			'redshopb.user.manage'          => array('redshopb.user.manage', 'redshopb.user.manage.own', 'redshopb.user.view'),
			'redshopb.user.negativewallet'  => array('redshopb.user.negativewallet'),
			'redshopb.department.manage'    => array('redshopb.department.manage', 'redshopb.department.manage.own', 'redshopb.department.view'),
			'redshopb.collection.manage'    => array('redshopb.collection.manage', 'redshopb.collection.manage.own', 'redshopb.collection.view'),
			'redshopb.product.manage'       => array('redshopb.product.manage', 'redshopb.product.manage.own', 'redshopb.product.view'),
			'redshopb.order.manage'         => array('redshopb.order.manage', 'redshopb.order.manage.own', 'redshopb.order.view'),
			'redshopb.category.manage'      => array('redshopb.category.manage', 'redshopb.category.manage.own', 'redshopb.category.view'),
			'redshopb.layout.manage'        => array('redshopb.layout.manage', 'redshopb.layout.manage.own', 'redshopb.layout.view'),
			'redshopb.currency.manage'      => array('redshopb.currency.manage', 'redshopb.currency.view'),
			'redshopb.mainwarehouse.manage' => array('redshopb.mainwarehouse.manage'),
			'redshopb.tag.manage'           => array('redshopb.tag.manage', 'redshopb.tag.manage.own', 'redshopb.tag.view'),
			'redshopb.address.manage'       => array('redshopb.address.manage', 'redshopb.address.manage.own', 'redshopb.address.view'),
			'redshopb.user.points'          => array('redshopb.user.points'),
			'redshopb.order.impersonate'    => array('redshopb.order.impersonate'),
			'redshopb.order.statusupdate'   => array('redshopb.order.statusupdate'),
			'redshopb.order.place'          => array('redshopb.order.place'),
		);

		return $permission;
	}

	/**
	 * Method for process on group of permissions for specific role type and scope
	 *
	 * @param   array   $permissions  List of permissions
	 * @param   int     $roleTypeId   ID of specific role
	 * @param   string  $scope        Scope for permission. Default is "global"
	 *
	 * @return  boolean               True on success. False otherwise.
	 */
	protected function processACLSimpleAccessReference($permissions = array(), $roleTypeId = 0, $scope = "global")
	{
		if (empty($permissions) || !is_array($permissions))
		{
			return false;
		}

		$db = Factory::getDbo();

		foreach ($permissions as $permission)
		{
			foreach ($this->getACLSimpleAccessPermissionTree() as $simpleRule => $rule)
			{
				if (in_array($permission, $rule))
				{
					$dataQuery = $db->getQuery(true)
						->select($db->qn('sa.id'))
						->select($db->qn('a.id'))
						->select($db->quote($roleTypeId))
						->select($db->quote($scope))
						->from($db->qn('#__redshopb_acl_access', 'sa'))
						->from($db->qn('#__redshopb_acl_access', 'a'))
						->where($db->qn('sa.name') . ' = ' . $db->quote($simpleRule))
						->where($db->qn('a.name') . ' = ' . $db->quote($permission));

					$query = $db->getQuery(true)
						->insert($db->qn('#__redshopb_acl_simple_access_xref'))
						->columns($db->qn(array('simple_access_id', 'access_id', 'role_type_id', 'scope')))
						->values($dataQuery);

					$db->setQuery($query)->execute();

					break;
				}
			}
		}

		return true;
	}
}
