<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Companies Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCompanies extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_companies';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'company_limit';

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
				'id', 'c.id',
				'name', 'c.name',
				'name2', 'c.name2',
				'state', 'c.state',
				'address', 'a.address',
				'address2', 'a.address2',
				'zip', 'a.zip',
				'city', 'a.city',
				'country', 'con.name',
				'customer_at', 'c.customer_at',
				'company_state', 'c.company_state',
				'parent_id', 'c.parent_id',
				'lft', 'c.lft',
				'customer_number', 'c.customer_number',
				'customer_at', 'starting_level',
				'ending_level'
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
		parent::populateState('c.lft', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__redshopb_company as c')
			// Filter ROOT company & deleted companies
			->where($db->qn('c.deleted') . ' = 0')
			->where($db->qn('c.level') . ' <> 0')

			// Select address infos
			->select(
				array(
					$db->qn('a.address'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.name', 'address_name1'),
					$db->qn('a.name2', 'address_name2'),
					$db->qn('a.address', 'address_line1'),
					$db->qn('a.address2', 'address_line2')
				)
			)
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('c.address_id') . ' = ' . $db->qn('a.id'))

			// Select the country name and code
			->select(
				array(
					$db->qn('con.name', 'country'),
					$db->qn('con.alpha2', 'country_code')
				)
			)
			->leftJoin($db->qn('#__redshopb_country', 'con') . ' ON ' . $db->qn('con.id') . ' = ' . $db->qn('a.country_id'))

			// Select the currency code
			->select(
				array(
					$db->qn('cu.alpha3', 'currency_code')
				)
			)
			->leftJoin($db->qn('#__redshopb_currency', 'cu') . ' ON ' . $db->qn('cu.id') . ' = ' . $db->qn('c.currency_id'))

			// Select the language code
			->select(
				array(
					$db->qn('l.lang_code', 'language_code')
				)
			)
			->leftJoin($db->qn('#__languages', 'l') . ' ON ' . $db->qn('l.lang_code') . ' = ' . $db->qn('c.site_language'))

			// Get the parent company name
			->select(
				$db->qn('c2.name', 'customer_at')
			)
			->leftJoin(
				$db->qn('#__redshopb_company', 'c2') .
				' ON ' . $db->qn('c2.id') . ' = ' . $db->qn('c.parent_id') .
				' AND ' . $db->qn('c2.level') . ' > 0 ' .
				' AND ' . $db->qn('c2.deleted') . ' = 0'
			)

			// Get the number of users
			->select('COUNT(' . $db->qn('u.id') . ') AS ' . $db->qn('users'))
			->leftJoin(
				$db->qn('#__redshopb_user_multi_company', 'umc') . ' ON ' . $db->qn('umc.company_id') . ' = ' . $db->qn('c.id')
			)
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('umc.user_id'))
			->group('c.id');

		// Filter by state.
		$state = $this->getState('filter.company_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('c.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('c.state') . ' = 1');
		}

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where($db->qn('c.id') . ' = ' . (int) $id);
		}

		$ids = $this->getState('company_ids');

		if (is_array($ids) && count($ids))
		{
			$query->where($db->qn('c.id') . ' IN (' . implode(', ', $ids) . ')');
		}

		$parentId = $this->getState('filter.parent_id', $this->getState('filter.parent'));

		if ($this->getState('list.ws', false))
		{
			// If it's called from a web service, transforms the filtered parent "1" to invalid and null or "0" to "1" to match the ws output
			if ($parentId == '1')
			{
				$query->where('1 = 0');
			}
			elseif ($parentId == 'null' || $parentId == '0')
			{
				$parentId = '1';
			}
		}

		if (!empty($parentId) && is_numeric($parentId))
		{
			$query->where($db->qn('c.parent_id') . ' = ' . (int) $parentId);
		}

		$customerNumber = $this->getState('filter.customer_number');

		if (!empty($customerNumber))
		{
			$query->where($db->qn('c.customer_number') . ' = ' . $db->q($customerNumber));
		}

		$zip = $this->getState('filter.zip');

		if (!empty($zip))
		{
			$query->where($db->qn('a.zip') . ' = ' . $db->q($zip));
		}

		$city = $this->getState('filter.city');

		if (!empty($city))
		{
			$query->where($db->qn('a.city') . ' = ' . $db->q($city));
		}

		$countryCode = $this->getState('filter.country_code');

		if (!empty($countryCode))
		{
			$query->where($db->qn('con.alpha2') . ' = ' . $db->q($countryCode));
		}

		$currencyCode = $this->getState('filter.currency_code');

		if (!empty($currencyCode))
		{
			$query->where($db->qn('cu.alpha3') . ' = ' . $db->q($currencyCode));
		}

		$languageCode = $this->getState('filter.language_code');

		if (!empty($languageCode))
		{
			$query->where($db->qn('l.lang_code') . ' = ' . $db->q($languageCode));
		}

		$b2c = $this->getState('filter.b2c');

		if ($b2c == '0' || $b2c == 'false')
		{
			$query->where($db->qn('c.b2c') . ' = 0');
		}
		elseif ($b2c == '1' || $b2c == 'true')
		{
			$query->where($db->qn('c.b2c') . ' = 1');
		}

		// Filter search
		$search = $this->getState('filter.search_companies', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				$db->qn('c.name') . ' LIKE ' . $search,
				$db->qn('c.name2') . ' LIKE ' . $search,
				$db->qn('c.customer_number') . ' LIKE ' . $search,
				$db->qn('a.name') . ' LIKE ' . $search,
				$db->qn('a.name2') . ' LIKE ' . $search,
				$db->qn('a.address') . ' LIKE ' . $search,
				$db->qn('a.address2') . ' LIKE ' . $search,
				$db->qn('a.zip') . ' LIKE ' . $search,
				$db->qn('a.city') . ' LIKE ' . $search,
				$db->qn('con.alpha2') . ' LIKE ' . $search,
				$db->qn('con.name') . ' LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Check for available companies for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user = Factory::getUser();
			$query->where('c.id IN (' . RedshopbHelperACL::listAvailableCompanies($user->id) . ')');
		}

		// Filter customer at.
		$customerAt = $this->getState('filter.customer_at');

		if (is_numeric($customerAt))
		{
			$query->leftJoin('#__redshopb_company as c3 ON c.lft BETWEEN c3.lft AND c3.rgt OR c3.lft BETWEEN c.lft AND c.rgt AND ' .
				$db->qn('c3.deleted') . ' = 0'
			)
				->where('c3.id = ' . (int) $customerAt);
		}

		// Filter: return only descendants of specific companies
		$ancestors = RedshopbHelperDatabase::filterInteger($this->getState('filter.ancestor'));

		if ($ancestors)
		{
			$query->innerJoin('#__redshopb_company as c4 ON c4.lft < c.lft AND c.rgt < c4.rgt');

			if (count($ancestors) == 1)
			{
				$query->where('c4.id = ' . $ancestors[0]);
			}
			else
			{
				$query->where('c4.id IN (' . implode(',', $ancestors) . ')');
			}
		}

		// Filter: return only ancestors of specific companies
		$descendants = RedshopbHelperDatabase::filterInteger($this->getState('filter.descendant'));

		if ($descendants)
		{
			$query->innerJoin('#__redshopb_company as c5 ON c.lft < c5.lft AND c5.rgt < c.rgt ');

			if (count($descendants) == 1)
			{
				$query->where('c5.id = ' . $descendants[0]);
			}
			else
			{
				$query->where('c5.id IN (' . implode(',', $descendants) . ')');
			}
		}

		// Filter starting level.
		$startingLevel = $this->getState('filter.starting_level');

		if (is_numeric($startingLevel))
		{
			$query->where('c.level >= ' . (int) $startingLevel);
		}

		// Filter ending level.
		$endingLevel = $this->getState('filter.ending_level');

		if (is_numeric($endingLevel))
		{
			$query->where('c.level <= ' . (int) $endingLevel);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		if ($orderList == 'c.address')
		{
			$orderList = 'a.address';
		}
		elseif ($orderList == 'c.zip')
		{
			$orderList = 'a.zip';
		}
		elseif ($orderList == 'c.city')
		{
			$orderList = 'a.city';
		}
		elseif ($orderList == 'c.country')
		{
			$orderList = 'con.name';
		}
		elseif ($orderList == 'c.customer_at')
		{
			$orderList = 'c2.name';
		}

		$order     = !empty($orderList) ? $orderList : 'c.lft';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Rebuild company redirections.
	 *
	 * @return boolean True on success, false on failure.
	 */
	public function rebuildRedirections()
	{
		if (RedshopbHelperLayout::canRedirect())
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Drop old redirections if any
			$query->delete($db->qn('#__redirect_links'))
				->where($db->qn('comment') . ' LIKE ' . $db->q('%' . Text::_('COM_REDSHOPB_LAYOUT_REDIRECT_COMMENT') . '%'));
			$db->setQuery($query);
			$db->execute();

			$query->clear();
			$query->select(Array($db->qn('c.name'), $db->qn('c.layout_id')))
				->from($db->qn('#__redshopb_company', 'c'))
				->where('c.state = 1')
				->where($db->qn('c.deleted') . ' = 0');

			$companies = $db->setQuery($query)->loadObjectList();
			$redirects = array();
			$jUri      = Uri::root();

			foreach ($companies as $company)
			{
				if ($company->layout_id != '' && $company->layout_id != 0)
				{
					$name = RedshopbHelperCompany::cleanName($company->name);

					$redirect    = $db->q($jUri . $name) . ', ';
					$redirect   .= $db->q($jUri . '?rb2b_layoutid=' . $company->layout_id) . ', ';
					$redirect   .= $db->q(Text::_("COM_REDSHOPB_LAYOUT_REDIRECT_COMMENT")) . ', ';
					$redirect   .= $db->q(1) . ', ';
					$redirect   .= $db->q(Factory::getDate()->toSql());
					$redirects[] = $redirect;

					$redirect    = $db->q($jUri . 'index.php/' . $name) . ', ';
					$redirect   .= $db->q($jUri . '?rb2b_layoutid=' . $company->layout_id) . ', ';
					$redirect   .= $db->q(Text::_("COM_REDSHOPB_LAYOUT_REDIRECT_COMMENT")) . ', ';
					$redirect   .= $db->q(1) . ', ';
					$redirect   .= $db->q(Factory::getDate()->toSql());
					$redirects[] = $redirect;
				}
			}

			$query->clear();
			$query->insert($db->qn('#__redirect_links'))
				->columns(
					$db->qn('old_url') . ', ' . $db->qn('new_url') . ', ' . $db->qn('comment')
					. ', ' . $db->qn('published') . ', ' . $db->qn('created_date')
				)
				->values($redirects);
			$db->setQuery($query);

			if ($db->execute())
			{
				return true;
			}

			$this->setError($db->getErrorMsg());
		}

		return false;
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

		$companies = parent::getItems();

		if (empty($companies) || ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) != 'true'))
		{
			return $companies;
		}

		foreach ($companies as $company)
		{
			if (empty($company->image))
			{
				continue;
			}

			$increment         = RedshopbHelperMedia::getIncrementFromFilename($company->image);
			$folderName        = RedshopbHelperMedia::getFolderName($increment);
			$company->imageurl = Uri::root() . 'media/com_redshopb/images/originals/companies/' . $folderName . '/' . $company->image;
		}

		return $companies;
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
		$db	= $this->getDbo();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->getListQuery();

		$query->select($db->q('UPDATE') . ' AS CRUD')
			->select($db->qn('c2.customer_number', 'customer_at_number'));

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

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Import Companies
	 *
	 * @param   array  $importData  Data received from CSV file
	 *
	 * @return  mixed
	 */
	public function import($importData)
	{
		$result  = array();
		$columns = $this->getCsvColumns();

		if (!is_array($importData))
		{
			return $result;
		}

		$allowedIds = $this->getAllowedCompanies();

		foreach ($importData as $rowNumber => $row)
		{
			if (!is_array($row))
			{
				$result['error'][] = Text::sprintf(
					'COM_REDSHOPB_COMPANIES_UNSUCCESSFULLY_IMPORTED', Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_COLUMNS_MISSING', $rowNumber + 2)
				);
				continue;
			}

			$data = array();

			// Prepare data with same columns
			foreach ($columns as $columnKey => $columnValue)
			{
				$data[$columnKey] = $row[strtolower($columnValue)];
			}

			$data['CRUD'] = !empty($data['CRUD']) ? strtoupper($data['CRUD']) : '';

			// Check if address can be modified
			if (in_array($data['CRUD'], array('UPDATE', 'DELETE')))
			{
				$data['id'] = RedshopbHelperCompany::getCompanyIdByCustomerNumber($data['customer_number']);

				if (!in_array($data['id'], $allowedIds))
				{
					$result['error'][] = Text::_('COM_REDSHOPB_COMPANY_ERROR_PERMISSIONS')
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}
			}

			$data['country_id'] = RedshopbEntityCountry::loadFromName($data['country'])->id;
			$data['type']       = !empty($data['type']) ? 'customer' : 'end_customer';

			if (!empty($data['customer_at_number']))
			{
				$data['parent_id'] = RedshopbHelperCompany::getCompanyIdByCustomerNumber($data['customer_at_number']);
			}

			if ($data['CRUD'] == 'CREATE')
			{
				$data['id'] = 0;
			}
			else
			{
				if (empty($data['id']))
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_COMPANIES_UNSUCCESSFULLY_IMPORTED',
						Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NUMBER')
					)
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}
			}

			/** @var RedshopbModelCompany $model */
			$model = RedshopbModelAdmin::getInstance('Company', 'RedshopbModel', array('ignore_request' => true));

			if ($data['CRUD'] == 'UPDATE' || $data['CRUD'] == 'CREATE')
			{
				if (!$model->save($data))
				{
					$result['error'][] = Text::sprintf('COM_REDSHOPB_COMPANIES_UNSUCCESSFULLY_IMPORTED', $model->getError())
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
				}
				else
				{
					$result['success'][$data['CRUD']][] = 1;
				}
			}
			elseif ($data['CRUD'] == 'DELETE')
			{
				$id = (int) $data['id'];

				if (!$model->delete($id))
				{
					$result['error'][] = Text::sprintf('COM_REDSHOPB_COMPANIES_UNSUCCESSFULLY_DELETED', $model->getError())
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
					'COM_REDSHOPB_COMPANIES_UNSUCCESSFULLY_IMPORTED', Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_CRUD')
				)
					. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
			}
		}

		return $result;
	}

	/**
	 * Get the columns for the csv file.
	 *
	 * @param   bool  $import  Get columns for import or export
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public function getCsvColumns($import = false)
	{
		return array(
			'CRUD' => Text::_('COM_REDSHOPB_CRUD'),
			'name' => Text::_('COM_REDSHOPB_NAME'),
			'customer_number' => Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NUMBER'),
			'state' => Text::_('JSTATUS'),
			'address' => Text::_('COM_REDSHOPB_ADDRESS_LABEL'),
			'zip' => Text::_('COM_REDSHOPB_ZIP_LABEL'),
			'city' => Text::_('COM_REDSHOPB_CITY_LABEL'),
			'country' => Text::_('COM_REDSHOPB_COUNTRY_LABEL'),
			'customer_at' => Text::_('COM_REDSHOPB_COMPANY_PARENT_LBL'),
			'customer_at_number' => Text::_('COM_REDSHOPB_EXPORT_COMPANY_PARENT_LBL'),
			'type' => Text::_('COM_REDSHOPB_TYPE'),
			'id' => Text::_('COM_REDSHOPB_EXPORT_COMPANY_SYSTEM_ID'),
		);
	}

	/**
	 * Get list of ids of allowed companies
	 *
	 * @return  array
	 */
	public function getAllowedCompanies()
	{
		$db	= $this->getDbo();
		/** @var RedshopbModelCompanies $companiesModel */
		$companiesModel = RedshopbModelAdmin::getInstance('Companies', 'RedshopbModel', array('ignore_request' => true));
		/** @var JDatabaseQuery $itemsQuery */
		$itemsQuery = $companiesModel->getListQuery();
		$itemsQuery->clear('select');
		$itemsQuery->select('c.id');

		$db->setQuery($itemsQuery);
		$items = $db->loadColumn();

		return !empty($items) ? $items : array();
	}
}
