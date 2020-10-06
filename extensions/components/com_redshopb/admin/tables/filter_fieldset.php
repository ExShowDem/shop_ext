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

/**
 * Filter Fieldset table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableFilter_Fieldset extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_filter_fieldset';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name = null;

	/**
	 * @var  integer
	 */
	public $state = 1;

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
	 * Fields ids needed for saving
	 *
	 * @var  array
	 */
	protected $fields;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.filterFieldset'
		),
		'erp' => array(
			'ws.filter_fieldset'
		),
		'b2b' => array(
			'erp.webservice.filterfieldset'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state');

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'fields' => 'Fields'
	);

	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'fields_relate.store' => true,
		'fields_relate.full.load' => false,
	);

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
		if ($this->getOption('fields_relate.full.load', false))
		{
			if (!$this->loadFieldsFullXref())
			{
				return false;
			}
		}
		else
		{
			if (!$this->loadFieldsXref())
			{
				return false;
			}
		}

		return parent::afterLoad($keys, $reset);
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
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (parent::store($updateNulls))
		{
			if ($this->getOption('fields_relate.store'))
			{
				// Store the fields
				if (!$this->storeFieldsXref())
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Load the fields related to this filter
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadFieldsXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('field_id')
			->from('#__redshopb_filter_fieldset_xref')
			->where('fieldset_id = ' . $db->q($this->id));

		$db->setQuery($query);

		$fieldId = $db->loadColumn();

		if (!is_array($fieldId))
		{
			$fieldId = array();
		}

		$this->fields = $fieldId;

		return true;
	}

	/**
	 * Load the fields related to this filter (full information)
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadFieldsFullXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true);

		$query->select(
			array(
					$db->qn('f.id', 'field_id'),
					$db->qn('f.name', 'field_name'),
					$db->qn('f.alias', 'type_code'),
					$db->qn('t.name', 'field_type_name')
				)
		)
			->from($db->qn('#__redshopb_filter_fieldset_xref', 'ffx'))
			->join('LEFT', $db->qn('#__redshopb_field', 'f') . ' ON f.id = ffx.field_id')
			->join('LEFT', $db->qn('#__redshopb_type', 't') . ' ON t.id = f.type_id')
			->where($db->qn('ffx.fieldset_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$this->fields = $db->loadObjectList();

		return true;
	}

	/**
	 * Store the fields x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeFieldsXref()
	{
		if (!isset($this->fields))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_filter_fieldset_xref')
			->where('fieldset_id = ' . $db->q($this->id));

		if (is_array($this->fields) && count($this->fields) > 0)
		{
			$this->fields = ArrayHelper::toInteger($this->fields);
			$query->where('field_id NOT IN (' . implode(',', $this->fields) . ')');
		}

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (is_array($this->fields) && count($this->fields) > 0)
		{
			/** @var RedshopbTableFilter_Fieldset_Xref $xrefTable */
			$xrefTable = RedshopbTable::getAdminInstance('Filter_Fieldset_Xref');

			// Store the new items
			foreach ($this->fields as $fieldId)
			{
				$xrefTable->reset();

				if (!$xrefTable->load(
					array(
						'fieldset_id' => $this->id,
						'field_id' => $fieldId
					)
				))
				{
					if (!$xrefTable->save(
						array(
							'id' => 0,
							'fieldset_id' => $this->id,
							'field_id' => $fieldId
						)
					))
					{
						$this->setError($xrefTable->getError());

						return false;
					}
				}
			}
		}

		return true;
	}
}
