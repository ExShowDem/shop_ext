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
 * Collection product table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCollection_Product_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_collection_product_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_key = array('collection_id', 'product_id');

	/**
	 * Indicates that the primary keys autoincrement.
	 *
	 * @var    boolean
	 * @since  12.3
	 */
	protected $_autoincrement = false;

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $collection_id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $ordering;

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 *
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string  $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 *
	 * @return  integer  The next ordering value.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function getNextOrder($where = '')
	{
		if (empty($where))
		{
			$where = $this->_db->qn('collection_id') . '=' . $this->_db->q($this->collection_id);
		}

		return parent::getNextOrder($where);
	}

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
		// New record with ordering value set so we need to create a space for it
		if (empty($this->id) && !empty($this->ordering))
		{
			$this->createOrderingSlot($this->ordering);
		}

		// New record without ordering so we place it at the bottom.
		if (empty($this->ordering))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder();
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
			->where($db->qn('collection_id') . ' = ' . (int) $this->collection_id)
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
}
