<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
/**
 * A Product attribute helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperProduct_Attribute
{
	/**
	 * Get the data of the given attribute
	 *
	 * @param   integer  $attributeId  The attribute id.
	 *
	 * @return  mixed  The attribute data as array or NULL.
	 */
	public static function getDataAsArray($attributeId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__redshopb_product_attribute')
			->where('id = ' . $db->q($attributeId));

		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Get the type of the given attribute.
	 *
	 * @param   integer  $attributeId  The attribute id.
	 *
	 * @return  mixed  The attribute type or NULL.
	 */
	public static function getType($attributeId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('type_id')
			->from('#__redshopb_product_attribute')
			->where('id = ' . $db->q($attributeId));

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get all values of this attribute.
	 * Note : it also contains the sku in the name if enabled and $checkSkuDisplay = true.
	 *
	 * @param   integer  $attributeId      The attribute id.
	 * @param   boolean  $checkSkuDisplay  True to check the sku display and enhance the sku value
	 * @param   integer  $collectionId     Collection id if use
	 * @param   array    $attrValues       Attribute value ids.
	 * @param   boolean  $isForShop        True for for getting attributes for display in shop
	 *
	 * @return  array  An array of values with keys = attribute_value_id
	 */
	public static function getValues($attributeId, $checkSkuDisplay = true, $collectionId = null, $attrValues = array(), $isForShop = false)
	{
		$query      = self::getValuesQuery($attributeId, 'pav.id', $checkSkuDisplay);
		$db         = Factory::getDbo();
		$attrValues = array_unique($attrValues);
		$attrValues = ArrayHelper::toInteger($attrValues);
		$key        = array_search(0, $attrValues);

		if ($isForShop)
		{
			$query->where($db->qn('pav.state') . ' = ' . $db->q(1));
		}

		if ($key !== false)
		{
			unset($attrValues[$key]);
		}

		if (!empty($attrValues))
		{
			$valuesQuery = $db->getQuery(true);
			$valuesQuery->select($db->qn('id'))
				->from($db->qn('#__redshopb_product_attribute_value'))
				->where($db->qn('product_attribute_id') . ' = ' . (int) $attributeId);
			$values     = $db->setQuery($query)->loadColumn();
			$values     = ArrayHelper::toInteger($values);
			$attrValues = array_diff($attrValues, $values);

			if (!empty($attrValues))
			{
				$itemsQuery = $db->getQuery(true);
				$itemsQuery->select($db->qn('piavx2.product_item_id'))
					->from($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx2'))
					->where($db->qn('piavx2.product_attribute_value_id') . ' IN (' . implode(',', $attrValues) . ')');

				$query->innerJoin(
					$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON ' .
					$db->qn('piavx.product_attribute_value_id') . ' = ' . $db->qn('pav.id')
				)
					->where($db->qn('piavx.product_item_id') . ' IN (' . $itemsQuery . ')');
			}
		}

		// Filter by collection
		if ($collectionId)
		{
			$query->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON pav.id = piavx.product_attribute_value_id ')
				->leftJoin(
					$db->qn('#__redshopb_collection_product_item_xref', 'wpi') . ' ON wpi.product_item_id = piavx.product_item_id'
				)
				->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = wpi.product_item_id')
				->where('wpi.collection_id = ' . (int) $collectionId)
				->where('wpi.state = 1')
				->where('pi.state = 1');
		}

		$db->setQuery($query);

		$values = $db->loadAssocList('id', 'value');

		if (!is_array($values))
		{
			return array();
		}

		return $values;
	}

	/**
	 * Get the attribute value objects for an attribute id
	 * The returned objects are enhanced of a value property
	 * corresponding to their actual value.
	 * Note : it also contains the sku in the name if enabled and $checkSkuDisplay = true.
	 *
	 * @param   integer  $attributeId      The attribute id
	 * @param   boolean  $checkSkuDisplay  True to check the sku display and enhance the sku value
	 *
	 * @return  array  The objects
	 */
	public static function getValuesAsObject($attributeId, $checkSkuDisplay = true)
	{
		$query = self::getValuesQuery($attributeId, 'pav.*', $checkSkuDisplay);

		$db = Factory::getDbo();
		$db->setQuery($query);

		$values = $db->loadObjectList();

		if (!is_array($values))
		{
			return array();
		}

		return $values;
	}

	/**
	 * Get the query to obtains attribute value data.
	 * Note : it also contains the sku in the name if enabled and $checkSkuDisplay = true.
	 *
	 * @param   integer  $attributeId      The attribute id
	 * @param   string   $select           The value to select
	 * @param   boolean  $checkSkuDisplay  True to check the sku display and enhance the sku value
	 *
	 * @return  JDatabaseQuery  The query
	 * @throws  Exception
	 */
	public static function getValuesQuery($attributeId, $select = 'pav.*', $checkSkuDisplay = true)
	{
		// Get the attribute type to select the correct value.
		$attribute       = self::getDataAsArray($attributeId);
		$attributeType   = $attribute['type_id'];
		$enSkuValDisplay = (bool) $attribute['enable_sku_value_display'];

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if (!empty($select))
		{
			$query->select($select);
		}

		switch ($attributeType)
		{
			case 2:
				if ($checkSkuDisplay && $enSkuValDisplay)
				{
					$query->select("CONCAT(pav.float_value, ' (', pav.sku, ')') AS value");
				}

				else
				{
					$query->select($db->qn('pav.float_value', 'value'));
				}

				$query->order('pav.float_value ASC');
				break;

			case 3:
				if ($checkSkuDisplay && $enSkuValDisplay)
				{
					$query->select("CONCAT(pav.int_value, ' (', pav.sku, ')') AS value");
				}

				else
				{
					$query->select('pav.int_value AS value');
				}

				$query->order('pav.int_value ASC');
				break;

			case 1:
			default:
				if ($checkSkuDisplay && $enSkuValDisplay)
				{
					$query->select("CONCAT(pav.string_value, ' (', pav.sku, ')') AS value");
				}

				else
				{
					$query->select('pav.string_value AS value');
				}
				break;
		}

		$query->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->where('pav.product_attribute_id = ' . $db->q($attributeId))
			->order('pav.ordering');

		return $query;
	}

	/**
	 * Get the Sku representation of the given attribute.
	 *
	 * @param   integer  $attributeId  The attribute id
	 * @param   boolean  $bold         True to bold the part of this attribute inside <b> tags
	 *
	 * @return  string  The SKU representation
	 */
	public static function getSkuRepresentation($attributeId, $bold = false)
	{
		$db = Factory::getDbo();

		// Get the attribute data
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redshopb_product_attribute')
			->where('id = ' . $db->q($attributeId));

		$db->setQuery($query);

		$attributeData = $db->loadAssoc();

		if (empty($attributeData))
		{
			return '';
		}

		// Get the product sku
		$query = $db->getQuery(true)
			->select('sku')
			->from('#__redshopb_product')
			->where('id =' . $db->q($attributeData['product_id']));

		$db->setQuery($query);

		$productSku = $db->loadResult();

		if (empty($productSku))
		{
			return '';
		}

		// Get the number of attribute values
		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from('#__redshopb_product_attribute')
			->where('product_id = ' . $db->q($attributeData['product_id']));

		$db->setQuery($query);

		$count = (int) $db->loadResult();

		if ($count < 1)
		{
			return '';
		}

		// Add the product sku in the count
		$count++;

		// Prepare the sku
		$skuParts    = array_fill(0, $count, 'XXXX');
		$skuParts[0] = $productSku;

		$attributeOrdering = (int) $attributeData['ordering'];

		if ($bold)
		{
			$skuPart = '<b>XXXX</b>';
		}

		else
		{
			$skuPart = 'XXXX';
		}

		$skuParts[$attributeOrdering] = $skuPart;
		ksort($skuParts, SORT_NUMERIC);

		return implode('-', $skuParts);
	}

	/**
	 * Replaces Size Language values for Logged in user
	 *
	 * @param   mixed  $additionalTableJoins  Additional table joins for better filtering
	 * @param   array  $tableAliases          List of table aliases to parse translations on
	 * @param   array  $skipColumns           List of columns to skip from translations
	 *
	 * @return  void
	 */
	public static function replaceSizeLanguageQuery($additionalTableJoins = array(), $tableAliases = array('pav'), $skipColumns = array('pa.name'))
	{
		$language = Factory::getLanguage()->getTag();

		if ($language != RTranslationHelper::getSiteLanguage())
		{
			$translationTables = RTranslationHelper::getInstalledTranslationTables();

			if (!empty($translationTables['#__redshopb_product_attribute_value']))
			{
				$db                                                         = Factory::getDbo();
				$attributeValueTable                                        = array();
				$attributeValueTable['#__redshopb_product_attribute_value'] = clone $translationTables['#__redshopb_product_attribute_value'];

				$attributeValueTable['#__redshopb_product_attribute_value']->tableJoinParams      = $additionalTableJoins;
				$attributeValueTable['#__redshopb_product_attribute_value']->tableJoinEndPosition = 1;
				$attributeValueTable['#__redshopb_product_attribute_value']->tableAliasesToParse  = $tableAliases;

				if (empty($db->parseTablesBefore))
				{
					$db->parseTablesBefore = array();
				}

				if (empty($db->skipColumns))
				{
					$db->skipColumns = array();
				}

				$db->parseTablesBefore['sizeLanguage']                    = new stdClass;
				$db->parseTablesBefore['sizeLanguage']->language          = $language;
				$db->parseTablesBefore['sizeLanguage']->translationTables = $attributeValueTable;
				$db->skipColumns['FROM']                                  = $skipColumns;
				$db->skipColumns['WHERE']                                 = $skipColumns;
			}
		}
	}

	/**
	 * Clears added queries for Size Language feature
	 *
	 * @return  void
	 */
	public static function clearSizeLanguageQuery()
	{
		$db = Factory::getDbo();
		unset($db->parseTablesBefore['sizeLanguage']);
		unset($db->skipColumns['FROM']);
	}

	/**
	 * Method for get attribute description
	 *
	 * @param   int  $productId         ID of product
	 * @param   int  $attributeValueId  ID of attribute value
	 *
	 * @return  boolean|string          Object of description. False otherwise
	 */
	public static function getAttributeDescription($productId, $attributeValueId = null)
	{
		$productId = (int) $productId;

		if (!$productId)
		{
			return false;
		}

		$productEntity = RedshopbEntityProduct::load($productId);

		if (!$productEntity->isLoaded())
		{
			return false;
		}

		$descriptions = $productEntity->getDescriptions();

		if (!($descriptions instanceof RedshopbEntitiesCollection) || $descriptions->isEmpty())
		{
			return false;
		}

		foreach ($descriptions->toObjects() as $description)
		{
			if (!empty($attributeValueId) && $description->main_attribute_value_id == $attributeValueId)
			{
				return $description;
			}
			elseif (is_null($attributeValueId) && empty($description->main_attribute_value_id))
			{
				return $description;
			}
		}

		return false;
	}
}
