<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Product_Item Entity.
 *
 * @since  2.0
 */
class RedshopbEntityProduct_Item extends RedshopbEntity
{
	use RedshopbEntityTraitProduct;

	/**
	 * Get entity by attribute values.
	 *
	 * @param   array  $attributes  Attribute values.
	 *
	 * @return  RedshopbEntityProduct_Item
	 *
	 * @since   1.12.61
	 */
	public static function getInstanceByAttributeValues($attributes)
	{
		$db    = Factory::getDbo();
		$i     = 1;
		$avi   = array_pop($attributes);
		$query = $db->getQuery(true);
		$query->select($db->qn('piavx.product_item_id'))
			->from($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx'))
			->where($db->qn('piavx.product_attribute_value_id') . ' = ' . (int) $avi);

		if (!empty($attributes))
		{
			foreach ($attributes as $attributeValueId)
			{
				$query->innerJoin(
					$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx' . $i) . ' ON ' .
					$db->qn('piavx' . $i . '.product_item_id') . ' = ' . $db->qn('piavx.product_item_id')
				);
				$query->where($db->qn('piavx' . $i . '.product_attribute_value_id') . ' = ' . (int) $attributeValueId);

				$i++;
			}
		}

		$pItemId = (int) $db->setQuery($query)->loadResult();

		return self::getInstance($pItemId);
	}

	/**
	 * Returns SKU for the product item
	 *
	 * @return  string
	 *
	 * @since   1.12.66
	 */
	public function getSku()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return $item->sku;
	}
}
