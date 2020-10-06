<?php
/**
 * @package     Webservices
 * @subpackage  Api
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Api Helper class for overriding default methods
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Api Helper
 * @since       1.2
 */
class RApiHalHelperSiteRedshopbProduct_Item
{
	/**
	 * Method for create new product item
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  integer           Product item ID on success. 0 otherwise.
	 */
	public function save($data)
	{
		$app = Factory::getApplication();

		// Remove the id to prevent update process
		if (isset($data['id']))
		{
			unset($data['id']);
		}

		// Get product Id
		$productId                = (int) $data['product_id'];
		$productAttributeValueIds = (array) $data['product_attribute_value_ids'];

		// Get attributes of this product
		$attributes = RedshopbHelperProduct::getAttributesAsArray($productId);

		if (empty($attributes))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_REQUIRE_SET_FROM_PRODUCT_MORE_ONE_TYPE'), 'error');

			return 0;
		}

		// An array of formatted attributes for the generator
		$formattedAttributes = array();

		// Get the possible values for each attribute
		foreach ($attributes as &$attribute)
		{
			$values = RedshopbHelperProduct_Attribute::getValues($attribute['id']);

			if ($values)
			{
				$attribute['values']                   = $values;
				$formattedAttributes[$attribute['id']] = array_keys($values);
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_EACH_TYPE_MUST_HAVE_AT_LEAST_ONE_ATTRIBUTE'), 'error');

				return 0;
			}
		}

		// Generate the attribute combinations
		$combinations = RedshopbHelperProduct::generateCombinations($formattedAttributes);

		if (empty($combinations))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_NOT_HAVE_COMBINATIONS_FROM_CURRENT_PRODUCT'), 'error');

			return 0;
		}

		// Prepare combinations array
		foreach ($combinations as &$combination)
		{
			// Sort values
			asort($combination);

			// Reset key
			$combination = array_values($combination);
		}

		// Prepare product attribute value ids
		asort($productAttributeValueIds);
		$productAttributeValueIds = array_values($productAttributeValueIds);

		// Check if there are a product item exist
		if (array_search($productAttributeValueIds, $combinations) !== false)
		{
			return 0;
		}

		// Create new product item
		$itemTable     = RedshopbTable::getAdminInstance('Product_Item');
		$itemTable->id = null;

		if (!$itemTable->save($data))
		{
			$app->enqueueMessage($itemTable->getError(), 'error');

			return 0;
		}

		// Insert product item reference with product attribute value ids
		$itemAttributeValueTable = RedshopbTable::getAdminInstance('Product_Item_Attribute_Value_Xref');

		foreach ($productAttributeValueIds as $productAttributeValueId)
		{
			$itemAttributeValueTable->reset();

			// Create the attribute value
			if (!$itemAttributeValueTable->save(
				array(
					'product_item_id' => $itemTable->id,
					'product_attribute_value_id' => $productAttributeValueId,
				)
			))
			{
				$app->enqueueMessage($itemAttributeValueTable->getError(), 'error');

				return 0;
			}
		}

		// Insert price for this product item if avaiable
		if (!empty($data['price']))
		{
			$model = RModelAdmin::getInstance('Product_Item', 'RedshopbModel');
			$model->setPrice($itemTable->id, (float) $data['price']);
		}

		// Insert retail price for this product item if avaiable
		if (!empty($data['retail_price']))
		{
			$model = RModelAdmin::getInstance('Product_Item', 'RedshopbModel');
			$model->setRetail($itemTable->id, (float) $data['retail_price']);
		}

		return $itemTable->id;
	}

	/**
	 * Method for update an product item
	 *
	 * @param   array  $data  Array of put data
	 *
	 * @return  integer           Product item ID on success. 0 otherwise.
	 */
	public function update($data)
	{
		$app              = Factory::getApplication();
		$productItemId    = $data['id'];
		$productItemTable = RedshopbTable::getAdminInstance('Product_Item');

		// Check if this product item has exist
		if (!$productItemTable->load($productItemId))
		{
			$app->enqueueMessage($productItemTable->getError(), 'error');

			return false;
		}

		// Update product item data
		if (!$productItemTable->save($data))
		{
			$app->enqueueMessage($productItemTable->getError(), 'error');

			return false;
		}

		// Insert price for this product item if avaiable
		if (!empty($data['price']))
		{
			$model = RModelAdmin::getInstance('Product_Item', 'RedshopbModel');
			$model->setPrice($productItemTable->id, (float) $data['price']);
		}

		// Insert retail price for this product item if avaiable
		if (!empty($data['retail_price']))
		{
			$model = RModelAdmin::getInstance('Product_Item', 'RedshopbModel');
			$model->setRetail($productItemTable->id, (float) $data['retail_price']);
		}

		return $productItemTable->id;
	}

	/**
	 * Method for discontinue an product item
	 *
	 * @param   int  $id  ID of product item
	 *
	 * @return  boolean   True on success. False otherwise.
	 */
	public function discontinue($id)
	{
		// Convert to array for use in model
		$id = array($id);

		$model = RModelAdmin::getInstance('Product_Item', 'RedshopbModel');

		return $model->discontinue($id);
	}
}
