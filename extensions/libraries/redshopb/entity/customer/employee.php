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
 * Employee customer entity.
 *
 * @since  2.0
 */
class RedshopbEntityCustomerEmployee extends RedshopbEntityCustomerAbstract
{
	/**
	 * Type of customer
	 *
	 * @var  string
	 */
	protected $type = RedshopbEntityCustomer::TYPE_EMPLOYEE;

	/**
	 * Get the customer main address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getAddress()
	{
		return $this->getUser()->getAddress();
	}

	/**
	 * Get the customer company
	 *
	 * @return  RedshopbEntityCompany
	 */
	public function getCompany()
	{
		return $this->getUser()->getCompany();
	}

	/**
	 * Get the customer department
	 *
	 * @return  RedshopbEntityDepartment
	 */
	public function getDepartment()
	{
		return $this->getUser()->getDepartment();
	}

	/**
	 * Get the customer shipping address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDefaultShippingAddress()
	{
		return $this->getUser()->getDefaultShippingAddress();
	}

	/**
	 * Gets the customer delivery address.
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDeliveryAddress()
	{
		return $this->getUser()->getDeliveryAddress();
	}

	/**
	 * Get all the customer shipping addresses
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getShippingAddresses()
	{
		return $this->getUser()->getShippingAddresses();
	}

	/**
	 * Get the associated user
	 *
	 * @return  RedshopbEntityUser
	 */
	public function getUser()
	{
		return RedshopbEntityUser::getInstance($this->id);
	}
}
