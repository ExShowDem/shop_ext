<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Address helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperAddress
{
	/**
	 * Get entity Shipping Addresses
	 *
	 * @param   int     $entityId    Entity id
	 * @param   string  $entityType  Entity type
	 * @param   bool    $idsOnly     Only ids of the whole record
	 *
	 * @return  array.
	 */
	public static function getAllShippingAddresses($entityId, $entityType, $idsOnly = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			array(
				'a.*',
				$db->qn('co.name', 'country')
			)
		)
			->select($db->qn('a.id', 'id'))
			->from($db->qn('#__redshopb_address', 'a'))
			->join('inner', $db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->where($db->qn('a.customer_id') . ' = ' . (int) $entityId)
			->where($db->qn('a.customer_type') . ' = ' . $db->q($entityType))
			->where($db->qn('a.type') . ' = 1');

		switch ($entityType)
		{
			case 'employee':
				$query->select($db->qn('u.name1', 'entity'))
					->join('inner', $db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('a.customer_id'));
				break;

			case 'department':
				$query->select($db->qn('d.name', 'entity'))
					->join('inner', $db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('d.id') . ' = ' . $db->qn('a.customer_id'));
				break;

			case 'company':
				$query->select($db->qn('c.name', 'entity'))
					->join('inner', $db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.customer_id'));
				break;
		}

		$db->setQuery($query);

		if ($idsOnly)
		{
			return $db->loadColumn(0);
		}
		else
		{
			return $db->loadObjectList();
		}
	}

	/**
	 *  Validate web service data for deliveryAddressAdd / deliveryAddressDefault function
	 *
	 * @param   integer  $addressId   Address to be validated
	 * @param   integer  $entityId    Entity id to be checked
	 * @param   string   $entityType  Entity type to be checked (company / department / employee)
	 *
	 * @return  boolean
	 */
	public static function validateDeliveryAddressAddWS($addressId, $entityId, $entityType)
	{
		$addressModel = RedshopbModelAdmin::getFrontInstance('Address');
		$address      = $addressModel->getItem($addressId);

		// Address already attached to this entity
		if (($address->type == 1 || $address->type == 3)
			&& $address->customer_id == $entityId
			&& $address->customer_type == $entityType)
		{
			return true;
		}

		// Considers a valid address only if it's anonymous - no entity attached
		if ($address->type != 1
			|| $address->customer_id != 0
			|| $address->customer_type != '')
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_REDSHOPB_ADDRESS_ATTACHED', $address->id, $address->customer_type, $address->customer_id), 'error'
			);

			return false;
		}

		return true;
	}

	/**
	 *  Validate web service data for deliveryAddressRemove function
	 *
	 * @param   integer  $addressId   Address to be validated
	 * @param   integer  $entityId    Entity id to be checked
	 * @param   string   $entityType  Entity type to be checked (company / department / employee)
	 *
	 * @return  boolean
	 */
	public static function validateDeliveryAddressRemoveWS($addressId, $entityId, $entityType)
	{
		$addressModel = RedshopbModelAdmin::getFrontInstance('Address');
		$address      = $addressModel->getItem($addressId);

		// Address is in fact attached to this entity
		if (($address->type == 1 || $address->type == 3)
			&& $address->customer_id == $entityId
			&& $address->customer_type == $entityType)
		{
			return true;
		}

		Factory::getApplication()->enqueueMessage(
			Text::sprintf('COM_REDSHOPB_ADDRESS_NOT_ATTACHED', $addressId, $entityType, $entityId), 'error'
		);

		return false;
	}

	/**
	 *  Add a delivery address to an entity
	 *
	 * @param   integer  $addressId   Address
	 * @param   integer  $entityId    Entity id
	 * @param   string   $entityType  Entity type (company / department / employee)
	 *
	 * @return  boolean
	 */
	public static function deliveryAddressAdd($addressId, $entityId, $entityType)
	{
		$addressTable = RedshopbTable::getAdminInstance('Address');

		if ($addressTable->load($addressId))
		{
			// If the address belongs to the current entity, it doesn't do anything
			if ($addressTable->customer_id == $entityId
				&& $addressTable->customer_type == $entityType)
			{
				return true;
			}

			if ($addressTable->save(
				array(
					'customer_id' => $entityId,
					'customer_type' => $entityType,
					'type' => 1
				)
			))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 *  Remove a delivery address from its entity
	 *
	 * @param   int  $addressId  id of address table
	 *
	 * @return  boolean
	 */
	public static function deliveryAddressRemove($addressId)
	{
		$addressTable = RedshopbTable::getAdminInstance('Address');

		if ($addressTable->load($addressId))
		{
			if ($addressTable->save(
				array(
					'customer_id' => 0,
					'customer_type' => '',
					'type' => 1
				)
			))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 *  Sets an address as the delivery address of a certain entity
	 *
	 * @param   integer  $addressId   Address
	 * @param   integer  $entityId    Entity id
	 * @param   string   $entityType  Entity type (company / department / employee)
	 *
	 * @return  boolean
	 */
	public static function deliveryAddressDefault($addressId, $entityId, $entityType)
	{
		$addressTable = RedshopbTable::getAdminInstance('Address');

		// Sets any existing default address for that entity as a regular delivery address
		if ($addressTable->load(
			array(
				'customer_id' => $entityId,
				'customer_type' => $entityType,
				'type' => 3
			)
		))
		{
			// If the default address is already the one to be set, it doesn't do anything
			if ($addressTable->id == $addressId)
			{
				return true;
			}

			if (!$addressTable->save(
				array(
					'type' => 1
				)
			))
			{
				return false;
			}
		}

		$addressTable->id = null;
		$addressTable->reset();

		if ($addressTable->load($addressId))
		{
			if ($addressTable->save(
				array(
					'customer_id' => $entityId,
					'customer_type' => $entityType,
					'type' => 3
				)
			))
			{
				return true;
			}
		}

		return false;
	}
}
