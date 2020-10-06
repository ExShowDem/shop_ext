<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Country Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelField_Group extends RedshopbModelAdmin
{
	/**
	 * Get the fields associated to this category.
	 *
	 * @param   integer  $fieldGroupId  The Field group ID
	 *
	 * @return  array  An array of fields to display
	 */
	public function getFields($fieldGroupId)
	{
		$db       = $this->_db;
		$fieldIds = array();
		$fields   = array();

		$query = $this->_db->getQuery(true)
			->select('id')
			->from('#__redshopb_field')
			->where($db->qn('field_group_id') . ' = ' . (int) $fieldGroupId)
			->order('id ASC');

		$results = $db->setQuery($query)->loadObjectList();

		foreach ($results as $result)
		{
			$field = RedshopbHelperField::getFieldById($result->id);

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * Gets all the fields that are not selected by a certain filter fieldset
	 *
	 * @param   integer  $fieldGroupId  The Field group ID
	 *
	 * @return  array  An array of unassociated fields
	 */
	public function getUnassociatedFields($fieldGroupId)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('id, name')
			->from($db->qn('#__redshopb_field'))
			->where('id NOT IN(SELECT id FROM #__redshopb_field WHERE field_group_id = ' . (int) $fieldGroupId . ')');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Validate incoming data from the web service for creation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateCreateWS($data)
	{
		// Sets scope to "product" to limit the web service API to custom fields for products
		$data['scope'] = 'product';

		return parent::validateCreateWS($data);
	}

	/**
	 * Method to get a single record using possible related data from the web service
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$item = parent::getItemWS($pk, $addRelatedData);

		// Rejects item for web service if it's not using product scope
		if (!$item || $item->scope != 'product')
		{
			return false;
		}

		return $item;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateUpdateWS($data)
	{
		$data['scope'] = 'product';

		return parent::validateUpdateWS($data);
	}

	/**
	 * Add a field to a field group
	 *
	 * @param   integer  $fieldGroupId  ID of the field group
	 * @param   integer  $fieldId       ID of the field to add
	 *
	 * @return boolean
	 */
	public function addField($fieldGroupId, $fieldId)
	{
		$table = RedshopbTable::getAdminInstance('Field');
		$table->load($fieldId);

		$row['field_group_id'] = $fieldGroupId;

		return (bool) $table->save($row);
	}

	/**
	 * Remove field group from a field
	 *
	 * @param   integer  $fieldId  ID of the field
	 *
	 * @return boolean
	 */
	public function removeField($fieldId)
	{
		$table = RedshopbTable::getAdminInstance('Field');
		$table->load($fieldId);

		$row['field_group_id'] = '';

		return (bool) $table->save($row);
	}
}
