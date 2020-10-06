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
 * Cart table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableField_Group extends RedshopbTable
{
	/**
	 * Columns used to generate alias from
	 *
	 * @var  string
	 */
	protected $_aliasColumns = array('scope', 'name');

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_field_group';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $scope;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  integer
	 */
	public $ordering;

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
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.field_group'
		),
		'erp' => array(
			'ws.field_group'
		),
		'b2b' => array(
			'erp.webservice.field_group'
		)
	);

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   11.1
	 */
	public function check()
	{
		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_NAME_CANNOT_BE_EMPTY'));

			return false;
		}

		// Check a field is not already existing with the same name
		$field = clone $this;

		if ($field->load(array('name' => $this->name, 'scope' => $this->scope))
			&& $field->id != $this->id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_FIELD_NAME_ALREADY_EXISTING', $this->name));

			return false;
		}

		// New record with ordering value set so we need to create a space for it
		if (empty($this->id) && !empty($this->ordering))
		{
			$this->createOrderingSlot($this->ordering, $this->scope);
		}

		// New record without ordering so we place it at the bottom.
		if (empty($this->ordering))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->qn('scope') . '=' . $this->_db->q($this->scope));
		}

		// We're updating this, so we need to make sure the ordering is correct for current scope
		if (!empty($this->id))
		{
			$this->checkScopeOrdering($this->id, $this->ordering, $this->scope);
		}

		// If field is set to global, we should remove all local scope associations
		if (!empty($this->global))
		{
			$this->removeFromCategoryAssociation($this->id);
		}

		return true;
	}

	/**
	 * Method to increment records ordering value by scope
	 * This allows us to create a slot in the ordering to insert a new record.
	 *
	 * @param   int     $ordering  the numeric ordering value
	 * @param   string  $scope     The scope of the field
	 *
	 * @return mixed
	 */
	private function createOrderingSlot($ordering, $scope)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($this->_tbl)
			->set('ordering = ordering + 1')
			->where('scope = ' . $db->q($scope))
			->where('ordering >= ' . (int) $ordering);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method to check if the scope has changed
	 *
	 * @param   int     $id        primary key of the record we're checking
	 * @param   int     $ordering  the numeric ordering value
	 * @param   string  $scope     The scope of the field
	 *
	 * @return void
	 */
	private function checkScopeOrdering($id, $ordering, $scope)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('ordering, scope')
			->from($this->_tbl)
			->where('id = ' . (int) $id);

		$oldRecord = $db->setQuery($query)->loadObject();

		if ($oldRecord->scope != $scope)
		{
			// First close the gap in the old scope
			$this->removeFromScopeOrdering($oldRecord->ordering, $oldRecord->scope);

			// Now we create a hole in the new scope
			$this->createOrderingSlot($ordering, $scope);

			return;
		}

		if ($oldRecord->ordering != $ordering)
		{
			$this->createOrderingSlot($ordering, $scope);
		}
	}

	/**
	 * Method to reduce the scope ordering by one
	 *
	 * @param   int     $oldOrdering  The old numeric ordering value
	 * @param   string  $oldScope     The old scope of the field
	 *
	 * @return void
	 */
	private function removeFromScopeOrdering($oldOrdering, $oldScope)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($this->_tbl)
			->set('ordering = ordering -1')
			->where('scope = ' . $db->q($oldScope))
			->where('ordering > ' . (int) $oldOrdering);
		$db->setQuery($query)->execute();
		$query->clear();
	}

	/**
	 * repairs the scope ordering when a record is  deleted.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function beforeDelete($pk = null)
	{
		if (is_null($pk))
		{
			$pk = $this->id;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('ordering, scope')
			->from($this->_tbl)
			->where('id = ' . (int) $pk);

		$record = $db->setQuery($query, 0, 1)
			->loadObject();

		if ($record)
		{
			$this->removeFromScopeOrdering($record->ordering, $record->scope);
		}

		return true;
	}
}
