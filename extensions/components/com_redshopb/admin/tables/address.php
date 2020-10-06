<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Address table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableAddress extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'notSetAddressSeparate' => false
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_address';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $country_id = null;

	/**
	 * @var null|int
	 */
	public $state_id = null;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $name2;

	/**
	 * @var  string
	 */
	public $address;

	/**
	 * @var  string
	 */
	public $address2;

	/**
	 * @var  string
	 */
	public $zip;

	/**
	 * @var  string
	 */
	public $city;

	/**
	 * @var  integer
	 */
	public $type;

	/**
	 * @var  integer
	 */
	public $customer_id;

	/**
	 * @var  string
	 */
	public $customer_type;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var string
	 */
	public $code;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @var string
	 */
	public $phone;

	/**
	 * @var integer
	 */
	public $order;

	/**
	 * @var integer
	 */
	protected $delivery_for_company_id;

	/**
	 * @var integer
	 */
	protected $delivery_for_department_id;

	/**
	 * @var integer
	 */
	protected $delivery_for_user_id;

	/**
	 * @var boolean
	 */
	protected $delivery_default;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.address'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'delivery_for_company_id' => array(
			'model' => 'Companies'
		),
		'delivery_for_department_id' => array(
			'model' => 'Departments'
		),
		'delivery_for_user_id' => array(
			'model' => 'Users'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'country_code' => 'Countries'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'delivery_default'
	);

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if (is_null($updateNulls))
		{
			$updateNulls = true;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		$order = $this->getAddressOrder($this->customer_id, $this->customer_type, $this->type);

		if ($order != 13)
		{
			$this->order = $order;
		}

		if (empty($this->country_id))
		{
			$this->country_id = null;
		}

		if (empty($this->state_id))
		{
			$this->state_id = null;
		}

		if ((int) $this->id > 0 && $this->customer_id == 0 && $this->customer_type == '')
		{
			$this->customer_id   = 0;
			$this->customer_type = '';
		}
		else
		{
			// Check if we are saving default shipping address
			if ((int) $this->type == 3 && $this->customer_id != 0 && $this->customer_type != '')
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select($db->qn('id'))
					->from($db->qn('#__redshopb_address'))
					->where(
						$db->qn('customer_id') . ' = ' . (int) $this->customer_id . ' AND ' . $db->qn('customer_type')
						. ' = ' . $db->q($this->customer_type)
						. ' AND ' . $db->qn('type') . ' = 3'
					);
				$ids = $db->setQuery($query)->loadColumn();

				if (count($ids) > 0)
				{
					$query->clear()
						->update($db->qn('#__redshopb_address'))
						->set($db->qn('type') . ' = 1')
						->set($db->qn('order') . ' = ' . (int) $this->getAddressOrder($this->customer_id, $this->customer_type, 1))
						->where($db->qn('id') . ' IN (' . implode(',', $ids) . ')');
					$db->setQuery($query)->execute();
				}
			}
			// Check if we are trying to make regular address as default shipping
			elseif ((int) $this->type == 3 && (int) $this->id > 0)
			{
				$address  = RedshopbEntityAddress::getInstance($this->id)->bind($this->getProperties());
				$customer = $address->getCustomer();
				$db       = $this->getDbo();
				$query    = $db->getQuery(true);

				if ($customer)
				{
					$this->customer_id   = $customer->getId();
					$this->customer_type = $customer->getType();

					switch ($this->customer_type)
					{
						case 'employee':
							$query->update($db->qn('#__redshopb_user'))
								->set($db->qn('address_id') . ' = NULL')
								->where($db->qn('id') . ' = ' . $this->customer_id);

							break;
						case 'department':
							$query->update($db->qn('#__redshopb_user'))
								->set($db->qn('address_id') . ' = NULL')
								->where($db->qn('id') . ' = ' . $this->customer_id);

							break;
						case 'company':
							$query->update($db->qn('#__redshopb_user'))
								->set($db->qn('address_id') . ' = NULL')
								->where($db->qn('id') . ' = ' . $this->customer_id);

							break;
					}

					$db->setQuery($query)->execute();
				}
				elseif ((int) $this->type == 3)
				{
					$this->setError(Text::_('COM_REDSHOPB_ADDRESS_ENTITY_MISSING'));

					return false;
				}
			}

			// Removing only spaces, NUL-byte and vertical tab from the start or the end
			$this->name     = trim($this->name, " \0\x0B");
			$this->name2    = trim($this->name2, " \0\x0B");
			$this->address  = trim($this->address, " \0\x0B");
			$this->address2 = trim($this->address2, " \0\x0B");
			$this->zip      = trim($this->zip, " \0\x0B");
			$this->city     = trim($this->city, " \0\x0B");
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * Delete Shipping Addresses
	 *
	 * @param   string/array  $pk            Array of ids or ids comma separated
	 * @param   string        $customerType  Customer type
	 *
	 * @return boolean
	 */
	public function deleteShippingAddresses($pk, $customerType)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_address'))
			->where('type <> 2')
			->where('customer_type = ' . $db->q($customerType))
			->where('customer_id IN (' . implode(',', $pk) . ')');
		$db->setQuery($query);

		$addresses = $db->loadColumn();

		if ($addresses)
		{
			foreach ($addresses as $addressId)
			{
				if ($this->load($addressId, true))
				{
					if (!$this->delete($addressId, true))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk                    An optional primary key value to delete.  If not set the instance property value is used.
	 * @param   bool   $deleteAnyTypeAddress  Flag from delete customer address
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null, $deleteAnyTypeAddress = false)
	{
		$pkInner = $pk;

		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		// Check if address is primary delivery address for any orders
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__redshopb_order'))
			->where('delivery_address_id IN (' . $pk . ')');
		$db->setQuery($query);

		$result = $db->loadResult();

		if ($result)
		{
			$this->unlinkAddresses($pkInner);

			return true;
		}
		elseif ($deleteAnyTypeAddress)
		{
			if (!parent::delete($pkInner))
			{
				return false;
			}
		}
		else
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__redshopb_address'))
				->where('id IN (' . $pk . ')')
				->where('type = 2');
			$db->setQuery($query);

			$result = $db->loadResult();

			if ($result)
			{
				$this->setError(Text::_('COM_REDSHOPB_ADDRESS_CANT_DELETE_DEFAULT_ADDRESS'));

				return false;
			}

			if (!parent::delete($pkInner))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset))
		{
			return false;
		}

		$this->delivery_default = false;

		// Next, it populates the ficticial fields for showing which company/department/user is this address bound to
		$address  = RedshopbEntityAddress::getInstance($this->id)->bind($this->getProperties());
		$customer = $address->getCustomer();

		if ($customer && (!isset($this->customer_id) || $this->customer_id == 0 || !isset($this->customer_type) || $this->customer_type == ''))
		{
			$this->customer_id   = $customer->getId();
			$this->customer_type = $customer->getType();
		}

		if (isset($this->customer_id) && isset($this->customer_type) && $this->customer_id > 0
			&& $this->customer_type != '' && RedshopbEntityCustomer::isAllowedType($this->customer_type))
		{
			$this->delivery_for_company_id    = $customer ? $customer->getCompany()->getId() : null;
			$this->delivery_for_department_id = $customer && ($this->customer_type == 'department' || $this->customer_type == 'employee')
												? $customer->getDepartment()->getId() : null;
			$this->delivery_for_user_id       = $customer && ($this->customer_type == 'employee') ? $customer->getUser()->getId() : null;

			if ($this->type == 3)
			{
				$this->delivery_default = true;
			}
		}

		return true;
	}

	/**
	 * Return address order value.
	 *
	 * $addressType:
	 * TYPE_SHIPPING = 1;
	 * TYPE_REGULAR = 2;
	 * TYPE_DEFAULT_SHIPPING = 3;
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 * @param   int     $addressType   Address type.
	 *
	 * @return  integer Address order value.
	 */
	private function getAddressOrder($customerId, $customerType, $addressType)
	{
		$order = 13;

		switch ($customerType)
		{
			case 'employee':
				switch ($addressType)
				{
					case 1:
						$order = 2;
						break;
					case 2:
						$order = 7;
						break;
					case 3:
						$order = 1;
						break;
				}

				break;
			case 'department':
				switch ($addressType)
				{
					case 1:
						$order = 4;
						break;
					case 2:
						$order = 8;
						break;
					case 3:
						$order = 3;
						break;
				}

				break;
			case 'company':
				$customerType = RedshopbHelperCompany::getCompanyById($customerId)->type;

				switch ($addressType)
				{
					case 1:
						if ($customerType == 'end_customer')
						{
							$order = 6;
						}
						elseif ($customerType == 'customer')
						{
							$order = 11;
						}

						break;
					case 2:
						if ($customerType == 'end_customer')
						{
							$order = 9;
						}
						elseif ($customerType == 'customer')
						{
							$order = 12;
						}

						break;
					case 3:
						if ($customerType == 'end_customer')
						{
							$order = 5;
						}
						elseif ($customerType == 'customer')
						{
							$order = 10;
						}

						break;
				}

				break;
			default:
				$order = 13;
		}

		return $order;
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		$user = Factory::getUser();

		if (!$user->guest || $this->getOption('forceWebserviceUpdate') === true)
		{
			// We only do these checks if this is a guest
			return true;
		}

		if (empty($this->address))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_MISSING_ADDRESS'));

			return false;
		}

		if (empty($this->city))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_MISSING_CITY'));

			return false;
		}

		if (empty($this->zip))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_MISSING_ZIP'));

			return false;
		}

		if (empty($this->country_id))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_MISSING_COUNTRY'));

			return false;
		}

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_MISSING_NAME'));

			return false;
		}

		if (empty($this->email))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_MISSING_EMAIL'));

			return false;
		}

		if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false)
		{
			$this->setError(Text::_('COM_REDSHOPB_SHOP_ADDRESS_INVALID_EMAIL'));

			return false;
		}

		return true;
	}

	/**
	 * Unlinks one or multiple addresses from its customer
	 *
	 * @param   mixed  $pk  One or more address IDs
	 *
	 * @throws RuntimeException  {@see} JDatabaseDriver::execute()
	 *
	 * @return  void
	 */
	private function unlinkAddresses($pk)
	{
		$db  = Factory::getDbo();
		$pks = $pk;

		if (!is_array($pk))
		{
			$pks = array($pk);
		}

		foreach ($pks AS $id)
		{
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__redshopb_order'))
				->where($db->qn('delivery_address_id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			$result = $db->loadResult();

			if ($result)
			{
				$query = $db->getQuery(true)
					->update($db->qn('#__redshopb_address'))
					->set($db->qn('customer_id') . ' = ' . $db->q(0))
					->where($db->qn('id') . ' = ' . $db->q($id));

				$db->setQuery($query)->execute();
			}
			else
			{
				$this->delete($id);
			}
		}
	}
}
