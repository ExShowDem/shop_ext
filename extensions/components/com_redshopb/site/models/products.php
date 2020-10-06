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
 * Products Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProducts extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_products';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_limit';

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
				'id', 'p.id',
				'name', 'p.name',
				'state', 'p.state',
				'discontinued', 'p.discontinued',
				'company', 'p.company',
				'sku', 'p.sku', 'sku_array',
				'manufacturer_sku', 'p.manufacturer_sku',
				'related_sku', 'p.related_sku',
				'product_state',
				'product_company',
				'product_discontinued',
				'product_category',
				'product_tag',
				'product_collection',
				'cid',
				'featured',
				'company_id',
				'category_id',
				'company_ids',
				'previous_id',
				'manufacturer_id',
				'manufacturer_name',
				'cpx.ordering',
				'filter_fieldset_id',
				'unit_measure_code',
				'template_code',
				'min_modified_date',
				'modified_date'
			);
		}

		parent::__construct($config);
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

		$includeCategories = ($this->getState('filter.include_categories', $this->getState('list.include_categories', 'false')) == 'true');
		$includeTags       = ($this->getState('filter.include_tags', $this->getState('list.include_tags', 'false')) == 'true');
		$includeImages     = ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) == 'true');
		$useObjects        = ($this->getState('include_objects', $this->getState('include_objects', 'false')) == 'true');

		if ($includeCategories || $includeTags || $includeImages)
		{
			// Get the categories related to the product
			foreach ($items as &$item)
			{
				$product = RedshopbEntityProduct::getInstance($item->id)->bind($item);

				if ($includeCategories)
				{
					if ($useObjects)
					{
						$item->categories = $product->getCategories();
					}
					else
					{
						$item->categories = $product->getCategories()->toFieldArray('id');
					}
				}

				if ($includeTags)
				{
					if ($useObjects)
					{
						$item->tags = $product->getTags();
					}
					else
					{
						$item->tags = $product->getTags()->toFieldArray('id');
					}
				}

				if ($includeImages)
				{
					$item->images = $product->getImages();

					foreach ($item->images as $image)
					{
						$item->imageurl = Uri::root() .
							RedshopbHelperMedia::getFullMediaPath($image->name, 'products', 'images', $image->remote_path);
					}
				}
			}
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
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
		parent::populateState('p.name', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.product_company');
		$id	.= ':' . $this->getState('filter.product_state');
		$id	.= ':' . $this->getState('filter.product_discontinued');
		$id	.= ':' . $this->getState('filter.product_collection');
		$id	.= ':' . $this->getState('filter.search_products');
		$id	.= ':' . $this->getState('list.product_state');
		$id	.= ':' . $this->getState('list.product_discontinued');
		$id	.= ':' . $this->getState('list.force_collection');
		$id	.= ':' . $this->getState('list.disallow_freight_fee_products');
		$id	.= ':' . $this->getState('list.include_images');
		$id	.= ':' . $this->getState('list.include_categories');
		$id	.= ':' . $this->getState('list.include_tags');
		$id	.= ':' . $this->getState('list.offer');
		$id	.= ':' . $this->getState('list.offerInclude');
		$id	.= ':' . $this->getState('list.allow_parent_companies_products');
		$id	.= ':' . $this->getState('list.allow_mainwarehouse_products');
		$id	.= ':' . $this->getState('list.company_id');
		$id	.= ':' . $this->getState('list.min_modified_date');
		$id	.= ':' . $this->getState('list.modified_date');
		$id	.= ':' . $this->getState('list.ignore_acl');

		$productIds = $this->getState('filter.product_id');
		$productIds = ArrayHelper::toInteger($productIds);

		if (!empty($productIds))
		{
			$id .= implode(',', $productIds);
		}

		$tags = $this->getState('filter.product_tag');

		if (!empty($tags))
		{
			if (is_array($tags))
			{
				$tags = ArrayHelper::toInteger($tags);
				$id  .= implode(',', $tags);
			}
			else
			{
				$id .= (int) $tags;
			}
		}

		$categories = $this->getState('filter.product_category');

		if (!empty($categories))
		{
			if (is_array($categories))
			{
				$categories = ArrayHelper::toInteger($categories);
				$id        .= implode(',', $categories);
			}
			else
			{
				$id .= (int) $categories;
			}
		}

		$notInProducts = $this->getState('filter.notInProducts');
		$notInProducts = ArrayHelper::toInteger($notInProducts);

		if (!empty($notInProducts))
		{
			$id .= implode(',', $notInProducts);
		}

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->from($db->qn('#__redshopb_product', 'p'));

		$isTotal = $this->getState('list.isTotal', false);

		if ($isTotal)
		{
			$query->select('COUNT(*)');
		}
		else
		{
			$select = array(
				'p.*',
				'IFNULL(c.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company',
				$db->qn('c.asset_id', 'company_asset_id'),
				$db->qn('t.alias', 'template_code'),
				$db->qn('um.alias', 'unit_measure_code'),
				$db->qn('m.name', 'manufacturer_name')
			);

			$query->select($this->getState('list.select', $select))
				->leftJoin(
					$db->qn('#__redshopb_company', 'c')
					. ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('p.company_id')
					. ' AND ' . $db->qn('c.deleted') . ' = 0'
				)
				->join('left', $db->qn('#__redshopb_template', 't') . ' ON ' . $db->qn('p.template_id') . ' = ' . $db->qn('t.id'))
				->join('left', $db->qn('#__redshopb_unit_measure', 'um') . ' ON ' . $db->qn('p.unit_measure_id') . ' = ' . $db->qn('um.id'))
				->join('left', $db->qn('#__redshopb_manufacturer', 'm') . ' ON ' . $db->qn('p.manufacturer_id') . ' = ' . $db->qn('m.id'));

			$collectionId = $this->getState('include.collection_price', 0);

			if ($collectionId)
			{
				$query->select('cpx.price')
					->leftJoin(
						$db->qn('#__redshopb_collection_product_xref', 'cpx')
						. ' ON cpx.product_id = p.id AND cpx.collection_id = '
						. (int) $collectionId
					);
			}
			else
			{
				$query->select(
					array(
						$db->qn('pp.price'),
						$db->qn('pp.retail_price')
					)
				)
					->join('left', $db->qn('#__redshopb_product_price', 'pp') . ' ON ' .
						$db->qn('pp.type_id') . ' = ' . $db->qn('p.id') . ' AND ' .
						$db->qn('pp.type') . ' = ' . $db->q('product') . ' AND ' .
						$db->qn('pp.sales_type') . ' = ' . $db->q('all_customers') . ' AND ' .
						$db->qn('pp.sales_code') . ' = ' . $db->q('') . ' AND ' .
						$db->qn('pp.country_id') . ' IS NULL AND ' .
						$db->qn('pp.starting_date') . ' = ' . $db->q('0000-00-00 00:00:00') . ' AND ' .
						$db->qn('pp.ending_date') . ' = ' . $db->q('0000-00-00 00:00:00') . ' AND ' .
						$db->qn('pp.quantity_min') . ' IS NULL AND ' .
						$db->qn('pp.quantity_max') . ' IS NULL AND ' .
						'((' . $db->qn('c.currency_id') . ' IS NULL AND ' .
						$db->qn('pp.currency_id') . ' = ' . (int) RedshopbApp::getDefaultCurrency()->getId() . ') OR ' .
						'(' . $db->qn('c.currency_id') . ' IS NOT NULL AND ' .
						$db->qn('c.currency_id') . ' = ' . $db->qn('pp.currency_id') . '))'
					);
			}
		}

		// List products without description
		if ($this->getState('filter.product_description') == 1)
		{
			$query->leftjoin('#__redshopb_product_descriptions AS pd ON pd.product_id = p.id');
			$query->where('pd.product_id IS NULL');
		}

		// Filter by product IDs
		$productIds = $this->getState('filter.product_id');

		// Extra filter by id
		if (!$productIds)
		{
			$productIds = RedshopbHelperDatabase::filterInteger($this->getState('filter.id'));
		}

		$productIds = ArrayHelper::toInteger($productIds);

		if (count($productIds) > 0)
		{
			$query->where('p.id IN (' . implode(',', $productIds) . ')');
		}

		// Filter by company
		$company = $this->getState('filter.product_company', $this->getState('filter.company_id'));

		if (is_numeric($company) && $company > 0)
		{
			$query->where('p.company_id = ' . (int) $company);
		}
		elseif ($company == 'null')
		{
			$query->where('p.company_id IS NULL');
		}

		// Filter by main category
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId) && $categoryId > 0)
		{
			$query->where('p.category_id = ' . (int) $categoryId);
		}
		elseif ($categoryId == 'null')
		{
			$query->where('p.category_id IS NULL');
		}

		// Filter by manufacturer
		$filterManufacturer = (int) $this->getState('filter.product_manufacturer', $this->getState('filter.manufacturer_id'));

		if ($filterManufacturer)
		{
			$query->where($db->qn('p.manufacturer_id') . ' = ' . $filterManufacturer);
		}

		// Filter by filter field set
		$filterFilterFieldset = (int) $this->getState('filter.filter_fieldset_id');

		if ($filterFilterFieldset)
		{
			$query->where($db->qn('p.filter_fieldset_id') . ' = ' . $filterFilterFieldset);
		}

		// Filter by featured
		$featured = $this->getState('filter.featured');

		if (is_string($featured) && ($featured == 'true' || $featured == 'false'))
		{
			$featured = ($featured == 'false' ? 0 : 1);
		}

		if (!empty($featured))
		{
			$query->where('p.featured = ' . (int) $featured);
		}

		// Filter by tag
		$tag = $this->getState('filter.product_tag');

		if ($tag)
		{
			if (is_array($tag))
			{
				$tag = ArrayHelper::toInteger($tag);
				$tag = implode(',', $tag);
			}
			else
			{
				$tag = (int) $tag;
			}

			if (!empty($tag))
			{
				$query->innerJoin($db->qn('#__redshopb_product_tag_xref', 'tcx') . ' ON tcx.product_id = p.id')
					->where('tcx.tag_id IN (' . $tag . ')');
			}
		}

		// Filter by category
		$category = $this->getState('filter.product_category');

		if ($category)
		{
			if (is_array($category))
			{
				$category = ArrayHelper::toInteger($category);
			}
			else
			{
				if ($category == 'null')
				{
					$category = array($category);
				}
				else
				{
					$category = array((int) $category);
				}
			}

			$category = implode(',', $category);

			if (!empty($category) && $category != 'null')
			{
				$query->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON pcx.product_id = p.id')
					->where('pcx.category_id IN (' . $category . ')');
			}
			elseif (!empty($category) && $category == 'null')
			{
				$query->where($db->qn('p.category_id') . 'IS NULL');
			}
		}

		// Limit companies based on allowed permissions (main warehouse or allowed companies' products)
		$query = $this->filterProductCompany($query);

		// Filter by state
		$state = $this->getState('list.product_state', $this->getState('filter.product_state', $this->getState('filter.state')));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('p.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('p.state') . ' = 1');
		}

		// Filter by discontinued
		$discontinued = $this->getState(
			'list.product_discontinued', $this->getState('filter.product_discontinued', $this->getState('filter.discontinued'))
		);

		if ($discontinued == '0' || $discontinued == 'false')
		{
			$query->where($db->qn('p.discontinued') . ' = 0');
		}
		elseif ($discontinued == '1' || $discontinued == 'true')
		{
			$query->where($db->qn('p.discontinued') . ' = 1');
		}

		// Filter by service
		$service = $this->getState(
			'list.product_service', $this->getState('filter.product_service', $this->getState('filter.service'))
		);

		if ($service == '0' || $service == 'false')
		{
			$query->where($db->qn('p.service') . ' = 0');
		}
		elseif ($service == '1' || $service == 'true')
		{
			$query->where($db->qn('p.service') . ' = 1');
		}

		// Filter by template (code - alias)
		$templateCode = $this->getState('filter.template_code', '');

		if ($templateCode != '')
		{
			$query->where('t.alias = ' . $db->q($templateCode));
		}

		// Filter by unit of measure (code - alias)
		$unitMeasureCode = $this->getState('filter.unit_measure_code', '');

		if ($unitMeasureCode != '')
		{
			$query->where('um.alias = ' . $db->q($unitMeasureCode));
		}

		// Filter by product IDs NOT IN
		$notInProducts = $this->getState('filter.notInProducts');
		$notInProducts = ArrayHelper::toInteger($notInProducts);

		if (count($notInProducts) > 0)
		{
			$query->where('p.id NOT IN (' . implode(',', $notInProducts) . ')');
		}

		// Collection
		$forceCollection = $this->getState('list.force_collection', false);
		$collection      = $this->getState('filter.product_collection');

		if ($forceCollection && $collection == '')
		{
			$form            = $this->getForm();
			$userCollections = RedshopbHelperCollection::getUserCollections();

			if ($userCollections)
			{
				$form->setValue('product_collection', 'filter', $userCollections[0]->identifier);
				$this->setState('filter.product_collection', $userCollections[0]->identifier);
			}
		}

		$collection = $this->getState('filter.product_collection');

		if (!empty($collection) || $forceCollection)
		{
			if (empty($collection))
			{
				$collection = 0;
			}

			if (!$isTotal)
			{
				$query->select($db->qn('cpx.ordering', 'collection_order'));
			}

			$query->join('inner', $db->qn('#__redshopb_collection_product_xref', 'cpx') . ' ON cpx.product_id = p.id')
				->where('cpx.collection_id = ' . (int) $collection);
		}

		// SKU search (exclusive)
		$sku = $this->getState('filter.sku');

		if (!empty($sku))
		{
			$query->where($db->qn('p.sku') . ' = ' . $db->q($sku));
		}

		// SKU search by array
		$skuArray = $this->getState('filter.sku_array');

		if (!empty($skuArray))
		{
			$skuArray = json_decode($skuArray, true);

			if (!empty($skuArray))
			{
				$query->where($db->qn('p.sku') . ' IN (' . implode(',', RHelperArray::quote($skuArray)) . ')');
			}
		}

		// Manufacturer SKU search (exclusive)
		$manufacturerSku = $this->getState('filter.manufacturer_sku');

		if (!empty($manufacturerSku))
		{
			$query->where(
				$db->qn('p.manufacturer_sku') . ' = ' . $db->q($manufacturerSku)
			);
		}

		// Related SKU search (exclusive)
		$relatedSku = $this->getState('filter.related_sku');

		if (!empty($relatedSku))
		{
			$query->where(
				$db->qn('p.related_sku') . ' = ' . $db->q($relatedSku)
			);
		}

		// Filter by multiple company ids
		$companyIds = $this->getState('filter.company_ids', null);

		if (!is_null($companyIds))
		{
			$parts = array($db->qn('p.company_id') . ' IN (' . implode(',', $db->q($companyIds)) . ')');

			if ($this->getState('list.allow_mainwarehouse_products'))
			{
				$parts[] = $db->qn('p.company_id') . ' IS NULL';
			}

			$query->where('(' . implode(' OR ', $parts) . ')');
		}

		$campaign = $this->getState('filter.campaign', null);

		if ($campaign)
		{
			$query->where($db->qn('p.campaign') . ' = ' . $db->q(1));
		}

		// Filter above some product id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('p.id') . ' > ' . (int) $previousId);
		}

		// Filter search
		$search = $this->getState('filter.search_products', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'p.name LIKE ' . $search,
				'p.sku LIKE ' . $search,
				'p.manufacturer_sku LIKE ' . $search,
				'p.related_sku LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Exclude products set up as fee, or freight, or service
		if ($this->getState('list.disallow_freight_fee_products', false))
		{
			$query2 = $db->getQuery(true)
				->select($db->qn('freight_product_id'))
				->from($db->qn('#__redshopb_company'))
				->where($db->qn('deleted') . ' = 0')
				->where($db->qn('freight_product_id') . ' IS NOT NULL');
			$query->where('p.id NOT IN (' . $query2 . ')');

			$query2->clear()
				->select($db->qn('product_id'))
				->from($db->qn('#__redshopb_fee'))
				->where($db->qn('product_id') . ' IS NOT NULL');
			$query->where('p.id NOT IN (' . $query2 . ')');

			$query->where('p.service = 0');
		}

		$offerId = $this->getState('list.offer', 0);

		if ($offerId)
		{
			// Offer include
			if ($this->getState('list.offerInclude', true))
			{
				if (!$isTotal)
				{
					$subQuery = $db->getQuery(true)
						->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
						->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
						->leftJoin(
							$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id'
						)
						->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
						->where('off.product_item_id = piavx.product_item_id')
						->order('pav.ordering');

					$query->select(
						array(
							$db->qn('off.id', 'offer_item_id'),
							$db->qn('off.product_item_id'),
							$db->qn('off.quantity'),
							$db->qn('off.unit_price'),
							$db->qn('off.subtotal'),
							$db->qn('off.discount_type'),
							$db->qn('off.discount'),
							$db->qn('off.total')
						)
					)
						->select('CONCAT_WS(' . $db->q('-') . ', ' . $db->qn('p.sku') . ', (' . $subQuery . ')) AS ' . $db->qn('product_item_sku'));
				}

				$query->leftJoin($db->qn('#__redshopb_offer_item_xref', 'off') . ' ON p.id = off.product_id')
					->where('off.offer_id = ' . (int) $offerId);
			}

			// Offer exclude
			else
			{
				$query->leftJoin($db->qn('#__redshopb_offer_item_xref', 'off') . ' ON p.id = off.product_id')
					->where('(off.offer_id != ' . (int) $offerId . ' OR off.offer_id IS NULL)');
			}
		}

		$modifiedDate = $this->getState('filter.modified_date');

		if (null !== $modifiedDate)
		{
			$query->where($db->qn('p.modified_date') . ' = ' . $db->q($modifiedDate));
		}

		$minModifiedDate = $this->getState('filter.min_modified_date');

		if (null !== $minModifiedDate)
		{
			$query->where($db->qn('p.modified_date') . ' > ' . $db->q($minModifiedDate));
		}

		if (!$isTotal)
		{
			// Ordering
			$orderList     = $this->getState('list.ordering');
			$directionList = $this->getState('list.direction');

			$order     = !empty($orderList) ? $orderList : 'p.name';
			$direction = !empty($directionList) ? $directionList : 'ASC';
			$query->order($db->escape($order) . ' ' . $db->escape($direction));
		}

		if (!$this->getState('list.forOfferItemsView', false))
		{
			$query->group($db->qn('p.id'));
		}

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQueryOnly()
	{
		return $this->getListQuery();
	}

	/**
	 * Filtering related to product's company
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return JDatabaseQuery
	 */
	public function filterProductCompany(JDatabaseQuery $query)
	{
		if ($this->getState('filter.ignore_acl'))
		{
			return $query;
		}

		$user  = Factory::getUser();
		$db    = $this->_db;
		$parts = array();

		$companyId = $this->getState('list.company_id');

		if ($companyId)
		{
			if ($this->getState('list.allow_parent_companies_products'))
			{
				$allowedCompanies = RedshopbEntityCompany::getInstance($companyId)->getTree();
			}
			else
			{
				$allowedCompanies   = RedshopbEntityCompany::getInstance($companyId)->getChildrenIds();
				$allowedCompanies[] = $companyId;
			}

			$allowedCompanies = implode(',', $allowedCompanies);
		}
		elseif ($this->getState('list.allow_parent_companies_products'))
		{
			$allowedCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
		}
		else
		{
			$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);
		}

		$parts[] = $db->qn('p.company_id') . ' IN (' . $allowedCompanies . ')';

		if ($this->getState('list.allow_mainwarehouse_products') || RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$parts[] = $db->qn('p.company_id') . ' IS NULL';
		}

		$query->where('(' . implode(' OR ', $parts) . ')');

		return $query;
	}

	/**
	 * Get the descriptions
	 *
	 * @param   array  $id  ID of product
	 *
	 * @return  array  An array of attributes to display
	 */
	public function getDescriptions($id)
	{
		$db = $this->_db;

		$query = $db->getQuery(true)
			->select('*')
			->from('#__redshopb_product_descriptions')
			->where($db->qn('product_id') . ' = ' . (int) $id);

		$db->setQuery($query);

		$descriptions = $db->loadAssocList('main_attribute_value_id');

		return $descriptions;
	}

	/**
	 * Get the parent entity Id (category)
	 *
	 * @param   int  $id         Product Id
	 * @param   int  $applicant  Applicant id
	 *
	 * @return  integer
	 */
	public function getParentEntityId($id, $applicant = 0)
	{
		$product = RedshopbHelperProduct::loadProduct($id);

		if (!$product
			|| (empty($product->categories) && empty($product->category_id)))
		{
			return 0;
		}

		if ($applicant
			&& (in_array($applicant, $product->categories) || $product->category_id == $applicant))
		{
			$categoryId = $applicant;
		}
		else
		{
			$categoryId = $product->category_id ?? current($product->categories);
		}

		return $categoryId;
	}

	/**
	 * Overridden to convert stock_upper and stock_lower levels to decimals, if the unit of measure requires it
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

		foreach ($items AS $item)
		{
			$item->stock_upper_level = RedshopbHelperProduct::decimalFormat($item->stock_upper_level, $item->id);
			$item->stock_lower_level = RedshopbHelperProduct::decimalFormat($item->stock_lower_level, $item->id);
		}

		return $items;
	}
}
