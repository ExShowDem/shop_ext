<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Helper class for Shipping calls
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Shipping
 * @since       1.6
 */
class RedshopbShippingHelper
{
	/**
	 * Plugin parameters container
	 * @var array
	 */
	protected static $pluginParams = array();

	/**
	 * Gets Shipping parameters
	 * If owner name config is not found it will use extension config, and if extension config is not found it will use default plugin config
	 *
	 * @param   string  $shippingName   Shipping Name
	 * @param   string  $extensionName  Extension Name
	 * @param   string  $ownerName      Owner Name
	 *
	 * @return  object
	 */
	public static function getShippingParams($shippingName = '', $extensionName = '', $ownerName = '')
	{
		if (isset(self::$pluginParams[$shippingName][$extensionName][$ownerName]))
		{
			return self::$pluginParams[$shippingName][$extensionName][$ownerName];
		}

		// This query will make a fallback
		// If owner name config is not found it will use extension config, and if extension config is not found it will use default plugin config
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				'sc1.*, p.*, COALESCE(sc2.params, COALESCE(sc1.params, p.params)) as params,
				 COALESCE(sc2.state, COALESCE(sc1.state, p.state)) as state'
			)
			->select('CONCAT("plg_redshipping_", p.element) as plugin_path_name')
			->select('COALESCE(sc2.extension_name, COALESCE(sc1.extension_name, ' . $db->q('') . ')) as extension_name')
			->select('COALESCE(sc2.owner_name, COALESCE(sc1.owner_name, ' . $db->q('') . ')) as owner_name')
			->select('COALESCE(sc2.state, COALESCE(sc1.state, p.enabled)) as state')
			->from($db->qn('#__extensions', 'p'))
			->where($db->qn('p.type') . '= ' . $db->q('plugin'))
			->where($db->qn('p.folder') . ' IN (' . $db->q('redshipping') . ', ' . $db->q('system') . ')')
			->where($db->qn('p.element') . ' = ' . $db->q($shippingName))
			->leftJoin(
				$db->qn('#__redshopb_shipping_configuration', 'sc1') .
				' ON sc1.shipping_name = p.element AND sc1.extension_name = ' .
				$db->q($extensionName)
			)
			->leftJoin(
				$db->qn('#__redshopb_shipping_configuration', 'sc2') .
				' ON sc2.shipping_name = p.element AND sc2.extension_name = ' .
				$db->q($extensionName) .
				' AND sc2.owner_name = ' .
				$db->q($ownerName)
			);

		$db->setQuery($query);
		$item = $db->loadObject();

		$registry = new Registry;
		$registry->loadString($item->params);
		$item->params = $registry;

		self::$pluginParams[$shippingName][$extensionName][$ownerName] = $item;

