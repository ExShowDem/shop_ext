<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Product_Attribute Entity.
 *
 * @since  2.0
 */
class RedshopbEntityProduct_Attribute extends RedshopbEntity
{
	use RedshopbEntityTraitProduct;

	/**
	 * Type of attribute
	 *
	 * @var  RedshopbEntityType
	 */
	protected $type;

	/**
	 * Check if this attribute is conversion a conversion set
	 *
	 * @return  boolean
	 */
	public function isConversionSet()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return ((int) $item->conversion_sets === 1);
	}

	/**
	 * Check if this attribute is a main attribute
	 *
	 * @return  boolean
	 */
	public function isMain()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return ((int) $item->main_attribute === 1);
	}

	/**
	 * Get the type of this attribute
	 *
	 * @return  RedshopbEntityType
	 */
	public function getType()
	{
		if (null === $this->type)
		{
			$this->loadType();
		}

		return $this->type;
	}

	/**
	 * Load the attribute type from DB
	 *
	 * @return  self
	 */
	protected function loadType()
	{
		$this->type = RedshopbEntityType::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->type_id)
		{
			return $this;
		}

		$this->type = RedshopbEntityType::load($item->type_id);

		return $this;
	}
}
