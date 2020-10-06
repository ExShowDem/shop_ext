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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
/**
 * Shop Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelShop extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_shop';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_shop_limit';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Array discounts from products
	 *
	 * @var array
	 */
	protected static $discounts = array();

	/**
	 * The company type
	 *
	 * @var  string
	 */
	public $customerCType = 'end_customer';

	/**
	 * The customer type
	 *
	 * @var  string
	 */
	protected $customerType = '';

	/**
	 * The customer ID
	 *
	 * @var  integer
	 */
	protected $customerId = 0;

	/**
	 * Constructor
	 *
	 * @param   array $config Configuration array
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
				'sku', 'p.sku',
				'product_state',
				'product_category',
				'product_tag',
				'product_discontinued',
				'attribute_flat_display',
				'product_collection',
				'filter_onsale',
				'product_id',
				'product_price_range'
			);
		}

		$app                 = Factory::getApplication();
		$this->customerType  = $app->getUserState('shop.customer_type', '');
		$this->customerId    = $app->getUserState('shop.customer_id', 0);
		$this->customerCType = RedshopbHelperShop::getCustomerType($this->customerId, $this->customerType);

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
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('p.sku', 'asc');
		$app = Factory::getApplication();

		$onsale = $app->input->get('filter_onsale', 0);

		if ($onsale || $app->getUserState('shop.offers.filter_onsale', 0) == 1)
		{
			$this->setState('filter_onsale', 1);
		}

		$shopname = $app->input->getString('filterShopName');

		if ($shopname)
		{
			$this->setState('filterShopName', $shopname);
		}

		$oldState = $this->__state_set;

		// Avoid recursion
		$this->__state_set = true;

		$this->setState('filter.product_collection', $this->getState('product_collection') ?? $this->getState('filter.product_collection'));

		$this->__state_set = $oldState;
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   2.5.0
	 */
	public function setState($property, $value = null)
	{
		switch ($property)
		{
			// If somewhere we set these properties, then make sure they are an array with integer inside
			case 'filter.product_collection':
			case 'product_collection':

				$property = 'filter.product_collection';

				if (!is_null($value))
				{
					if (RedshopbHelperShop::inCollectionMode())
					{
						$availableCollections = RedshopbHelperCollection::getCustomerCollectionsForShop();

						if (empty($value))
						{
							$value = $availableCollections;
						}
						else
						{
							$value = array_intersect((array) $value, $availableCollections);
						}

						if (empty($value))
						{
							$value = [0];
						}

						$value = ArrayHelper::toInteger((array) $value);
					}
					else
					{
						$value = null;
					}
				}

				break;
		}

		return parent::setState($property, $value);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$useFilterStatus = (boolean) $this->getState('use_filter', true);

		if (isset($this->customerId) && $this->customerId > 0 && isset($this->customerType) && $this->customerType != '')
		{
			$db                = $this->getDbo();
			$app               = Factory::getApplication();
			$disableUserStates = $this->getState('disable_user_states', false);
			$companyId         = RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType);
			$itemKey           = $app->getUserState('shop.itemKey', 0);
			$config            = RedshopbEntityConfig::getInstance();

			// Begin the query to retrieve products
			$query = $db->getQuery(true)
				->select('p.*')
				->from($db->qn('#__redshopb_product', 'p'))
				->where($db->qn('p.state') . ' = 1')
				->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where($db->qn('p.discontinued') . ' = 0')
				->where($db->qn('p.service') . ' = 0');

			$volumePriceQuery = $db->getQuery(true);
			$volumePriceQuery->select('vp.id')
				->from($db->qn('#__redshopb_product_price', 'vp'))
				->where($db->qn('vp.type') . ' = ' . $db->q('product'))
				->where($db->qn('vp.type_id') . ' = ' . $db->qn('p.id'))
				->where('(' . $db->qn('vp.quantity_min') . ' IS NOT NULL OR ' . $db->qn('vp.quantity_max') . ' IS NOT NULL)');

			$query->select('(' . $volumePriceQuery . ' LIMIT 0, 1) AS hasVolumePricing');

			// Optional product item join
			$query->join(
				'left',
				$db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pi.product_id')
				. ' = ' . $db->qn('p.id') . ' AND ' . $db->qn('pi.state') . ' = 1'
			);

			$productCollection = $this->getState('filter.product_collection');

			// Collection assignment
			if (!empty($productCollection))
			{
				$query->leftJoin($db->qn('#__redshopb_collection_product_item_xref', 'wpix')
					. ' ON (' . $db->qn('wpix.product_item_id') . ' = ' . $db->qn('pi.id')
					. ' AND ' . $db->qn('wpix.state') . ' = 1)'
				)
					->leftJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') .
						' ON ' . $db->qn('wpx.product_id') . ' = ' . $db->qn('p.id') .
						' AND ' . $db->qn('wpx.state') . ' = 1'
					)
					->leftJoin($db->qn('#__redshopb_collection', 'w') .
						' ON ' . $db->qn('w.id') . ' IN (' . $db->qn('wpix.collection_id') . ', ' . $db->qn('wpx.collection_id') . ')'
					)
					->where($db->qn('w.state') . ' = 1')
					->where($db->qn('wpix.collection_id') . ' IN (' . implode(',', $productCollection) . ')')
					->order(array('ISNULL(wpx.ordering)', 'wpx.ordering', 'p.name'))
					->group('CONCAT_WS (' . $db->q('_') . ', p.id, w.id)');

				if (is_null($this->getState('list.ordering', null)))
				{
					$this->setState('list.ordering', 'wpx.ordering');
				}
			}
			else
			{
				$accessoryQuery = $db->getQuery(true);
				$accessoryQuery->select('DISTINCT accessory_product_id')
					->from($db->qn('#__redshopb_product_item_accessory'));

				$query->where($db->qn('p.id') . ' NOT IN (' . $accessoryQuery . ')')
					->group($db->qn('p.id'));
			}

			$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($this->customerId, $this->customerType);

			if ($isFromMainCompany)
			{
				$query->where('0 = 1');
			}
			else
			{
				$companies = array();

				if ($companyId)
				{
					$companies = RedshopbEntityCompany::getInstance($companyId)
						->getTree(false, false);
				}

				if (empty($companies))
				{
					if (!RedshopbHelperCommon::getUser()->b2cMode)
					{
						$query->where($db->qn('p.company_id') . ' IS NULL');
					}
				}
				else
				{
					$query->where(
						'(' . $db->qn('p.company_id') . ' IN (' . implode(',', $companies) . ') OR ' . $db->qn('p.company_id') . ' IS NULL)'
					);
				}

				// Limit by company restriction of product.
				$query->leftJoin(
					$db->qn('#__redshopb_product_company_xref', 'pcref') . ' ON ' . $db->qn('pcref.product_id') . ' = ' . $db->qn('p.id')
				)
					->where('(' . $db->qn('pcref.company_id') . ' IS NULL OR ' . $db->qn('pcref.company_id') . ' = ' . (int) $companyId . ')');
			}

			// Filter by category
			$category = $this->getState('filter.product_category');

			if (empty($category)
				&& !$disableUserStates
				&& ($app->getUserState('shop.layout') != 'product' && $app->getUserState('shop.layout') != 'manufacturer'))
			{
				$category = $app->getUserState('shop.category', 0);
			}

			if (!empty($category))
			{
				$category = $this->stringifyIds($category);

				if (!empty($category))
				{
					$query->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON pcx.product_id = p.id')
						->where('pcx.category_id IN (' . $category . ')');
				}
			}

			// Filter by manufacturer
			$filterManufacturer     = $this->getState('filter.manufacturer', $this->getState('filter.manufacturer_id'));
			$manufacturerJoinNeeded = false;
			$tagXrefJoinNeeded      = false;
			$tagJoinNeeded          = false;

			if (is_array($filterManufacturer))
			{
				$filterManufacturer = array_filter($filterManufacturer);
			}

			if (!$disableUserStates && empty($filterManufacturer))
			{
				$filterManufacturer = $app->getUserState('shop.manufacturer.' . $itemKey, 0);
			}

			if (!empty($filterManufacturer) && $app->getUserState('shop.layout') == 'manufacturer' ? true : $useFilterStatus)
			{
				$filterManufacturer = $this->stringifyIds($filterManufacturer);

				if (!empty($filterManufacturer))
				{
					$manufacturerJoinNeeded = true;
					$query->where($db->qn('mf.id') . ' IN (' . $filterManufacturer . ')');
				}
			}

			// Filter stock
			$filterStock = $app->getUserState('shop.in_stock.' . $itemKey, 0);

			if ($filterStock)
			{
				$query->leftJoin($db->qn('#__redshopb_stockroom_product_xref', 'spx') . ' ON p.id = spx.product_id')
					->leftJoin($db->qn('#__redshopb_stockroom_product_item_xref', 'spix') . ' ON spix.product_item_id = pi.id')
					->where('(spx.product_id IS NOT NULL OR spix.product_item_id IS NOT NULL)')
					->where('(spx.unlimited = 1 OR spx.amount > 0 OR spix.unlimited = 1 OR spix.amount > 0)');
			}

			// Exclude products set up as fee or freight
			$query2 = $db->getQuery(true)
				->select($db->qn('freight_product_id'))
				->from($db->qn('#__redshopb_company'))
				->where($db->qn('deleted') . ' = 0')
				->where($db->qn('freight_product_id') . ' IS NOT NULL');
			$query3 = $db->getQuery(true)
				->select($db->qn('product_id'))
				->from($db->qn('#__redshopb_fee'))
				->where($db->qn('product_id') . ' IS NOT NULL');

			$query->where($db->qn('p.id') . ' NOT IN (' . $query2->__toString() . ')');
			$query->where($db->qn('p.id') . ' NOT IN (' . $query3->__toString() . ')');

			$excludedProducts = $this->getState('filter.excluded_products', array());

			if (!empty($excludedProducts))
			{
				$query->where($db->qn('p.id') . ' NOT IN (' . implode(',', $excludedProducts) . ')');
			}

			if ($this->getState('filter.product_recent', false))
			{
				$unixOffset = strtotime('-' . $config->getInt('date_new_product', 14) . ' day', Date::getInstance()->toUnix());
				$offset     = date('Y-m-d', $unixOffset);
				$query->where(
					'IF(p.date_new = STR_TO_DATE(' . $db->q('0000-00-00') . ', ' . $db->q('%Y-%m-%d')
					. '), p.created_date >= STR_TO_DATE(' . $db->q($offset) . ', ' . $db->q('%Y-%m-%d')
					. '), p.date_new >= STR_TO_DATE(' . $db->q($offset) . ', ' . $db->q('%Y-%m-%d') . '))'
				);
			}

			if ($this->getState('filter.product_featured', false))
			{
				$query->where($db->qn('p.featured') . ' = 1');
			}

			// If not use filter - just use filter category_id (for Shop Category page) and manufacturer_id (for Product List page)
			if ($useFilterStatus)
			{
				// Filter search
				$search = trim($this->getState('filter.search_shop_products'));

				if (empty($search) && !$disableUserStates)
				{
					$search = trim(
						$app->getUserState(
							'mod_filter.search.' . $itemKey,
							$app->getUserState('shop.search', $app->input->getString('search', '')
							)
						)
					);
				}

				if (!empty($search))
				{
					$manufacturerJoinNeeded = true;
					$tagJoinNeeded          = true;
					$search                 = 'LIKE ' . $db->q('%' . $db->escape($search, true) . '%');
					$query->where(
						'(p.name ' . $search . ' OR p.sku ' . $search . ' OR mf.name ' . $search . ' OR tag.name ' . $search . ')'
					);
				}

				// Filter by tag
				$tag = $this->getState('filter.product_tag', $this->getState('filter.tag_id'));

				if (is_array($tag))
				{
					$tag = array_filter($tag);
				}

				if (!$disableUserStates && empty($tag))
				{
					$tag = $app->getUserState('shop.tag.' . $itemKey, 0);
				}

				if (!empty($tag))
				{
					$tag = $this->stringifyIds($tag);

					if (!empty($tag))
					{
						$tagXrefJoinNeeded = true;
						$query->where('tcx.tag_id IN (' . $tag . ')');
					}
				}

				// Filter by colour
				$colour = $this->getState('filter.attribute_flat_display');

				if ($colour)
				{
					$query->innerJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_item_id = pi.id')
						->innerJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = piavx.product_attribute_value_id')
						->where('pav.string_value = ' . $db->q($colour));
				}

				// Filter on sale
				if ($this->getState('filter_onsale'))
				{
					RedshopbHelperShop::setOnSaleItemsQuery($query, false, 'p', 'pi');
				}

				// Filter an specific product ID
				$filterProductId = $this->getState('filter.product_id');

				if ($filterProductId)
				{
					$query->where($db->qn('p.id') . ' = ' . $filterProductId);
				}

				// Filter by price range
				$priceRange = $this->getState('filter.price_range');
				$now        = Date::getInstance()->toSql();

				if (!$disableUserStates && empty($priceRange))
				{
					$priceRange = $app->getUserState('shop.price_range.' . $itemKey);
				}

				if (!empty($priceRange))
				{
					if ($companyId)
					{
						$currency = RedshopbEntityCompany::getInstance($companyId)->getCustomerCurrency();
					}
					else
					{
						$currency = RedshopbApp::getConfig()->get('default_currency', 38);
					}

					$query->leftJoin(
						$db->qn('#__redshopb_product_price', 'pp') . ' ON ' . $db->qn('pp.type_id') . ' = ' . $db->qn('p.id')
						. ' AND pp.type = ' . $db->q('product')
						. ' AND (' . $db->qn('pp.sales_type') . ' = ' . $db->quote('all_customers')
						. ' OR (' . $db->qn('pp.sales_type') . ' = ' . $db->quote('customer_price')
						. ' AND ' . $db->qn('pp.sales_code') . ' = ' . (int) $companyId . '))'
						. ' AND (pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
						. ' AND (pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
						. ' AND (pp.currency_id = ' . (int) $currency . ' OR pp.currency_id IS NULL)'
					);

					$priceRange = explode(',', $priceRange);
					$orPrefix   = '';

					if (isset($priceRange[0]) && !is_null($priceRange[0]))
					{
						if ((float) $priceRange[0] == 0)
						{
							$orPrefix = ' OR pp.price IS NULL';
						}

						$query->where('(' . $db->qn('pp.price') . ' >= ' . (float) $priceRange[0] . $orPrefix . ')');
					}

					if (isset($priceRange[1]) && !is_null($priceRange[1]))
					{
						$query->where('(' . $db->qn('pp.price') . ' <= ' . (float) $priceRange[1] . $orPrefix . ')');
					}
				}

				// Filter by campaign price & discounts
				$filterCampaign = $app->getUserState('shop.campaign_price.' . $itemKey, 0);

				if ($filterCampaign)
				{
					$query->where($db->qn('p.campaign') . ' = ' . $db->q(1));
				}
			}

			if ($manufacturerJoinNeeded)
			{
				$query->leftJoin(
					$db->qn('#__redshopb_manufacturer', 'mf') . ' ON mf.id = p.manufacturer_id AND mf.state = 1'
				);
			}

			if ($tagXrefJoinNeeded || $tagJoinNeeded)
			{
				$query->leftJoin($db->qn('#__redshopb_product_tag_xref', 'tcx') . ' ON ' . $db->qn('tcx.product_id') . ' = ' . $db->qn('p.id'));
			}

			if ($tagJoinNeeded)
			{
				$query->leftJoin($db->qn('#__redshopb_tag', 'tag') . ' ON tag.id = tcx.tag_id AND tag.state = 1');
			}

			// Ordering
			$orderList     = $this->getState('list.ordering');
			$directionList = $this->getState('list.direction');

			$order     = !empty($orderList) ? $orderList : 'p.sku';
			$direction = !empty($directionList) ? $directionList : 'ASC';
			$query->order($db->escape($order) . ' ' . $db->escape($direction));

			return $query;
		}

		// Dummy query to avoid returning null in case a getTotal is called
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('1')
			->from($db->qn('#__redshopb_product', 'p'))
			->where('1 = 0');

		return $query;
	}

	/**
	 * Method to normalize an ID input into an array of integers and then implode it
	 *
	 * @param   mixed $ids either an integer string or array of id values
	 *
	 * @return string
	 */
	private function stringifyIds($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$ids = ArrayHelper::toInteger($ids);

		return implode(',', $ids);
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
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app  = Factory::getApplication();
		$form = parent::getForm($data, $loadData);

		$shippingRateId = $app->getUserState('checkout.shipping_rate_id', '');

		if ($shippingRateId)
		{
			$shippingRate = RedshopbShippingHelper::getShippingRateById($shippingRateId);

			if ($shippingRate->shipping_name == 'self_pickup')
			{
				$form->setFieldAttribute('usebilling', 'disabled', 'true');
			}
		}

		return $form;
	}

	/**
	 * Get product images
	 *
	 * @param   array $ids              Object product values
	 * @param   array $dropDownSelected Array drop down selected
	 *
	 * @return array
	 */
	public static function getProductImages($ids, $dropDownSelected = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('m.*')
			->from($db->qn('#__redshopb_media', 'm'))
			->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = m.attribute_value_id AND pav.state = 1')
			->where('m.state = 1')
			->order($db->qn('m.ordering') . ' ASC')
			->order('IF(m.view = 0, 0, 1/m.view) DESC, m.id');

		if (is_array($dropDownSelected) && count($dropDownSelected) > 0)
		{
			$where = array();

			foreach ($ids as $id)
			{
				// In case attribute id has been passed and not zero or null
				if (isset($dropDownSelected[$id]) && $dropDownSelected[$id])
				{
					$where[] = '(m.attribute_value_id = ' . (int) $dropDownSelected[$id] . ' AND m.product_id = ' . (int) $id . ')';
				}
				else
				{
					$where[] = '(m.product_id = ' . (int) $id . ')';
				}
			}

			$query->where('(' . implode(' OR ', $where) . ')');
		}
		else
		{
			$where = array();

			foreach ($ids as $id)
			{
				$where[] = '(m.product_id = ' . (int) $id . ')';
			}

			$query->where('(' . implode(' OR ', $where) . ')');
		}

		$db->setQuery($query);
		$oldTranslate  = $db->translate;
		$db->translate = false;
		$results       = $db->loadObjectList();
		$db->translate = $oldTranslate;
		$images        = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($images[$result->product_id]))
				{
					$images[$result->product_id] = array();
				}

				$images[$result->product_id][] = $result;
			}
		}

		return $images;
	}

	/**
	 * Get isset dynamic variants
	 *
	 * @param   array $ids              Object product values
	 * @param   array $dropDownSelected Array drop down selected
	 *
	 * @return mixed
	 */
	public function getIssetDynamicVariants($ids, $dropDownSelected = array())
	{
		$ids = ArrayHelper::toInteger($ids);
		$db  = Factory::getDbo();

		$productCollection = $this->getState('filter.product_collection');

		// Select minimal ordering from current product
		$subQuery = $db->getQuery(true)
			->select('MIN(pa4.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa4'))
			->where('pa4.product_id = pa.product_id')
			->where('pa4.state = 1')
			->where('pa4.main_attribute != 1');

		$subQuery2 = $db->getQuery(true)
			->select('GROUP_CONCAT(pav2.id ORDER BY pa2.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav2'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx2') . ' ON pivx2.product_attribute_value_id = pav2.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa2') . ' ON pa2.id = pav2.product_attribute_id')
			->where('pi.id = pivx2.product_item_id')
			->where('pav2.state = 1')
			->where('pa2.state = 1')
			->where('pa2.product_id = pa.product_id')
			->where('pa2.ordering != (' . $subQuery . ')')
			->where('pa2.main_attribute != 1')
			->order('pa2.ordering ASC')
			->order('pav2.ordering')
			->order('pav2.id ASC');
		$query     = $db->getQuery(true)
			->select(
				array(
					'CONCAT_WS(' . $db->q('_') . ', pa.product_id, (' . $subQuery2 . ')) AS concat_dynamics',
					'pa.product_id', 'pivx.product_item_id'
				)
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pa.ordering = (' . $subQuery . ')')
			->where('pi.state = 1')
			->order('pav.ordering');

		// Select related to drop down select
		if (is_array($dropDownSelected) && count($dropDownSelected) > 0)
		{
			$where = array();

			foreach ($ids as $id)
			{
				if (isset($dropDownSelected[$id]))
				{
					$where[] = '(pav3.id = ' . (int) $dropDownSelected[$id] . ' AND pa.product_id = ' . (int) $id . ')';
				}
				else
				{
					$where[] = '(pa.product_id = ' . (int) $id . ')';
				}
			}

			$query->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx3') . ' ON pi.id = pivx3.product_item_id')
				->leftJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav3') . ' ON pav3.id = pivx3.product_attribute_value_id AND pav3.state = 1'
				)
				->leftJoin(
					$db->qn('#__redshopb_product_attribute', 'pa3')
					. ' ON pav3.product_attribute_id = pa3.id AND pa3.main_attribute = 1 AND pa3.state = 1 AND pa3.product_id = pa.product_id'
				)
				->where('(' . implode(' OR ', $where) . ')')
				->order('pav3.ordering');
		}
		else
		{
			$query->where('pa.product_id IN (' . implode(',', $ids) . ')');
		}

		if (!empty($productCollection))
		{
			$query->innerJoin(
				$db->qn('#__redshopb_collection_product_item_xref', 'wpix') . ' ON ' . $db->qn('wpix.product_item_id') . ' = ' . $db->qn('pi.id') .
				' AND ' . $db->qn('wpix.collection_id') . ' IN (' . implode(',', $productCollection) . ')' .
				' AND ' . $db->qn('wpix.state') . ' = 1'
			)
				->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpix.collection_id'))
				->where($db->qn('w.state') . ' = 1')
				->group('concat_dynamics')
				->order('pav.id ASC');
		}
		else
		{
			$query->group('concat_dynamics');
		}

		$db->setQuery($query);
		$results              = $db->loadObjectList();
		$issetDynamicVariants = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($issetDynamicVariants[$result->product_id]))
				{
					$issetDynamicVariants[$result->product_id] = array();
				}

				$issetDynamicVariants[$result->product_id][] = $result->concat_dynamics;
			}
		}

		return $issetDynamicVariants;
	}

	/**
	 * Get isset item and relating items with attribute values
	 *
	 * @param   array $ids              Object product values
	 * @param   array $dropDownSelected Array drop down selected
	 *
	 * @return array|null
	 */
	public function getIssetItems($ids, $dropDownSelected = array())
	{
		$ids = ArrayHelper::toInteger($ids);
		$db  = Factory::getDbo();

		$productCollection = $this->getState('filter.product_collection');

		// Select attribute values ids from items
		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.id ORDER BY pa.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->where('pa.product_id = pi.product_id ')
			->where('pi.id = pivx.product_item_id')
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pa.main_attribute != 1')
			->order('pa.ordering ASC')
			->order('pav.ordering')
			->order('pav.id ASC');

		$query = $db->getQuery(true)
			->select(
				array('(' . $subQuery . ') AS values_ids', 'pi.id', 'pi.product_id', 'pi.stock_lower_level', 'pi.stock_upper_level')
			)
			->from($db->qn('#__redshopb_product_item', 'pi'));

		if (is_array($dropDownSelected) && count($dropDownSelected) > 0)
		{
			$where = array();

			foreach ($ids as $id)
			{
				if (isset($dropDownSelected[$id]))
				{
					$where[] = '(pav3.id = ' . (int) $dropDownSelected[$id] . ' AND pi.product_id = ' . (int) $id . ')';
				}
				else
				{
					$where[] = '(pi.product_id = ' . (int) $id . ')';
				}
			}

			$query->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx3') . ' ON pi.id = pivx3.product_item_id')
				->leftJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav3') . ' ON pav3.id = pivx3.product_attribute_value_id AND pav3.state = 1'
				)
				->leftJoin(
					$db->qn('#__redshopb_product_attribute', 'pa3')
					. ' ON pav3.product_attribute_id = pa3.id AND pa3.main_attribute = 1 AND pa3.state = 1 AND pa3.product_id = pi.product_id'
				)
				->where('(' . implode(' OR ', $where) . ')')
				->order('pav3.ordering');
		}
		else
		{
			$query->where('pi.product_id IN (' . implode(',', $ids) . ')');
		}

		if (!empty($productCollection))
		{
			$query->innerJoin(
				$db->qn('#__redshopb_collection_product_item_xref', 'wpix') . ' ON ' . $db->qn('wpix.product_item_id') . ' = ' . $db->qn('pi.id') .
				' AND ' . $db->qn('wpix.collection_id') . ' IN (' . implode(',', $productCollection) . ')' .
				' AND ' . $db->qn('wpix.state') . ' = 1'
			)
				->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpix.collection_id'))
				->where($db->qn('w.state') . ' = 1');
		}

		$query->where($db->qn('pi.state') . ' = 1')
			->where($db->qn('pi.discontinued') . ' = 0');

		$db->setQuery($query);
		$results    = $db->loadObjectList();
		$issetItems = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($issetItems[$result->product_id]))
				{
					$issetItems[$result->product_id] = array();
				}

				$issetItems[$result->product_id][$result->values_ids] = $result;
			}
		}

		return $issetItems;
	}

	/**
	 * Get dynamic types
	 *
	 * @param   array   $ids              Object product values
	 * @param   array   $dropDownSelected Array drop down selected
	 * @param   boolean $useCollection    @todo please comment about this *bump*
	 *
	 * @return mixed
	 */
	public function getDynamicTypes($ids, $dropDownSelected = array(), $useCollection = true)
	{
		$ids = ArrayHelper::toInteger($ids);
		$db  = Factory::getDbo();

		$productCollection = $this->getState('filter.product_collection');

		// Select minimal ordering from current product
		$subQuery = $db->getQuery(true)
			->select('MIN(pa2.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa2'))
			->where('pa2.product_id = pa.product_id')
			->where('pa2.state = 1')
			->where('pa2.main_attribute != 1');

		$query = $db->getQuery(true)
			->select(array('pav.*', 'pa.name', 'pa.type_id', 'pa.product_id'))
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pi.product_id = pa.product_id')
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pa.ordering != (' . $subQuery . ')')
			->where('pa.main_attribute != 1')
			->where('pi.state = 1')
			->order('pav.ordering');

		// Select related to drop down select
		if (is_array($dropDownSelected) && count($dropDownSelected) > 0)
		{
			$where = array();

			foreach ($ids as $id)
			{
				if (isset($dropDownSelected[$id]))
				{
					$where[] = '(pav3.id = ' . (int) $dropDownSelected[$id] . ' AND pa.product_id = ' . (int) $id . ')';
				}
				else
				{
					$where[] = '(pa.product_id = ' . (int) $id . ')';
				}
			}

			$query->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx3') . ' ON pi.id = pivx3.product_item_id')
				->leftJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav3') . ' ON pav3.id = pivx3.product_attribute_value_id AND pav3.state = 1'
				)
				->leftJoin(
					$db->qn('#__redshopb_product_attribute', 'pa3')
					. ' ON pav3.product_attribute_id = pa3.id AND pa3.main_attribute = 1 AND pa3.state = 1 AND pa3.product_id = pa.product_id'
				)
				->where('(' . implode(' OR ', $where) . ')')
				->order('pav3.ordering');
		}
		else
		{
			$query->where('pa.product_id IN (' . implode(',', $ids) . ')');
		}

		if ($useCollection)
		{
			if (!empty($productCollection))
			{
				$query->innerJoin(
					$db->qn('#__redshopb_collection_product_item_xref', 'wpix') .
					' ON ' . $db->qn('wpix.product_item_id') . ' = ' . $db->qn('pi.id') .
					' AND ' . $db->qn('wpix.collection_id') .
					' IN (' . implode(',', $productCollection) . ')' .
					' AND ' . $db->qn('wpix.state') . ' = 1'
				)
					->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpix.collection_id'))
					->where($db->qn('w.state') . ' = 1');
			}
		}

		$query->group('pav.id')
			->order('pav.id ASC');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(
				RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			),
			array('pav'),
			array('pa.name')
		);
		$db->setQuery($query);
		$results = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		$dynamicTypes = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($dynamicTypes[$result->product_id]))
				{
					$dynamicTypes[$result->product_id] = array();
				}

				$dynamicTypes[$result->product_id][$result->id] = $result;
			}
		}

		return $dynamicTypes;
	}

	/**
	 * Get types from X axis
	 *
	 * @param   array   $ids              Object product values
	 * @param   array   $dropDownSelected Array drop down selected
	 * @param   boolean $useCollection    @todo please comment about this *bump*
	 *
	 * @return mixed
	 */
	public function getStaticTypes($ids, $dropDownSelected = array(), $useCollection = true)
	{
		$ids = ArrayHelper::toInteger($ids);
		$db  = Factory::getDbo();

		$productCollection = $this->getState('filter.product_collection');

		// Select minimal ordering from current product
		$subQuery = $db->getQuery(true)
			->select('MIN(pa2.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa2'))
			->where('pa2.product_id = pa.product_id')
			->where('pa2.state = 1')
			->where('pa2.main_attribute != 1');

		$query = $db->getQuery(true)
			->select(
				array(
					'pav.*', 'pa.product_id', 'pivx.product_item_id', 'pa.ordering', 'pa.name', 'pa.type_id'
				)
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pi.product_id = pa.product_id')
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pi.state = 1')
			->where('pa.ordering = (' . $subQuery . ')')
			->order('pav.ordering');

		// Select related to drop down select
		if (is_array($dropDownSelected) && count($dropDownSelected) > 0)
		{
			$where = array();

			foreach ($ids as $id)
			{
				if (isset($dropDownSelected[$id]))
				{
					$where[] = '(pav3.id = ' . (int) $dropDownSelected[$id] . ' AND pa.product_id = ' . (int) $id . ')';
				}
				else
				{
					$where[] = '(pa.product_id = ' . (int) $id . ')';
				}
			}

			$query->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx3') . ' ON pi.id = pivx3.product_item_id')
				->leftJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav3') . ' ON pav3.id = pivx3.product_attribute_value_id AND pav3.state = 1'
				)
				->leftJoin(
					$db->qn('#__redshopb_product_attribute', 'pa3')
					. ' ON pav3.product_attribute_id = pa3.id AND pa3.main_attribute = 1 AND pa3.state = 1 AND pa3.product_id = pa.product_id'
				)
				->where('(' . implode(' OR ', $where) . ')')
				->order('pav3.ordering');
		}
		else
		{
			$query->where('pa.product_id IN (' . implode(',', $ids) . ')');
		}

		if ($useCollection)
		{
			if (!empty($productCollection))
			{
				$query->innerJoin(
					$db->qn('#__redshopb_collection_product_item_xref', 'wpix') .
					' ON ' . $db->qn('wpix.product_item_id') . ' = ' . $db->qn('pi.id') .
					' AND ' . $db->qn('wpix.collection_id') . ' IN (' . implode(',', $productCollection) . ')' .
					' AND ' . $db->qn('wpix.state') . ' = 1'
				)
					->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpix.collection_id'))
					->where($db->qn('w.state') . ' = 1');
			}
		}

		$query->group('pav.id')
			->order('pav.id ASC');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(
				RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			),
			array('pav'),
			array('pa.name')
		);
		$db->setQuery($query);
		$results = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		$staticTypes = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($staticTypes[$result->product_id]))
				{
					$staticTypes[$result->product_id] = array();
				}

				$staticTypes[$result->product_id][] = $result;
			}
		}

		return $staticTypes;
	}

	/**
	 * Get drop down types
	 *
	 * @param   array $ids Object product values
	 *
	 * @return array|null
	 */
	public function getDropDownTypes($ids)
	{
		$ids = ArrayHelper::toInteger($ids);
		$db  = Factory::getDbo();

		$productCollection = $this->getState('filter.product_collection');

		$query = $db->getQuery(true)
			->select(array('pav.*', 'pa.product_id', 'pa.type_id', 'pa.name'))
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pa.product_id IN (' . implode(',', $ids) . ')')
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pi.state = 1')
			->where('pa.main_attribute = 1')
			->order('pav.ordering');

		if (!empty($productCollection))
		{
			$query->innerJoin(
				$db->qn('#__redshopb_collection_product_item_xref', 'wpix') . ' ON ' . $db->qn('wpix.product_item_id') . ' = ' . $db->qn('pi.id') .
				' AND ' . $db->qn('wpix.collection_id') . ' IN (' . implode(',', $productCollection) . ')' .
				' AND ' . $db->qn('wpix.state') . ' = 1'
			)
				->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpix.collection_id'))
				->where($db->qn('w.state') . ' = 1')
				->order('pav.id ASC')
				->group('pav.id, w.id');
		}
		else
		{
			$query->group('pav.id');
		}

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			)
		);
		$db->setQuery($query);
		$results = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		$dropDownTypes = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($dropDownTypes[$result->product_id]))
				{
					$dropDownTypes[$result->product_id] = array();
				}

				$result->description                  = RedshopbHelperProduct_Attribute::getAttributeDescription($result->product_id, $result->id);
				$dropDownTypes[$result->product_id][] = $result;
			}
		}

		return $dropDownTypes;
	}

	/**
	 * Get product complimentary products
	 *
	 * @param   array $ids Object product values
	 *
	 * @return  array
	 */
	public function getComplimentaryProducts($ids)
	{
		$complimentaryProducts = array();

		// Get products
		if (!empty($ids))
		{
			$ids     = ArrayHelper::toInteger($ids);
			$db      = Factory::getDbo();
			$query   = $db->getQuery(true)
				->select(
					array(
						'p2.*',
						'pia.*',
						$db->qn('pia.complimentary_product_id', 'complimentary_id'),
					)
				)
				->from($db->qn('#__redshopb_product_complimentary', 'pia'))
				->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = pia.product_id')
				->leftJoin($db->qn('#__redshopb_product', 'p2') . ' ON p2.id = pia.complimentary_product_id')
				->where('pia.state = 1')
				->where('p2.state = 1')
				->where('p.state = 1')
				->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('p.id IN (' . implode(',', $ids) . ')')
				->group('pia.id');
			$results = $db->setQuery($query)->loadObjectList();

			if (!empty($results))
			{
				foreach ($results as $result)
				{
					$complimentaryProducts[$result->product_id][] = $result;
				}
			}
		}

		return $complimentaryProducts;
	}

	/**
	 * Get product accessories
	 *
	 * @param   array  $productIds       Object product values
	 * @param   array  $dropDownSelected Array drop down selected
	 * @param   int    $departmentId     Department id.
	 * @param   int    $customerId       Customer id.
	 * @param   string $customerType     Customer type.
	 * @param   string $currency         Currency.
	 * @param   int    $endCustomer      End customer company id.
	 *
	 * @return array
	 */
	public function getAccessories(
		$productIds, $dropDownSelected = array(), $departmentId = 0,
		$customerId = 0, $customerType = '', $currency = 'DKK', $endCustomer = 0
	)
	{
		$accessories = array();
		$productIds  = ArrayHelper::toInteger($productIds);
		$db          = Factory::getDbo();

		/** @var RedshopbModelProduct $model */
		$model = RModelAdmin::getInstance('Product', 'RedshopbModel');

		$productCollection = $this->getState('filter.product_collection');

		// Split Product Ids and Product Item Ids
		$productItemIds = array();
		$products       = array();

		foreach ($productIds as $key => $productId)
		{
			if (array_key_exists($productId, $dropDownSelected))
			{
				$productItemIds[] = $productId;
				unset($productIds[$key]);
			}
		}

		reset($productIds);

		// Get products
		if (!empty($productIds))
		{
			$query    = $model->getAccessoriesQuery($productIds, $departmentId, $productCollection);
			$products = $db->setQuery($query)->loadObjectList();
		}

		// Get product items
		$productItems = array();

		if (is_array($dropDownSelected) && count($dropDownSelected) > 0)
		{
			$query        = $model->getAccessoriesQuery($productItemIds, $departmentId, $productCollection, $dropDownSelected);
			$productItems = $db->setQuery($query)->loadObjectList();
		}

		$results = array_merge($products, $productItems);

		if (empty($results))
		{
			return array();
		}

		$isCollectionShopping = RedshopbHelperShop::inCollectionMode(
			RedshopbEntityCompany::getInstance(
				RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
			)
		);

		// Prepare accessories data
		foreach ($results as $result)
		{
			if ($isCollectionShopping)
			{
				$priceObject = RedshopbHelperPrices::getProductPrice(
					$result->accessory_product_id, $customerId, $customerType, $currency, array(), '', $endCustomer
				);

				if (!empty($priceObject))
				{
					$result->price       = $priceObject->price;
					$result->currency    = $priceObject->currency;
					$result->currency_id = $priceObject->currency_id;
				}
				else
				{
					$result->price       = (float) 0;
					$currencyObj         = RedshopbHelperProduct::getCurrency($currency);
					$result->currency    = $currencyObj->alpha3;
					$result->currency_id = $currencyObj->id;
				}
			}

			$accessories[$result->product_id][] = $result;
		}

		return $accessories;
	}

	// @ToDo: Remove following function in future release, we have this function in multiple places

	/**
	 * Gets company details
	 *
	 * @param   int $companyId The primary key id for the item.
	 *
	 * @return  object  Company details
	 */
	public function getCompany($companyId)
	{
		$table = $this->getTable('Company', 'RedshopbTable');
		$table->load($companyId);

		$properties = $table->getProperties(1);
		$item       = ArrayHelper::toObject($properties, CMSObject::class);

		return $item;
	}

	/**
	 * Get parents from current node
	 *
	 * @param   string $table      Name table
	 * @param   int    $id         Id current node
	 * @param   array  $allowItems Array allow items
	 *
	 * @return  mixed
	 */
	public function getParents($table, $id, $allowItems = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('parent.*')
			->from($db->qn($table, 'parent'))
			->leftJoin($db->qn($table, 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('parent.deleted') . ' = 0')
			->where('node.id = ' . (int) $id)
			->where('parent.level > 0')
			->where($db->qn('parent.deleted') . ' = 0')
			->order('parent.lft');

		if ($table == '#__redshopb_company')
		{
			$query->select($db->qn('d.id', 'department_id'))
				->leftJoin(
					$db->qn('#__redshopb_department', 'd') .
					' ON d.company_id = parent.id AND d.level = 1 AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
				)
				->order('d.lft')
				->group('parent.id');
		}

		// If the user is a super User (Joomla Admin), it doesn't use the user Id, since it's not tied to any specific company
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$itemsIds = array(0);

			foreach ($allowItems as $item)
			{
				$itemsIds[] = (int) $item->id;
			}

			$query->where('parent.id IN (' . implode(',', $itemsIds) . ')');
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get cart Items from Session
	 *
	 * @return mixed
	 */
	public function getCartItems()
	{
		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		return RedshopbHelperCart::getCart($customerId, $customerType)->get('items', array());
	}

	/**
	 * Get all orders made from cart.
	 *
	 * @return   array  List of customer orders.
	 */
	public function getCustomerOrders()
	{
		$cartCustomers  = RedshopbHelperCart::getCartCustomers();
		$customerOrders = array();

		foreach ($cartCustomers as $cartCustomer)
		{
			$customerInfo = explode('.', $cartCustomer);
			$customerType = $customerInfo[0];
			$customerId   = $customerInfo[1];

			$isWalletCart         = false;
			$transformed          = array();
			$cartData             = RedshopbHelperCart::getCart($customerId, $customerType);
			$cartItems            = $cartData->get('items', array());
			$cartOffers           = $cartData->get('offers', array());
			$availableCollections = array();
			$dbFields             = RedshopbHelperCart::cartFieldsForCheck(true);

			if (!empty($cartItems))
			{
				foreach ($cartItems AS $cartItem)
				{
					$new = new stdClass;

					foreach ($cartItem as $cartItemKey => $cartItemValue)
					{
						$new->{$cartItemKey} = $cartItemValue;
					}

					// Convert cart fields values to DB
					foreach (RedshopbHelperCart::cartFieldsForCheck() as $fieldToCheckKey => $fieldToCheck)
					{
						if (array_key_exists($fieldToCheck, $cartItem))
						{
							$new->{$dbFields[$fieldToCheckKey]} = $cartItem[$fieldToCheck];
						}
					}

					$new->product_item_id        = $cartItem['productItem'];
					$new->product_id             = $cartItem['productId'];
					$new->price                  = !empty($cartItem['price']) ? $cartItem['price'] : 0;
					$new->discount               = !empty($cartItem['discount']) ? $cartItem['discount'] : 0;
					$new->discount_type          = !empty($cartItem['discount_type']) ? $cartItem['discount_type'] : null;
					$new->price_without_discount = !empty($cartItem['price_without_discount']) ? $cartItem['price_without_discount'] : $new->price;
					$new->accessories            = isset($cartItem['accessories']) ? $cartItem['accessories'] : array();
					$new->id                     = 0;
					$new->order_id               = 0;
					$new->collectionId           = isset($cartItem['collectionId']) ? $cartItem['collectionId'] : 0;
					$new->keyAccessories         = isset($cartItem['keyAccessories']) ? $cartItem['keyAccessories'] : '';
					$new->stockroom              = isset($cartItem['stockroom']) ? $cartItem['stockroom'] : '';
					$new->stockroomId            = isset($cartItem['stockroomId']) ? $cartItem['stockroomId'] : '';
					$new->wallet                 = isset($cartItem['wallet']) ? $cartItem['wallet'] : false;

					$isWalletCart = (!$isWalletCart) ? $new->wallet : $isWalletCart;

					$currency = (is_numeric($cartItem['currency'])) ? $cartItem['currency'] : RHelperCurrency::getCurrency($cartItem['currency'])->id;

					if (!isset($transformed[$currency]))
					{
						$transformed[$currency]          = new stdClass;
						$transformed[$currency]->regular = array();
						$transformed[$currency]->offers  = array();
					}

					$transformed[$currency]->regular[] = $new;

					if ($new->collectionId)
					{
						$availableCollections[$new->collectionId] = $new->collectionId;
					}

					$hidePrice = false;
					RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$new->price, &$hidePrice, 0, $new->productId));

					if ($hidePrice)
					{
						$new->hiddenPrice                = $new->price;
						$new->hiddenPriceWithoutDiscount = $new->price_without_discount;

						$new->price                  = 0;
						$new->price_without_discount = 0;
					}
				}
			}

			if (!empty($cartOffers))
			{
				foreach ($cartOffers as $offerId => $offer)
				{
					$offer    = (object) $offer;
					$currency = $offer->currency;

					if (!isset($transformed[$currency]))
					{
						$transformed[$currency]          = new stdClass;
						$transformed[$currency]->regular = array();
						$transformed[$currency]->offers  = array();
					}

					if (!isset($transformed[$currency]->offers[$offerId]))
					{
						$transformed[$currency]->offers[$offerId] = $offer;
					}

					if ($offer->collection_id)
					{
						$availableCollections[$offer->collection_id] = $offer->collection_id;
					}

					foreach ($transformed[$currency]->offers[$offerId]->items as $key => $item)
					{
						$item = (object) $item;

						// Convert cart fields values to DB
						foreach (RedshopbHelperCart::cartFieldsForCheck() as $fieldToCheckKey => $fieldToCheck)
						{
							if (property_exists($item, $fieldToCheck))
							{
								$item->{$dbFields[$fieldToCheckKey]} = $item->{$fieldToCheck};
							}
						}

						$item->price                                           = isset($item->unit_price) ? $item->unit_price : 0;
						$item->price_without_discount                          = isset($item->price_without_discount) ?
							$item->price_without_discount : $item->price;
						$item->accessories                                     = isset($item->accessories) ? $item->accessories : array();
						$item->id                                              = 0;
						$item->order_id                                        = 0;
						$item->keyAccessories                                  = isset($item->keyAccessories) ? $item->keyAccessories : '';
						$item->stockroom                                       = isset($item->stockroom) ? $item->stockroom : '';
						$item->stockroomId                                     = isset($item->stockroomId) ? $item->stockroomId : '';
						$item->wallet                                          = isset($item->wallet) ? $item->wallet : false;
						$isWalletCart                                          = (!$isWalletCart) ? $item->wallet : $isWalletCart;
						$transformed[$currency]->offers[$offerId]->items[$key] = $item;
					}
				}
			}

			foreach ($transformed as $currency => $cartItems)
			{
				$customerCurrencyOrders                                 = new stdClass;
				$customerCurrencyOrders->regular                        = new stdClass;
				$customerCurrencyOrders->offers                         = array();
				$customerCurrencyOrders->currency                       = RedshopbHelperProduct::getCurrency($currency)->alpha3;
				$customerCurrencyOrders->currency_id                    = $currency;
				$customerCurrencyOrders->customerType                   = $customerType;
				$customerCurrencyOrders->customerId                     = $customerId;
				$customerCurrencyOrders->total                          = 0;
				$customerCurrencyOrders->hiddenTotal                    = 0;
				$customerCurrencyOrders->isHidden                       = false;
				$customerCurrencyOrders->taxs                           = array();
				$customerCurrencyOrders->tax                            = 0;
				$customerCurrencyOrders->subtotalWithoutDiscounts       = 0;
				$customerCurrencyOrders->hiddenSubtotalWithoutDiscounts = 0;
				$customerCurrencyOrders->totalFinal                     = 0;
				$customerCurrencyOrders->shipping                       = 0;

				if (!empty($cartItems->regular))
				{
					// Setting shipping to 0. We should checkout with a client how can we get shipping price per item.
					$customerOrder                                 = new stdClass;
					$customerOrder->total                          = (float) 0;
					$customerOrder->hiddenTotal                    = (float) 0;
					$customerOrder->isHidden                       = false;
					$customerOrder->currency                       = RedshopbHelperProduct::getCurrency($currency)->alpha3;
					$customerOrder->currency_id                    = $currency;
					$customerOrder->items                          = array();
					$customerOrder->discount_type                  = RedshopbHelperPrices::DISCOUNT_PERCENTAGE;
					$customerOrder->discount                       = 0;
					$customerOrder->shipping                       = 0;
					$customerOrder->offer_id                       = 0;
					$customerOrder->subtotalWithoutDiscounts       = 0;
					$customerOrder->hiddenSubtotalWithoutDiscounts = 0;
					$customerOrder->customerType                   = $customerType;
					$customerOrder->customerId                     = $customerId;
					$customerOrder->hasDelayProduct                = false;

					foreach ($cartItems->regular as $key => $item)
					{
						$item->id                = 0;
						$item->product_name      = RedshopbHelperProduct::getName($item->product_id);
						$item->product_sku       = RedshopbHelperProduct::getSKU($item->product_id);
						$item->currency          = $customerOrder->currency;
						$item->currency_id       = $customerOrder->currency_id;
						$item->order_id          = 0;
						$item->attributes        = RedshopbHelperProduct_Item::getAttributesValues($item->product_item_id, true);
						$item->attributesDefault = RedshopbHelperProduct_Item::getAttributesValues($item->product_item_id);
						$item->product_item_code = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
						$item->product_item_sku  = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();

						if (isset($item->hiddenPrice))
						{
							$customerOrder->hiddenTotal += $item->hiddenPrice * $item->quantity;
							$customerOrder->isHidden     = true;
						}

						if ($item->product_item_id)
						{
							// If this item is an product item
							$item->product_item_code = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
							$item->product_item_sku  = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
						}

						$item->stockroom_id   = 0;
						$item->stock          = 0;
						$item->stockroom_name = '';

						if ($item->params->get('delayed_order', 0) == 1)
						{
							$customerOrder->hasDelayProduct = true;
						}

						if (!empty($item->stockroom))
						{
							$item->stockroom_id   = $item->stockroom->stockroom_id;
							$item->stockroom_name = RedshopbEntityStockroom::getInstance($item->stockroom->stockroom_id)->get('name');

							if ($item->stockroom->unlimited)
							{
								$item->stock = -1;
							}
							else
							{
								$item->stock = (int) $item->stockroom->amount;
							}
						}

						$accessoriesPrice = 0;

						if (isset($item->accessories))
						{
							foreach ($item->accessories as $accessory)
							{
								if (array_key_exists('quantity', $accessory))
								{
									$accessoriesPrice += (float) ($accessory['price'] * $accessory['quantity']);
								}
								else
								{
									$accessoriesPrice += (float) $accessory['price'];
								}
							}
						}

						if (isset($item->hiddenPrice))
						{
							$customerOrder->hiddenAccessoriesPrice = $accessoriesPrice;
							$item->hiddenFinalPrice                = ($item->hiddenPrice + $accessoriesPrice) * $item->quantity;
							$customerOrder->hiddenTotal           += $accessoriesPrice * $item->quantity;
							$customerOrder
								->hiddenSubtotalWithoutDiscounts  += ($item->hiddenPriceWithoutDiscount + $accessoriesPrice) * $item->quantity;

							$accessoriesPrice = 0;
						}

						$item->final_price                        = isset($item->subtotal)
							? $item->subtotal
							: ($item->price + $accessoriesPrice) * $item->quantity;
						$customerOrder->total                    += isset($item->subtotal)
							? $item->subtotal
							: ($item->price + $accessoriesPrice) * $item->quantity;
						$customerOrder->subtotalWithoutDiscounts += ($item->price_without_discount + $accessoriesPrice) * $item->quantity;

						// Get product taxes
						$taxes = RedshopbHelperTax::getProductsTaxRates(array($item->product_id), $customerId, $customerType);

						if ($taxes)
						{
							if (!is_array($taxes[$item->product_id]))
							{
								$taxes[$item->product_id] = array($taxes[$item->product_id]);
							}

							foreach ($taxes[$item->product_id] as $tax)
							{
								$singleTax           = new stdClass;
								$singleTax->name     = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item->product_name);
								$singleTax->tax_rate = $tax->tax_rate;
								$singleTax->tax      = $item->final_price * $tax->tax_rate;

								$customerCurrencyOrders->taxs[] = $singleTax;
								$customerCurrencyOrders->tax   += $singleTax->tax;
							}
						}

						$customerOrder->items[] = $item;
					}

					RFactory::getDispatcher()->trigger('onAfterRedshopbProcessCartItems', array(&$customerOrder, $cartData));

					$customerOrder->hiddenTotalFinal                         = $customerOrder->hiddenTotal;
					$customerCurrencyOrders->hiddenSubtotalWithoutDiscounts += $customerOrder->subtotalWithoutDiscounts;
					$customerCurrencyOrders->hiddenTotal                    += $customerOrder->hiddenTotal;

					$customerOrder->totalFinal                         = $customerOrder->total;
					$customerOrder->isWalletCart                       = $isWalletCart;
					$customerCurrencyOrders->subtotalWithoutDiscounts += $customerOrder->subtotalWithoutDiscounts;
					$customerCurrencyOrders->total                    += $customerOrder->total;
					$customerCurrencyOrders->regular                   = $customerOrder;
				}

				if (!empty($cartItems->offers))
				{
					foreach ($cartItems->offers as $offerId => $offer)
					{
						// Setting shipping to 0. We should checkout with a client how can we get shipping price per item.
						$offer->shipping                 = 0;
						$offer->customerType             = $customerType;
						$offer->customerId               = $customerId;
						$offer->currency                 = RedshopbHelperProduct::getCurrency($currency)->alpha3;
						$offer->currency_id              = $currency;
						$offer->offer_id                 = $offer->id;
						$offer->subtotalWithoutDiscounts = 0;
						$offer->customerType             = $customerType;
						$offer->customerId               = $customerId;

						foreach ($offer->items as $key => $item)
						{
							$item->id                = 0;
							$item->product_name      = RedshopbHelperProduct::getName($item->product_id);
							$item->product_sku       = RedshopbHelperProduct::getSKU($item->product_id);
							$item->order_id          = 0;
							$item->attributes        = RedshopbHelperProduct_Item::getAttributesValues($item->product_item_id, true);
							$item->attributesDefault = RedshopbHelperProduct_Item::getAttributesValues($item->product_item_id);
							$item->product_item_code = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
							$item->product_item_sku  = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
							$item->collectionId      = $offer->collection_id;
							$item->currency          = $offer->currency;
							$item->currency_id       = $offer->currency_id;

							if ($item->product_item_id)
							{
								// If this item is an product item
								$item->product_item_code = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
								$item->product_item_sku  = RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getSku();
							}

							$item->stockroom_id   = 0;
							$item->stock          = 0;
							$item->stockroom_name = '';

							if (!empty($item->stockroom))
							{
								$item->stockroom_id   = $item->stockroom->stockroom_id;
								$item->stockroom_name = RedshopbEntityStockroom::getInstance($item->stockroom->stockroom_id)->get('name');

								if ($item->stockroom->unlimited)
								{
									$item->stock = -1;
								}
								else
								{
									$item->stock = (int) $item->stockroom->amount;
								}
							}

							$item->final_price                = $item->total;
							$offer->subtotalWithoutDiscounts += $item->subtotal;

							// Get product taxes
							$taxes = RedshopbHelperTax::getProductsTaxRates(array($item->product_id), $customerId, $customerType);

							if ($taxes)
							{
								foreach ($taxes[$item->product_id] as $tax)
								{
									$singleTax           = new stdClass;
									$singleTax->name     = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item->product_name);
									$singleTax->tax_rate = $tax->tax_rate;
									$singleTax->tax      = $item->final_price * $tax->tax_rate;

									$customerCurrencyOrders->taxs[] = $singleTax;
									$customerCurrencyOrders->tax   += $singleTax->tax;
								}
							}
						}

						$offer->isWalletCart                               = $isWalletCart;
						$offer->totalFinal                                 = $offer->total;
						$customerCurrencyOrders->total                    += $offer->total;
						$customerCurrencyOrders->subtotalWithoutDiscounts += $offer->subtotalWithoutDiscounts;
						$customerCurrencyOrders->offers[]                  = $offer;
					}
				}

				// Get global taxes
				$taxes = RedshopbHelperTax::getTaxRates($customerId, $customerType);

				if ($taxes)
				{
					foreach ($taxes as $tax)
					{
						$singleTax           = new stdClass;
						$singleTax->name     = $tax->name;
						$singleTax->tax_rate = $tax->tax_rate;
						$singleTax->tax      = $customerCurrencyOrders->total * $tax->tax_rate;

						$customerCurrencyOrders->taxs[] = $singleTax;
						$customerCurrencyOrders->tax   += $singleTax->tax;
					}
				}

				$customerCurrencyOrders->isWalletCart = $isWalletCart;
				$customerCurrencyOrders->totalFinal   = $customerCurrencyOrders->total
					+ $customerCurrencyOrders->tax
					+ $customerCurrencyOrders->shipping;
				$customerOrders[]                     = $customerCurrencyOrders;
			}
		}

		Factory::getApplication()->triggerEvent('onRedshopbGetCustomerOrders', array(&$customerOrders));

		return $customerOrders;
	}

	/**
	 * Proxy to Orders getCustomForm method
	 *
	 * @param   string $formName Forms name
	 *
	 * @return Form object
	 */
	public function getCustomForm($formName)
	{
		return RModel::getAutoInstance('Orders')->getCustomForm($formName);
	}

	/**
	 * Clear Cart from Session
	 *
	 * @param   bool $clearAllEmployees Clear cart all employees
	 *
	 * @return mixed
	 */
	public function clearCart($clearAllEmployees = false)
	{
		return RedshopbHelperCart::clearCartFromSession($clearAllEmployees);
	}

	/**
	 * Removes an item from the cart
	 *
	 * @NOTE @since 1.13.2 the method signature has been changed
	 *
	 * @param   string    $hash           Unique item hash
	 * @param   integer   $customerId     Needed to get the correct cart
	 * @param   string    $customerType   Needed to get the correct cart
	 *
	 * @return   RedshopbHelperCart_Object
	 */
	public function removeFromCart($hash, $customerId, $customerType)
	{
		return RedshopbHelperCart::removeFromCartByHash($hash, $customerId, $customerType);
	}

	/**
	 * Update product item quantity.
	 *
	 * @NOTE @since 1.13.2 the method signature has been changed
	 *
	 * @param   string          $hash           Unique item hash
	 * @param   integer         $customerId     Needed to get the correct cart
	 * @param   string          $customerType   Needed to get the correct cart
	 * @param   integer|float   $quantity       Quantity to add
	 *
	 * @return   array
	 */
	public function updateCartProductQuantity($hash, $customerId, $customerType, $quantity)
	{
		return RedshopbHelperCart::setItemQuantityByHash($hash, $customerId, $customerType, $quantity);
	}

	/**
	 * Add new item in shopping cart
	 *
	 * @param   int    $productId        Product id
	 * @param   int    $productItem      Product item
	 * @param   string $accessory        Selected accessory id
	 * @param   int    $quantity         Product item quantity
	 * @param   float  $price            Price
	 * @param   string $currency         Products currency
	 * @param   int    $customerId       Customer id.
	 * @param   string $customerType     Customer type.
	 * @param   string $collectionId     Include collections ids
	 * @param   int    $dropDownSelected Id seleceted attribute value
	 * @param   int    $stockroomId      ID of stockroom
	 *
	 * @return array
	 */
	public function addNewCartItem(
		$productId,
		$productItem,
		$accessory = null,
		$quantity = 1,
		$price = 0.0,
		$currency = 'DKK',
		$customerId = 0,
		$customerType = '',
		$collectionId = null,
		$dropDownSelected = 0,
		$stockroomId = 0
	)
	{
		return RedshopbHelperCart::addToCartById(
			$productId,
			$productItem,
			$accessory,
			$quantity,
			$price,
			$currency,
			$customerId,
			$customerType,
			$collectionId,
			$dropDownSelected,
			$stockroomId
		);
	}

	/**
	 * Prepare products or items data for Shop view
	 *
	 * @param   array|false  $items         Item list
	 * @param   integer      $customerId    Customer id.
	 * @param   string       $customerType  Customer type.
	 * @param   integer      $collectionId  Collection Id
	 * @param   boolean      $isProducts    If true, the $items array will be treated as products, not as items
	 *
	 * @return  mixed
	 * @throws  Exception
	 */
	public function prepareItemsForShopView($items, $customerId = 0, $customerType = '', $collectionId = 0, $isProducts = false)
	{
		$app                     = Factory::getApplication();
		$preparedItems           = new stdClass;
		$shopOnBehalfEndCustomer = false;

		$userComp = RedshopbHelperUser::getUserCompany();

		if (!$customerId || $customerType == '')
		{
			$company = RedshopbApp::getB2cCompany()->getItem();
		}
		else
		{
			$company = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		}

		if ($company)
		{
			$currency = RedshopbEntityCompany::getInstance($company->id)->getCustomerCurrency();
		}
		else
		{
			$currency = RedshopbApp::getConfig()->get('default_currency', 38);
		}

		if ($userComp === null || $userComp === new stdClass)
		{
			$userComp = RedshopbHelperCompany::getCustomerCompanyByCustomer($customerId, $customerType);
		}

		if ($items && !empty($items))
		{
			$ids      = array();
			$products = array();

			foreach ($items as $item)
			{
				$itemId = is_numeric($item) ? $item : (int) $item->id;
				$ids[]  = $itemId;

				if (isset($item->id))
				{
					$products[$item->id] = $item;
				}
			}

			RedshopbHelperProduct::setProduct($products);
			$items = RedshopbHelperProduct::getProductsData($items, $ids);

			if ($userComp->type == 'customer' && $company->type == 'end_customer' && $customerType != 'employee')
			{
				if (!is_null($userComp->currency_id))
				{
					$preparedItems->currency = $userComp->currency_id;
				}
				else
				{
					$config                  = RedshopbEntityConfig::getInstance();
					$currency                = $config->getInt('default_currency', 38);
					$preparedItems->currency = $currency;
				}

				$shopOnBehalfEndCustomer = true;
			}
			else
			{
				// Only end customers will use collection own pricing system. Customers will use real price
				if ($collectionId > 0)
				{
					$preparedItems->currency = RedshopbHelperCollection::getCurrency($collectionId);
				}
				else
				{
					$preparedItems->currency = $currency;
				}
			}

			$preparedItems->items        = $items;
			$preparedItems->ids          = $ids;
			$preparedItems->collectionId = $collectionId;
			$preparedItems->showStockAs  = RedshopbHelperStockroom::getStockVisibility();

			// Specific actions for products
			if ($isProducts)
			{
				// Product prices
				if ($shopOnBehalfEndCustomer)
				{
					$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
						$ids, $userComp->id, 'company', $preparedItems->currency, array(), '', $company->id
					);
				}
				elseif (!empty($collectionId))
				{
					$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
						$ids, $customerId, $customerType, $preparedItems->currency, array($collectionId)
					);
				}
				else
				{
					$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
						$ids, $customerId, $customerType, $preparedItems->currency, array(0), '', $company->id
					);
				}

				$preparedItems->productImages = $this->getProductImages($ids, null);

				if ($shopOnBehalfEndCustomer)
				{
					$preparedItems->accessories = $this->getAccessories(
						$ids,
						array(),
						0,
						$userComp->id,
						'company',
						$preparedItems->currency,
						$company->id
					);
				}
				else
				{
					$preparedItems->accessories = $this->getAccessories(
						$ids,
						array(),
						0,
						$customerId,
						$customerType,
						$preparedItems->currency
					);
				}
			}
			else
				// Specific actions for product items
			{
				$preparedItems->dropDownTypes    = $this->getDropDownTypes($ids);
				$preparedItems->dropDownSelected = array();
				$filterColor                     = $this->state->get('filter.attribute_flat_display', '');

				foreach ($preparedItems->dropDownTypes as $productId => $product)
				{
					$selected = '';

					$dropDownSelectedFromRequest = $app->getUserStateFromRequest(
						'list.drop_down_selected.' . $customerType . '_' . $customerId . '_' . $productId, 'drop_down_selected',
						0,
						'int'
					);

					if ($filterColor !== '')
					{
						foreach ($product as $dropDownType)
						{
							if ($selected == '')
							{
								$selected = $dropDownType->id;
							}

							if ($dropDownType->string_value == $filterColor)
							{
								$selected = $dropDownType->id;
								break;
							}
						}

						$preparedItems->dropDownSelected[$productId] = $selected;
					}
					elseif ($dropDownSelectedFromRequest)
					{
						$preparedItems->dropDownSelected[$productId] = $dropDownSelectedFromRequest;
					}
					else
					{
						foreach ($product as $dropDownType)
						{
							if ($selected == '')
							{
								$selected = $dropDownType->id;
							}

							if ($dropDownType->selected)
							{
								$selected = $dropDownType->id;
								break;
							}
						}

						$preparedItems->dropDownSelected[$productId] = $selected;
					}
				}

				$preparedItems->staticTypes          = $this->getStaticTypes($ids, $preparedItems->dropDownSelected);
				$preparedItems->dynamicTypes         = $this->getDynamicTypes($ids, $preparedItems->dropDownSelected);
				$preparedItems->issetItems           = $this->getIssetItems($ids, $preparedItems->dropDownSelected);
				$preparedItems->issetDynamicVariants = $this->getIssetDynamicVariants($ids, $preparedItems->dropDownSelected);
				$preparedItems->productImages        = $this->getProductImages($ids, $preparedItems->dropDownSelected);

				if ($shopOnBehalfEndCustomer)
				{
					$preparedItems->accessories = $this->getAccessories(
						$ids,
						$preparedItems->dropDownSelected,
						0,
						$userComp->id,
						'company',
						$preparedItems->currency,
						$company->id
					);
				}
				else
				{
					$preparedItems->accessories = $this->getAccessories(
						$ids,
						$preparedItems->dropDownSelected,
						0,
						$customerId,
						$customerType,
						$preparedItems->currency
					);
				}

				if ($preparedItems->issetItems)
				{
					$itemIds = array();

					foreach ($preparedItems->issetItems as $product)
					{
						foreach ($product as $item)
						{
							$itemIds[] = $item->id;
						}
					}

					// Price set from product items
					if ($shopOnBehalfEndCustomer)
					{
						$preparedItems->prices = RedshopbHelperPrices::getProductItemsPrice(
							$itemIds, array(), $userComp->id, 'company', $preparedItems->currency, array(), '', $company->id
						);
					}
					elseif (!empty($collectionId))
					{
						$preparedItems->prices = RedshopbHelperPrices::getProductItemsPrice(
							$itemIds, array(), $customerId, $customerType, $preparedItems->currency, array($collectionId)
						);
					}
					else
					{
						$preparedItems->prices = RedshopbHelperPrices::getProductItemsPrice(
							$itemIds, array(), $customerId, $customerType, $preparedItems->currency, array(0), '', $company->id
						);
					}
				}

				// Only product without items
				else
				{
					// Product prices
					if ($shopOnBehalfEndCustomer)
					{
						$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
							$ids, $userComp->id, 'company', $preparedItems->currency, array(), '', $company->id
						);
					}
					elseif (!empty($collectionId))
					{
						$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
							$ids, $customerId, $customerType, $preparedItems->currency, array($collectionId)
						);

						// If a collection does not have a collection price, get it without collectionId
						if (empty($preparedItems->prices))
						{
							$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
								$ids, $customerId, $customerType, $preparedItems->currency, array()
							);
						}
					}
					else
					{
						$preparedItems->prices = RedshopbHelperPrices::getProductsPrice(
							$ids, $customerId, $customerType, $preparedItems->currency, array(0)
						);
					}
				}
			}

			$preparedItems->complimentaryProducts = $this->getComplimentaryProducts($ids);

			RFactory::getDispatcher()
				->trigger('onRedshopbPrepareItemsForShopView', array($preparedItems, $items, $customerId, $customerType, $collectionId, $isProducts));
		}

		return $preparedItems;
	}

	/**
	 * Get wash and care info for product
	 *
	 * @param   int $productId Product id
	 *
	 * @return mixed
	 */
	public function getWash($productId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('wc.*')
			->from($db->qn('#__redshopb_wash_care_spec', 'wc'))
			->innerJoin($db->qn('#__redshopb_product_wash_care_spec_xref', 'wcx') . ' ON wc.id = wcx.wash_care_spec_id')
			->where('wcx.product_id = ' . (int) $productId)
			->order('wcx.ordering ASC');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get wash and care product data
	 *
	 * @param   int $productId Product id
	 * @param   int $colorId   Color Id
	 *
	 * @return mixed
	 */
	public function getWashProduct($productId, $colorId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('p.*', 'pd.description_intro', 'pd.description'))
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin(
				$db->qn('#__redshopb_product_descriptions', 'pd')
				. ' ON p.id = pd.product_id AND main_attribute_value_id = ' . (int) $colorId
			)
			->where('p.id = ' . (int) $productId);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get stock visibility status for the current logged-in user
	 *
	 * @deprecated  Use RedshopbHelperStockroom::getStockVisibility(); instead
	 *
	 * @return mixed
	 */
	public function getStockVisibility()
	{
		return RedshopbHelperStockroom::getStockVisibility();
	}

	/**
	 * Create and print product list PDF.
	 *
	 * @param   boolean $showStock Show products stock.
	 * @param   int     $currency  Currency id.
	 *
	 * @return object Products object for products list.
	 */
	public function getPrintProductsList($showStock = false, $currency = 38)
	{
		$app   = Factory::getApplication();
		$items = $this->getItems();
		$ids   = array();

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$ids[] = (int) $item->id;
			}
		}

		$filterColor                     = $this->state->get('filter.attribute_flat_display', '');
		$preparedItems                   = new stdClass;
		$preparedItems->dropDownTypes    = $this->getDropDownTypes($ids);
		$preparedItems->dropDownSelected = array();

		foreach ($preparedItems->dropDownTypes as $productId => $product)
		{
			$selected = '';

			$dropDownSelectedFromRequest = $app->getUserStateFromRequest(
				'list.drop_down_selected.' . $this->customerType . '_' . $this->customerId . '_' . $productId, 'drop_down_selected',
				0,
				'int'
			);

			if ($filterColor !== '')
			{
				foreach ($product as $dropDownType)
				{
					if ($selected == '')
					{
						$selected = $dropDownType->id;
					}

					if ($dropDownType->string_value == $filterColor)
					{
						$selected = $dropDownType->id;
						break;
					}
				}

				$preparedItems->dropDownSelected[$productId] = $selected;
			}
			elseif ($dropDownSelectedFromRequest)
			{
				$preparedItems->dropDownSelected[$productId] = $dropDownSelectedFromRequest;
			}
			else
			{
				foreach ($product as $dropDownType)
				{
					if ($selected == '')
					{
						$selected = $dropDownType->id;
					}

					if ($dropDownType->selected)
					{
						$selected = $dropDownType->id;
						break;
					}
				}

				$preparedItems->dropDownSelected[$productId] = $selected;
			}
		}

		$preparedItems->items    = $items;
		$preparedItems->currency = $currency;

		$preparedItems->staticTypes          = $this->getStaticTypes($ids, $preparedItems->dropDownSelected, false);
		$preparedItems->dynamicTypes         = $this->getDynamicTypes($ids, $preparedItems->dropDownSelected, false);
		$preparedItems->issetItems           = $this->getIssetItems($ids, $preparedItems->dropDownSelected);
		$preparedItems->productImages        = $this->getProductImages($ids, $preparedItems->dropDownSelected);
		$preparedItems->issetDynamicVariants = $this->getIssetDynamicVariants($ids, $preparedItems->dropDownSelected);

		if ($showStock)
		{
			$preparedItems->showStockAs = 'actual_stock';
		}

		if ($preparedItems->issetItems)
		{
			$itemIds = array();

			foreach ($preparedItems->issetItems as $product)
			{
				foreach ($product as $item)
				{
					$itemIds[] = $item->id;
				}
			}

			$preparedItems->prices = RedshopbHelperPrices::getProductItemsPrice(
				$itemIds, array(), $this->customerId, $this->customerType, $currency
			);
		}

		return $preparedItems;
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
		$db  = $this->getDbo();
		$app = Factory::getApplication();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		if ($app->getUserState('shop.layout') != 'product')
		{
			RedshopbHelperFilter::addFilterFieldsetQuery($query, $this->getState(), 'filter.', 'p.');
		}

		if (!is_null($query))
		{
			$query2 = clone $query;
			$query2->clear('select')->select('p.id');
			$ids        = $db->setQuery($query2)->loadColumn();
			$attributes = array();
			$collection = $this->getState('filter.product_collection', 0);

			if (!empty($ids))
			{
				$dropDownTypes = $this->getDropDownTypes($ids);

				foreach ($dropDownTypes as $productId => $productDropDowns)
				{
					foreach ($productDropDowns as $dropDown)
					{
						if (!is_null($dropDown->string_value))
						{
							if (!isset($attributes['string']))
							{
								$attributes['string']   = array();
								$attributes['string'][] = $dropDown->string_value;
							}
							elseif (!in_array($dropDown->string_value, $attributes['string']))
							{
								$attributes['string'][] = $dropDown->string_value;
							}
						}

						if (!is_null($dropDown->float_value))
						{
							if (!isset($attributes['float']))
							{
								$attributes['float']   = array();
								$attributes['float'][] = $dropDown->float_value;
							}
							elseif (!in_array($dropDown->float_value, $attributes['float']))
							{
								$attributes['float'][] = $dropDown->float_value;
							}
						}

						if (!is_null($dropDown->int_value))
						{
							if (!isset($attributes['int']))
							{
								$attributes['int']   = array();
								$attributes['int'][] = $dropDown->int_value;
							}
							elseif (!in_array($dropDown->int_value, $attributes['int']))
							{
								$attributes['int'][] = $dropDown->int_value;
							}
						}
					}
				}
			}

			if (is_array($collection) && count($collection) == 1)
			{
				$collectionId = reset($collection);

				if (!empty($collectionId))
				{
					$app->setUserState('shop.dropdowns_collection_' . $collection, $attributes);
				}
				else
				{
					$app->setUserState('shop.dropdowns', $attributes);
				}
			}
			else
			{
				$app->setUserState('shop.dropdowns', $attributes);
			}

			$app->setUserState('shop.dropdowns_current_state', $this->getState('filter.attribute_flat_display'));
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
	 * Increment the hit counter for the product.
	 *
	 * @param   integer  $pk  Optional primary key of the product to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$input    = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('id');

			$table = RedshopbTable::getInstance('Product', 'RedshopbTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