		return self::$pluginParams[$shippingName][$extensionName][$ownerName];
	}

	/**
	 * Gets price for the given shipping rate
	 *
	 * @param   int  $shippingRateId  Id of the shipping rate
	 *
	 * @return  float
	 *
	 * @since   1.6
	 */
	public static function getShippingRatePrice($shippingRateId)
	{
		if (empty($shippingRateId))
		{
			return 0;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('sr.price')
			->from($db->qn('#__redshopb_shipping_rates', 'sr'))
			->where('sr.id = ' . (int) $shippingRateId);

		$db->setQuery($query);

		return (float) $db->loadResult();
	}

	/**
	 * Gets price for the given shipping rate
	 *
	 * @param   int      $shippingRateId  Id of the shipping rate
	 * @param   bool     $fullName        Should it show full shipping plugin and shipping rate name or just shipping rate name
	 * @param   string   $currency        Currency
	 * @param   integer  $orderId         Optional id of specifik order shipping price to get
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public static function getShippingRateName($shippingRateId, $fullName = true, $currency = 'DKK', $orderId = null)
	{
		if (empty($shippingRateId))
		{
			return '';
		}

		$item = self::getShippingRateById($shippingRateId);

		if ($item)
		{
			if ($fullName)
			{
				$params = new Registry;
				$params->loadString($item->params);

				if (null !== $orderId && $orderId > 0)
				{
					$price = RedshopbEntityOrder::load($orderId)->get('shipping_price');
				}
				else
				{
					$price = $item->price;
				}

				return RedshopbLayoutHelper::render(
					'checkout.shipping.rate_name',
					array(
						'shipping_title' => $params->get('shipping_title'),
						'item_name' => $item->name,
						'item_price' => RHelperCurrency::getFormattedPrice($price, $currency)
					)
				);
			}

			return $item->name;
		}

		return '';
	}

	/**
	 * Gets price for the given shipping rate
	 *
	 * @param   int  $shippingRateId  Id of the shipping rate
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public static function getShippingRateById($shippingRateId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('sr.*')
			->from($db->qn('#__redshopb_shipping_rates', 'sr'))

			->select('sc.params, sc.shipping_name')
			->leftJoin($db->qn('#__redshopb_shipping_configuration', 'sc') . ' ON sc.id = sr.shipping_configuration_id')

			->where('sr.id = ' . (int) $shippingRateId);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Gets filtered shipping rates
	 *
	 * @param   string  $shippingName     Shipping name
	 * @param   string  $extensionName    Extension name
	 * @param   string  $ownerName        Owner name
	 * @param   object  $deliveryAddress  Address information for delivery
	 * @param   array   $cart             Array of product items
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function getShippingRates($shippingName, $extensionName, $ownerName, $deliveryAddress, $cart)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('sr.*')
			->from($db->qn('#__redshopb_shipping_rates', 'sr'))
			->leftJoin($db->qn('#__redshopb_shipping_configuration', 'sc') . ' ON sc.id = sr.shipping_configuration_id')
			->where('sc.shipping_name = ' . $db->q($shippingName))
			->where('sc.extension_name = ' . $db->q($extensionName))
			->where('sc.owner_name = ' . $db->q($ownerName))
			->where('sr.state = 1')
			->where('sc.state = 1')
			->order('sr.priority ASC');

		// Filter by total price
		$totalPrice = RedshopbHelperCart::getFirstTotalPrice();
		$totalPrice = $totalPrice[key($totalPrice)];
		$query->where('(sr.order_total_end = 0 OR ' . $db->q($totalPrice) . ' BETWEEN sr.order_total_start AND sr.order_total_end)');

		// Get Cart Info
		$cartInfo = self::getCartItemInfo($cart);

		// There are 3 cases of dimension volume shipping
		$volumeCases     = array(
			0 => array(
				'length' => $cartInfo['maxLength'],
				'width' => $cartInfo['maxWidth'],
				'height' => $cartInfo['totalHeight'],
			),
			1 => array(
				'length' => $cartInfo['maxLength'],
				'width' => $cartInfo['totalWidth'],
				'height' => $cartInfo['maxHeight'],
			),
			2 => array(
				'length' => $cartInfo['totalLength'],
				'width' => $cartInfo['maxWidth'],
				'height' => $cartInfo['maxHeight'],
			)
		);
		$volumeCaseQuery = array();

		foreach ($volumeCases as $volumeCase)
		{
			$volumeCaseQuery[] = '('
				. '(sr.length_end = 0 OR ' . $db->q($volumeCase['length']) . ' BETWEEN sr.length_start AND sr.length_end)'
				. 'AND (sr.width_end = 0 OR ' . $db->q($volumeCase['width']) . ' BETWEEN sr.width_start AND sr.width_end)'
				. 'AND (sr.height_end = 0 OR ' . $db->q($volumeCase['height']) . ' BETWEEN sr.height_start AND sr.height_end)'
				. ' )';
		}

		if (!empty($volumeCaseQuery))
		{
			$query->where('( ' . implode(' OR ', $volumeCaseQuery) . ' )');
		}

		// Filter by volume
		$query->where('(sr.volume_end = 0 OR ' . $db->q($cartInfo['totalVolume']) . ' BETWEEN sr.volume_start AND sr.volume_end)');

		// Filter by weight
		$query->where('(sr.weight_end = 0 OR ' . $db->q($cartInfo['totalWeight']) . ' BETWEEN sr.weight_start AND sr.weight_end)');

		// Filter by products
		if (count($cartInfo['productList']))
		{
			$productSetQuery = array();

			foreach ($cartInfo['productList'] as $productId)
			{
				$productSetQuery[] = 'FIND_IN_SET(' . (int) $productId . ', sr.on_product)';
			}

			if (!empty($productSetQuery))
			{
				$query->where('(sr.on_product = ' . $db->q('') . ' OR ' . implode(' OR ', $productSetQuery) . ' )');
			}
		}

		// Filter by category
		if (count($cartInfo['categoryList']))
		{
			$categorySetQuery = array();

			foreach ($cartInfo['categoryList'] as $categoryId)
			{
				$categorySetQuery[] = 'FIND_IN_SET(' . (int) $categoryId . ', sr.on_category)';
			}

			if (!empty($categorySetQuery))
			{
				$query->where('(sr.on_category = ' . $db->q('') . ' OR ' . implode(' OR ', $categorySetQuery) . ' )');
			}
		}

		// Filter by Country
		if (isset($deliveryAddress->country_id))
		{
			$query->where('(sr.countries = ' . $db->q('') . ' OR FIND_IN_SET(' . (int) $deliveryAddress->country_id . ', sr.countries))');
		}

		// Filter by Zip code
		if (isset($deliveryAddress->zip))
		{
			$zip = trim($deliveryAddress->zip);
			$zip = preg_replace("/[^a-z A-Z0-9]+/", "", $zip);

			if ($zip != '')
			{
				$query->where(
					'(sr.zip_end =' . $db->q('0') . ' OR sr.zip_end = ' . $db->q('') . ' OR ' . $db->q($zip) . ' BETWEEN sr.zip_start AND sr.zip_end)'
				);
			}
		}

		$db->setQuery($query);
		$shippingRates = $db->loadObjectList();
		$shippingRates = self::filterRatesByPriority($shippingRates);

		return $shippingRates;
	}

	/**
	 * Check Self Pickup Available For Product Id
	 *
	 * @param   integer  $productId  Product id
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public static function checkSelfPickupAvailableForProduct($productId)
	{
		$product = RedshopbHelperProduct::loadProduct($productId);

		if (!$product)
		{
			return false;
		}

		$db           = Factory::getDbo();
		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$companyId    = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$priceGroups  = RedshopbEntityCompany::getInstance($companyId)->getPriceGroups()->ids();

		$query = $db->getQuery(true)
			->select('sr.*')
			->from($db->qn('#__redshopb_shipping_rates', 'sr'))
			->leftJoin($db->qn('#__redshopb_shipping_configuration', 'sc') . ' ON sc.id = sr.shipping_configuration_id')
			->leftJoin($db->qn('#__extensions', 'p') . ' ON sc.shipping_name = p.element')
			->where($db->qn('p.type') . '= ' . $db->q('plugin'))
			->where($db->qn('p.folder') . '= ' . $db->q('redshipping'))
			->where($db->qn('p.element') . ' = ' . $db->q('self_pickup'))
			->where('sc.extension_name = ' . $db->q('com_redshopb'))
			->where('sr.state = 1')
			->where('sc.state = 1');

		if ($priceGroups)
		{
			$query->where('sc.owner_name IN (' . implode(',', $priceGroups) . ')');
		}
		else
		{
			$query->where('sc.owner_name = ' . $db->q(''));
		}

		$query->where('(sr.on_product = ' . $db->q('') . ' OR FIND_IN_SET(' . $db->q($productId) . ', sr.on_product))');
		$orCondition = array('sr.on_category = ' . $db->q(''));

		if ($product->categories)
		{
			foreach ($product->categories as $category)
			{
				$orCondition[] = 'FIND_IN_SET(' . $db->q($category) . ', sr.on_category)';
			}
		}

		$query->where('(' . implode(' OR ', $orCondition) . ')');
		$result = $db->setQuery($query, 0, 1)
			->loadResult();

		if ($result)
		{
			$query  = $db->getQuery(true)
				->select('spx.product_id')
				->from($db->qn('#__redshopb_stockroom_product_xref', 'spx'))
				->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON spx.stockroom_id = s.id')
				->where('s.state = 1')
				->where('s.pick_up = 1')
				->where('(spx.amount > 0 OR spx.unlimited = 1)')
				->where('spx.product_id = ' . (int) $productId);
			$result = $db->setQuery($query, 0, 1)
				->loadResult();

			if ($result)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets dimensions from cart
	 *
	 * @param   array  $cart  Array of product items
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function getCartItemInfo($cart)
	{
		$idx = count($cart);

		$cartInfo = array(
			"totalQuantity" => 0,
			"maxWeight"   => 0,
			"maxVolume"   => 0,
			"maxLength"   => 0,
			"maxHeight"   => 0,
			"maxWidth"    => 0,
			"maxDepth"    => 0,
			"totalWeight"   => 0,
			"totalVolume"   => 0,
			"totalLength"   => 0,
			"totalHeight"   => 0,
			"totalWidth"    => 0,
			"totalDepth"    => 0,
			"productList"   => array(),
			"categoryList"  => array(),
		);

		if ($idx > 0)
		{
			foreach ($cart as $oneProduct)
			{
				$product = RedshopbHelperProduct::loadProduct($oneProduct['productId']);
				$data    = RedshopbHelperProduct::loadProductFields($oneProduct['productId']);

				if (!empty($oneProduct['accessories']))
				{
					for ($a = 0; $a < count($oneProduct['accessories']); $a++)
					{
						$accessoryId      = $oneProduct['accessories'][$a]['productId'];
						$accessoryProduct = RedshopbHelperProduct::loadProduct($oneProduct['accessories'][$a]['productId']);
						$accessoryData    = RedshopbHelperProduct::loadProductFields($accessoryId);

						self::addToCartItemInfo($cartInfo, $accessoryProduct, $accessoryData, $oneProduct['quantity']);
					}
				}

				self::addToCartItemInfo($cartInfo, $product, $data, $oneProduct['quantity']);
			}
		}

		return $cartInfo;
	}

	/**
	 * Gets dimensions and other info from product
	 *
	 * @param   array   $cartInfo   Cart Info
	 * @param   mixed   $product    Product
	 * @param   array   $fields     Product fields
	 * @param   integer $quantity   Quantity
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addToCartItemInfo(&$cartInfo, $product, $fields, $quantity)
	{
		$cartInfo['totalQuantity']            += $quantity;
		$cartInfo['productList'][$product->id] = $product->id;

		if (!empty($product->categories))
		{
			$cartInfo['categoryList'] = array_merge($cartInfo['categoryList'], $product->categories);
		}

		if (!empty($fields['UnitWeight']))
		{
			$cartInfo['totalWeight'] += ((float) $fields['UnitWeight'] * $quantity);

			if ($cartInfo['maxWeight'] < (float) $fields['UnitWeight'])
			{
				$cartInfo['maxWeight'] = (float) $fields['UnitWeight'];
			}
		}

		if (!empty($fields['Volume']))
		{
			$cartInfo['totalVolume'] += ((float) $fields['Volume'] * $quantity);

			if ($cartInfo['maxVolume'] < (float) $fields['Volume'])
			{
				$cartInfo['maxVolume'] = (float) $fields['Volume'];
			}
		}

		if (!empty($fields['Length']))
		{
			$cartInfo['totalLength'] += ((float) $fields['Length'] * $quantity);

			if ($cartInfo['maxLength'] < (float) $fields['Length'])
			{
				$cartInfo['maxLength'] = (float) $fields['Length'];
			}
		}

		if (!empty($fields['Height']))
		{
			$cartInfo['totalHeight'] += ((float) $fields['Height'] * $quantity);

			if ($cartInfo['maxHeight'] < (float) $fields['Height'])
			{
				$cartInfo['maxHeight'] = (float) $fields['Height'];
			}
		}

		if (!empty($fields['Width']))
		{
			$cartInfo['totalWidth'] += ((float) $fields['Width'] * $quantity);

			if ($cartInfo['maxWidth'] < (float) $fields['Width'])
			{
				$cartInfo['maxWidth'] = (float) $fields['Width'];
			}
		}

		if (!empty($fields['Depth']))
		{
			$cartInfo['totalDepth'] += ((float) $fields['Depth'] * $quantity);

			if ($cartInfo['maxDepth'] < (float) $fields['Depth'])
			{
				$cartInfo['maxDepth'] = (float) $fields['Depth'];
			}
		}
	}

	/**
	 * Filter Shipping rates based on their priority
	 * Only show Higher priority rates (In [1,2,3,4] take 1 as a high priority)
	 * Rates with same priority will shown as radio button list in checkout
	 *
	 * @param   array  $shippingRates  Array shipping rates
	 *
	 * @return array
	 */
	public static function filterRatesByPriority($shippingRates)
	{
		$filteredRates = array();

		$ni = count($shippingRates);
		$ii = 0;

		for ($i = 0; $i < $ni; $i++)
		{
			if ($shippingRates[0]->priority == $shippingRates[$i]->priority)
			{
				$filteredRates[$ii] = $shippingRates[$i];
				$ii++;
			}
		}

		return $filteredRates;
	}
}
