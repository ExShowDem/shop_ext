<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Access\Access;

/**
 * User Entity
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 * @since       1.0
 *
 * @property string $name1
 * @property string $name2
 */
final class RedshopbEntityUser extends RedshopbEntity
{
	use RedshopbEntityTraitAddress, RedshopbEntityTraitAddressesShipping, RedshopbEntityTraitAddressShippingDefault;
	use RedshopbEntityTraitCompany, RedshopbEntityTraitDepartment, RedshopbEntityTraitAddressDelivery;
	use RedshopbEntityTraitImage, RedshopbEntityTraitFields, RedshopbEntityTraitCustomer;

	/**
	 * User groups this user belongs to
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $groups;

	/**
	 * Associated Joomla user
	 *
	 * @var    User
	 * @since  1.7
	 */
	protected $joomlaUser;

	/**
	 * User role
	 *
	 * @var    RedshopbEntityRole
	 * @since  2.0
	 */
	protected $role;

	/**
	 * Mapped joomla user to avoid duplicated loading
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected static $mappedJoomlaUsers = array();

	/**
	 * User companies this user belongs to
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $userMultiCompanies;

	/**
	 * User company this user has selected
	 *
	 * @var    RedshopbEntityCompany
	 * @since  1.7
	 */
	protected $selectedCompany;

	/**
	 * User role this user has selected
	 *
	 * @var    RedshopbEntityRole
	 * @since  1.7
	 */
	protected $selectedRole;

	/**
	 * Get the user groups for this user
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getGroups()
	{
		if (null === $this->groups)
		{
			$this->loadGroups();
		}

		return $this->groups;
	}

	/**
	 * Gets the associated joomla user
	 *
	 * @return  User
	 *
	 * @since   1.7
	 */
	public function getJoomlaUser()
	{
		if (null === $this->joomlaUser)
		{
			$this->loadJoomlaUser();
		}

		return $this->joomlaUser;
	}

	/**
	 * Get the user role
	 *
	 * @return  RedshopbEntityRole
	 *
	 * @since   2.0
	 */
	public function getRole()
	{
		if (null === $this->role)
		{
			$this->loadRole();
		}

		return $this->role;
	}

	/**
	 * Get the user role
	 *
	 * @return  RedshopbEntityCompany
	 *
	 * @since   2.0
	 */
	public function getSelectedCompany()
	{
		if (null === $this->selectedCompany)
		{
			$app = Factory::getApplication();

			// If we have selected multi company saved in the session we fetch it
			if ($app->getUserState('shop.multi_company_id', null))
			{
				$this->selectedCompany = RedshopbEntityCompany::getInstance($app->getUserState('shop.multi_company_id', null))->loadItem();

				return $this->selectedCompany;
			}

			// Check if user only have one company
			$this->getUserMultiCompanies();

			if (count($this->userMultiCompanies) == 1)
			{
				$company               = reset($this->userMultiCompanies);
				$this->selectedCompany = RedshopbEntityCompany::getInstance($company->company_id)->loadItem();

				$app->setUserState('shop.multi_company_id', $company->company_id);
				$app->setUserState('shop.role_type_id', $company->role_id);
			}
		}

		return $this->selectedCompany;
	}

	/**
	 * Get the company_id for the active user
	 *
	 * @return  integer
	 *
	 * @since   2.0
	 */
	public static function getCompanyIdForCurrentUser()
	{
		$user = self::loadActive();

		if (RedshopbHelperACL::isSuperAdmin())
		{
			$user = self::loadActive(true);
		}

		$company = $user->getSelectedCompany();

		if ($company)
		{
			return $company->get('id', 0);
		}

		return 0;
	}

