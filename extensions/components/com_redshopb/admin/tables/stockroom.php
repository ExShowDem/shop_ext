<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Stockroom table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableStockroom extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_stockroom';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $color;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  integer
	 */
	public $pick_up = 0;

	/**
	 * @var  integer
	 */
	public $company_id = null;

	/**
	 * @var  integer
	 */
	public $address_id = null;

	/**
	 * @var  integer
	 */
	public $min_delivery_time;

	/**
	 * @var  integer
	 */
	public $max_delivery_time;

	/**
	 * @var  float
	 */
	public $stock_upper_level;

	/**
	 * @var  float
	 */
	public $stock_lower_level;

	/**
	 * @var  integer
	 */
	public $ordering;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  datetime
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  datetime
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  datetime
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	protected $country_id;

	/**
	 * @var  integer|null
	 */
	protected $state_id;

	/**
	 * @var  string
	 */
	protected $address_name;

	/**
	 * @var  string
	 */
	protected $address_name2;

	/**
	 * @var  string
	 */
	protected $address;

	/**
	 * @var  string
	 */
	protected $address2;

	/**
	 * @var  string
	 */
	protected $zip;

	/**
	 * @var  string
	 */
	protected $city;

	/**
	 * @var  string
	 */
	protected $phone;

	/**
	 * @var  boolean
	 */
	protected $deleteIfEmptyDefaultAddress = false;

	/**
	 * @var  string
	 */
	protected $type;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Stockroom_Product_Xref' => 'stockroom_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.stockroom'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'company_id' => array(
			'model' => 'Companies'
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
		'state', 'pick_up'
	);

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		if ($this->min_delivery_time > $this->max_delivery_time)
		{
			$this->setError(Text::_('COM_REDSHOPB_STOCKROOM_ERROR_SAVE_DELIVERY_TIME'));

			return false;
		}

		if ($this->stock_lower_level && $this->stock_upper_level && ($this->stock_lower_level >= $this->stock_upper_level))
		{
			$this->setError(Text::_('COM_REDSHOPB_STOCKROOM_ERROR_SAVE_STOCK_LEVEL'));

			return false;
		}

		$db = $this->getDbo();

		// New record with ordering value set so we need to create a space for it
		if (empty($this->id) && !empty($this->ordering))
		{
			$this->createOrderingSlot($this->ordering);
		}

		// New record without ordering so we place it at the bottom.
		if (empty($this->ordering))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($db->qn('state') . ' >= 0');
		}

		// We're updating this, so we need to make sure the ordering is correct
		if (!empty($this->id))
		{
			$this->checkOrdering($this->id, $this->ordering);
		}

		return true;
	}

	/**
	 * Method to increment records ordering value by scope
	 * This allows us to create a slot in the ordering to insert a new record.
	 *
	 * @param   int  $ordering  the numeric ordering value
	 *
	 * @return mixed
	 */
	private function createOrderingSlot($ordering)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->update($this->_tbl)
			->set($db->qn('ordering') . ' = ' . $db->qn('ordering') . ' + 1')
			->where($db->qn('ordering') . ' >= ' . (int) $ordering);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method to check if the scope has changed
	 *
	 * @param   int  $id        primary key of the record we're checking
	 * @param   int  $ordering  the numeric ordering value
	 *
	 * @return void
	 */
	private function checkOrdering($id, $ordering)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('ordering'))
			->from($this->_tbl)
			->where($db->qn('id') . ' = ' . (int) $id);

		$oldRecord = $db->setQuery($query)->loadObject();

		if ($oldRecord->ordering != $ordering)
		{
			$this->createOrderingSlot($ordering);
		}
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		$isNew = (int) $this->id <= 0;

		$addressTable = RedshopbTable::getAdminInstance('Address')
			->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
			->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'))
			->setOption('notSetAddressSeparate', true);

		if ($this->country_id == '' && $this->address == '' && $this->zip == '' && $this->city == '')
		{
			$this->deleteIfEmptyDefaultAddress = true;
		}

		if ($this->deleteIfEmptyDefaultAddress && $this->type != 'customer')
		{
			if ($this->address_id)
			{
				if (!$addressTable->delete($this->address_id, true))
				{
					$this->setError($addressTable->getError());

					return false;
				}
			}

			$this->address_id = null;
		}
		else
		{
			$address = array(
				'name' => $this->address_name,
				'name2' => $this->address_name2,
				'id' => $this->address_id,
				'country_id' => $this->country_id,
				'state_id' => $this->state_id,
				'address' => $this->address,
				'address2' => $this->address2,
				'zip' => $this->zip,
				'city' => $this->city,
				'phone' => $this->phone,
				'type' => 2,
				'customer_type' => 'stockroom',
				'customer_id' => (int) $this->id
			);

			if ($address['id'])
			{
				if (!$addressTable->load($address['id']))
				{
					$address['id'] = 0;
				}
			}

			if (!$addressTable->save($address))
			{
				$this->setError($addressTable->getError());

				return false;
			}

			$this->address_id = $addressTable->id;
		}

		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if (isset($address) && $addressTable->get('id') && $addressTable->get('customer_id') != $this->get('id'))
		{
			if (!$addressTable->save(array('customer_id' => $this->get('id'))))
			{
				$this->setError($addressTable->getError());

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
	 *
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset))
		{
			return false;
		}

		if ($this->id && $this->address_id)
		{
			/** @var RedshopbTableAddress $addressTable */
			$addressTable = RedshopbTable::getAdminInstance('Address');

			if (!$addressTable->load($this->address_id))
			{
				$this->setError($addressTable->getError());

				return false;
			}

			$this->address_id    = $addressTable->id;
			$this->country_id    = $addressTable->country_id;
			$this->state_id      = $addressTable->state_id;
			$this->address_name  = $addressTable->name;
			$this->address_name2 = $addressTable->name2;
			$this->address       = $addressTable->address;
			$this->address2      = $addressTable->address2;
			$this->zip           = $addressTable->zip;
			$this->city          = $addressTable->city;
			$this->phone         = $addressTable->phone;
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (parent::bind($src, $ignore))
		{
			if (isset($src['company_id']) && $src['company_id'] == '')
			{
				$this->company_id = null;
				$this->setOption('storeNulls', true);
			}

			return true;
		}

		return false;
	}
}
