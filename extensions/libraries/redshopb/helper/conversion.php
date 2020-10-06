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
 * A Conversion Set helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.6.26
 */
final class RedshopbHelperConversion
{
	/**
	 * Method for remove an conversion set
	 *
	 * @param   int  $conversionId        ID of conversion
	 * @param   int  $productAttributeId  ID of Product attribute
	 *
	 * @return  boolean                   True on success. False otherwise.
	 */
	public static function removeConversionSet($conversionId = 0, $productAttributeId = 0)
	{
		$conversionId       = (int) $conversionId;
		$productAttributeId = (int) $productAttributeId;

		if (!$conversionId || !$productAttributeId)
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_conversion'))
			->where($db->qn('id') . ' = ' . $conversionId)
			->where($db->qn('product_attribute_id') . ' = ' . $productAttributeId);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method for get list of Conversion Sets base on specific Product Attribute
	 *
	 * @param   int  $productAttributeId  ID of Product Attribute
	 *
	 * @return  array/boolean             List of Conversion Sets if success. False otherwise.
	 */
	public static function getProductAttributeConversionSets($productAttributeId = 0)
	{
		$productAttributeId = (int) $productAttributeId;

		if (!$productAttributeId)
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_conversion'))
			->where($db->qn('product_attribute_id') . ' = ' . $productAttributeId);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method for remove all Conversion Sets of specific Product Attribute
	 *
	 * @param   int  $productAttributeId  ID of Product Attribute
	 *
	 * @return  boolean                   True on success. False otherwise.
	 */
	public static function removeProductAttributeConversionSet($productAttributeId = 0)
	{
		$productAttributeId = (int) $productAttributeId;

		if (!$productAttributeId)
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_conversion'))
			->where($db->qn('product_attribute_id') . ' = ' . $productAttributeId);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method for get Conversion data for Product Attribute Value Id
	 *
	 * @param   int  $productAttributeValueId  ID of Product Attribute value
	 * @param   int  $conversionId             ID of Conversion
	 *
	 * @return  array/boolean                  List of conversion data if success. False otherwise.
	 */
	public static function getProductAttributeValueConversionData($productAttributeValueId = 0, $conversionId = 0)
	{
		$productAttributeValueId = (int) $productAttributeValueId;

		if (!$productAttributeValueId)
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn(array('conversion_set_id', 'value', 'image')))
			->from($db->qn('#__redshopb_product_attribute_value_conv_xref'))
			->where($db->qn('value_id') . ' = ' . $productAttributeValueId);

		if (!empty($conversionId))
		{
			$query->where($db->qn('conversion_set_id') . ' = ' . (int) $conversionId);
		}

		$db->setQuery($query);

		return $db->loadObjectList('conversion_set_id');
	}

	/**
	 * Method for get default selected conversion set of specific Product Attribute Type
	 *
	 * @param   int  $productAttributeId  ID of Product Attribute
	 *
	 * @return  object/boolean            Product Attribute data if success. False otherwise.
	 */
	public static function getProductAtrributeDefaultConversionSet($productAttributeId = 0)
	{
		$productAttributeId = (int) $productAttributeId;

		if (!$productAttributeId)
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_conversion'))
			->where($db->qn('product_attribute_id') . ' = ' . $productAttributeId)
			->where($db->qn('default') . ' = 1');
		$db->setQuery($query);

		return $db->loadObject();
	}
}