	/**
	 * Method to get a user address id by address
	 *
	 * @param   array  $address  associative array of address values
	 *
	 * @return integer|mixed
	 */
	public function getAddressIdByAddress($address = array())
	{
		if (empty($address))
		{
			return 0;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__redshopb_address');

		$fields           = array_keys(RedshopbTable::getAdminInstance('Address')->getFields());
		$hasAddressFields = false;

		foreach ($address AS $fieldName => $value)
		{
			if (!in_array($fieldName, $fields) || empty($value))
			{
				continue;
			}

			if ($fieldName != 'customer_id' && $fieldName != 'customer_type')
			{
				// We have an actual address
				$hasAddressFields = true;
			}

			$query->where($db->qn($fieldName) . ' = ' . $db->q($value));
		}

		if (!$hasAddressFields)
		{
			return 'NO_ADDRESS_FIELDS';
		}

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Override to ensure that we cache joomla_user_id - user id relationships
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function load($id = null)
	{
		$instance     = parent::load($id);
		$joomlaUserId = $instance->get('joomla_user_id');

		if ($joomlaUserId && !isset(static::$mappedJoomlaUsers[$joomlaUserId]))
		{
			static::$mappedJoomlaUsers[$joomlaUserId] = $instance->id;
		}

		return $instance;
	}

	/**
	 * Default loading is trying to use the associated table.  Optionally using two arrays.
	 *
	 * @param   string|array  $key       Field name(s) used as key
	 * @param   string|array  $keyValue  Value(s) used if it's not the $this->id property of the instance
	 *
	 * @return  self
	 */
	public function loadItem($key = 'id', $keyValue = null)
	{
		parent::loadItem($key, $keyValue);

		// This is the current user we will set it company_id
		if ($keyValue == RedshopbHelperUser::getUserRSid())
		{
			if ($this->item)
			{
				$this->item->company_id = self::getCompanyIdForCurrentUser();
			}
		}

		return $this;
	}

	/**
	 * Fast use proxy to load the user from the active Joomla user
	 *
	 * @param   boolean   $loadImpersonation   If true it will return an instance based on the
	 *                                         impersonated user instead of the logged in Joomla user
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	public static function loadActive($loadImpersonation = false)
	{
		if ($loadImpersonation)
		{
			try
			{
				$customer = self::getCustomer();

				if ($customer instanceof RedshopbEntityCustomerEmployee)
				{
					return $customer->getUser();
				}
			}
			catch (Exception $e)
			{
				// If the customer can't be loaded, then load the joomla user
			}
		}

		return static::loadFromJoomlaUser();
	}

	/**
	 * Load delivery address from database
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadDefaultShippingAddress()
	{
		$this->defaultShippingAddress = RedshopbEntityAddress::getInstance();

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('a') . '.*')
			->from($db->qn('#__redshopb_address', 'a'))
			->innerJoin(
				$db->qn('#__redshopb_user', 'u')
				. ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('a.customer_id')
				. ' AND ' . $db->qn('a.customer_type') . ' = ' . $db->q('employee')
			)
			->where($db->qn('a.type') . ' = ' . (int) RedshopbEntityAddress::TYPE_DEFAULT_SHIPPING)
			->where($db->qn('u.id') . ' = ' . (int) $this->id);

		$db->setQuery($query, 0, 1);

		$addressData = $db->loadObject();

		if ($addressData)
		{
			$this->defaultShippingAddress = RedshopbEntityAddress::getInstance($addressData->id)->bind($addressData);
		}

		return $this;
	}

	/**
	 * Get an instance or create it from a joomla user id.
	 *
	 * @param   mixed  $joomlaUserId  integer: Joomla user identifier | null: load active user
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	public static function loadFromJoomlaUser($joomlaUserId = null)
	{
		if (null === $joomlaUserId)
		{
			$joomlaUserId = Factory::getUser()->get('id');
		}

		$joomlaUserId = (int) $joomlaUserId;

		$instance = static::getInstance();

		if (!$joomlaUserId)
		{
			return $instance;
		}

		if (isset(static::$mappedJoomlaUsers[$joomlaUserId]))
		{
			return static::load(static::$mappedJoomlaUsers[$joomlaUserId]);
		}

		$table = RTable::getAdminInstance('user', array(), 'com_redshopb');

		if ($table->load(array('joomla_user_id' => $joomlaUserId)))
		{
			$instance->loadFromTable($table);

			static::$mappedJoomlaUsers[$joomlaUserId] = $instance->id;
		}

		return $instance;
	}

	/**
	 * Get an instance from a user email address.
	 *
	 * @param   string  $email  Email address to load user from.
	 *
	 * @return  self
	 *
	 * @since   1.12.56
	 */
	public function loadFromEmail($email)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('ru.id'))
			->from($db->qn('#__users', 'u'))
			->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('u.id'))
			->where($db->qn('u.email') . ' = ' . $db->q($email));
		$id = (int) $db->setQuery($query)->loadResult();

