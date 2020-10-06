<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * collections Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCollections extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_collections';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'collection_limit';

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
				'id', 'w.id',
				'name', 'w.name',
				'state', 'w.state',
				'company', 'w.company',
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
		parent::populateState('w.name', 'asc');
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
			->select('DISTINCT w.*')
			->from('#__redshopb_collection AS w')
			->select('c1.name AS company')
			->innerJoin('#__redshopb_company AS c1 ON c1.id = w.company_id AND ' . $db->qn('c1.deleted') . ' = 0');

		// Filter by department
		$department = $this->getState('filter.department');

		$departmentFilter = false;

		if (is_numeric($department))
		{
			$departmentFilter = true;

			$query
				->innerJoin('#__redshopb_collection_department_xref AS map ON map.collection_id = w.id')
				->where('map.department_id = ' . (int) $department);
		}

		// Filter search
		$search = $this->getState('filter.search_collections');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(w.name LIKE ' . $search . ')');
		}

		// Filter by product
		$product = $this->getState('filter.product');

		if (is_numeric($product))
		{
			$query->leftJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON wpx.collection_id = w.id')
				->where('wpx.product_id = ' . (int) $product);
		}

		// Filter by company
		$company = $this->getState('filter.company');

		if (is_numeric($company))
		{
			$query->where('w.company_id = ' . (int) $company);
		}

		$skipUserCheck = $this->getState('filter.skipUserCheck', false);

		// Check for available companies and departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin() && !$skipUserCheck)
		{
			$user = Factory::getUser();
			$query->where('w.company_id IN (' . RedshopbHelperACL::listAvailableCompaniesbyPermission($user->id, 'redshopb.collection.view') . ')');

			if (!$departmentFilter)
			{
				$query->innerJoin('#__redshopb_collection_department_xref AS map ON map.collection_id = w.id');
			}

			$query->where('map.department_id IN (' . RedshopbHelperACL::listAvailableDepartments($user->id) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		if ($orderList == 'w.company')
		{
			$orderList = 'c1.name';
		}

		$order     = !empty($orderList) ? $orderList : 'w.name';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->group('w.id')
			->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Set the department id for each item
		foreach ($items as &$item)
		{
			$item->departments = $this->getDepartments($item->id);
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Get the departments related to the specified collection.
	 *
	 * @param   integer  $collectionId  The collection id
	 *
	 * @return  array  An array of departments
	 */
	private function getDepartments($collectionId)
	{
		$db    = $this->_db;
		$query = $this->_db->getQuery(true)
			->select('d.name')
			->from('#__redshopb_department AS d')
			->innerJoin('#__redshopb_collection_department_xref AS map ON map.department_id = d.id')
			->where('map.collection_id = ' . (int) $collectionId)
			->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');

		$db->setQuery($query);

		$departments = $db->loadColumn();

		if (!is_array($departments))
		{
			return array();
		}

		return $departments;
	}

	/**
	 * Get collection information
	 *
	 * @param   integer  $id  Id current collection
	 *
	 * @return mixed
	 */
	public function getCollection($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_collection'))
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get product items
	 *
	 * @param   int  $id  Id current collection
	 *
	 * @return mixed
	 */
	public function getProductItems($id)
	{
		$db       = Factory::getDbo();
		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
			->where('piavx.product_item_id = pi.id')
			->order('pav.ordering');

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('p.name', 'product_name'),
					'pi.*',
					'(CONCAT_WS(' . $db->q('-') . ', p.sku, (' . $subQuery . '))) AS sku'
				)
			)
			->from($db->qn('#__redshopb_product_item', 'pi'))
			->leftJoin($db->qn('#__redshopb_collection_product_item_xref', 'wpix') . ' ON wpix.product_item_id = pi.id')
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON pi.product_id = p.id')
			->where('wpix.state = 1')
			->where('wpix.collection_id = ' . (int) $id)
			->order('pi.product_id');
		$db->setQuery($query);

		return $db->loadObjectList();
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
		$db = $this->getDbo();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->getListQuery();

		if (null !== $data)
		{
			$data = implode(',', $db->q($data));
			$query->where("{$db->qn('w.id')} IN ({$data})");
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
}
