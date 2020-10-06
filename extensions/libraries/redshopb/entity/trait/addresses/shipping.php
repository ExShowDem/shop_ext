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
 * Trait for entities with shipping addresses
 *
 * @since  2.0
 */
trait RedshopbEntityTraitAddressesShipping
{
	/**
	 * Extra shipping addresses
	 *
	 * @var    RedshopbEntitiesCollection
	 * @since  2.0
	 */
	protected $shippingAddresses;

	/**
	 * Get the available shipping addresses
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function getShippingAddresses()
	{
		if (null === $this->shippingAddresses)
		{
			$this->loadShippingAddresses();
		}

		return $this->shippingAddresses;
	}


	/**
	 * Get the available shipping addresses
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	abstract protected function loadShippingAddresses();
}
