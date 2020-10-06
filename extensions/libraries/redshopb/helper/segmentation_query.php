<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Segmentation query helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.6.17
 */
final class RedshopbHelperSegmentation_Query
{
	/**
	 * Method for render an Segmentation Query Builder base on configuration
	 *
	 * @param   array   $config        Array of configuration
	 * @param   string  $jsCallback    Javascript function callback when generate Segmentation query.
	 * @param   string  $builderValue  Builder value for render Segmentation Query
	 *
	 * @return  string                 HTML string of Segmentation Query builder
	 */
	public static function render($config = array(), $jsCallback = '', $builderValue = '')
	{
		if (!is_array($config) || empty($config))
		{
			$config = self::getDefaultColumns();
		}

		$layoutData = array(
			'configuration' => $config,
			'jsCallback'    => $jsCallback,
			'builderValue'  => $builderValue
		);

		return RedshopbLayoutHelper::render('segmentation_query.builder', $layoutData);
	}

	/**
	 * Method for get default configuration for Segmentation Query
	 *
	 * @return  array    Array of default column and data
	 */
	public static function getDefaultColumns()
	{
		$return = array();

		// Prepare companies rule
		$return[] = self::getCompaniesRule();

		// Prepare departments rule
		$return[] = self::getDepartmentsRule();

		// Prepare role type rule
		$return[] = self::getRolesRule();

		// Prepare zipcode rule
		$return[] = self::getZipcodesRule();

		// Prepare city rule
		$return[] = self::getCitiesRule();

		// Prepare country rule
		$return[] = self::getCountriesRule();

		return $return;
	}

	/**
	 * Method for get companies rule
	 *
	 * @return  array  Data of companies rule
	 */
	protected static function getCompaniesRule()
	{
		$companies = array(
			'id'     => 'company',
			'label'  => Text::_('COM_REDSHOPB_COMPANY'),
			'type'   => 'integer',
			'input'  => 'select',
			'operators' => array('equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null'),
			'values' => array()
		);

		// Default load companies of current user.
		$companies['values'][] = Text::_('JOPTION_SELECT_COMPANY');

		$userCompanies = RedshopbHelperACL::listAvailableCompanies(Factory::getUser()->id, 'objectList');

		if (empty($userCompanies))
		{
			return $companies;
		}

		foreach ($userCompanies as $company)
		{
			$companies['values'][$company->id] = $company->name;
		}

		return $companies;
	}

	/**
	 * Method for get departments rule
	 *
	 * @return  array  Data of departments rule
	 */
	protected static function getDepartmentsRule()
	{
		$departments = array(
			'id'     => 'department',
			'label'  => Text::_('COM_REDSHOPB_DEPARTMENT'),
			'type'   => 'integer',
			'input'  => 'select',
			'operators' => array('equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null'),
			'values' => array()
		);

		// Default load companies of current user.
		$departments['values'][] = Text::_('JOPTION_SELECT_DEPARTMENT');
		$userDepartments         = RedshopbHelperACL::listAvailableDepartments(Factory::getUser()->id, 'objectList');

		if (empty($userDepartments))
		{
			return $departments;
		}

		foreach ($userDepartments as $department)
		{
			$departments['values'][$department->id] = $department->name;
		}

		return $departments;
	}

