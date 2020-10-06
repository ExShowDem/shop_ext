<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;

/**
 * Field Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelField extends RedshopbModelAdmin
{
	/**
	 * @var array
	 */
	protected static $multipleTypes;

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   12.2
	 */
	protected function getReorderConditions($table)
	{
		$db        = $this->getDbo();
		$condition = $db->qn('scope') . '=' . $db->q($table->scope) . ' AND state >= 0';

		return array($condition);
	}

	/**
	 * Method for getting the field values for specific field.
	 *
	 * @param   int  $fieldPk  Field ID
	 *
	 * @return  array  Array of field values
	 */
	public function getFieldValues($fieldPk)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->qn('#__redshopb_field_value'))
			->where($db->qn('field_id') . ' = ' . $db->q((int) $fieldPk))
			->order($db->qn('ordering') . ' ASC');

		$db->setQuery($query);
		$values = $db->loadObjectList();

		if (!is_array($values))
		{
			return array();
		}

		return $values;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$name = $this->context . '.' . $this->formName;
		$src  = $this->formName;

		if (isset($data['formName']))
		{
			$name = $this->context . '.' . $data['formName'];
			$src  = $data['formName'];
		}

		// Get the form.
		$form = $this->loadForm(
			$name,
			$src,
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		$fieldType = (isset($data['type_id'])) ? $data['type_id'] : $form->getValue('type_id');

		// Check multiple values option support
		if (!in_array($fieldType, $this->getMultipleTypes()))
		{
			$form->removeField('multiple_values');
		}

		// Check render values option support
		if (!in_array($fieldType, RedshopbHelperFilter::getFilterTypesNeedPrepareValues()))
		{
			$form->removeField('only_available');
		}

		return $form;
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

		// Converts params field to JSON code so it can be displayed in read item operation
		if (count($item->params))
		{
			$item->params = json_encode($item->params);
		}
		else
		{
			$item->params = '';
		}

		return $item;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  false|array     Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$return = parent::validate($form, $data, $group);

		if (!$return)
		{
			return false;
		}

		if (!isset($data['multiple_values']) || !$data['multiple_values'])
		{
			return $data;
		}

		$type = RedshopbEntityType::load($data['type_id']);

		if (!$type)
		{
			return false;
		}

		if (!$type->get('multiple', 0))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_TYPE_NO_MULTIPLE', $type->get('name')), 'warning');

			return false;
		}

		return $data;
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

		if ($data['field_group_id'])
		{
			$fieldGroups = RedshopbHelperField::getFieldGroups();
			$fieldGroup  = $fieldGroups[$data['field_group_id']];

			if ($fieldGroup->scope != 'product')
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_FIELD_GROUP_WRONG_SCOPE'), 'error');

				return false;
			}
		}

		return parent::validateCreateWS($data);
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

		if ($data['field_group_id'])
		{
			$fieldGroups = RedshopbHelperField::getFieldGroups();
			$fieldGroup  = $fieldGroups[$data['field_group_id']];

			if ($fieldGroup->scope != 'product')
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_FIELD_GROUP_WRONG_SCOPE'), 'error');

				return false;
			}
		}

		return parent::validateUpdateWS($data);
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateWS($data)
	{
		$fieldValueXrefSet = false;

		// Sets the right related fields
		if (isset($data['values_field_id']))
		{
			$fieldValueXrefSet           = $data['values_field_id'];
			$data['field_value_xref_id'] = $data['values_field_id'];
		}

		$data = parent::validateWS($data);

		if (!$data)
		{
			return false;
		}

		if ($fieldValueXrefSet !== false && !isset($data['field_value_xref_id']))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $fieldValueXrefSet), 'error');

			return false;
		}

		return $data;
	}

	/**
	 * Method for get all multiple type
	 *
	 * @return  array  List of field type which is support multiple values
	 */
	public function getMultipleTypes()
	{
		if (is_null(self::$multipleTypes))
		{
			self::$multipleTypes = array();

			$typesModel = RedshopbModel::getAutoInstance('Types');
			$typesModel->setState('filter.multiple', 1);
			$types = $typesModel->getItems();

			if ($types)
			{
				foreach ($types as $type)
				{
					self::$multipleTypes[] = $type->id;
				}
			}
		}

		return self::$multipleTypes;
	}
}
