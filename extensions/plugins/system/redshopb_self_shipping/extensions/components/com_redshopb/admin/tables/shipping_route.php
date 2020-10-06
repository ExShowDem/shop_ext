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
 * Unit measure table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableShipping_Route extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'address_relate.store' => true,
	);

	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_shipping_route';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $company_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  integer
	 */
	public $weekday_1;

	/**
	 * @var  integer
	 */
	public $weekday_2;

	/**
	 * @var  integer
	 */
	public $weekday_3;

	/**
	 * @var  integer
	 */
	public $weekday_4;

	/**
	 * @var  integer
	 */
	public $weekday_5;

	/**
	 * @var  integer
	 */
	public $weekday_6;

	/**
	 * @var  integer
	 */
	public $weekday_7;

	/**
	 * @var  integer
	 */
	public $max_delivery_time;

	/**
	 * @var  array
	 */
	public $addresses;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'b2b' => array(
			'erp.webservice.shipping_routes'
		),
		'pim' => array(
			'erp.pim.shipping_routes'
		),
		'erp' => array(
			'ws.shipping_routes'
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
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'addresses' => 'Address'
	);

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->addresses = null;

		parent::reset();
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		try
		{
			if (!parent::delete($pk))
			{
				throw new Exception;
			}
		}
		catch (Exception $exception)
		{
			$this->setError(Text::_('COM_REDSHOPB_UNIT_MEASURE_ERROR_DELETE'));

			return false;
		}

		return true;
	}
	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if ($this->id)
		{
			$isNew = false;
		}
		else
		{
			$isNew = true;
		}

		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if ($this->getOption('address_relate.store') && !$this->storeAddressesXref($isNew))
		{
			return false;
		}

		return true;
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		if (!$this->loadAddressesXref())
		{
			return false;
		}

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Store the addresses x references
	 *
	 * @param   boolean  $isNew  Is product new.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeAddressesXref($isNew)
	{
		if (!isset($this->addresses))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_shipping_route_address_xref')
			->where($db->qn('shipping_route_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (!is_array($this->addresses) || count($this->addresses) <= 0)
		{
			return true;
		}

		/** @var RedshopbTableShipping_Route_Address_Xref $xrefTable */
		$xrefTable = RedshopbTable::getAdminInstance('Shipping_Route_Address_Xref');

		// Store the new items
		foreach ($this->addresses as $addressId)
		{
			$keys = array('shipping_route_id' => $this->id, 'address_id' => $addressId);

			if ($xrefTable->load($keys))
			{
				continue;
			}

			if (!$xrefTable->save($keys))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Load the Addresses related to this Shipping Route
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadAddressesXref()
	{
		if ($this->id)
		{
			$db    = $this->_db;
			$query = $db->getQuery(true)
				->select('address_id')
				->from('#__redshopb_shipping_route_address_xref')
				->where($db->qn('shipping_route_id') . ' = ' . (int) $this->id);

			$db->setQuery($query);

			$addresses = $db->loadColumn();

			if (!is_array($addresses))
			{
				$addresses = array();
			}

			$this->addresses = $addresses;
		}

		return true;
	}
}
