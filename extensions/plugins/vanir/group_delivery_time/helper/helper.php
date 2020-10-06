<?php
/**
 * @package     Aesir.E-Commerce.Plugins
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Vanir - Group Delivery Time helper.
 *
 * @package     Aesir.E-Commerce.Plugins
 * @subpackage  Helper
 * @since       1.0
 */
class PlgVanirGroupDeliveryTimeHelper
{
	/**
	 * List of available groups
	 *
	 * @var array
	 */
	protected static $groups = null;

	/**
	 * Method for get group of delivery time.
	 *
	 * @return  array  List of group
	 */
	public static function getGroups()
	{
		if (is_null(self::$groups))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshopb_delivery_time_group'))
				->order($db->qn('id') . ' ASC');

			self::$groups = $db->setQuery($query)->loadObjectList();
		}

		return self::$groups;
	}

	/**
	 * Method for get stock id with have minimum delivery time.
	 *
	 * @param   array|int  $productIds  ID of product.
	 *
	 * @return  array ID of stockrooms.
	 */
	public static function getMinDeliveryStocks($productIds)
	{
		$productIds        = (array) $productIds;
		$productIds        = ArrayHelper::toInteger($productIds);
		static $stockrooms = array();

		if (empty($productIds))
		{
			return array();
		}

		$foundProducts     = array();
		$productsForSearch = array();

		foreach ($productIds as $productId)
		{
			if (array_key_exists($productId, $stockrooms))
			{
				$foundProducts[$productId] = $stockrooms[$productId];
			}
			else
			{
				$productsForSearch[$productId] = $productId;
			}
		}

		if (!empty($productsForSearch))
		{
			$db = Factory::getDbo();

			// Getting min delivery time for each product
			$subQuery = $db->getQuery(true)
				->select('MIN(s1.min_delivery_time) AS min_delivery_time, ref2.product_id')
				->from($db->qn('#__redshopb_stockroom_product_xref', 'ref2'))
				->leftJoin($db->qn('#__redshopb_stockroom', 's1') . ' ON ' . $db->qn('s1.id') . ' = ' . $db->qn('ref2.stockroom_id'))
				->where($db->qn('ref2.product_id') . ' IN (' . implode(',', $productsForSearch) . ')')
				->where('(' . $db->qn('ref2.unlimited') . ' = 1 OR (' . $db->qn('ref2.unlimited') . ' = 0 AND ' . $db->qn('ref2.amount') . ' > 0))')
				->group('ref2.product_id');

			// Getting stockroom id for each product depends with min delivery time
			$query = $db->getQuery(true)
				->select('ref.product_id, ref.stockroom_id')
				->from($db->qn('#__redshopb_stockroom_product_xref', 'ref'))
				->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ref.stockroom_id'))
				->where($db->qn('ref.product_id') . ' IN (' . implode(',', $productsForSearch) . ')')
				->innerJoin(
					'(' . $subQuery . ') AS subQuery ON subQuery.product_id = ref.product_id AND subQuery.min_delivery_time = s.min_delivery_time'
				)
				->group('ref.product_id');

			$results = $db->setQuery($query)
				->loadAssocList('product_id', 'stockroom_id');

			if (!empty($results))
			{
				$stockrooms    = array_replace($stockrooms, $results);
				$foundProducts = array_replace($foundProducts, $results);
			}

			foreach ($productsForSearch as $item)
			{
				if (!array_key_exists($item, $stockrooms))
				{
					$stockrooms[$item] = null;
				}
			}
		}

		return $foundProducts;
	}

	/**
	 * Method for get stock id with have minimum delivery time.
	 *
	 * @param   int  $productId  ID of product.
	 *
	 * @return  integer|boolean
	 */
	public static function getMinDeliveryStock($productId)
	{
		$results = self::getMinDeliveryStocks($productId);

		if (array_key_exists($productId, $results))
		{
			return $results[$productId];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method for get delivery time from specific stockroom ID.
	 *
	 * @param   int  $stockroomId  ID of stockroom.
	 *
	 * @return  integer|boolean
	 */
	public static function getDeliveryTime($stockroomId)
	{
		$stockroomId = (int) $stockroomId;

		if (!$stockroomId)
		{
			return false;
		}

		$stockroom = RedshopbEntityStockroom::load($stockroomId);

		if (!$stockroom->isLoaded())
		{
			return false;
		}

		static $deliveryTimeObj = array();
		$minDeliveryTime        = (int) $stockroom->get('min_delivery_time');

		if (!array_key_exists($minDeliveryTime, $deliveryTimeObj))
		{
			$db                                = Factory::getDbo();
			$query                             = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshopb_delivery_time_group'))
				->where($db->qn('min_time') . ' <= ' . (int) $minDeliveryTime)
				->where($db->qn('max_time') . ' >= ' . (int) $minDeliveryTime)
				->order($db->qn('min_time') . ' ASC');
			$deliveryTimeObj[$minDeliveryTime] = $db->setQuery($query, 0, 1)
				->loadObject();
		}

		return $deliveryTimeObj[$minDeliveryTime];
	}
}
