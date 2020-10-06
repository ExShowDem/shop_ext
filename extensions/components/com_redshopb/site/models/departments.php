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
 * Departments Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelDepartments extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_departments';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'department_limit';

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
				'id', 'd.id',
				'name', 'd.name',
				'company', 'd.company',
				'address', 'd.address',
				'zip', 'd.zip',
				'city', 'd.city',
				'country', 'd.country',
				'lft', 'd.lft',
				'department_number', 'd.department_number',
				'company_id', 'd.company_id',
				'parent_id', 'd.parent_id',
				'children_ids'
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
		parent::populateState('d.lft', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db       = $this->getDbo();
		$subQuery = $db->getQuery(true);
		$query    = $db->getQuery(true)
			->select('d.*')
			->from($db->qn('#__redshopb_department', 'd'))
			->select($db->quoteName('d.department_number', 'customer_number'))

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
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('d.address_id') . ' = ' . $db->qn('a.id'))

			// Select the country name and code
			->select(
				array(
					$db->qn('con.name', 'country'),
					$db->qn('con.alpha2', 'country_code')
				)
			)
			->leftJoin($db->qn('#__redshopb_country', 'con') . ' ON ' . $db->qn('con.id') . ' = ' . $db->qn('a.country_id'))

			// Get the owner company name
			->select($db->qn('c.name', 'company'))
			->leftJoin(
				$db->qn('#__redshopb_company', 'c') .
				' ON ' . $db->qn('c.id') . ' = ' . $db->qn('d.company_id') .
				' AND ' . $db->qn('c.deleted') . ' = 0'
			)

			->where('d.level != 0')
			->where($db->qn('d.deleted') . ' = 0');

		// Get the number of users
		$subQuery->select('COUNT(u.id)')
			->from($db->qn('#__redshopb_user', 'u'))
			->where('u.department_id = d.id');

		$query->select('(' . $subQuery . ') AS users');

		// Filter: return only departments descendant of specific companies
		$companyAncestors = RedshopbHelperDatabase::filterInteger($this->getState('filter.company_ancestor'));

		if ($companyAncestors)
		{
			$query->innerJoin('#__redshopb_company as c2 ON c.lft >= c2.lft AND c.rgt <= c2.rgt');

			if (count($companyAncestors) == 1)
			{
				$query->where('c2.id = ' . $companyAncestors[0]);
			}
			else
			{
				$query->where('c2.id IN (' . implode(',', $companyAncestors) . ')');
			}
		}

		// Filter: return only departments descendant of specific departments
		$ancestors = RedshopbHelperDatabase::filterInteger($this->getState('filter.ancestor'));

		if ($ancestors)
		{
			$query->innerJoin('#__redshopb_department as d2 ON d.lft > d2.lft AND d.rgt < d2.rgt');

			if (count($ancestors) == 1)
			{
				$query->where('d2.id = ' . $ancestors[0]);
			}
			else
			{
				$query->where('d2.id IN (' . implode(',', $ancestors) . ')');
			}
		}

		$company = $this->getState('filter.company_id', $this->getState('filter.company'));

		if (is_numeric($company))
		{
			$query->where('c.id = ' . (int) $company);
		}

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where('d.id = ' . (int) $id);
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
			$query->where($db->qn('d.parent_id') . ' = ' . (int) $parentId);
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

		// Filter by state.
		$state = $this->getState('filter.department_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('c.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('c.state') . ' = 1');
		}

		// Filter by children id (to find all children within a few ids sent as array)
		$childrenIds = $this->getState('filter.children_ids', null);

		if (!is_null($childrenIds))
		{
			$query->join(
				'inner', $db->qn('#__redshopb_department', 'dp') .
					' ON ' . $db->qn('dp.lft') . ' < ' . $db->qn('d.lft') .
					' AND ' . $db->qn('dp.rgt') . ' > ' . $db->qn('d.rgt') .
					' AND ' . $db->qn('dp.level') . ' < ' . $db->qn('d.level')
			)
				->where($db->qn('dp.id') . ' IN (' . implode(',', $db->q($childrenIds)) . ')');
		}

		// Filter search
		$search = $this->getState('filter.search_departments', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				$db->qn('d.name') . ' LIKE ' . $search,
				$db->qn('d.name2') . ' LIKE ' . $search,
				$db->qn('d.department_number') . ' LIKE ' . $search,
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

		// Check for available departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin() && $this->getState('check.user_departments', true) === true)
		{
			$user = Factory::getUser();

			// Skip check user's department if current mode is in B2C.
			if (!$user->b2cMode)
			{
				$query->where('d.id IN (' . RedshopbHelperACL::listAvailableDepartments($user->id) . ')');
			}
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		if ($orderList == 'd.company')
		{
			$orderList = 'c.name';
		}

		if ($orderList == 'd.address')
		{
			$orderList = 'a.address';
		}
		elseif ($orderList == 'd.zip')
		{
			$orderList = 'a.zip';
		}
		elseif ($orderList == 'd.city')
		{
			$orderList = 'a.city';
		}
		elseif ($orderList == 'd.country')
		{
			$orderList = 'con.name';
		}

		$order     = !empty($orderList) ? $orderList : 'd.lft';
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

		$departments = parent::getItems();

		if (empty($departments) || ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) != 'true'))
		{
			return $departments;
		}

		foreach ($departments as $department)
		{
			if (empty($department->image))
			{
				continue;
			}

			$increment            = RedshopbHelperMedia::getIncrementFromFilename($department->image);
			$folderName           = RedshopbHelperMedia::getFolderName($increment);
			$department->imageurl = Uri::root() . 'media/com_redshopb/images/originals/departments/' . $folderName . '/' . $department->image;
		}

		return $departments;
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
			->select($db->qn('c.customer_number'));

		$query->select($db->qn('dp.name', 'parent_department'))
			->leftJoin(
				$db->qn('#__redshopb_department', 'dp') . ' ON dp.id = d.parent_id AND ' . $db->qn('dp.deleted') . ' = 0 AND ' .
				$db->qn('dp.state') . ' = 1'
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

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Import Departments
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

		$allowedIds = $this->getAllowedDepartments();

		foreach ($importData as $rowNumber => $row)
		{
			if (!is_array($row))
			{
				$result['error'][] = Text::sprintf(
					'COM_REDSHOPB_DEPARTMENTS_UNSUCCESSFULLY_IMPORTED', Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_COLUMNS_MISSING', $rowNumber + 2)
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
				if (!in_array($data['id'], $allowedIds))
				{
					$result['error'][] = Text::_('COM_REDSHOPB_DEPARTMENT_ERROR_PERMISSIONS')
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}
			}

			$data['country_id'] = RedshopbEntityCountry::loadFromName($data['country'])->id;
			$data['company_id'] = RedshopbHelperCompany::getCompanyIdByCustomerNumber($data['customer_number']);

			if (empty($data['company_id']))
			{
				$result['error'][] = Text::sprintf(
					'COM_REDSHOPB_DEPARTMENTS_UNSUCCESSFULLY_IMPORTED',
					Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NAME')
				)
					. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
				continue;
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
						'COM_REDSHOPB_DEPARTMENTS_UNSUCCESSFULLY_IMPORTED',
						Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_EXPORT_DEPARTMENT_NUMBER')
					)
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}
			}

			/** @var RedshopbModelDepartment $model */
			$model = RedshopbModelAdmin::getInstance('Department', 'RedshopbModel', array('ignore_request' => true));

			if ($data['CRUD'] == 'UPDATE' || $data['CRUD'] == 'CREATE')
			{
				if (!$model->save($data))
				{
					$result['error'][] = Text::sprintf('COM_REDSHOPB_DEPARTMENTS_UNSUCCESSFULLY_IMPORTED', $model->getError())
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
					$result['error'][] = Text::sprintf('COM_REDSHOPB_DEPARTMENTS_UNSUCCESSFULLY_DELETED', $model->getError())
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
					'COM_REDSHOPB_DEPARTMENTS_UNSUCCESSFULLY_IMPORTED', Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_CRUD')
				)
					. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
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
			'name' => Text::_('COM_REDSHOPB_NAME'),
			'department_number' => Text::_('COM_REDSHOPB_EXPORT_DEPARTMENT_NUMBER'),
			'parent_department' => Text::_('COM_REDSHOPB_DEPARTMENTS_PARENT_DEPARTMENT'),
			'parent_id' => Text::_('COM_REDSHOPB_EXPORT_DEPARTMENT_PARENT_LBL'),
			'company' => Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NAME'),
			'customer_number' => Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NUMBER'),
			'address' => Text::_('COM_REDSHOPB_ADDRESS_LABEL'),
			'zip' => Text::_('COM_REDSHOPB_ZIP_LABEL'),
			'city' => Text::_('COM_REDSHOPB_CITY_LABEL'),
			'country' => Text::_('COM_REDSHOPB_COUNTRY_LABEL'),
			'id' => Text::_('COM_REDSHOPB_EXPORT_DEPARTMENT_SYSTEM_ID'),
		);
	}

	/**
	 * Get list of ids of allowed departments
	 *
	 * @return  array
	 */
	public function getAllowedDepartments()
	{
		$db	= $this->getDbo();
		/** @var RedshopbModelDepartments $departmentsModel */
		$departmentsModel = RedshopbModelAdmin::getInstance('Departments', 'RedshopbModel', array('ignore_request' => true));
		/** @var JDatabaseQuery $itemsQuery */
		$itemsQuery = $departmentsModel->getListQuery();
		$itemsQuery->clear('select');
		$itemsQuery->select('d.id');

		$db->setQuery($itemsQuery);
		$items = $db->loadColumn();

		return !empty($items) ? $items : array();
	}
}
