<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
/**
 * A Cart helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperCart
{
	/**
	 * Cart items
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $items = array();

	/**
	 * Total Cart amount
	 *
	 * @var    float
	 * @since  2.0
	 */
	public $totalAmount = 0;

	/**
	 * Total Cart items
	 *
	 * @var    integer
	 * @since  2.0
	 */
	public $totalItems = 0;

	/**
	 * Array amount in stock current items
	 *
	 * @var array
	 */
	public static $amountInStock = array();

	/**
	 * Check for load from database
	 *
	 * @var boolean
	 */
	public static $loadingCartFromDatabase = false;

	/**
	 * Method to get tax totals per product
	 *
	 * @param   int     $customerId    customer ID
	 * @param   string  $customerType  customer type
	 *
	 * @return array
	 */
	public static function getCustomerCartTaxes($customerId = 0, $customerType = '')
	{
		$taxTotals = array();

		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$cart  = self::getCart($customerId, $customerType);
		$items = $cart->get('items', array());

		foreach ($items as $item)
		{
			if (!array_key_exists($item['currency'], $taxTotals))
			{
				$taxTotals[$item['currency']] = array();
			}

			foreach ($item['taxes'] as $tax)
			{
				$taxTotals[$item['currency']][] = $tax;
			}
		}

		$offers = $cart->get('offers', array());

		foreach ($offers as $offer)
		{
			if (!array_key_exists($offer['currency'], $taxTotals))
			{
				$taxTotals[$offer['currency']] = array();
			}

			foreach ($offer['items'] as $item)
			{
				foreach ($item['taxes'] as $tax)
				{
					$taxTotals[$offer['currency']][] = $tax;
				}
			}
		}

		return $taxTotals;
	}

	/**
	 * Method to get tax summary by tax
	 *
	 * @param   int     $customerId    customer ID
	 * @param   string  $customerType  customer type
	 *
	 * @return  array
	 */
	public static function getCustomerCartTaxByName($customerId = 0, $customerType= '')
	{
		$taxTotals = array();

		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$cart  = self::getCart($customerId, $customerType);
		$items = $cart->get('items', array());

		foreach ($items as $item)
		{
			if (!array_key_exists($item['currency'], $taxTotals))
			{
				$taxTotals[$item['currency']] = array();
			}

			foreach ($item['taxes'] as $tax)
			{
				if (empty($taxTotals[$item['currency']][$tax['name']]))
				{
					$taxTotals[$item['currency']][$tax['name']] = $tax;

					$taxTotals[$item['currency']][$tax['name']]['product'] = array();

					if (isset($tax['product']))
					{
						$taxTotals[$item['currency']][$tax['name']]['product'][] = $tax['product'];
					}

					continue;
				}

				$taxTotals[$item['currency']][$tax['name']]['tax'] += $tax['tax'];

				if (isset($tax['product']))
				{
					$taxTotals[$item['currency']][$tax['name']]['product'][] = $tax['product'];
				}
			}
		}

		$offers = $cart->get('offers', array());

		foreach ($offers as $offer)
		{
			if (!array_key_exists($offer['currency'], $taxTotals))
			{
				$taxTotals[$offer['currency']] = array();
			}

			foreach ($offer['items'] as $item)
			{
				foreach ($item['taxes'] as $tax)
				{
					if (empty($taxTotals[$offer['currency']][$tax['name']]))
					{
						$taxTotals[$offer['currency']][$tax['name']] = array();
					}

					$taxTotals[$offer['currency']][$tax['name']][] = $tax;
				}
			}
		}

		return $taxTotals;
	}

	/**
	 * Method for getting customer cart totals by currency.
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 * @param   bool    $withTax       Show totals with taxes or not.
	 *
	 * @return  array  Array of totals.
	 */
	public static function getCustomerCartTotals($customerId = 0, $customerType = '', $withTax = false)
	{
		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$cart = self::getCart($customerId, $customerType);

		$cartTotals = array();

		$items = $cart->get('items', array());

		foreach ($items as $item)
		{
			if (!array_key_exists($item['currency'], $cartTotals))
			{
				$cartTotals[$item['currency']] = 0;
			}

			$cartTotals[$item['currency']] += $item['subtotal'];
		}

		$offers = $cart->get('offers', array());

		foreach ($offers as $offer)
		{
			if (!array_key_exists($offer['currency'], $cartTotals))
			{
				$cartTotals[$offer['currency']] = 0;
			}

			$cartTotals[$offer['currency']] += $offer['total'];
		}

		if (!$withTax)
		{
			return $cartTotals;
		}

		$taxTotals = self::getCustomerCartTaxes();

		foreach ($cartTotals AS $currency => $total)
		{
			if (empty($taxTotals[$currency]))
			{
				continue;
			}

			foreach ($taxTotals[$currency] AS $tax)
			{
				$total += $tax['tax'];
			}

			$cartTotals[$currency] = $total;
		}

		if (empty($cartTotals))
		{
			$config          = RedshopbEntityConfig::getInstance();
			$defaultCurrency = $config->get('default_currency', 38);

			$cartTotals[$defaultCurrency] = 0.00;
		}

		return $cartTotals;
	}

	/**
	 * Returns first array element of the cart
	 *
	 * @return array
	 */
	public static function getFirstTotalPrice()
	{
		$item = self::getTotalFinalPrice();

		return !empty($item) ? array(key($item) => $item[key($item)]) : array('DKK' => '0.00');
	}

	/**
	 * Get total final price from all employees
	 *
	 * @return array
	 */
	public static function getTotalFinalPrice()
	{
		$customers       = self::getCartCustomers();
		$totalFinalPrice = array();

		foreach ($customers as $customer)
		{
			$cstring      = explode('.', $customer);
			$customerId   = $cstring[1];
			$customerType = $cstring[0];
			$cart         = self::getCart($customerId, $customerType);
			$items        = $cart->get('items', array());

			foreach ($items as $cartItem)
			{
				$currency          = $cartItem['currency'];
				$cartItem['price'] = (isset($cartItem['price'])) ? $cartItem['price'] : 0;

				if (!isset($totalFinalPrice[$currency]))
				{
					$totalFinalPrice[$currency] = 0;
				}

				$totalFinalPrice[$currency] += $cartItem['price'] * $cartItem['quantity'];
			}

			$offers = $cart->get('offers', array());

			foreach ($offers as $offer)
			{
				$currency = $offer['currency'];

				if (!isset($totalFinalPrice[$currency]))
				{
					$totalFinalPrice[$currency] = 0;
				}

				$totalFinalPrice[$currency] += $offer['total'];
			}
		}

		return $totalFinalPrice;
	}

	/**
	 * get Cart Customers
	 *
	 * @return mixed
	 */
	public static function getCartCustomers()
	{
		return Factory::getSession()->get('customers', array(), 'redshopb');
	}

	/**
	 * Add item to the Cart.
	 *
	 * @param   array           $item          Cart Item.
	 * @param   integer|float   $quantity      Number of items.
	 * @param   integer         $customerId    Customer id.
	 * @param   string          $customerType  Customer type.
	 *
	 * @return  array  Cart items
	 */
	public static function addToCart($item, $quantity, $customerId, $customerType)
	{
		$app   = Factory::getApplication();
		$cart  = self::getCart($customerId, $customerType);
		$taxes = RedshopbHelperTax::getProductsTaxRates(array($item['productId']), $customerId, $customerType);

		if (!is_array($taxes))
		{
			$taxes = array($item['productId'] => $taxes);
		}

		$items = $cart->get('items', array());

		$app->triggerEvent('onBeforeRedshopbAddToCart', array(&$items, &$item, &$quantity, $customerId, $customerType));

		$cart->set('items', $items);

		$hash = $cart->generateItemHash($item);

		if ($cart->itemExists($hash))
		{
			$quantity = $cart->getItemQuantity($hash) + $quantity;
			$cart->removeItem($hash);
		}

		// Check if there are quantity chunks for split / existence of price multiplications
		$quantityArr = self::splitQuantityMultiplications(
			$item['productId'],
			$item['productItem'],
			$quantity, $item['currency'],
			$customerId, $customerType,
			$item['collectionId']
		);

		foreach ($quantityArr as $quantity)
		{
			$item = self::updateItemPrice($item, $quantity, $customerId, $customerType);

			// Support custom item price calculation
			$app->triggerEvent('onAfterRedshopbCartItemPriceUpdate', array(&$item, $quantity, $hash));

			$item['quantity']  = $quantity;
			$item['price']     = (isset($item['price'])) ? $item['price'] : 0;
			$item['subtotal']  = ($item['price'] + self::accessoriesTotalPrice($item)) * $quantity;
			$item['total_tax'] = 0;
			$item['taxes']     = self::getTaxObjects($taxes, $item);

			foreach ($item['taxes'] AS $tax)
			{
				$item['total_tax'] += $tax['tax'];
			}

			$item['subtotal_with_tax'] = $item['subtotal'] + $item['total_tax'];

			$cart->addItem($hash, $item);
		}

		$cart = self::checkTotal($cart, $customerId, $customerType, $item['currency']);

		self::setCart($cart, $customerId, $customerType);

		return $cart->get('items', array());
	}

	/**
	 * Method to check if there are prices with multiplicity on.
	 * If so and if we have quantity enough for that price range,
	 * we need to split quantity into chunks for cart add.
	 *
	 * @param   integer           $productId      Product id for adding to cart.
	 * @param   integer           $productItemId  Product item id for adding to cart.
	 * @param   integer|float     $quantity       Item quantity we want to add to cart.
	 * @param   mixed             $currency       Item currency.
	 * @param   integer           $customerId     Customer id.
	 * @param   string            $customerType   Customer type.
	 * @param   integer           $collectionId   Collection id.
	 *
	 * @return  array  Array of quantity chunks.
	 *
	 * @since   1.12.65
	 */
	public static function splitQuantityMultiplications($productId, $productItemId, $quantity, $currency, $customerId, $customerType, $collectionId)
	{
		// Collection prices follows different logic
		if ($collectionId > 0)
		{
			return array($quantity);
		}

		$db    = Factory::getDbo();
		$query = RedshopbHelperPrices::getBaseQuery(array($productId), array($productItemId), $currency, '', 0, $customerId, $customerType);

		$query->clear('select')->select(
			array(
				$db->qn('pp.quantity_min', 'min'),
				$db->qn('pp.quantity_max', 'max'),
				$db->qn('pp.is_multiple', 'multi')
			)
		);

		$query->clear('order')->order($db->qn('pp.is_multiple') . ' DESC,' . $db->qn('pp.quantity_min') . ' DESC');
		$quantities = $db->setQuery($query)->loadAssocList();

		// If there aren't any multiple prices just return quantity
		if (empty($quantities) || !$quantities[0]['multi'])
		{
			return array($quantity);
		}
		else
		{
			$res = array();

			foreach ($quantities as $q)
			{
				if ($quantity == 0)
				{
					break;
				}

				if ($q['multi'])
				{
					$multiplier = ($quantity - $quantity % $q['min']) / $q['min'];

					if ($multiplier)
					{
						$res[]     = $multiplier * $q['min'];
						$quantity -= $multiplier * $q['min'];
					}
				}
			}

			if ($quantity > 0)
			{
				$res[] = $quantity;
			}

			return $res;
		}
	}

	/**
	 * Method to get individual taxes for an item
	 *
	 * @param   array  $taxes  all applicable taxes
	 * @param   array  $item   to be taxed
	 *
	 * @return array
	 */
	protected static function getTaxObjects($taxes, $item)
	{
		$itemTaxes = array();

		foreach ($taxes AS $tax)
		{
			if (is_array($tax))
			{
				$taxValues = self::getTaxObjects($tax, $item);

				$itemTaxes = array_merge($itemTaxes, $taxValues);

				continue;
			}

			if (!is_object($tax))
			{
				continue;
			}

			$singleTax             = array();
			$singleTax['name']     = $tax->name;
			$singleTax['product']  = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item['product_name']);
			$singleTax['tax_rate'] = $tax->tax_rate;
			$singleTax['tax']      = $item['subtotal'] * $tax->tax_rate;

			$itemTaxes[] = $singleTax;
		}

		return $itemTaxes;
	}

	/**
	 * Get the cart from Session storage
	 *
	 * @param   int     $customerId      Customer id.
	 * @param   string  $customerType    Customer type.
	 * @param   bool    $returnFullData  Flag for return normal products and offers in a cart
	 *
	 * @return  array  Cart helper Class
	 *
	 * @deprecated 1.13.2 Use {@see getCart} instead
	 */
	public static function getCartFromSession($customerId = 0 , $customerType = '', $returnFullData = false)
	{
		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$cart = self::getCart($customerId, $customerType);

		if (true === $returnFullData)
		{
			return array(
				'items' => $cart->get('items', array()),
				'offers' => $cart->get('offers', array())
			);
		}

		return $cart->get('items', array());
	}

	/**
	 * Load items from database
	 *
	 * @param   array  $cartItems  Cart items
	 *
	 * @return   array|false
	 */
	public static function loadItems($cartItems)
	{
		if (!count($cartItems))
		{
			return false;
		}

		static $items = array();
		$products     = array();
		$productItems = array();
		$displaySKU   = (boolean) Factory::getApplication()->getUserState('shop.cart.display_sku', true);
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true)
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pc3') . ' ON pc3.product_id = p.id AND p.category_id = pc3.category_id')
			->leftJoin($db->qn('#__redshopb_category', 'c3') . ' ON pc3.category_id = c3.id AND c3.state = 1')
			->where('p.state = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			);

		foreach ($cartItems as $item)
		{
			$key = $item['productId'] . '_' . (int) $item['productItem'];

			// Select just non cached items
			if (array_key_exists($key, $items))
			{
				continue;
			}

			if (isset($item['productItem']) && $item['productItem'])
			{
				$productItems[] = '(p.id = ' . (int) $item['productId'] . ' AND pi.id = ' . (int) $item['productItem'] . ')';
			}
			else
			{
				$products[] = (int) $item['productId'];
			}

			$items[$key] = false;
		}

		$displayNameSelect = ($displaySKU) ? 'CONCAT(p.sku, " ", p.name) AS product_name' : $db->qn('p.name', 'product_name');
		$query->select($displayNameSelect);

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(DISTINCT c2.id ORDER BY c2.id ASC SEPARATOR ' . $db->q(',') . ')')
			->from($db->qn('#__redshopb_category', 'c2'))
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pc2') . ' ON c2.id = pc2.category_id')
			->where('p.id = pc2.product_id')
			->where('((p.category_id IS NOT NULL AND p.category_id != pc2.category_id) OR p.category_id IS NULL)')
			->where('c2.state = 1');

		$query->select('(' . $subQuery . ' LIMIT 0, 1) AS category_id_xref')
			->select('c3.id AS category_id');

		$productsData = self::loadProductsData($products, $db, $query);

		foreach ($productsData as $product)
		{
			$key         = $product['productId'] . '_0';
			$items[$key] = $product;

			if (!$product['category_id'])
			{
				$items[$key]['category_id'] = $product['category_id_xref'];
			}

			unset($items[$key]['category_id_xref']);
		}

		if (count($productItems) > 0)
		{
			$productItemsData = self::loadProductItemsData($productItems, $db, $query);

			foreach ($productItemsData as $productItem)
			{
				$key = $productItem['productId'] . '_' . (int) $productItem['productItem'];

				if ($items[$key] != false)
				{
					$items[$key]['sku']          .= '-' . $productItem['sku'];
					$items[$key]['string_value'] .= '-' . $productItem['string_value'];

					continue;
				}

				if (!$productItem['category_id'])
				{
					$productItem['category_id'] = $productItem['category_id_xref'];
				}

				unset($productItem['category_id_xref']);
				$items[$key] = $productItem;
			}
		}

		$result = array();

		foreach ($cartItems as $item)
		{
			$key = $item['productId'] . '_' . (int) $item['productItem'];

			// If product not specific decimal position. Check on Unit Measure fallback if available
			if (is_null($items[$key]['decimal']))
			{
				$items[$key]['decimal'] = RedshopbEntityProduct::load($item['productId'])->getDecimalPosition();
			}

			$result[$key] = $items[$key];
		}

		RFactory::getDispatcher()->trigger('onAfterRedshopbLoadItems', array($cartItems, &$result));

		return $result;
	}

	/**
	 * Method to load product data from database
	 *
	 * @param   array            $products  Array of product IDs
	 * @param   JDatabaseDriver  $db        DBO object
	 * @param   JDatabaseQuery   $query     Base query to use to search for products
	 *
	 * @return array
	 */
	protected static function loadProductsData($products, $db, $query)
	{
		if (empty($products))
		{
			return array();
		}

		$productsQuery = clone $query;

		$productsQuery->select(
			array(
				'p.sku',
				$db->q('') . ' AS string_value',
				'0 AS productItem',
				$db->qn('p.id', 'productId'),
				'0 AS ordering',
				$db->q('') . ' AS name',
				'0 AS type_id',
				'p.stock_upper_level',
				'p.stock_lower_level',
				'p.min_sale',
				'p.max_sale',
				'p.pkg_size',
				'p.name',
				'p.unit_measure_id',
				$db->qn('p.decimal_position', 'decimal')
			)
		)
			->where('p.id IN (' . implode(',', $products) . ')');

		$productsData = $db->setQuery($productsQuery)->loadAssocList();

		if (empty($productsData))
		{
			return array();
		}

		return $productsData;
	}

	/**
	 * Method to load product items data
	 *
	 * @param   array            $productItems  Array of product item IDs
	 * @param   JDatabaseDriver  $db            DBO object
	 * @param   JDatabaseQuery   $query         Base query to use to search for products
	 *
	 * @return array
	 */
	protected static function loadProductItemsData($productItems, $db, $query)
	{
		if (empty($productItems))
		{
			return array();
		}

		$productItemsQuery = clone $query;
		$productItemsQuery->select(
			array(
				'pi.sku',
				'pav.string_value',
				'pi.id AS productItem',
				$db->qn('p.id', 'productId'),
				'pa.ordering',
				'pa.name',
				'pa.type_id',
				$db->qn('p.decimal_position', 'decimal'),
				'pi.stock_upper_level',
				'pi.stock_lower_level'
			)
		)
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.product_id = p.id AND pi.state = 1')
			->leftJoin(
				$db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON ' .
				$db->qn('pivx.product_item_id') . ' = ' . $db->qn('pi.id')
			)
			->leftJoin(
				$db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON ' .
				$db->qn('pivx.product_attribute_value_id') . ' = ' . $db->qn('pav.id') . ' AND pav.state = 1'
			)
			->leftJoin(
				$db->qn('#__redshopb_product_attribute', 'pa') . ' ON ' .
				$db->qn('pa.id') . ' = ' . $db->qn('pav.product_attribute_id') . ' AND pa.state = 1'
			)
			->order('pa.ordering, pav.ordering, pav.product_attribute_id')
			->where('(' . implode(' OR ', $productItems) . ')');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')))
		);

		$productItemsData = $db->setQuery($productItemsQuery)->loadAssocList();

		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		if (empty($productItemsData))
		{
			return array();
		}

		return $productItemsData;
	}

	/**
	 * Method to refresh the cart item data with database data
	 *
	 * @param   array  $dataItems  of cart items
	 * @param   array  $dbItems    of database records
	 *
	 * @return array
	 */
	protected static function prepareDataItems($dataItems, $dbItems)
	{
		if (empty($dataItems))
		{
			return array();
		}

		$itemsInMainOrder = 0;

		foreach ($dataItems as &$cartItem)
		{
			if (empty($cartItem))
			{
				unset($cartItem);

				continue;
			}

			$key = $cartItem['productId'] . '_' . $cartItem['productItem'];

			if (!array_key_exists('params', $cartItem))
			{
				$cartItem['params'] = new Registry;
			}

			if (!($cartItem['params'] instanceof Registry))
			{
				$cartItem['params'] = new Registry($cartItem['params']);
			}

			if ($cartItem['params']->get('delayed_order', 0) == 0)
			{
				$itemsInMainOrder++;
			}

			if (array_key_exists($key, $dbItems) && is_array($dbItems[$key]))
			{
				$cartItem = array_replace($cartItem, $dbItems[$key]);
				continue;
			}

			unset($cartItem);
		}

		// If all items in postponed order, then move them all to the main order
		if (!empty($dataItems) && $itemsInMainOrder == 0)
		{
			foreach ($dataItems as &$cartItem)
			{
				$cartItem['params']->set('delayed_order', 0);
			}
		}

		return $dataItems;
	}

	/**
	 * Load offers from database
	 *
	 * @param   array    $cartOffers    Array cart offers
	 * @param   boolean  $ignoreStatus  Ignore availability-status of offers
	 *
	 * @return  array
	 * @throws  Exception
	 */
	public static function loadOffers($cartOffers, $ignoreStatus = false)
	{
		if (!count($cartOffers))
		{
			return array();
		}

		static $offers = array();
		$displaySKU    = (boolean) Factory::getApplication()->getUserState('shop.cart.display_sku', true);
		$db            = Factory::getDbo();
		$now           = Date::getInstance()->toSql();
		$idOffers      = array();

		foreach ($cartOffers as $cartOffer)
		{
			// Select just non cached items
			if (!array_key_exists($cartOffer['id'], $offers))
			{
				$idOffer          = $cartOffer['id'];
				$idOffers[]       = $idOffer;
				$offers[$idOffer] = false;
			}
		}

		if (empty($idOffers))
		{
			return self::rebuildOfferResults($cartOffers, $offers);
		}

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_offer', 'off'))
			->where('off.id IN (' . implode(',', $idOffers) . ')');

		if (!$ignoreStatus)
		{
			$query->where('off.status = ' . $db->q('accepted'))
				->where('off.state = 1')
				->where(
					'(off.expiration_date >= STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s')
					. ') OR off.expiration_date = ' . $db->q($db->getNullDate()) . ' OR off.expiration_date IS NULL)'
				);
		}

		$listOffers = $db->setQuery($query)->loadAssocList('id');

		if (empty($listOffers))
		{
			return self::rebuildOfferResults($cartOffers, $offers);
		}

		$offers = array_replace($offers, $listOffers);

		$subQuery = $db->getQuery(true)
			->select('c2.id')
			->from($db->qn('#__redshopb_category', 'c2'))
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pc2') . ' ON c2.id = pc2.category_id')
			->where('p.id = pc2.product_id')
			->where('((p.category_id IS NOT NULL AND p.category_id != pc2.category_id) OR p.category_id IS NULL)')
			->where('c2.state = 1')
			->order('c2.id ASC');

		$query->clear()
			->select('(' . $subQuery . ' LIMIT 0, 1) AS category_id_xref')
			->select('c3.id AS category_id')
			->from($db->qn('#__redshopb_offer_item_xref', 'oix'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = oix.product_id')
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pc3') . ' ON pc3.product_id = p.id AND p.category_id = pc3.category_id')
			->leftJoin($db->qn('#__redshopb_category', 'c3') . ' ON pc3.category_id = c3.id AND c3.state = 1')
			->where('oix.offer_id IN (' . implode(',', array_keys($listOffers)) . ')');

		$nameSelect = ($displaySKU) ? 'CONCAT(p.sku, " ", p.name) AS product_name' : $db->qn('p.name', 'product_name');
		$query->select($nameSelect);

		// Get Product Info
		$listOfferProducts = self::loadListOfferProducts($db, $query);

		foreach ($listOfferProducts as $listOfferItem)
		{
			$offerId = $listOfferItem['offer_id'];

			if (!isset($offers[$offerId]['items']))
			{
				$offers[$offerId]['items'] = array();
			}

			if (!$listOfferItem['category_id'])
			{
				$listOfferItem['category_id'] = $listOfferItem['category_id_xref'];
			}

			unset($listOfferItem['category_id_xref']);

			$taxes = RedshopbHelperTax::getProductsTaxRates(array($listOfferItem['product_id']));

			if (!is_array($taxes[$listOfferItem['product_id']]))
			{
				$taxes[$listOfferItem['product_id']] = array($taxes[$listOfferItem['product_id']]);
			}

			$listOfferItem['total_tax'] = 0;
			$listOfferItem['taxes']     = self::getTaxObjects($taxes[$listOfferItem['product_id']], $listOfferItem);

			foreach ($listOfferItem['taxes'] as $tax)
			{
				$listOfferItem['total_tax'] += $tax['tax'];
			}

			$listOfferItem['total_with_tax'] = $listOfferItem['total'] + $listOfferItem['total_tax'];

			$offers[$offerId]['items'][] = $listOfferItem;
		}

		// Get Product Item Info
		$productItemsData = self::loadListOfferProductItems($db, $query);

		$productItems = array();

		foreach ($productItemsData as $product)
		{
			$key = $product['productId'] . '_' . (int) $product['productItem'];

			if (isset($productItems[$key]))
			{
				$productItems[$key]['sku']          .= '-' . $product['sku'];
				$productItems[$key]['string_value'] .= '-' . $product['string_value'];

				continue;
			}

			$productItems[$key] = $product;

			if (!$product['category_id'])
			{
				$productItems[$key]['category_id'] = $product['category_id_xref'];
			}

			unset($productItems[$key]['category_id_xref']);
		}

		foreach ($productItems as $productItem)
		{
			$key                             = $productItem['productId'] . '_' . (int) $productItem['productItem'];
			$offerId                         = $productItem['offer_id'];
			$offers[$offerId]['items'][$key] = $productItem;
		}

		foreach ($offers as $offer)
		{
			$offerId = $offer['id'];

			switch ($offer['customer_type'])
			{
				case 'company':
					$offers[$offerId]['customer_id'] = $offer['company_id'];
					break;
				case 'department':
					$offers[$offerId]['customer_id'] = $offer['department_id'];
					break;
				case 'employee':
					$offers[$offerId]['customer_id'] = $offer['user_id'];
					break;
			}

			$offers[$offerId]['currency'] = RedshopbHelperPrices::getCurrency(
				$offers[$offerId]['customer_id'], $offer['customer_type'], $offer['collection_id']
			);
		}

		return self::rebuildOfferResults($cartOffers, $offers);
	}

	/**
	 * Method to rebuild the offer results with only valid offers
	 *
	 * @param   array  $cartOffers  from the cart
	 * @param   array  $offers      Offers from the database
	 *
	 * @return array
	 */
	protected static function rebuildOfferResults($cartOffers, $offers)
	{
		$result = array();

		foreach ($cartOffers as $offer)
		{
			$key = $offer['id'];

			if ($offers[$key])
			{
				$result[$key] = $offers[$key];
			}
		}

		return $result;
	}

	/**
	 * Method to load offer product data
	 *
	 * @param   JDatabaseDriver  $db     database object
	 * @param   JDatabaseQuery   $query  base query
	 *
	 * @return array
	 */
	protected static function loadListOfferProducts($db, $query)
	{
		// Get Product Info
		$productQuery = clone $query;
		$productQuery->select(
			array(
				'oix.*',
				'p.sku',
				$db->q('') . ' AS string_value',
				'0 AS productItem',
				$db->qn('p.id', 'productId'),
				'0 AS ordering',
				$db->q('') . ' AS name',
				'0 AS type_id',
				'p.stock_upper_level',
				'p.stock_lower_level'
			)
		)
			->where('oix.product_item_id IS NULL');

		$listOfferProducts = $db->setQuery($productQuery)->loadAssocList();

		if (empty($listOfferProducts))
		{
			return array();
		}

		return $listOfferProducts;
	}

	/**
	 * Method to load offer product item data
	 *
	 * @param   JDatabaseDriver  $db     database object
	 * @param   JDatabaseQuery   $query  base query
	 *
	 * @return array
	 */
	protected static function loadListOfferProductItems($db, $query)
	{
		// Get Product Item Info
		$productItemQuery = clone $query;
		$productItemQuery->select(
			array(
				'oix.*',
				'pi.sku',
				'pav.string_value',
				'pi.id AS productItem',
				$db->qn('p.id', 'productId'),
				'pa.ordering',
				'pa.name',
				'pa.type_id',
				'pi.stock_upper_level',
				'pi.stock_lower_level'
			)
		)
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = oix.product_item_id')
			->leftJoin(
				$db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON ' .
				$db->qn('pivx.product_item_id') . ' = ' . $db->qn('pi.id')
			)
			->leftJoin(
				$db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON ' .
				$db->qn('pivx.product_attribute_value_id') . ' = ' . $db->qn('pav.id') . ' AND pav.state = 1'
			)
			->leftJoin(
				$db->qn('#__redshopb_product_attribute', 'pa') . ' ON ' .
				$db->qn('pa.id') . ' = ' . $db->qn('pav.product_attribute_id') . ' AND pa.state = 1'
			)
			->order('pa.ordering, pav.ordering, pav.product_attribute_id')
			->where('oix.product_item_id IS NOT NULL');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')))
		);

		$productItemsData = $db->setQuery($productItemQuery)->loadAssocList();

		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		if (empty($productItemsData))
		{
			return array();
		}

		return $productItemsData;
	}

	/**
	 * Method to set or update an items price based on quantity
	 *
	 * @param   array   $item          Cart Item.
	 * @param   int     $quantity      Number of items.
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return array
	 */
	public static function updateItemPrice($item, $quantity, $customerId, $customerType)
	{
		$collectionIds = null;

		if (!is_null($item['collectionId']))
		{
			$collectionIds = array($item['collectionId']);
		}

		// Make sure we update the price if the quantity has changed
		if ($item['productItem'])
		{
			$prices = RedshopbHelperPrices::getProductItemPrice(
				$item['productItem'], $customerId, $customerType, $item['currency'], $collectionIds, null, null, $quantity, false, true
			);
		}
		else
		{
			$prices = RedshopbHelperPrices::getProductPrice(
				$item['productId'], $customerId, $customerType, $item['currency'], $collectionIds, null, null, $quantity, false, true
			);
		}

		return self::addPriceParams($item, $prices ?: new stdClass);
	}

	/**
	 * Adds the additional price parameters needed for calculations e.g. discount
	 *
	 * @param   array      $item     Cart item
	 * @param   stdClass   $prices   Price types to add
	 *
	 * @return   array
	 */
	protected static function addPriceParams($item, $prices)
	{
		$item['price']                  = empty($prices->price) ? 0.0 : $prices->price;
		$item['price_without_discount'] = empty($prices->price) ? 0.0 : $prices->price;
		$item['price_multiple']         = empty($prices->is_multiple) ? 0 : $prices->is_multiple;
		$item['price_multiple_of']      = empty($prices->quantity_min) ? 0 : $prices->quantity_min;

		if (isset($prices->price_without_discount))
		{
			$item['price_without_discount'] = $prices->price_without_discount;
		}

		if (!empty($prices->discount))
		{
			$item['discount'] = $prices->discount;
		}

		if (!empty($prices->discount_type))
		{
			$item['discount_type'] = $prices->discount_type;
		}

		return $item;
	}

	/**
	 * Accessories Total Price
	 *
	 * @param   array  $item  Cart item
	 *
	 * @return integer
	 */
	public static function accessoriesTotalPrice($item)
	{
		$accessoriesPrices = 0;

		if (!isset($item['accessories']))
		{
			return $accessoriesPrices;
		}

		foreach ($item['accessories'] as $accessory)
		{
			$accessoriesPrices += $accessory['price'];
		}

		return $accessoriesPrices;
	}

	/**
	 * Get Key Accessories
	 *
	 * @param   array  $item  Cart item
	 *
	 * @return null|string
	 */
	public static function getKeyAccessories($item)
	{
		if (!isset($item['accessories']))
		{
			return null;
		}

		$key = array();

		foreach ($item['accessories'] as $accessory)
		{
			$key[] = $accessory['accessory_id'];

			if (array_key_exists('quantity', $accessory))
			{
				$key[] = 'q' . $accessory['quantity'];
			}
		}

		return implode('a', $key);
	}

	/**
	 * Add item to the Cart
	 *
	 * @param   integer           $productId         Product Id
	 * @param   integer           $productItem       Product Item ID
	 * @param   string            $accessory         Selected accessory id
	 * @param   integer|float     $quantity          Product item quantity
	 * @param   float             $price             Item price.
	 * @param   string            $currency          Product currency
	 * @param   integer           $customerId        Customer id.
	 * @param   string            $customerType      Customer type.
	 * @param   string            $collectionId      Include collections ids
	 * @param   integer           $dropDownSelected  Id selected attribute value
	 * @param   integer           $stockroomId       Id of stockroom
	 *
	 * @internal param array $item Cart Item
	 *
	 * @return  array  Cart items
	 * @throws  Exception
	 */
	public static function addToCartById(
		$productId, $productItem, $accessory = null, $quantity = 0,
		$price = 0.0, $currency = 'DKK', $customerId = 0, $customerType = '',
		$collectionId = null, $dropDownSelected = 0, $stockroomId = 0
	)
	{
		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		if (!$customerId || !$customerType)
		{
			return array('items' => array(), 'message' => Text::_('PLG_REDSHOPB_NEED_IMPERSONATE'), 'messageType' => 'alert-error');
		}

		if (RedshopbHelperUser::isFromMainCompany($customerId, $customerType))
		{
			return array(
				'items'       => array(),
				'message'     => Text::_('COM_REDSHOPB_ADD_TO_CART_ERROR_MAIN_COMPANY_USER'),
				'messageType' => 'alert-error'
			);
		}

		$msg   = Text::_('COM_REDSHOPB_SHOP_WAS_SUCCESSFULLY_ADDED_TO_THE_CART');
		$cart  = self::getCart($customerId, $customerType);
		$items = $cart->get('items', array());

		/** @var RedshopbModelProduct $modelProduct */
		$modelProduct    = RModelAdmin::getInstance('Product', 'RedshopbModel');
		$userCompany     = RedshopbHelperUser::getUserCompany();
		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		$stockroom       = null;

		if (RedshopbHelperACL::isSuperAdmin() || !$userCompany)
		{
			$userCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($customerId, $customerType);
		}

		// Check if item is available or not.
		$item = self::loadItem($productId, $productItem);

		if (!$item)
		{
			$msg = Text::_('COM_REDSHOPB_ADD_TO_CART_ERROR_NOT_FOUND');

			return array('items' => $items, 'message' => $msg, 'messageType' => 'alert-error');
		}

		// Product wallet reference
		$item['wallet'] = (!empty($userCompany->walletProductId) && $productId == $userCompany->walletProductId) ? true : false;

		// Flag use for disable find stockroom id, when it is not set. Flag can set in event onRedshopbAddToCartValidation
		$findStockroomId = true;
		$dispatcher      = RFactory::getDispatcher();
		$return          = null;
		$results         = $dispatcher->trigger('onRedshopbAddToCartValidation', array(compact(array_keys(get_defined_vars())), &$return));

		if (is_array($return))
		{
			return $return;
		}

		// Re-define values if needed
		foreach ($results as $result)
		{
			if (is_array($result))
			{
				extract($result);
			}
		}

		if ($userCompany && $userCompany->stockroom_verification)
		{
			// If there are no stockroom provided.
			if ($findStockroomId && !$stockroomId)
			{
				// Get available stockroom for product if available
				$stockroomId = (int) RedshopbHelperStockroom::getProductAvailableStockroom($productId, $productItem, $quantity);
			}

			// If there are have exist stockroom created for this product/product item.
			if ($stockroomId)
			{
				// Check stockroom amount if this adaptable for quantity item
				if ($productItem)
				{
					$stockroom = RedshopbHelperStockroom::getProductItemStockroomData($productItem, array($stockroomId));
					$stockroom = $stockroom[$productItem . '_' . $stockroomId];
				}
				else
				{
					$stockroom = RedshopbHelperStockroom::getProductStockroomData($productId, $stockroomId);
				}
			}

			if (!$stockroom && RedshopbHelperStockroom::hasAvailableStockroom($productId, $productItem))
			{
				return array('items' => $items, 'message' => Text::_('COM_REDSHOPB_ADD_TO_CART_ERROR_STOCK_AMOUNT'), 'messageType' => 'alert-error');
			}
		}

		if ($userCompany && $userCompany->type != 'end_customer' && $customerCompany && $customerCompany->type == 'end_customer')
		{
			$currency = 0;
			$price    = 0.0;

			if (!empty($collectionId))
			{
				$currency = RedshopbHelperPrices::getCurrency($customerId, $customerType, (int) $collectionId);
			}
		}

		if (!is_numeric($currency))
		{
			$currency = RedshopbEntityCurrency::loadByAlpha3($currency)->get(
				'id', RedshopbHelperPrices::getCurrency($customerId, $customerType)
			);
		}
		elseif ((int) $currency == 0)
		{
			$currency = RedshopbHelperPrices::getCurrency($customerId, $customerType);
		}

		// Product Accessories
		$accessories = $modelProduct->getAccessoriesIds(
			$productId,
			$accessory,
			true,
			0,
			array($collectionId),
			$dropDownSelected,
			$customerId,
			$customerType,
			$currency
		);

		if (!empty($item['unit_measure_id']))
		{
			$item['unit_measure_text'] = RedshopbEntityUnit_Measure::getInstance($item['unit_measure_id'])
				->get('name', Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS'));
		}
		else
		{
			$item['unit_measure_text'] = Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS');
		}

		$item['accessories']            = (!empty($accessories)) ? $accessories : array();
		$item['keyAccessories']         = self::getKeyAccessories($item);
		$item['price_without_discount'] = $price;
		$item['discount']               = 0;
		$item['discount_type']          = '';
		$item['collectionId']           = (is_null($collectionId) ? 0 : $collectionId);
		$item['price']                  = 0;
		$item['currency']               = $currency;
		$item['stockroom']              = $stockroom;
		$item['stockroomId']            = $stockroomId;

		$hash           = $cart->generateItemHash($item);
		$statusQuantity = $quantity;

		if ($cart->itemExists($hash))
		{
			$statusQuantity += $cart->getItemQuantity($hash);
		}

		// We need to check again in case the item is not already been added to the cart
		$productEntity  = RedshopbEntityProduct::load($productId);
		$quantityStatus = $productEntity->checkQuantities($statusQuantity);

		if (!$quantityStatus['isOK'])
		{
			return array('items' => array(), 'message' => $quantityStatus['msg'], 'messageType' => 'alert-error');
		}

		$msg = Text::_('COM_REDSHOPB_SHOP_WAS_SUCCESSFULLY_ADDED_TO_THE_CART');

		$items = self::addToCart($item, $quantity, $customerId, $customerType);
		$dispatcher->trigger('onAfterRedshopbAddToCart', array(&$items, $item, $quantity, $customerId, $customerType, &$msg));

		return array('items' => $items, 'message' => $msg, 'messageType' => 'alert-info');
	}

	/**
	 * Add offer in cart
	 *
	 * @param   int      $offerId       Offer id
	 * @param   boolean  $ignoreStatus  Ignore availability-status of offer
	 *
	 * @return  boolean
	 */
	public static function addToCartOffer($offerId, $ignoreStatus = false)
	{
		$offer = self::loadOffer($offerId, $ignoreStatus);

		if (!is_array($offer))
		{
			return false;
		}

		$cart = self::getCart($offer['customer_id'], $offer['customer_type']);

		$cart->addOffer($offerId, $offer);

		self::setCart($cart, $offer['customer_id'], $offer['customer_type']);

		return true;
	}

	/**
	 * Load item from database
	 *
	 * @param   int  $productId    Product Id
	 * @param   int  $productItem  Product Item ID
	 *
	 * @return  mixed  Cart item
	 */
	public static function loadItem($productId, $productItem)
	{
		$item = self::loadItems(
			array(0 => array('productId' => $productId, 'productItem' => $productItem))
		);

		if (empty($item))
		{
			return false;
		}

		$key = $productId . '_' . (int) $productItem;

		return $item[$key];
	}

	/**
	 * Load offer from database
	 *
	 * @param   integer  $offerId       Offer id
	 * @param   boolean  $ignoreStatus  Ignore availability-status of offer
	 *
	 * @return  array|false
	 * @throws  Exception
	 */
	public static function loadOffer($offerId, $ignoreStatus = false)
	{
		$offer = self::loadOffers(
			array(array('id' => $offerId)),
			$ignoreStatus
		);

		if (!empty($offer) && count($offer) > 0)
		{
			return $offer[$offerId];
		}

		return false;
	}

	/**
	 * Remove from Cart
	 *
	 * @param   int     $productId       Product Id
	 * @param   int     $productItem     Product Item Id
	 * @param   int     $customerId      Customer id.
	 * @param   string  $customerType    Customer type.
	 * @param   int     $collectionId    Collection id
	 * @param   string  $keyAccessories  Key Accessories
	 * @param   int     $stockroomId     Stockroom id
	 * @param   int     $quantity        Quantity to match multiple cart items.
	 *
	 * @return  array  Cart items
	 *
	 * @deprecated 1.13.2 Use {@see removeFromCartByHash}
	 */
	public static function removeFromCart(
		$productId, $productItem, $customerId = 0,
		$customerType = '', $collectionId = 0,
		$keyAccessories = '', $stockroomId = 0, $quantity = 0
	)
	{
		$cart  = self::getCart($customerId, $customerType);
		$items = $cart->get('items', array());

		RFactory::getDispatcher()->trigger(
			'onBeforeRedshopbRemoveFromCart',
			array(&$items, $productId, $productItem, $customerId, $customerType, $collectionId, $keyAccessories, $stockroomId)
		);

		$hash = Factory::getApplication()->input->getString('cartItemHash', '');

		return self::removeFromCartByHash($hash, $customerId, $customerType)->get('items', array());
	}

	/**
	 * Removes an item from the cart
	 *
	 * @param   string    $hash           Unique item hash for identification
	 * @param   integer   $customerId     Needed to get the right cart object
	 * @param   string    $customerType   Needed to get the right cart object
	 *
	 * @return   RedshopbHelperCart_Object
	 */
	public static function removeFromCartByHash($hash, $customerId, $customerType)
	{
		$cart     = self::getCart($customerId, $customerType);
		$item     = $cart->getItem($hash);
		$currency = $item['currency'];

		RFactory::getDispatcher()->trigger(
			'onBeforeRedshopbRemoveFromCartByHash',
			array(&$hash, $customerId, $customerType, &$cart, &$item, &$currency)
		);

		$cart->removeItem($hash);

		if (isset($currency))
		{
			$cart = self::checkTotal($cart, $customerId, $customerType, $currency);
		}

		self::setCart($cart, $customerId, $customerType);

		return self::getCart($customerId, $customerType);
	}

	/**
	 * Remove Offer from Cart
	 *
	 * @param   int     $offerId       Offer id
	 * @param   int     $customerId    Customer id
	 * @param   string  $customerType  Customer type
	 *
	 * @return  RedshopbHelperCart_Object  Cart object
	 */
	public static function removeOfferFromCart($offerId, $customerId = 0, $customerType = '')
	{
		$cart   = self::getCart($customerId, $customerType);
		$offers = $cart->get('offers', array());

		if ($cart->offerExists($offerId))
		{
			$currency = $offers[$offerId]['currency'];
			$cart->removeOffer($offerId);
		}

		if (isset($currency))
		{
			$cart = self::checkTotal($cart, $customerId, $customerType, $currency);
		}

		self::setCart($cart, $customerId, $customerType);

		return self::getCart($customerId, $customerType);
	}

	/**
	 * Set Item Quantity.
	 *
	 * @param   int     $itemId          Product Id.
	 * @param   int     $productItemId   Product Item Id.
	 * @param   int     $quantity        Quantity.
	 * @param   int     $customerId      Customer id.
	 * @param   string  $customerType    Customer type.
	 * @param   int     $collectionId    Collection id
	 * @param   string  $keyAccessories  Key Accessories
	 * @param   int     $stockroomId     StockroomId
	 *
	 * @return  array  Cart items
	 *
	 * @deprecated   1.13.2  Use RedshopbHelperCart::setItemQuantityByHash instead.
	 */
	public static function setItemQuantity(
		$itemId, $productItemId, $quantity = 0,
		$customerId = 0, $customerType = '',
		$collectionId = 0, $keyAccessories = '', $stockroomId = 0
	)
	{
		$hash = Factory::getApplication()->input->getString('cartItemHash', '');

		return self::setItemQuantityByHash($hash, $customerId, $customerType, $quantity);
	}

	/**
	 * Set Item Quantity using cart item id.
	 *
	 * @param   string            $hash          Unique item hash for identification
	 * @param   integer           $customerId    Customer id.
	 * @param   string            $customerType  Customer type.
	 * @param   integer|float     $quantity      Quantity to match multiple cart items.
	 *
	 * @return  array   Cart items
	 *
	 * @since   1.13.2
	 */
	public static function setItemQuantityByHash($hash, $customerId = 0, $customerType = '', $quantity = 0)
	{
		$cart           = self::getCart($customerId, $customerType);
		$items          = $cart->get('items', array());
		$cartItem       = $cart->getItem($hash);
		$userCompany    = RedshopbHelperUser::getUserCompany();
		$productEntity  = RedshopbEntityProduct::load($cartItem['productId']);
		$quantityStatus = $productEntity->checkQuantities($quantity);

		if (!$quantityStatus['isOK'])
		{
			return array('items' => $items, 'message' => $quantityStatus['msg'], 'messageType' => 'alert-error');
		}

		if (RedshopbHelperACL::isSuperAdmin() || !$userCompany)
		{
			$userCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($customerId, $customerType);
		}

		if (!$cart->itemExists($hash))
		{
			return array('items' => $items, 'message' => '');
		}

		if (!empty($cartItem['stockroomId']) && $userCompany && $userCompany->stockroom_verification
			&& !RedshopbHelperStockroom::isProductInStock($cartItem['productId'], $cartItem['productItem'], $cartItem['stockroomId'], $quantity))
		{
			return array('items' => $items, 'message' => Text::_('COM_REDSHOPB_ADD_TO_CART_ERROR_STOCK_AMOUNT'), 'messageType' => 'alert-error');
		}

		$cart->removeItem($hash);

		self::setCart($cart, $customerId, $customerType);

		RFactory::getDispatcher()->trigger('onAECSetItemQuantityByHashBeforeAddToCart', array(&$cartItem));

		$data = self::addToCartById(
			$cartItem['productId'],
			$cartItem['productItem'],
			$cartItem['keyAccessories'],
			$quantity,
			$cartItem['price'],
			$cartItem['currency'],
			$customerId,
			$customerType,
			$cartItem['collectionId'],
			0,
			$cartItem['stockroomId']
		);

		return array('items' => $data['items'], 'message' => Text::sprintf('COM_REDSHOPB_STOCK_UPDATED', $cartItem['product_name']));
	}

	/**
	 * Check cart item is match with current items.
	 *
	 * @param   array  $cartItem  Cart item
	 * @param   array  $item      Current item
	 *
	 * @return boolean
	 *
	 * @deprecated 1.13.2 Validate using {@see RedshopbHelperCart_Object::itemExists()}
	 */
	public static function cartItemMatch($cartItem, $item)
	{
		RFactory::getDispatcher()->trigger('onRedshopbBeforeCartItemMatch', array(&$cartItem, &$item));

		foreach (self::cartFieldsForCheck() as $fieldName)
		{
			if (!array_key_exists($fieldName, $cartItem)
				|| !array_key_exists($fieldName, $item)
				|| $cartItem[$fieldName] != $item[$fieldName])
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Return cart item fields for check
	 *
	 * @param   bool  $getDBNames  Return DB values instead cart session
	 *
	 * @return array
	 */
	public static function cartFieldsForCheck($getDBNames = false)
	{
		$cartFieldsForCheck = array(
			'product_id'     => 'productId',
			'product_item'   => 'productItem',
			'collection_id'  => 'collectionId',
			'keyAccessories' => 'keyAccessories',
			'stockroom_id'   => 'stockroomId'
		);

		if ($getDBNames)
		{
			$cartFieldsForCheck = array_keys($cartFieldsForCheck);
		}
		else
		{
			$cartFieldsForCheck = array_values($cartFieldsForCheck);
		}

		RFactory::getDispatcher()->trigger('onRedshopbAfterCartFieldsForCheck', array(&$cartFieldsForCheck, $getDBNames));

		return $cartFieldsForCheck;
	}

	/**
	 * Returns Cart Item Quantity Sum
	 *
	 * @param   integer   $customerId     Needed to get the correct cart
	 * @param   string    $customerType   Needed to get the correct cart
	 *
	 * @return   integer  Sum of quantities of items
	 */
	public static function getCartItemQuantities($customerId = 0, $customerType = '')
	{
		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$cart         = self::getCart($customerId, $customerType);
		$items        = $cart->get('items', array());
		$quantity     = 0;
		$countingType = RedshopbEntityConfig::getInstance()->get('count_cart_items', 'quantity');

		if (!empty($items))
		{
			foreach ($items as $cartItem)
			{
				if ($countingType == 'quantity')
				{
					if (isset($cartItem['decimal']) && $cartItem['decimal'] > 0)
					{
						$quantity++;
					}
					else
					{
						$quantity += $cartItem['quantity'];
					}
				}
				else
				{
					$quantity++;
				}
			}
		}

		$offers = $cart->get('offers', array());

		if (!empty($offers))
		{
			foreach ($offers as $offer)
			{
				foreach ($offer['items'] as $item)
				{
					if ($countingType == 'quantity')
					{
						$quantity += $item['quantity'];
					}
					else
					{
						$quantity++;
					}
				}
			}
		}

		return $quantity;
	}

	/**
	 * Set the cart in Session storage
	 *
	 * @param   array   $data          Cart Data.
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 * @param   bool    $setFullData   Set full cart data or just normal products(avoid offers)
	 *
	 * @internal param array $cart Class for handling cart
	 *
	 * @return  void
	 *
	 * @deprecated 1.13.2 Use {@see setCart} instead
	 */
	public static function setCartToSession($data, $customerId = 0, $customerType = '', $setFullData = false)
	{
		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$cart = new RedshopbHelperCart_Object;

		if (true === $setFullData)
		{
			$cart->set('items', $data['items']);
			$cart->set('offers', $data['offers']);
		}
		else
		{
			$cart->set('items', $data);
		}

		self::setCart($cart, $customerId, $customerType);
	}

	/**
	 * Clear the cart from Session storage
	 *
	 * @param   bool  $clearAllCustomers  Clear cart for all customers.
	 *
	 * @return  Session  User session
	 */
	public static function clearCartFromSession($clearAllCustomers = false)
	{
		$session = Factory::getSession();
		$app     = Factory::getApplication();

		if (!$clearAllCustomers)
		{
			$customerId   = $app->getUserState('shop.customer_id',  0);
			$customerType = $app->getUserState('shop.customer_type', '');
			$session->clear('cart.' . $customerType . '.' . $customerId, 'redshopb');
			$session->clear('saved_cart.' . $customerType . '.' . $customerId, 'redshopb');
			$customers = $session->get('customers', array(), 'redshopb');
			unset($customers[$customerType . '.' . $customerId]);
			$session->set('customers', $customers, 'redshopb');

			return $session;
		}

		if (RedshopbEntityConfig::getInstance()->getInt('save_cart_for_logged_in', '0') == '1'
			&& RedshopbHelperACL::isSuperAdmin() === false && !self::$loadingCartFromDatabase)
		{
			$cartTable = RedshopbTable::getAdminInstance('Cart');
			$user      = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

			$row = array(
				'user_id' => $user->id,
				'company_id' => $user->company,
				'name' => $user->id . '_' . $user->username,
				'user_cart' => '1'
			);

			try
			{
				if ($cartTable->load($row))
				{
					$cartTable->delete($cartTable->get('id'));
				}
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		$customers = $session->get('customers', array(), 'redshopb');

		foreach ($customers as $customer)
		{
			$session->clear('cart.' . $customer, 'redshopb');
			$session->clear('saved_cart.' . $customer, 'redshopb');
		}

		$session->clear('customers', 'redshopb');

		return $session;
	}

	/**
	 * Check cart total for given price for applying fees.
	 *
	 * @param   RedshopbHelperCart_Object   $cart          Current cart object
	 * @param   integer                     $customerId    Customer id.
	 * @param   string                      $customerType  Customer type.
	 * @param   integer                     $currency      Currency for checking total.
	 *
	 * @return RedshopbHelperCart_Object
	 */
	public static function checkTotal($cart, $customerId, $customerType, $currency)
	{
		$company       = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		$userCompany   = RedshopbHelperUser::getUserCompany();
		$customerCType = ($company) ? $company->type : '';
		$totalPrice    = 0;
		$feeKey        = -1;
		$freightKey    = -1;
		$fee           = null;
		$freight       = null;

		// Check fee
		if (($company && (int) $company->calculate_fee)
			|| (((isset($userCompany) && $userCompany->type == 'customer' && $userCompany->calculate_fee) || RedshopbHelperACL::isSuperAdmin())
			&& $customerCType == 'end_customer'
			&& $customerType != 'employee'))
		{
			$fee = RedshopbHelperShop::getAdditionalCharges($currency, 'fee');
		}

		// Check shipping charges (freight)
		if ($customerCType == 'end_customer')
		{
			$freight = RedshopbHelperShop::getAdditionalCharges($currency, 'freight');
		}

		$items = $cart->get('items', array());

		// Calculating total price
		foreach ($items as $key => $cartItem)
		{
			if ($cartItem['currency'] != $currency)
			{
				continue;
			}

			if (!is_null($fee) && ($cartItem['productItem'] == $fee->itemId || $cartItem['productId'] == $fee->id))
			{
				$feeKey = $key;
				continue;
			}

			if (!is_null($freight) && ($cartItem['productItem'] == $freight->itemId || $cartItem['productId'] == $freight->id))
			{
				$freightKey = $key;
				continue;
			}

			$totalPrice += $cartItem['price'] * $cartItem['quantity'];
		}

		$offers = $cart->get('offers', array());

		foreach ($offers as $offer)
		{
			$totalPrice += $offer['total'];
		}

		if (!is_null($fee))
		{
			if ($feeKey >= 0 && ($totalPrice >= $fee->limit || $totalPrice <= 0))
			{
				$cart->removeItem($feeKey);
			}
			elseif ($totalPrice < $fee->limit && $totalPrice > 0)
			{
				if ($feeKey >= 0)
				{
					$cart->removeItem($feeKey);
				}

				$item                           = array();
				$item['price']                  = $fee->price;
				$item['subtotal']               = $fee->price;
				$item['currency']               = $fee->currency_id;
				$item['product_name']           = $fee->sku . ' ' . $fee->name;
				$item['productId']              = $fee->id;
				$item['productItem']            = $fee->itemId;
				$item['string_value']           = '';
				$item['sku']                    = '';
				$item['name']                   = '';
				$item['type']                   = '';
				$item['quantity']               = 1;
				$item['price_without_discount'] = $fee->price;
				$item['discount']               = 0;

				$cart->addItem($feeKey, $item);
			}
		}

		if (!is_null($freight))
		{
			if ($freightKey >= 0 && ($totalPrice >= $freight->limit || $totalPrice <= 0))
			{
				$cart->removeItem($freightKey);
			}
			elseif ($totalPrice < $freight->limit && $totalPrice > 0)
			{
				if ($freightKey >= 0)
				{
					$cart->removeItem($freightKey);
				}

				$item                           = array();
				$item['price']                  = $freight->price;
				$item['subtotal']               = $freight->price;
				$item['currency']               = $freight->currency_id;
				$item['product_name']           = $freight->sku . ' ' . $freight->name;
				$item['productId']              = $freight->id;
				$item['productItem']            = $freight->itemId;
				$item['string_value']           = '';
				$item['sku']                    = '';
				$item['name']                   = '';
				$item['type']                   = '';
				$item['quantity']               = 1;
				$item['price_without_discount'] = $freight->price;
				$item['discount']               = 0;

				$cart->addItem($freightKey, $item);
			}
		}

		return $cart;
	}

	/**
	 * Change the parameters of an item in the cart
	 *
	 * @param   integer  $customerId    Customer id
	 * @param   string   $customerType  Customer type
	 * @param   string   $hash          Unique item hash for identification
	 * @param   array    $params        Params for current item
	 *
	 * @return boolean
	 *
	 * @since 1.12.82  Originally implemented
	 *
	 * @since 1.13.2  Method signature has changed; $item<integer> is now $hash<string>
	 */
	public static function changeAdditionalParameters($customerId, $customerType, $hash, $params)
	{
		if (!is_array($params) || empty($params))
		{
			return false;
		}

		$cart = self::getCart($customerId, $customerType);

		if (!$cart->itemExists($hash))
		{
			return false;
		}

		$cart->changeItemParams($hash, $params);

		self::setCart($cart, $customerId, $customerType);

		return true;
	}

	/**
	 * Gets the cart object from a session or creates it if it's not found
	 *
	 * @param   integer   $customerId     Customer id
	 * @param   string    $customerType   Customer type
	 *
	 * @return   RedshopbHelperCart_Object
	 *
	 * @since 1.13.2
	 */
	public static function getCart($customerId , $customerType)
	{
		$session = Factory::getSession();
		$cart    = $session->get('cart.' . $customerType . '.' . $customerId, null, 'redshopb');

		if (null === $cart || !is_string($cart))
		{
			$cart = new RedshopbHelperCart_Object;
		}
		else
		{
			$cart = unserialize(base64_decode($cart));
		}

		self::processCart($cart);

		return $cart;
	}

	/**
	 * Processes the products in the cart
	 *
	 * Checks the items and offers against the database, sorts items and filters offers
	 *
	 * @param   RedshopbHelperCart_Object   $cart   Reference to the cart object
	 *
	 * @return   void
	 */
	private static function processCart(&$cart)
	{
		/*
		 * Update cart items
		 */
		if ($cart->exists('items'))
		{
			$dbItems = self::loadItems($cart->get('items', array()));

			if (is_array($dbItems))
			{
				$items = self::prepareDataItems($cart->get('items', array()), $dbItems);

				$sort = function ($ab, $ba)
				{
					return $ab['sku'] <=> $ba['sku'];
				};

				RFactory::getDispatcher()->trigger('onAECProcessCartSortItems', array(&$sort));

				usort($items, $sort);

				$cart->set('items', $items);

				/*
				 * Update cart offers
				 */
				if ($cart->exists('offers'))
				{
					$offers = $cart->get('offers', array());

					$dbOffers = self::loadOffers($offers);

					foreach ($offers as $key => &$offer)
					{
						if (!array_key_exists($offer['id'], $dbOffers))
						{
							unset($offers[$key]);

							continue;
						}

						foreach ($offer['items'] as &$cartItem)
						{
							if (empty($cartItem))
							{
								continue;
							}

							$key = $cartItem['productId'] . '_' . $cartItem['productItem'];

							if (array_key_exists($key, $dbOffers) && is_array($dbItems[$key]))
							{
								$cartItem = array_replace($cartItem, $dbItems[$key]);
								continue;
							}

							unset($cartItem);
						}
					}

					$cart->set('offers', $offers);
				}
			}
		}
	}

	/**
	 * Saves the cart object in a session
	 *
	 * @param   RedshopbHelperCart_Object   $data           The cart object to be saved
	 * @param   integer                     $customerId     Customer id
	 * @param   string                      $customerType   Customer type
	 *
	 * @return void
	 *
	 * @since 1.13.2
	 */
	public static function setCart($data, $customerId, $customerType)
	{
		$session = Factory::getSession();
		$cart    = base64_encode(serialize($data));

		$session->set('cart.' . $customerType . '.' . $customerId, $cart, 'redshopb');

		$customers = $session->get('customers', array(), 'redshopb');

		$items  = $data->get('items', array());
		$offers = $data->get('offers', array());

		if ((empty($items) && !isset($offers))
			|| (count($items) == 0 && count($offers) == 0))
		{
			unset($customers[$customerType . '.' . $customerId]);
		}
		else
		{
			$customers[$customerType . '.' . $customerId] = $customerType . '.' . $customerId;
		}

		$session->set('customers', $customers, 'redshopb');

		if (RedshopbEntityConfig::getInstance()->getInt('save_cart_for_logged_in', '0') == '1'
			&& RedshopbHelperACL::isSuperAdmin() === false && !self::$loadingCartFromDatabase)
		{
			$cartTable = RedshopbTable::getAdminInstance('Cart');
			$user      = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

			$row = array(
				'user_id' => $user->id,
				'company_id' => $user->company,
				'name' => $user->id . '_' . $user->username,
				'user_cart' => '1'
			);

			if (!$cartTable->load($row))
			{
				$cartTable->save($row);
			}

			// If not from an order. Get items from cart session.
			$cartId = $cartTable->get('id');
			$db     = Factory::getDbo();

			// If the cart is exist, then need delete all related items before apply new
			if ($cartId)
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__redshopb_cart_item'))
					->where('cart_id = ' . (int) $cartId);

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					return;
				}
			}

			if (empty($items))
			{
				return;
			}

			// Process save cart for items get from cart session.
			foreach ($items as $cartItem)
			{
				$cartItemTable = RTable::getAdminInstance('Cart_Item', array(), 'com_redshopb');

				$cartItemTable->cart_id             = $cartId;
				$cartItemTable->product_id          = $cartItem['productId'];
				$cartItemTable->product_item_id     = $cartItem['productItem'];
				$cartItemTable->parent_cart_item_id = null;
				$cartItemTable->collection_id       = $cartItem['collectionId'];
				$cartItemTable->quantity            = $cartItem['quantity'];

				if (!$cartItemTable->store())
				{
					return;
				}

				if (empty($cartItem['accessories']))
				{
					continue;
				}

				$parentCartItemId = $cartItemTable->get('id');

				foreach ($cartItem['accessories'] as $accessory)
				{
					$cartItemTable = RTable::getAdminInstance('Cart_Item', array(), 'com_redshopb');

					$cartItemTable->set('cart_id', $cartTable->get('id'));
					$cartItemTable->set('product_id', $accessory['accessory_id']);
					$cartItemTable->set('product_item_id', 0);
					$cartItemTable->set('parent_cart_item_id', $parentCartItemId);
					$cartItemTable->set('collection_id', 0);
					$cartItemTable->set('quantity', $accessory['quantity']);

					if (!$cartItemTable->store())
					{
						return;
					}
				}
			}
		}
	}
}
