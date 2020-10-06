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
 * Address Entity.
 *
 * @since  2.0
 *
 * @property string $address
 * @property string $address2
 * @property string $zip
 * @property string $city
 * @property string $phone
 * @property string $email
 */
class RedshopbEntityAddress extends RedshopbEntity
{
	/**
	 * @const  integer
	 * @since  2.0
	 */
	const TYPE_SHIPPING = 1;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const TYPE_REGULAR = 2;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const TYPE_DEFAULT_SHIPPING = 3;

	/**
	 * Country where this address belongs to
	 *
	 * @var    RedshopbEntityCountry
	 * @since  2.0
	 */
	protected $country;

	/**
	 * State where this address belongs to
	 *
	 * @var    RedshopbEntityCountry
	 * @since  2.0
	 */
	protected $state;

	/**
	 * Related customer
	 *
	 * @var  mixed
	 */
	protected $customer;

	/**
	 * Get address country
	 *
	 * @return  RedshopbEntityCountry
	 *
	 * @since   2.0
	 */
	public function getCountry()
	{
		if (null === $this->country)
		{
			$this->loadCountry();
		}

		return $this->country;
	}

	/**
	 * Get address state
	 *
	 * @return  RedshopbEntityState
	 *
	 * @since   2.0
	 */
	public function getState()
	{
		if (null === $this->state)
		{
			$this->loadState();
		}

		return $this->state;
	}

	/**
	 * Get the customer associated to this address
	 *
	 * @return  mixed  Null if not found | RedshopbCustomerInterface otherwise
	 */
	public function getCustomer()
	{
		if (null === $this->customer)
		{
			$this->loadCustomer();
		}

		return $this->customer;
	}

	/**
	 * Get extended data from this address. Required for B/C with old helper calls.
	 *
	 * @return  stdClass
	 *
	 * @since   2.0
	 */
	public function getExtendedData()
	{
		$item = $this->getItem();

		if ($item
			&& $item->customer_type === 'company'
			&& RedshopbEntityCompany::load($item->customer_id)->__isset('hide_company')
			&& RedshopbEntityCompany::load($item->customer_id)->get('hide_company'))
		{
			$item = self::getInstance(RedshopbHelperUser::getUser()->addressId)->getItem();
		}

		if (!$item)
		{
			return new stdClass;
		}

		$item->delivery_address_id = $item->id;
		$item->address_code        = $item->code;
		$item->address_type        = $item->customer_type;
		$item->country             = null;
		$item->country_code        = null;
		$item->state_name          = null;
		$item->state_code          = null;
		$country                   = $this->getCountry();
		$state                     = $this->getState();

		if ($country->isLoaded())
		{
			$item->country      = Text::_($country->get('name'));
			$item->country_code = $country->get('alpha2');
		}

		if ($state->isLoaded())
		{
			$item->state_name = $state->get('name');
			$item->state_code = $state->get('alpha2');
		}

		return $item;
	}

	/**
	 * Get the name of this type of address
	 *
	 * @param   integer  $typeId  Address type identifier
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getTypeName($typeId = null)
	{
		if (null === $typeId)
		{
			$item = $this->getItem();

			$typeId = $item ? $item->type : null;
		}

		$typeId = (int) $typeId;

		switch ($typeId)
		{
			case static::TYPE_SHIPPING:
				return Text::_('JOPTION_SELECT_ADDRESS_SHIPPING');
				break;
			case static::TYPE_REGULAR:
				return Text::_('JOPTION_SELECT_ADDRESS_REGULAR');
				break;
			case static::TYPE_DEFAULT_SHIPPING:
				return Text::_('JOPTION_SELECT_ADDRESS_DEFAULT_SHIPPING');
				break;
			default:
				return Text::_('JOPTION_SELECT_ADDRESS_UNKNOWN');
				break;
		}
	}

	/**
	 * Load country info from DB
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadCountry()
	{
		$this->country = RedshopbEntityCountry::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->country_id)
		{
			return $this;
		}

		$this->country = RedshopbEntityCountry::load($item->country_id);

		return $this;
	}

	/**
	 * Load state info from DB
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadState()
	{
		$this->state = RedshopbEntityState::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->state_id)
		{
			return $this;
		}

		$this->state = RedshopbEntityState::load($item->state_id);

		return $this;
	}

	/**
	 * Load customer associated to this address
	 *
	 * @return  self
	 */
	protected function loadCustomer()
	{
		$item           = $this->getItem();
		$this->customer = false;

		if (!$item)
		{
			return $this;
		}

		if (!RedshopbEntityCustomer::isAllowedType($item->customer_type))
		{
			return $this;
		}

		// Address has all the information we need
		if ($item->customer_id)
		{
			$this->customer = RedshopbEntityCustomer::getInstance($item->customer_id, $item->customer_type);

			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('u.id', 'user_id'),
					$db->qn('d.id', 'department_id'),
					$db->qn('c.id', 'company_id')
				)
			)
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('u.address_id'))
			->leftJoin(
				$db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('a.id') . ' = ' .
				$db->qn('d.address_id') . ' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
			)
			->leftJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('a.id') . ' = ' .
				$db->qn('c.address_id') . ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->where($db->qn('a.id') . ' = ' . (int) $this->id);

		$db->setQuery($query, 0, 1);

		$relationships = $db->loadObject();

		if (!$relationships)
		{
			return $this;
		}

		// There is a related user
		if ($relationships->user_id)
		{
			$this->customer = RedshopbEntityCustomer::getInstance($relationships->user_id, RedshopbEntityCustomer::TYPE_EMPLOYEE);

			return $this;
		}

		// There is a related department
		if ($relationships->department_id)
		{
			$this->customer = RedshopbEntityCustomer::getInstance($relationships->department_id, RedshopbEntityCustomer::TYPE_DEPARTMENT);

			return $this;
		}

		// There is a related company
		if ($relationships->company_id)
		{
			$this->customer = RedshopbEntityCustomer::getInstance($relationships->company_id, RedshopbEntityCustomer::TYPE_COMPANY);

			return $this;
		}

		return $this;
	}
}
