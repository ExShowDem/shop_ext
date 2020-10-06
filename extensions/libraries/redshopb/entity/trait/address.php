<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Trait for entities with a common address pattern
 *
 * @since  2.0
 */
trait RedshopbEntityTraitAddress
{
	/**
	 * Item address
	 *
	 * @var    RedshopbEntityAddress
	 * @since  2.0
	 */
	protected $address;

	/**
	 * Get the address associated to this item
	 *
	 * @param   boolean   $fallback   If no address is found, return the department/company address instead
	 *
	 * @return  RedshopbEntityAddress
	 *
	 * @since   2.0
	 */
	public function getAddress($fallback = false)
	{
		if (null === $this->address)
		{
			$this->loadAddress();
		}

		if ($fallback && !$this->address->isLoaded())
		{
			$this->loadAddressFallback();
		}

		return $this->address;
	}

	/**
	 * Load default address from DB
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadAddress()
	{
		$this->address = RedshopbEntityAddress::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->address_id)
		{
			return $this;
		}

		$this->address = RedshopbEntityAddress::load($item->address_id);

		return $this;
	}

	/**
	 * If the fallback is trigger we try to get the department or company address
	 *
	 * @return   self
	 */
	protected function loadAddressFallback()
	{
		$this->determineBillingAddress();

		return $this;
	}

	/**
	 * Determine the billing address
	 *
	 * @return  void
	 */
	abstract protected function determineBillingAddress();
}
