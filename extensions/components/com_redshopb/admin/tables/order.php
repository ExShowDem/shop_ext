<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
/**
 * Voucher table
 *
 * @package     RedSHOP.Backend
 * @subpackage  Table
 * @since       2.0
 */
class RedshopbTableOrder extends RedshopbTable
{
	/**
	 * The name of the table with currencies
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $_tableName = 'redshopb_order';

	/**
	 * The primary key of the table
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $_tableKey = 'id';

	/**
	 * Field name to publish/unpublish/trash table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'order_status';

	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'shipping_date_check' => true,
	);

	/**
	 * @var null
	 */
	public $shipping_date = null;

	/**
	 * Layout created date.
	 *
	 * @var  integer
	 */
	public $created_date;

	/**
	 * Layout created by user id.
	 *
	 * @var  integer
	 */
	public $created_by;

	/**
	 * Layout date modified.
	 *
	 * @var  integer
	 */
	public $modified_date;

	/**
	 * Layout modified by user id.
	 *
	 * @var  integer
	 */
	public $modified_by;

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
	protected $company_id = null;

	/**
	 * @var  integer
	 */
	protected $department_id = null;

	/**
	 * @var  integer
	 */
	protected $user_id = null;

	/**
	 * @var  string
	 */
	protected $currency_code = null;

	/**
	 * @var  string
	 */
	protected $status_code = null;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.order'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'company_id' => array(
			'model' => 'Companies'
		),
		'department_id' => array(
			'model' => 'Departments'
		),
		'user_id' => array(
			'model' => 'Users'
		),
		'author_employee_id' => array(
			'model' => 'Users'
		),
		'delivery_address_id' => array(
			'model' => 'Addresses'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'currency_code' => 'Currencies'
	);

	/**
	 * WS sync map of date fields to prevent invalid dates
	 *
	 * @var  array
	 */
	protected $wsSyncMapDate = array('date');

	/**
	 * Overloaded bind function.
	 *
	 * @param   array   $array   named array
	 * @param   string  $ignore  An optional array or space separated list of properties
	 *                           to ignore while binding.
	 *
	 * @return  mixed   Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     Table::bind()
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['shipping_details'])
			&& is_array($array['shipping_details']))
		{
			$registry = new Registry;
			$registry->loadArray($array['shipping_details']);
			$array['shipping_details'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
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

		switch ($this->customer_type)
		{
			case 'company':
				$this->company_id = RedshopbHelperCompany::getCompanyIdByCustomer($this->customer_id, $this->customer_type, false);
				break;

			case 'department':
				$department          = RedshopbHelperDepartment::getDepartmentByCustomer($this->customer_id, $this->customer_type);
				$this->company_id    = $department->company_id;
				$this->department_id = $department->id;
				break;

			case 'employee':
				$user                = RedshopbHelperUser::getUser($this->customer_id);
				$this->company_id    = $user->company;
				$this->department_id = $user->department;
				$this->user_id       = $user->id;
				break;
		}

		$statuses          = RedshopbEntityOrder::getAllowedStatusCodes();
		$this->status_code = $statuses[$this->status];

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

		if (!$this->shipping_date)
		{
			$this->shipping_date = null;
		}
		else
		{
			$oldShippingDate = null;

			if ($this->get('id'))
			{
				if (empty($this->propertiesAfterLoad))
				{
					$cloneTable = clone $this;

					if ($cloneTable->load($this->get('id')))
					{
						$oldShippingDate = $cloneTable->shipping_date;
					}
				}
				else
				{
					$oldShippingDate = $this->propertiesAfterLoad['shipping_date'];
				}
			}

			$oldShippingDate = new DateTime($oldShippingDate);
			$newShippingDate = new DateTime($this->shipping_date);

			if ($this->getOption('shipping_date_check', true) && $oldShippingDate->format('Y-m-d') != $newShippingDate->format('Y-m-d'))
			{
				if (!RedshopbHelperOrder::isShippingDateAvailable($this->shipping_date, $this->customer_type, $this->customer_id))
				{
					$this->setError(
						Text::sprintf(
							'COM_REDSHOPB_SHOP_SHIPPING_DATE_ALLOW_FROM',
							$newShippingDate->format(Text::_('DATE_FORMAT_LC4'))
						)
					);

					return false;
				}
			}
		}

		return true;
	}
}
