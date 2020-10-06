<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Stockroom Entity.
 *
 * @since  2.0
 */
class RedshopbEntityStockroom extends RedshopbEntity
{
	/**
	 * @var object
	 */
	protected $stockroomAddress;

	/**
	 * Get stockRoomAddress
	 *
	 * @return  mixed
	 */
	public function getAddress()
	{
		if (!$this->hasId())
		{
			return false;
		}

		if (!empty($this->stockroomAddress))
		{
			return $this->stockroomAddress;
		}

		$db              = Factory::getDbo();
		$subQueryCompany = $db->getQuery(true)
			->select('c.address_id')
			->from($db->qn('#__redshopb_company', 'c'))
			->leftJoin($db->qn('#__redshopb_stockroom', 's2') . ' ON s2.company_id = c.id')
			->where('s2.id = ' . (int) $this->id);

		$subQueryMainCompany = $db->getQuery(true)
			->select('c2.address_id')
			->from($db->qn('#__redshopb_company', 'c2'))
			->where('c2.type = ' . $db->q('main'));

		$addressId = $this->get('address_id');

		if (is_null($addressId))
		{
			$addressId = 'NULL';
		}
		else
		{
			$addressId = (int) $addressId;
		}

		$query = $db->getQuery(true)
			->select('a.*, country.name AS country')
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin($db->qn('#__redshopb_country', 'country') . ' ON country.id = a.country_id')
			->where('a.id = COALESCE((' . $addressId . '), (' . $subQueryCompany . '), (' . $subQueryMainCompany . '))');

		$result = $db->setQuery($query)
			->loadObject();

		if ($result)
		{
			$this->stockroomAddress = $result;

			return $result;
		}

		return $this;
	}

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __get($property)
	{
		if ($property == 'stockroomAddress')
		{
			return parent::getAddress();
		}
		else
		{
			return parent::__get($property);
		}
	}
}
