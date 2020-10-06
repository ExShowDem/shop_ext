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
 * Trait for entities with a delivery address
 *
 * @since  2.0
 */
trait RedshopbEntityTraitAddressDelivery
{
	/**
	 * Gets the item delivery address.
	 * Defaults to shipping address & falls back to main address.
	 *
	 * @param   boolean   $fallback   If true, triggers the fallback method if no address was found
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDeliveryAddress($fallback = false)
	{
		$address = $this->getDefaultShippingAddress($fallback);

		if (!$address->isLoaded())
		{
			$address = $this->getAddress($fallback);
		}

		return $address;
	}

	/**
	 * Get the address associated to this item
	 *
	 * @see     RedshopbEntityTraitAddress
	 *
	 * @param   boolean   $fallback   If no address is found, return the department/company address instead
	 *
	 * @return  RedshopbEntityAddress
	 */
	abstract public function getAddress($fallback = false);

	/**
	 * Get the default shipping address
	 *
	 * @see     RedshopbEntityTraitAddressShippingDefault
	 *
	 * @param   boolean   $fallback   If no address is found, return the department/company address instead
	 *
	 * @return  RedshopbEntityAddress
	 */
	abstract public function getDefaultShippingAddress($fallback = false);
}
