<?php
/**
 * @package     Redshopb.Plugin
 * @subpackage  Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Product Based Shipping Calculator Redshipping plugin.
 *
 * @package     Redshopb.Plugin
 * @subpackage  Shipping
 * @since       1.6
 */
class PlgRedshippingProduct_Based_Shipping_Calculator extends RedshopbShippingPluginBase
{
	/**
	 * @var float
	 */
	protected $totalPriceBySize = 0.0;

	/**
	 * @var string
	 */
	protected $shippingName = 'product_based_shipping_calculator';

	/**
	 * @var array
	 */
	protected $freeShippingExpenditures = array();

	/**
	 * Change shipping price if applicable
	 *
	 * @param   float    $shippingPrice   Shipping price
	 * @param   integer  $shippingRateId  Shipping rate ID
	 *
	 * @return  void
	 */
	public function onAECsetShippingPrice(&$shippingPrice, $shippingRateId)
	{
		if ($this->isShippingRateProductBased($shippingRateId))
		{
			$this->recalculateShipping();

			$shippingPrice = $this->totalPriceBySize;
		}
	}

	/**
	 * Checks if shipping rate belongs to this type
	 *
	 * @param   integer  $shippingRateId  Shipping rate ID
	 *
	 * @return  boolean
	 */
	private function isShippingRateProductBased($shippingRateId)
	{
		$shippingRateTable = RedshopbTable::getAutoInstance('Shipping_Rate');

		$shippingRateTable->load($shippingRateId);
		$shippingConfigId = $shippingRateTable->get('shipping_configuration_id');

		if (!$shippingConfigId)
		{
			return false;
		}

		$shippingConfigTable = RedshopbTable::getAutoInstance('Shipping_Configuration');

		$shippingConfigTable->load($shippingConfigId);
		$shippingName = $shippingConfigTable->get('shipping_name');

		if ($shippingName == $this->shippingName)
		{
			return true;
		}

		return false;
	}

	/**
	 * Upon checkout
	 *
	 * @return  boolean
	 */
	public function onRedshopbCheckoutShipping()
	{
		return $this->recalculateShipping();
	}

	/**
	 * Update shipping on cart page load
	 *
	 * @return   boolean
	 */
	public function onBeforeAECPrepareCheckout()
	{
		return $this->recalculateShipping();
	}

	/**
	 * Update shipping when refreshing shipping methods
	 *
	 * @return   boolean
	 */
	public function onAECBeforeRenderShippingMethods()
	{
		return $this->recalculateShipping();
	}

	/**
	 * Recalculate shipping cost
	 *
	 * @return   boolean
	 */
	private function recalculateShipping()
	{
		$app = Factory::getApplication();

		$currentCart = RedshopbHelperCart::getCart(
			$app->getUserState('shop.customer_id',  0),
			$app->getUserState('shop.customer_type', '')
		);

		$currentItems = $currentCart->get('items', array());

		$this->applyOffersToItems($currentItems, $currentCart);

		$result = $this->calcShipPrice($currentItems);

		return $result;
	}

	/**
	 * Upon cart item quantity change
	 *
	 * @param   array  $response  Contain cart info
	 *
	 * @return  boolean
	 */
	public function onAfterAjaxUpdateShoppingCartQuantity(&$response)
	{
		$items = $response['items'];

		$this->applyOffersToItems($items);

		$result = $this->calcShipPrice($items);

		return $result;
	}

	/**
	 * Upon cart item removal
	 *
	 * @param   array  $response  Contain cart info
	 *
	 * @return  boolean
	 */
	public function onAfterAjaxRemoveShoppingCartItem(&$response)
	{
		$items = $response['items'];

		$this->applyOffersToItems($items);

		$result = $this->calcShipPrice($items);

		return $result;
	}

	/**
	 * Adds offers in cart to an array of items
	 *
	 * @param   array                      $items  Contain cart info
	 * @param   RedshopbHelperCart_Object  $cart   Optional cart object
	 *
	 * @return  void
	 */
	private function applyOffersToItems(&$items, $cart = null)
	{
		if (!$cart)
		{
			$app = Factory::getApplication();

			$cart = RedshopbHelperCart::getCart(
				$app->getUserState('shop.customer_id',  0),
				$app->getUserState('shop.customer_type', '')
			);
		}

		$currentOffers = $cart->get('offers', array());

		foreach ($currentOffers AS $offer)
		{
			$offerItems = $offer['items'];

			array_push($items, ...$offerItems);
		}
	}

