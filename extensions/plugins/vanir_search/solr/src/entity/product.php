<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * [PlgVanirSearchSolrEntityProduct description]
 *
 * @since __VERSION__
 */
class PlgVanirSearchSolrEntityProduct extends RedshopbEntityProduct
{
	/**
	 * @var array
	 * @since 2.0
	 */
	protected $productItemSku;

	/**
	 * Get item from the database
	 *
	 * @param   int  $fieldLimit   the maximum length of entity fields
	 *
	 * @return  mixed  stdClass
	 */
	public function getIndexItem($fieldLimit)
	{
		if (empty($this->item))
		{
			$this->loadItem();
		}

		$item = new stdClass;

		$item->id           = 'product.' . $this->id;
		$item->product_id   = $this->id;
		$item->product_name = $this->normalizeText($this->item->name, $fieldLimit);
		$item->product_sku  = $this->normalizeText($this->item->sku, $fieldLimit);
		$item->related_sku  = $this->normalizeText($this->item->related_sku, $fieldLimit);

		if (!empty($item->manufacturer_sku))
		{
			$item->manufacturer_sku = $this->normalizeText($this->item->manufacturer_sku, $fieldLimit);
		}

		$itemCategories       = $this->getCategories();
		$categoryTitles       = array();
		$categoryDescriptions = array();
		/**
		 * @var  int $cat_id
		 * @var  RedshopbEntityCategory $category
		 */
		foreach ($itemCategories AS $category)
		{
			$categoryTitles[]       = $this->normalizeText($category->get('name'), $fieldLimit);
			$categoryDescriptions[] = $this->normalizeText($category->get('description'), $fieldLimit);
		}

		if (!empty($categoryTitles))
		{
			$item->category_name = $categoryTitles;
		}

		if (!empty($categoryDescriptions))
		{
			$item->category_description = $categoryDescriptions;
		}

		$itemDesc     = $this->getDescriptions();
		$descriptions = array();

		foreach ($itemDesc AS $description)
		{
			$description = $this->normalizeText($description->get('description'), $fieldLimit);

			if (!empty($description))
			{
				$descriptions[] = $description;
			}
		}

		if (!empty($descriptions))
		{
			$item->product_description = $descriptions;
		}

		$manufacturer = $this->getManufacturer();

		if (!empty($manufacturer))
		{
			$item->manufacturer_name = $this->normalizeText($manufacturer->get('name'), $fieldLimit);
		}

		$itemTags = $this->getTags();
		$tags     = array();

		foreach ($itemTags AS $tag)
		{
			$tag = $this->normalizeText($tag->get('name'), $fieldLimit);

			if (!empty($tag))
			{
				$tags[] = $tag;
			}
		}

		if (!empty($tags))
		{
			$item->tags = $tags;
		}

		$itemImages  = $this->getImages();
		$imageAltTxt = array();

		foreach ($itemImages AS $image)
		{
			$altText = $image->get('alt');

			if (empty($altText))
			{
				continue;
			}

			$imageAltTxt[] = $this->normalizeText($altText, $fieldLimit);
		}

		if (!empty($imageAltTxt))
		{
			$item->image_alt_text = $imageAltTxt;
		}

		$productItemSku = $this->getProductItemSku();

		if (!empty($productItemSku))
		{
			$item->product_item_sku = $productItemSku;
		}

		$extraFields = $this->getFields();

		/** @var RedshopbEntityField $field */
		foreach ($extraFields AS $field)
		{
			$fieldData = $field->getFieldData('product', $this->id);
			$fItem     = $field->getItem();
			$type      = $field->getType();

			$value = array();

			switch ($type->value_type)
			{
				case 'float_value':
				case 'int_value':
					if (empty($fieldData->value))
					{
						break;
					}

					foreach ($fieldData AS $dataValue)
					{
						$value[] = $dataValue->value;
					}

				default:
					if (empty($fieldData))
					{
						break;
					}

					foreach ($fieldData AS $dataValue)
					{
						$value[] = $this->normalizeText($dataValue->value, $fieldLimit);
					}

					break;
			}

			if (empty($value))
			{
				continue;
			}

			if (empty($fItem->multiple_values))
			{
				$item->{$field->getId()} = array_shift($value);
				continue;
			}

			$item->{$field->getId()} = $value;
		}

		foreach ((array) $item as $key => $value)
		{
			$item->{$key . '_ac'} = $value;
		}

		return $item;
	}

	/**
	 * Method to get an empty item with properites and data values consistent with extra field definitions
	 *
	 * @return stdClass
	 */
	public function getSchemaItem()
	{
		$item = new stdClass;

		$item->id                   = $this->getSchemaItemId();
		$item->product_id           = 0;
		$item->product_name         = 'NULL';
		$item->product_sku          = 'NULL';
		$item->related_sku          = 'NULL';
		$item->manufacturer_sku     = 'NULL';
		$item->category_name        = array('NULL');
		$item->category_description = array('NULL');
		$item->product_description  = array('NULL');
		$item->manufacturer_name    = 'NULL';
		$item->tags                 = array('NULL');
		$item->image_alt_text       = array('NULL');
		$item->product_item_sku     = array('NULL');

		$extraFields = $this->getFields();

		/** @var RedshopbEntityField $field */
		foreach ($extraFields AS $field)
		{
			$fItem = $field->getItem();
			$type  = $field->getType();

			switch ($type->value_type)
			{
				case 'float_value':
					$value = 0.0;
					break;

				case 'int_value':
					$value = 0;
					break;
				default:
					$value = 'NULL';
					break;
			}

			if ($fItem->multiple_values)
			{
				$value = array($value);
			}

			$item->{$field->getId()} = $value;
		}

		return $item;
	}

	/**
	 * Method to get index record ID for the initial item used to define the SOLR schema
	 *
	 * @return string
	 */
	public function getSchemaItemId()
	{
		return 'product.0';
	}

	/**
	 * Method to normalize UTF8 text
	 *
	 * @param   string  $text        Text to normalize
	 * @param   int     $fieldLimit  the maximum length of the text
	 *
	 * @return string
	 */
	private function normalizeText($text, $fieldLimit)
	{
		$normalizedText = $text;

		if (empty($normalizedText) || !is_string($normalizedText))
		{
			if (strlen($normalizedText) > $fieldLimit)
			{
				$normalizedText = substr($normalizedText, 0, $fieldLimit);
			}

			return $normalizedText;
		}

		$normalizedText = PlgVanirSearchSolrSync::prepareText($normalizedText, $fieldLimit);

		return $normalizedText;
	}

	/**
	 * getProductItemSku
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function getProductItemSku()
	{
		if (null === $this->productItemSku)
		{
			$this->productItemSku = $this->searchProductItemSku();
		}

		return $this->productItemSku;
	}

	/**
	 * Search on this product item SKU
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function searchProductItemSku()
	{
		if (!$this->hasId())
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('sku')
			->from($db->qn('#__redshopb_product_item'))
			->where('product_id = ' . (int) $this->id);

		return $db->setQuery($query)
			->loadColumn();
	}
}
