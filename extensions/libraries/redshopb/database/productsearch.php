<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Address Model
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 * @since       1.0
 */
class RedshopbDatabaseProductsearch
{
	/**
	 * @var string
	 */
	protected $itemKey;

	/**
	 * @var integer
	 */
	protected $customerId;

	/**
	 * @var string
	 */
	protected $customerType;

	/**
	 * @var string
	 */
	protected $term;

	/**
	 * @var array
	 */
	protected $criteria = array();

	/**
	 * @var boolean
	 */
	protected $useSimpleSearch = false;

	/**
	 * @var boolean
	 */
	protected $hasFilters = false;

	/**
	 * @var RedshopbEntityConfig
	 */
	protected $redShopConfig;

	/**
	 * @var boolean
	 */
	protected $categoryNameFiltered = false;

	/**
	 * @var boolean
	 */
	protected $productNameFiltered = false;

	/**
	 * @var boolean
	 */
	protected $productDescriptionFiltered = false;

	/**
	 * @var boolean
	 */
	protected $additionalFieldsFiltered = false;

	/**
	 * @var boolean
	 */
	protected $productSKUFiltered = false;

	/**
	 * @var array
	 */
	protected $commonStates = array(
		'p.state'        => '= 1',
		'p.discontinued' => '= 0',
		'p.service'      => '= 0',
		'p.category_id'  => 'IS NOT NULL'
	);

	/**
	 * @var RedshopbDatabaseIndexerQuery|null
	 */
	public $indexerQuery;

	/**
	 * RedshopbModelSearch constructor.
	 *
	 * @param   array  $options  Options list.
	 *
	 * @since   1.13.0
	 * @throws  Exception
	 */
	public function __construct($options = array())
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$options = new Registry($options);

		$this->itemKey      = (string) $options->get('itemKey', $app->getUserState('shop.itemKey', 0));
		$this->customerType = (string) $options->get('customer_type', $app->getUserState('shop.customer_type', ''));
		$this->customerId   = (int) $options->get('customer_id', $app->getUserState('shop.customer_id', 0));
		$this->term         = (string) $options->get('term',
			$app->getUserState(
				'mod_filter.search.' . $this->itemKey,
				$input->getString(
					'mod_redshopb_search_searchword',
					$input->getString(
						'search',
						''
					)
				)
			)
		);

		// Default useSimpleSearch is false
		$this->useSimpleSearch = (boolean) $options->get('useSimpleSearch', false);
		$this->redShopConfig   = RedshopbEntityConfig::getInstance();
		$this->criteria        = (array) $this->redShopConfig->get('product_search_criterias_table', array(), 'array');
		$searchSynonyms        = true;

		if (!$this->redShopConfig->getBool('product_search_synonyms', false) || $this->useSimpleSearch)
		{
			$searchSynonyms = false;
		}

