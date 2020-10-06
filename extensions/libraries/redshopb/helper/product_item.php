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
/**
 * A Product Item helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperProduct_Item
{
	/**
	 * Product items cache
	 *
	 * @var  array
	 */
	private static $productItems = array();

	/**
	 * Get the attributes values for a given product item.
	 * Note : it also contains the sku in the name if enabled and $checkSkuDisplay = true.
	 *
	 * @param   integer  $productItemId    The product item id
	 * @param   boolean  $checkSkuDisplay  True to check the sku display and enhance the sku value
	 *
	 * @return  array  An array of attribute values index by the attribute id
	 */
	public static function getAttributeValues($productItemId, $checkSkuDisplay = true)
	{
		$db = Factory::getDbo();

		// Get the attributes
		$attributes = self::getAttributesAsArray($productItemId);

		$finalAttributes = array();

		// For each attribute get the values
		foreach ($attributes as $attribute)
		{
			$attributeId = (int) $attribute['id'];

			$query = RedshopbHelperProduct_Attribute::getValuesQuery($attributeId, '', $checkSkuDisplay)
				->innerJoin('#__redshopb_product_item_attribute_value_xref AS map on map.product_attribute_value_id = pav.id')
				->where('map.product_item_id = ' . $db->quote($productItemId));

			$db->setQuery($query);

			$attributeValue = $db->loadResult();

			$finalAttributes[$attributeId] = $attributeValue;
		}

		return $finalAttributes;
	}

	/**
	 * Get the attributes data for a given product item
	 *
	 * @param   integer  $productItemId  The product item id
	 *
	 * @return  array  An array of attribute data
	 */
	public static function getAttributesAsArray($productItemId)
	{
		$productId = self::getProductId($productItemId);

		if (null === $productId)
		{
			return array();
		}

		return RedshopbHelperProduct::getAttributesAsArray($productId);
	}

	/**
	 * Get the product id associated to this item
	 *
	 * @param   integer  $productItemId  The product item id
	 *
	 * @return  mixed  The product id of null if no product
	 */
	public static function getProductId($productItemId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('product_id')
			->from('#__redshopb_product_item')
			->where('id = ' . $db->q($productItemId));

		$db->setQuery($query);

		$productId = $db->loadResult();

		if (empty($productId))
		{
			return null;
		}

		return (int) $productId;
	}

	/**
	 * Load product item
	 *
	 * @param   integer  $productItemId  product item id
	 *
	 * @return  mixed    Product item object on success, false on failure.
	 */
	public static function loadProductItem($productItemId)
	{
		if (!isset(self::$productItems[$productItemId]))
		{
			$table = RTable::getAdminInstance('product_item');
			$table->load($productItemId);
			self::$productItems[$productItemId] = $table;
		}

		return self::$productItems[$productItemId];
	}

	/**
	 * Get product item price.
	 *
	 * @param   int     $productItemId  Product item id.
	 * @param   int     $customerId     Customer id.
	 * @param   string  $currency       Currency.
	 *
	 * @return float Product item price.
	 */
	public static function getProductItemPrice($productItemId, $customerId = null, $currency = 'DKK')
	{
		if ($customerId == null)
		{
			$customerId = Factory::getApplication()->getUserStateFromRequest('list.customer_id', 'customer_id', 0, 'int');
		}

		$iPrice = RedshopbHelperPrices::getProductItemPrice($productItemId, $customerId, $currency);

		if (!isset($iPrice->price) || $iPrice->price <= 0.0)
		{
			$productPrice  = RedshopbHelperProduct::getProductPrice((int) $iPrice->product_id, $customerId, $currency);
			$iPrice->price = ($productPrice > 0.0) ? $productPrice : 0.0;
		}

		return $iPrice->price;
	}

	/**
	 * Get product variant SKU.
	 *
	 * @param   int      $productItemId  Product item id.
	 * @param   boolean  $fullSKU        Return full sku with product sku beside item sku.
	 *
	 * @return string Product item SKU.
	 */
	public static function getSKU($productItemId, $fullSKU = false)
	{
		if (!empty($productItemId))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			if ($fullSKU)
			{
				$subQuery = $db->getQuery(true)
					->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
					->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
					->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id')
					->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
					->where('piavx.product_item_id = pi.id')
					->where('pa.enable_sku_value_display = 1')
					->order('pav.ordering');

				$query->select('(CONCAT_WS(' . $db->q('-') . ', p.sku, (' . $subQuery . '))) AS sku')
					->from($db->qn('#__redshopb_product', 'p'))
					->innerJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.product_id = p.id')
					->where('pi.id = ' . (int) $productItemId);
			}
			else
			{
				$query->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
					->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
					->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id')
					->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
					->where('piavx.product_item_id = ' . (int) $productItemId)
					->where('pa.enable_sku_value_display = 1')
					->order('pav.ordering');
			}

			return $db->setQuery($query)->loadResult();
		}

		return '';
	}

	/**
	 * Get product item attributes with its values.
	 *
	 * @param   int      $productItemId       Product item id.
	 * @param   boolean  $enableTranslations  Use translations for getting attribute values.
	 *
	 * @return array Ordered assoc array with attribute name as key.
	 */
	public static function getAttributesValues($productItemId, $enableTranslations = false)
	{
		if (!empty($productItemId))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('pa.name'),
						$db->qn('pav.sku'),
						$db->qn('pav.string_value'),
						$db->qn('pav.int_value'),
						$db->qn('pav.float_value')
					)
				)
				->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
				->leftJoin(
					$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON ' .
					$db->qn('pav.id') . ' = ' . $db->qn('piavx.product_attribute_value_id')
				)
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON ' . $db->qn('pa.id') . ' = ' . $db->qn('pav.product_attribute_id'))
				->where($db->qn('piavx.product_item_id') . ' = ' . (int) $productItemId)
				->order($db->qn('pa.ordering'))
				->order($db->qn('pav.ordering'));

			if ($enableTranslations)
			{
				RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
					array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')))
				);
			}

			$db->setQuery($query);

			$attributesValues = $db->loadObjectList('name');

			if ($enableTranslations)
			{
				RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();
			}

			foreach ($attributesValues as $attributeValue)
			{
				if (!empty($attributeValue->string_value))
				{
					$attributeValue->value = $attributeValue->string_value;
				}
				elseif (!empty($attributeValue->float_value))
				{
					$attributeValue->value = $attributeValue->float_value;
				}
				else
				{
					$attributeValue->value = $attributeValue->int_value;
				}
			}

			return $attributesValues;
		}

		return array();
	}

	/**
	 * Get more descriptive attribute names.
	 *
	 * @param   int     $productItemId    Product item id.
	 *
	 * @return  string  $attrName         A more descriptive attribute name.
	 */
	public static function getDescriptiveAttributeNames($productItemId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')');
		$query->from($db->qn('#__redshopb_product_attribute_value', 'pav'));
		$query->leftJoin(
			$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id'
		);
		$query->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id');
		$query->where('piavx.product_item_id = ' . $db->q($productItemId));

		$attrName = $db->setQuery($query)->loadResult();

		return $attrName;
	}
}
