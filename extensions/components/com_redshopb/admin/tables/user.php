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
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\User\UserHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * User table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableUser extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_user';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_key = 'id';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $joomla_user_id;

	/**
	 * @var  integer
	 */
	protected $company_id;

	/**
	 * @var  array
	 */
	protected $companies;

	/**
	 * @var  integer
	 */
	public $department_id = null;

	/**
	 * @var  integer
	 */
	public $address_id;

	/**
	 * @var integer
	 */
	public $wallet_id;

	/**
	 * @var  string
	 */
	public $phone;

	/**
	 * @var  string
	 */
	public $cell_phone;

	/**
	 * @var  integer
	 */
	public $employee_number;

	/**
	 * @var  string
	 */
	public $image = '';

	/**
	 * @var integer
	 */
	public $send_email = 1;

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
	protected $userStatus;

	/**
	 * @var  string
	 */
	protected $name;

	/**
	 * @var  string
	 */
	protected $username;

	/**
	 * @var  string
	 */
	protected $password;

	/**
	 * @var  string
	 */
	protected $password2;

	/**
	 * @var  string
	 */
	protected $email;

	/**
	 * @var  string
	 */
	protected $registerDate;

	/**
	 * @var  integer
	 */
	protected $country_id = '';

	/**
	 * @var  integer|null
	 */
	protected $state_id = '';

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
	protected $address_phone;

	/**
	 * @var  string
	 */
	protected $role_type_id;

	/**
	 * @var integer
	 */
	protected $block = 0;

	/**
	 * @var integer
	 */
	public $use_company_email = 0;

	/**
	 * @var integer
	 */
	protected $useCompanyAddress = 0;

	/**
	 * @var float
	 */
	protected $points = 0;

	/**
	 * @var string
	 */
	protected $passwordEncrypted;

	/**
	 * @var array
	 */
	public $companyIds = array();

	/**
	 * @var array
	 */
	protected $delivery_addresses = array();

	/**
	 * @var integer
	 */
	protected $default_delivery_address_id = null;

	/**
	 * @var string
	 */
	public $name1;

	/**
	 * @var string
	 */
	public $name2;

	/**
	 * @var  string
	 */
	public $printed_name;

	/**
	 * @var boolean
	 */
	protected $deleteIfEmptyDefaultAddress = false;

	/**
	 * @var [type]
	 */
	protected $userCode;

	/**
	 * @var [type]
	 */
	protected $params;

	/**
	 * @var array
	 */
	protected $joomla_usergroups = array();

	/**
	 * @var  string
	 */
	protected $activation;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.user'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks. If started by dot, it ignores the table prefix in list query
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'company_id' => array(
			'model' => 'Companies',
			'alias' => 'umc.company_id'
		),
		'department_id' => array(
			'model' => 'Departments'
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
		'default_delivery_address_id' => 'Addresses',
		'delivery_addresses' => 'Addresses',
		'company_id' => 'Companies',
		'companies' => 'Companies'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'no_email',
		'send_email'
	);

	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'useTransaction' => true,
		'forceWebserviceUpdate' => false,
		'addressUpdate' => true,
		'forceCustomersUpdate' => false,
		'store.ws' => false,
		'noPasswordUpdate' => false
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
		$this->companyIds = null;

		parent::reset();
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		try
		{
			if ($this->getOption('useTransaction', true))
			{
				$this->_db->transactionStart();
			}

			$isNew = (int) $this->id <= 0;

			// Save the joomla user.
			$this->storeJoomlaUser();

			// Create a wallet if this is a new user
			if ($isNew)
			{
				$this->createWallet();
			}

			if ($this->getOption('forceWebserviceUpdate', false) && $this->wallet_id)
			{
				$walletMoneyTable = RedshopbTable::getAdminInstance('Wallet_Money');
				$walletMoneyTable->load(array('wallet_id' => $this->wallet_id, 'currency_id' => 159), true);
				$walletMoneyTable->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));
				$data = array('wallet_id' => $this->wallet_id, 'currency_id' => 159, 'amount' => $this->points);

				if (!$walletMoneyTable->save($data))
				{
					throw new Exception($walletMoneyTable->getError());
				}
			}

			if ($this->getOption('addressUpdate', true) === true)
			{
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

							throw new Exception($addressTable->getError());
						}
					}

					$this->address_id = null;
				}
				else
				{
					$address = array(
						'name'       => $this->address_name,
						'name2'      => $this->address_name2,
						'id'         => $this->address_id,
						'email'      => $this->email,
						'country_id' => $this->country_id,
						'state_id'   => $this->state_id,
						'address'    => $this->address,
						'address2'   => $this->address2,
						'zip'        => $this->zip,
						'city'       => $this->city,
						'phone'       => $this->address_phone,
						'type'       => 2,
						'order'      => 7,
						'customer_type' => 'employee',
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

						throw new Exception($addressTable->getError());
					}

					$this->address_id = $addressTable->id;
				}
			}

			if (parent::store($updateNulls))
			{
				if ($this->getOption('addressUpdate', true) === true
					&& isset($address)
					&& $addressTable->get('id')
					&& $addressTable->get('customer_id') != $this->get('id'))
				{
					if (!$addressTable->save(array('customer_id' => $this->get('id'))))
					{
						$this->setError($addressTable->getError());

						throw new Exception($addressTable->getError());
					}
				}

				// Store the customers
				if ($this->getOption('forceCustomersUpdate', false) == true)
				{
					$this->storeCompanySalesPersonXref();
				}

				// Store the customers
				$this->storeMultiCompanyUser();

				if ($this->getOption('useTransaction', true))
				{
					$this->_db->transactionCommit();
				}

				return true;
			}

			throw new Exception(Text::plural('COM_REDSHOPB_ITEM_SAVE_FAILURE', Text::_('COM_REDSHOPB_USER_FORM_TITLE')));
		}
		catch (Exception $e)
		{
			if ($this->getOption('useTransaction', true))
			{
				$this->_db->transactionRollback();
			}

			if (count($this->getErrors()) == 0)
			{
				$this->setError($e->getMessage());
			}

			return false;
		}
	}

	/**
	 * Store user company relation
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	protected function storeMultiCompanyUser()
	{
		$xrefTable = RedshopbTable::getAdminInstance('User_Multi_Company');
		$id        = 0;
		$xrefTable->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
			->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

		if ($xrefTable->load(
			array(
				'user_id' => $this->id,
				'main' => 1
			)
		))
		{
			$id = $xrefTable->id;
		}

		if (!$xrefTable->save(
			array(
				'id' => $id,
				'user_id' => $this->id,
				'company_id' => $this->company_id,
				'role_id' => $this->role_type_id,
				'main' => 1,
				'state' => 1,
			)
		))
		{
			throw new Exception($xrefTable->getError());
		}
	}

	/**
	 * Store sync table ws reference
	 *
	 * @return  void
	 */
	protected function storeWSReference()
	{
		if (isset($this->wsSyncMapPK) && isset($this->wsSyncMapPK['erp']))
		{
			$wsRef      = $this->wsSyncMapPK['erp'][0];
			$syncHelper = new RedshopbHelperSync;

			$currentCustNumber = $syncHelper->findSyncedLocalId($wsRef, $this->id);

			if (empty($currentCustNumber) || $currentCustNumber != $this->employee_number)
			{
				$syncHelper->deleteSyncedLocalId($wsRef, $this->id);

				if ($this->employee_number != '')
				{
					$syncHelper->recordSyncedId($wsRef, $this->employee_number, $this->id);
				}
			}
		}
	}

	/**
	 * Store the company sales persons
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @throws Exception
	 */
	protected function storeCompanySalesPersonXref()
	{
		$currentCompanyIds = RedshopbHelperUser::getCompaniesforSalesPerson($this->id);
		$xrefTable         = RedshopbTable::getAdminInstance('Company_Sales_Person_Xref');

		// Deletes companies for this sales person
		$companiesDelete = array_diff($currentCompanyIds, $this->companyIds);

		if ($companiesDelete)
		{
			foreach ($companiesDelete as $companyId)
			{
				if ($xrefTable->load(
					array(
						'user_id' => $this->id,
						'company_id' => $companyId
					)
				))
				{
					if (!$xrefTable->delete())
					{
						throw new Exception($xrefTable->getError());
					}
				}
			}
		}

		// Saves missing (new) companies for this sales person
		if (is_array($this->companyIds) && count($this->companyIds) > 0)
		{
			// Store the new items
			foreach ($this->companyIds as $companyId)
			{
				if (!$xrefTable->load(
					array(
						'user_id' => $this->id,
						'company_id' => $companyId
					)
				))
				{
					if (!$xrefTable->save(
						array(
							'user_id' => $this->id,
							'company_id' => $companyId
						)
					))
					{
						throw new Exception($xrefTable->getError());
					}
				}
			}
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

		$this->company_id = null;

		// Load the user
		if ($this->id && $this->joomla_user_id)
		{
			/** @var Joomla\CMS\Table\User $userTable */
			$userTable = Table::getInstance('User');

			if (!$userTable->load($this->joomla_user_id))
			{
				return false;
			}

			$this->name         = $userTable->get('name');
			$this->username     = $userTable->get('username');
			$this->password     = $userTable->get('password');
			$this->password2    = $userTable->get('password');
			$this->email        = $userTable->get('email');
			$this->registerDate = DateTime::createFromFormat(
				$this->_db->getDateFormat(),
				$userTable->get('registerDate')
			)->format('d-m-Y');
			$this->block        = $userTable->get('block');
			$this->activation   = $userTable->get('activation');
			$this->userStatus   = ($this->block == 1) ? 0 : 1;

			$walletMoney = RedshopbTable::getAdminInstance('Wallet_Money');

			if ($walletMoney->load(array('currency_id' => 159, 'wallet_id' => $this->wallet_id)))
			{
				$this->points = $walletMoney->amount;
			}
		}

		// Load main company_id
		if ($this->id)
		{
			if ($this->id == RedshopbHelperUser::getUserRSid())
			{
				$selectedCompany = RedshopbEntityUser::getInstance($this->id)->getSelectedCompany();

				if ($selectedCompany)
				{
					$this->company_id = $selectedCompany->get('id');
				}
			}

			if (!$this->company_id)
			{
				/** @var RedshopbTableUser_Multi_Company $userCompanyTable */
				$userCompanyTable = RedshopbTable::getAdminInstance('User_Multi_Company');

				if (!$userCompanyTable->load(array('user_id' => $this->id, 'main' => 1)))
				{
					$this->setError($userCompanyTable->getError());

					return false;
				}

				$this->company_id = $userCompanyTable->company_id;
			}
		}

		// Load all the companies
		if ($this->id)
		{
			$this->companies = RedshopbHelperUser::getUserCompanies($this->id);
		}

		// Load the address
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
			$this->address_phone = $addressTable->phone;
		}

		// Loads associated data
		if ($this->id)
		{
			$customer = RedshopbEntityCustomer::getInstance($this->id, RedshopbEntityCustomer::TYPE_EMPLOYEE);

			// Loads the Companies where it's set as sales person
			$this->companyIds                  = RedshopbHelperUser::getCompaniesforSalesPerson($this->id);
			$this->delivery_addresses          = $customer->getShippingAddresses()->ids();
			$this->default_delivery_address_id = array($customer->getDefaultShippingAddress()->id);

			// Loads the role
			if (!$this->loadRole())
			{
				return false;
			}
		}

		return true;
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
		$roleTypes        = RedshopbHelperRole::getTypeIds();
		$rolesWithNoLogin = array();

		if (empty($this->department_id) || $this->department_id == '')
		{
			$this->department_id = null;
		}

		foreach ($roleTypes as $roleType)
		{
			if ($roleType->allow_access == 0)
			{
				$rolesWithNoLogin[] = $roleType->id;
			}
		}

		// Employee without login
		if (in_array($this->role_type_id, $rolesWithNoLogin))
		{
			$password                = UserHelper::genRandomPassword();
			$this->password          = $password;
			$this->password2         = $password;
			$this->passwordEncrypted = '';

			if (!isset($this->username) || empty($this->username))
			{
				setlocale(LC_ALL, 'en_US.UTF8');

				$clean          = iconv('UTF-8', 'ASCII//TRANSLIT', $this->name1);
				$clean          = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
				$clean          = strtolower(trim($clean, '-'));
				$clean          = preg_replace("/[\/_|+ -]+/", '-', $clean);
				$this->username = urlencode($clean);

				if ($this->checkUsername($this->username))
				{
					$this->username = uniqid($this->username . '_');
				}

				$username = $this->username;
			}
			else
			{
				$username = uniqid($this->username . '_');
			}
		}
		else
		{
			$username = $this->username;
		}

		if ((int) $this->use_company_email == 1)
		{
			$companyName = RedshopbEntityCompany::getInstance($this->company_id)->get('name');

			if (isset($this->department_id) && !is_null($this->department_id) && $this->department_id != 0)
			{
				$departmentName = RedshopbHelperDepartment::getName($this->department_id, true);
			}

			$domainName = OutputFilter::stringURLSafe(isset($departmentName) ? $departmentName . '.' . $companyName : $companyName);
			$domainName = preg_replace("/[&'#]/", '', $domainName);
			$username   = preg_replace("/[&'#]/", '', OutputFilter::stringURLSafe($username));

			$this->email      = $username . '@' . $domainName . '.com';
			$this->send_email = 0;
		}
		elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_VALID_MAIL'));

			return false;
		}

		if ((int) $this->useCompanyAddress != 0)
		{
			/** @var RedshopbTableDepartment $departmentTable */
			$departmentTable = RedshopbTable::getAdminInstance('Department');
			$departmentTable->load($this->department_id);

			if (!empty($departmentTable->address_id))
			{
				$addressId = $departmentTable->address_id;
			}
			else
			{
				$addressId = RedshopbHelperCompany::getCompanyById($this->company_id)->addressId;
			}

			/** @var RedshopbTableAddress $addressTable */
			$addressTable = RedshopbTable::getAdminInstance('Address');

			$addressTable->load($addressId);

			if (!empty($addressTable))
			{
				$this->address_name = $addressTable->name;
				$this->country_id   = $addressTable->country_id;
				$this->state_id     = $addressTable->state_id;
				$this->address      = $addressTable->address;
				$this->address2     = $addressTable->address2;
				$this->zip          = $addressTable->zip;
				$this->city         = $addressTable->city;
			}
		}

		if ($this->employee_number != '')
		{
			$user     = clone $this;
			$user->id = null;
			$user->reset();

			if ($user->load(
				array(
					'employee_number' => $this->employee_number
				)
			))
			{
				if (!$this->id || $this->id != $user->id)
				{
					$this->setError(Text::_('COM_REDSHOPB_USER_DUPLICATE_EMPLOYEE_NUMBER'));

					return false;
				}
			}
		}

		return parent::check();
	}

	/**
	 * Delete Users
	 *
	 * @param   string/array  $pk            Array of ids or ids comma separated
	 * @param   string        $customerType  Customer type
	 *
	 * @return boolean
	 */
	public function deleteUsers($pk, $customerType)
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
			->select('ru.id')
			->from($db->qn('#__redshopb_user', 'ru'))
			->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = ru.id');

		switch ($customerType)
		{
			case 'department':
				$query->where('ru.department_id IN (' . implode(',', $pk) . ')');
			break;
			case 'company':
				$query->where('umc.company_id IN (' . implode(',', $pk) . ')');
			break;
			default:
				return true;
		}

		$db->setQuery($query);

		$users = $db->loadColumn();

		if ($users)
		{
			foreach ($users as $userId)
			{
				if ($this->load($userId, true))
				{
					if (!$this->delete($userId))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Delete on or more registers
	 *
	 * @param   string/array  $pk  Array of ids or ids comma separated
	 *
	 * @throws Exception
	 *
	 * @return  boolean  Deleted successfully?
	 */
	public function delete($pk = null)
	{
		// Check that we are not trying to delete the current user.
		$joomlaUserId = (int) $this->joomla_user_id;
		$walletId     = (int) $this->wallet_id;
		$userId       = (int) Factory::getUser()->id;

		if ($joomlaUserId === $userId)
		{
			$this->setError(Text::_('COM_REDSHOPB_USER_CANNOT_DELETE_CURRENT_USER'));

			return false;
		}

		if (Factory::getUser($joomlaUserId)->authorise('core.admin'))
		{
			$this->setError(Text::_('COM_REDSHOPB_USER_CANNOT_DELETE_SUPER_USER'));

			return false;
		}

		try
		{
			if ($this->getOption('useTransaction', true))
			{
				$this->_db->transactionStart();
			}

			$addressTable = RedshopbTable::getAdminInstance('Address');

			if (!$addressTable->deleteShippingAddresses($pk, 'employee'))
			{
				throw new Exception($addressTable->getError());
			}

			if (parent::delete($pk))
			{
				if ($walletId)
				{
					$walletTable = RedshopbTable::getAdminInstance('Wallet');

					if (!$walletTable->delete($walletId))
					{
						throw new Exception($walletTable->getError());
					}
				}

				/** @var Joomla\CMS\Table\User $userTable */
				$userTable = Table::getInstance('User');
				$userTable->load($joomlaUserId);

				if (!$userTable->delete($joomlaUserId))
				{
					throw new Exception($userTable->getError());
				}

				// Delete session for deleted user
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->delete($db->qn('#__session'))
					->where($db->qn('userid') . ' = ' . (int) $joomlaUserId);

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					throw $e;
				}

				// Update orders placed by user so they can still be accessed
				$query = $db->getQuery(true)
					->update($db->qn('#__redshopb_order', 'o'))
					->set($db->qn('o.customer_type') . ' = ' . $db->q('company'))
					->set($db->qn('o.customer_id') . ' = o.customer_company')
					->where($db->qn('o.customer_type') . ' = ' . $db->q('employee'))
					->where($db->qn('o.customer_id') . ' = ' . $db->q((int) $pk));

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					throw $e;
				}

				if ($this->address_id)
				{
					$addressTable = RedshopbTable::getAdminInstance('Address')
						->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
						->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

					if (!$addressTable->delete($this->address_id, true))
					{
						throw new Exception($addressTable->getError());
					}
				}

				if ($this->getOption('useTransaction', true))
				{
					$this->_db->transactionCommit();
				}

				return true;
			}

			throw new Exception;
		}
		catch (Exception $e)
		{
			if ($this->getOption('useTransaction', true))
			{
				$this->_db->transactionRollback();
			}

			if ($e->getMessage())
			{
				$this->setError($e->getMessage());
			}

			return false;
		}
	}

	/**
	 * Save the joomla user.
	 *
	 * @return boolean  True on success, false otherwise
	 *
	 * @throws Exception
	 */
	protected function storeJoomlaUser()
	{
		// Get the joomla group id corresponding to the role.
		$roleTypeId = (int) $this->role_type_id;
		$companyId  = (int) $this->company_id;
		$groups     = array();

		// This is existing user user
		if ($this->id)
		{
			$userCompanies = RedshopbEntityUser::getInstance($this->id)->getUserMultiCompanies();

			foreach ($userCompanies as $userCompany)
			{
				// If we are fetching main company and role then we should use the current ones
				$joomlaGroupId = $userCompany->main == 1 ?
					RedshopbHelperRole::getJoomlaGroupId($companyId, $roleTypeId)
					: RedshopbHelperRole::getJoomlaGroupId($userCompany->company_id, $userCompany->role_id);

				if (!$joomlaGroupId)
				{
					throw new Exception(Text::_('COM_REDSHOPB_ERROR_LOADING_ROLETYPE_ID_COMPANY'));
				}

				$groups[] = $joomlaGroupId;
			}
		}
		else
		{
			$joomlaGroupId = RedshopbHelperRole::getJoomlaGroupId($companyId, $roleTypeId);

			if (!$joomlaGroupId)
			{
				throw new Exception(Text::_('COM_REDSHOPB_ERROR_LOADING_ROLETYPE_ID_COMPANY'));
			}

			$groups[] = $joomlaGroupId;
		}

		// Save the joomla user.
		$user = User::getInstance($this->joomla_user_id ? $this->joomla_user_id : 0);

		// Adds default Joomla usergroup (only if role type is allowed login)
		$roleTypes        = RedshopbHelperRole::getTypeIds();
		$rolesWithNoLogin = array();

		foreach ($roleTypes as $roleType)
		{
			if ($roleType->allow_access == 0)
			{
				$rolesWithNoLogin[] = $roleType->id;
			}
		}

		// Users with login, add default usergroup
		if (!in_array($roleTypeId, $rolesWithNoLogin))
		{
			$config = ComponentHelper::getParams('com_users');

			$groupId = $config->get('new_usertype');

			if ($groupId)
			{
				$groups[] = $groupId;
			}
		}

		// Salespersons, add salespersons groups
		$groups = array_merge($groups, RedshopbHelperUser::getRolesforSalesPerson($this->id));

		if (!is_null($this->userStatus) && (int) $this->userStatus == 1)
		{
			$this->block = 0;
		}
		elseif (!is_null($this->userStatus) && (int) $this->userStatus == 0)
		{
			$this->block = 1;
		}
		elseif (is_null($this->block))
		{
			$this->block = 0;
		}

		if (!empty($this->joomla_usergroups))
		{
			$groups = array_merge($groups, $this->joomla_usergroups);
		}

		$params = new Registry;
		$params->loadString($this->params);

		$userInfos = array(
			'name'      => trim($this->name1 . ' ' . $this->name2),
			'username'  => $this->username,
			'password'  => $this->password,
			'password2' => $this->password2,
			'email'     => $this->email,
			'groups'    => $groups,
			'block'     => $this->block,
			'send_email' => $this->send_email,
			'use_company_email' => $this->use_company_email,
			'activation' => $this->activation,
			'params'    => $params->toArray()
		);

		if ($this->getOption('noPasswordUpdate', false))
		{
			unset($userInfos['password']);
			unset($userInfos['password2']);
		}

		if ($this->joomla_user_id)
		{
			$userInfos['id'] = $this->joomla_user_id;
		}
		else
		{
			// If new user then we set default language of the company
			$company   = RedshopbHelperCompany::getCompanyById($companyId);
			$languages = LanguageHelper::getLanguages('lang_code');

			// If exists use it, if not use default
			if (!empty($company->site_language) && !empty($languages[$company->site_language]))
			{
				$userInfos['params']['language'] = $languages[$company->site_language]->lang_code;
			}
		}

		if (!$user->bind($userInfos))
		{
			throw new Exception($user->getError());
		}

		if (!empty($this->passwordEncrypted))
		{
			$config         = RedshopbEntityConfig::getInstance();
			$encryptionKey  = $config->getEncryptionKey();
			$user->password = RedshopbHelperUser::decryptCsvPassword($this->passwordEncrypted, $encryptionKey);
		}

		if (!$user->save())
		{
			throw new Exception($user->getError());
		}

		$this->joomla_user_id = $user->id;

		return true;
	}

	/**
	 * Loads the user role type.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	protected function loadRole()
	{
		$roleTypeId = RedshopbHelperRole::getUserRoleTypeId($this->company_id, $this->joomla_user_id);

		if (!$roleTypeId)
		{
			$this->setError('COM_REDSHOPB_ERROR_LOAD_ROLE_TYPE_ID');

			return false;
		}

		$this->role_type_id = $roleTypeId;

		return true;
	}

	/**
	 * Creates a new wallet for the new user.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @throws Exception
	 */
	protected function createWallet()
	{
		$walletTable = RedshopbTable::getAdminInstance('Wallet');

		if (!$walletTable->save(array()))
		{
			throw new Exception($walletTable->getError());
		}

		$this->wallet_id = $walletTable->id;

		return true;
	}

	/**
	 * Method for check if username has exist
	 *
	 * @param   string  $username  Username string
	 *
	 * @return  boolean            True on exist. False otherwise.
	 */
	public function checkUsername($username)
	{
		if (empty($username))
		{
			return false;
		}

		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('COUNT(*) AS ' . $db->qn('count'))
			->from($db->qn('#__users'))
			->where($db->qn('username') . ' = ' . $db->quote($username));
		$db->setQuery($query);
		$result = $db->loadObject();

		return (boolean) $result->count;
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
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		// It only forces the noPasswordUpdate option if there is a username set
		if (isset($src['username']))
		{
			if (!isset($src['password']) && !isset($src['password2'])
				&& ((isset($src['id']) && (int) $src['id'] > 0) || (int) $this->id > 0))
			{
				$this->setOption('noPasswordUpdate', true);
			}
			else
			{
				$this->setOption('noPasswordUpdate', false);
			}
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Function for getting Joomla usergroups.
	 *
	 * @param   int   $id       Joomla user id for associated usergroups.
	 * @param   bool  $idsOnly  Load usergroup ids only.
	 *
	 * @return  mixed           Null on fail, array of usergroup objects otherwise.
	 *
	 * @since   1.12.50
	 */
	public function getJoomlaUserGroups($id = 0, $idsOnly = false)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$b2bQ  = clone $query;

		$b2bQ->select(
			array (
				$db->qn('lft'),
				$db->qn('rgt')
			)
		)
			->from($db->qn('#__usergroups'))
			->where($db->qn('title') . ' = ' . $db->q('redSHOPB2B'));

		$b2bUsergroup = $db->setQuery($b2bQ)->loadObject();

		$query->from($db->qn('#__usergroups', 'u'))
			->where($db->qn('u.lft') . ' NOT BETWEEN ' . (int) $b2bUsergroup->lft . ' AND ' . (int) $b2bUsergroup->rgt)
			->order($db->qn('u.lft') . ' ASC');

		if ($idsOnly)
		{
			$query->select($db->qn('u.id'));
		}
		else
		{
			$query->select('u.*');
		}

		if ($id)
		{
			$query->innerJoin($db->qn('#__user_usergroup_map', 'um') . ' ON ' . $db->qn('um.group_id') . ' = ' . $db->qn('u.id'));
			$query->where($db->qn('um.user_id') . ' = ' . (int) $id);
		}

		$db->setQuery($query);

		if ($idsOnly)
		{
			$res = $db->loadColumn();
		}
		else
		{
			$res = $db->loadObjectList();
		}

		return $res;
	}
}
