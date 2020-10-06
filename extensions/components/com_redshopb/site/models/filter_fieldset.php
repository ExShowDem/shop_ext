<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
/**
 * Filter Fieldset Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelFilter_Fieldset extends RedshopbModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		// If it's not called from the web service (default) it loads the full field information, not just the id
		if (!$this->getState('load.ws', false))
		{
			$table->setOption('fields_relate.full.load', true);
		}

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);

		$item = ArrayHelper::toObject($properties, CMSObject::class);

		// This is needed because toObject will transform
		// The field array to an object.
		if (isset($properties['fields']) && !empty($properties['fields']))
		{
			$fields = $properties['fields'];

			// We have to add a custom check for Field restriction for webservices
			if ($this->getState('load.ws', false)
				&& RedshopbApp::getConfig()->get('use_webservice_permission_restriction', 0) == 1)
			{
				foreach ($fields as $key => $fieldId)
				{
					if (!(RedshopbHelperWebservice_Permission::checkWSPermissionRestrictionItem($fieldId, 'field') == 1))
					{
						unset($fields[$key]);
					}
				}
			}

			$item->fields = $fields;
		}
		else
		{
			$item->fields = array();
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Gets all the fields that are not selected by a certain filter fieldset
	 *
	 * @param   integer  $pk      Id of the filter fieldset
	 * @param   string   $search  Field name to search.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	public function getUnselectedFields($pk = null, $search = '')
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('f.id', 'field_id'),
				$db->qn('f.name', 'field_name'),
				$db->qn('f.alias', 'type_code'),
				$db->qn('t.name', 'field_type_name')
			)
		);

		$query->from($db->qn('#__redshopb_field', 'f'));
		$query->join('LEFT', $db->qn('#__redshopb_type', 't') . ' ON t.id = f.type_id');

		$query->where('NOT EXISTS(SELECT field_id FROM #__redshopb_filter_fieldset_xref WHERE fieldset_id = ' . (int) $pk . ' AND field_id = f.id)');

		if (!empty($search))
		{
			$query->where('LOWER(' . $db->qn('f.name') . ') LIKE ' . $db->q('%' . strtolower($search) . '%'));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *  Validate web service data for fieldAdd function
	 *
	 * @param   int  $data  Data to be validated ('field_id')
	 *
	 * @return  array | false
	 */
	public function validatefieldAddWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'field');
	}

	/**
	 *  Add a field to the filter
	 *
	 * @param   int  $filterId  id of filter
	 * @param   int  $fieldId   id of field
	 *
	 * @return  boolean Filter ID on success. False otherwise.
	 */
	public function fieldAdd($filterId, $fieldId)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$filterTable = $this->getTable();

		if (!$filterTable->load($filterId))
		{
			return false;
		}

		$fieldTable = RedshopbTable::getAdminInstance('Field');

		if (!$fieldTable->load($fieldId))
		{
			return false;
		}

		$fields = $filterTable->get('fields');

		if (array_search($fieldId, $fields) === false)
		{
			$fields[] = $fieldId;
			$filterTable->set('fields', $fields);
			$filterTable->setOption('fields_relate.store', true);

			if (!$filterTable->save(array()))
			{
				return false;
			}
		}

		return $filterId;
	}

	/**
	 *  Validate web service data for fieldRemove function
	 *
	 * @param   int  $data  Data to be validated ('field_id')
	 *
	 * @return  array | false
	 */
	public function validateFieldRemoveWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'field');
	}

	/**
	 *  Remove a field from a filter
	 *
	 * @param   int  $filterId  id of filter
	 * @param   int  $fieldId   id of field
	 *
	 * @return  boolean Field ID on success. False otherwise.
	 */
	public function fieldRemove($filterId, $fieldId)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$filterTable = $this->getTable();

		if (!$filterTable->load($filterId))
		{
			return false;
		}

		$fields = $filterTable->get('fields');
		$i      = array_search($fieldId, $fields);

		if ($i !== false)
		{
			unset($fields[$i]);
			$filterTable->set('fields', $fields);
		}
		else
		{
			return $filterId;
		}

		$filterTable->setOption('fields_relate.store', true);

		if (!$filterTable->save(array()))
		{
			return false;
		}

		return $filterId;
	}
}
