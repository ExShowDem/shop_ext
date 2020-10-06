<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
/**
 * Manufacturers Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.51
 */
class RedshopbModelManufacturers extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_manufacturers';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'manufacturer_limit';

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
				'id', 'm.id',
				'name', 'm.name',
				'published', 'm.state',
				'created_date', 'm.created_date',
				'modified_date', 'm.modified_date',
				'created_by', 'm.created_by',
				'modified_by', 'm.modified_by',
				'manufacturer_state',
				'lft', 'm.lft',
				'id',
				'parent_id',
				'children_ids',
				'featured',
				'previous_id',
				'product_category'
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
		$ordering  = is_null($ordering) ? 'm.lft' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db                 = $this->getDbo();
		$app                = Factory::getApplication();
		$categoryJoinNeeded = false;
		$productJoinNeeded  = false;
		$groupNeeded        = false;
		$query              = $db->getQuery(true);

		$query->select($this->getState('list.select', 'm.*'))
			->from($db->qn('#__redshopb_manufacturer', 'm') . ' FORCE INDEX (PRIMARY)');

		// Filter search
		$search = $this->getState('filter.search_manufacturers', $this->getState('filter.search'));

		if (!empty($search))
		{
			$query->where($db->qn('m.name') . ' LIKE ' . $db->quote('%' . $db->escape($search, true) . '%'));
		}

		// Filter by state
		$state = $this->getState('filter.manufacturer_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('m.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('m.state') . ' = 1');
		}

		// Filter by featured
		$featured = $this->getState('filter.featured');

		if ($featured == '0' || $featured == 'false')
		{
			$query->where($db->qn('m.featured') . ' = 0');
		}
		elseif ($featured == '1' || $featured == 'true')
		{
			$query->where($db->qn('m.featured') . ' = 1');
		}

		// Filter search by first character for taglist view
		$searchChars = $this->getState('filter.search_char', null);

		if (!empty($searchChars))
		{
			if (!is_array($searchChars))
			{
				$searchChars = array($searchChars);
			}

			$searchConditions = array();

			foreach ($searchChars as $searchChar)
			{
				$searchVal          = $db->quote($db->escape($searchChar, true) . '%');
				$searchConditions[] = $db->qn('m.name') . ' LIKE ' . $searchVal;
			}

			$query->where('((' . implode(') OR (', $searchConditions) . '))');
		}

		// Filter by category
		$categoryFilter = $this->getState('filter.category');

		if (is_numeric($categoryFilter) && $categoryFilter > 0)
		{
			$query->where($db->qn('cref.category_id') . ' = ' . (int) $categoryFilter);
			$categoryJoinNeeded = true;
		}
		elseif (is_array($categoryFilter) && !empty($categoryFilter))
		{
			$categoryFilter = ArrayHelper::toInteger($categoryFilter);
			$query->where('cref.category_id IN (' . implode(',', $categoryFilter) . ')');
			$categoryJoinNeeded = true;
		}

		$productCategoryFilter = $this->getState('filter.product_category');

		if (is_numeric($productCategoryFilter) && $productCategoryFilter > 0)
		{
			$customerType = $this->getState('shop.customer_type', 'employee');
			$companyId    = $this->getState('shop.company_id', null);

			if ($customerType == 'company')
			{
				$companyId = $this->getState('shop.customer_id', null);
			}

			$allChildrenWithProducts = RedshopbHelperACL::listAvailableCategories(
				Factory::getUser()->id,
				$productCategoryFilter,
				100,
				$companyId,
				false,
				'comma',
				'',
				null,
				0,
				0,
				false,
				true
			);

			$query->where($db->qn('cref.category_id') . ' IN (' . $productCategoryFilter . ',' . $allChildrenWithProducts . ')');
			$categoryJoinNeeded = true;
		}

		// Filter by multiple product
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			if (!is_array($productIds))
			{
				$productIds = array($productIds);
			}

			if (!count($productIds))
			{
				$query->where('0 = 1');
			}
			else
			{
				$productIds = ArrayHelper::toInteger($productIds);
				$query->where($db->qn('p.id') . ' IN (' . implode(',', $productIds) . ')');
				$productJoinNeeded = true;
			}
		}

		// Filter by children id (to find all children within a few ids sent as array)
		$childrenIds = $this->getState('filter.children_ids', null);

		if (!is_null($childrenIds))
		{
			$query->join(
				'inner', $db->qn('#__redshopb_manufacturer', 'mp') .
					' ON ' . $db->qn('mp.lft') . ' < ' . $db->qn('m.lft') .
					' AND ' . $db->qn('mp.rgt') . ' > ' . $db->qn('m.rgt') .
					' AND ' . $db->qn('mp.level') . ' < ' . $db->qn('m.level')
			)
				->where($db->qn('mp.id') . ' IN (' . implode(',', $db->quote($childrenIds)) . ')');
			$groupNeeded = true;
		}

		// Filter by parent manufacturer
		$parentId = $this->getState('filter.parent_id');

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
			$query->where($db->qn('m.parent_id') . ' = ' . (int) $parentId);
		}

		// Filter above some category id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('m.id') . ' > ' . (int) $previousId);
		}

		$query->where($db->qn('m.parent_id') . ' IS NOT NULL');
		$query->where($db->qn('m.parent_id') . ' > 0');

		$view = Factory::getApplication()->input->get('view');

		if ((!$this->getState('list.isTotal', false)
			&& $this->getState('list.countProductsSelect', false))
			|| $view == 'shop')
		{
			$productSearch     = new RedshopbDatabaseProductsearch;
			$productJoinNeeded = true;
			$query->select('COUNT(p.id) AS totalCount');

			if ($productSearch->hasTerm())
			{
				$query->where($db->qn('p.id') . ' IN (' . $productSearch->getStoredSearch() . ')');
			}

			$productSearch->filterByRecent($query);
		}

		if ($this->getState('filter.only_with_active_product', null))
		{
			$customerType = $app->getUserState('shop.customer_type', '');
			$customerId   = $app->getUserState('shop.customer_id', 0);

			$companyId           = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
			$availableCategories = RedshopbHelperACL::listAvailableCategories(Factory::getUser()->id, false, 100, $companyId, false, 'comma', '');
			$availableCategories = explode(',', $availableCategories);

			$query->where($db->qn('cref.category_id') . ' IN (' . implode(',', $availableCategories) . ')')
				->where($db->qn('p.id') . ' IS NOT NULL');
			$categoryJoinNeeded = true;
		}

		// Filter by manufacturers category
		$listCategory = $this->getState('manufacturers_list.category', '');

		if (!empty($listCategory))
		{
			$query->where($db->qn('m.category') . ' = ' . $db->q($listCategory));
		}

		if ($productJoinNeeded || $categoryJoinNeeded)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_product', 'p') . ' FORCE INDEX (idx_state) ON '
				. $db->qn('p.manufacturer_id') . ' = ' . $db->qn('m.id') . ' AND p.state = 1 AND p.service = 0'
			);
		}

		if ($categoryJoinNeeded)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_product_category_xref', 'cref')
				. ' FORCE INDEX(PRIMARY) ON ' . $db->qn('cref.product_id') . ' = ' . $db->qn('p.id')
			);
		}

		if ($productJoinNeeded || $categoryJoinNeeded || $groupNeeded)
		{
			$query->group($db->qn('m.id'));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'm.lft';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Return list first chars of manufacturers which are has product related with.
	 *
	 * @return  array     List of available chars.
	 */
	public function getFirstCharAvailable()
	{
		$db = $this->getDbo();

		// Temporary remove search_char filter
		$searchChars = $this->getState('filter.search_char');
		$this->setState('filter.search_char', null);

		$query = $this->getListQuery();

		$query->clear('select')
			->select('UPPER(LEFT(' . $db->qn('m.name') . ', 1)) AS ' . $db->qn('char'));

		// Remove number from result and exclude ROOT tree.
		$query->where($db->qn('m.name') . ' NOT REGEXP ' . $db->quote('^[0-9]'))
			->where($db->qn('m.level') . ' > 0')
			->group($db->qn('char'));

		$result = $db->setQuery($query)->loadColumn();

		$this->setState('filter.search_char', $searchChars);

		return $result;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItems()
	{
		$manufacturers = parent::getItems();

		if (empty($manufacturers) || ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) != 'true'))
		{
			return $manufacturers;
		}

		foreach ($manufacturers as $manufacturer)
		{
			if (empty($manufacturer->image))
			{
				continue;
			}

			$increment              = RedshopbHelperMedia::getIncrementFromFilename($manufacturer->image);
			$folderName             = RedshopbHelperMedia::getFolderName($increment);
			$manufacturer->imageurl = Uri::root() . 'media/com_redshopb/images/originals/manufacturers/' . $folderName . '/' . $manufacturer->image;
		}

		return $manufacturers;
	}
}
