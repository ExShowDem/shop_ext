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

/**
 * [PlgVanirSearchSolrEntityCategory description]
 *
 * @since __VERSION__
 */
class PlgVanirSearchSolrEntityCategory extends RedshopbEntityCategory
{
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

		$item                = new stdClass;
		$item->id            = 'category.' . $this->id;
		$item->record_type   = 'category';
		$item->category_id   = $this->id;
		$item->category_name = PlgVanirSearchSolrSync::prepareText($this->item->name, $fieldLimit);

		if (!empty(trim($this->item->description)))
		{
			$item->category_description = PlgVanirSearchSolrSync::prepareText($this->item->description, $fieldLimit);
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
		$item->cagetory_id          = 0;
		$item->category_name        = 'null';
		$item->category_description = 'null';

		return $item;
	}

	/**
	 * Method to get index record ID for the initial item used to define the SOLR schema
	 *
	 * @return string
	 */
	public function getSchemaItemId()
	{
		return 'category.0';
	}
}
