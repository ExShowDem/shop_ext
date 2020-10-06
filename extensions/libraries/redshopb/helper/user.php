<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * A User helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperUser
{
	/**
	 * Array with users departments
	 *
	 * @var array
	 */
	public static $usersDepartment = array();

	/**
	 * Array with role users
	 *
	 * @var array
	 */
	public static $usersRole = array();

	/**
	 * Array with users' role ids
	 *
	 * @var array
	 */
	public static $usersRoleIds = array();

	/**
	 * Get user. If $id is not set, current user is returned.
	 *
	 * @param   int     $id        User id to be loaded
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return object user object
	 */
	public static function getUser($id = 0, $userType = 'redshopb')
	{
		static $users = array();

		if (!$id)
		{
			$userType = 'joomla';
			$id       = Factory::getUser()->id;
		}

		$key = (int) $id . '_' . $userType;

		if (!array_key_exists($key, $users))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('ju.name', 'name'),
						$db->qn('ru.name2', 'name2'),
						'IF (ru.use_company_email = 0, ju.email, ' . $db->q('') . ') AS email',
						$db->qn('ju.username', 'username'),
						$db->qn('ru.id', 'id'),
						$db->qn('ru.phone', 'phone'),
						$db->qn('ru.department_id', 'department'),
						$db->qn('umc.company_id', 'company'),
						$db->qn('ru.wallet_id', 'wallet'),
						$db->qn('a.address', 'address'),
						$db->qn('a.address2', 'address2'),
						$db->qn('a.id', 'addressId'),
						$db->qn('a.city', 'city'),
						$db->qn('a.zip', 'zip'),
						$db->qn('c.name', 'country'),
						$db->qn('s.name', 'state_name'),
						$db->qn('ju.id', 'joomla_user_id'),
						$db->qn('ru.use_company_email'),
						$db->qn('ru.send_email'),
						$db->qn('ru.employee_number', 'number')
					)
				)
				->from($db->qn('#__redshopb_user', 'ru'))
				->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
				->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('ru.address_id'))
				->leftJoin($db->qn('#__redshopb_country', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.country_id'))
				->leftJoin($db->qn('#__redshopb_state', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('a.state_id'));

			if ($userType == 'joomla')
			{
				$query->where($db->qn('ru.joomla_user_id') . ' = ' . (int) $id);
				$isCurrentUser = $id == Factory::getUser()->id;
			}
			else
			{
				$query->where($db->qn('ru.id') . ' = ' . (int) $id);
				$user		   = RedshopbEntityUser::loadFromJoomlaUser();
				$isCurrentUser = $id == $user->getId();
			}

			$isSuper = Factory::getUser()->authorise('core.admin');

			// If we are fetching different user than the one the user is currently logged on we need to get its main company
			if ($isCurrentUser || $isSuper)
			{
				$query->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id AND umc.main = 1');
			}
			else
			{
				$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();
				$query->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id AND umc.company_id = ' . $selectedCompanyId);
			}

			$users[$key] = $db->setQuery($query)->loadObject();

			if ($users[$key])
			{
				$users[$users[$key]->joomla_user_id . '_joomla'] = $users[$key];
				$users[$users[$key]->id . '_redshopb']           = $users[$key];
			}
		}

		return $users[$key];
	}

	/**
	 * Get user department. If $id is not set, current user department is returned.
	 *
	 * @param   int      $id           User id for getting department
	 * @param   string   $userType     User type : redshopb/joomla.
	 * @param   boolean  $hideDeleted  Hide deleted departments.
	 *
	 * @return object Department object
	 */
	public static function getUserDepartment($id = 0, $userType = 'redshopb', $hideDeleted = true)
	{
		if (!isset(self::$usersDepartment[$id][$userType]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select(
				array(
					$db->qn('d.id', 'id'),
					$db->qn('d.parent_id', 'parent'),
					$db->qn('d.level', 'level'),
					$db->qn('d.name', 'name'),
					$db->qn('d.company_id', 'company_id'),
					$db->qn('d.requisition', 'requisition'),
					$db->qn('d.asset_id', 'asset_id'),
					$db->qn('a.name', 'addressName'),
					$db->qn('a.id', 'addressId'),
					$db->qn('a.address', 'address'),
					$db->qn('a.address2', 'address2'),
					$db->qn('a.city', 'city'),
					$db->qn('a.zip', 'zip'),
					$db->qn('c.name', 'country'),
					$db->qn('s.name', 'state_name')
				)
			)
				->from($db->qn('#__redshopb_department', 'd'))
				->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON d.id = ru.department_id')
				->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = d.address_id')
				->leftJoin($db->qn('#__redshopb_country', 'c') . ' ON c.id = a.country_id')
				->leftJoin($db->qn('#__redshopb_state', 's') . ' ON s.id = a.state_id');

			if ($hideDeleted)
			{
				$query->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');
			}

			if ($id == 0 || $userType == 'joomla')
			{
				if ($userType == 'joomla' && $id > 0)
				{
					$query->where('ru.joomla_user_id = ' . (int) $id);
				}
				else
				{
					$user = Factory::getUser();
					$query->where('ru.joomla_user_id = ' . (int) $user->id);
				}
			}
			else
			{
				$query->where('ru.id = ' . (int) $id);
			}

			$db->setQuery($query);

			self::$usersDepartment[$id] = array($userType => $db->loadObject());
		}

		return self::$usersDepartment[$id][$userType];
	}

	/**
	 * Get user company. If $id is not set, current user company is returned.
	 *
	 * @param   int      $id           User id for getting company.
	 * @param   string   $userType     User type : redshopb/joomla.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return object Company object
	 */
	public static function getUserCompany($id = 0, $userType = 'redshopb', $hideDeleted = true)
	{
		if (!$id)
		{
			$userType = 'joomla';
			$id       = Factory::getUser()->id;
		}

		$key                 = (int) $id . '_' . $userType . '_' . (int) $hideDeleted;
		static $usersCompany = array();

		if (!array_key_exists($key, $usersCompany))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('comp.id', 'id'),
						$db->qn('comp.parent_id', 'parent'),
						$db->qn('comp.level', 'level'),
						$db->qn('comp.name', 'name'),
						$db->qn('comp.type', 'type'),
						$db->qn('comp.use_wallets', 'useWallet'),
						$db->qn('comp.calculate_fee', 'calculate_fee'),
						$db->qn('comp.contact_info', 'contact_info'),
						$db->qn('comp.employee_mandatory', 'mandatory'),
						$db->qn('comp.order_approval', 'order_approval'),
						$db->qn('comp.currency_id', 'currency_id'),
						$db->qn('comp.site_language', 'site_language'),
						$db->qn('comp.freight_amount_limit', 'limit'),
						$db->qn('comp.freight_amount', 'amount'),
						$db->qn('comp.freight_product_id', 'productId'),
						$db->qn('comp.wallet_product_id', 'walletProductId'),
						$db->qn('comp.requisition', 'requisition'),
						$db->qn('comp.customer_number', 'number'),
						$db->qn('comp.send_mail_on_order', 'send_mail'),
						$db->qn('comp.show_stock_as', 'show_stock_as'),
						$db->qn('comp.use_collections', 'use_collections'),
						$db->qn('comp.stockroom_verification', 'stockroom_verification'),
						$db->qn('comp.b2c'),
						$db->qn('a.name', 'addressName'),
						$db->qn('a.id', 'addressId'),
						$db->qn('a.address', 'address'),
						$db->qn('a.address2', 'address2'),
						$db->qn('a.city', 'city'),
						$db->qn('a.zip', 'zip'),
						$db->qn('cont.name', 'country'),
						$db->qn('state.name', 'state_name'),
						$db->qn('comp.asset_id', 'asset_id'),
						$db->qn('ru.joomla_user_id'),
						$db->qn('ru.id', 'redshopb_user_id')
					)
				)
				->from($db->qn('#__redshopb_company', 'comp'))
				->innerJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.company_id = comp.id')
				->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON ru.id = umc.user_id')
				->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = comp.address_id')
				->leftJoin($db->qn('#__redshopb_country', 'cont') . ' ON cont.id = a.country_id')
				->leftJoin($db->qn('#__redshopb_state', 'state') . ' ON state.id = a.state_id');

			if ($hideDeleted)
			{
				$query->where($db->qn('comp.deleted') . ' = 0');
			}

			if ($userType == 'joomla')
			{
				$query->where($db->qn('ru.joomla_user_id') . ' = ' . (int) $id);
				$userId = Factory::getUser()->id;
			}
			else
			{
				$query->where($db->qn('ru.id') . ' = ' . (int) $id);
				$userId = self::getUserRSid();
			}

			// If current user is logged in, he might have selected another company to work with
			if ($userId != $id)
			{
				$query->where($db->qn('umc.main') . ' = 1');
			}
			else
			{
				$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();
				$query->where($db->qn('umc.company_id') . ' = ' . $selectedCompanyId);
			}

			$usersCompany[$key] = $db->setQuery($query)->loadObject();

			if ($usersCompany[$key])
			{
				$usersCompany[$usersCompany[$key]->joomla_user_id . '_joomla_' . (int) $hideDeleted]     = $usersCompany[$key];
				$usersCompany[$usersCompany[$key]->redshopb_user_id . '_redshopb_' . (int) $hideDeleted] = $usersCompany[$key];
			}
		}

		return $usersCompany[$key];
	}

	/**
	 * Get user company Id. If $id is not set, current user company Id is returned.
	 *
	 * @param   int      $id           User id for getting company
	 * @param   string   $userType     User type : redshopb/joomla.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return  integer  Company Id
	 */
	public static function getUserCompanyId($id = 0, $userType = 'redshopb', $hideDeleted = true)
	{
		$company = self::getUserCompany($id, $userType, $hideDeleted);

		if ($company)
		{
			return $company->id;
		}

		return 0;
	}

	/**
	 * Get user company Asset Id. If $id is not set, current user company Asset Id is returned.
	 *
	 * @param   int     $id        User id for getting company asset
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return  integer  Company Asset Id
	 */
	public static function getUserCompanyAssetId($id = 0, $userType = 'redshopb')
	{
		$company = self::getUserCompany($id, $userType);

		if ($company)
		{
			return $company->asset_id;
		}

		return 0;
	}

	/**
	 * Check State Company For User
	 *
	 * @param   int  $userId  Current user id
	 *
	 * @return  void
	 */
	public static function checkStateCompanyForUser($userId = null)
	{
		$user = Factory::getUser($userId);
		$app  = Factory::getApplication();

		// If Super Admin, returns true
		if ($user->authorise('core.admin'))
		{
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('parent.id')
			->from($db->qn('#__redshopb_company', 'parent'))
			->where($db->qn('parent.deleted') . ' = 0')
			->leftJoin(
				$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
			);

		if (Factory::getUser()->id != $userId)
		{
			$query->innerJoin('#__redshopb_user_multi_company AS umc ON umc.company_id = node.id AND ' . $db->qn('umc.main') . ' = 1');
		}
		else
		{
			$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();
			$query->innerJoin(
				'#__redshopb_user_multi_company AS umc ON umc.company_id = node.id AND ' . $db->qn('umc.company_id') . ' = ' . $selectedCompanyId
			);
		}

		$query->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON umc.user_id = ru.id')
			->where('parent.state = 0')
			->where('parent.level > 0')
			->where('ru.joomla_user_id = ' . (int) $user->get('id'));
		$db->setQuery($query);

		if ($db->loadResult())
		{
			if ($user->get('id'))
			{
				$app->logout($user->get('id'), array('clientid' => 0));
			}

			$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=b2buserregister', false));
		}
	}

	/**
	 * Get user department Id. If $id is not set, current user department Id is returned.
	 *
	 * @param   int      $id           User id for getting department
	 * @param   string   $userType     User type : redshopb/joomla.
	 * @param   boolean  $hideDeleted  Hide deleted departments.
	 *
	 * @return  integer  Department Id
	 */
	public static function getUserDepartmentId($id = 0, $userType = 'redshopb', $hideDeleted = true)
	{
		$department = self::getUserDepartment($id, $userType, $hideDeleted);

		if ($department)
		{
			return $department->id;
		}

		return 0;
	}

	/**
	 * Get user department Asset Id. If $id is not set, current user department Asset Id is returned.
	 *
	 * @param   int     $id        User id for getting department
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return  integer  Department Id
	 */
	public static function getUserDepartmentAssetId($id = 0, $userType = 'redshopb')
	{
		$department = self::getUserDepartment($id, $userType);

		if ($department)
		{
			return $department->asset_id;
		}

		return 0;
	}

	/**
	 * Get user role Id
	 *
	 * @param   int     $id        User id for getting role (redshopb user id)
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return  string  Role
	 */
	public static function getUserRoleId($id = 0, $userType = 'redshopb')
	{
		if ($id != 0 && $userType == 'redshopb')
		{
			$id = self::getJoomlaId($id);
		}
		elseif ($id == 0)
		{
			$id = Factory::getUser()->id;
		}

		if ($id)
		{
			if (!isset(self::$usersRoleIds[(int) $id]))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select(Array('r.id'))
					->from($db->qn('#__users', 'u'))
					->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON u.id = ug.user_id')
					->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
					->where('u.id = ' . (int) $id);

				$db->setQuery($query);
				self::$usersRoleIds[(int) $id] = $db->loadResult();
			}

			return self::$usersRoleIds[$id];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Get user role type, explicitly "admin", "hod", "employee",  "sales"
	 *
	 * @param   int     $id        User id for getting role (redshopb user id)
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return  string  Role
	 */
	public static function getUserRole($id = 0, $userType = 'redshopb')
	{
		if (array_key_exists((int) $id, self::$usersRole))
		{
			return self::$usersRole[$id];
		}

		if ($id && $userType == 'redshopb')
		{
			$id = self::getJoomlaId($id);
		}

		if (!$id)
		{
			$id = Factory::getUser()->id;
		}

		if ($id)
		{
			if (self::isRoot($id, 'joomla'))
			{
				self::$usersRole[(int) $id] = 'superadmin';
			}
			else
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select(Array('rt.type'))
					->from($db->qn('#__users', 'u'))
					->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON u.id = ug.user_id')
					->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
					->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
					->where('u.id = ' . (int) $id);

				self::$usersRole[(int) $id] = $db->setQuery($query, 0, 1)
					->loadResult();
			}
		}
		else
		{
			self::$usersRole[$id] = 'undefined';
		}

		return self::$usersRole[$id];
	}

	/**
	 * Determines if a user is an admin of a given company (or any company)
	 *
	 * @param   int  $userId     User id (redshopb id)
	 * @param   int  $companyId  (optional) Company Id
	 *
	 * @return  string  Role
	 */
	public static function isCompanyAdmin($userId, $companyId = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(Array('u.id'))
			->from($db->qn('#__redshopb_user', 'u'))
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON u.joomla_user_id = ug.user_id')
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
			->innerJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = u.id')
			->where('u.id = ' . $userId)
			->where('rt.type = ' . $db->quote('admin'));

		if ($companyId)
		{
			$query->where('umc.company_id = ' . $companyId);
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			return true;
		}

		return false;
	}

	/**
	 * Determines if a user is an HOD of a given department (or any department)
	 *
	 * @param   int  $userId        User id (redshopb id)
	 * @param   int  $departmentId  (optional) Department Id
	 *
	 * @return  string  Role
	 */
	public static function isHOD($userId, $departmentId = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(Array('u.id'))
			->from($db->qn('#__redshopb_user', 'u'))
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON u.joomla_user_id = ug.user_id')
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
			->where('u.id = ' . $userId)
			->where('rt.type = ' . $db->quote('hod'));

		if ($departmentId)
		{
			$query->where('u.department_id = ' . $departmentId);
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			return true;
		}

		return false;
	}

	/**
	 * Determines if a user is an Employee of a given company and/or department
	 *
	 * @param   int  $userId        User id (redshopb id)
	 * @param   int  $companyId     (optional) Department Id
	 * @param   int  $departmentId  (optional) Department Id
	 *
	 * @return  string  Role
	 */
	public static function isEmployee($userId, $companyId = 0, $departmentId = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(Array('u.id'))
			->from($db->qn('#__redshopb_user', 'u'))
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON u.joomla_user_id = ug.user_id')
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
			->innerJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = u.id')
			->where('u.id = ' . $userId)
			->where('rt.type = ' . $db->quote('employee'));

		if ($companyId)
		{
			$query->where('umc.company_id = ' . $companyId);
		}

		if ($departmentId)
		{
			$query->where('u.department_id = ' . $departmentId);
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			return true;
		}

		return false;
	}

	/**
	 * Get user level. If $id is not set, current user level is returned.
	 *
	 * @param   int     $id        User id for getting department
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return  integer  Level
	 */
	public static function getUserLevel($id = 0, $userType = 'redshopb')
	{
		$company = self::getUserCompany($id, $userType);

		if ($company)
		{
			return $company->level;
		}

		return 0;
	}

	/**
	 * Returning redshopb user id for given Joomla user id.
	 *
	 * @param   int  $id  Joomla user id.
	 *
	 * @return integer 0 on failure, redshopb user id on success.
	 */
	public static function getUserRSid($id = 0)
	{
		$user     = self::getUser($id, 'joomla');
		$userRSId = 0;

		if ($user)
		{
			$userRSId = $user->id;
		}

		return $userRSId;
	}

	/**
	 * Get user shopping currency. If user is an employee, he will use points.
	 *
	 * @param   int      $id        User id.
	 * @param   string   $userType  User type : redshopb/joomla.
	 * @param   boolean  $alpha3    Currency will be given as alpha3 if true. Id otherwise.
	 *
	 * @return  string  Currency alpha3
	 */
	public static function getCurrency($id = 0, $userType = 'redshopb', $alpha3 = true)
	{
		/**
		 * @ToDo: Setting default currency to DKK for now, change following by client request
		 * We can also change default currency value(DKK) to redshopb settings default currency value.
		 */
		$currency = $alpha3 ? 'DKK' : 38;

		if ($id == 0 || $userType == 'joomla')
		{
			if ($userType == 'joomla' && $id > 0)
			{
				$id = self::getUserRSid($id);
			}
			else
			{
				$id = self::getUserRSid(Factory::getUser()->id);
			}
		}

		if (self::isEmployee($id))
		{
			$currency = $alpha3 ? 'PTS' : 159;
		}

		return $currency;
	}

	/**
	 * Get user points.
	 *
	 * @param   int     $id        User id.
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return integer Points amount.
	 */
	public static function getPoints($id = 0, $userType = 'redshopb')
	{
		$walletId = RedshopbHelperWallet::getUserWallet($id, $userType)->id;
		$points   = RedshopbHelperWallet::getMoneyAmount($walletId, 159);

		return $points;
	}

	/**
	 * Functions for employee purchases. For shopping, employees use points instead of real currency.
	 *
	 * @param   int     $id              User id (redshopb user)
	 * @param   float   $price           Order total price.
	 * @param   int     $currency        Currency id.
	 * @param   object  $shopperCompany  Shopper company (cannot be null)
	 * @param   bool    $checkFunds      Does not do the actual operation, just fund checking
	 *
	 * @return boolean True on success, false on purchase failure.
	 */
	public static function employeePurchase($id = 0, $price = 0.0, $currency = 159, $shopperCompany = null, $checkFunds = false)
	{
		// Allow user to shop if price is 0, wallet status update is not needed
		if ((float) $price == 0.0)
		{
			return true;
		}

		if ($id > 0)
		{
			$wallet = RedshopbHelperWallet::getUserWallet($id);

			if (is_null($wallet))
			{
				$amount = 0;
			}
			else
			{
				$walletId = $wallet->id;
				$amount   = RedshopbHelperWallet::getMoneyAmount($walletId, $currency);
			}

			if (is_array($amount))
			{
				$newAmount = (float) $amount->amount - $price;
			}
			else
			{
				$newAmount = $amount - $price;
			}

			// Check if credit for given money exists and amount is not negative
			if ($newAmount >= 0
				|| RedshopbHelperACL::getPermission('negativewallet', 'user', Array(), false, $shopperCompany->asset_id, 'redshopb'))
			{
				// It does the actual fund debit in case it's not just checking funds
				if (!$checkFunds)
				{
					/** @var RedshopbModelUser $model */
					$model = RModelAdmin::getInstance('User', 'RedshopbModel');
					$model->saveWallet($id, $currency, $newAmount);
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Get joomla user id for given redSHOPB2B user id.
	 *
	 * @param   int  $id  redSHOPB2B user id.
	 *
	 * @return integer Joomla user id.
	 */
	public static function getJoomlaId($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('joomla_user_id'))
			->from($db->qn('#__redshopb_user'))
			->where($db->qn('id') . ' = ' . (int) $id);
		$db->setQuery($query);
		$jid = $db->loadResult();

		if ($jid)
		{
			return $jid;
		}

		return 0;
	}

	/**
	 * Check if user has Joomla root rights.
	 *
	 * @param   int     $id        User id.
	 * @param   string  $userType  User type : redshopb/joomla.
	 *
	 * @return boolean True if user is root, false otherwise.
	 */
	public static function isRoot($id = 0, $userType = 'redshopb')
	{
		if ($id > 0 && $userType == 'redshopb')
		{
			return Factory::getUser(self::getJoomlaId($id))->authorise('core.admin');
		}
		elseif ($id > 0 && $userType == 'joomla')
		{
			return Factory::getUser($id)->authorise('core.admin');
		}

		return Factory::getUser()->authorise('core.admin');
	}

	/**
	 * Get employees or an employee for rsbuserId/companyId/departmentId/rolesId combination.
	 * If companyId and departmentId are set to 0, all employees will be returned.
	 * We can also get employees for particular company or department by setting
	 * one of this to 0.
	 *
	 * @param   array  $ids           User ids for getting employees
	 * @param   int    $companyId     Id company
	 * @param   int    $departmentId  Id department
	 * @param   array  $roles         Employee roles for selecting employees
	 *
	 * @return mixed
	 */
	public static function getEmployees($ids = array(), $companyId = 0, $departmentId = 0, $roles = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'ru.*',	'u.name', 'ru.department_id', 'umc.company_id',
					$db->qn('c.name', 'company'), $db->qn('d.name', 'department'), 'rt.type', 'rt.name AS role'
				)
			)
			->from($db->qn('#__redshopb_user', 'ru'))
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
			->leftJoin($db->qn('#__users', 'u') . ' ON u.id = ru.joomla_user_id')
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = umc.company_id AND ' . $db->qn('c.deleted') . ' = 0')
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON d.id = ru.department_id')
			->leftJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ru.joomla_user_id = ug.user_id')
			->leftJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
			->leftJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
			->where('u.block = 0')
			->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1')
			->group('u.id');

		if ($departmentId)
		{
			$query->where('ru.department_id = ' . (int) $departmentId);
		}

		if ($companyId)
		{
			$query->where('umc.company_id = ' . (int) $companyId);
		}

		if (!empty($ids))
		{
			$query->where($db->qn('ru.id') . ' IN (' . implode(',', $ids) . ')');
		}

		if (!empty($roles))
		{
			$roless = array();

			foreach ($roles as $role)
			{
				$roless[] = $db->q($role);
			}

			$query->where($db->qn('rt.type') . ' IN (' . implode(',', $roless) . ')');
		}

		$db->setQuery($query);

		if (count($ids) == 1)
		{
			return $db->loadObject();
		}

		return $db->loadObjectList();
	}

	/**
	 * Return user name.
	 *
	 * @param   int     $id        User id.
	 * @param   string  $userType  User type.
	 *
	 * @return string User name.
	 */
	public static function getName($id = 0, $userType = 'redshopb')
	{
		$user = self::getUser($id, $userType);

		return $user->name;
	}

	/**
	 * Return user name2.
	 *
	 * @param   int     $id        User id.
	 * @param   string  $userType  User type.
	 *
	 * @return string User name2.
	 */
	public static function getName2($id = 0, $userType = 'redshopb')
	{
		$user = self::getUser($id, $userType);

		return $user->name2;
	}

	/**
	 * Get the redshopb user id from the user number.
	 *
	 * @param   string  $userNumber  The number of the user
	 *
	 * @return mixed The user ID or null.
	 */
	public static function getUserIdByUserNumber($userNumber)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->qn('#__redshopb_user'))
			->where($db->qn('employee_number') . ' = ' . $db->q($userNumber));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get an encrypted password with salt
	 *
	 * @param   string  $password       Password from database (password should be already encrypted)
	 * @param   string  $encryptionKey  Salt key used for encryption
	 *
	 * @return  string  New generated encrypted password
	 *
	 * @since   1.0
	 */
	public static function encryptCsvPassword($password, $encryptionKey)
	{
		$iv    = openssl_random_pseudo_bytes(openssl_cipher_iv_length("AES-256-CBC"));
		$crypt = openssl_encrypt($password, "AES-256-CBC", $encryptionKey, OPENSSL_RAW_DATA, $iv);

		return base64_encode($iv . $crypt);
	}

	/**
	 * Method to get an password from encryption with salt
	 *
	 * @param   string  $password       Password from CSV file that was previously encrypted with same salt key
	 * @param   string  $encryptionKey  Salt key used for encryption
	 *
	 * @return  string  Joomla Password
	 *
	 * @since   1.0
	 */
	public static function decryptCsvPassword($password, $encryptionKey)
	{
		$decodedPassword = base64_decode($password);

		$ivlen = openssl_cipher_iv_length("AES-256-CBC");
		$iv    = substr($decodedPassword, 0, $ivlen);
		$crypt = substr($decodedPassword, $ivlen);

		return openssl_decrypt($crypt, "AES-256-CBC", $encryptionKey, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * Method to get salespersons to select for a company (salespersons not associated to that company yet)
	 *
	 * @param   string  $companyId  Company ID to exclude salespersons from
	 *
	 * @return  array   Objects: id, name1, name2, email
	 *
	 * @since   1.0
	 */
	public static function getSalesPersonsAdd($companyId)
	{
		$db = Factory::getDbo();

		$subquery = $db->getQuery(true)
			->select(
				array(
					'cspx.user_id'
				)
			)
			->from($db->qn('#__redshopb_company_sales_person_xref', 'cspx'))
			->where('cspx.company_id = ' . $companyId);

		$query = $db->getQuery(true)
			->select(
				array(
					'u.id',
					'u.name1',
					'u.name2',
					'j.email',
					'IF (u.use_company_email = 0, j.email, ' . $db->q('') . ') AS email',
					'u.use_company_email',
					'u.send_email'
				)
			)
			->from($db->qn('#__redshopb_user', 'u'))
			->join('inner', $db->qn('#__users', 'j') . ' ON u.joomla_user_id = j.id')
			->join('inner', $db->qn('#__user_usergroup_map', 'um') . ' ON j.id = um.user_id')
			->join('inner', $db->qn('#__usergroups', 'g') . ' ON um.group_id = g.id')
			->join('inner', $db->qn('#__redshopb_role', 'r') . ' ON g.id = r.joomla_group_id')
			->join('inner', $db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
			->join('inner', $db->qn('#__redshopb_company', 'cs') . ' ON r.company_id = cs.id AND cs.deleted = 0')
			->join('inner', $db->qn('#__redshopb_company', 'c') . ' ON c.deleted = 0 AND c.lft > cs.lft AND c.rgt < cs.rgt AND c.level > cs.level')
			->where('rt.type = ' . $db->q('sales'))
			->where('u.id NOT IN (' . $subquery->__toString() . ')')
			->where('c.id = ' . (int) $companyId)
			->order(
				array(
					'u.name1',
					'u.name2'
				)
			);

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get companies where a user is set as sales person
	 *
	 * @param   string  $userId  User ID to search companies where it's set as sales person
	 *
	 * @return  array   Companies' IDs
	 *
	 * @since   1.0
	 */
	public static function getCompaniesforSalesPerson($userId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'c.id',
				)
			)
			->from($db->qn('#__redshopb_company', 'c'))
			->join('inner', $db->qn('#__redshopb_company_sales_person_xref', 'cspx') . ' ON cspx.company_id = c.id')
			->join('inner', $db->qn('#__redshopb_user', 'u') . ' ON cspx.user_id = u.id')
			->where('u.id = ' . $userId);

		$db->setQuery($query);

		return $db->loadColumn(0);
	}

	/**
	 * Method to get Joomla Roles where a user is set as sales person
	 *
	 * @param   string  $userId  User ID to search companies where it's set as sales person
	 *
	 * @return  array   Roles' IDs
	 *
	 * @since   1.0
	 */
	public static function getRolesforSalesPerson($userId)
	{
		if ($userId)
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array(
						'joomla_group_id',
					)
				)
				->from($db->qn('#__redshopb_usergroup_sales_person_xref'))
				->where('user_id = ' . $userId);

			$db->setQuery($query);

			return $db->loadColumn(0);
		}

		return array();
	}

	/**
	 * Method for check if user is from Main Company
	 *
	 * @param   int     $customerId    ID of user
	 * @param   string  $customerType  Type of customer
	 *
	 * @return  boolean                 True if user belong to Main Company. False otherwise.
	 */
	public static function isFromMainCompany($customerId = 0, $customerType = '')
	{
		$app          = Factory::getApplication();
		$customerId   = (!$customerId) ? $app->getUserState('shop.customer_id', 0) : (int) $customerId;
		$customerType = (!$customerType) ? $app->getUserState('shop.customer_type', '') : (string) $customerType;

		if (!$customerId || empty($customerType))
		{
			$company = self::getUserCompany();
		}
		else
		{
			$company = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		}

		return ($company && $company->type == 'main');
	}

	/**
	 * Function for getting just the ids of a user's multicompanies
	 *
	 * @param   int  $userId  id of user.
	 *
	 * @return  array  Array of company ids
	 */
	public static function getUserCompanies($userId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('company_id'))
			->from($db->qn('#__redshopb_user_multi_company'))
			->where($db->qn('user_id') . ' = ' . $db->q($userId));

		$companies = (array) $db->setQuery($query)->loadColumn();

		return $companies;
	}
}
