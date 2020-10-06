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
 * Field Value table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableField_Value extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'forceOrderingValues' => false
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_field_value';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $field_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $value = null;

	/**
	 * @var  integer
	 */
	public $default = 0;

	/**
	 * @var  integer
	 */
	public $ordering;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.field.value'
		),
		'erp' => array(
			'ws.field_value'
		),
		'b2b' => array(
			'erp.webservice.field_values'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'field_id' => array (
			'model' => 'Fields'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('default');

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
		if (!$this->getOption('forceOrderingValues'))
		{
			// New record with ordering value set so we need to create a space for it
			if (empty($this->id) && !empty($this->ordering))
			{
				$this->createOrderingSlot($this->ordering, $this->field_id);
			}

			// New record without ordering so we place it at the bottom.
			if (empty($this->ordering))
			{
				$this->ordering = self::getNextOrder($this->_db->qn('field_id') . '=' . $this->_db->q($this->field_id));
			}

			// We're updating this, so we need to make sure the ordering is correct for current parent field
			if (!empty($this->id))
			{
				$this->checkFieldOrdering($this->id, $this->ordering, $this->field_id);
			}
		}
		else
		{
			if (!is_numeric($this->ordering))
			{
				// Place record at bottom if no ordering is set
				$this->ordering = self::getNextOrder($this->_db->qn('field_id') . '=' . $this->_db->q($this->field_id));
			}
		}

		if ($this->default)
		{
			RedshopbHelperField::cleanDefaultFieldValue($this->field_id, (int) $this->id);
		}

		return parent::check();
	}

	/**
	 * Method to increment records ordering value by field
	 * This allows us to create a slot in the ordering to insert a new record.
	 *
	 * @param   int  $ordering  The numeric ordering value
	 * @param   int  $fieldId   The parent field id
	 *
	 * @return mixed
	 */
	private function createOrderingSlot($ordering, $fieldId)
	{
		// First we need to find out if creating a slot is actually necessary
		$fieldValueTable = clone $this;

		if (!$fieldValueTable->load(array('field_id' => $fieldId, 'ordering' => $ordering)))
		{
			return true;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($this->_tbl)
			->set('ordering = ordering + 1')
			->where('field_id = ' . (int) $fieldId)
			->where('ordering >= ' . (int) $ordering);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method to check if the parent field has changed
	 *
	 * @param   int  $pk        primary key of the record we're checking
	 * @param   int  $ordering  the numeric ordering value
	 * @param   int  $fieldId   The scope of the field
	 *
	 * @return void
	 */
	private function checkFieldOrdering($pk, $ordering, $fieldId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('ordering, field_id')
			->from($this->_tbl)
			->where('id = ' . (int) $pk);

		$oldRecord = $db->setQuery($query)->loadObject();

		if ($oldRecord->field_id != $fieldId)
		{
			// First close the gap in the old scope
			$this->removeFromFieldOrdering($oldRecord->ordering, $oldRecord->field_id);

			// Now we create a hole in the new scope
			$this->createOrderingSlot($ordering, $fieldId);

			return;
		}

		if ($oldRecord->ordering != $ordering)
		{
			$this->createOrderingSlot($ordering, $fieldId);
		}
	}

	/**
	 * Method to reduce the scope ordering by one
	 *
	 * @param   int  $oldOrdering  The old numeric ordering value
	 * @param   int  $oldFieldId   The old field id
	 *
	 * @return void
	 */
	private function removeFromFieldOrdering($oldOrdering, $oldFieldId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($this->_tbl)
			->set('ordering = ordering -1')
			->where('field_id = ' . (int) $oldFieldId)
			->where('ordering > ' . (int) $oldOrdering);
		$db->setQuery($query)->execute();
		$query->clear();
	}

	/**
	 * repairs the field value ordering when a record is deleted.
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
		$query = $db->getQuery(true);
		$query->select('ordering, field_id')
			->from($this->_tbl)
			->where('id = ' . (int) $pk);

		$record = $db->setQuery($query)->loadObject();

		if ($record)
		{
			$this->removeFromFieldOrdering($record->ordering, $record->field_id);
		}

		$query = $db->getQuery(true)
			->select('d.id')
			->from($db->qn('#__redshopb_field_data', 'd'))
			->innerJoin($db->qn('#__redshopb_field_value', 'fv') . ' ON d.field_value = fv.id AND d.field_id = fv.field_id')
			->where('fv.id = ' . (int) $pk);

		$results = $db->setQuery($query)
			->loadColumn();

		if (!empty($results))
		{
			$fieldDataTable = RedshopbTable::getAdminInstance('Field_Data');

			foreach ($results as $result)
			{
				if (!$fieldDataTable->delete($result))
				{
					$this->setError($fieldDataTable->getError());

					return false;
				}
			}
		}

		return true;
	}
}
