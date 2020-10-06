<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
/**
 * A Company helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperCompany
{
	/**
	 * Static array for storing child companies per company.
	 *
	 * @var array
	 */
	public static $childCompanies = array();

	/**
	 * Static array for storing company departments per company.
	 *
	 * @var array
	 */
	public static $companyDepartments = array();

	/**
	 * Static array for storing status display or not retail prices per company.
	 *
	 * @var array
	 */
	private static $retailPriceStatus = array();

	/**
	 * Get the parent of the given company.
	 *
	 * @param   integer  $companyId    The company id.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return  integer  The parent company or null if no parent.
	 *
	 * @deprecated  1.7  Use RedshopbEntityCompany::getInstance($companyId)->getParent()
	 */
	public static function getParent($companyId, $hideDeleted = true)
	{
		$parent = RedshopbEntityCompany::getInstance($companyId)->getParent();

		return $parent->isLoaded() ? $parent : null;
	}

	/**
	 * Get the name of the company
	 *
	 * @param   integer  $companyId    The company id.
	 * @param   boolean  $webSafe      Use URL safe name.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return  mixed  The company name or null.
	 *
	 * @deprecated  1.7  Use RedshopbEntityCompany::getInstance($companyId)->get('name')
	 */
	public static function getName($companyId, $webSafe = false, $hideDeleted = true)
	{
		$company = RedshopbEntityCompany::load($companyId);

		if (!$company->isLoaded())
		{
			return null;
		}

		return $webSafe ? $company->getWebSafeName() : $company->name;
	}

	/**
	 * Get the name2 of the company
	 *
	 * @param   integer  $companyId    The company id.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return  mixed  The company name2 or empty string.
	 */
	public static function getName2($companyId, $hideDeleted = true)
	{
		return RedshopbEntityCompany::load($companyId)->name2;
	}

	/**
	 * Get the image folder path for the company
	 *
	 * @param   integer  $companyId  The company id.
	 * @param   bool     $relative   set true to return path relative to images
	 *
	 * @return  string  The path
	 *
	 * @deprecated  1.7  Use RedshopbEntityCompany::getInstance($companyId)->getImageFolder($relative)
	 */
	public static function getImageFolder($companyId, $relative = false)
	{
		return RedshopbEntityCompany::getInstance($companyId)->getImageFolder($relative);
	}

	/**
	 * Get company info.
	 *
	 * @param   int      $id           Company id
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return object Company object
	 */
	public static function getCompanyById($id, $hideDeleted = true)
	{
		if ($id == 0)
		{
			return null;
		}

		static $companies = array();
		$key              = $id . '_' . (int) $hideDeleted;

		if (array_key_exists($key, $companies))
		{
			return $companies[$key];
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('comp.id', 'id'),
					$db->qn('comp.parent_id', 'parent'),
					$db->qn('comp.level', 'level'),
					$db->qn('comp.name', 'name'),
					$db->qn('comp.name2', 'name2'),
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
					$db->qn('comp.freight_product_id', 'freight_product_id'),
					$db->qn('comp.requisition', 'requisition'),
					$db->qn('comp.customer_number', 'number'),
					$db->qn('comp.send_mail_on_order', 'send_mail'),
					$db->qn('comp.show_stock_as', 'show_stock_as'),
					$db->qn('comp.show_retail_price'),
					$db->qn('comp.image', 'image'),
					$db->qn('comp.state', 'state'),
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
					$db->qn('comp.show_price', 'show_price'),
					$db->qn('comp.stockroom_verification', 'stockroom_verification')
				)
			)
			->from($db->qn('#__redshopb_company', 'comp'))
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = comp.address_id')
			->leftJoin($db->qn('#__redshopb_country', 'cont') . ' ON cont.id = a.country_id')
			->leftJoin($db->qn('#__redshopb_state', 'state') . ' ON state.id = a.state_id')
			->where('comp.id = ' . (int) $id);

		if ($hideDeleted)
		{
			$query->where($db->qn('comp.deleted') . ' = 0');
		}

		$companies[$key] = $db->setQuery($query, 0, 1)
			->loadObject();

		return $companies[$key];
	}

	/**
	 * Get company users count.
	 *
	 * @param   array    $companies    Company ids.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return integer Users count.
	 */
	public static function getUsersCount($companies, $hideDeleted = true)
	{
		$db = Factory::getDbo();

		if ($companies)
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(ru.id)')
				->from($db->qn('#__redshopb_user', 'ru'))
				->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
				->where('umc.company_id IN (' . implode(', ', $companies) . ')');

			if ($hideDeleted)
			{
				$query->where($db->qn('ru.deleted') . ' = 0');
			}

			$db->setQuery($query);

			return $db->loadResult();
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Returns companies(customers) ids for particular layouts.
	 *
	 * @param   array    $layoutsIds   Layout ids.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return array|null Companies ids.
	 */
	public static function getCompaniesByLayoutsIds($layoutsIds, $hideDeleted = true)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->qn('#__redshopb_company'))
			->where('layout_id IN (' . implode(',', $layoutsIds) . ')');

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0');
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Returns all company parent ids
	 *
	 * @param   int      $companyId    Company id.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return array
	 *
	 * @deprecated  1.7  Use RedshopbEntityCompany::getInstance($companyId)->getTree(true, false);
	 */
	public static function getParentsIds($companyId, $hideDeleted = true)
	{
		return RedshopbEntityCompany::getInstance($companyId)->getTree(true, false);
	}

	/**
	 * Get number of child companies for given company.
	 *
	 * @param   array  $companies       Parent company ids.
	 * @param   bool   $hideDeleted     Hide deleted companies.
	 * @param   bool   $getDeeperCount  Get count all deeper companies
	 *
	 * @return array Number of companies per company.
	 */
	public static function getSubCompaniesCount($companies = array(), $hideDeleted = true, $getDeeperCount = true)
	{
		$companies = ArrayHelper::toInteger($companies, array());

		if (empty($companies) || !is_array($companies))
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array (
					$db->qn('c_parent.id', 'key'),
					'COUNT(' . $db->qn('c_node.id') . ') AS counter'
				)
			)
			->from($db->qn('#__redshopb_company', 'c_node'));

		if ($hideDeleted)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_company', 'c_parent') . ' ON c_node.lft > c_parent.lft AND c_node.rgt < c_parent.rgt AND ' .
				$db->qn('c_parent.deleted') . ' = 0'
			)
				->where($db->qn('c_node.deleted') . ' = 0');
		}
		else
		{
			$query->leftJoin(
				$db->qn('#__redshopb_company', 'c_parent') . ' ON c_node.lft > c_parent.lft AND c_node.rgt < c_parent.rgt'
			);
		}

		$query->where('c_parent.id IN (' . implode(',', $companies) . ')')
			->where('c_node.level > 0')
			->group($db->qn('c_parent.id'));

		if (!$getDeeperCount)
		{
			$query->where('c_node.level = c_parent.level + 1');
		}

		$db->setQuery($query);

		return $db->loadAssocList('key', 'counter');
	}

	/**
	 * Get number of departments for given companies.
	 *
	 * @param   array  $companies       Parent companies ids.
	 * @param   bool   $hideDeleted     Hide deleted companies.
	 * @param   bool   $getDeeperCount  Get count all deeper companies
	 *
	 * @return array Number of departments per companies.
	 */
	public static function getDepartmentsCount($companies, $hideDeleted = true, $getDeeperCount = true)
	{
		$companies = ArrayHelper::toInteger($companies, array());

		if (empty($companies) || !is_array($companies))
		{
			return array();
		}

		$allCompanies   = $companies;
		$childCompanies = array();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('c_parent.id', 'parent'),
					$db->qn('c_node.id', 'child')
				)
			)
			->from($db->qn('#__redshopb_company', 'c_node'));

		if ($hideDeleted)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_company', 'c_parent') . ' ON c_node.lft > c_parent.lft AND c_node.rgt < c_parent.rgt AND ' .
				$db->qn('c_parent.deleted') . ' = 0'
			)
				->where($db->qn('c_node.deleted') . ' = 0');
		}
		else
		{
			$query->leftJoin($db->qn('#__redshopb_company', 'c_parent') . ' ON c_node.lft > c_parent.lft AND c_node.rgt < c_parent.rgt');
		}

		$query->where('c_parent.id IN (' . implode(',', $companies) . ')')
			->where('c_node.level > 0');

		$db->setQuery($query);

		$resultCompanies = $db->loadObjectList();

		foreach ($resultCompanies as $company)
		{
			if (!isset($childCompanies[$company->parent]))
			{
				if (is_null($company->child))
				{
					$childCompanies[$company->parent] = array();
				}
				else
				{
					$childCompanies[$company->parent] = array($company->child);
				}
			}
			else
			{
				$childCompanies[$company->parent][] = $company->child;
			}
		}

		foreach ($childCompanies as $chCompanies)
		{
			$chCompanies  = ArrayHelper::toInteger($chCompanies);
			$allCompanies = array_merge($allCompanies, $chCompanies);
		}

		$query->clear()
			->select(
				array (
					$db->qn('d.company_id', 'key'),
					'COUNT(' . $db->qn('d.id') . ') AS counter'
				)
			)
			->from($db->qn('#__redshopb_department', 'd'))
			->where($db->qn('d.company_id') . ' IN (' . implode(',', $allCompanies) . ')');

		if ($hideDeleted)
		{
			$query->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');
		}

		$query->group($db->qn('d.company_id'));

		$db->setQuery($query);

		$resultList = $db->loadAssocList('key', 'counter');
		$final      = array();

		foreach ($resultList as $key => $counter)
		{
			if (in_array((int) $key, $companies))
			{
				if (!isset($final[(int) $key]))
				{
					$final[(int) $key] = (int) $counter;
				}
				else
				{
					$final[(int) $key] += (int) $counter;
				}
			}

			if ($getDeeperCount)
			{
				foreach ($childCompanies as $cKey => $cKeyCompanies)
				{
					if (in_array((int) $key, $cKeyCompanies))
					{
						if (!isset($final[(int) $cKey]))
						{
							$final[(int) $cKey] = (int) $counter;
						}
						else
						{
							$final[(int) $cKey] += (int) $counter;
						}
					}
				}
			}
		}

		return $final;
	}

	/**
	 * Get number of employees for given companies.
	 *
	 * @param   array  $companies       Parent company ids.
	 * @param   bool   $hideDeleted     Hide deleted companies.
	 * @param   bool   $getDeeperCount  Get count all deeper companies
	 *
	 * @return array Number of employees per companies.
	 */
	public static function getEmployeesCount($companies, $hideDeleted = true, $getDeeperCount = true)
	{
		$companies = ArrayHelper::toInteger($companies, array());

		if (empty($companies) || !is_array($companies))
		{
			return array();
		}

		$allCompanies   = $companies;
		$childCompanies = array();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('c_parent.id', 'parent'),
					$db->qn('c_node.id', 'child')
				)
			)
			->from($db->qn('#__redshopb_company', 'c_node'));

		if ($hideDeleted)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_company', 'c_parent') . ' ON c_node.lft > c_parent.lft AND c_node.rgt < c_parent.rgt AND ' .
				$db->qn('c_parent.deleted') . ' = 0'
			)
				->where($db->qn('c_node.deleted') . ' = 0');
		}
		else
		{
			$query->leftJoin(
				$db->qn('#__redshopb_company', 'c_parent') . ' ON c_node.lft > c_parent.lft AND c_node.rgt < c_parent.rgt'
			);
		}

		$query->where('c_parent.id IN (' . implode(',', $companies) . ')')
			->where('c_node.level > 0');

		$db->setQuery($query);

		$resultCompanies = $db->loadObjectList();

		foreach ($resultCompanies as $company)
		{
			if (!isset($childCompanies[$company->parent]))
			{
				if (is_null($company->child))
				{
					$childCompanies[$company->parent] = array();
				}
				else
				{
					$childCompanies[$company->parent] = array($company->child);
				}
			}
			else
			{
				$childCompanies[$company->parent][] = $company->child;
			}
		}

		foreach ($childCompanies as $chCompanies)
		{
			$chCompanies  = ArrayHelper::toInteger($chCompanies);
			$allCompanies = array_merge($allCompanies, $chCompanies);
		}

		$query->clear()
			->select(
				array (
					$db->qn('umc.company_id', 'key'),
					'COUNT(' . $db->qn('ru.id') . ') AS counter'
				)
			)
			->from($db->qn('#__redshopb_user', 'ru'))
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
			->where($db->qn('umc.company_id') . ' IN (' . implode(',', $allCompanies) . ')')
			->group($db->qn('umc.company_id'));

		if (!$getDeeperCount)
		{
			$query->where('ru.department_id IS NULL');
		}

		$db->setQuery($query);

		$resultList = $db->loadAssocList('key', 'counter');
		$final      = array();

		foreach ($resultList as $key => $counter)
		{
			if (in_array((int) $key, $companies))
			{
				if (!isset($final[(int) $key]))
				{
					$final[(int) $key] = (int) $counter;
				}
				else
				{
					$final[(int) $key] += (int) $counter;
				}
			}

			if ($getDeeperCount)
			{
				foreach ($childCompanies as $cKey => $cKeyCompanies)
				{
					if (in_array((int) $key, $cKeyCompanies))
					{
						if (!isset($final[(int) $cKey]))
						{
							$final[(int) $cKey] = (int) $counter;
						}
						else
						{
							$final[(int) $cKey] += (int) $counter;
						}
					}
				}
			}
		}

		return $final;
	}

	/**
	 * Get employees list.
	 *
	 * @param   int  $companyId  Company id.
	 *
	 * @return array List of employees under given company.
	 */
	public static function getEmployees($companyId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT ' . $db->qn('ru.id'))
			->from($db->qn('#__redshopb_user', 'ru'))
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
			->where('umc.company_id = ' . (int) $companyId);
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Return company departments.
	 *
	 * @param   int      $companyId            Company id.
	 * @param   boolean  $getChildDepartments  Get company child departments.
	 * @param   boolean  $hideDeleted          Hide deleted companies.
	 *
	 * @return mixed
	 *
	 * @deprecated  1.7  Use array_keys() on RedshopbEntityCompany::getInstance($companyId)->getDescendantDepartments() | ->getDepartments()
	 */
	public static function getCompanyDepartments($companyId, $getChildDepartments = false, $hideDeleted = true)
	{
		$company = RedshopbEntityCompany::getInstance($companyId);

		$departments = $getChildDepartments ? $company->getDescendantDepartments() : $company->getDepartments();

		return $departments->ids();
	}

	/**
	 * Get child companies for given company.
	 *
	 * @param   int      $companyId    Company id.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return mixed
	 *
	 * @deprecated  1.7  Use RedshopbEntityCompany::getInstance($companyId)->getChildrenIds();
	 */
	public static function getChildCompanies($companyId = 0, $hideDeleted = true)
	{
		if (!isset(self::$childCompanies[$companyId]))
		{
			self::$childCompanies[$companyId] = RedshopbEntityCompany::getInstance($companyId)->getChildrenIds();
		}

		return self::$childCompanies[$companyId];
	}

	/**
	 * Get lowest level customer company for given company id.
	 * If company id is not given, F. Engel is returned.
	 *
	 * @param   int      $companyId    Company id.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return object Customer type company with lowest level.
	 */
	public static function getCustomerCompanyById($companyId = 0, $hideDeleted = true)
	{
		$company = self::getCompanyById($companyId);

		if (!is_null($company) && $company->type != 'end_customer')
		{
			return $company;
		}
		else
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			if ($companyId)
			{
				$query->select(
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
						// @TODO: frontend DB fix - check in RSBTB-1960 *bump*
						$db->qn('comp.freight_product_id', 'productId'),
						$db->qn('comp.requisition', 'requisition'),
						$db->qn('comp.customer_number', 'number'),
						// @TODO: frontend DB fix - check in RSBTB-1960 *bump*
						// $db->qn('comp.size_language'),
						$db->qn('comp.send_mail_on_order', 'send_mail'),
						$db->qn('comp.show_stock_as', 'show_stock_as'),
						$db->qn('comp.image', 'image'),
						$db->qn('comp.stockroom_verification', 'stockroom_verification'),
						$db->qn('a.name', 'addressName'),
						$db->qn('a.id', 'addressId'),
						$db->qn('a.address', 'address'),
						$db->qn('a.address2', 'address2'),
						$db->qn('a.city', 'city'),
						$db->qn('a.zip', 'zip'),
						$db->qn('cont.name', 'country'),
						$db->qn('state.name', 'state_name')
					)
				)
					->from($db->qn('#__redshopb_company', 'comp'))
					->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = comp.address_id')
					->leftJoin($db->qn('#__redshopb_country', 'cont') . ' ON cont.id = a.country_id')
					->leftJoin($db->qn('#__redshopb_state', 'state') . ' ON state.id = a.state_id');

				if ($hideDeleted)
				{
					$query->leftJoin(
						$db->qn('#__redshopb_company', 'comp2') . ' ON comp.lft < comp2.lft AND comp.rgt > comp2.rgt AND ' .
						$db->qn('comp2.deleted') . ' = 0'
					)
						->where($db->qn('comp.deleted') . ' = 0');
				}
				else
				{
					$query->leftJoin($db->qn('#__redshopb_company', 'comp2') . ' ON comp.lft < comp2.lft AND comp.rgt > comp2.rgt');
				}

				$query->where('comp.type != ' . $db->q('end_customer'))
					->where('comp2.id = ' . (int) $companyId)
					->order('comp.level DESC');
			}
			else
			{
				return self::getMain();
			}

			$db->setQuery($query);

			return $db->loadObject();
		}
	}

	/**
	 * Get company type customer for given shop customer.
	 *
	 * @param   int      $customerId    Customer id.
	 * @param   string   $customerType  Customer type.
	 * @param   boolean  $hideDeleted   Hide deleted companies.
	 *
	 * @return object Customer type company.
	 */
	public static function getCustomerCompanyByCustomer($customerId, $customerType, $hideDeleted = true)
	{
		$company = self::getCompanyByCustomer($customerId, $customerType, $hideDeleted);

		if (empty($company))
		{
			return self::getMain();
		}
		elseif (!empty($company) && $company->type != 'end_customer')
		{
			return $company;
		}

		return self::getCustomerCompanyById($company->id, $hideDeleted);
	}

	/**
	 * Get company type customer for given shop customer.
	 *
	 * @param   int      $customerId    Customer id.
	 * @param   string   $customerType  Customer type.
	 * @param   boolean  $hideDeleted   Hide deleted companies.
	 *
	 * @return integer Customer type company.
	 */
	public static function getCompanyIdByCustomer($customerId, $customerType, $hideDeleted = true)
	{
		switch ($customerType)
		{
			case 'employee':
				$companyId = RedshopbHelperUser::getUserCompanyId($customerId, 'redshopb', $hideDeleted);
				break;
			case 'department':
				$companyId = RedshopbHelperDepartment::getCompanyId($customerId, $hideDeleted);
				break;
			case 'company':
				$companyId = $customerId;
				break;
			default:
				$companyId = 0;
		}

		return $companyId;
	}

	/**
	 * Check Status Display Retail Price
	 *
	 * @param   int   $customerId    Customer id.
	 * @param   int   $customerType  Customer type.
	 * @param   bool  $hideDeleted   Hide deleted companies.
	 *
	 * @return mixed
	 */
	public static function checkStatusDisplayRetailPrice($customerId, $customerType, $hideDeleted = true)
	{
		$config             = RedshopbEntityConfig::getInstance();
		$displayRetailPrice = $config->getInt('show_retail_price', 1);

		if (!array_key_exists($customerId . '_' . $customerType, self::$retailPriceStatus))
		{
			$companyId = self::getCompanyIdByCustomer($customerId, $customerType, $hideDeleted);

			if ($companyId)
			{
				$company = self::getCompanyById($companyId, $hideDeleted);

				if (isset($company->show_retail_price) && $company->show_retail_price != -1)
				{
					$displayRetailPrice = $company->show_retail_price;
				}
			}

			self::$retailPriceStatus[$customerId . '_' . $customerType] = $displayRetailPrice;
		}

		return self::$retailPriceStatus[$customerId . '_' . $customerType];
	}

	/**
	 * Get company type customer for given shop customer.
	 *
	 * @param   int      $customerId    Customer id.
	 * @param   string   $customerType  Customer type.
	 * @param   boolean  $hideDeleted   Hide deleted companies.
	 *
	 * @return object Customer type company.
	 */
	public static function getCompanyByCustomer($customerId, $customerType, $hideDeleted = true)
	{
		$companyId = self::getCompanyIdByCustomer($customerId, $customerType, $hideDeleted);

		return self::getCompanyById($companyId, $hideDeleted);
	}

	/**
	 * Get vendor company of customer for given shop customer.
	 *
	 * @param   int      $customerId    Customer id.
	 * @param   string   $customerType  Customer type.
	 * @param   boolean  $hideDeleted   Hide deleted companies.
	 *
	 * @return object Customer type company.
	 */
	public static function getVendorCompanyByCustomer($customerId, $customerType, $hideDeleted = true)
	{
		return RedshopbEntityCompany::getInstance(self::getCompanyIdByCustomer($customerId, $customerType, $hideDeleted))
			->getVendor();
	}

	/**
	 * Clean company name from special characters
	 *
	 * @param   string  $name  Company name
	 *
	 * @return  string  cleaned company name
	 */
	public static function cleanName($name)
	{
		// Replaces all spaces with hyphens.
		$name = str_replace(' ', '-', $name);

		// Removes special chars.
		$name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);

		return $name;
	}

	/**
	 * Get main company. (Company with highest level)
	 *
	 * @return object Main company object.
	 */
	public static function getMain()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
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
				// @TODO: frontend DB fix - check in RSBTB-1960 *bump*
				$db->qn('comp.freight_product_id', 'productId'),
				$db->qn('comp.requisition', 'requisition'),
				$db->qn('comp.customer_number', 'number'),
				// @TODO: frontend DB fix - check in RSBTB-1960 *bump*
				// $db->qn('comp.size_language'),
				$db->qn('comp.send_mail_on_order', 'send_mail'),
				$db->qn('comp.show_stock_as', 'show_stock_as'),
				$db->qn('a.name', 'addressName'),
				$db->qn('a.id', 'addressId'),
				$db->qn('a.address', 'address'),
				$db->qn('a.address2', 'address2'),
				$db->qn('a.city', 'city'),
				$db->qn('a.zip', 'zip'),
				$db->qn('cont.name', 'country'),
				$db->qn('state.name', 'state_name')
			)
		)
			->from($db->qn('#__redshopb_company', 'comp'))
			->where($db->qn('comp.deleted') . ' = 0')
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = comp.address_id')
			->leftJoin($db->qn('#__redshopb_country', 'cont') . ' ON cont.id = a.country_id')
			->leftJoin($db->qn('#__redshopb_state', 'state') . ' ON state.id = a.state_id')
			->where($db->qn('comp.type') . ' = ' . $db->q('main'))
			->where($db->qn('comp.state') . ' = 1');

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Get the company Id from the customer number.
	 *
	 * @param   string   $customerNumber  Customer number of the company
	 * @param   boolean  $hideDeleted     Hide deleted companies.
	 *
	 * @return mixed The company ID or null.
	 */
	public static function getCompanyIdByCustomerNumber($customerNumber, $hideDeleted = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('customer_number') . ' = ' . $db->q($customerNumber));

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0');
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get the company object from the customer number.
	 *
	 * @param   string   $customerNumber  Customer number of the company
	 * @param   boolean  $hideDeleted     Hide deleted companies.
	 *
	 * @return mixed The company object or null.
	 */
	public static function getCompanyByCustomerNumber($customerNumber, $hideDeleted = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('customer_number') . ' = ' . $db->q($customerNumber));

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0');
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Checks if we are able to shop as company or a department
	 * for a given company. If false, we can shop only as employees
	 * under given company.
	 *
	 * @param   int      $companyId    Company id.
	 * @param   boolean  $hideDeleted  Hide deleted companies.
	 *
	 * @return boolean true/false
	 */
	public static function checkEmployeeMandatory($companyId, $hideDeleted = true)
	{
		$company = self::getCompanyById($companyId, $hideDeleted);

		if (((int) $company->mandatory))
		{
			$user      = Factory::getUser();
			$companyId = RedshopbHelperUser::getUserCompanyId($user->id, 'joomla', $hideDeleted);

			if ($company->id == $companyId)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Change each user's language according to the company language
	 *
	 * @param   int     $companyId  Company ID
	 * @param   string  $language   New language
	 *
	 * @return void
	 */
	public static function setUsersLanguage($companyId, $language)
	{
		$db         = Factory::getDbo();
		$loggedUser = Factory::getUser();

		$languages = LanguageHelper::getLanguages('lang_code');

		// If language exists use it, if not use default
		$paramLanguage = !empty($languages[$language]) ? $languages[$language]->lang_code : '';

		$query = $db->getQuery(true)
			->select($db->qn('u.id'))
			->from($db->qn('#__users', 'u'))
			->join('inner', $db->qn('#__redshopb_user', 'ru') . ' ON u.id = ru.joomla_user_id')
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
			->where($db->qn('umc.company_id') . ' = ' . (int) $companyId);
		$users = $db->setQuery($query)
			->loadObjectList();

		if ($users)
		{
			$userTable = Table::getInstance('User');

			foreach ($users as $user)
			{
				if ($loggedUser->id == $user->id)
				{
					$loggedUser->setParam('language', $paramLanguage);
					$loggedUser->save(true);
				}
				else
				{
					$userTable->id = null;
					$userTable->reset();

					$userTable->load($user->id);
					$params           = json_decode($userTable->params);
					$params->language = $paramLanguage;

					$userTable->save(array('params' => json_encode($params)));
				}
			}
		}
	}

	/**
	 * Get company discount group id.
	 *
	 * @param   int  $companyId  Company id.
	 *
	 * @return integer Discount group id.
	 */
	public static function getDiscountGroupId($companyId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('discount_group_id'))
			->from($db->qn('#__redshopb_customer_discount_group_xref'))
			->where($db->qn('customer_id') . ' = ' . (int) $companyId);

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Get company discount group name using discount group id.
	 *
	 * @param   int  $discountGroupId  Discount group id.
	 *
	 * @return string Discount group name.
	 */
	public static function getDiscountGroupName($discountGroupId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('name'))
			->from($db->qn('#__redshopb_customer_discount_group'))
			->where($db->qn('id') . ' = ' . (int) $discountGroupId);

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Get company price group id.
	 *
	 * @param   int  $companyId  Company id.
	 *
	 * @return  integer|array        Company price group id
	 *
	 * @deprecated  1.13.0  Use RedshopbEntityCompany::getInstance($companyId)->getPriceGroups()->ids() instead
	 */
	public static function getPriceGroupIds($companyId)
	{
		return RedshopbEntityCompany::getInstance($companyId)->getPriceGroups()->ids();
	}

	/**
	 * Get company discount group id.
	 *
	 * @param   int  $companyId  Company id.
	 *
	 * @return integer|array Company discount group id
	 */
	public static function getDiscountGroupIds($companyId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('dg.id'))
			->from($db->qn('#__redshopb_customer_discount_group_xref', 'dgx'))
			->join(
				'inner',
				$db->qn('#__redshopb_customer_discount_group', 'dg') . ' ON ' .
				$db->qn('dgx.discount_group_id') . ' = ' .
				$db->qn('dg.id')
			)
			->where($db->qn('dgx.customer_id') . ' = ' . (int) $companyId);

		$db->setQuery($query);

		return $db->loadColumn(0);
	}

	/**
	 * Get company price group name using price group id.
	 *
	 * @param   int  $priceGroupId  Price group id.
	 *
	 * @return string Price group name.
	 */
	public static function getPriceGroupName($priceGroupId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('name'))
			->from($db->qn('#__redshopb_customer_price_group'))
			->where($db->qn('id') . ' = ' . (int) $priceGroupId);

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method for get company which is marked as "B2C"
	 *
	 * @param   boolean  $fresh  Return fresh data
	 *
	 * @return   integer  ID of company
	 */
	public static function getCompanyB2C($fresh = false)
	{
		static $result = null;

		if (is_null($result) || $fresh)
		{
			$domain = Uri::getInstance()->getHost();
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__redshopb_company'))
				->where($db->qn('b2c') . ' = 1')
				->where($db->qn('state') . ' = 1')
				->where($db->qn('url') . ' = ' . $db->quote($domain))
				->where($db->qn('deleted') . ' = 0');
			$result = $db->setQuery($query)->loadResult();

			if (!$result)
			{
				$result = self::getMainCompanyB2C();
			}
		}

		return $result;
	}

	/**
	 * Method for get main B2C Company with url is NULL.
	 *
	 * @return   integer  ID of company
	 */
	public static function getMainCompanyB2C()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('b2c') . ' = 1')
			->where($db->qn('state') . ' = 1')
			->where('(' . $db->qn('url') . ' IS NULL OR ' . $db->qn('url') . ' = ' . $db->quote('') . ')')
			->where($db->qn('deleted') . ' = 0');

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Determines if a certain company is child of another one
	 *
	 * @param   int  $id        Parent company
	 * @param   int  $parentId  Child company
	 *
	 * @return   integer  ID of company
	 */
	public static function isChildOf($id, $parentId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('c.id'))
			->from($db->qn('#__redshopb_company', 'c'))
			->join(
				'inner',
				$db->qn('#__redshopb_company', 'cp') .
				' ON ' . $db->qn('cp.lft') . ' < ' . $db->qn('c.lft') .
				' AND ' . $db->qn('cp.rgt') . ' > ' . $db->qn('c.rgt') .
				' AND ' . $db->qn('cp.level') . ' < ' . $db->qn('c.level')
			)
			->where($db->qn('c.id') . ' = ' . (int) $id)
			->where($db->qn('cp.id') . ' = ' . (int) $parentId)
			->where($db->qn('c.deleted') . ' = 0')
			->where($db->qn('cp.deleted') . ' = 0');

		return $db->setQuery($query)->loadResult();
	}
}
