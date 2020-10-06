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
/**
 * My offers table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableOffer extends RedshopbTable
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_offer';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  integer
	 */
	public $vendor_id;

	/**
	 * @var  string
	 */
	public $status = 'created';

	/**
	 * @var  string
	 */
	public $state;

	/**
	 * @var  integer
	 */
	public $company_id = null;

	/**
	 * @var  integer
	 */
	public $department_id = null;

	/**
	 * @var  integer
	 */
	public $user_id = null;

	/**
	 * @var  float
	 */
	public $subtotal;

	/**
	 * @var  float
	 */
	public $discount = 0;

	/**
	 * @var  float
	 */
	public $total;

	/**
	 * @var  string
	 */
	public $requested_date;

	/**
	 * @var  string
	 */
	public $sent_date;

	/**
	 * @var  string
	 */
	public $execution_date;

	/**
	 * @var  string
	 */
	public $order_date;

	/**
	 * @var  string
	 */
	public $expiration_date;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var integer
	 */
	protected $customer_id = 0;

	/**
	 * @var null
	 */
	protected $currency_id = null;

	/**
	 * @var string
	 */
	public $discount_type = 'percent';

	/**
	 * @var string
	 */
	public $customer_type = '';

	/**
	 * @var null
	 */
	public $collection_id = null;

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

		switch ($this->customer_type)
		{
			case 'employee':
				$this->customer_id = $this->user_id;
				break;
			case 'department':
				$this->customer_id = $this->department_id;
				break;
			case 'company':
				$this->customer_id = $this->company_id;
				break;
		}

		$this->currency_id = RedshopbHelperPrices::getCurrency($this->customer_id, $this->customer_type, $this->collection_id);

		return true;
	}

	/**
	 * Called before check().
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function beforeCheck()
	{
		if (!parent::beforeCheck())
		{
			return false;
		}

		if ($this->vendor_id == '' || $this->vendor_id == 0)
		{
			$this->vendor_id = null;
		}

		if ($this->department_id == '' || $this->department_id == 0)
		{
			$this->department_id = null;
		}

		if ($this->collection_id == '' || $this->collection_id == 0)
		{
			$this->collection_id = null;
		}

		if ($this->user_id == '' || $this->user_id == 0)
		{
			$this->user_id = null;
		}

		if (!$this->customer_type)
		{
			if ($this->user_id)
			{
				$this->customer_type = 'employee';
			}
			elseif ($this->department_id)
			{
				$this->customer_type = 'department';
			}
			elseif ($this->company_id)
			{
				$this->customer_type = 'company';
			}
		}

		return true;
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
		$currentDate = Factory::getDate()->toSql();

		switch ($this->status)
		{
			case 'requested':
				$this->requested_date = $currentDate;
				break;
			case 'sent':
				$this->sent_date = $currentDate;
				break;
			case 'accepted':
			case 'rejected':
				$this->execution_date = $currentDate;
				break;
			case 'ordered':
				$this->order_date = $currentDate;

				if (!$this->execution_date)
				{
					$this->execution_date = $currentDate;
				}

				break;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Delete Offers
	 *
	 * @param   string/array  $pk  Array of company ids or ids comma separated
	 *
	 * @return boolean
	 */
	public function deleteOffers($pk)
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

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select(array('id'))
			->from($db->qn('#__redshopb_offer'))
			->where('company_id IN (' . implode(',', $pk) . ')');
		$offers = $db->setQuery($query)
			->loadColumn();

		if ($offers)
		{
			foreach ($offers as $offerId)
			{
				if ($this->load($offerId, true))
				{
					if (!$this->delete($offerId))
					{
						return false;
					}
				}
			}
		}

		return true;
	}
}