		$this->indexerQuery = new RedshopbDatabaseIndexerQuery(
			array(
				'input' => $this->term,
				'search_synonyms' => $searchSynonyms,
				'use_stem' => $this->redShopConfig->getInt('stem', 0),
				'stemmer' => $this->redShopConfig->getString('stemmer', 'snowball')
			)
		);
	}

	/**
	 * Get the current customer ID
	 *
	 * @return integer|mixed
	 */
	public function getCustomerId()
	{
		return $this->customerId;
	}

	/**
	 * Get the current customer type
	 *
	 * @return mixed|string
	 */
	public function getCustomerType()
	{
		return $this->customerType;
	}

	/**
	 * Method to manually set the search term
	 *
	 * @param   string  $term  to search for
	 *
	 * @return $this
	 *
	 * @since  1.13.0
	 */
	public function setTerm($term)
	{
		$this->term     = $term;
		$searchSynonyms = true;

		if (!$this->redShopConfig->getBool('product_search_synonyms', false) || $this->useSimpleSearch)
		{
			$searchSynonyms = false;
		}

		$this->indexerQuery = new RedshopbDatabaseIndexerQuery(
			array(
				'input' => $this->term,
				'search_synonyms' => $searchSynonyms,
				'use_stem' => $this->redShopConfig->getInt('stem', 0),
				'stemmer' => $this->redShopConfig->getString('stemmer', 'snowball')
			)
		);

		return $this;
	}

	/**
	 * Method to check if there is a search term
	 *
	 * @return boolean
	 *
	 * @since  1.13.0
	 */
	public function hasTerm()
	{
		if (empty($this->term))
		{
			return false;
		}

		if (!($this->indexerQuery instanceof RedshopbDatabaseIndexerQuery))
		{
			return true;
		}

		foreach ($this->indexerQuery->included AS $term)
		{
			if (!empty($term->term))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to set the search criteria
	 *
	 * @param   array|string  $criteria  Criteria list
	 *
	 * @see RedshopbDatabaseProductsearch::getProductIdsBySearchCriteria for valid values
	 * @return $this
	 *
	 * @since  1.13.0
	 */
	public function setCriteria($criteria)
	{
		if (!is_array($criteria))
		{
			$criteria = array($criteria);
		}

		$firstCriteria = reset($criteria);

		// Back compatibility with old criteria
		if (!is_object($firstCriteria))
		{
			$compatibility = new RedshopbDatabaseProductsearchCompatibility($criteria);
			$criteria      = $compatibility->getNewFormatCriteria();
		}

		$this->criteria = $criteria;

		return $this;
	}

	/**
	 * Method to add search criteria to the existing list
	 *
	 * @param   mixed  $criteria  Criteria list
	 *
	 * @see RedshopbDatabaseProductsearch::getProductIdsBySearchCriteria for valid values
	 * @return $this
	 *
	 * @since  1.13.0
	 */
	public function addCriteria($criteria)
	{
		if (is_object($criteria))
		{
			$this->criteria[] = $criteria;
		}

		// Back compatibility with old criteria
		elseif (is_string($criteria))
		{
			$compatibility  = new RedshopbDatabaseProductsearchCompatibility(array($criteria));
			$this->criteria = array_merge($this->criteria, $compatibility->getNewFormatCriteria());
		}

		return $this;
	}

	/**
	 * Method to check if search has any criteria
	 *
	 * @return boolean
	 */
	public function hasCriteria()
	{
		return (!empty($this->criteria));
	}

	/**
	 * Method to check if this is a simple search
	 *
	 * @return boolean
	 */
	public function isSimpleSearch()
	{
		return $this->useSimpleSearch;
	}

	/**
	 * Method to get a count of all products that match the search term and all current filters
	 * We'll use this when getting counts for manufacturers, categories and filters
	 *
	 * @param   int     $collectionId  Collection id
	 * @param   bool    $setHash       Set hash name instead ids
	 * @param   string  $skipSection   Skip section
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since  1.13.0
	 */
	public function getFilteredProductQuery($collectionId = null, $setHash = false, $skipSection = '')
	{
		$db    = Factory::getDbo();
		$query = $this->getBaseSearchQuery($this->customerId, $this->customerType);

		$query->clear('select');
		$query->select('p.id AS product_id');
		$filteredProductIds = $this->getFilteredProductIds($this->customerId, $this->customerType, $collectionId, $skipSection);

		if (!empty($filteredProductIds) && $this->hasFilters)
		{
			list($ids, , $hash) = explode(':', $filteredProductIds);

			if ($setHash)
			{
				$query->where($db->qn('p.id') . ' IN (' . $hash . ')');
			}
			else
			{
				$query->where($db->qn('p.id') . ' IN (' . $ids . ')');
			}
		}

		return $query;
	}

	/**
	 * Set model states
	 *
	 * @param   RedshopbModel  $model         Current model
	 * @param   int            $collectionId  Collection id
	 *
	 * @return  void
	 */
	public function setModelStates($model, $collectionId = null)
	{
		$filteredProductIds = $this->getFilteredProductIds($this->customerId, $this->customerType, $collectionId);

		if (!empty($filteredProductIds) && $this->hasFilters)
		{
			list($ids, $count, $hash) = explode(':', $filteredProductIds);
			$productSearchHash        = $model->getState('productsearch.ids', array());
			$productSearchHash[$hash] = $ids;
			$model->setState('productsearch.hash', $productSearchHash);
		}
	}

	/**
	 * Method to get a count of all products that match the search term and all current filters
	 *
	 * @param   integer  $collectionId  Collection id
	 *
	 * @return  integer
	 */
	public function getProductCount($collectionId = 0)
	{
		$filteredProductIds = $this->getFilteredProductIds($this->customerId, $this->customerType, $collectionId);

		return (int) explode(':', $filteredProductIds)[1];
	}

	/**
	 * Method to get product ids that match the search term and all current filters
	 *
	 * @param   array|int  $collectionId  Collection id/ids
	 * @param   string     $skipSection   Skip section
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 */
	public function getJustFilteredProductIds($collectionId = array(), $skipSection = '')
	{
		$filteredProductIds = $this->getFilteredProductIds($this->customerId, $this->customerType, $collectionId, $skipSection);

		return explode(':', $filteredProductIds)[0];
	}

	/**
	 * Method to get products for a product list view
	 *
	 * @param   integer  $start         Start offset
	 * @param   integer  $limit         Limit of results to return
	 * @param   integer  $collectionId  Collection id
	 * @param   boolean  $isQuickOrder  Is it a quick order?
	 *
	 * @return  object
	 * @throws  Exception
	 */
	public function getProductForProductListLayout($start = 0, $limit = 0, $collectionId = 0, $isQuickOrder = false)
	{
		$db    = Factory::getDbo();
		$query = $this->getBaseSearchQuery();
		$query->clear('select');

		$useCollection = RedshopbHelperShop::inCollectionMode(
			RedshopbEntityCompany::getInstance(
				RedshopbHelperCompany::getCompanyIdByCustomer(
					$this->customerId, $this->customerType
				)
			)
		);

		if ($collectionId && $useCollection)
		{
			$query->select(
				array(
					'p.*',
					'px.price AS collectionPrice'
				)
			);
		}
		else
		{
			$query->select('p.*');
		}

		$groupNeeded = false;

		$volumePriceQuery = $db->getQuery(true);
		$volumePriceQuery->select('vp.id')
			->from($db->qn('#__redshopb_product_price', 'vp'))
			->where($db->qn('vp.type') . ' = ' . $db->q('product'))
			->where($db->qn('vp.type_id') . ' = ' . $db->qn('p.id'))
			->where('(' . $db->qn('vp.quantity_min') . ' IS NOT NULL OR ' . $db->qn('vp.quantity_max') . ' IS NOT NULL)');
		$query->select('(' . $volumePriceQuery . ' LIMIT 0, 1) AS hasVolumePricing');

		if ($collectionId && $useCollection)
		{
			$query->join(
				'inner',
				$db->qn('#__redshopb_collection_product_xref') . ' AS px' .
				' ON ' . $db->qn('p.id') . ' = ' . $db->qn('px.product_id')
			);
		}

		$filteredProductIds = $this->getFilteredProductIds($this->customerId, $this->customerType, $collectionId);
		$hash               = '';
		$ids                = '';

		if (!empty($filteredProductIds) && strpos($filteredProductIds, ':') !== false)
		{
			list($ids, , $hash) = explode(':', $filteredProductIds);
			$query->where($db->qn('p.id') . ' IN (' . $hash . ')');
		}

		// Checking if logged in user is an E-Commerce user to get the favorite lists
		$user      = Factory::getUser();
		$rsbUserId = RedshopbEntityUser::loadActive(true)->get('id');

		if ((0 == $rsbUserId || '' == $rsbUserId) && RedshopbHelperACL::isSuperAdmin($user->id))
		{
			$rsbUserId = $this->getCustomerId();
		}

		if ($rsbUserId)
		{
			$query->select('COUNT(' . $db->qn('fl.id') . ') AS ' . $db->qn('favoritelists'))
				->leftJoin($db->qn('#__redshopb_favoritelist_product_xref', 'flpx') . ' ON ' . $db->qn('flpx.product_id') . ' = ' . $db->qn('p.id'))
				->leftJoin(
					$db->qn('#__redshopb_favoritelist', 'fl') . ' ON (' .
					$db->qn('fl.id') . ' = ' . $db->qn('flpx.favoritelist_id') . ' AND (' .
					$db->qn('fl.user_id') . ' IS NULL OR ' . $db->qn('fl.user_id') . ' = ' . (int) $rsbUserId . '))'
				);
			$groupNeeded = true;
		}

		$app     = Factory::getApplication();
		$layout  = $app->getUserState('shop.layout', '');
		$sortDir = $app->getUserState('shop.show.' . $layout . '.SortByDir', 'asc');

		// Check if products with a price of 0 should be hidden and hide them.
		if ($isQuickOrder && RedshopbEntityConfig::getInstance()->get('hide_quickorder_products', '0') == 1)
		{
			$quickproductPrices = $this->getProductsPrices($this->customerId, $this->customerType);
			$companyId          = RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType);
			$currency           = RedshopbEntityCompany::getInstance($companyId)->getCurrency();
			$defaultCurrency    = RedshopbEntityConfig::getInstance()->get('default_currency');

			foreach ($quickproductPrices as $quickProductPrice)
			{
				if ($defaultCurrency !== $currency)
				{
					$allProducts[] = $quickProductPrice->{'product_id'};
				}

				if ($quickProductPrice->price > 0 || $quickProductPrice->{'price_without_discount'} > 0 || $quickProductPrice->{'fallback_price'} > 0)
				{
					continue;
				}

				$freeProducts[] = $quickProductPrice->{'product_id'};
			}

			if (!empty($allProducts))
			{
				$query->where('p.id IN ( ' . implode(',', $allProducts) . ')');
			}

			if (!empty($freeProducts))
			{
				$query->where('p.id NOT IN (' . implode(',', $freeProducts) . ')');
			}
		}

		if (!in_array($sortDir, array('asc', 'desc')))
		{
			$sortDir = 'asc';
		}

		$sortBy               = $app->getUserState('shop.show.' . $layout . '.SortBy', $this->redShopConfig->getDefaultOrderByField($layout));
		$allowedOrderByFields = $this->redShopConfig->getAllowedOrderByFields($layout);

		if (!in_array($sortBy, $allowedOrderByFields))
		{
			$sortBy = $this->redShopConfig->getDefaultOrderByField($layout);
		}

		switch ($sortBy)
		{
			case 'name':
				$sort = $db->qn('p.name') . ' ' . $sortDir;
				break;

			case 'sku':
				$sort = $db->qn('p.sku') . ' ' . $sortDir;
				break;

			case 'recent':
				$sort = $db->qn('p.date_new') . ' ' . $sortDir . ',' . $db->qn('p.created_date') . ' ' . $sortDir;
				break;

			case 'price':

				$productPrices = $this->getProductsPrices($this->customerId, $this->customerType);

				if (!empty($filteredProductIds))
				{
					$productIds = explode(',', $ids);
				}
				else
				{
					$productIds = $this->getProductIdsForProductPrices();
				}

				// @codingStandardsIgnoreStart
				if ($sortDir == 'asc')
				{
					// Move products with not existed price in the first position, it looks like 0 price and sort by asc
					$productIdOrder = array_diff($productIds, array_keys($productPrices));

					uasort(
						$productPrices,
						function ($a, $b)
						{
							if ((float) $a->price == (float) $b->price)
						{

								return 0;
							}

							return ((float) $a->price < (float) $b->price) ? -1 : 1;
						}
					);

					$productIdOrder = array_merge($productIdOrder, array_keys($productPrices));
				}
				else
				{
					uasort(
						$productPrices,
						function ($a, $b)
						{
							if ((float) $a->price == (float) $b->price)
							{
								return 0;
							}

							return ((float) $a->price > (float) $b->price) ? -1 : 1;
						}
					);

					$productIdOrder = array_keys($productPrices);
					$productIdOrder = array_merge($productIdOrder, array_diff($productIds, $productIdOrder));
				}
				// @codingStandardsIgnoreEnd

				if (!empty($productIdOrder))
				{
					$sort = 'FIELD(p.id, ' . implode(',', $productIdOrder) . ')';
				}
				else
				{
					$sort = $db->qn('p.name') . ' ' . $sortDir . ',' . $db->qn('p.sku');
				}
				break;

			case 'relevance':
				$sort = $db->qn('p.name') . ' ' . $sortDir;

				if (!empty($filteredProductIds) && strpos($filteredProductIds, ':') !== false)
				{
					$priorities = $this->getStoredSearch(false, false);

					if (!empty($priorities) && count((array) $priorities) > 1)
					{
						$cases = array('CASE');

						foreach ($priorities as $priority => $priorIds)
						{
							$cases[] = ' WHEN p.id IN (' . $priorIds . ') THEN ' . (int) $priority;
						}

						$cases[]        = ' ELSE 999 END';
						$cases          = implode('', $cases);
						$priorityHash   = md5($cases);
						$prioritySelect = $cases;

						$query->select('(' . $priorityHash . ') AS priority');
						$sort = 'priority ' . $sortDir . ',' . $db->qn('p.name') . ' ASC';
					}
				}

				break;

			case 'most_popular':
				// Since we are dealing with a sorting most popular number, default sorting should be from most largest number
				$sortDirPopular = $sortDir == 'asc' ? 'desc' : 'asc';

				$sort = $db->qn('p.hits') . ' ' . $sortDirPopular . ', ' . $db->qn('p.name') . ' ASC';

				break;

			case 'most_purchased':
				// Since we are dealing with a sorting most popular number, default sorting should be from most largest number
				$sortDirPurchased = $sortDir == 'asc' ? 'desc' : 'asc';
				$purchasedFilter  = '';

				if (!$this->redShopConfig->getInt('most_purchased_include_from_all', 1))
				{
					$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);
					$purchasedFilter  = ' AND (' . $db->qn('of.customer_company') . ' IN (' . $allowedCompanies . ')) ';
				}

				$query->select('(CASE WHEN ' . $db->qn('of.id') . ' IS NOT NULL THEN SUM(oif.quantity) ELSE 0 END) AS number_of_sold_items')
					->leftJoin($db->qn('#__redshopb_order_item', 'oif') . ' ON oif.product_id = p.id')
					->leftJoin($db->qn('#__redshopb_order', 'of') . ' ON of.id = oif.order_id' . $purchasedFilter)
					->group('p.id');

				$sort = $db->qn('number_of_sold_items') . ' ' . $sortDirPurchased . ',' . $db->qn('p.name') . ' ASC';

				break;
			case 'custom':
				$categoryId = 0;

				if ($app->input->getString('layout') == 'category')
				{
					$categoryId = $app->input->getInt('id', 0);
				}

				$query->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' . $db->qn('pcx.product_id') . ' = ' . $db->qn('p.id'));
				$query->innerJoin($db->qn('#__redshopb_category', 'cat') . ' ON ' . $db->qn('cat.id') . ' = ' . $db->qn('pcx.category_id'));
				$sort = $db->qn('cat.lft') . ' ASC,' . $db->qn('pcx.ordering') . ' ' . $sortDir . ',' . $db->qn('p.sku') . ' ASC';

				if ($categoryId > 0)
				{
					$query->where($db->qn('cat.id') . ' = ' . (int) $categoryId);
				}

				break;

			default:
				break;
		}

		if (!empty($sort))
		{
			$query->order($sort);
		}

		if ($groupNeeded)
		{
			$query->group($db->qn('p.id'));
		}

		// Avoid translation parse for product id list
		$query = $db->replacePrefix((string) $query);
		$query = str_replace($hash, $ids, $query);

		if (!empty($priorityHash) && !empty($prioritySelect))
		{
			$query = str_replace($priorityHash, $prioritySelect, $query);
		}

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$products      = $db->setQuery($query, (int) $start, (int) $limit)
			->loadObjectList('id');
		$db->translate = $oldTranslate;

		if (empty($products))
		{
			return null;
		}

		/** @var RedshopbModelShop $shopModel */
		$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
		RedshopbHelperProduct::setProduct($products);
		$preparedItems              = $shopModel->prepareItemsForShopView($products, $this->customerId, $this->customerType, $collectionId, true);
		$preparedItems->productData = $products;

		return $preparedItems;
	}

	/**
	 * Method to get categories
	 *
	 * @param   integer  $start  Start offset
	 * @param   integer  $limit  Limit of results to return
	 *
	 * @return  array
	 */
	public function getCategories($start = 0, $limit = 0)
	{
		if (!$this->indexerQuery->search)
		{
			return array();
		}

		if (!empty($this->term) && !$this->hasTerm())
		{
			return array();
		}

		$categoryIds = array();
		Factory::getApplication()->triggerEvent('onVanirGetCategories', array($this, &$categoryIds));

		if (empty($categoryIds))
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_category'))
			->where($db->qn('id') . ' IN (' . implode(',', $categoryIds) . ')')
			->where('name != ' . $db->q('ROOT'))
			->where('state = ' . $db->q(1))
			->order('FIELD(' . $db->qn('id') . ',' . implode(',', $categoryIds) . ')');

		return $db->setQuery($query, $start, $limit)
			->loadObjectList();
	}

	/**
	 * Method to get all productIds in the current category
	 *
	 * @return mixed
	 */
	public function getCategoryProductIds()
	{
		$db          = Factory::getDbo();
		$searchQuery = $this->getBaseSearchQuery($this->customerId, $this->customerType, true);

		return $db->setQuery($searchQuery)->loadColumn();
	}

	/**
	 * Method to get a list of all product IDs matching a certain term
	 *
	 * @param   int     $customerId    id of the current customer
	 * @param   string  $customerType  type of customer
	 * @param   mixed   $collectionId  Collection id
	 * @param   string  $skipSection   Skip section
	 *
	 * @return  mixed
	 * @throws  Exception
	 */
	public function getFilteredProductIds($customerId, $customerType, $collectionId = null, $skipSection = '')
	{
		$db          = Factory::getDbo();
		$searchQuery = $this->getBaseSearchQuery($customerId, $customerType, false);

		$searchQuery->clear('from')
			->from($db->qn('#__redshopb_product', 'p') . ' FORCE INDEX (idx_state)');

		$searchQuery->leftJoin(
			$db->qn('#__redshopb_fee', 'f') . ' FORCE INDEX(#__rs_fee_fk2) ON ' . $db->qn('f.product_id') . ' = ' . $db->qn('p.id')
		)
			->where($db->qn('f.id') . ' IS NULL')
			->group($db->qn('p.id'));

		if (!empty($this->term))
		{
			if (!$this->hasTerm())
			{
				$hash = $this->getSearchHash($customerId, $customerType);

				return '0:0:@' . $hash;
			}

			$this->hasFilters = true;
			$storedSearch     = $this->getStoredSearch(true);
			$searchQuery->where($db->qn('p.id') . ' IN (' . $storedSearch . ')');
			$searchQuery->order('FIELD(p.id, ' . $storedSearch . ')');
		}

		// Filter by Fields
		$session  = Factory::getSession();
		$registry = $session->get('registry');

		if (RedshopbHelperFilter::addFilterFieldsetQuery($searchQuery, $registry, 'filter.' . $this->itemKey . '.', 'p.', 'product', $skipSection))
		{
			$this->hasFilters = true;
		}

		// Filter by category
		if ($skipSection != 'category')
		{
			$this->filterByCategory($searchQuery, $customerId, $customerType, ' FORCE INDEX (PRIMARY)');
		}

		// Filter by manufacturer
		if ($skipSection != 'manufacturer')
		{
			$this->filterByManufactures($searchQuery);
		}

		// Filter by tags
		if ($skipSection != 'tag')
		{
			$this->filterByTags($searchQuery);
		}

		// Filter by Attributes
		$this->filterByAttributes($searchQuery);

		// Filter by recent
		$this->filterByRecent($searchQuery);

		// Filter by featured
		$this->filterByFeatured($searchQuery);

		// Filter by campaign price
		$this->filterByCampaignPrice($searchQuery);

		// Filter by price range
		$this->filterByPriceRange($searchQuery, $customerId, $customerType);

		// Filter by stock
		$this->filterByStock($searchQuery);

		if (RedshopbHelperShop::inCollectionMode())
		{
			$availableCollections = RedshopbHelperCollection::getCustomerCollectionsForShop();

			if (empty($collectionId))
			{
				$collectionId = $availableCollections;
			}
			else
			{
				$collectionId = array_intersect((array) $collectionId, $availableCollections);
			}

			if (empty($collectionId))
			{
				$collectionId = [0];
			}

			$collectionId = ArrayHelper::toInteger((array) $collectionId);

			$searchQuery->innerJoin(
				$db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON ' . $db->qn('wpx.product_id') . ' = ' . $db->qn('p.id')
			)
				->where($db->qn('wpx.collection_id') . ' IN (' . implode(',', $collectionId) . ')');
		}

		static $queryResults = array();

		if (!empty($this->term))
		{
			// Avoid translation parse for product id list
			$searchQuery = $db->replacePrefix((string) $searchQuery);
			$searchQuery = str_replace($this->getStoredSearch(true), $this->getStoredSearch(), $searchQuery);
		}

		$hash = md5((string) $searchQuery);

		if (array_key_exists($hash, $queryResults))
		{
			return $queryResults[$hash];
		}

		$oldTranslate       = $db->translate;
		$db->translate      = false;
		$filteredProductIds = $db->setQuery($searchQuery)
			->loadColumn();
		$db->translate      = $oldTranslate;

		if (empty($filteredProductIds))
		{
			$queryResults[$hash] = '0:0:@' . $hash;

			return $queryResults[$hash];
		}

		$queryResults[$hash] = implode(',', $filteredProductIds) . ':' . count($filteredProductIds) . ':@' . $hash;

		return $queryResults[$hash];
	}

	/**
	 * Method to get a list of all product IDs matching a certain term from the cache
	 *
	 * @param   bool  $returnHash     Return hash
	 * @param   bool  $returnIdsList  Return ids list or array ids sorted by priority
	 *
	 * @return string|array
	 */
	public function getStoredSearch($returnHash = false, $returnIdsList = true)
	{
		static $searchResults = array();

		$customerId   = $this->customerId;
		$customerType = $this->customerType;

		$key = $this->getSearchHash($customerId, $customerType);

		if (array_key_exists($key, $searchResults))
		{
			if ($returnHash)
			{
				return $key;
			}

			return $this->cacheToFormat($searchResults[$key], $returnIdsList);
		}

		$now    = new Date;
		$result = $this->loadDbCache($key, $now);

		if (null !== $result)
		{
			$searchResults[$key] = json_decode($result);

			if ($returnHash)
			{
				return $key;
			}

			return $this->cacheToFormat($searchResults[$key], $returnIdsList);
		}

		$results = null;

		if ($this->indexerQuery->search)
		{
			$results = $this->getProductIdsBySearchCriteria($customerId, $customerType);
		}

		// There should never be an empty result set
		if (empty($results))
		{
			$results = array(0);
		}

		$productSet     = json_encode($results);
		$productSetHash = '@' . md5($productSet);
		$db             = Factory::getDbo();

		try
		{
			$cacheQuery = $db->getQuery(true);
			$cacheQuery->insert('#__redshopb_word_synonym_search_sets')
				->set($db->qn('cache') . ' = ' . $db->q($now->toSql()))
				->set($db->qn('phrase') . ' = ' . $db->q($this->term))
				->set($db->qn('hash') . ' = ' . $db->q($key))
				->set($db->qn('product_set') . ' = ' . $db->q($productSet));

			// Avoid translation parse for product id list
			$cacheQuery = $db->replacePrefix((string) $cacheQuery);
			$cacheQuery = str_replace($productSetHash, $productSet, $cacheQuery);

			$db->transactionStart();
			$this->executeSearchQuery($cacheQuery);
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
		}

		$searchResults[$key] = $results;

		if ($returnHash)
		{
			return $key;
		}

		return $this->cacheToFormat($searchResults[$key], $returnIdsList);
	}

	/**
	 * Method to get a md5 hash based on the input vars
	 *
	 * @param   int     $customerId    id of the current customer
	 * @param   string  $customerType  type of customer
	 *
	 * @return string hash key
	 */
	protected function getSearchHash($customerId, $customerType)
	{
		$hashKey = array($this->term, $customerId, $customerType);

		$hashKey[] = $this->redShopConfig->getBool('product_search_stop_if_found', false);
		$hashKey[] = (int) $this->useSimpleSearch;
		$hashKey[] = serialize($this->criteria);

		if (!$this->useSimpleSearch)
		{
			$hashKey[] = $this->redShopConfig->getBool('product_search_synonyms', false);
		}

		if ($this->redShopConfig->getInt('stem', 1))
		{
			$hashKey[] = $this->redShopConfig->getString('stemmer', 'snowball');
		}

		return md5(strtolower(implode('.', $hashKey)));
	}

	/**
	 * Method to load a result set from the db cache
	 *
	 * @param   string  $key  hash key
	 * @param   Date    $now  Date instance of the current time
	 *
	 * @return null|string
	 */
	protected function loadDbCache($key, $now)
	{
		$db         = Factory::getDbo();
		$cacheQuery = $db->getQuery(true);

		// 30 minutes cache
		$expired = new Date($now->toUnix() - 1800);

		// First Clean the cache
		$cacheQuery->delete('#__redshopb_word_synonym_search_sets')
			->where($db->qn('cache') . '<= ' . $db->q($expired->toSql()));

		$db->setQuery($cacheQuery)->execute();

		$cacheQuery->clear()
			->select('product_set')
			->from('#__redshopb_word_synonym_search_sets')
			->where($db->qn('hash') . ' = ' . $db->q($key));

		return $db->setQuery($cacheQuery)->loadResult();
	}

	/**
	 * Method to optionally convert the cache contents to a string of ids
	 *
	 * @param   array    $cacheContent   content of the cache
	 * @param   boolean  $returnIdsList  should we return as a list of ids or the original array
	 *
	 * @return string|array
	 */
	protected function cacheToFormat($cacheContent, $returnIdsList)
	{
		if ($returnIdsList)
		{
			$return = array();

			foreach ($cacheContent as $ids)
			{
				$return[] = $ids;
			}

			return implode(',', $return);
		}

		return $cacheContent;
	}

	/**
	 * Get Searchable ExtraFields
	 *
	 * @return  array
	 */
	public function getSearchableExtraFields()
	{
		static $extraFields = null;

		if (!is_null($extraFields))
		{
			return $extraFields;
		}

		$db         = Factory::getDbo();
		$fieldQuery = $db->getQuery(true)
			->select('f.id, t.value_type')
			->from($db->qn('#__redshopb_field', 'f'))
			->leftJoin($db->qn('#__redshopb_type', 't') . ' ON f.type_id = t.id')
			->where('f.scope = ' . $db->q('product'))
			->where('f.searchable_frontend = 1');

		$extraFields = $db->setQuery($fieldQuery)->loadObjectList('id');

		if (!$extraFields)
		{
			$extraFields = array();
		}

		return $extraFields;
	}

	/**
	 * Method to disable translation parsing and execute a query
	 *
	 * @param   JDatabaseQuery|string  $query  Valid SQL query object or string
	 *
	 * @return  void
	 */
	protected function executeSearchQuery($query)
	{
		$db = Factory::getDbo();

		if (!is_string($query))
		{
			// Avoid translation parse for product id list
			$query = $db->replacePrefix((string) $query);
		}

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$db->setQuery($query)
			->execute();
		$db->translate = $oldTranslate;
	}

	/**
	 * Method to get a list of product IDs based on search term
	 *
	 * @param   int     $customerId    Id of the current customer
	 * @param   string  $customerType  Type of customer
	 *
	 * @return  array
	 */
	protected function getProductIdsBySearchCriteria($customerId, $customerType)
	{
		$productIds = array();

		Factory::getApplication()->triggerEvent('onVanirGetProductIdsBySearchCriteria', array($this, &$productIds, $customerId, $customerType));

		return $productIds;
	}

	/**
	 * Method to get a structured array of search criteria
	 *
	 * @return array|mixed
	 */
	public function getSearchCriteria()
	{
		$searchableExtraFields = $this->getSearchableExtraFields();
		$priority              = 1;
		$criteria              = $this->criteria;

		// Double check ability allowed extra fields inside search
		foreach ($criteria as $priority => $fields)
		{
			foreach ($fields as $key => $field)
			{
				if (!is_numeric($field->name))
				{
					continue;
				}

				if (array_key_exists($field->name, $searchableExtraFields))
				{
					unset($searchableExtraFields[$field->name]);

					continue;
				}

				unset($criteria[$priority]->{$field->name});
			}
		}

		if (empty($searchableExtraFields))
		{
			return $criteria;
		}

		$priority++;
		$criteria[$priority] = new stdClass;

		foreach ($searchableExtraFields as $key => $value)
		{
			$criteria[$priority]->{$key} = (object) array(
				'name' => $key,
				'synonym' => -1,
				'stem' => -1,
				'method' => -1
			);
		}

		return $criteria;
	}

	/**
	 * Method to get a list of product IDs based on a simple search
	 *
	 * @param   int     $customerId    Id of the current customer
	 * @param   string  $customerType  Type of customer
	 *
	 * @return  array
	 */
	public function simpleSearch($customerId, $customerType)
	{
		$db            = Factory::getDbo();
		$mainQuery     = $this->getBaseSearchQuery($customerId, $customerType);
		$templateQuery = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p'));

		$searchFields = array(
			'product_name' => (object) array('field' => 'p.name'),
			'product_sku' => (object) array('field' => 'p.sku'),
			'related_sku' => (object) array('field' => 'p.related_sku'),
			'manufacturer_sku' => (object) array('field' => 'p.manufacturer_sku')
		);
		$priority     = 0;
		$unionQueries = array();

		foreach ($this->criteria as $priority => $fields)
		{
			foreach ($fields as $field)
			{
				switch ($field->name)
				{
					case 'product_name':
					case 'product_sku':
					case 'related_sku':
					case 'manufacturer_sku':
						$subQuery = clone $templateQuery;
						$subQuery->select((int) $priority . ' AS priority')
							->where($this->preparePartiallySearch(array($searchFields[$field->name])));
						$unionQueries[] = $subQuery;
						unset($searchFields[$field->name]);
						break;
				}
			}
		}

		$priority++;

		if (!empty($searchFields))
		{
			$subQuery = clone $templateQuery;
			$subQuery->select((int) $priority . ' AS priority')
				->where($this->preparePartiallySearch($searchFields));
			$unionQueries[] = $subQuery;
		}

		$syncRef            = $this->redShopConfig->get('product_search_sync_ref', 'NONE');
		$terms              = array();
		$standardConditions = array();

		foreach ($this->indexerQuery->included as $item)
		{
			$terms[] = $item->term;
		}

		if ($syncRef !== 'NONE' && !empty($terms))
		{
			$prefix   = str_replace('.', '_', $syncRef);
			$subQuery = $db->getQuery(true);
			$subQuery->select($db->qn($prefix . '.local_id'))
				->from($db->qn('#__redshopb_sync') . ' AS ' . $prefix)
				->where($db->qn($prefix . '.reference') . ' = ' . $db->q($syncRef))
				->where($db->qn($prefix . '.remote_key') . ' IN (' . implode(',', RHelperArray::quote($terms)) . ')');

			$standardConditions[] = '(' . $db->qn('p.id') . ' IN (' . $subQuery . '))';
		}

		if (!empty($standardConditions))
		{
			$subQuery = clone $templateQuery;
			$subQuery->select((int) $priority . ' AS priority')
				->where('(' . implode(' OR ', $standardConditions) . ')');
			$unionQueries[] = $subQuery;
		}

		if (empty($unionQueries))
		{
			return array();
		}

		$query = array_shift($unionQueries);

		if (!empty($unionQueries))
		{
			foreach ($unionQueries as $unionQuery)
			{
				$query->unionDistinct($unionQuery);
			}
		}

		$mainQuery->select('MIN(subQuery.priority)')
			->innerJoin('(' . $query . ') AS subQuery ON subQuery.id = p.id')
			->group('subQuery.id')
			->order('subQuery.priority ASC');

		$result = $db->setQuery($mainQuery)
			->loadAssocList(null, 'id');

		if (!$result)
		{
			$result = array();
		}

		return $result;
	}

	/**
	 * Prepare partial search priority
	 *
	 * @param   array  $fieldsNames     Fields names
	 * @param   int    $configPriority  Config priority
	 *
	 * @return  string
	 */
	public function preparePartiallySearchPriority(array $fieldsNames, $configPriority = 0)
	{
		$case                = array('(CASE');
		$db                  = Factory::getDbo();
		$concatenationNeeded = false;
		$globalSearchMethod  = RedshopbApp::getConfig()
			->getString('product_search_method', 'exact_and_partial');

		foreach (array('exact', 'exact_synonym', 'exact_stem', 'match') as $secondPriorityIndex => $secondPriorityName)
		{
			$subCase = array('WHEN');
			$or      = array();

			foreach ($this->indexerQuery->included as $item)
			{
				if (!empty($item->synonyms))
				{
					switch ($secondPriorityName)
					{
						case 'exact':
							$pattern = '[[:<:]]' . $db->escape($item->term, true) . '[[:>:]]';

							foreach ($fieldsNames as $fieldName)
							{
								$isNumeric = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;
								$or[]      = 'LOWER(' . $db->qn($fieldName->field) . ') '
									. ($isNumeric
									|| is_numeric($item->term)
									|| !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
									. 'REGEXP ' . $db->q($pattern, false);
							}
							break;
						case 'exact_synonym':
							foreach ($item->synonyms as $synonym)
							{
								$pattern = '[[:<:]]' . $db->escape($synonym, true) . '[[:>:]]';

								foreach ($fieldsNames as $fieldName)
								{
									if (!isset($fieldName->synonym) || $fieldName->synonym == -1)
									{
										$isNumeric = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;
										$or[]      = 'LOWER(' . $db->qn($fieldName->field) . ') '
											. ($isNumeric
											|| is_numeric($synonym)
											|| !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
											. 'REGEXP ' . $db->q($pattern, false);
									}
								}
							}
							break;
						case 'match':
							foreach ($item->synonyms as $synonym)
							{
								foreach ($fieldsNames as $fieldName)
								{
									if (isset($fieldName->method) && $fieldName->method != '-1')
									{
										$currentSearchMethod = $fieldName->method;
									}
									else
									{
										$currentSearchMethod = $globalSearchMethod;
									}

									if ($currentSearchMethod == 'exact_and_partial' && (!isset($fieldName->synonym) || $fieldName->synonym == -1))
									{
										$isNumeric = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;
										$or[]      = 'LOWER(' . $db->qn($fieldName->field) . ') '
											. ($isNumeric
											|| is_numeric($synonym)
											|| !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
											. 'LIKE LOWER(' . $db->quote('%' . $db->escape($synonym, true) . '%', false) . ')';
									}
								}
							}
							break;
					}
				}
				else
				{
					switch ($secondPriorityName)
					{
						case 'exact':
							$pattern = '[[:<:]]' . $db->escape($item->term, true) . '[[:>:]]';

							foreach ($fieldsNames as $fieldName)
							{
								$isNumeric = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;
								$or[]      = 'LOWER(' . $db->qn($fieldName->field) . ') '
									. ($isNumeric
									|| is_numeric($item->term)
									|| !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
									. 'REGEXP ' . $db->q($pattern, false);
							}
							break;

						case 'exact_stem':
							if (!$item->phrase && $item->stem && $item->term != $item->stem)
							{
								$pattern = '[[:<:]]' . $db->escape($item->stem, true);

								foreach ($fieldsNames as $fieldName)
								{
									if (!isset($fieldName->stem) || $fieldName->stem == -1)
									{
										$isNumeric           = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;
										$or[]                = 'LOWER(' . $db->qn($fieldName->field) . ') '
											. ($isNumeric
											|| is_numeric($item->stem)
											|| !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
											. 'REGEXP ' . $db->q($pattern, false);
										$concatenationNeeded = true;
									}
								}
							}

							break;

						case 'match':
							foreach ($fieldsNames as $fieldName)
							{
								if (isset($fieldName->method) && $fieldName->method != '-1')
								{
									$currentSearchMethod = $fieldName->method;
								}
								else
								{
									$currentSearchMethod = $globalSearchMethod;
								}

								if ($currentSearchMethod == 'exact_and_partial')
								{
									$isNumeric = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;
									$or[]      = 'LOWER(' . $db->qn($fieldName->field) . ') '
										. ($isNumeric
										|| is_numeric($item->term)
										|| !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
										. 'LIKE LOWER(' . $db->quote('%' . $db->escape($item->term, true) . '%', false) . ')';
								}
							}

							break;
					}
				}
			}

			if (!empty($or))
			{
				$subCase[]     = implode(' OR ', $or);
				$priorityIndex = $configPriority * 10;

				if (in_array($secondPriorityName, array('exact_stem', 'match')))
				{
					$priorityIndex += $secondPriorityIndex * 1000;
				}
				else
				{
					$priorityIndex += $secondPriorityIndex;
				}

				$subCase[] = 'THEN ' . $priorityIndex;
				$case[]    = implode(' ', $subCase);
			}
		}

		$case[] = 'ELSE 99999 END) AS priority';

		if ($concatenationNeeded)
		{
			$concatenation = array();

			foreach ($fieldsNames as $fieldName)
			{
				$concatenation[] = $db->qn($fieldName->field);
			}

			$case[] = ',CONCAT(' . implode(',' . $db->q(' ') . ',', $concatenation) . ') AS concatenation';
		}
		else
		{
			$case[] = ',NULL AS concatenation';
		}

		return implode(' ', $case);
	}

	/**
	 * Get Regexp Condition
	 *
	 * @param   string   $field      Field name
	 * @param   string   $term       Search term
	 * @param   boolean  $exact      Search exact or started from current string
	 * @param   boolean  $isNumeric  Is numeric
	 *
	 * @return string
	 *
	 * @since  1.13.0
	 */
	protected function getRegexpCondition($field, $term, $exact, $isNumeric = false)
	{
		$db      = Factory::getDbo();
		$pattern = '[[:<:]]' . $db->escape($term, true) . ($exact ? '[[:>:]]' : '');

		return 'LOWER(' . $db->qn($field) . ') '
			. ($isNumeric || is_numeric($term) || !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
			. 'REGEXP ' . $db->q($pattern, false);
	}

	/**
	 * Get Like Condition
	 *
	 * @param   string   $field      Field name
	 * @param   string   $term       Search term
	 * @param   boolean  $notLike    Like or NOT like condition
	 * @param   boolean  $isNumeric  Is numeric
	 *
	 * @return string
	 *
	 * @since  1.13.0
	 */
	protected function getLikeCondition($field, $term, $notLike = false, $isNumeric = false)
	{
		$db = Factory::getDbo();

		return 'LOWER(' . $db->qn($field) . ') '
			. ($isNumeric || is_numeric($term) || !$this->indexerQuery->hasSpecialCharacter ? '' : 'collate utf8_bin ')
			. ($notLike ? ' NOT' : '')
			. ' LIKE LOWER(' . $db->quote('%' . str_replace(' ', '%', $db->escape($term, true)) . '%') . ')';
	}

	/**
	 * Prepare partial search
	 *
	 * @param   array  $fieldsNames  Fields names
	 *
	 * @return  string
	 *
	 * @since 1.13.0
	 */
	public function preparePartiallySearch(array $fieldsNames)
	{
		$fieldOr            = array();
		$globalSearchMethod = RedshopbApp::getConfig()
			->getString('product_search_method', 'exact_and_partial');

		foreach ($fieldsNames as $fieldName)
		{
			$and                 = array();
			$notRequiredOr       = array();
			$excluded            = array();
			$currentSearchMethod = isset($fieldName->method) && $fieldName->method != '-1' ? $fieldName->method : $globalSearchMethod;
			$isNumeric           = isset($fieldName->isNumeric) && $fieldName->isNumeric ? $fieldName->isNumeric : false;

			foreach ($this->indexerQuery->included as $item)
			{
				if (!empty($item->synonyms))
				{
					$synonyms = array();

					if ($currentSearchMethod == 'exact_and_partial')
					{
						// Include the main word
						$synonyms[] = $this->getLikeCondition($fieldName->field, $item->term, false, $isNumeric);

						if (isset($fieldName->synonym) && $fieldName->synonym == -1)
						{
							foreach ($item->synonyms as $synonym)
							{
								$synonyms[] = $this->getLikeCondition($fieldName->field, $synonym, false, $isNumeric);
							}
						}
					}
					else
					{
						// Include the main word
						$synonyms[] = $this->getRegexpCondition($fieldName->field, $item->term, true, $isNumeric);

						if (isset($fieldName->synonym) && $fieldName->synonym == -1)
						{
							foreach ($item->synonyms as $synonym)
							{
								$synonyms[] = $this->getRegexpCondition($fieldName->field, $synonym, true, $isNumeric);
							}
						}
					}

					if ($item->required)
					{
						$and[] = '(' . implode(' OR ', $synonyms) . ')';
					}
					else
					{
						$notRequiredOr[] = '(' . implode(' OR ', $synonyms) . ')';
					}
				}
				else
				{
					if ($item->required)
					{
						$or = array();

						if ($currentSearchMethod == 'exact_and_partial')
						{
							$or[] = $this->getLikeCondition($fieldName->field, $item->term, false, $isNumeric);
						}
						else
						{
							$or[] = $this->getRegexpCondition($fieldName->field, $item->term, true, $isNumeric);
						}

						if (!$item->phrase && $item->stem && $item->term != $item->stem && (!isset($fieldName->stem) || $fieldName->stem == -1))
						{
							$or[] = $this->getRegexpCondition($fieldName->field, $item->stem, false, $isNumeric);
						}

						$and[] = '(' . implode(' OR ', $or) . ')';
					}
					else
					{
						if ($currentSearchMethod == 'exact_and_partial')
						{
							$notRequiredOr[] = $this->getLikeCondition($fieldName->field, $item->term, false, $isNumeric);
						}
						else
						{
							$notRequiredOr[] = $this->getRegexpCondition($fieldName->field, $item->term, true, $isNumeric);
						}

						if (!$item->phrase && $item->stem && $item->term != $item->stem && (!isset($fieldName->stem) || $fieldName->stem == -1))
						{
							$notRequiredOr[] = $this->getRegexpCondition($fieldName->field, $item->stem, false, $isNumeric);
						}
					}
				}
			}

			foreach ($this->indexerQuery->excluded as $item)
			{
				$excluded[] = $this->getLikeCondition($fieldName->field, $item->term, true, $isNumeric);
			}

			if (!empty($and))
			{
				$andString = '(' . implode(' AND ', $and) . ')';
			}

			if (!empty($notRequiredOr))
			{
				// Make sense optional words search just when require words don't exists
				if (empty($andString))
				{
					$andString = '(' . implode(' OR ', $notRequiredOr) . ')';
				}

				// Required words are available
				else
				{
					$andString = '(IF (' . $andString . ',(' . $andString . ' OR (' . implode(' OR ', $notRequiredOr) . ')),FALSE)) <> FALSE';
				}
			}

			if (!empty($excluded))
			{
				$andString = '(' . (!empty($andString) ? $andString . ' AND ' : '') . implode(' AND ', $excluded) . ')';
			}

			if (!empty($andString))
			{
				$fieldOr[] = $andString;
			}
		}

		if (!empty($fieldOr))
		{
			$result = implode(' OR ', $fieldOr);
		}
		else
		{
			$result = '1 = 1';
		}

		return $result;
	}

	/**
	 * Method to get the base search query limited by company IDs
	 *
	 * @param   int      $customerId    Id of the current customer
	 * @param   string   $customerType  Type of customer
	 * @param   boolean  $byCategory    Require category filters
	 *
	 * @return  JDatabaseQuery
	 */
	public function getBaseSearchQuery($customerId = null, $customerType = null, $byCategory = false)
	{
		if (is_null($customerId))
		{
			$customerId = $this->customerId;
		}

		if (is_null($customerType))
		{
			$customerType = $this->customerType;
		}

		$db          = Factory::getDbo();
		$searchQuery = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p') . ' FORCE INDEX (idx_state, PRIMARY)');

		$commonStates = $this->getCommonStates();

		// Add common states for query
		foreach ($commonStates as $column => $value)
		{
			$searchQuery->where($db->qn($column) . ' ' . $value);
		}

		$searchQuery->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
			. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
		)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			);

		$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($customerId, $customerType);

		if ($isFromMainCompany)
		{
			$searchQuery->where('0 = 1');

			return $searchQuery;
		}

		$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$companies = RedshopbEntityCompany::getInstance($companyId)->getTree(false, false);

		$searchQuery->leftJoin(
			$db->qn('#__redshopb_product_company_xref', 'pcref') . ' FORCE INDEX(PRIMARY) ON '
			. $db->qn('pcref.product_id') . ' = ' . $db->qn('p.id')
		)
			->where('(' . $db->qn('pcref.company_id') . ' IS NULL OR ' . $db->qn('pcref.company_id') . ' = ' . (int) $companyId . ')');

		if ($byCategory)
		{
			// Filter by category
			$this->filterByCategory($searchQuery, $customerId, $customerType);
		}

		if (empty($companies))
		{
			if (!RedshopbHelperCommon::getUser()->b2cMode)
			{
				$searchQuery->where($db->qn('p.company_id') . ' IS NULL');
			}

			return $searchQuery;
		}

		$searchQuery->where('(' . $db->qn('p.company_id') . ' IN (' . implode(',', $companies) . ') OR ' . $db->qn('p.company_id') . ' IS NULL)');

		return $searchQuery;
	}

	/**
	 * Method to quote terms for sql inclusion
	 *
	 * @param   array  $terms  search terms
	 *
	 * @return  mixed
	 *
	 * @since   1.12.0
	 *
	 * @deprecated  1.12.32 Use RHelperArray::quote() instead
	 */
	public function arrayQuote($terms)
	{
		return RHelperArray::quote($terms);
	}

	/**
	 * Method to add categories filter conditions to a query
	 *
	 * @param   JDatabaseQuery  $query         Query to add filters to
	 * @param   int             $customerId    Id of the current customer
	 * @param   string          $customerType  Type of customer
	 * @param   string          $index         Index query rule
	 *
	 * @return  void
	 */
	public function filterByCategory($query, $customerId, $customerType, $index = '')
	{
		$db                  = Factory::getDbo();
		$availableCategories = $this->getAvailableCategories($customerId, $customerType);

		if (!empty($availableCategories))
		{
			$this->hasFilters = true;
			$query->leftJoin(
				$db->qn('#__redshopb_product_category_xref', 'cref') . $index . ' ON ' . $db->qn('cref.product_id') . ' = ' . $db->qn('p.id')
			)
				->where($db->qn('cref.category_id') . ' IN (' . implode(',', $availableCategories) . ')');
		}
	}

	/**
	 * Method to get a list of available categories based on filter settings
	 *
	 * @param   int     $customerId    Id of the current customer
	 * @param   string  $customerType  Type of customer
	 *
	 * @return  array
	 */
	protected function getAvailableCategories($customerId, $customerType)
	{
		$app        = Factory::getApplication();
		$categories = array();

		$filterCategories = Factory::getApplication()->getUserState('shop.categoryfilter.' . $this->itemKey, array());

		if (!empty($filterCategories))
		{
			if (!is_array($filterCategories))
			{
				$filterCategories = array($filterCategories);
			}

			$categories = $filterCategories;
		}

		$categoryId = $app->input->getInt('category_id', $app->getUserState('filter.product_category', 0));

		if ($app->input->getCmd('layout') == 'category')
		{
			$categoryId = $app->input->getInt('id');
		}

		if ($categoryId)
		{
			if ($this->redShopConfig->get('show_subcategories_products', 0) == 1)
			{
				$categories = array_merge($categories, $this->getSubCategories($categoryId));
			}

			$categories[] = $categoryId;
		}

		// Limit by available categories
		$companyId           = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$availableCategories = RedshopbHelperACL::listAvailableCategories(Factory::getUser()->id, false, 100, $companyId, false, 'comma', '');

		$availableCategories = explode(',', $availableCategories);

		PluginHelper::importPlugin('kvasir_search');
		$dispatcher = RFactory::getDispatcher();
		$dispatcher->trigger('onVanirSearchGetAvailableCategories', array(&$availableCategories));

		$categories = (empty($categories)) ? $availableCategories : array_intersect($availableCategories, $categories);

		return ArrayHelper::toInteger($categories);
	}

	/**
	 * Method to get all sub categories of a category
	 *
	 * @param   int  $categoryId  ID of the top category
	 *
	 * @return  array
	 *
	 * @since   1.12.30
	 */
	public function getSubCategories($categoryId)
	{
		if ($this->redShopConfig->getBool('show_subcategories_products', false) === false)
		{
			return array();
		}

		$currentCategory = RedshopbEntityCategory::getInstance($categoryId);
		$currentCategory->getItem();
		$subCategories = $currentCategory->getAllChildrenIds();

		return $subCategories;
	}

	/**
	 * Method to add manufactures filter conditions to a query
	 *
	 * @param   JDatabaseQuery  $query  Query to add filters to
	 *
	 * @return  void
	 */
	public function filterByManufactures($query)
	{
		$app                 = Factory::getApplication();
		$filterManufacturers = $app->getUserState('shop.manufacturer.' . $this->itemKey, array());

		if (!empty($filterManufacturers) && !is_array($filterManufacturers))
		{
			$filterManufacturers = array($filterManufacturers);
		}

		$manufacturerId = $app->getUserState('shop.manufacturer.' . $this->itemKey, 0);

		if ($manufacturerId)
		{
			$filterManufacturers[] = (int) $manufacturerId;
		}

		$manufacturers = ArrayHelper::toInteger(
			array_unique($filterManufacturers)
		);

		if (empty($manufacturers))
		{
			return;
		}

		$this->hasFilters = true;

		$db = Factory::getDbo();
		$query->leftJoin($db->qn('#__redshopb_manufacturer', 'mf') . ' ON mf.id = p.manufacturer_id AND mf.state = 1')
			->where($db->qn('mf.id') . ' IN (' . implode(',', $manufacturers) . ')');
	}

	/**
	 * Filter by recent
	 *
	 * @param   JDatabaseQuery  $query  Query to add filters to
	 *
	 * @return  void
	 */
	public function filterByRecent($query)
	{
		$app     = Factory::getApplication();
		$itemKey = $app->getUserState('shop.itemKey', '');

		if ($itemKey != 'productrecent_0')
		{
			return;
		}

		$config           = RedshopbEntityConfig::getInstance();
		$this->hasFilters = true;
		$db               = Factory::getDbo();
		$unixOffset       = (int) strtotime('-' . $config->getInt('date_new_product', 14) . ' day', Date::getInstance()->toUnix());
		$now              = date('Y-m-d', $unixOffset);

		$query->where(
			'IF(p.date_new = STR_TO_DATE(' . $db->q('0000-00-00') . ', ' . $db->q('%Y-%m-%d')
			. '), p.created_date >= STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d')
			. '), p.date_new >= STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d') . '))'
		);
	}

	/**
	 * Filter by featured
	 *
	 * @param   JDatabaseQuery  $query  Query to add filters to
	 *
	 * @return  void
	 */
	public function filterByFeatured($query)
	{
		$app     = Factory::getApplication();
		$itemKey = $app->getUserState('shop.itemKey', '');

		if ($itemKey != 'productfeatured_0')
		{
			return;
		}

		$this->hasFilters = true;
		$db               = Factory::getDbo();

		$query->where($db->qn('p.featured') . ' = 1');
	}

	/**
	 * Method to add tag filter conditions to a query
	 *
	 * @param   JDatabaseQuery  $query  Query to add filters to
	 *
	 * @return void
	 */
	public function filterByTags($query)
	{
		$app        = Factory::getApplication();
		$filterTags = $app->getUserState('shop.tag.' . $this->itemKey, array());

		if (empty($filterTags))
		{
			return;
		}

		$this->hasFilters = true;

		if (!is_array($filterTags))
		{
			$filterTags = array($filterTags);
		}

		$filterTags = ArrayHelper::toInteger($filterTags);

		$db = Factory::getDbo();
		$query->leftJoin($db->qn('#__redshopb_product_tag_xref', 'tcx') . ' FORCE INDEX(#__rs_prod_tag_fk1) ON tcx.product_id = p.id')
			->leftJoin($db->qn('#__redshopb_tag', 'tag') . ' FORCE INDEX(PRIMARY) ON tag.id = tcx.tag_id AND tag.state = 1')
			->where($db->qn('tcx.tag_id') . ' IN (' . implode(',', $filterTags) . ')');
	}

	/**
	 * Method to add attribute filter conditions to a query
	 *
	 * @param   JDatabaseQuery  $query  Query to add filters to
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function filterByAttributes($query)
	{
		$app              = Factory::getApplication();
		$filterAttributes = $app->getUserState('shop.attributefilter.' . $this->itemKey, array());

		if (empty($filterAttributes))
		{
			return;
		}

		$db       = Factory::getDbo();
		$subQuery = $db->getQuery(true);
		$subQuery->select('DISTINCT(a.product_id)')
			->from('#__redshopb_product_attribute AS a');

		foreach ($filterAttributes AS $attribute => $values)
		{
			if ($values)
			{
				$valueList = implode(',', RHelperArray::quote($values));

				$joinKey = $db->qn($attribute . '_value');
				$subQuery->leftJoin('#__redshopb_product_attribute_value AS ' . $joinKey . ' ON ' . $joinKey . '.product_attribute_id = a.id');
				$subQuery->where(
					'(a.name = ' . $db->q($attribute) . '  AND ('
					. $joinKey . '.string_value IN (' . $valueList . ') OR '
					. $joinKey . '.float_value IN (' . $valueList . ') OR '
					. $joinKey . '.int_value IN (' . $valueList . ') OR '
					. $joinKey . '.text_value IN (' . $valueList . ')'
					. '))', 'OR'
				);
			}
		}

		$query->where('p.id IN (' . $subQuery . ')');
	}

	/**
	 * getProductIdsForProductPrices
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 */
	public function getProductIdsForProductPrices()
	{
		$input = Factory::getApplication()->input;

		if ($input->getCmd('option') == 'com_redshopb'
			&& $input->getCmd('view') == 'shop'
			&& in_array($input->getCmd('layout'), array('productrecent', 'productfeatured', 'productlist')))
		{
			if ($this->hasTerm())
			{
				$productIds = $this->getStoredSearch();
			}
			else
			{
				$productIds = RedshopbHelperShop::getFilteredProductIds(false);
			}
		}
		else
		{
			$productIds = RedshopbHelperShop::getFilteredProductIds(false);
		}

		return explode(',', $productIds);
	}

	/**
	 * Get products prices
	 *
	 * @param   int     $customerId    Id of the current customer
	 * @param   string  $customerType  Type of customer
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 */
	public function getProductsPrices($customerId, $customerType)
	{
		static $prices = null;

		if (is_null($prices))
		{
			$productIds = $this->getProductIdsForProductPrices();
			$prices     = array();

			if (!empty($productIds))
			{
				$prices = RedshopbHelperPrices::getProductsPrice($productIds, $customerId, $customerType);
			}
		}

		return $prices;
	}

	/**
	 * Method to add campaign price filter conditions to a query
	 *
	 * @param   JDatabaseQuery   $query   Query to add filters to
	 *
	 * @throws  Exception
	 *
	 * @return  null
	 */
	public function filterByCampaignPrice($query)
	{
		$db            = Factory::getDbo();
		$app           = Factory::getApplication();
		$campaignPrice = $app->getUserState('shop.campaign_price.' . $this->itemKey, null);

		if (empty($campaignPrice))
		{
			return null;
		}

		$query->where($db->qn('p.campaign') . ' = ' . $db->q(1));
	}

	/**
	 * Method to add price range filter conditions to a query
	 *
	 * @param   JDatabaseQuery  $query         Query to add filters to
	 * @param   int             $customerId    Id of the current customer
	 * @param   string          $customerType  Type of customer
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function filterByPriceRange($query, $customerId, $customerType)
	{
		$app = Factory::getApplication();

		$priceRange = $app->getUserState('shop.price_range.' . $this->itemKey, '');

		if (empty($priceRange))
		{
			return;
		}

		$matchProductIds  = array(0);
		$this->hasFilters = true;
		$priceRange       = explode(',', $priceRange);
		$productIds       = $this->getProductIdsForProductPrices();

		if (!empty($productIds))
		{
			$prices = $this->getProductsPrices($customerId, $customerType);

			foreach ($productIds as $productId)
			{
				if (array_key_exists($productId, $prices))
				{
					if (isset($priceRange[0]) && !is_null($priceRange[0]) && (float) $priceRange[0] > (float) $prices[$productId]->price)
					{
						continue;
					}

					if (isset($priceRange[1]) && !is_null($priceRange[1]) && (float) $prices[$productId]->price > (float) $priceRange[1])
					{
						continue;
					}

					$matchProductIds[] = $productId;
				}
				elseif (isset($priceRange[0]) ? !is_null($priceRange[0]) && (float) $priceRange[0] == 0 : true)
				{
					$matchProductIds[] = $productId;
				}
			}
		}

		$query->where('p.id IN (' . implode(',', $matchProductIds) . ')');
	}

	/**
	 * Method to add stock filter conditions to a query
	 *
	 * @param   JDatabaseQuery  $query         Query to add filters to
	 *
	 * @return void
	 */
	public function filterByStock($query)
	{
		$db          = Factory::getDbo();
		$app         = Factory::getApplication();
		$filterStock = $app->getUserState('shop.in_stock.' . $this->itemKey, array());

		if (empty($filterStock))
		{
			return;
		}

		$query->leftJoin($db->qn('#__redshopb_stockroom_product_xref', 'spx') . ' ON p.id = spx.product_id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.product_id = p.id')
			->leftJoin($db->qn('#__redshopb_stockroom_product_item_xref', 'spix') . ' ON spix.product_item_id = pi.id')
			->where('(spx.product_id IS NOT NULL OR spix.product_item_id IS NOT NULL)')
			->where('(spx.unlimited = 1 OR spx.amount > 0 OR spix.unlimited = 1 OR spix.amount > 0)');
	}

	/**
	 * Method for set common states.
	 *
	 * @param   array  $states  New common states.
	 *
	 * @return  void
	 */
	public function setCommonStates($states = array())
	{
		$this->commonStates = $states;
	}

	/**
	 * Method for get common states.
	 *
	 * @return  array
	 */
	public function getCommonStates()
	{
		return (array) $this->commonStates;
	}
}
