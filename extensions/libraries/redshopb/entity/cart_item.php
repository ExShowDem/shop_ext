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
 * Cart Item Entity.
 *
 * @since  2.0
 */
class RedshopbEntityCart_Item extends RedshopbEntity
{
	use RedshopbEntityTraitProduct;

	/**
	 * Cart where this item belongs to
	 *
	 * @var  RedshopbEntityCart
	 */
	protected $cart;

	/**
	 * Get the parent cart
	 *
	 * @return  RedshopbEntityCart
	 */
	public function getCart()
	{
		if (null === $this->cart)
		{
			$this->loadCart();
		}

		return $this->cart;
	}

	/**
	 * Load parent cart from DB
	 *
	 * @return  self
	 */
	protected function loadCart()
	{
		$this->cart = RedshopbEntityCart::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->cart_id)
		{
			return $this;
		}

		$this->cart = RedshopbEntityCart::load($item->cart_id);

		return $this;
	}
}
