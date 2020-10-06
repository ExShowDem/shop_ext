<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * Customer Entity.
 *
 * @since  2.0
 */
abstract class RedshopbEntityCustomer
{
	/**
	 * @const  integer
	 */
	const TYPE_EMPLOYEE = 'employee';

	/**
	 * @const  integer
	 */
	const TYPE_COMPANY = 'company';

	/**
	 * @const  integer
	 */
	const TYPE_DEPARTMENT = 'department';

	/**
	 * Cached instances
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * Get the customer types allowed by the system
	 *
	 * @return  array
	 */
	public static function getAllowedTypes()
	{
		return array(
			static::TYPE_COMPANY    => Text::_('COM_REDSHOPB_COMPANY'),
			static::TYPE_DEPARTMENT => Text::_('COM_REDSHOPB_DEPARTMENT'),
			static::TYPE_EMPLOYEE   => Text::_('COM_REDSHOPB_EMPLOYEE')
		);
	}

	/**
	 * Create and return a cached instance
	 *
	 * @param   integer  $id    Identifier of the active item
	 * @param   string   $type  Type of customer
	 *
	 * @throws  Exception
	 *
	 * @return  RedshopbEntityCustomerEmployee|RedshopbEntityCustomerDepartment|RedshopbEntityCustomerCompany
	 */
	public static function getInstance($id, $type)
	{
		$type = strtolower($type);

		if (!static::isAllowedType($type))
		{
			throw new Exception(Text::sprintf('COM_REDSHOPB_SHOP_CUSTOMER_TYPE_UNSUPPORTED', $type));
		}

		$customerClass = 'RedshopbEntityCustomer' . ucfirst($type);

		if (!class_exists($customerClass))
		{
			throw new Exception(Text::sprintf('COM_REDSHOPB_SHOP_CUSTOMER_HANDLER_NOT_FOUND', $customerClass));
		}

		if (empty(static::$instances[$customerClass][$id]))
		{
			static::$instances[$customerClass][$id] = new $customerClass($id);
		}

		return static::$instances[$customerClass][$id];
	}

	/**
	 * Get the translated name of a customer type
	 *
	 * @param   string  $type  Translated string for a customer type
	 *
	 * @return  string
	 */
	public static function getTypeName($type)
	{
		$type = strtolower($type);

		$allowedTypes = static::getAllowedTypes();

		if (!isset($allowedTypes[$type]))
		{
			return null;
		}

		return $allowedTypes[$type];
	}

	/**
	 * Check if a customer type is valid
	 *
	 * @param   string  $type  Customer type
	 *
	 * @return  boolean
	 */
	public static function isAllowedType($type)
	{
		$allowedTypes = array_keys(static::getAllowedTypes());

		return in_array($type, $allowedTypes);
	}
}
