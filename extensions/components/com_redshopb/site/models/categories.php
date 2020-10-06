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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
/**
 * Categories Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCategories extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_categories';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'category_limit';

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
				'published', 'c.state',
				'created_date', 'c.created_date',
				'modified_date', 'c.modified_date',
				'created_by', 'c.created_by',
				'modified_by', 'c.modified_by',
				'category_state',
				'category_company',
				'category_hidden',
				'id',
				'parent_id',
				'children_ids',
				'company_ids',
				'previous_id',
				'filter_fieldset_id',
				'c.lft'
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
	 * @param   string|null  $ordering   An optional ordering field.
	 * @param   string|null  $direction  An optional direction (asc|desc).
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
	public function getListQuery()
	{
		$db	   = $this->getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__redshopb_category as c')
			->select('u1.name as author')
			->leftJoin('#__users AS u1 ON u1.id = c.created_by')
			->select('u2.name as editor')
			->leftJoin('#__users AS u2 ON u2.id = c.modified_by')

			// Select the company
			->select('IFNULL(comp.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->leftJoin('#__redshopb_company AS comp ON comp.id = c.company_id AND ' . $db->qn('comp.deleted') . ' = 0')

			// Select the templates
			->select($db->qn('t.alias', 'template_code'))
			->join('left', $db->qn('#__redshopb_template', 't') . ' ON ' . $db->qn('c.template_id') . ' = ' . $db->qn('t.id'))
			->select($db->qn('tpl.alias', 'product_list_template_code'))
			->join('left', $db->qn('#__redshopb_template', 'tpl') . ' ON ' . $db->qn('c.product_list_template_id') . ' = ' . $db->qn('tpl.id'))
			->select($db->qn('tpg.alias', 'product_grid_template_code'))
			->join('left', $db->qn('#__redshopb_template', 'tpg') . ' ON ' . $db->qn('c.product_grid_template_id') . ' = ' . $db->qn('tpg.id'));

		// Filter search
		$search = $this->getState('filter.search_categories', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(c.name LIKE ' . $search . ')');
		}

		// Limit companies based on allowed permissions (main warehouse or allowed companies' categories)
		$user = RedshopbHelperCommon::getUser();

		if ($user->b2cMode)
		{
			$availableCompanies = RedshopbEntityCompany::getInstance($user->b2cCompany)->getTree(true, true);
			$query->where(
				'(' . $db->qn('c.company_id') . ' IN(' . implode(',', $availableCompanies) . ') OR ' . $db->qn('c.company_id') . ' IS NULL)'
			);
		}
		elseif ($this->getState('manageOnly', false) && !RedshopbHelperACL::isSuperAdmin())
		{
			if ($this->getState('includeMainWarehouse', false))
			{
				$query->where(
					'(' . $db->qn('c.company_id') . ' IN ('
					. RedshopbHelperACL::listAvailableCompanies($user->id, 'comma', 0, '', 'redshopb.company.view') . ') OR '
					. $db->qn('c.company_id') . ' IS NULL)'
				);
			}
			else
			{
				$query->where(
					'(' . $db->qn('c.company_id') . ' IN ('
					. RedshopbHelperACL::listAvailableCompanies($user->id, 'comma', 0, '', 'redshopb.company.manage') . '))'
				);
			}
		}
		else
		{
			$query->where(
				'(' . $db->qn('c.company_id') . ' IN ('
				. RedshopbHelperACL::listAvailableCompanies($user->id, 'comma', 0, '', 'redshopb.company.view', '', true) . ') OR '
				. $db->qn('c.company_id') . ' IS NULL)'
			);
		}

		// Filter by state
		$state = $this->getState('filter.category_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('c.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('c.state') . ' = 1');
		}

		// Filter hidden categories
		$hidden = $this->getState('filter.category_hidden', '');

		if (is_numeric($hidden))
		{
			$query->where($db->qn('c.hide') . ' = ' . (int) $hidden);
		}

		// Filter by company
		$company = $this->getState('filter.category_company', $this->getState('filter.company_id'));

		if (is_numeric($company) && $company > 0)
		{
			$query->where('c.company_id = ' . (int) $company);
		}
		elseif ($company == 'null')
		{
			$query->where('c.company_id IS NULL');
		}

		// Filter by parent category
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

		if (is_numeric($parentId) && $parentId > 0)
		{
			$query->where('c.parent_id = ' . (int) $parentId);
		}
		elseif ($parentId == 'null')
		{
			$query->where('c.parent_id IS NULL');
		}

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where('c.id = ' . (int) $id);
		}

		$filterIds = $this->getState('filter.ids', null);

		if (is_array($filterIds) && !empty($filterIds))
		{
			$filterIds = ArrayHelper::toInteger($filterIds);

			$query->where($db->qn('c.id') . ' IN (' . implode(',', $filterIds) . ')');
		}

		// Filter by filter field set
		$filterFilterFieldset = (int) $this->getState('filter.filter_fieldset_id');

		if ($filterFilterFieldset)
		{
			$query->where($db->qn('c.filter_fieldset_id') . ' = ' . $filterFilterFieldset);
		}

		// Filter by template (code - alias)
		$templateCode = $this->getState('filter.template_code', '');

		if ($templateCode != '')
		{
			$query->where('t.alias = ' . $db->q($templateCode));
		}

		// Filter: product
		$products              = RedshopbHelperDatabase::filterInteger($this->getState('filter.product'));
		$productXrefJoinNeeded = false;

		if ($products)
		{
			$productXrefJoinNeeded = true;

			if (count($products) == 1)
			{
				$query->where($db->qn('cref.product_id') . ' = ' . (int) $products[0]);
			}
			else
			{
				$query->where('cref.product_id IN (' . implode(',', $products) . ')');
			}
		}

		if (!$this->getState('list.isTotal', false)
			&& $this->getState('list.countProductsSelect', false))
		{
			$productSearch         = new RedshopbDatabaseProductsearch;
			$productXrefJoinNeeded = true;
			$query->select('COUNT(p.id) AS totalCount');

			if ($productSearch->hasTerm())
			{
				$query->where($db->qn('p.id') . ' IN (' . $productSearch->getStoredSearch() . ')');
			}

			$productSearch->filterByRecent($query);
		}

		// Filter by manufacturer
		$manufacturer = $this->getState('filter.manufacturer');

		if (!empty($manufacturer))
		{
			if (is_numeric($manufacturer) && $manufacturer > 0)
			{
				$query->where('p.manufacturer_id = ' . (int) $manufacturer);
				$productXrefJoinNeeded = true;
			}
			elseif (is_array($manufacturer))
			{
				$manufacturer = ArrayHelper::toInteger($manufacturer);
				$query->where('p.manufacturer_id IN (' . implode(',', $manufacturer) . ')');
				$productXrefJoinNeeded = true;
			}
		}

		if ($productXrefJoinNeeded)
		{
			$query->innerJoin(
				$db->qn('#__redshopb_product_category_xref', 'cref')
				. ' FORCE INDEX(PRIMARY) ON ' . $db->qn('cref.category_id') . ' = ' . $db->qn('c.id')
			)->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON cref.product_id = p.id');
		}

		if ($productXrefJoinNeeded)
		{
			$query->group('c.id');
		}

		// Filter by children id (to find all children within a few ids sent as array)
		$childrenIds = $this->getState('filter.children_ids', null);

		if (!is_null($childrenIds))
		{
			$query->join(
				'inner', $db->qn('#__redshopb_category', 'cp') .
					' ON ' . $db->qn('cp.lft') . ' < ' . $db->qn('c.lft') .
					' AND ' . $db->qn('cp.rgt') . ' > ' . $db->qn('c.rgt') .
					' AND ' . $db->qn('cp.level') . ' < ' . $db->qn('c.level')
			)
				->where($db->qn('cp.id') . ' IN (' . implode(',', $db->q($childrenIds)) . ')');
		}

		// Filter by multiple company ids
		$companyIds = $this->getState('filter.company_ids', null);

		if (!is_null($companyIds))
		{
			$query->where($db->qn('c.company_id') . ' IN (' . implode(',', $db->q($companyIds)) . ')');
		}

		// Filter above some category id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('c.id') . ' > ' . (int) $previousId);
		}

		$query->where($db->qn('c.parent_id') . ' IS NOT NULL');
		$query->where($db->qn('c.parent_id') . ' > 0');

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'c.lft';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds sync enrichment
		$this->getSyncEnrichedQuery($query);

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Get the array of parents of a certain category
	 *
	 * @param   int  $id  Category Id
	 *
	 * @return  array
	 */
	public function getParents($id)
	{
		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');

		return RedshopbHelperCategory::getParentCategories($customerId, $customerType, $id, true, false);
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
		$categories = parent::getItems();

		if ($this->getState('filter.include_local_fields'))
		{
			foreach ($categories as $category)
			{
				$model       = RModel::getFrontInstance('Category');
				$localFields = $model->getFields($category->id, true);

				foreach ($localFields as $localField)
				{
					$category->local_fields[] = $localField;
				}
			}
		}

		if (empty($categories) || ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) != 'true'))
		{
			return $categories;
		}

		foreach ($categories as $category)
		{
			if (empty($category->image))
			{
				continue;
			}

			$increment          = RedshopbHelperMedia::getIncrementFromFilename($category->image);
			$folderName         = RedshopbHelperMedia::getFolderName($increment);
			$category->imageurl = Uri::root() . 'media/com_redshopb/images/originals/categories/' . $folderName . '/' . $category->image;
		}

		return $categories;
	}

	/**
	 * Method overridden to insure that the parent_id_other property is an empty array if it is not set
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItemsWS()
	{
		$items = parent::getItemsWS();

		if (!$items)
		{
			return false;
		}

		foreach ($items as $item)
		{
			if (!empty($item->parent_id_syncref))
			{
				continue;
			}

			$item->parent_id_syncref = array();
		}

		return $items;
	}
}
