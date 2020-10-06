<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for entities with a product
 *
 * @since  2.0
 */
trait RedshopbEntityTraitProduct
{
	/**
	 * Product where this item belongs to
	 *
	 * @var  RedshopbEntityProduct
	 */
	protected $product;

	/**
	 * Get the parent product
	 *
	 * @return  RedshopbEntityProduct
	 */
	public function getProduct()
	{
		if (null === $this->product)
		{
			$this->loadProduct();
		}

		return $this->product;
	}

	/**
	 * Load parent product from DB
	 *
	 * @return  self
	 */
	protected function loadProduct()
	{
		$this->product = RedshopbEntityProduct::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->product_id)
		{
			return $this;
		}

		$this->product = RedshopbEntityProduct::load($item->product_id);

		return $this;
	}
}
