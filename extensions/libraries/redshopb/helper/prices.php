<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Utilities\ArrayHelper;

/**
 * Redshop Prices Helper
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperPrices
{
	/**
	 * Discount percentage string
	 * @var  string
	 */
	const DISCOUNT_PERCENTAGE = 'percent';

	/**
	 * Discount total string
	 * @var  string
	 */
	const DISCOUNT_TOTAL = 'total';

	/**
	 * Array of product $discounts.
	 *
	 * @var array
	 */
	public static $discounts = array();

	/**
	 * @var array
	 */
	public static $isRightCustomer = array();

	/**
	 * Returns price for a single product item.
	 *
	 * @param   int     $itemId           Product item id.
	 * @param   int     $customerId       Customer id.
	 * @param   string  $customerType     Customer type.
	 * @param   mixed   $currency         Currency id or alpha3.
	 * @param   array   $collections      Collections for getting from points value.
	 * @param   string  $date             Date to get price for.
	 * @param   int     $endCustomer      End customer id which price and discount group will be used.
	 * @param   int     $quantity         Quantity of product item for calculate price base on volume
	 * @param   bool    $forceCollection  Use collection prices if has collection
	 * @param   bool    $allowPlugins     Allow plugins that modify the price to be returned by the products (only when no collections are used)
	 *
	 * @return  stdClass|false                     Object item with set prices.
	 * @throws  Exception
	 */
	public static function getProductItemPrice(
		$itemId,
		$customerId = 0,
		$customerType = '',
		$currency = 'DKK',
		$collections = array(),
		$date = '',
		$endCustomer = 0,
		$quantity = 0,
		$forceCollection = false,
		$allowPlugins = false
	)
	{
		if ($customerId == 0)
		{
			$customerId = Factory::getApplication()->getUserStateFromRequest('list.customer_id', 'customer_id', 0, 'int');
		}

		if ($customerType == '')
		{
			$customerType = Factory::getApplication()->getUserStateFromRequest('list.customer_type', 'customer_type', '', 'string');
		}

		$prices = self::getProductItemsPrice(
			array($itemId), array(), $customerId, $customerType, $currency, $collections, $date, $endCustomer, $quantity, $forceCollection
		);

		// Processes extra plugins that might modify the default price
		if ($allowPlugins)
		{
			$newPrices = array();

			RFactory::getDispatcher()->trigger(
				'onRedshopbPriceItemLoad',
				array(
					RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType),
					array($itemId => $quantity),
					&$newPrices, &$prices
				)
			);

			if (isset($newPrices[$itemId]) && isset($newPrices[$itemId]->price))
			{
				if (!isset($prices[$itemId]))
				{
					$prices[$itemId] = new stdClass;
				}

				$prices[$itemId]->price                  = $newPrices[$itemId]->price;
				$prices[$itemId]->price_without_discount = $newPrices[$itemId]->price;

				$prices = array($prices);
			}
		}

		$product = reset($prices);

		if (!$product)
		{
			return false;
		}

		return is_array($product) ? reset($product) : $product;
	}

	/**
	 * Get price for product items.
	 *
	 * @param   array   $itemIds            Array ProductItem ids.
	 * @param   array   $productIds         Product Ids to include all their product items from (it's ignored if $itemIds is set)
	 * @param   int     $customerId         Customer id.
	 * @param   string  $customerType       Customer type.
	 * @param   mixed   $currency           Currency id or alpha3.
	 * @param   array   $collections        Collections for getting from points value.
	 * @param   string  $date               Date to get price for.
	 * @param   int     $endCustomer        End customer id which price and discount group will be used.
	 * @param   int     $quantity           Quantity of product items for calculate price base on volume
	 * @param   bool    $forceCollection    Use collection prices if has collection
	 * @param   bool    $itemsWithQuantity  Product items id get with separate quantity
	 * @param   bool    $onlyActive         True for return only active price of products. False for all
	 *
	 * @return array Array object items with set prices.
	 */
	public static function getProductItemsPrice(
		$itemIds, $productIds = array(), $customerId = 0, $customerType = '', $currency = 0, $collections = array(), $date = '', $endCustomer = 0,
		$quantity = 0, $forceCollection = false, $itemsWithQuantity = false, $onlyActive = true
	)
	{
		if (!$endCustomer)
		{
			$endCustomer = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		}

		$isShop = self::displayPrices();

		if (!$isShop)
		{
			return array();
		}

		$db = Factory::getDbo();

		if ($itemsWithQuantity)
		{
			$itemsQuantity  = $itemIds;
			$selectQuantity = 'CASE %s';

			foreach ($itemsQuantity as $id => $itemQuantity)
			{
				$selectQuantity .= ' WHEN ' . (int) $id . ' THEN ' . $db->q($itemQuantity);
			}

			$selectQuantity .= ' ELSE 1 END AS quantity';
			$itemIds         = array_keys($itemIds);
		}
		else
		{
			$itemsQuantity  = $quantity ? $quantity : 1;
			$selectQuantity = $db->q($itemsQuantity) . ' AS quantity';
		}

		$itemIds    = ArrayHelper::toInteger($itemIds);
		$productIds = ArrayHelper::toInteger($productIds);
		$query      = $db->getQuery(true);

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
			$companyId = $company->id;
		}
		else
		{
			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		}

		// This is the default, so we make sure it really is the default
		if (is_null($currency) || $currency == 'DKK')
		{
			$currency = RedshopbEntityCompany::getInstance($companyId)->getCustomerCurrency();
		}

		// Always use a numeric currency id
		if (!is_numeric($currency))
		{
			$currency = RedshopbEntityCurrency::loadByAlpha3($currency)->getItem()->id;
		}

		if ($forceCollection)
		{
			if (!count($collections) || (count($collections) == 1 && !$collections[0]))
			{
				$collectionUse = false;
			}
			else
			{
				$collectionUse = true;
			}
		}
		else
		{
			$collectionUse = RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($companyId));
		}

		Factory::getApplication()
			->triggerEvent('onRedshopbJustifyCollectionUse', [__METHOD__, &$collectionUse]);

		if ($collectionUse)
		{
			$defaultCurrencyObj = RedshopbApp::getDefaultCurrency();
			$defaultCurrency    = $defaultCurrencyObj->get('alpha3');
			$defaultCurrencyId  = (int) $defaultCurrencyObj->get('id');
			$now                = Date::getInstance()->toSql();

			/**
			 * Customer is an endcustomer type.
			 * Prices are stored in collections.
			 */
			if (empty($collections))
			{
				switch ($customerType)
				{
					case 'employee':
						$departments = explode(',', RedshopbHelperACL::listAvailableDepartments(RedshopbHelperUser::getJoomlaId($customerId)));
						break;
					case 'department':
						$departments = array($customerId);
						$departments = array_merge($departments, RedshopbHelperDepartment::getChildDepartments($customerId));
						break;
					case 'company':
						$departments = RedshopbEntityCompany::getInstance($companyId)->getDescendantDepartments()->ids();
						break;
					default:
						$departments = array();
				}

				// Relating with collection
				$collections = RedshopbHelperCollection::getCollectionsFromDepartments($departments);
			}

			$company     = RedshopbHelperCompany::getCompanyById($companyId);
			$collections = ArrayHelper::toInteger($collections);
			$collections = implode(',', $collections);

			// Join to collection to get points
			$priceQuery = $db->getQuery(true);
			$priceQuery->select(
				array (
					$db->qn('wpix.product_item_id', 'piid'),
					$db->qn('wpix.collection_id', 'wid'),
					'MIN(' . $db->qn('wpix.price') . ') AS ' . $db->qn('price'),
					'CONCAT_WS(' . $db->q('_') . ', wpix.product_item_id, wpix.collection_id) AS common_id'
				)
			)
				->from($db->qn('#__redshopb_collection_product_item_xref', 'wpix'))
				->where('wpix.state = 1')
				->where('wpix.price > 0')
				->group($db->qn('common_id'));

			if (empty($itemIds) && !empty($productIds))
			{
				$priceQuery->innerJoin(
					$db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pi.id') . ' = ' . $db->qn('wpix.product_item_id')
				)
					->where('pi.product_id IN (' . implode(',', $productIds) . ')')
					->where('pi.state = 1');
			}
			else
			{
				$priceQuery->where('wpix.product_item_id IN (' . implode(',', $itemIds) . ')');
			}

			if (!empty($collections))
			{
				$priceQuery->where('wpix.collection_id IN (' . $collections . ')');
			}

			$query->innerJoin('(' . $priceQuery . ') AS ' . $db->qn('wpi') . ' ON ' . $db->qn('wpi.piid') . ' = ' . $db->qn('pi.id'))
				->select(
					array (
						$db->qn('pi.id', 'id'),
						$db->qn('pi.product_id', 'product_id'),
						$db->qn('wpi.price', 'price'),
						$db->qn('wpi.wid', 'wid'),
						'COALESCE(w.currency_id, ' . $db->q($defaultCurrencyId) . ') AS currency_id',
						'COALESCE(cur2.alpha3, ' . $db->q($defaultCurrency) . ') AS currency',
						sprintf($selectQuantity, 'pi.id'),
						$db->q('collection') . ' AS stype',
						'1 AS volume_order',
						'NULL AS quantity_min',
						'NULL AS quantity_max'
					)
				)
				->from($db->qn('#__redshopb_product_item', 'pi'))
				->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpi.wid'))
				->leftJoin($db->qn('#__redshopb_currency', 'cur2') . ' ON cur2.id = w.currency_id');

			// Get retail price
			if (RedshopbHelperCompany::checkStatusDisplayRetailPrice($customerId, $customerType))
			{
				$subQuery = $db->getQuery(true)
					->select(
						array(
							'pp.retail_price',
							'COALESCE(pp.currency_id, ' . $db->q($defaultCurrencyId) . ') AS currency_id',
							'pp.type_id',
							'pp.type',
							'pp.starting_date',
							'pp.ending_date'
						)
					)
					->from($db->qn('#__redshopb_product_price', 'pp'))
					->where('pp.type = ' . $db->q('product_item'))
					->where('pp.type_id IN (' . implode(',', $itemIds) . ')');

				// Currency
				$subQuery->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pp.currency_id');

				// Selecting currency. If currency is given as a number, getting it by ID
				if (is_numeric($currency))
				{
					if ($currency != 0)
					{
						$subQuery->where('(cur.id = ' . (int) $currency . ' OR pp.currency_id IS NULL)');
					}
					else
					{
						$subQuery->where('(cur.id = ' . (int) $company->currency_id . ' OR pp.currency_id IS NULL)');
					}
				}
				else
				{
					$subQuery->where('(cur.alpha3 = ' . $db->q($currency) . ' OR pp.currency_id IS NULL)');
				}

				if ($date == '')
				{
					$subQuery->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					)
						->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
							. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
						);
				}
				else
				{
					$subQuery->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
						. $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					)
						->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
							. $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
						);
				}

				$query->select(
					array(
						'retail_table.retail_price',
						$db->qn('retail_table.currency_id', 'retail_currency_id')
					)
				)
					->leftJoin('(' . $subQuery . ') AS retail_table ON ((retail_table.type = ' . $db->q('product_item')
						. ' AND retail_table.type_id = pi.id) OR (retail_table.type = ' . $db->q('product')
						. ' AND retail_table.type_id = pi.product_id))'
					);
			}

			$oldTranslate  = $db->translate;
			$db->translate = false;
			$results       = $db->setQuery($query)->loadObjectList('id');
			$db->translate = $oldTranslate;

			if ($results)
			{
				$prices = $results;
			}
			else
			{
				$prices = array();
			}
		}
		else
		{
			/**
			 * Customer is customer type (level 2 & 1).
			 * Prices are stored in product_price table.
			 */
			$query = self::getBaseQuery($productIds, $itemIds, $currency, $date, $quantity);
			$query->select(
				array(
					$db->qn('pp.type_id', 'product_item_id'),
					sprintf($selectQuantity, 'pp.type_id'),
				)
			);

			// Currency
			$query->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pp.currency_id');
			$query->clear('order')->order($db->qn('volume_order') . ' DESC', $db->qn('pp.price') . ' ASC');

			RFactory::getDispatcher()->trigger(
				'onRedshopbChangeProductItemsPriceQuery',
				array(
					$query, $productIds, $itemIds, $customerId, $customerType, $currency, $collections, $date, $endCustomer,
					$quantity, $forceCollection, $itemsWithQuantity
				)
			);

			$oldTranslate  = $db->translate;
			$db->translate = false;
			$results       = $db->setQuery($query)->loadObjectList('id');
			$db->translate = $oldTranslate;
			$prices        = array();

			if ($results)
			{
				if ($onlyActive)
				{
					$prices = self::selectActiveItemPrices($results, $companyId, $endCustomer, $currency, $quantity);
				}
				else
				{
					$prices = $results;
				}
			}
		}

		// Check for Price Plugin options
		RFactory::getDispatcher()->trigger('onAfterRedshopbProductItemsPrice', array($query, &$prices, $itemsQuantity, $itemIds, $productIds,
				$customerId, $customerType, $currency, $collections, $date, $endCustomer, $quantity, $forceCollection, $itemsWithQuantity)
		);

		return $prices;
	}

	/**
	 * Returns price for a single item.
	 *
	 * @param   int     $productId        Product id.
	 * @param   int     $customerId       Customer id.
	 * @param   string  $customerType     Customer type.
	 * @param   mixed   $currency         Currency id or alpha3.
	 * @param   array   $collections      Collections for getting from points value.
	 * @param   string  $date             Date to get price for.
	 * @param   int     $endCustomer      End customer id which price and discount group will be used.
	 * @param   int     $quantity         Quantity of product items for calculate price base on volume
	 * @param   bool    $forceCollection  Use collection prices if has collection
	 * @param   bool    $allowPlugins     Allow plugins that modify the price to be returned by the products (only when no collections are used)
	 *
	 * @return object|false Product object with set prices.
	 */
	public static function getProductPrice(
		$productId,
		$customerId,
		$customerType,
		$currency = 'DKK',
		$collections = array(),
		$date = '',
		$endCustomer = 0,
		$quantity = null,
		$forceCollection = false,
		$allowPlugins = false
	)
	{
		$prices = self::getProductsPrice(
			array($productId), $customerId, $customerType, $currency, $collections, $date,
			$endCustomer, $quantity, $forceCollection
		);

		// Processes extra plugins that might modify the default price
		if ($allowPlugins)
		{
			$newPrices = array();

			RFactory::getDispatcher()->trigger(
				'onRedshopbPriceLoad',
				array(
					RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType),
					array($productId => $quantity),
					&$newPrices, &$prices
				)
			);

			if (isset($newPrices[$productId]) && isset($newPrices[$productId]->price))
			{
				if (!isset($prices[$productId]))
				{
					$prices[$productId] = new stdClass;
				}

				$prices[$productId]->price                  = $newPrices[$productId]->price;
				$prices[$productId]->price_without_discount = $newPrices[$productId]->price;
			}
		}

		return reset($prices);
	}

	/**
	 * Get price from the list of active product prices for the product or its variant.
	 * In case there are more then one active price per product for sent company, we will select
	 * lowest value between prices per customer and customer group. In case where there is no price
	 * defined for this customer, global price will be chosen.
	 * Also, campaign prices have highest priority over all other prices, so if sales type is
	 * campaign, we will return that price.
	 *
	 * @param   array      $products     Products prices.
	 * @param   int        $companyId    Company id.
	 * @param   int        $endCustomer  End customer id which price and discount group will be used.
	 * @param   mixed      $currency     Current currency id or alpha3.
	 * @param   int|array  $quantity     Quantity of product items for calculate price base on volume
	 *
	 * @return array Selected product price. Null if there is no price for given product.
	 *
	 * @since 1.12.69
	 */
	public static function selectActiveItemPrices($products, $companyId, $endCustomer = 0, $currency = 'DKK', $quantity = 0)
	{
		$config              = RedshopbApp::getConfig();
		$useLowestPrice      = $config->getInt('use_lowest_price_first', 0);
		$isShop              = self::displayPrices();
		$discountConditions  = array();
		$allPrices           = array();
		$sorted              = array();
		$availableCurrencies = array();
		$allDiscounts        = array();
		$app                 = Factory::getApplication();

		foreach ($products as $price)
		{
			// Avoid error make redshopb can not get price from products
			if (is_array($price))
			{
				$price = $price[0];
			}

			if (!isset($sorted[$price->product_id][$price->product_item_id]))
			{
				$sorted[$price->product_id][$price->product_item_id] = array();
			}

			if ($endCustomer && !array_key_exists($price->currency_id . '_' . $endCustomer, $discountConditions))
			{
				$discountConditions[$price->currency_id . '_' . $endCustomer] = array();
			}

			if (!array_key_exists($price->currency_id . '_' . $companyId, $discountConditions))
			{
				$discountConditions[$price->currency_id . '_' . $companyId] = array();
			}

			$price->priority                                       = new RedshopbHelperPrice_Priority($price);
			$sorted[$price->product_id][$price->product_item_id][] = $price;
			$key                                                   = $price->product_item_id . '_' . $price->currency_id;

			if (!array_key_exists($key, $availableCurrencies))
			{
				if ($endCustomer && $endCustomer != $companyId)
				{
					$discountConditions[$price->currency_id . '_' . $endCustomer][] = $price->product_item_id;
				}

				$discountConditions[$price->currency_id . '_' . $companyId][] = $price->product_item_id;
			}
			else
			{
				continue;
			}

			$availableCurrencies[$key] = $price->currency_id;
		}

		unset($availableCurrencies);

		if (!empty($discountConditions))
		{
			foreach ($discountConditions as $key => $productItemIds)
			{
				$tmpKey      = explode('_', $key);
				$tmpCurrency = $tmpKey[0];
				$tmpCustomer = $tmpKey[1];

				$discounts = self::getProductItemsDiscount($productItemIds, $tmpCustomer, $tmpCurrency, $quantity);

				if (!is_array($discounts) || empty($discounts))
				{
					continue;
				}

				$allDiscounts = array_merge($allDiscounts, $discounts);
			}
		}

		$showOutlet = $config->getInt('show_outlet', 0);

		foreach ($sorted as $productId => $productItemSorted)
		{
			foreach ($productItemSorted as $productItemId => $prices)
			{
				$priceGroup   = array();
				$outlet       = null;
				$outletBackup = null;
				$checkLowest  = false;

				// Clean up prices base on volume and quantity
				foreach ($prices as $key => $price)
				{
					$newPrice         = 0;
					$newPriceMultiple = 0;
					$lowestPrice      = null;

					if (!isset($priceGroup[$price->stype]))
					{
						$priceGroup[$price->stype] = 0;
					}

					if (is_array($quantity))
					{
						if (array_key_exists($price->product_item_id, $quantity))
						{
							$checkQuantity = $quantity[$price->product_item_id];
						}
						else
						{
							$checkQuantity = 0;
						}
					}
					else
					{
						$checkQuantity = $quantity;
					}

					if ($checkQuantity)
					{
						if ($price->is_multiple)
						{
							if ((!$useLowestPrice && $checkQuantity % $price->quantity_min == 0)
								|| ($useLowestPrice && $lowestPrice < $price->price && $checkQuantity % $price->quantity_min == 0))
							{
								$newPrice    = $price->volume_order;
								$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
								$checkLowest = true;
							}
							else
							{
								unset($prices[$key]);

								continue;
							}
						}
						// Price with volume has min quantity and max quantity
						elseif ($price->quantity_min && $price->quantity_max
							&& ($checkQuantity >= $price->quantity_min && $checkQuantity <= $price->quantity_max))
						{
							$newPrice    = $price->volume_order;
							$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
							$checkLowest = true;
						}
						elseif ($price->quantity_min && !$price->quantity_max && $checkQuantity >= $price->quantity_min)
						{
							$newPrice    = $price->volume_order;
							$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
							$checkLowest = true;
						}
						elseif (!$price->quantity_min && $price->quantity_max && $checkQuantity <= $price->quantity_max)
						{
							$newPrice    = $price->volume_order;
							$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
							$checkLowest = true;
						}
						elseif (!$price->quantity_min && !$price->quantity_max)
						{
							$newPrice    = $price->volume_order;
							$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
							$checkLowest = true;
						}
					}
					// If quantity is null or zero, just return price for minimum quantity
					else
					{
						$newPrice = $price->volume_order;

						if ($price->quantity_min)
						{
							$price->quantity = $price->quantity_min;
						}
					}

					if ((!$useLowestPrice && (!$newPrice || $newPrice < $priceGroup[$price->stype]))
						|| ($checkLowest && $useLowestPrice && $price->price > $lowestPrice))
					{
						unset($prices[$key]);
					}
					else
					{
						$priceGroup[$price->stype] = $newPrice;
					}
				}

				unset($priceGroup);
				$fallBackRetailPrice = null;
				$fallBackPrice       = null;

				foreach ($prices as $price)
				{
					$keyCurr         = $price->currency_id;
					$endCustomerKey  = $price->product_item_id . '_' . $keyCurr . '_' . $endCustomer;
					$isCustomerKey   = (isset($allDiscounts[$endCustomerKey]) ? true : false);
					$noneCurrencyKey = $price->product_item_id . '_none_' . $endCustomer;

					if (array_key_exists($noneCurrencyKey, $allDiscounts)
						&& ($isCustomerKey ? $allDiscounts[$noneCurrencyKey] > $allDiscounts[$endCustomerKey] : true))
					{
						$endCustomerKey = $noneCurrencyKey;
						$isCustomerKey  = true;
					}

					$companyKey      = $price->product_item_id . '_' . $keyCurr . '_' . $companyId;
					$isCompanyKey    = (isset($allDiscounts[$companyKey]) ? true : false);
					$noneCurrencyKey = $price->product_item_id . '_none_' . $companyId;

					if (array_key_exists($noneCurrencyKey, $allDiscounts)
						&& ($isCompanyKey ? $allDiscounts[$noneCurrencyKey] > $allDiscounts[$companyKey] : true))
					{
						$companyKey   = $noneCurrencyKey;
						$isCompanyKey = true;
					}

					$allDebtorKey    = $price->product_item_id . '_' . $keyCurr . '_allDebtor';
					$isAllDebtorKey  = (isset($allDiscounts[$allDebtorKey]) ? true : false);
					$noneCurrencyKey = $price->product_item_id . '_none_allDebtor';

					if (array_key_exists($noneCurrencyKey, $allDiscounts)
						&& ($isAllDebtorKey ? $allDiscounts[$noneCurrencyKey] > $allDiscounts[$allDebtorKey] : true))
					{
						$allDebtorKey   = $noneCurrencyKey;
						$isAllDebtorKey = true;
					}

					$skip = false;

					switch ($price->stype)
					{
						case 'campaign':
							// Check if discounts are allowed
							if ($price->allow_discount == 1 && $endCustomer > 0 && ($isCustomerKey || $isAllDebtorKey))
							{
								if ($isCustomerKey)
								{
									$discount = $allDiscounts[$endCustomerKey];
								}
								else
								{
									$discount = $allDiscounts[$allDebtorKey];
								}

								$price->discount = $discount;
							}
							else
							{
								$price->price = (float) $price->price_without_discount;
							}

							if ($showOutlet == 1)
							{
								if ($outlet == null || $outlet->price > $price->price)
								{
									$outlet = $price->price;
								}
							}

							break;

						case 'customer_price':
						case 'customer_price_group':

							// If this price for customer price group but this customer not in group. Skip this price.
							if (!self::isRightCustomer($price->stype, $price->scode, $endCustomer))
							{
								$skip = true;

								break;
							}

							$discount = null;

							// Check if customer is shopping for some of his end customers and there is end customer price for this product
							if ($endCustomer > 0 && self::isRightCustomer($price->stype, $price->scode, $endCustomer))
							{
								// Check if discounts are allowed
								if ($price->allow_discount == 1 && ($isCustomerKey || $isAllDebtorKey))
								{
									if ($isCustomerKey)
									{
										$discount = $allDiscounts[$endCustomerKey];
									}
									else
									{
										$discount = $allDiscounts[$allDebtorKey];
									}

									$price->discount = $discount;
								}
								else
								{
									$price->price = $price->price_without_discount;
								}

								break;
							}

							// Check if this prices applies to customer
							// If shop display prices is set to list (2), we exclude company or debtor pricing
							if ($isShop != 2
								&& self::isRightCustomer($price->stype, $price->scode, $companyId))
							{
								// Check if discounts are allowed
								if ($price->allow_discount == 1)
								{
									// If discount is set, it is an end customer discount which we need to use
									if (is_null($discount))
									{
										// Check if customer is shopping for some of his end customers
										if ($endCustomer > 0 && ($isCustomerKey || $isAllDebtorKey))
										{
											if ($isCustomerKey)
											{
												$discount = $allDiscounts[$endCustomerKey];
											}
											else
											{
												$discount = $allDiscounts[$allDebtorKey];
											}
										}

										// If discount is set, it is an end customer discount which we need to use
										if ((!isset($discount) || is_null($discount)) && ($isCompanyKey || $isAllDebtorKey))
										{
											if ($isCompanyKey)
											{
												$discount = $allDiscounts[$companyKey];
											}
											else
											{
												$discount = $allDiscounts[$allDebtorKey];
											}
										}
									}

									if ($discount)
									{
										$price->discount = $discount;
									}
									else
									{
										$price->price = $price->price_without_discount;
									}
								}
								else
								{
									$price->price = $price->price_without_discount;
								}
							}

							break;
						case 'all_customers':

							// Setup fallback retail price
							if (isset($price->retail_price)
								&& $price->starting_date == '0000-00-00 00:00:00'
								&& $price->ending_date == '0000-00-00 00:00:00'
								&& $price->quantity_min == null
								&& $price->quantity_max == null
								&& ($fallBackRetailPrice == null || $fallBackRetailPrice > $price->retail_price))
							{
								$fallBackRetailPrice = $price->retail_price;
							}

							if ($fallBackPrice == null || $fallBackPrice > $price->price_without_discount)
							{
								$fallBackPrice = $price->price_without_discount;
							}

							// Check if discounts are allowed
							if ($price->allow_discount == 1)
							{
								$discount = null;

								// Check if customer is shopping for some of his end customers
								if ($endCustomer > 0 && ($isCustomerKey || $isAllDebtorKey))
								{
									if ($isCustomerKey)
									{
										$discount = $allDiscounts[$endCustomerKey];
									}
									else
									{
										$discount = $allDiscounts[$allDebtorKey];
									}
								}

								// If discount is set, it is an end customer discount which we need to use
								if ((!isset($discount) || is_null($discount)) && ($isCompanyKey || $isAllDebtorKey))
								{
									if ($isCompanyKey)
									{
										$discount = $allDiscounts[$companyKey];
									}
									else
									{
										$discount = $allDiscounts[$allDebtorKey];
									}
								}

								if ($discount)
								{
									$price->discount = $discount;
								}
								else
								{
									$price->price = $price->price_without_discount;
								}
							}
							else
							{
								$price->price = $price->price_without_discount;
							}

							break;
					}

					$conditionsResults = $app->triggerEvent('onRedshopbApplyPriceCondition', array(compact(array_keys(get_defined_vars()))));

					if (!empty($conditionsResults))
					{
						foreach ($conditionsResults as $conditionsResult)
						{
							if (is_array($conditionsResult))
							{
								extract($conditionsResult);
							}
						}
					}

					if ($skip === true)
					{
						continue;
					}

					// Do not set campaign price as main, when show outlet is true
					if (!($price->stype == 'campaign' && $showOutlet == 1))
					{
						if ($price->currency_id == $currency)
						{
							if ((empty($allPrices[$productId]) || !array_key_exists($productItemId, $allPrices[$productId]))
								|| ($useLowestPrice ? ($price->price <= $allPrices[$productId][$productItemId]->price) : ($price->priority->getPriority() >= $allPrices[$productId][$productItemId]->priority->getPriority()))
							)
							{
								$allPrices[$productId][$productItemId] = $price;
							}
						}
					}
				}

				if ($showOutlet == 1 && !is_null($outlet))
				{
					if (isset($allPrices[$productId][$productItemId]) && $allPrices[$productId][$productItemId]->price > $outlet->price)
					{
						$oldPrice                                                      = $allPrices[$productId][$productItemId]->price;
						$allPrices[$productId][$productItemId]->discount               = 0;
						$allPrices[$productId][$productItemId]->price_without_discount = $outlet;
						$allPrices[$productId][$productItemId]->price                  = $outlet;
						$allPrices[$productId][$productItemId]->oldPrice               = $oldPrice;
						$allPrices[$productId][$productItemId]->outlet                 = true;
					}

					// If exists just outlet price - set it as main
					elseif (!isset($allPrices[$productId][$productItemId]))
					{
						$allPrices[$productId][$productItemId] = $outlet;
					}
				}

				// Setup fallback retail price, when current retail price not exists
				if ((!$allPrices[$productId][$productItemId]->retail_price || !isset($allPrices[$productId][$productItemId]->retail_price))
					&& $fallBackRetailPrice)
				{
					$allPrices[$productId][$productItemId]->retail_price = $fallBackRetailPrice;
				}

				$allPrices[$productId][$productItemId]->fallback_price = $fallBackPrice;
			}
		}

		foreach ($allPrices as &$allPrice)
		{
			// Calculate price discounts
			self::selectHighestDiscount($allPrice);
		}

		// Binding products tax rates
		$taxes         = RedshopbHelperTax::getProductsTaxRates(array_keys($allPrices));
		$globalTaxRate = 0;

		if (array_key_exists(0, $taxes) && !empty($taxes[0]))
		{
			foreach ($taxes[0] as $taxRateData)
			{
				$globalTaxRate += $taxRateData->tax_rate;
			}
		}

		foreach ($allPrices as $productId => $allItemPrices)
		{
			foreach ($allItemPrices as $productItemId => $price)
			{
				$price->tax_rate = $globalTaxRate;

				if (array_key_exists($productId, $taxes) && !empty($taxes[$productId]))
				{
					if (!is_array($taxes[$productId]))
					{
						$taxes[$productId] = array($taxes[$productId]);
					}

					foreach ($taxes[$productId] as $index => $taxRateData)
					{
						$price->tax_rate += $taxRateData->tax_rate;
					}
				}

				$price->price_with_tax          = $price->price;
				$price->fallback_price_with_tax = $price->fallback_price;

				if ($price->tax_rate)
				{
					$price->price_with_tax += $price->price * $price->tax_rate;

					if ($price->fallback_price)
					{
						$price->fallback_price_with_tax += $price->fallback_price * $price->tax_rate;
					}
				}
			}
		}

		return $allPrices;
	}

	/**
	 * Get price for products.
	 *
	 * @param   array   $productIds         Array product ids.
	 * @param   int     $customerId         Customer id.
	 * @param   string  $customerType       Customer type.
	 * @param   mixed   $currency           Currency id or alpha3.
	 * @param   array   $collections        Collections for getting from points value.
	 * @param   string  $date               Date to get price for.
	 * @param   int     $endCustomer        End customer id which price and discount group will be used.
	 * @param   int     $quantity           Quantity of product items for calculate price base on volume
	 * @param   bool    $forceCollection    Use collection prices if has collection
	 * @param   bool    $itemsWithQuantity  Product items id get with separate quantity
	 * @param   bool    $onlyActive         True for return only active price of products. False for all
	 *
	 * @return array Array of product objects with set prices.
	 */
	public static function getProductsPrice(
		$productIds, $customerId = 0, $customerType = '', $currency = null, $collections = array(), $date = '', $endCustomer = 0,
		$quantity = null, $forceCollection = false, $itemsWithQuantity = false, $onlyActive = true
	)
	{
		if (!$endCustomer)
		{
			$endCustomer = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		}

		$isShop = self::displayPrices();

		if (!$isShop)
		{
			return array();
		}

		$db = Factory::getDbo();

		if ($itemsWithQuantity)
		{
			$quantity       = $productIds;
			$selectQuantity = 'CASE %s';

			foreach ($productIds as $id => $itemQuantity)
			{
				$selectQuantity .= ' WHEN ' . (int) $id . ' THEN ' . $db->q($itemQuantity);
			}

			$selectQuantity .= ' ELSE 1 END AS quantity';
			$productIds      = array_keys($productIds);
		}
		else
		{
			$quantity       = $quantity ? $quantity : 1;
			$selectQuantity = $db->q($quantity) . ' AS quantity';
		}

		$productIds = ArrayHelper::toInteger($productIds);

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
			$companyId = $company->id;
		}
		else
		{
			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		}

		// This is the default, so we make sure it really is the default
		if (is_null($currency) || $currency == 'DKK')
		{
			$currency = RedshopbEntityCompany::getInstance($companyId)->getCustomerCurrency();
		}

		// Always use a numeric currency id
		if (!is_numeric($currency))
		{
			$currency = RedshopbEntityCurrency::loadByAlpha3($currency)->getItem()->id;
		}

		if (count($collections) > 1 || (count($collections) == 1 && $collections[0]))
		{
			$collectionUse = true;
		}
		else
		{
			$collectionUse = false;
		}

		Factory::getApplication()
			->triggerEvent('onRedshopbJustifyCollectionUse', [__METHOD__, &$collectionUse]);

		if ($collectionUse)
		{
			$now                = Date::getInstance()->toSql();
			$defaultCurrencyObj = RedshopbApp::getDefaultCurrency();
			$defaultCurrency    = $defaultCurrencyObj->get('alpha3');
			$defaultCurrencyId  = (int) $defaultCurrencyObj->get('id');

			if (empty($collections))
			{
				switch ($customerType)
				{
					case 'employee':
						$departments = explode(',', RedshopbHelperACL::listAvailableDepartments(RedshopbHelperUser::getJoomlaId($customerId)));
						break;
					case 'department':
						$departments = array($customerId);
						$departments = array_merge($departments, RedshopbHelperDepartment::getChildDepartments($customerId));
						break;
					case 'company':
						$departments = RedshopbEntityCompany::getInstance($companyId)->getDescendantDepartments()->ids();
						break;
					default:
						$departments = array();
				}

				// Relating with collection
				$collections = RedshopbHelperCollection::getCollectionsFromDepartments($departments);
			}

			$company     = RedshopbHelperCompany::getCompanyById($companyId);
			$collections = ArrayHelper::toInteger($collections);
			$collections = implode(',', $collections);
			$query       = $db->getQuery(true)
				->select($db->qn('p.id'))
				->from($db->qn('#__redshopb_product', 'p'))
				->where($db->qn('p.id') . ' IN (' . implode(',', $productIds) . ')');

			// Join to collection to get points
			$priceQuery = $db->getQuery(true)
				->select(
					array (
						$db->qn('wpx.product_id', 'piid'),
						$db->qn('wpx.collection_id', 'wid'),
						'MIN(' . $db->qn('wpx.price') . ') AS ' . $db->qn('price'),
						'CONCAT_WS(' . $db->q('_') . ', wpx.product_id, wpx.collection_id) AS common_id'
					)
				)
				->from($db->qn('#__redshopb_collection_product_xref', 'wpx'))
				->where('wpx.product_id IN (' . implode(',', $productIds) . ')')
				->where('wpx.state = 1')
				->where('wpx.price > 0')
				->group($db->qn('common_id'));

			if (!empty($collections))
			{
				$priceQuery->where('wpx.collection_id IN (' . $collections . ')');
			}

			$query->innerJoin('(' . $priceQuery . ') AS ' . $db->qn('wpi') . ' ON ' . $db->qn('wpi.piid') . ' = ' . $db->qn('p.id'))
				->select(
					array (
						$db->qn('wpi.price', 'price'),
						$db->qn('wpi.price', 'price_without_discount'),
						$db->qn('wpi.wid', 'wid'),
						'COALESCE(w.currency_id, ' . $db->q($defaultCurrencyId) . ') AS currency_id',
						'COALESCE(cur2.alpha3, ' . $db->q($defaultCurrency) . ') AS currency',
						sprintf($selectQuantity, 'p.id')
					)
				)
				->innerJoin($db->qn('#__redshopb_collection', 'w') . ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpi.wid'))
				->leftJoin($db->qn('#__redshopb_currency', 'cur2') . ' ON cur2.id = w.currency_id')
				->leftJoin($db->qn('#__redshopb_product_price', 'pp') . ' ON ' . $db->qn('pp.product_id') . ' = ' . $db->qn('p.id'));

			// Get retail price
			if (RedshopbHelperCompany::checkStatusDisplayRetailPrice($customerId, $customerType))
			{
				$subQuery = $db->getQuery(true)
					->select(
						array(
							'pp.retail_price',
							'COALESCE(pp.currency_id, ' . $db->q($defaultCurrencyId) . ') AS currency_id',
							'pp.type_id',
							'pp.type',
							'pp.starting_date',
							'pp.ending_date'
						)
					)
					->from($db->qn('#__redshopb_product_price', 'pp'))
					->where('pp.type = ' . $db->q('product'))
					->where('pp.type_id IN (' . implode(',', $productIds) . ')');

				// Currency
				$subQuery->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pp.currency_id');

				// Selecting currency. If currency is given as a number, getting it by ID
				if (is_numeric($currency))
				{
					if ($currency != 0)
					{
						$subQuery->where('(cur.id = ' . (int) $currency . ' OR pp.currency_id IS NULL)');
					}
					else
					{
						$subQuery->where('(cur.id = ' . (int) $company->currency_id . ' OR pp.currency_id IS NULL)');
					}
				}
				else
				{
					$subQuery->where('(cur.alpha3 = ' . $db->q($currency) . ' OR pp.currency_id IS NULL)');
				}

				if ($date == '')
				{
					$subQuery->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					)
						->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
							. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
						);
				}
				else
				{
					$subQuery->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
						. $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					)
						->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
							. $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
						);
				}

				$query->select(
					array(
						'retail_table.retail_price',
						'COALESCE(retail_table.currency_id, ' . $db->q($defaultCurrencyId) . ') AS retail_currency_id'
					)
				)
					->leftJoin('(' . $subQuery . ') AS retail_table ON retail_table.type_id = p.id');
			}

			$oldTranslate  = $db->translate;
			$db->translate = false;
			$results       = $db->setQuery($query)->loadObjectList('id');
			$db->translate = $oldTranslate;

			if ($results)
			{
				$prices = $results;
			}
			else
			{
				$prices = array();
			}
		}
		else
		{
			// Get all possible price per items
			$query = self::getBaseQuery($productIds, array(), $currency, $date, $quantity);
			$query->select(
				array(
						$db->qn('pp.type_id', 'product_id'),
						sprintf($selectQuantity, 'pp.type_id'),
					)
			);

			// Currency
			$query->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pp.currency_id');
			$query->clear('order')->order($db->qn('volume_order') . ' DESC', $db->qn('pp.price') . ' ASC');

			RFactory::getDispatcher()->trigger(
				'onRedshopbChangeProductsPriceQuery',
				array(
					$query, $productIds, $customerId, $customerType, $currency, $collections, $date, $endCustomer,
					$quantity, $forceCollection, $itemsWithQuantity
				)
			);

			$oldTranslate  = $db->translate;
			$db->translate = false;
			$results       = $db->setQuery($query)->loadObjectList();
			$db->translate = $oldTranslate;
			$prices        = array();

			if ($results)
			{
				$prices = ($onlyActive) ? self::selectActivePrices($results, $companyId, $endCustomer, $currency, $quantity) : $results;
			}
		}

		// Check for Price Plugin options
		RFactory::getDispatcher()->trigger('onAfterRedshopbProductsPrice', array($query, &$prices, $productIds, $customerId, $customerType,
			$currency, $collections, $date, $endCustomer, $quantity, $forceCollection, $itemsWithQuantity)
		);

		return $prices;
	}

	/**
	 * Get price from the list of active product prices for the product or its variant.
	 * In case there are more then one active price per product for sent company, we will select
	 * lowest value between prices per customer and customer group. In case where there is no price
	 * defined for this customer, global price will be chosen.
	 * Also, campaign prices have highest priority over all other prices, so if sales type is
	 * campaign, we will return that price.
	 *
	 * @param   array  $prices       Product prices.
	 * @param   int    $companyId    Company id.
	 * @param   int    $endCustomer  End customer id which price and discount group will be used.
	 *
	 * @return array Selected product price. Null if there is no price for given product.
	 */
	public static function selectActivePrice($prices, $companyId, $endCustomer = 0)
	{
		$products   = self::selectActivePrices(array($prices), $companyId, $endCustomer);
		$firstPrice = reset($prices);

		return $products[$firstPrice->product_id];
	}

	/**
	 * Get price from the list of active product prices for the product or its variant.
	 * In case there are more then one active price per product for sent company, we will select
	 * lowest value between prices per customer and customer group. In case where there is no price
	 * defined for this customer, global price will be chosen.
	 * Also, campaign prices have highest priority over all other prices, so if sales type is
	 * campaign, we will return that price.
	 *
	 * @param   array      $products     Products prices.
	 * @param   int        $companyId    Company id.
	 * @param   int        $endCustomer  End customer id which price and discount group will be used.
	 * @param   mixed      $currency     Current currency id or alpha3.
	 * @param   int|array  $quantity     Quantity of product items for calculate price base on volume
	 *
	 * @return array Selected product price. Null if there is no price for given product.
	 */
	public static function selectActivePrices($products, $companyId, $endCustomer = 0, $currency = 'DKK', $quantity = 0)
	{
		$config              = RedshopbApp::getConfig();
		$useLowestPrice      = $config->getInt('use_lowest_price_first', 0);
		$isShop              = self::displayPrices();
		$discountConditions  = array();
		$allPrices           = array();
		$sorted              = array();
		$availableCurrencies = array();
		$allDiscounts        = array();
		$app                 = Factory::getApplication();

		foreach ($products as $price)
		{
			// Avoid error make redshopb can not get price from products
			if (is_array($price))
			{
				$price = $price[0];
			}

			if (!isset($sorted[$price->product_id]))
			{
				$sorted[$price->product_id] = array();
			}

			if ($endCustomer && !isset($discountConditions[$price->currency_id . '_' . $endCustomer]))
			{
				$discountConditions[$price->currency_id . '_' . $endCustomer] = array();
			}

			if (!isset($discountConditions[$price->currency_id . '_' . $companyId]))
			{
				$discountConditions[$price->currency_id . '_' . $companyId] = array();
			}

			$price->priority              = new RedshopbHelperPrice_Priority($price);
			$sorted[$price->product_id][] = $price;
			$key                          = $price->product_id . '_' . $price->currency_id;

			if (!array_key_exists($key, $availableCurrencies))
			{
				if ($endCustomer)
				{
					$discountConditions[$price->currency_id . '_' . $endCustomer][] = $price->product_id;
				}

				$discountConditions[$price->currency_id . '_' . $companyId][] = $price->product_id;
			}
			else
			{
				continue;
			}

			$availableCurrencies[$key] = $price->currency_id;
		}

		unset($availableCurrencies);

		if (!empty($discountConditions))
		{
			foreach ($discountConditions as $key => $productIds)
			{
				$tmpKey      = explode('_', $key);
				$tmpCurrency = $tmpKey[0];
				$tmpCustomer = $tmpKey[1];

				$discounts = self::getProductsDiscount($productIds, $tmpCustomer, $tmpCurrency, $quantity);

				if (!is_array($discounts) || empty($discounts))
				{
					continue;
				}

				$allDiscounts = array_merge($allDiscounts, $discounts);
			}
		}

		$showOutlet = $config->getInt('show_outlet', 0);

		foreach ($sorted as $productId => $prices)
		{
			$priceGroup   = array();
			$outlet       = null;
			$outletBackup = null;
			$checkLowest  = false;

			// Clean up prices base on volume and quantity
			foreach ($prices as $key => $price)
			{
				$newPrice         = 0;
				$newPriceMultiple = 0;
				$lowestPrice      = null;

				if (!isset($priceGroup[$price->stype]))
				{
					$priceGroup[$price->stype] = 0;
				}

				if (is_array($quantity))
				{
					if (array_key_exists($price->product_id, $quantity))
					{
						$checkQuantity = $quantity[$price->product_id];
					}
					else
					{
						$checkQuantity = 0;
					}
				}
				else
				{
					$checkQuantity = $quantity;
				}

				if ($checkQuantity)
				{
					if ($price->is_multiple)
					{
						if ((!$useLowestPrice && $checkQuantity % $price->quantity_min == 0)
							|| ($useLowestPrice && $lowestPrice < $price->price && $checkQuantity % $price->quantity_min == 0))
						{
							$newPrice    = $price->volume_order;
							$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
							$checkLowest = true;
						}
						else
						{
							unset($prices[$key]);

							continue;
						}
					}
					// Price with volume has min quantity and max quantity
					elseif ($price->quantity_min && $price->quantity_max
						&& ($checkQuantity >= $price->quantity_min && $checkQuantity <= $price->quantity_max))
					{
						$newPrice    = $price->volume_order;
						$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
						$checkLowest = true;
					}
					elseif ($price->quantity_min && !$price->quantity_max && $checkQuantity >= $price->quantity_min)
					{
						$newPrice    = $price->volume_order;
						$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
						$checkLowest = true;
					}
					elseif (!$price->quantity_min && $price->quantity_max && $checkQuantity <= $price->quantity_max)
					{
						$newPrice    = $price->volume_order;
						$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
						$checkLowest = true;
					}
					elseif (!$price->quantity_min && !$price->quantity_max)
					{
						$newPrice    = $price->volume_order;
						$lowestPrice = is_null($lowestPrice) || $price->price < $lowestPrice ? $price->price : $lowestPrice;
						$checkLowest = true;
					}
				}
				// If quantity is null or zero, just return price for minimum quantity
				else
				{
					$newPrice = $price->volume_order;

					if ($price->quantity_min)
					{
						$price->quantity = $price->quantity_min;
					}
				}

				if ((!$useLowestPrice && (!$newPrice || $newPrice < $priceGroup[$price->stype]))
					|| ($checkLowest && $useLowestPrice && $price->price > $lowestPrice))
				{
					unset($prices[$key]);
				}
				else
				{
					$priceGroup[$price->stype] = $newPrice;
				}
			}

			unset($priceGroup);
			$fallBackRetailPrice = null;
			$fallBackPrice       = null;

			foreach ($prices as $price)
			{
				$keyCurr         = $price->currency_id;
				$endCustomerKey  = $price->product_id . '_' . $keyCurr . '_' . $endCustomer;
				$isCustomerKey   = (isset($allDiscounts[$endCustomerKey]) ? true : false);
				$noneCurrencyKey = $price->product_id . '_none_' . $endCustomer;

				if (array_key_exists($noneCurrencyKey, $allDiscounts)
					&& ($isCustomerKey ? $allDiscounts[$noneCurrencyKey] > $allDiscounts[$endCustomerKey] : true))
				{
					$endCustomerKey = $noneCurrencyKey;
					$isCustomerKey  = true;
				}

				$companyKey      = $price->product_id . '_' . $keyCurr . '_' . $companyId;
				$isCompanyKey    = (isset($allDiscounts[$companyKey]) ? true : false);
				$noneCurrencyKey = $price->product_id . '_none_' . $companyId;

				if (array_key_exists($noneCurrencyKey, $allDiscounts)
					&& ($isCompanyKey ? $allDiscounts[$noneCurrencyKey] > $allDiscounts[$companyKey] : true))
				{
					$companyKey   = $noneCurrencyKey;
					$isCompanyKey = true;
				}

				$allDebtorKey    = $price->product_id . '_' . $keyCurr . '_allDebtor';
				$isAllDebtorKey  = (isset($allDiscounts[$allDebtorKey]) ? true : false);
				$noneCurrencyKey = $price->product_id . '_none_allDebtor';

				if (array_key_exists($noneCurrencyKey, $allDiscounts)
					&& ($isAllDebtorKey ? $allDiscounts[$noneCurrencyKey] > $allDiscounts[$allDebtorKey] : true))
				{
					$allDebtorKey   = $noneCurrencyKey;
					$isAllDebtorKey = true;
				}

				$skip = false;

				switch ($price->stype)
				{
					case 'campaign':
						// Check if discounts are allowed
						if ($price->allow_discount == 1 && $endCustomer > 0 && ($isCustomerKey || $isAllDebtorKey))
						{
							if ($isCustomerKey)
							{
								$discount = $allDiscounts[$endCustomerKey];
							}
							else
							{
								$discount = $allDiscounts[$allDebtorKey];
							}

							$price->discount = $discount;
						}
						else
						{
							$price->price = (float) $price->price_without_discount;
						}

						if ($showOutlet == 1)
						{
							if ($outlet == null || $outlet->price > $price->price)
							{
								$outlet = $price->price;
							}
						}

						break;

					case 'customer_price':
					case 'customer_price_group':

						// If this price for customer price group but this customer not in group. Skip this price.
						if (!self::isRightCustomer($price->stype, $price->scode, $endCustomer))
						{
							$skip = true;

							break;
						}

						$discount = null;

						// Check if customer is shopping for some of his end customers and there is end customer price for this product
						if ($endCustomer > 0 && self::isRightCustomer($price->stype, $price->scode, $endCustomer))
						{
							// Check if discounts are allowed
							if ($price->allow_discount == 1 && ($isCustomerKey || $isAllDebtorKey))
							{
								if ($isCustomerKey)
								{
									$discount = $allDiscounts[$endCustomerKey];
								}
								else
								{
									$discount = $allDiscounts[$allDebtorKey];
								}

								$price->discount = $discount;
							}
							else
							{
								$price->price = $price->price_without_discount;
							}

							break;
						}

						// Check if this prices applies to customer
						// If shop display prices is set to list (2), we exclude company or debtor pricing
						if ($isShop != 2
							&& self::isRightCustomer($price->stype, $price->scode, $companyId))
						{
							// Check if discounts are allowed
							if ($price->allow_discount == 1)
							{
								// If discount is set, it is an end customer discount which we need to use
								if (is_null($discount))
								{
									// Check if customer is shopping for some of his end customers
									if ($endCustomer > 0 && ($isCustomerKey || $isAllDebtorKey))
									{
										if ($isCustomerKey)
										{
											$discount = $allDiscounts[$endCustomerKey];
										}
										else
										{
											$discount = $allDiscounts[$allDebtorKey];
										}
									}

									// If discount is set, it is an end customer discount which we need to use
									if ((!isset($discount) || is_null($discount)) && ($isCompanyKey || $isAllDebtorKey))
									{
										if ($isCompanyKey)
										{
											$discount = $allDiscounts[$companyKey];
										}
										else
										{
											$discount = $allDiscounts[$allDebtorKey];
										}
									}
								}

								if ($discount)
								{
									$price->discount = $discount;
								}
								else
								{
									$price->price = $price->price_without_discount;
								}
							}
							else
							{
								$price->price = $price->price_without_discount;
							}
						}

						break;
					case 'all_customers':

						// Setup fallback retail price
						if (isset($price->retail_price)
							&& $price->starting_date == '0000-00-00 00:00:00'
							&& $price->ending_date == '0000-00-00 00:00:00'
							&& $price->quantity_min == null
							&& $price->quantity_max == null
							&& ($fallBackRetailPrice == null || $fallBackRetailPrice > $price->retail_price))
						{
							$fallBackRetailPrice = $price->retail_price;
						}

						if ($fallBackPrice == null || $fallBackPrice > $price->price_without_discount)
						{
							$fallBackPrice = $price->price_without_discount;
						}

						// Check if discounts are allowed
						if ($price->allow_discount == 1)
						{
							$discount = null;

							// Check if customer is shopping for some of his end customers
							if ($endCustomer > 0 && ($isCustomerKey || $isAllDebtorKey))
							{
								if ($isCustomerKey)
								{
									$discount = $allDiscounts[$endCustomerKey];
								}
								else
								{
									$discount = $allDiscounts[$allDebtorKey];
								}
							}

							// If discount is set, it is an end customer discount which we need to use
							if ((!isset($discount) || is_null($discount)) && ($isCompanyKey || $isAllDebtorKey))
							{
								if ($isCompanyKey)
								{
									$discount = $allDiscounts[$companyKey];
								}
								else
								{
									$discount = $allDiscounts[$allDebtorKey];
								}
							}

							if ($discount)
							{
								$price->discount = $discount;
							}
							else
							{
								$price->price = $price->price_without_discount;
							}
						}
						else
						{
							$price->price = $price->price_without_discount;
						}

						break;
				}

				$conditionsResults = $app->triggerEvent('onRedshopbApplyPriceCondition', array(compact(array_keys(get_defined_vars()))));

				if (!empty($conditionsResults))
				{
					foreach ($conditionsResults as $conditionsResult)
					{
						if (is_array($conditionsResult))
						{
							extract($conditionsResult);
						}
					}
				}

				if ($skip === true)
				{
					continue;
				}

				// Do not set campaign price as main, when show outlet is true
				if (!($price->stype == 'campaign' && $showOutlet == 1))
				{
					if (!array_key_exists($productId, $allPrices)
						|| ($useLowestPrice ? ($price->price <= $allPrices[$productId]->price) : ($price->priority->getPriority() >= $allPrices[$productId]->priority->getPriority()))
					)
					{
						$allPrices[$productId] = $price;
					}
				}
			}

			if ($showOutlet == 1 && !is_null($outlet))
			{
				if (isset($allPrices[$productId]) && $allPrices[$productId]->price > $outlet->price)
				{
					$oldPrice                                      = $allPrices[$productId]->price;
					$allPrices[$productId]->discount               = 0;
					$allPrices[$productId]->price_without_discount = $outlet;
					$allPrices[$productId]->price                  = $outlet;
					$allPrices[$productId]->oldPrice               = $oldPrice;
					$allPrices[$productId]->outlet                 = true;
				}

				// If exists just outlet price - set it as main
				elseif (!isset($allPrices[$productId]))
				{
					$allPrices[$productId] = $outlet;
				}
			}

			// Setup fallback retail price, when current retail price not exists
			if ((!isset($allPrices[$productId]->retail_price) || !$allPrices[$productId]->retail_price)
				&& $fallBackRetailPrice)
			{
				$allPrices[$productId]->retail_price = $fallBackRetailPrice;
			}

			if (isset($allPrices[$productId]))
			{
				$allPrices[$productId]->fallback_price = $fallBackPrice;
			}
		}

		// Calculate price discounts
		self::selectHighestDiscount($allPrices);

		// Binding products tax rates
		$taxes         = RedshopbHelperTax::getProductsTaxRates(array_keys($allPrices));
		$globalTaxRate = 0;

		if (array_key_exists(0, $taxes) && !empty($taxes[0]))
		{
			foreach ($taxes[0] as $taxRateData)
			{
				$globalTaxRate += $taxRateData->tax_rate;
			}
		}

		foreach ($allPrices as $productId => $price)
		{
			$price->tax_rate = $globalTaxRate;

			if (array_key_exists($productId, $taxes) && !empty($taxes[$productId]))
			{
				if (!is_array($taxes[$productId]))
				{
					$taxes[$productId] = array($taxes[$productId]);
				}

				foreach ($taxes[$productId] as $index => $taxRateData)
				{
					$price->tax_rate += $taxRateData->tax_rate;
				}
			}

			$price->price_with_tax          = $price->price;
			$price->fallback_price_with_tax = $price->fallback_price;

			if ($price->tax_rate)
			{
				$price->price_with_tax += $price->price * $price->tax_rate;

				if ($price->fallback_price)
				{
					$price->fallback_price_with_tax += $price->fallback_price * $price->tax_rate;
				}
			}
		}

		return $allPrices;
	}

	/**
	 * Get all discounts
	 *
	 * @param   array  $conditions  Discount conditions
	 * @param   mixed  $currency    Default currency id or alpha3.
	 *
	 * @return mixed
	 */
	public static function getDiscounts($conditions, $currency = 'DKK')
	{
		$db  = Factory::getDbo();
		$now = Date::getInstance()->toSql();

		// Get maximum discount in "percent" type
		$query = $db->getQuery(true)
			->select(
				array(
					'MAX(percent) AS percent',
					'p.id',
					'CONCAT_WS(' . $db->q('_') . ', p.id, COALESCE(pd.currency_id, ' . $db->q($currency)
					. '), COALESCE(pd.sales_id, cdgx.customer_id, ' . $db->q('allDebtor') . ')) AS common'
				)
			)
			->from($db->qn('#__redshopb_product_discount', 'pd'))
			->leftJoin(
				$db->qn('#__redshopb_product_discount_group_xref', 'pdgx') . ' ON pd.type = '
				. $db->q('product_discount_group') . ' AND pdgx.discount_group_id = pd.type_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_customer_discount_group_xref', 'cdgx') . ' ON cdgx.discount_group_id = pd.sales_id AND pd.sales_type = '
				. $db->q('debtor_discount_group')
			)
			->leftJoin(
				$db->qn('#__redshopb_product', 'p') . ' ON ((pd.type = ' . $db->q('product') . ' AND pd.type_id = p.id) OR (pd.type = '
				. $db->q('product_discount_group') . ' AND pdgx.product_id = p.id))'
			)
			->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pd.currency_id')
			->where('(pd.starting_date = ' . $db->q($db->getNullDate()) . ' OR pd.starting_date <= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(pd.ending_date = ' . $db->q($db->getNullDate()) . ' OR pd.ending_date >= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('pd.state = 1')
			->group('common');

		$whereCond = array();

		foreach ($conditions as $condition)
		{
			if (!$condition->currencyId)
			{
				// Selecting currency. If currency is given as a number, getting it by ID
				if (is_numeric($currency))
				{
					$currencyCondition = '(pd.currency_id = ' . (int) $currency . ' OR pd.currency_id IS NULL)';
				}
				else
				{
					$currencyCondition = '(cur.alpha3 = ' . $db->q($currency) . ' OR pd.currency_id IS NULL)';
				}
			}
			else
			{
				$currencyCondition = '(pd.currency_id = ' . (int) $condition->currencyId . ' OR pd.currency_id IS NULL)';
			}

			$whereCond[] = '(p.id = ' . (int) $condition->id . ' AND ('
				. $db->qn('pd.sales_type') . ' = ' . $db->q('all_debtor') . ' OR (pd.sales_type = '
				. $db->q('debtor') . ' AND pd.sales_id = ' . (int) $condition->companyId . ') OR (pd.sales_type = '
				. $db->q('debtor_discount_group') . ' AND cdgx.customer_id = ' . (int) $condition->companyId . ')) AND ' . $currencyCondition . ')';
		}

		$query->where('(' . implode(' OR ', $whereCond) . ')');

		$db->setQuery($query);
		$oldTranslate  = $db->translate;
		$db->translate = false;
		$return        = $db->loadObjectList('common');
		$db->translate = $oldTranslate;

		return $return;
	}

	/**
	 * Get all discounts
	 *
	 * @param   array      $productItemIds  List Id of products
	 * @param   int        $customerId      Id of customer
	 * @param   mixed      $currency        Default currency id or alpha3 or null.
	 * @param   int|array  $quantity        Quantity amount of product.
	 *
	 * @return  false|array
	 *
	 * @since  1.12.73
	 */
	public static function getProductItemsDiscount($productItemIds, $customerId, $currency = null, $quantity = 0)
	{
		if (empty($productItemIds) || !is_array($productItemIds) || !$customerId)
		{
			return false;
		}

		$productItemIds = ArrayHelper::toInteger($productItemIds);

		if (!is_null($currency) && !is_numeric($currency))
		{
			$currency = RedshopbHelperProduct::getCurrency($currency)->id;
		}

		$db    = Factory::getDbo();
		$now   = Date::getInstance()->toSql();
		$query = $db->getQuery(true)
			->from($db->qn('#__redshopb_product_discount', 'pd'))
			->leftJoin(
				$db->qn('#__redshopb_customer_discount_group_xref', 'cdgx')
				. ' ON cdgx.discount_group_id = pd.sales_id AND pd.sales_type = ' . $db->quote('debtor_discount_group')
			)
			->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pd.currency_id')
			->where('(pd.starting_date = ' . $db->quote($db->getNullDate()) . ' OR pd.starting_date <= STR_TO_DATE('
				. $db->quote($now) . ', ' . $db->quote('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(pd.ending_date = ' . $db->quote($db->getNullDate()) . ' OR pd.ending_date >= STR_TO_DATE('
				. $db->quote($now) . ', ' . $db->quote('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('pd.state = 1')
			->where('((' . $db->qn('pd.sales_type') . ' = ' . $db->quote('all_debtor') . ' AND cdgx.customer_id IS NULL)'
				. ' OR (pd.sales_type = ' . $db->quote('debtor') . ' AND pd.sales_id = ' . (int) $customerId . ' AND cdgx.customer_id IS NULL)'
				. ' OR (pd.sales_type = ' . $db->quote('debtor_discount_group') . ' AND cdgx.customer_id = ' . (int) $customerId . '))'
			)
			->group('common');

		if (is_null($currency))
		{
			$query->where('pd.currency_id IS NULL');
		}
		else
		{
			$query->where('(pd.currency_id = ' . (int) $currency . ' OR pd.currency_id IS NULL)');
		}

		if ($quantity && !is_array($quantity))
		{
			$query->where(self::getDiscountQuantityQueryClosure($quantity));
		}

		$query->select('IF (pd.kind = 0, MAX(percent), MAX(total)) AS value')
			->select('IF (pd.kind = 0, ' . $db->quote(self::DISCOUNT_PERCENTAGE) . ', ' . $db->quote(self::DISCOUNT_TOTAL) . ') AS type');

		RFactory::getDispatcher()->trigger(
			'onRedshopbChangeProductItemsDiscountQuery',
			array(
				$query, $productItemIds, $customerId, $currency, $quantity
			)
		);

		$mainQuery = clone $query;
		$mainQuery->select(
			array(
				'pdgx.product_item_id AS id',
				'CONCAT_WS(' . $db->quote('_') . ', pdgx.product_item_id, COALESCE(pd.currency_id, ' . $db->q('none') . '),
					CASE pd.sales_type'
				. ' WHEN ' . $db->quote('debtor') . ' THEN pd.sales_id'
				. ' WHEN ' . $db->quote('debtor_discount_group') . ' THEN cdgx.customer_id'
				. ' WHEN ' . $db->quote('all_debtor') . ' THEN ' . $db->q('allDebtor') . ' END '
				. ') AS common'
			)
		)
			->leftJoin(
				$db->qn('#__redshopb_product_item_discount_group_xref', 'pdgx') . ' ON pd.type = '
				. $db->quote('product_discount_group') . ' AND pdgx.discount_group_id = pd.type_id'
			)
			->where($db->qn('pdgx.product_item_id') . ' IN (' . implode(',', $productItemIds) . ')')
			->where('pd.type = ' . $db->quote('product_discount_group'));
		$combineProductsByQuantity = array();

		if ($quantity && is_array($quantity))
		{
			foreach ($quantity as $productItemId => $productItemQuantity)
			{
				if (array_key_exists($productItemQuantity, $combineProductsByQuantity))
				{
					$combineProductsByQuantity[$productItemQuantity][] = $productItemId;
				}
				else
				{
					$combineProductsByQuantity[$productItemQuantity] = array($productItemId);
				}
			}

			$where = array();

			foreach ($combineProductsByQuantity as $productItemQuantity => $productItemQuantityIds)
			{
				$where[] = '(' . self::getDiscountQuantityQueryClosure($productItemQuantity)
					. ' AND pdgx.product_item_id IN (' . implode(',', $productItemQuantityIds) . '))';
			}

			$mainQuery->where('(' . implode(' OR ', $where) . ')');
		}

		$mainQuery2 = clone $query;
		$mainQuery2->select(
			array(
				'pd.type_id AS id',
				'CONCAT_WS(' . $db->quote('_') . ', pd.type_id, COALESCE(pd.currency_id, ' . $db->q('none') . '),
						CASE pd.sales_type'
				. ' WHEN ' . $db->quote('debtor') . ' THEN pd.sales_id'
				. ' WHEN ' . $db->quote('debtor_discount_group') . ' THEN cdgx.customer_id'
				. ' WHEN ' . $db->quote('all_debtor') . ' THEN ' . $db->q('allDebtor') . ' END '
				. ') AS common'
			)
		)
			->where('pd.type = ' . $db->quote('product_item') . ' AND pd.type_id IN (' . implode(',', $productItemIds) . ')');

		if ($quantity && is_array($quantity))
		{
			$where = array();

			foreach ($combineProductsByQuantity as $productItemQuantity => $productItemQuantityIds)
			{
				$where[] = '(' . self::getDiscountQuantityQueryClosure($productItemQuantity)
					. ' AND pd.type_id IN (' . implode(',', $productItemQuantityIds) . '))';
			}

			$mainQuery2->where('(' . implode(' OR ', $where) . ')');
		}

		$mainQuery->union($mainQuery2);

		RFactory::getDispatcher()->trigger(
			'onRedshopbChangeProductItemsDiscountMainQuery',
			array(
				$mainQuery, $productItemIds, $customerId, $currency, $quantity
			)
		);

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$return        = $db->setQuery($mainQuery)
			->loadObjectList();
		$db->translate = $oldTranslate;

		$result = array();

		if (!empty($return))
		{
			foreach ($return as $discount)
			{
				$key = $discount->common;

				if (!isset($result[$key]))
				{
					$result[$key] = array();
				}

				$result[$key][] = $discount;
			}
		}

		return $result;
	}

	/**
	 * Get all discounts
	 *
	 * @param   array      $productIds  List Id of products
	 * @param   int        $customerId  Id of customer
	 * @param   mixed      $currency    Default currency id or alpha3 or null.
	 * @param   int|array  $quantity    Quantity amount of product.
	 *
	 * @return mixed
	 */
	public static function getProductsDiscount($productIds, $customerId, $currency = null, $quantity = 0)
	{
		if (empty($productIds) || !is_array($productIds) || !$customerId)
		{
			return false;
		}

		$productIds = ArrayHelper::toInteger($productIds);

		if (!is_null($currency) && !is_numeric($currency))
		{
			$currency = RedshopbHelperProduct::getCurrency($currency)->id;
		}

		$db    = Factory::getDbo();
		$now   = Date::getInstance()->toSql();
		$query = $db->getQuery(true)
			->from($db->qn('#__redshopb_product_discount', 'pd'))
			->leftJoin(
				$db->qn('#__redshopb_customer_discount_group_xref', 'cdgx')
				. ' ON cdgx.discount_group_id = pd.sales_id AND pd.sales_type = ' . $db->quote('debtor_discount_group')
			)
			->leftJoin($db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pd.currency_id')
			->where('(pd.starting_date = ' . $db->quote($db->getNullDate()) . ' OR pd.starting_date <= STR_TO_DATE('
				. $db->quote($now) . ', ' . $db->quote('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(pd.ending_date = ' . $db->quote($db->getNullDate()) . ' OR pd.ending_date >= STR_TO_DATE('
				. $db->quote($now) . ', ' . $db->quote('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('pd.state = 1')
			->where('((' . $db->qn('pd.sales_type') . ' = ' . $db->quote('all_debtor') . ' AND cdgx.customer_id IS NULL)'
				. ' OR (pd.sales_type = ' . $db->quote('debtor') . ' AND pd.sales_id = ' . (int) $customerId . ' AND cdgx.customer_id IS NULL)'
				. ' OR (pd.sales_type = ' . $db->quote('debtor_discount_group') . ' AND cdgx.customer_id = ' . (int) $customerId . '))'
			)
			->group('common');

		if (is_null($currency))
		{
			$query->where('pd.currency_id IS NULL');
		}
		else
		{
			$query->where('(pd.currency_id = ' . (int) $currency . ' OR pd.currency_id IS NULL)');
		}

		if ($quantity && !is_array($quantity))
		{
			$query->where(self::getDiscountQuantityQueryClosure($quantity));
		}

		$query->select('IF (pd.kind = 0, MAX(percent), MAX(total)) AS value')
			->select('IF (pd.kind = 0, ' . $db->quote(self::DISCOUNT_PERCENTAGE) . ', ' . $db->quote(self::DISCOUNT_TOTAL) . ') AS type');

		RFactory::getDispatcher()->trigger(
			'onRedshopbChangeProductsDiscountQuery',
			array(
				$query, $productIds, $customerId, $currency, $quantity
			)
		);

		$mainQuery = clone $query;
		$mainQuery->select(
			array(
				'pdgx.product_id AS id',
				'CONCAT_WS(' . $db->quote('_') . ', pdgx.product_id, COALESCE(pd.currency_id, ' . $db->q('none') . '),
					CASE pd.sales_type'
				. ' WHEN ' . $db->quote('debtor') . ' THEN pd.sales_id'
				. ' WHEN ' . $db->quote('debtor_discount_group') . ' THEN cdgx.customer_id'
				. ' WHEN ' . $db->quote('all_debtor') . ' THEN ' . $db->q('allDebtor') . ' END '
				. ') AS common'
			)
		)
			->leftJoin(
				$db->qn('#__redshopb_product_discount_group_xref', 'pdgx') . ' ON pd.type = '
				. $db->quote('product_discount_group') . ' AND pdgx.discount_group_id = pd.type_id'
			)
			->where($db->qn('pdgx.product_id') . ' IN (' . implode(',', $productIds) . ')')
			->where('pd.type = ' . $db->quote('product_discount_group'));
		$combineProductsByQuantity = array();

		if ($quantity && is_array($quantity))
		{
			foreach ($quantity as $productId => $productQuantity)
			{
				if (array_key_exists($productQuantity, $combineProductsByQuantity))
				{
					$combineProductsByQuantity[$productQuantity][] = $productId;
				}
				else
				{
					$combineProductsByQuantity[$productQuantity] = array($productId);
				}
			}

			$where = array();

			foreach ($combineProductsByQuantity as $productQuantity => $productQuantityIds)
			{
				$where[] = '(' . self::getDiscountQuantityQueryClosure($productQuantity)
					. ' AND pdgx.product_id IN (' . implode(',', $productQuantityIds) . '))';
			}

			$mainQuery->where('(' . implode(' OR ', $where) . ')');
		}

		$mainQuery2 = clone $query;
		$mainQuery2->select(
			array(
				'pd.type_id AS id',
				'CONCAT_WS(' . $db->quote('_') . ', pd.type_id, COALESCE(pd.currency_id, ' . $db->q('none') . '),
						CASE pd.sales_type'
				. ' WHEN ' . $db->quote('debtor') . ' THEN pd.sales_id'
				. ' WHEN ' . $db->quote('debtor_discount_group') . ' THEN cdgx.customer_id'
				. ' WHEN ' . $db->quote('all_debtor') . ' THEN ' . $db->q('allDebtor') . ' END '
				. ') AS common'
			)
		)
			->where('pd.type = ' . $db->quote('product') . ' AND pd.type_id IN (' . implode(',', $productIds) . ')');

		if ($quantity && is_array($quantity))
		{
			$where = array();

			foreach ($combineProductsByQuantity as $productQuantity => $productQuantityIds)
			{
				$where[] = '(' . self::getDiscountQuantityQueryClosure($productQuantity)
					. ' AND pd.type_id IN (' . implode(',', $productQuantityIds) . '))';
			}

			$mainQuery2->where('(' . implode(' OR ', $where) . ')');
		}

		$mainQuery->union($mainQuery2);

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$return        = $db->setQuery($mainQuery)
			->loadObjectList();
		$db->translate = $oldTranslate;

		$result = array();

		if (!empty($return))
		{
			foreach ($return as $discount)
			{
				$key = $discount->common;

				if (!isset($result[$key]))
				{
					$result[$key] = array();
				}

				$result[$key][] = $discount;
			}
		}

		return $result;
	}

	/**
	 * Get Discount Quantity Query Closure
	 *
	 * @param   int|float  $quantity  Quantity
	 *
	 * @return string
	 *
	 * @since 1.13.0
	 */
	protected static function getDiscountQuantityQueryClosure($quantity)
	{
		$db = Factory::getDbo();

		return '(
			(pd.quantity_max >= ' . $db->q($quantity) . ' AND (pd.quantity_min IS NULL OR pd.quantity_min <= ' . $db->q($quantity) . '))
			OR
			(pd.quantity_min <= ' . $db->q($quantity) . ' AND (pd.quantity_max IS NULL OR pd.quantity_max >= ' . $db->q($quantity) . '))
			OR
			(pd.quantity_min IS NULL AND pd.quantity_max IS NULL)
		)';
	}

	/**
	 * Get Discount Condition
	 *
	 * @param   int         $productId   Product id
	 * @param   int         $companyId   Company id
	 * @param   int|string  $currencyId  Currency id
	 *
	 * @return stdClass
	 */
	public static function getDiscountCondition($productId, $companyId, $currencyId)
	{
		$new             = new stdClass;
		$new->id         = $productId;
		$new->companyId  = $companyId;
		$new->currencyId = $currencyId;

		return $new;
	}

	/**
	 * Gets discount for given product.
	 *
	 * @param   array  $productId   Product Ids.
	 * @param   int    $companyId   Company id.
	 * @param   int    $currencyId  Currency id
	 *
	 * @return  mixed               List of products discounts.
	 */
	public static function getDiscount($productId, $companyId, $currencyId = null)
	{
		$hash = md5(serialize(get_defined_vars()));

		if (isset(self::$discounts[$hash]))
		{
			return self::$discounts[$hash];
		}

		$db  = Factory::getDbo();
		$now = Date::getInstance()->toSql();

		$percent = $db->getQuery(true)
			->select('MAX(percent)')
			->from($db->qn('#__redshopb_product_discount', 'pd'))
			->leftJoin(
				$db->qn('#__redshopb_product_discount_group_xref', 'pdgx') . ' ON pd.type = '
				. $db->q('product_discount_group') . ' AND pdgx.discount_group_id = pd.type_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_customer_discount_group_xref', 'cdgx') . ' ON cdgx.discount_group_id = pd.sales_id AND pd.sales_type = '
				. $db->q('debtor_discount_group')
			)
			->where('((pd.type = ' . $db->q('product') . ' AND pd.type_id = p.id) OR (pd.type = '
				. $db->q('product_discount_group') . ' AND pdgx.discount_group_id = pd.type_id AND pdgx.product_id = p.id))'
			)
			->where('(pd.starting_date = ' . $db->q($db->getNullDate()) . ' OR pd.starting_date <= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(pd.ending_date = ' . $db->q($db->getNullDate()) . ' OR pd.ending_date >= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('pd.state = 1')
			->where('(' . $db->qn('pd.sales_type') . ' = ' . $db->q('all_debtor') . ' OR (pd.sales_type = '
				. $db->q('debtor') . ' AND pd.sales_id = ' . (int) $companyId . ') OR (pd.sales_type = '
				. $db->q('debtor_discount_group') . ' AND cdgx.customer_id = ' . (int) $companyId . '))'
			);

		if (null !== $currencyId)
		{
			$percent->where('(pd.currency_id = ' . (int) $currencyId . ' OR pd.currency_id IS NULL)');
		}
		else
		{
			$percent->where('pd.currency_id IS NULL');
		}

		$query = $db->getQuery(true)
			->select(
				array(
					'p.id',
					'(' . $percent . ') AS percent'
				)
			)
			->from($db->qn('#__redshopb_product', 'p'))
			->where('p.id = ' . (int) $productId);

		$db->setQuery($query);

		self::$discounts[$hash] = $db->loadObject();

		return self::$discounts[$hash];
	}

	/**
	 * Checks if price applies to this customer.
	 *
	 * @param   string  $salesType  Sales type (customer, customer_price_group).
	 * @param   int     $salesCode  Sales code (id of customer or id of customer price group).
	 * @param   int     $companyId  Company id (customer id).
	 *
	 * @return boolean  True if it applies, false otherwise.
	 */
	public static function isRightCustomer($salesType, $salesCode, $companyId)
	{
		if ($salesType == 'customer_price')
		{
			return $salesCode == $companyId;
		}
		else
		{
			if (!isset(self::$isRightCustomer[$salesCode . '_' . $companyId]))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select('*')
					->from($db->qn('#__redshopb_customer_price_group_xref'))
					->where($db->qn('price_group_id') . ' = ' . (int) $salesCode)
					->where($db->qn('customer_id') . ' = ' . (int) $companyId);
				$db->setQuery($query);
				$result = $db->loadObject();

				if (is_null($result) || empty($result))
				{
					self::$isRightCustomer[$salesCode . '_' . $companyId] = false;
				}
				else
				{
					self::$isRightCustomer[$salesCode . '_' . $companyId] = true;
				}
			}

			return self::$isRightCustomer[$salesCode . '_' . $companyId];
		}
	}

	/**
	 * Get offer currency
	 *
	 * @param   int     $customerId    Customer id
	 * @param   string  $customerType  Customer type
	 * @param   int     $collectionId  Collection id
	 *
	 * @return  integer|string
	 */
	public static function getCurrency($customerId, $customerType, $collectionId = 0)
	{
		$funcArgs          = get_defined_vars();
		$key               = serialize($funcArgs);
		static $currencies = array();

		if (!array_key_exists($key, $currencies))
		{
			$config   = RedshopbEntityConfig::getInstance();
			$currency = $config->getInt('default_currency', 38);

			if ($collectionId)
			{
				$currency = RedshopbHelperCollection::getCurrency($collectionId);
			}
			else
			{
				$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

				if ($companyId)
				{
					$currency = RedshopbEntityCompany::getInstance($companyId)->getCustomerCurrency();
				}
			}

			$currencies[$key] = $currency;
		}

		return $currencies[$key];
	}

	/**
	 * Check display prices or not
	 *
	 * @return integer
	 */
	public static function displayPrices()
	{
		static $isShop = null;

		if ($isShop === null)
		{
			$config = RedshopbEntityConfig::getInstance();
			$isShop = $config->getInt('show_price', 1);
			$values = new stdClass;
			RedshopbHelperShop::setUserStates($values);
			$company = RedshopbHelperCompany::getCompanyById($values->companyId);

			if ($company && $company->show_price != -1)
			{
				$isShop = $company->show_price;
			}

			// Checks ACL for displaying prices
			if ($isShop)
			{
				$user = RedshopbHelperCommon::getUser();

				if ($user->b2cMode)
				{
					// Disable shop prices
					if (!RedshopbHelperACL::getGlobalB2CPermission('view', 'shopprice'))
					{
						$isShop = 0;
					}
				}
				else
				{
					// Disable shop prices
					if (!RedshopbHelperACL::getPermission('view', 'shopprice'))
					{
						$isShop = 0;
					}
				}
			}

			RFactory::getDispatcher()->trigger('onAfterRedshopbHelperPricesDisplayPrices', array(&$isShop));
		}

		// Allows a false value so it can be recognized as "forced"
		if ($isShop === false)
		{
			return $isShop;
		}

		return (int) $isShop;
	}

	/**
	 * Calculate discount
	 *
	 * @param   float   $price         Price
	 * @param   string  $discountType  Discount type
	 * @param   float   $discount      Discount
	 *
	 * @return  integer
	 */
	public static function calculateDiscount($price, $discountType = self::DISCOUNT_PERCENTAGE, $discount = 0.00)
	{
		switch ($discountType)
		{
			case self::DISCOUNT_TOTAL:
				$price = $price - $discount;
				break;
			case self::DISCOUNT_PERCENTAGE:
			default:
				$price = $price - ($price * $discount / 100);
				break;
		}

		if ($price < 0)
		{
			$price = 0;
		}

		return $price;
	}

	/**
	 * Method for calculate and select highest discount for price.
	 *
	 * @param   array  $prices  List of product prices.
	 *
	 * @since  1.9.7
	 *
	 * @return void
	 */
	public static function selectHighestDiscount(&$prices)
	{
		if (empty($prices))
		{
			return;
		}

		foreach ($prices as $productId => $price)
		{
			if (!empty($price->price_without_discount))
			{
				$price->price = $price->price_without_discount;
			}

			if (empty($price->allow_discount) || empty($price->discount))
			{
				continue;
			}

			$discountPrice = $price->price_without_discount;
			$discountValue = 0;
			$discountType  = '';
			$hasDiscount   = false;

			foreach ($price->discount as $discount)
			{
				$tmpPrice = self::calculateDiscount($price->price_without_discount, $discount->type, $discount->value);

				if ($tmpPrice < $discountPrice)
				{
					$hasDiscount   = true;
					$discountValue = (float) $discount->value;
					$discountType  = $discount->type;
					$discountPrice = $tmpPrice;
				}
			}

			if ($hasDiscount)
			{
				$price->price         = $discountPrice;
				$price->discount      = $discountValue;
				$price->discount_type = $discountType;
			}
			else
			{
				$price->discount      = 0;
				$price->discount_type = 'percent';
			}
		}
	}

	/**
	 * Method for return text of discount.
	 *
	 * @param   int     $value         Discount amount
	 * @param   string  $discountType  Discount type
	 * @param   mixed   $currency      Currency value
	 *
	 * @return  string                 Formatted string of discount
	 *
	 * @since   1.9.7
	 */
	public static function displayDiscount($value = 0, $discountType = self::DISCOUNT_PERCENTAGE, $currency = null)
	{
		if (!$value)
		{
			return '';
		}

		if ($discountType == self::DISCOUNT_TOTAL)
		{
			return RedshopbHelperProduct::getProductFormattedPrice($value, $currency);
		}

		return $value . '%';
	}

	/**
	 * Function for getting base prices query on given product/product item.
	 *
	 * @param   array   $productIds      Product ids.
	 * @param   array   $productItemIds  Product item ids.
	 * @param   mixed   $currency        Item currency.
	 * @param   string  $date            Date for checking prices.
	 * @param   int     $quantity        Item quantity.
	 * @param   int     $customerId      Customer id.
	 * @param   string  $customerType    Customer type.
	 *
	 * @return  JDatabaseQuery  Query for later usage.
	 *
	 * @since   1.12.66
	 */
	public static function getBaseQuery(
		$productIds, $productItemIds = array(), $currency = 0, $date = '',
		$quantity = 0, $customerId = 0, $customerType = ''
	)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$now   = Date::getInstance()->toSql();

		// Setting default currency
		$defaultCurrencyObj = RedshopbApp::getDefaultCurrency();
		$defaultCurrency    = $defaultCurrencyObj->get('alpha3');
		$defaultCurrencyId  = (int) $defaultCurrencyObj->get('id');

		if ($customerId != 0 && !empty($customerType))
		{
			$company = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		}
		else
		{
			$company = 0;
		}

		$query->select(
			array(
				'pp.*',
				$db->qn('pp.price', 'price_without_discount'),
				$db->qn('pp.retail_price', 'retail_price'),
				$db->qn('pp.type', 'type'),
				$db->qn('pp.type_id', 'type_id'),
				$db->qn('pp.sales_type', 'stype'),
				$db->qn('pp.sales_code', 'scode'),
				'COALESCE(pp.currency_id, ' . $db->q($defaultCurrencyId) . ') AS currency_id',
				$db->qn('pp.allow_discount', 'allow_discount'),
				'COALESCE(cur.alpha3, ' . $db->q($defaultCurrency) . ') AS currency',
				'COALESCE(pp.currency_id, ' . $db->q($defaultCurrencyId) . ') AS retail_currency_id',
				$db->qn('pp.quantity_min', 'quantity_min'),
				$db->qn('pp.quantity_max', 'quantity_max'),
				'CASE
					WHEN (' . $db->qn('pp.quantity_min') . ' > 0  AND ' . $db->qn('pp.is_multiple') . ' = 1 AND '
						. 'MOD(' . (int) $quantity . ',' . $db->qn('pp.quantity_min') . ') = 0) THEN 4
					WHEN (' . $db->qn('pp.quantity_min') . ' > 0 AND ' . $db->qn('pp.quantity_max') . ' > 0 AND ('
						. (int) $quantity . ' BETWEEN ' . $db->qn('pp.quantity_min') . ' AND '
						. $db->qn('pp.quantity_max') . ') AND ' . $db->qn('pp.is_multiple') . ' != 1) THEN 3
					WHEN (' . $db->qn('pp.quantity_min') . ' > 0 AND (' . $db->qn('pp.quantity_max') . ' = 0 OR '
						. $db->qn('pp.quantity_max') . ' IS NULL) AND ' . $db->qn('pp.quantity_min') . ' <= ' . (int) $quantity
						. ' AND ' . $db->qn('pp.is_multiple') . ' != 1) THEN 2
					WHEN ((' . $db->qn('pp.quantity_min') . ' = 0 OR ' . $db->qn('pp.quantity_min') . ' IS NULL) AND '
						. $db->qn('pp.quantity_max') . ' > 0 AND ' . $db->qn('pp.quantity_max') . ' >= ' . (int) $quantity
						. ' AND ' . $db->qn('pp.is_multiple') . ' != 1) THEN 2
					WHEN (' . $db->qn('pp.quantity_min') . ' = 0 AND ' . $db->qn('pp.quantity_max') . ' = 0) THEN 1
					WHEN (' . $db->qn('pp.quantity_min') . ' IS NULL AND ' . $db->qn('pp.quantity_max') . ' IS NULL) THEN 1
					WHEN 1 THEN 0
					END AS ' . $db->qn('volume_order')
			)
		)
			->from($db->qn('#__redshopb_product_price', 'pp'));

		// Select only currently used prices or for provided date
		if (!empty($date))
		{
			$query->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
				. $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
				->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
					. $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				);
		}
		else
		{
			$query->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
				->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				);
		}

		// Narrow by price type
		if (!empty($productItemIds) && array_sum($productItemIds) > 0)
		{
			$query->where($db->qn('pp.type') . ' = ' . $db->q('product_item'))
				->where($db->qn('pp.type_id') . ' IN (' . implode(',', $productItemIds) . ')');
		}
		else
		{
			$query->where($db->qn('pp.type') . ' = ' . $db->q('product'))
				->where($db->qn('pp.type_id') . ' IN (' . implode(',', $productIds) . ')');
		}

		// Narrow by currency
		$currency = (is_numeric($currency)) ? (int) $currency : (int) RedshopbHelperProduct::getCurrency($currency)->id;

		$query->where('(' . $db->qn('pp.currency_id') . ' = ' . $currency . ' OR ' . $db->qn('pp.currency_id') . ' IS NULL)');

		if ($company > 0)
		{
			// Narrow by current customer
			$subQuery = $db->getQuery(true);
			$subQuery->select('cpgx.price_group_id')
				->from($db->qn('#__redshopb_customer_price_group_xref', 'cpgx'))
				->where($db->qn('cpgx.customer_id') . ' = ' . (int) $company);

			$query->where(
				'(' . $db->qn('pp.sales_type') . ' = ' . $db->q('all_customers') . ' OR ('
				. $db->qn('pp.sales_type') . ' = ' . $db->q('customer_price') . ' AND ' . $db->qn('pp.sales_code') . ' = ' . (int) $company . ') OR ('
				. $db->qn('pp.sales_type') . ' = ' . $db->q('customer_price_group') . ' AND ' . $db->qn('pp.sales_code') . ' IN (' . $subQuery . ')))'
			);
		}

		$query->where($db->qn('pp.price') . ' >= 0');
		$query->order($db->qn('volume_order') . ' DESC', $db->qn('pp.price') . ' ASC');

		return $query;
	}

	/**
	 * Converts a formmatted currency string to a float number.
	 *
	 * @param   string  $price           Formatted price string.
	 * @param   int     $currencyCode    Currency code.
	 *
	 * @return  float   $price           Unformatted price as float
	 */
	public static function unformatCurrency($price, $currencyCode)
	{
		$currency = RedshopbHelperProduct::getCurrency($currencyCode);
		$price    = str_replace(trim($currency->symbol), '', $price);
		$price    = str_replace(trim($currency->thousands_separator), '', $price);
		$price    = str_replace(trim($currency->decimal_separator), '.', $price);
		$price    = preg_replace('/[^\\d.-]+/', '', $price);
		$price    = (float) $price;

		return $price;
	}
}
