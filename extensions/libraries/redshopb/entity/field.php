<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Entity for fields
 *
 * @since  2.0
 */
class RedshopbEntityField extends RedshopbEntity
{
	/**
	 * Field type
	 *
	 * @var    RedshopbEntityType
	 * @since  2.0
	 */
	protected $type;

	/**
	 * Field data (multi-dimensional array)
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $data = array();

	/**
	 * Field values (array of entities)
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $values = array();

	/**
	 * Gets the field type
	 *
	 * @return  RedshopbEntityType
	 *
	 * @since   2.0
	 */
	public function getType()
	{
		if (null === $this->type)
		{
			$this->loadType();
		}

		return $this->type;
	}

	/**
	 * Gets the field data for a specific scope/item/subitem_id
	 *
	 * @param   string  $scope       Field data scope
	 * @param   int     $itemId      Item id to retrieve data from
	 * @param   int     $subitemId   Subitem id to retrieve data from (if no subitem is required, send 0)
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function getFieldData($scope, $itemId, $subitemId = 0)
	{
		if (!isset($this->data[$scope . '_' . $itemId . '_' . $subitemId]))
		{
			$this->loadFieldData($scope, $itemId, $subitemId);
		}

		// Returning empty array if data was not loaded
		if (!isset($this->data[$scope . '_' . $itemId . '_' . $subitemId]))
		{
			return array();
		}

		return $this->data[$scope . '_' . $itemId . '_' . $subitemId];
	}

	/**
	 * Gets a certain field value
	 *
	 * @param   int  $id  Id of the field value
	 *
	 * @return  RedshopbEntityField_Value | false
	 *
	 * @since   2.0
	 */
	public function getFieldValue($id)
	{
		if (isset($this->values[$id]))
		{
			return $this->values[$id];
		}

		$this->loadFieldValue($id);

		if (isset($this->values[$id]))
		{
			return $this->values[$id];
		}

		return false;
	}

	/**
	 * Loads the field type
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadType()
	{
		if (!$this->hasId())
		{
			return $this;
		}

		$this->type = RedshopbEntityType::load($this->item->type_id);

		return $this;
	}

	/**
	 * Loads the field data for a specific scope/item/subitem_id
	 *
	 * @param   string  $scope       Field data scope
	 * @param   string  $itemId      Item id to retrieve data from
	 * @param   string  $subitemId   Subitem id to retrieve data from
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadFieldData($scope, $itemId, $subitemId)
	{
		if (!$this->hasId())
		{
			return $this;
		}

		// State filters
		$state = array(
			'filter.field_scope' => $scope,
			'filter.item_id' => $itemId,
			'filter.field_id' => $this->id,
			'list.ordering'  => 'p.id',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		if ($subitemId)
		{
			$state['subitem_id'] = $subitemId;
		}

		/** @var RedshopbModelField_Datas $model */
		$model                                                 = RedshopbModel::getFrontInstance('field_datas');
		$this->data[$scope . '_' . $itemId . '_' . $subitemId] = $model->search($state);

		return $this;
	}

	/**
	 * Loads a certain field value
	 *
	 * @param   int  $id  Id of the field value
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	public function loadFieldValue($id)
	{
		if (!$this->hasId())
		{
			return $this;
		}

		$fieldValue = RedshopbEntityField_Value::load($id);

		if (!$fieldValue)
		{
			return $this;
		}

		if ($fieldValue->get('field_id') != $id)
		{
			return $this;
		}

		$this->values[$id] = $fieldValue;

		return $this;
	}

	/**
	 * Returns modified float number depend on units of measure
	 * ToDo: Redo this crap.  This needs a proper function in the way entities deal with things.
	 *
	 * @param   stdClass  $fieldData  Id of the field value
	 *
	 * @return  string
	 * @since   1.12
	 */
	public static function getFloatFieldValue(&$fieldData)
	{
		return RedshopbHelperField::getFloatFieldValue($fieldData);
	}
}
