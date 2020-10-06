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
 * Handles Product based shipping calculator
 *
 * @package     Redshopb.Plugin
 * @subpackage  Shipping
 * @since       1.6
 */
class ShippingHelperProduct_Based_Shipping_Calculator extends RedshopbShippingPluginHelperShipping
{
	/**
	 * Get list of shipping rates applicable for product based shipping cost
	 *
	 * @param   string  $shippingName  Shipping rate determination method's name
	 *
	 * @return  array  Array of shipping config Ids
	 */
	public static function getShippingConfigId($shippingName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from($db->quoteName('#__redshopb_shipping_configuration'));
		$query->where($db->quoteName('shipping_name') . " = " . $db->quote($shippingName));

		$db->setQuery($query);
		$shippingConfigIds = $db->loadObjectList();

		return $shippingConfigIds;
	}

	/**
	 * Update Auto-calculated Shipping rate
	 * by Shipping rate determination method's ID
	 *
	 * @param   array  $shippingConfigIds  Object list of shipping configuration IDs
	 * @param   float  $price              Auto-calculated shipping cost
	 *
	 * @return  void
	 */
	public static function updateAutoCalculatedShippingCost($shippingConfigIds, $price)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$shippingConfigArray = array();

		foreach ($shippingConfigIds AS $shippingConfigId)
		{
			$shippingConfigArray[] = $shippingConfigId->id;
		}

		$query->update($db->qn('#__redshopb_shipping_rates'))
			->set($db->qn('price') . ' = ' . $db->q($price))
			->where($db->qn('shipping_configuration_id') . ' IN (' . implode(',', $shippingConfigArray) . ')');

		$db->setQuery($query);

		$db->execute();
	}

	/**
	 * Calculate price based on weight
	 *
	 * @param   float  $weight Total weight of purchase
	 *
	 * @return  array
	 */
	public static function getPriceByWeight($weight)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('price')
			->from($db->quoteName('#__redshopb_shipping_rates'))
			->where($db->quoteName('weight_start') . " < " . $db->quote($weight))
			->where($db->quoteName('weight_end') . " >= " . $db->quote($weight))
			->setLimit(1);

		$db->setQuery($query);
		$price = $db->loadResult();

		return $price;
	}

	/**
	 * Calculate price based on volume
	 *
	 * @param   float  $volume  Total volume of purchase
	 *
	 * @return  array
	 */
	public static function getPriceByVolume($volume)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('price')
			->from($db->quoteName('#__redshopb_shipping_rates'))
			->where($db->quoteName('volume_start') . " < " . $db->quote($volume))
			->where($db->quoteName('volume_end') . " >= " . $db->quote($volume))
			->setLimit(1);

		$db->setQuery($query);
		$price = $db->loadResult();

		return $price;
	}

	/**
	 * Get shipping rates which are product specific
	 *
	 * @return  array
	 */
	public static function getProductSpecificShipping()
	{
		$db               = Factory::getDbo();
		$query            = $db->getQuery(true);
		$productSpecifics = array();

		$query->select(array($db->qn('id'), $db->qn('price'), $db->qn('on_product')))
			->from($db->qn('#__redshopb_shipping_rates'))
			->where($db->qn('on_product') . ' != \'\'');

		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results AS $key => $result)
		{
			if (!$result->on_product || !$result->price)
			{
				continue;
			}

			$productSpecifics[$key]['price']      = $result->price;
			$productSpecifics[$key]['on_product'] = explode(',', $result->on_product);
		}

		return $productSpecifics;
	}

	/**
	 * Get free shipping options for the product specified
	 *
	 * @param   array    $productInfo   Array of product information
	 * @param   integer  $customerId    Customer ID
	 * @param   string   $customerType  Customer Type
	 *
	 * @return  array
	 */
	public static function getFreeShippingRates($productInfo, $customerId, $customerType)
	{
		$db = Factory::getDbo();

		// Get applicable product discount groups
		$query = $db->getQuery(true)
			->select(array($db->qn('discount_group_id')))
			->from($db->qn('#__redshopb_product_discount_group_xref'))
			->where($db->qn('product_id') . ' = ' . $productInfo['id']);

		$db->setQuery($query);
		$productDiscountGroups = $db->loadColumn();

		$productRecursiveCategory = RedshopbHelperCategory::getParentCategories(
			$customerId,
			$customerType,
			(array) $productInfo['categories'],
			true
		);

		$processedCategoryList = array_merge(...$productRecursiveCategory);

		if (!empty($productRecursiveCategory))
		{
			$query = $db->getQuery(true)
				->select(array($db->qn('id'), $db->qn('product_discount_group_id'), $db->qn('category_id'), $db->qn('threshold_expenditure')))
				->from($db->qn('#__redshopb_free_shipping_threshold_purchases'));

			if (!empty($processedCategoryList))
			{
				$query->where($db->qn('category_id') . ' IN (' . implode(',', $processedCategoryList) . ')', 'OR');
			}

			if (!empty($productDiscountGroups))
			{
				$query->where($db->qn('product_discount_group_id') . ' IN (' . implode(',', $productDiscountGroups) . ')');
			}

			$query->order('threshold_expenditure ASC');
			$db->setQuery($query);

			return $db->loadObjectList();
		}

		return array();
	}
}