	/**
	 * Method for get roles rule
	 *
	 * @return  array  Data of roles rule
	 */
	protected static function getRolesRule()
	{
		$db = Factory::getDbo();

		$roles = array(
			'id'     => 'role',
			'label'  => Text::_('COM_REDSHOPB_ROLE_LABEL'),
			'type'   => 'integer',
			'input'  => 'select',
			'operators' => array('equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null'),
			'values' => array()
		);

		$roles['values'][] = Text::_('JOPTION_SELECT_ROLE');

		$query = $db->getQuery(true)
			->select($db->qn(array('id', 'name')))
			->from($db->qn('#__redshopb_role_type'))
			->where($db->qn('company_role') . ' = 0')
			->order($db->qn('name'));

		if (RedshopbHelperACL::getPermission('manage', 'company'))
		{
			if (!RedshopbHelperACL::getPermission('manage', 'mainwarehouse') && !RedshopbHelperACL::isSuperAdmin())
			{
				$query->where($db->qn('type') . ' NOT IN (' . $db->quote('sales') . ')');
			}
		}
		else
		{
			$query->where($db->qn('type') . ' NOT IN (' . $db->quote('admin') . ',' . $db->quote('sales') . ')');
		}

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if (empty($results))
		{
			return $roles;
		}

		foreach ($results as $role)
		{
			$roles['values'][$role->id] = $role->name;
		}

		return $roles;
	}

