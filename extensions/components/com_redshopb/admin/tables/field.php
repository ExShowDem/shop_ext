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
 * Field table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableField extends RedshopbTable
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
	protected $_tableName = 'redshopb_field';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $scope;

	/**
	 * @var  integer
	 */
	public $type_id;

	/**
	 * @var  integer
	 */
	public $filter_type_id;

	/**
	 * @var  integer
	 */
	public $field_value_xref_id;

	/**
	 * @var  integer
	 */
	public $field_group_id;

	/**
	 * @var integer
	 */
	public $unit_measure_id = null;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $title;

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
	public $multiple_values;

	/**
	 * @var  integer
	 */
	public $only_available;

	/**
	 * @var  string
	 */
	public $default_value;

	/**
	 * @var  integer
	 */
	public $ordering;

	/**
	 * @var string
	 */
	public $field_value_ordering;

	/**
	 * @var  integer
	 */
	public $required = 0;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  integer
	 */
	public $importable = 0;

	/**
	 * @var  integer
	 */
	public $searchable_frontend = 1;

	/**
	 * @var  integer
	 */
	public $searchable_backend = 1;

	/**
	 * @var  string
	 */
	public $params;

	/**
	 * @var  string
	 */
	public $decimal_separator;

	/**
	 * @var  string
	 */
	public $thousand_separator;

	/**
	 * @var  integer
	 */
	public $decimal_position;

	/**
	 * @var string
	 */
	protected $type_code;

	/**
	 * @var   string
	 *
	 * @since 1.13.2
	 */
	public $prefix;

	/**
	 * @var   string
	 *
	 * @since 1.13.2
	 */
	public $suffix;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Field_Value' => 'field_id',
		'Field_Data'  => 'field_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.field',
			'erp.pim.field.cvl'
		),
		'erp' => array(
			'ws.field'
		),
		'b2b' => array(
			'erp.webservice.fields'
		)
	);

	/**
	 * Method to store a node in the database table.
	 * Here set $updateNulls = true for can be possible set nulls values
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		return parent::store($updateNulls);
	}

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'field_value_xref_id' => array(
			'model' => 'Fields'
		),
		'field_group_id' => array(
			'model' => 'Field_Groups'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'type_code' => 'Types',
		'filter_type_code' => 'Types'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('multiple_values', 'searchable_frontend', 'searchable_backend', 'state');

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
		if ($this->getOption('load.type_code', true))
		{
			if (is_numeric($this->type_id))
			{
				$typeTable = RedshopbTable::getAdminInstance('Type');

				if ($typeTable->load($this->type_id))
				{
					$this->type_code = $typeTable->alias;
				}
			}
		}

		return parent::afterLoad($keys, $reset);
	}

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
		$field->setOption('load.type_code', false);

		if ($field->load(array('name' => $this->name, 'scope' => $this->scope))
			&& $field->id != $this->id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_FIELD_NAME_ALREADY_EXISTING', $this->name));

			return false;
		}

		if (empty($this->title))
		{
			$this->title = $this->name;
		}

		if ($this->filter_type_id == '')
		{
			$this->filter_type_id = null;
		}

		if ($this->field_value_xref_id == '')
		{
			$this->field_value_xref_id = null;
		}

		if ($this->field_group_id == '')
		{
			$this->field_group_id = null;
		}

		if (!in_array(RedshopbHelperField::getTypeById($this->type_id)->value_type, array('float_value', 'int_value')))
		{
			$this->unit_measure_id = null;
		}

		if ($this->unit_measure_id == '')
		{
			$this->unit_measure_id = null;
		}

		if ($this->unit_measure_id !== null)
		{
			$this->decimal_separator  = '';
			$this->thousand_separator = '';
			$this->decimal_position   = 0;
			$this->prefix             = '';
			$this->suffix             = '';
		}

		if (!$this->getOption('forceOrderingValues'))
		{
			// New record with ordering value set so we need to create a space for it
			if (empty($this->id) && !empty($this->ordering))
			{
				$this->createOrderingSlot($this->ordering, $this->scope);
			}

			// New record without ordering so we place it at the bottom.
			if (empty($this->ordering))
			{
				// Set ordering to last if ordering was 0
				$this->ordering = self::getNextOrder($this->_db->qn('scope') . '=' . $this->_db->q($this->scope) . ' AND state >= 0');
			}

			// We're updating this, so we need to make sure the ordering is correct for current scope
			if (!empty($this->id))
			{
				$this->checkScopeOrdering($this->id, $this->ordering, $this->scope);
			}
		}
		else
		{
			if (!is_numeric($this->ordering))
			{
				// Set ordering to last if ordering is not set
				$this->ordering = self::getNextOrder($this->_db->qn('scope') . '=' . $this->_db->q($this->scope) . ' AND state >= 0');
			}
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
		// First we need to find out if creating a slot is actually necessary
		$fieldTable = clone $this;

		if (!$fieldTable->load(array('scope' => $scope, 'ordering' => $ordering)))
		{
			return true;
		}

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

	/**
	 * Deletes fields associations from categories given a field id
	 *
	 * @param   integer  $id  Id of the field
	 *
	 * @return  void
	 */
	public function removeFromCategoryAssociation($id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->qn('#__redshopb_category_field_xref'))
			->where($db->qn('field_id') . ' = ' . $db->q($id));
		$db->setQuery($query)->execute();
		$query->clear();
	}
}
