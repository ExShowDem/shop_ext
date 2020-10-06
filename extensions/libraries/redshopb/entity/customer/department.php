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
 * Department customer entity.
 *
 * @since  2.0
 */
class RedshopbEntityCustomerDepartment extends RedshopbEntityCustomerAbstract
{
	/**
	 * Type of customer
	 *
	 * @var  string
	 */
	protected $type = RedshopbEntityCustomer::TYPE_DEPARTMENT;

	/**
	 * Get the customer main address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getAddress()
	{
		return $this->getDepartment()->getAddress();
	}

	/**
	 * Get the customer company
	 *
	 * @return  RedshopbEntityCompany
	 */
	public function getCompany()
	{
		return $this->getDepartment()->getCompany();
	}

	/**
	 * Get the customer shipping address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDefaultShippingAddress()
	{
		return $this->getDepartment()->getDefaultShippingAddress();
	}

	/**
	 * Gets the customer delivery address.
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDeliveryAddress()
	{
		return $this->getDepartment()->getDeliveryAddress();
	}

	/**
	 * Get the associated department
	 *
	 * @return  RedshopbEntityDepartment
	 */
	public function getDepartment()
	{
		return RedshopbEntityDepartment::getInstance($this->id);
	}

	/**
	 * Get all the customer shipping addresses
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getShippingAddresses()
	{
		return $this->getDepartment()->getShippingAddresses();
	}
}
