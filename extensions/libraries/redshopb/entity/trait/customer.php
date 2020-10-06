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
 * Trait for entities that can be tied to a customer id/type
 *
 * @since  1.13.2
 */
trait RedshopbEntityTraitCustomer
{
	/**
	 * Customer entity
	 *
	 * @var  RedshopbEntityCustomerEmployee|RedshopbEntityCustomerDepartment|RedshopbEntityCustomerCompany
	 */
	protected static $customer;

	/**
	 * Get the customer this item is tied to
	 *
	 * @return  RedshopbEntityCustomerEmployee|RedshopbEntityCustomerDepartment|RedshopbEntityCustomerCompany
	 */
	public static function getCustomer()
	{
		if (null === self::$customer)
		{
			self::loadCustomer();
		}

		return self::$customer;
	}

	/**
	 * Load customer
	 *
	 * @return  void
	 */
	protected static function loadCustomer()
	{
		$app  = Factory::getApplication();
		$id   = $app->getUserState('shop.customer_id',  0);
		$type = $app->getUserState('shop.customer_type', '');

		self::$customer = RedshopbEntityCustomer::getInstance($id, $type);
	}
}
