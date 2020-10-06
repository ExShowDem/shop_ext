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
 * Customer interface
 *
 * @since  2.0
 */
interface RedshopbEntityCustomerInterface
{
	/**
	 * Get the customer main address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getAddress();

	/**
	 * Get the customer company
	 *
	 * @return  RedshopbEntityCompany
	 */
	public function getCompany();

	/**
	 * Get this customer shipping address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDefaultShippingAddress();

	/**
	 * Get this customer delivery address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getDeliveryAddress();

	/**
	 * Get the customer identifier
	 *
	 * @return  integer
	 */
	public function getId();

	/**
	 * Get all the customer shipping addresses
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getShippingAddresses();

	/**
	 * Get the type of customer
	 *
	 * @return  string
	 */
	public function getType();
}
