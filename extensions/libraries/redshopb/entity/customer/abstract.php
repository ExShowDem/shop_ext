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
abstract class RedshopbEntityCustomerAbstract implements RedshopbEntityCustomerInterface
{
	/**
	 * Type of customer
	 *
	 * @var  string
	 */
	protected $type;

	/**
	 * Id of the customer
	 *
	 * @var  integer
	 */
	protected $id;

	/**
	 * Constructor
	 *
	 * @param   mixed  $id  Identifier of the active item
	 */
	public function __construct($id)
	{
		$this->id = (int) $id;
	}

	/**
	 * Get the default main address of this customer
	 *
	 * @return RedshopbEntityAddress
	 */
	abstract public function getAddress();

	/**
	 * Get the default shipping address of this customer
	 *
	 * @return RedshopbEntityAddress
	 */
	abstract public function getDefaultShippingAddress();

	/**
	 * Gets the customer delivery address.
	 *
	 * @return  RedshopbEntityAddress
	 */
	abstract public function getDeliveryAddress();

	/**
	 * Get the customer identifier
	 *
	 * @return  integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get all the customer shipping addresses
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	abstract public function getShippingAddresses();

	/**
	 * Get the customer type
	 *
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get the translated name of this type
	 *
	 * @return  string
	 */
	public function getTypeName()
	{
		return RedshopbEntityCustomer::getTypeName($this->getType());
	}
}
