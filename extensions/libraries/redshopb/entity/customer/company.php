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
 * Customer Entity.
 *
 * @since  2.0
 */
class RedshopbEntityCustomerCompany extends RedshopbEntityCustomerAbstract
{
	/**
	 * Type of customer
	 *
	 * @var  string
	 */
	protected $type = RedshopbEntityCustomer::TYPE_COMPANY;

	/**
	 * Get the customer main address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getAddress()
	{
		return $this->getCompany()->getAddress();
	}

	/**
	 * Get the customer company
	 *
	 * @return  RedshopbEntityCompany
	 */
	public function getCompany()
	{
		return RedshopbEntityCompany::getInstance($this->id);
	}

	/**
	 * Get the customer shipping address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDefaultShippingAddress()
	{
		return $this->getCompany()->getDefaultShippingAddress();
	}

	/**
	 * Gets the customer delivery address.
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDeliveryAddress()
	{
		return $this->getCompany()->getDeliveryAddress();
	}

	/**
	 * Get all the customer shipping addresses
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getShippingAddresses()
	{
		return $this->getCompany()->getShippingAddresses();
	}
}
