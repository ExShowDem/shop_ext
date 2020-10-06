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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
/**
 * Scope Fields Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelScope_Fields extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$scope     = $this->getState('filter.scope', 'product');
		$itemId    = (int) $this->getState('filter.item_id', 0);
		$subItemId = $this->getState('filter.subitem_id', 0);
		$fields    = RedshopbHelperField::loadScopeFieldData($scope, $itemId, $subItemId, true);
		$item      = array('extrafields' => array());

		foreach ($fields as $field)
		{
			$fieldDataTable = RTable::getInstance('Field_Data', 'RedshopbTable');
			$fieldDataTable->loadWebserviceRelation($field->id);

			if ($this->isMultipleField($field))
			{
				$item['extrafields']['scope_field_' . $field->field_id][] = $this->getCustomFieldValue($field);
			}
			else
			{
				$item['extrafields']['scope_field_' . $field->field_id] = $this->getCustomFieldValue($field);
			}

			$item['extrafields']['scope_field_' . $field->field_id . '_field_data_id'] = $field->id;
		}

		return $item;
	}

	/**
	 * Method to check if a record is multiple.
	 *
	 * @param   object  $field  Field object
	 *
	 * @return  boolean
	 */
	public function isMultipleField($field)
	{
		if ($field && $field->type_multiple == 1 && $field->multiple_values == 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get a field value
	 *
	 * @param   object  $field  Field object
	 *
	 * @return  mixed
	 */
	public function getCustomFieldValue($field)
	{
		if ($field)
		{
			// We are making an exception for Redshopb Media type as it needs additional information
			if (in_array($field->type_field_name, array('mediaRedshopb')))
			{
				return $field;
			}

			if ($field->value_type == 'field_value')
			{
				return $field->field_value_id;
			}

			return $field->value;
		}

		return '';
	}

	/**
	 * Get the associated Table. We use different method for save so it does not matter which table we use
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'product', $prefix = '', $config = array())
	{
		if (is_null($name))
		{
			$name = 'product';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean True on success, False on error.
	 * @throws Exception
	 */
	public function save($data)
	{
		$scope     = $this->getState('filter.scope', 'product');
		$itemId    = (int) $this->getState('filter.item_id', 0);
		$subItemId = (int) $this->getState('filter.subitem_id', 0);
		$table     = $this->getTable();

		return RedshopbHelperField::storeScopeFieldData($scope, $itemId, $subItemId, $data, true, $table->getOption('lockingMethod', 'User'));
	}

	/**
	 * Method to return the form for a single field.
	 *
	 * @param   object  $field  Field object
	 *
	 * @return  Form
	 */
	public function getSingleFieldForm($field)
	{
		if ($field)
		{
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');

			$form     = $xml->addChild('form');
			$fieldSet = $form->addChild('fieldset');
			$fieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
			$fieldSetFields = $fieldSet->addChild('fields');
			$fieldSetFields->addAttribute('name', 'extrafields');
			$formField = $fieldSetFields->addChild('field');
			$multiple  = (int) RedshopbEntityType::load($field->type_id)->get('multiple', 0);
			$multiple  = ($multiple && $field->multiple_values) ? 1 : 0;

			// Field data
			$formField->addAttribute('name', 'scope_field_' . $field->id);
			$formField->addAttribute('label', $field->title);
			$formField->addAttribute('description', $field->description);
			$formField->addAttribute('alias', $field->alias);
			$formField->addAttribute('value_type', $field->value_type);
			$formField->addAttribute('field_id', $field->id);
			$formField->addAttribute('multiple', $multiple);
			$formField->addAttribute('required', $field->required);
			$formField->addAttribute('multiple_values', $multiple);

			$default = $field->default_value;

			if (!empty($default))
			{
				$formField->addAttribute('default', $default);
			}

			$fieldType = ($field->multiple_values && strtolower($field->field_name) == 'rtext') ? 'TextMultiple' : $field->field_name;
			$formField->addAttribute('type', $fieldType);

			if ($fieldType == 'mediaRedshopb')
			{
				$formField->addAttribute('form_group', 'extrafields');
				unset($formField['multiple']);
			}

			// Get the form.
			$form = $this->loadForm(
				$this->context . '.' . $this->name,
				$xml->asXML(),
				array(
					'control' => 'jform',
					'load_data' => false
				)
			);

			return $form;
		}

		return null;
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
		// Get the form.
		$form = $this->loadForm(
			$this->context . '.' . $this->formName,
			$this->getFormXml()->asXML(),
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to return a formatted SimpleXMLElement
	 *
	 * @return SimpleXMLElement
	 */
	public function getFormXml()
	{
		$scope        = (string) $this->getState('filter.scope', 'product');
		$requiredOnly = (boolean) $this->getState('filter.requiredOnly', false);
		$b2cOnly      = (boolean) $this->getState('filter.b2c', false);

		switch ($scope)
		{
			case 'product':
				return $this->getProductFormXml($requiredOnly);
			case 'user':
				return $this->getUserFormXml($b2cOnly);
			case 'category':
				return $this->getCategoryFormXml();
			case 'company':
				if ($b2cOnly)
				{
					return $this->getCompanyFormXml();
				}

				break;

			default:
				break;
		}

		$fieldsUsedInTemplate = (array) $this->getState('filter.fieldsUsedInTemplate', array());

		$inTemplateAllFields    = in_array('product.fields-data', $fieldsUsedInTemplate);
		$inTemplateAllDocuments = in_array('product.fields-documents', $fieldsUsedInTemplate);
		$inTemplateAllFiles     = in_array('product.fields-files', $fieldsUsedInTemplate);
		$inTemplateAllVideos    = in_array('product.fields-videos', $fieldsUsedInTemplate);
		$inTemplateAllImages    = in_array('product.fields-field-images', $fieldsUsedInTemplate);

		$form = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');

		$fieldSet = $form->addChild('fieldset');
		$fieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$fieldSet->addAttribute('name', 'fields_in_template');
		$fieldSet->addAttribute('title', 'COM_REDSHOPB_PRODUCT_FIELDS_FIELDS_IN_TEMPLATE');
		$fieldSet->addAttribute('extra_field_set', 'true');
		$fieldSetFields = $fieldSet->addChild('fields');
		$fieldSetFields->addAttribute('name', 'extrafields');

		$fieldSetNoTemplate = $form->addChild('fieldset');
		$fieldSetNoTemplate->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$fieldSetNoTemplate->addAttribute('name', 'fields_not_in_template');
		$fieldSetNoTemplate->addAttribute('title', 'COM_REDSHOPB_PRODUCT_FIELDS_FIELDS_NOT_IN_TEMPLATE');
		$fieldSetNoTemplate->addAttribute('extra_field_set', 'true');
		$fieldSetNoTemplateFields = $fieldSetNoTemplate->addChild('fields');
		$fieldSetNoTemplateFields->addAttribute('name', 'extrafields');

		$fields = RedshopbHelperField::getFields($scope, $requiredOnly);

		if (empty($fields[$scope]))
		{
			return $form;
		}

		foreach ($fields[$scope] as $field)
		{
			if ($field->state != 1)
			{
				continue;
			}

			switch ($field->type_alias)
			{
				case 'documents':
					$inTemplate = $inTemplateAllDocuments;
					break;
				case 'files':
					$inTemplate = $inTemplateAllFiles;
					break;
				case 'videos':
					$inTemplate = $inTemplateAllVideos;
					break;
				case 'field-images':
					$inTemplate = $inTemplateAllImages;
					break;
				default:
					$inTemplate = $inTemplateAllFields;
					break;
			}

			$multiple = (int) RedshopbEntityType::load($field->type_id)->get('multiple', 0);
			$multiple = ($multiple && $field->multiple_values) ? 1 : 0;

			if (!$inTemplate)
			{
				$inTemplate = in_array('fields.' . $field->alias, $fieldsUsedInTemplate);
			}

			$setToField = $inTemplate ? $fieldSetFields : $fieldSetNoTemplateFields;

			$this->addXmlField($setToField, $field, $multiple);
		}

		return $form;
	}

	/**
	 * Method to add field definition xml to a parent element
	 *
	 * @param   SimpleXMLElement  $setToFieldset  Parent fieldset to add the field definition to
	 * @param   object            $field          Field definition
	 * @param   int               $multiple       Is it a multiple value field
	 *
	 * @return boolean
	 */
	protected function addXmlField($setToFieldset, $field, $multiple)
	{
		$formField = $setToFieldset->addChild('field');

		// Hidden fields data identifier
		$formFieldIdentifier = $setToFieldset->addChild('field');
		$formFieldIdentifier->addAttribute('type', 'hidden');
		$formFieldIdentifier->addAttribute('name', 'scope_field_' . $field->id . '_field_data_id');

		// Field data
		$formField->addAttribute('name', 'scope_field_' . $field->id);
		$formField->addAttribute('label', $field->title);
		$formField->addAttribute('description', $field->description);
		$formField->addAttribute('alias', $field->alias);
		$formField->addAttribute('value_type', $field->value_type);
		$formField->addAttribute('field_id', $field->id);
		$formField->addAttribute('required', $field->required);
		$formField->addAttribute('multiple', $multiple);
		$formField->addAttribute('multiple_values', $multiple);

		$default = $field->default_value;

		$fieldType = ($field->multiple_values && strtolower($field->field_name) == 'rtext') ? 'TextMultiple' : $field->field_name;
		$formField->addAttribute('type', $fieldType);

		if ($fieldType == 'mediaRedshopb')
		{
			$formField->addAttribute('form_group', 'extrafields');
			unset($formField['multiple']);
		}

		if ($field->value_type == 'field_value')
		{
			$fieldValuedXrefId = !empty($field->field_value_xref_id) ? $field->field_value_xref_id : $field->id;
			$options           = RedshopbHelperField::getFieldValues($fieldValuedXrefId, array(), false, 0);

			if ($options)
			{
				if ($field->field_name != 'checkboxes' && $field->field_name != 'radio')
				{
					$fieldOptionDefault = $formField->addChild('option', Text::_('JOPTION_SELECT'));
					$fieldOptionDefault->addAttribute('value', '');
				}

				foreach ($options as $option)
				{
					if ($option->default == 1)
					{
						$default = $option->identifier;
					}

					$fieldOptionDefault = $formField->addChild('option', htmlspecialchars($option->name));
					$fieldOptionDefault->addAttribute('value', $option->identifier);
				}
			}
		}
		elseif ($field->value_type == 'int_value')
		{
			if ($field->field_name == 'radioRedshopb')
			{
				$fieldOptionDefault = $formField->addChild('option', Text::_('JNO'));
				$fieldOptionDefault->addAttribute('value', '0');
				$fieldOptionDefault = $formField->addChild('option', Text::_('JYES'));
				$fieldOptionDefault->addAttribute('value', '1');
			}
		}

		if (!empty($default))
		{
			$formField->addAttribute('default', $default);
		}

		return true;
	}

	/**
	 * Method to get user extra fields form xml.
	 *
	 * @param   boolean  $b2cOnly  B2C only
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getUserFormXml($b2cOnly = false)
	{
		$fields = RedshopbHelperField::getB2CUserFields();
		$form   = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');

		if (empty($fields))
		{
			return $form;
		}

		$fieldSet = $form->addChild('fieldset');
		$fieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$fieldSet->addAttribute('name', 'user_extra_fields');
		$fieldSet->addAttribute('extra_field_set', 'true');
		$fieldSet->addAttribute('title', 'COM_REDSHOPB_USER_REGISTER_EXTRA_FIELDS');
		$fieldSetFields = $fieldSet->addChild('fields');
		$fieldSetFields->addAttribute('name', 'extrafields');

		foreach ($fields['user'] as $field)
		{
			if ($b2cOnly && (boolean) $field->b2c === false)
			{
				continue;
			}

			if ((boolean) $field->state === false)
			{
				continue;
			}

			$multiple = (int) RedshopbEntityType::load($field->type_id)->get('multiple', 0);
			$multiple = ($multiple && $field->multiple_values) ? 1 : 0;
			$this->addXmlField($fieldSetFields, $field, $multiple);
		}

		return $form;
	}

	/**
	 * Method to get company extra fields form xml.
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getCompanyFormXml()
	{
		$fields = RedshopbHelperField::getFields('company');
		$form   = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');

		if (empty($fields))
		{
			return $form;
		}

		$fieldSet = $form->addChild('fieldset');
		$fieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$fieldSet->addAttribute('name', 'company_extra_fields');
		$fieldSet->addAttribute('extra_field_set', 'true');
		$fieldSet->addAttribute('title', 'COM_REDSHOPB_USER_REGISTER_EXTRA_FIELDS');
		$fieldSetFields = $fieldSet->addChild('fields');
		$fieldSetFields->addAttribute('name', 'extrafields');

		foreach ($fields['company'] as $field)
		{
			if ((boolean) $field->b2c === false || (boolean) $field->state === false)
			{
				continue;
			}

			$multiple = (int) RedshopbEntityType::load($field->type_id)->get('multiple', 0);
			$multiple = ($multiple && $field->multiple_values) ? 1 : 0;
			$this->addXmlField($fieldSetFields, $field, $multiple);
		}

		return $form;
	}

	/**
	 * Method to get the form XML definitions divided into global, local and other field sets.
	 *
	 * @param   boolean  $requiredOnly  Get required only.
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getProductFormXml($requiredOnly)
	{
		$form = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');

		$globalFieldSet = $form->addChild('fieldset');
		$globalFieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$globalFieldSet->addAttribute('name', 'global_fields');
		$globalFieldSet->addAttribute('title', 'COM_REDSHOPB_PRODUCT_FIELDS_FIELDS_GLOBAL_FIELDS');
		$globalFieldSet->addAttribute('extra_field_set', 'true');
		$globalFields = $globalFieldSet->addChild('fields');
		$globalFields->addAttribute('name', 'extrafields');

		$localFieldSet = $form->addChild('fieldset');
		$localFieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$localFieldSet->addAttribute('name', 'local_fields');
		$localFieldSet->addAttribute('title', 'COM_REDSHOPB_PRODUCT_FIELDS_FIELDS_LOCAL_FIELDS');
		$localFieldSet->addAttribute('extra_field_set', 'true');
		$localFields = $localFieldSet->addChild('fields');
		$localFields->addAttribute('name', 'extrafields');

		$otherFieldset = $form->addChild('fieldset');
		$otherFieldset->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$otherFieldset->addAttribute('name', 'other_fields');
		$otherFieldset->addAttribute('title', 'COM_REDSHOPB_PRODUCT_FIELDS_FIELDS_OTHER_FIELDS');
		$otherFieldset->addAttribute('extra_field_set', 'true');
		$otherFields = $otherFieldset->addChild('fields');
		$otherFields->addAttribute('name', 'extrafields');

		$fields = RedshopbHelperField::getFields('product', $requiredOnly);

		if (empty($fields['product']))
		{
			return $form;
		}

		$productCategories = $this->getState('filter.categories', array());

		foreach ($fields['product'] as $field)
		{
			if ($field->state != 1)
			{
				continue;
			}

			$multiple = (int) RedshopbEntityType::load($field->type_id)->get('multiple', 0);
			$multiple = ($multiple && $field->multiple_values) ? 1 : 0;

			$setToField = $otherFields;

			if ($field->local != 0 && $this->inProductCategories($productCategories, $field->categories))
			{
				$setToField = $localFields;
			}

			if ($field->global != 0)
			{
				$setToField = $globalFields;
			}

			$this->addXmlField($setToField, $field, $multiple);
		}

		return $form;
	}

	/**
	 * Method to get the form XML definitions divided into global, local and other field sets.
	 *
	 * @return SimpleXMLElement
	 */
	protected function getCategoryFormXml()
	{
		$form = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form></form>');

		$globalFieldSet = $form->addChild('fieldset');
		$globalFieldSet->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$globalFieldSet->addAttribute('name', 'global_fields');
		$globalFieldSet->addAttribute('title', 'COM_REDSHOPB_CATEGORY_FIELDS_FIELDS_GLOBAL_FIELDS');
		$globalFieldSet->addAttribute('extra_field_set', 'true');
		$globalFields = $globalFieldSet->addChild('fields');
		$globalFields->addAttribute('name', 'extrafields');

		$otherFieldset = $form->addChild('fieldset');
		$otherFieldset->addAttribute('addfieldpath', '/libraries/redshopb/form/fields');
		$otherFieldset->addAttribute('name', 'other_fields');
		$otherFieldset->addAttribute('title', 'COM_REDSHOPB_CATEGORY_FIELDS_FIELDS_OTHER_FIELDS');
		$otherFieldset->addAttribute('extra_field_set', 'true');
		$otherFields = $otherFieldset->addChild('fields');
		$otherFields->addAttribute('name', 'extrafields');

		$fields = RedshopbHelperField::getFields('category');

		if (empty($fields['category']))
		{
			return $form;
		}

		foreach ($fields['category'] as $field)
		{
			if ($field->state != 1)
			{
				continue;
			}

			$multiple = (int) RedshopbEntityType::load($field->type_id)->get('multiple', 0);
			$multiple = ($multiple && $field->multiple_values) ? 1 : 0;

			$setToField = $otherFields;

			if ($field->global != 0)
			{
				$setToField = $globalFields;
			}

			$this->addXmlField($setToField, $field, $multiple);
		}

		return $form;
	}

	/**
	 * Method to get the form XML definitions divided into global, local and other field sets.
	 *
	 * @param   array  $productCategories  Product Categories
	 * @param   array  $fieldCategories    Field Categories
	 *
	 * @return boolean
	 */
	protected function inProductCategories($productCategories, $fieldCategories)
	{
		foreach ($fieldCategories AS $fieldCategory)
		{
			if (in_array($fieldCategory, $productCategories))
			{
				return true;
			}
		}

		return false;
	}
}