		return self::load($id);
	}

	/**
	 * Load User Multi Companies.
	 *
	 * @return  array
	 *
	 * @since   1.12.58
	 */
	public function getUserMultiCompanies()
	{
		if (null === $this->userMultiCompanies)
		{
			$this->loadUserMultiCompanies();
		}

		return $this->userMultiCompanies;
	}

	/**
	 * Load user role from DB
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadUserMultiCompanies()
	{
		$this->userMultiCompanies = array();

		$item = $this->getItem();

		if (!$item)
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('umc.*, c.name AS company_name, u.name1 AS user_name, r.name AS role_name')
			->from($db->qn('#__redshopb_user_multi_company', 'umc'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = umc.company_id')
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON u.id = umc.user_id')
			->leftJoin($db->qn('#__redshopb_role_type', 'r') . ' ON r.id = umc.role_id')
			->where($db->qn('umc.user_id') . ' = ' . (int) $item->id);

		$db->setQuery($query);

		$this->userMultiCompanies = $db->loadObjectList();

		return $this->userMultiCompanies;
	}

	/**
	 * Load the associated Joomla User
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	protected function loadJoomlaUser()
	{
		$this->joomlaUser = new User;

		$item = $this->getItem();

		if (!$item || !$item->joomla_user_id)
		{
			return $this;
		}

		$this->joomlaUser = new User($item->joomla_user_id);

		return $this;
	}

	/**
	 * Load the groups where the user is active
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	protected function loadGroups()
	{
		$this->groups = array();

		$item = $this->getItem();

		if (!$item || !$item->joomla_user_id)
		{
			return $this;
		}

		$this->groups = Access::getGroupsByUser($item->joomla_user_id);

		return $this;
	}

	/**
	 * Load user role from DB
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadRole()
	{
		$this->role = RedshopbEntityRole::getInstance();

		$item = $this->getItem();

		if (!$item || !isset($item->joomla_user_id) || !$item->joomla_user_id)
		{
			return $this;
		}

		$db                = $this->getDbo();
		$selectedCompany   = $this->getSelectedCompany();
		$selectedCompanyId = $selectedCompany ? $selectedCompany->get('id') : 0;

		$query = $db->getQuery(true)
			->select('r.*')
			->from($db->qn('#__redshopb_user_multi_company', 'umc'))
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('umc.role_id') . ' = ' . $db->qn('r.role_type_id'))
			->where($db->qn('umc.user_id') . ' = ' . (int) $item->id)
			->where($db->qn('umc.role_id') . ' = ' . (int) Factory::getApplication()->getUserState('shop.role_type_id', 0))
			->where($db->qn('r.company_id') . ' = ' . (int) $selectedCompanyId);

		$db->setQuery($query, 0, 1);

		$role = $db->loadObject();

		if ($role)
		{
			$this->role = RedshopbEntityRole::getInstance($role->id)->bind($role);
		}

		return $this;
	}

	/**
	 * Get the available shipping addresses
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadShippingAddresses()
	{
		$this->shippingAddresses = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('a') . '.*')
			->from($db->qn('#__redshopb_address', 'a'))
			->innerJoin(
				$db->qn('#__redshopb_user', 'u')
				. ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('a.customer_id')
				. ' AND ' . $db->qn('a.customer_type') . ' = ' . $db->q('employee')
			)
			->where($db->qn('a.type') . ' = ' . (int) RedshopbEntityAddress::TYPE_SHIPPING)
			->where($db->qn('u.id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$addresses = $db->loadObjectList();

		foreach ($addresses as $address)
		{
			$entity = RedshopbEntityAddress::getInstance($address->id)->bind($address);

			$this->shippingAddresses->add($entity);
		}

		return $this;
	}

	/**
	 * Load company from DB
	 *
	 * @return  self
	 */
	protected function loadCompany()
	{
		$this->company = RedshopbEntityCompany::getInstance();

		$item = $this->getItem();

		// User not found, return B2C company
		if (!$item)
		{
			$this->company = RedshopbApp::getB2cCompany();
		}
		elseif (self::getCompanyIdForCurrentUser())
		{
			$this->company = RedshopbEntityCompany::load(self::getCompanyIdForCurrentUser());
		}

		return $this;
	}

	/**
	 * Fast use proxy to see if current member is root
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function isRoot()
	{
		$joomlaUser = $this->getJoomlaUser();

		return $joomlaUser->authorise('core.admin');
	}

	/**
	 * Method for check if user is from Main Company
	 *
	 * @return boolean
	 */
	public function isFromMainCompany()
	{
		$company = $this->getCompany();

		return (empty($company->getId()) || $company->get('type') == 'main');
	}

	/**
	 * Get the billing address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getBillingAddress()
	{
		$this->determineBillingAddress();

		return $this->address;
	}

	/**
	 * Determine the billing address
	 *
	 * @return  void
	 */
	protected function determineBillingAddress()
	{
		$currentUserId  = (int) $this->get('id');
		$usersCompanyId = RedshopbHelperCompany::getCompanyIdByCustomer($currentUserId, RedshopbEntityCustomer::TYPE_EMPLOYEE);
		$usersCompany   = RedshopbEntityCompany::load($usersCompanyId);

		if ((int) $usersCompany->get('b2c') === 1)
		{
			$instance      = RedshopbEntityAddress::getInstance();
			$this->address = $instance->loadItem(
				array('customer_id', 'customer_type', 'type'),
				array($currentUserId, RedshopbEntityCustomer::TYPE_EMPLOYEE, 2)
			);
		}
		else
		{
			$this->address = $usersCompany->getAddress();
		}
	}
}
