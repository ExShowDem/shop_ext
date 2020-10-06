<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Stockroom Group table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6.0
 */
class RedshopbTableStockroom_Group extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_stockroom_group';

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
	public $alias;

	/**
	 * @var  string
	 */
	public $color;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  string
	 */
	public $deadline_weekday_1;

	/**
	 * @var  string
	 */
	public $deadline_weekday_2;

	/**
	 * @var  string
	 */
	public $deadline_weekday_3;

	/**
	 * @var  string
	 */
	public $deadline_weekday_4;

	/**
	 * @var  string
	 */
	public $deadline_weekday_5;

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
	public $state = 1;

	/**
	 * @var  array
	 */
	public $stockrooms;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.stockroom_group'
		),
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'state'
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
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if (!$this->storeStockrooms($updateNulls))
		{
			return false;
		}

		return true;
	}

	/**
	 * Store stockrooms relations
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function storeStockrooms($updateNulls = true)
	{
		if (!isset($this->stockrooms))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_stockroom_group_stockroom_xref')
			->where($db->qn('stockroom_group_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (!is_array($this->stockrooms) || count($this->stockrooms) <= 0)
		{
			return true;
		}

		/** @var RedshopbTableStockroom_Group_Stockroom_Xref $xrefTable */
		$xrefTable = RedshopbTable::getAdminInstance('Stockroom_Group_Stockroom_Xref');

		// Store the new items
		foreach ($this->stockrooms as $stockroomId)
		{
			$keys = array('id' => 0, 'stockroom_group_id' => $this->id, 'stockroom_id' => $stockroomId);

			if (!$xrefTable->save($keys))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
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
		if (!$this->loadStockroomXref())
		{
			return false;
		}

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Load the stockrooms related to this stockroom group
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadStockroomXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('stockroom_id')
			->from('#__redshopb_stockroom_group_stockroom_xref')
			->where($db->qn('stockroom_group_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$ids = $db->loadColumn();

		if (!is_array($ids))
		{
			$ids = array();
		}

		$this->stockrooms = $ids;

		return true;
	}
}