	/**
	 * Method for get zipcode rule
	 *
	 * @return  array  Data of zipcode rule
	 */
	protected static function getZipcodesRule()
	{
		$db                = Factory::getDbo();
		$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();

		$zipcodes = array(
			'id'     => 'zipcode',
			'label'  => Text::_('COM_REDSHOPB_ZIP_LABEL'),
			'type'   => 'string',
			'input'  => 'select',
			'operators' => array('equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null'),
			'values' => array()
		);

		// Default load companies of current user.
		$zipcodes['values'][] = Text::_('COM_REDSHOPB_SEGMENTATION_QUERY_SELECT_ZIPCODE');

		$select = 'CASE WHEN ' . $db->qn('u.address_id') . ' IS NULL THEN
				CASE WHEN ' . $db->qn('d.address_id') . ' IS NULL THEN
					CASE WHEN ' . $db->qn('c.address_id') . ' IS NULL THEN
						NULL
					ELSE
						(SELECT ' . $db->qn('zip') . ' FROM ' . $db->qn('#__redshopb_address')
						. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('c.address_id') . ')
					END
				ELSE
					(SELECT ' . $db->qn('zip') . ' FROM ' . $db->qn('#__redshopb_address')
					. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('d.address_id') . ')
				END
			ELSE
				' . $db->qn('a.zip') . '
			END AS ' . $db->qn('finalzipcode');

		$query = $db->getQuery(true)
			->select($select)
			->from($db->qn('#__redshopb_user', 'u'))
			->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = u.id AND umc.company_id = ' . $selectedCompanyId)
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('u.address_id') . ' = ' . $db->qn('a.id'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' . $db->qn('d.id'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('umc.company_id') . ' = ' . $db->qn('c.id'))
			->group($db->qn('finalzipcode'))
			->order($db->qn('finalzipcode'))
			->having($db->qn('finalzipcode') . ' IS NOT NULL')
			->having($db->qn('finalzipcode') . ' <> ' . $db->quote(''));

		$db->setQuery($query);

		$userZipcodes = $db->loadColumn();

		if (empty($userZipcodes))
		{
			return $zipcodes;
		}

		$zipcodes['values'] = array_merge($zipcodes['values'], $userZipcodes);

		return $zipcodes;
	}

	/**
	 * Method for get cities rule
	 *
	 * @return  array  Data of cities rule
	 */
	protected static function getCitiesRule()
	{
		$db                = Factory::getDbo();
		$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();

		$cities = array(
			'id'     => 'city',
			'label'  => Text::_('COM_REDSHOPB_CITY_LABEL'),
			'type'   => 'string',
			'input'  => 'select',
			'operators' => array('equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null'),
			'values' => array()
		);

		// Default load companies of current user.
		$cities['values'][] = Text::_('COM_REDSHOPB_SEGMENTATION_QUERY_SELECT_CITY');

		$select = 'CASE WHEN ' . $db->qn('u.address_id') . ' IS NULL THEN
				CASE WHEN ' . $db->qn('d.address_id') . ' IS NULL THEN
					CASE WHEN ' . $db->qn('c.address_id') . ' IS NULL THEN
						NULL
					ELSE
						(SELECT ' . $db->qn('city') . ' FROM ' . $db->qn('#__redshopb_address')
						. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('c.address_id') . ')
					END
				ELSE
					(SELECT ' . $db->qn('city') . ' FROM ' . $db->qn('#__redshopb_address')
					. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('d.address_id') . ')
				END
			ELSE
				' . $db->qn('a.city') . '
			END AS ' . $db->qn('finalcity');

		$query = $db->getQuery(true)
			->select($select)
			->from($db->qn('#__redshopb_user', 'u'))
			->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = u.id AND umc.company_id = ' . $selectedCompanyId)
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('u.address_id') . ' = ' . $db->qn('a.id'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' . $db->qn('d.id'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('umc.company_id') . ' = ' . $db->qn('c.id'))
			->group($db->qn('finalcity'))
			->order($db->qn('finalcity'))
			->having($db->qn('finalcity') . ' IS NOT NULL')
			->having($db->qn('finalcity') . ' <> ' . $db->quote(''));

		$db->setQuery($query);

		$userCities = $db->loadColumn();

		if (empty($userCities))
		{
			return $cities;
		}

		$cities['values'] = array_merge($cities['values'], $userCities);

		return $cities;
	}

	/**
	 * Method for get countries rule
	 *
	 * @return  array  Data of countries rule
	 */
	protected static function getCountriesRule()
	{
		// @TODO: Need to discuss with @tito about how-to get zip code
		$db                = Factory::getDbo();
		$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();

		$countries = array(
			'id'     => 'country',
			'label'  => Text::_('COM_REDSHOPB_COUNTRY_LABEL'),
			'type'   => 'integer',
			'input'  => 'select',
			'operators' => array('equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null'),
			'values' => array()
		);

		// Default load companies of current user.
		$countries['values'][] = Text::_('COM_REDSHOPB_SEGMENTATION_QUERY_SELECT_COUNTRY');

		$countryIdSql = 'CASE WHEN ' . $db->qn('u.address_id') . ' IS NULL THEN
				CASE WHEN ' . $db->qn('d.address_id') . ' IS NULL THEN
					CASE WHEN ' . $db->qn('c.address_id') . ' IS NOT NULL THEN
						(SELECT ' . $db->qn('country_id') . ' FROM ' . $db->qn('#__redshopb_address')
						. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('c.address_id') . ')
					END
				ELSE
					(SELECT ' . $db->qn('country_id') . ' FROM ' . $db->qn('#__redshopb_address')
					. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('d.address_id') . ')
				END
			ELSE
				' . $db->qn('a.country_id') . '
			END AS ' . $db->qn('finalcountry');

		$query = $db->getQuery(true)
			->select($countryIdSql)
			->from($db->qn('#__redshopb_user', 'u'))
			->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = u.id AND umc.company_id = ' . $selectedCompanyId)
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('u.address_id') . ' = ' . $db->qn('a.id'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' . $db->qn('d.id'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('umc.company_id') . ' = ' . $db->qn('c.id'))
			->group($db->qn('finalcountry'))
			->order($db->qn('finalcountry'))
			->having($db->qn('finalcountry') . ' IS NOT NULL')
			->having($db->qn('finalcountry') . ' <> ' . $db->quote(''));

		$db->setQuery($query);

		$userCountries = $db->loadColumn();

		if (empty($userCountries))
		{
			return $countries;
		}

		// Get country name base on country ids
		$query->clear()
			->select($db->qn(array('id', 'name')))
			->from($db->qn('#__redshopb_country'))
			->where($db->qn('id') . ' IN (' . implode(',', $userCountries) . ')');
		$db->setQuery($query);

		$countriesResult = $db->loadObjectList();

		foreach ($countriesResult as $country)
		{
			$countries['values'][$country->id] = $country->name;
		}

		return $countries;
	}
}
