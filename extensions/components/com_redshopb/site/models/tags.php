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
 * Tags Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTags extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_tags';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'tag_limit';

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
				't.id',
				't.name',
				't.state',
				'tag_company',
				't.type',
				'children_ids',
				'company_ids',
				'previous_id'
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
		$filterSearchChar = $this->getUserStateFromRequest($this->context . '.searchchar', 'searchchar');
		$this->setState('filter.search_char', $filterSearchChar);

		parent::populateState('t.lft', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db                    = $this->getDbo();
		$catProdXrefJoinNeeded = false;
		$tagProdXrefJoinNeeded = false;
		$productJoinNeeded     = false;
		$groupNeeded           = false;

		$query = $db->getQuery(true)
			->select('t.*')
			->from($db->qn('#__redshopb_tag', 't'))

			// Select the company
			->select('IFNULL(comp.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->leftJoin(
				$db->qn('#__redshopb_company', 'comp') . ' FORCE INDEX(PRIMARY) ON comp.id = t.company_id AND ' . $db->qn('comp.deleted') . ' = 0'
			);

		// Filter search
		$search = $this->getState('filter.search_tags', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				't.name LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Filter search by first charcter for taglist view
		$searchChar = $this->getState('filter.search_char');

		if (!empty($searchChar))
		{
			$searchVal = $db->quote($db->escape($searchChar, true) . '%');
			$query->where('t.name LIKE ' . $searchVal);
		}

		// Filter by category
		$category = $this->getState('filter.category');

		// Filter by public product
		$publicProduct = $this->getState('filter.public_product');

		if ($publicProduct || (is_array($category) ? !empty($category) : (is_numeric($category) && $category > 0)))
		{
			$query->where('pTag.state = 1')
				->where($db->qn('pTag.service') . ' = 0');
			$productJoinNeeded = true;
		}

		// Filter: product
		$products = RedshopbHelperDatabase::filterInteger($this->getState('filter.product'));

		if ($products)
		{
			if (count($products) == 1)
			{
				$query->where($db->qn('ptxTag.product_id') . ' = ' . (int) $products[0]);
			}
			else
			{
				$query->where('ptxTag.product_id IN (' . implode(',', $products) . ')');
			}

			$tagProdXrefJoinNeeded = true;
		}

		// Filter by company
		$company = $this->getState('filter.tag_company', $this->getState('filter.company_id'));

		if (is_numeric($company) && $company > 0)
		{
			$query->where('t.company_id = ' . (int) $company);
		}
		elseif ($company == 'null')
		{
			$query->where('t.company_id IS NULL');
		}

		// Filter by parent tag
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
			$query->where('t.parent_id = ' . (int) $parentId);
		}
		elseif ($parentId == 'null')
		{
			$query->where('t.parent_id IS NULL');
		}

		// Filter by state
		$state = $this->getState('filter.tag_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('t.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('t.state') . ' = 1');
		}

		// Filter by tag type
		$type = $this->getState('filter.type');

		if (!empty($type))
		{
			$query->where('t.type = ' . $db->q($type));
		}

		// Exclude tag type
		$excludeType = $this->getState('filter.type_exclude');

		if (!empty($excludeType))
		{
			if (is_array($excludeType))
			{
				foreach ($excludeType as $i => $excludeTypeItem)
				{
					$excludeType[$i] = $db->quote($excludeTypeItem);
				}

				$query->where($db->qn('t.type') . ' NOT IN (' . implode(',', $excludeType) . ')');
			}
			else
			{
				$query->where('t.type <> ' . $db->quote($excludeType));
			}
		}

		// Filter by children id (to find all children within a few ids sent as array)
		$childrenIds = $this->getState('filter.children_ids', null);

		if (!is_null($childrenIds))
		{
			$query->join(
				'inner', $db->qn('#__redshopb_tag', 'tp') .
					' ON ' . $db->qn('tp.lft') . ' < ' . $db->qn('t.lft') .
					' AND ' . $db->qn('tp.rgt') . ' > ' . $db->qn('t.rgt') .
					' AND ' . $db->qn('tp.level') . ' < ' . $db->qn('t.level')
			)
				->where($db->qn('tp.id') . ' IN (' . implode(',', $db->q($childrenIds)) . ')');
		}

		// Filter by multiple company ids
		$companyIds = $this->getState('filter.company_ids', null);

		if (!is_null($companyIds))
		{
			$query->where($db->qn('t.company_id') . ' IN (' . implode(',', $db->q($companyIds)) . ')');
		}

		$query->where($db->qn('t.parent_id') . ' IS NOT NULL');
		$query->where($db->qn('t.parent_id') . ' > 0');

		// Filter by multiple product
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			if (!is_array($productIds))
			{
				$productIds = array($productIds);
			}

			$productIds = ArrayHelper::toInteger($productIds);
			$query->where($db->qn('ptxTag.product_id') . ' IN (' . implode(',', $productIds) . ')');
			$tagProdXrefJoinNeeded = true;
		}

		// Filter above some tag id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('t.id') . ' > ' . (int) $previousId);
		}

		// Filter by manufacturer
		$manufacturer = $this->getState('filter.manufacturer');

		if (!empty($manufacturer))
		{
			if (is_numeric($manufacturer) && $manufacturer > 0)
			{
				$query->where('pTag.manufacturer_id = ' . (int) $manufacturer);
				$productJoinNeeded = true;
			}
			elseif (is_array($manufacturer))
			{
				$manufacturer = ArrayHelper::toInteger($manufacturer);
				$query->where('pTag.manufacturer_id IN (' . implode(',', $manufacturer) . ')');
				$productJoinNeeded = true;
			}
		}

		if (!empty($category))
		{
			if (is_numeric($category) && $category > 0)
			{
				$query->where('pcxTag.category_id = ' . (int) $category);
				$catProdXrefJoinNeeded = true;
			}
			elseif (is_array($category))
			{
				$category = ArrayHelper::toInteger($category);
				$query->where('pcxTag.category_id IN (' . implode(',', $category) . ')');
				$catProdXrefJoinNeeded = true;
			}
		}

		$view = Factory::getApplication()->input->get('view');

		if ((!$this->getState('list.isTotal', false) && $this->getState('list.countProductsSelect', false)) || $view == 'shop')
		{
			$productSearch         = new RedshopbDatabaseProductsearch;
			$tagProdXrefJoinNeeded = true;
			$query->select('COUNT(DISTINCT(ptxTag.product_id)) AS totalCount');

			if ($productSearch->hasTerm())
			{
				$query->where($db->qn('ptxTag.product_id') . ' IN (' . $productSearch->getStoredSearch() . ')');
			}
			else
			{
				$query->where('ptxTag.product_id IS NOT NULL');
			}
		}

		if ($tagProdXrefJoinNeeded || $catProdXrefJoinNeeded || $productJoinNeeded)
		{
			$query->leftJoin($db->qn('#__redshopb_product_tag_xref', 'ptxTag') . ' FORCE INDEX (PRIMARY) ON ptxTag.tag_id = t.id');
		}

		if ($catProdXrefJoinNeeded || $productJoinNeeded)
		{
			$query->leftJoin($db->qn('#__redshopb_product', 'pTag') . ' ON pTag.id = ptxTag.product_id');
		}

		if ($catProdXrefJoinNeeded)
		{
			$query->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcxTag') . ' FORCE INDEX (PRIMARY) ON pcxTag.product_id = pTag.id');
		}

		if ($groupNeeded || $catProdXrefJoinNeeded || $productJoinNeeded || $tagProdXrefJoinNeeded)
		{
			$query->group('t.id');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 't.lft';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  false|array  An array of data items on success, false on failure.
	 */
	public function getAllItems()
	{
		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query);
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $items;
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
		$tags = parent::getItems();

		if (empty($tags) || ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) != 'true'))
		{
			return $tags;
		}

		foreach ($tags as $tag)
		{
			if (empty($tag->image))
			{
				continue;
			}

			$increment     = RedshopbHelperMedia::getIncrementFromFilename($tag->image);
			$folderName    = RedshopbHelperMedia::getFolderName($increment);
			$tag->imageurl = Uri::root() . 'media/com_redshopb/images/originals/tags/' . $folderName . '/' . $tag->image;
		}

		return $tags;
	}
}
