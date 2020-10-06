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
 * Trait for entities with a common address pattern
 *
 * @since  2.0
 */
trait RedshopbEntityTraitAddressShippingDefault
{
	/**
	 * Default shipping address
	 *
	 * @var    RedshopbEntityAddress
	 * @since  2.0
	 */
	protected $defaultShippingAddress;

	/**
	 * Get the default shipping address
	 *
	 * @param   boolean   $fallback   If no address is found, return the department/company address instead
	 *
	 * @return  RedshopbEntityAddress
	 *
	 * @since   2.0
	 */
	public function getDefaultShippingAddress($fallback = false)
	{
		if (null === $this->defaultShippingAddress)
		{
			$this->loadDefaultShippingAddress();
		}

		if ($fallback && !$this->defaultShippingAddress->isLoaded())
		{
			$this->loadDefaultShippingAddressFallback();
		}

		return $this->defaultShippingAddress;
	}

	/**
	 * Load delivery address from database
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	abstract protected function loadDefaultShippingAddress();

	/**
	 * If the fallback is trigger we try to get the department or company address
	 *
	 * @return   self
	 */
	protected function loadDefaultShippingAddressFallback()
	{
		if (is_callable(array($this, 'getDepartment')))
		{
			$this->defaultShippingAddress = $this->getDepartment()->getDefaultShippingAddress(true);

			return $this;
		}

		if (is_callable(array($this, 'getCompany')))
		{
			$this->defaultShippingAddress = $this->getCompany()->getDefaultShippingAddress(true);

			return $this;
		}

		return $this;
	}
}
