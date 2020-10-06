<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

/**
 * Users Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelUsers extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_users';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'user_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'u.id',
				'name', 'u.name',
				'username', 'j.username',
				'company', 'u.company',
				'department', 'u.department',
				'company_id', 'umc.company_id',
				'department_id', 'u.department_id',
				'role', 'u.role',
				'block', 'u.block',
				'user_block',
				'user_role_type',
				'role_id',
				'employee_number', 'u.employee_number'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$ordering  = is_null($ordering) ? 'u.name' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$company    = $this->getState('filter.company_id', $this->getState('filter.company'));
		$department = $this->getState('filter.department_id', $this->getState('filter.department'));

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('u') . '.*',
					$db->qn('c.name', 'company'),
					$db->qn('d.name', 'department'),
					$db->qn('j.username', 'username'),
					$db->qn('j.block', 'block'),
					$db->qn('rt.name', 'role'),
					$db->qn('rt.id', 'role_id'),
					$db->qn('a.name', 'address_name1'),
					$db->qn('a.name2', 'address_name2'),
					$db->qn('a.address', 'address_line1'),
					$db->qn('a.address2', 'address_line2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('co.alpha2', 'country_code'),
					'IF (u.use_company_email = 0, j.email, ' . $db->q('') . ') AS email',
					$db->qn('u.use_company_email', 'no_email'),
					$db->qn('j.block', 'blocked'),
					$db->qn('dda.id', 'default_delivery_address_id'),
					$db->qn('umc.company_id', 'company_id')
				)
			)
			->from($db->qn('#__redshopb_user', 'u'))
			->innerJoin(
				$db->qn('#__redshopb_user_multi_company', 'umc') . ' ON ' . $db->qn('umc.user_id') . ' = ' . $db->qn('u.id') . ' AND umc.main = 1'
			)
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('umc.company_id')
				. ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->leftJoin(
				$db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('d.id') . ' = ' . $db->qn('u.department_id')
				. '  AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
			)
			->innerJoin($db->qn('#__users', 'j') . ' ON ' . $db->qn('u.joomla_user_id') . ' = ' . $db->qn('j.id'))

			// Join over the group for the role type
			->innerJoin($db->qn('#__user_usergroup_map', 'map') . ' ON ' . $db->qn('map.user_id') . ' = ' . $db->qn('u.joomla_user_id'))
			->innerJoin(
				$db->qn('#__redshopb_role', 'r') . ' ON ' .
				$db->qn('r.joomla_group_id') . ' = ' . $db->qn('map.group_id') .
				' AND ' . $db->qn('r.company_id') . ' = ' . $db->qn('c.id')
			)
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('u.address_id') . ' = ' . $db->qn('a.id'))
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('rt.id') . ' = ' . $db->qn('r.role_type_id'))
			->leftJoin(
				$db->qn('#__redshopb_address', 'dda') .
					' ON ' . $db->qn('u.id') . ' = ' . $db->qn('dda.customer_id') .
					' AND ' . $db->qn('dda.customer_type') . ' = ' . $db->q('employee') .
					' AND ' . $db->qn('dda.type') . ' = 3'
			);

		// Name is name1 on web services
		if ($this->getState('list.ws', false))
		{
			$query->select($db->qn('u.name1', 'name'));
		}
		else
		{
			$query->select($db->qn('j.name', 'name'));
		}

		// Check for available companies and departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user = Factory::getUser();

			// Companies where user can see its users
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesByPermission($user->id, 'redshopb.user.view');

			if ($availableCompanies == '0' && $department == 0)
			{
				$query->where(
					$db->qn('d.id') . ' IN (' . RedshopbHelperACL::listAvailableDepartmentsByPermission($user->id, 'redshopb.user.view') . ')'
				);
			}
			elseif ($company == 0)
			{
				$query->where($db->qn('c.id') . ' IN (' . $availableCompanies . ')');
			}
		}

		// Filter by block status.
		$block = $this->getState('filter.user_block', $this->getState('filter.blocked'));

		if ($block == '0' || $block == 'false')
		{
			$query->where($db->qn('j.block') . ' = 0');
		}
		elseif ($block == '1' || $block == 'true')
		{
			$query->where($db->qn('j.block') . ' = 1');
		}

		// Filter by no email status.
		$useCompanyEmail = $this->getState('filter.no_email');

		if ($useCompanyEmail == '0' || $useCompanyEmail == 'false')
		{
			$query->where($db->qn('u.use_company_email') . ' = 0');
		}
		elseif ($useCompanyEmail == '1' || $useCompanyEmail == 'true')
		{
			$query->where($db->qn('u.use_company_email') . ' = 1');
		}

		// Filter by send email status.
		$sendEmail = $this->getState('filter.send_email');

		if ($sendEmail == '0' || $sendEmail == 'false')
		{
			$query->where($db->qn('u.send_email') . ' = 0');
		}
		elseif ($sendEmail == '1' || $sendEmail == 'true')
		{
			$query->where($db->qn('u.send_email') . ' = 1');
		}

		$id = (int) $this->getState('filter.id', 0);

		if (is_numeric($id) && $id > 0)
		{
			$query->where($db->qn('u.id') . ' = ' . (int) $id);
		}

		// Filter by array list of id
		$ids = (array) $this->getState('filter.ids', array());

		if (!empty($ids))
		{
			$ids = ArrayHelper::toInteger($ids);
			$query->where($db->qn('u.id') . ' IN (' . implode(',', $ids) . ')');
		}

		// Filter search
		$search = $this->getState('filter.search_users', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where(
				'((j.name LIKE ' . $search . ')' .
				'OR (u.name1 LIKE ' . $search . ')' .
				'OR (u.name2 LIKE ' . $search . ')' .
				'OR (u.printed_name LIKE ' . $search . ')' .
				'OR (u.employee_number LIKE ' . $search . ')' .
				'OR (j.username LIKE ' . $search . ')' .
				'OR (u.use_company_email = 0 AND j.email LIKE ' . $search . '))'
			);
		}

		$roleType = $this->getState('filter.role_id', $this->getState('filter.user_role_type'));

		if (is_numeric($roleType))
		{
			$query->where($db->qn('rt.id') . ' = ' . (int) $roleType);
		}

		// Filter by role type's type field
		$roleTypeType = $this->getState('filter.user_role_type_type');

		if ($roleTypeType != '')
		{
			$query->where($db->qn('rt.type') . ' = ' . $db->q($roleTypeType));
		}

		if ($company)
		{
			$query->where($db->qn('c.id') . ' = ' . $company);
		}

		if ($department)
		{
			$query->where($db->qn('d.id') . ' = ' . $department);
		}

		$filterZip = $this->getState('filter.zip', null);

		if (!is_null($filterZip) && $filterZip != '')
		{
			$query->where($db->qn('a.zip') . ' = ' . $db->q($filterZip));
		}

		$filterCity = $this->getState('filter.city', null);

		if (!is_null($filterCity) && $filterCity != '')
		{
			$query->where($db->qn('a.city') . ' = ' . $db->q($filterCity));
		}

		$filterCountryCode = $this->getState('filter.country_code', null);

		if (!is_null($filterCountryCode) && $filterCountryCode != '')
		{
			$query->where($db->qn('co.alpha2') . ' = ' . $db->q($filterCountryCode));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering', 'u.name');
		$directionList = $this->getState('list.direction', 'asc');

		if ($orderList == 'u.company')
		{
			$orderList = 'c.name';
		}
		elseif ($orderList == 'u.department')
		{
			$orderList = 'd.name';
		}
		elseif ($orderList == 'u.block')
		{
			$orderList = 'j.block';
		}
		elseif ($orderList == 'u.name')
		{
			$orderList = 'j.name';
		}
		elseif ($orderList == 'u.role')
		{
			$orderList = 'rt.name';
		}
		elseif ($orderList == 'u.id')
		{
			$orderList = 'u.id';
		}

		$order     = !empty($orderList) ? $db->qn($orderList) : $db->qn('j.name');
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		if ($this->getState('streamOutput', '') == 'csv')
		{
			return $this->getItemsCsv();
		}
		else
		{
			$items = parent::getItems();

			if ($items)
			{
				foreach ($items AS $item)
				{
					$item->companies = RedshopbHelperUser::getUserCompanies($item->id);
				}
			}

			return $items;
		}
	}

	/**
	 * Import users
	 *
	 * @param   array  $importData  Data received from CSV file
	 *
	 * @return  mixed
	 */
	public function import($importData)
	{
		$result  = array();
		$columns = $this->getCsvColumns();

		if (is_array($importData))
		{
			$allowedIds = $this->getAllowedUsers();

			foreach ($importData as $rowNumber => $row)
			{
				if (!is_array($row))
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED', Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_COLUMNS_MISSING', $rowNumber + 2)
					);
					continue;
				}

				$data = array();

				// Prepare data with same columns
				foreach ($columns as $columnKey => $columnValue)
				{
					if (isset($row[strtolower($columnValue)]))
					{
						$data[$columnKey] = $row[strtolower($columnValue)];
					}
				}

				$data['CRUD'] = !empty($data['CRUD']) ? strtoupper($data['CRUD']) : '';

				// Check if address can be modified
				if (in_array($data['CRUD'], array('UPDATE', 'DELETE')))
				{
					if (!in_array($data['id'], $allowedIds))
					{
						$result['error'][] = Text::_('COM_REDSHOPB_USER_ERROR_PERMISSIONS')
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
						continue;
					}
				}

				$data['company_id'] = RedshopbHelperCompany::getCompanyIdByCustomerNumber($data['customer_number']);

				if (empty($data['company_id']))
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED',
						Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NUMBER')
					)
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}

				$data['country_id']   = RedshopbEntityCountry::loadFromName($data['country'])->id;
				$data['role_type_id'] = RedshopbHelperRole::getRoleIdByName($data['role']);

				$password          = $data['password_insert'];
				$passwordEncrypted = (isset($data['password_encrypt']) ? $data['password_encrypt'] : '');

				if ($data['CRUD'] != 'CREATE')
				{
					if (empty($data['id']))
					{
						$result['error'][] = Text::sprintf(
							'COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED',
							Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_SYSTEM_ID')
						)
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
						continue;
					}
				}

				if ($data['CRUD'] == 'CREATE')
				{
					$data['id']             = 0;
					$data['joomla_user_id'] = 0;
					$data['params']         = array('migratedFromCSV' => 1);

					if (!empty($password))
					{
						$data['password']  = $password;
						$data['password2'] = $password;
					}
					elseif (!empty($passwordEncrypted))
					{
						$data['passwordEncrypted'] = $passwordEncrypted;
					}
					else
					{
						// If everything else fails
						$password          = UserHelper::genRandomPassword();
						$data['password']  = $password;
						$data['password2'] = $password;
					}

					if (!isset($data['use_company_email']))
					{
						if (isset($data['email']))
						{
							$data['use_company_email'] = 0;
						}
						else
						{
							$data['use_company_email'] = 1;
						}
					}
				}
				elseif ($data['CRUD'] == 'UPDATE')
				{
					if (!empty($password))
					{
						$data['password']  = $password;
						$data['password2'] = $password;
					}
					elseif (!empty($passwordEncrypted))
					{
						$data['passwordEncrypted'] = $passwordEncrypted;
					}

					$user                   = RedshopbHelperUser::getUser((int) $data['id']);
					$data['address_id']     = $user->addressId;
					$data['joomla_user_id'] = $user->joomla_user_id;
				}

				/** @var RedshopbModelUser $userModel */
				$userModel = RedshopbModelAdmin::getInstance('User', 'RedshopbModel', array('ignore_request' => true));

				if ($data['CRUD'] == 'UPDATE' || $data['CRUD'] == 'CREATE')
				{
					if (!$userModel->save($data))
					{
						$result['error'][] = Text::sprintf('COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED', $userModel->getError())
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					}
					else
					{
						$userId = $userModel->getState($userModel->getName() . '.id');

						if (!empty($data['walletInfo']))
						{
							$walletInfos = explode('|', $data['walletInfo']);

							if (!empty($walletInfos))
							{
								foreach ($walletInfos as $walletInfo)
								{
									$wallet = explode(':', $walletInfo);

									if (!empty($wallet) && count($wallet) > 1)
									{
										$currency = RedshopbHelperProduct::getCurrency(trim($wallet[0]));

										if (empty($currency) || !$userModel->saveWallet($userId, $currency->id, trim($wallet[1])))
										{
											$result['error'][] = Text::sprintf(
												'COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED_WALLET',
												$userModel->getError()
											) . RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
										}
									}
									else
									{
										$result['error'][] = Text::sprintf('COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED_WALLET')
											. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
									}
								}
							}
						}

						$result['success'][$data['CRUD']][] = 1;
					}
				}
				elseif ($data['CRUD'] == 'DELETE')
				{
					$userId = (int) $data['id'];

					if (!$userModel->delete($userId))
					{
						$result['error'][] = Text::sprintf('COM_REDSHOPB_USERS_UNSUCCESSFULLY_DELETED', $userModel->getError())
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					}
					else
					{
						$result['success'][$data['CRUD']][] = 1;
					}
				}
				else
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_USERS_UNSUCCESSFULLY_IMPORTED', Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_CRUD')
					)
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
				}
			}
		}

		return $result;
	}

	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public function getCsvColumns()
	{
		return array(
			'CRUD' => Text::_('COM_REDSHOPB_CRUD'),
			'company' => Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NAME'),
			'customer_number' => Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NUMBER'),

			'department' => Text::_('COM_REDSHOPB_DEPARTMENT_LABEL'),
			'department_id' => Text::_('COM_REDSHOPB_EXPORT_DEPARTMENT_NUMBER'),

			'id' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_SYSTEM_ID'),
			'employee_number' => Text::_('COM_REDSHOPB_EXPORT_ERP_USER_ID'),
			'name1' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_NAME'),
			'printed_name' => Text::_('COM_REDSHOPB_EXPORT_PRINTED_NAME'),
			'address' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_ADDRESS'),
			'address2' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_ADDRESS') . '2',
			'zip' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_ZIP'),
			'city' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_CITY'),
			'country' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_COUNTRY'),
			'phone' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_PHONE'),
			'cell_phone' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_CELL_PHONE'),

			'username' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_USERNAME'),
			'email' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_EMAIL'),
			'block' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_BLOCK'),
			'use_company_email' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_USE_COMPANY_EMAIL'),

			'role' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_ROLE'),

			'walletInfo' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_POINT_BALLANCE'),

			'password_insert' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_PASSWORD'),
			'password_encrypt' => Text::_('COM_REDSHOPB_EXPORT_EMPLOYEE_PASSWORD_ENCRYPTED'),
		);
	}

	/**
	 * Get data for CSV export
	 *
	 * @param   string   $tableAlias   Aliased table name (usually the first letter)
	 * @param   string   $data         Array data in string format (from e.g. implode())
	 *
	 * @return   array|false
	 */
	public function getItemsCsv($tableAlias = null, $data = null)
	{
		$db            = $this->getDbo();
		$config        = RedshopbEntityConfig::getInstance();
		$encryptionKey = $config->getEncryptionKey();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->getListQuery();

		$query->select(
			array(
					'a.country_id, a.address, a.address2',
					'co.name AS country',
					'j.password',
					'c.customer_number',
					'd.id AS department_id',
					$db->q('UPDATE') . ' AS CRUD'
				)
		);

		if (null !== $data)
		{
			$data = implode(',', $db->q($data));
			$query->where("{$db->qn("{$tableAlias}.id")} IN ({$data})");
		}

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Prepare Wallet info and password encryption
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$queryWallet = $db->getQuery(true)
					->select('c.alpha3, wm.amount')
					->from($db->qn('#__redshopb_wallet_money', 'wm'))
					->leftJoin($db->qn('#__redshopb_currency', 'c') . ' ON c.id = wm.currency_id')
					->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON u.wallet_id = wm.wallet_id')
					->where('u.id = ' . $item->id);

				$db->setQuery($queryWallet);
				$userWallets = $db->loadObjectList();

				$item->walletInfo = array();

				foreach ($userWallets as $userWallet)
				{
					$item->walletInfo[] = $userWallet->alpha3 . ':' . $userWallet->amount;
				}

				$item->walletInfo = implode('|', $item->walletInfo);

				$item->password_encrypt = RedshopbHelperUser::encryptCsvPassword($item->password, $encryptionKey);
				$item->password_insert  = '';
			}
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Get list of ids of allowed users
	 *
	 * @return  array
	 */
	public function getAllowedUsers()
	{
		$db	= $this->getDbo();
		/** @var RedshopbModelUsers $usersModel */
		$usersModel = RedshopbModelAdmin::getInstance('Users', 'RedshopbModel', array('ignore_request' => true));
		/** @var JDatabaseQuery $itemsQuery */
		$itemsQuery = $usersModel->getListQuery();
		$itemsQuery->clear('select');
		$itemsQuery->select('u.id');

		$db->setQuery($itemsQuery);
		$items = $db->loadColumn();

		return !empty($items) ? $items : array();
	}

	/**
	 * Function for activating provided users.
	 *
	 * @param   array  $users  Array of user ids.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since version
	 */
	public function activate($users)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('u.id', 'id'),
				$db->qn('u.activation', 'activation'),
				$db->qn('u.email', 'email')
			)
		)
			->from($db->qn('#__redshopb_user', 'ru'))
			->innerJoin($db->qn('#__users', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('ru.joomla_user_id'))
			->where($db->qn('ru.id') . ' IN (' . implode(',', $users) . ')');
		$users   = $db->setQuery($query)->loadObjectList();
		$jIds    = array();
		$newOnes = array();

		foreach ($users as $user)
		{
			$jIds[] = $user->id;

			if (strlen((string) $user->activation) > 1)
			{
				$newOnes[] = $user;
			}
		}

		// Start activation
		$db->transactionStart();

		$query->clear()
			->update($db->qn('#__users'))
			->set($db->qn('block') . ' = 0')
			->set($db->qn('activation') . ' = ' . $db->q(''))
			->where($db->qn('id') . ' IN (' . implode(',', $jIds) . ')');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$db->transactionRollback();

			return false;
		}

		foreach ($newOnes as $newOne)
		{
			// Send email to user
			$sender          = RedshopbHelperEmail::getSenderInfo();
			$user            = RedshopbEntityUser::getInstance()->loadFromJoomlaUser($newOne->id);
			$subject         = Text::_('COM_REDSHOPB_REGISTER_FLOW_USER_APPROVED_MAIL_SUBJECT');
			$emailTemplateId = RedshopbEntityConfig::getInstance()->get('register_flow_user_approved_email_template', 0);
			$emailTemplate   = RedshopbHelperTemplate::renderTemplate(
				'user-approved', 'email', $emailTemplateId, array('user' => $user), '', null, true
			);

			if (!empty($emailTemplate->params))
			{
				$templateParams = new Registry($emailTemplate->params);
				$pSubject       = $templateParams->get('mail_subject', '');

				if (!empty($pSubject))
				{
					$subject = $pSubject;
				}
			}

			$body   = RedshopbHelperEmail::fixImagesPaths($emailTemplate->content);
			$mailer = RFactory::getMailer();

			$mailer->setSender(array($sender->sender, $sender->fromName));
			$mailer->addRecipient(array($newOne->email));
			$mailer->isHtml(true);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Encoding = 'base64';

			if (!$mailer->Send())
			{
				// Do stuff send activation email
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_ERROR_COULD_NOT_SEND_EMAIL'), 'error');

				$db->transactionRollback();

				return false;
			}
		}

		// Finish activating users
		$db->transactionCommit();

		return true;
	}

	/**
	 * Function for blocking provided users.
	 *
	 * @param   array  $users  Array of user ids.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since version
	 */
	public function block($users)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('joomla_user_id'))
			->from($db->qn('#__redshopb_user'))
			->where($db->qn('id') . ' IN (' . implode(',', $users) . ')');
		$jIds = $db->setQuery($query)->loadColumn();

		$query->clear()
			->update($db->qn('#__users'))
			->set($db->qn('block') . ' = 1')
			->where($db->qn('id') . ' IN (' . implode(',', $jIds) . ')');
		$db->setQuery($query);

		return (bool) $db->execute();
	}
}