	/**
	 * Calculates shipping cost
	 *
	 * @param   array  $cart  Cart info
	 *
	 * @return  boolean
	 */
	protected function calcShipPrice($cart)
	{
		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id',  0);
		$customerType = $app->getUserState('shop.customer_type', '');

		$totalWeight        = 0;
		$totalVolume        = 0;
		$totalWeightPrice   = 0;
		$totalVolumePrice   = 0;
		$totalSpecificPrice = 0;

		$onProductItems = ShippingHelperProduct_Based_Shipping_Calculator::getProductSpecificShipping();

		foreach ($cart as &$product)
		{
			$productId             = $product['productId'];
			$quantity              = (int) $product['quantity'];
			$productWeight         = 0;
			$productVolume         = 0;
			$productSpecifiedPrice = null;

			$productInfo = (array) RedshopbEntityProduct::load($productId)->getItem();
			$weight      = (float) $productInfo['weight'];
			$volume      = (float) $productInfo['volume'];
			$calcType    = (int) $productInfo['calc_type'];

			foreach ($onProductItems AS $onProductItem)
			{
				if (in_array($productId, $onProductItem['on_product']))
				{
					$productSpecifiedPrice = $onProductItem['price'] * $quantity;
					$totalSpecificPrice   += $productSpecifiedPrice;

					break;
				}
			}

			if (is_null($productSpecifiedPrice))
			{
				switch ($calcType)
				{
					case 1:
						$productWeight = $weight * $quantity;
						$totalWeight  += $productWeight;
						break;
					case 2:
						$productVolume = $volume * $quantity;
						$totalVolume  += $productVolume;
						break;
				}
			}

			$freeShippingsForProduct = ShippingHelperProduct_Based_Shipping_Calculator::getFreeShippingRates(
				$productInfo,
				$customerId,
				$customerType
			);

			if (!empty($freeShippingsForProduct))
			{
				$productPriceInfo = RedshopbHelperPrices::getProductsPrice(
					array($productId),
					$customerId,
					$customerType,
					null,
					array(),
					'',
					0,
					$quantity
				);

				foreach ($freeShippingsForProduct AS $freeShippingForProduct)
				{
					$dataKey = $productId . '_' . $quantity;

					$this->freeShippingExpenditures[$freeShippingForProduct->id]['expenditure'] += $productPriceInfo[$productId]->price * $quantity;
					$this->freeShippingExpenditures[$freeShippingForProduct->id]['limit']        = $freeShippingForProduct->threshold_expenditure;

					$this->freeShippingExpenditures[$freeShippingForProduct->id]['product_data'][$dataKey] = array(
						'weight'           => $productWeight,
						'volume'           => $productVolume,
						'specified_prices' => $productSpecifiedPrice
					);
				}
			}
		}

		foreach ($this->freeShippingExpenditures AS &$freeShippingRules)
		{
			if ((float) $freeShippingRules['expenditure'] < (float) $freeShippingRules['limit'])
			{
				continue;
			}

			$this->enactFreeShippingRules($freeShippingRules, $totalWeight, $totalVolume, $totalSpecificPrice);

			$totalWeight        -= $freeShippingRules['weight'];
			$totalVolume        -= $freeShippingRules['volume'];
			$totalSpecificPrice -= $freeShippingRules['specified_prices'];
		}

		if ($totalWeight > 0)
		{
			$totalWeightPrice = ShippingHelperProduct_Based_Shipping_Calculator::getPriceByWeight($totalWeight);
		}

		if ($totalVolume > 0)
		{
			$totalVolumePrice = ShippingHelperProduct_Based_Shipping_Calculator::getPriceByVolume($totalVolume);
		}

		$this->totalPriceBySize = $totalWeightPrice + $totalVolumePrice + $totalSpecificPrice;

		if ($this->totalPriceBySize == 0)
		{
			$this->totalPriceBySize = $this->params->get('fallback_price', 0);
		}

		$shippingConfigIds = ShippingHelperProduct_Based_Shipping_Calculator::getShippingConfigId($this->shippingName);

		if (!empty($shippingConfigIds))
		{
			ShippingHelperProduct_Based_Shipping_Calculator::updateAutoCalculatedShippingCost(
				$shippingConfigIds,
				$this->totalPriceBySize
			);
		}

		return true;
	}

	/**
	 * Get free shipping options for the product specified
	 *
	 * @param   array  $freeShippingRules   Rules and data for the free shipping to enact
	 * @param   float  $totalWeight         Total weight of order to modify
	 * @param   float  $totalVolume         Total volume of order to modify
	 * @param   float  $totalSpecificPrice  Total amount of shipping for product specific shipping in order
	 *
	 * @return  void
	 */
	protected function enactFreeShippingRules($freeShippingRules, &$totalWeight, &$totalVolume, &$totalSpecificPrice)
	{
		foreach ($freeShippingRules['product_data'] AS $key => $productData)
		{
			$totalWeight        -= $productData['weight'];
			$totalVolume        -= $productData['volume'];
			$totalSpecificPrice -= $productData['specified_prices'];

			// We do not want to subtract products data from final shipping twice
			$this->unsetProductKey($key);
		}
	}

	/**
	 *
	 * @param   string  $dataKey  Key to unset
	 *
	 * @return  void
	 */
	protected function unsetProductKey($dataKey)
	{
		foreach ($this->freeShippingExpenditures AS $key => $freeShippingExpenditure)
		{
			if (array_key_exists($dataKey, $freeShippingExpenditure['product_data']))
			{
				unset($this->freeShippingExpenditures[$key]['product_data'][$dataKey]);
			}
		}
	}
}
