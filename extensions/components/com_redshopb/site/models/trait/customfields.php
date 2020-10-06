<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;

/**
 * Trait for models with custom fields
 *
 * @since  1.12.61
 */
trait RedshopbModelsTraitCustomFields
{
	/**
	 * Fields scope.
	 *
	 * @var   string
	 *
	 * @since 1.12.61
	 */
	private $scope;

	/**
	 * Scope getter.
	 *
	 * @return  string  Entity scope.
	 *
	 * @since   1.12.61
	 */
	public function getScope()
	{
		return $this->scope;
	}

	/**
	 * Scope setter.
	 *
	 * @param   string  $scope  Scope to set.
	 *
	 * @return  void
	 *
	 * @since   1.12.61
	 */
	public function setScope($scope)
	{
		$this->scope = $scope;
	}

	/**
	 * Method to add the extra field xml to the form
	 *
	 * @param   Form    $form            The product form
	 * @param   array   $customOptions   Custom options for get fields.
	 *
	 * @return  void
	 *
	 * @since   1.12.61
	 */
	public function addExtraFields($form, $customOptions = array())
	{
		$id = $form->getValue('id', 0);

		/** @var RedshopbModelScope_Fields $model */
		$model = RModelAdmin::getInstance('Scope_Fields', 'RedshopbModel');
		$model->getState();
		$model->setState('filter.scope', $this->scope);
		$model->setState('filter.item_id', $id);
		$model->setState('filter.requiredOnly', empty($id));

		if ($this->scope === 'category')
		{
			$template             = RedshopbHelperTemplate::findTemplate('category', 'shop', $form->getValue('template_id'));
			$fieldsUsedInTemplate = RedshopbHelperTemplate::getUsedTagsInTemplate($template);

			$model->setState('filter.fieldsUsedInTemplate', $fieldsUsedInTemplate);
		}

		if (!empty($customOptions))
		{
			foreach ($customOptions as $key => $value)
			{
				$model->setState($key, $value);
			}
		}

		$extraFieldsXml = $model->getFormXml();

		$form->load($extraFieldsXml->asXML(), false);
	}

	/**
	 * Method to attach the extra fields data to the item
	 *
	 * @param   object         $item   The record item
	 * @param   RedshopbModel  $model  Model using the trait.
	 *
	 * @return  void
	 *
	 * @since   1.12.61
	 */
	public function attachExtraFields($item, $model = null)
	{
		if (empty($item->id))
		{
			return;
		}

		/** @var RedshopbModelScope_Fields $model */
		$sModel = RModelAdmin::getInstance('Scope_Fields', 'RedshopbModel');
		$sModel->getState();
		$sModel->setState('filter.scope', $this->scope);
		$sModel->setState('filter.item_id', $item->id);

		if ($this->scope == 'category')
		{
			$template             = RedshopbHelperTemplate::findTemplate('category', 'shop', $item->template_id);
			$fieldsUsedInTemplate = RedshopbHelperTemplate::getUsedTagsInTemplate($template);
			$sModel->setState('filter.fieldsUsedInTemplate', $fieldsUsedInTemplate);

			if (!is_null($model))
			{
				$model->setState('fieldsUsedInTemplate', $fieldsUsedInTemplate);
			}
		}

		$extraFields = $sModel->getItem();

		$item->extrafields = $extraFields['extrafields'];
	}

	/**
	 * Function for checking if there are any required custom fields for this entity.
	 *
	 * @return  boolean  True if there are some, false otherwise.
	 *
	 * @since   1.12.61
	 */
	public function areThereAnyRequiredFields()
	{
		$fields = RedshopbHelperField::getFields($this->scope, true);

		if (!empty($fields) && !empty($fields[$this->scope]) && count($fields[$this->scope]) > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Function for adding form validation on custom fields data.
	 *
	 * @param   Form  $form  Form for adding custom fields validation.
	 *
	 * @return  void
	 *
	 * @since   1.12.61
	 */
	public function addCustomFieldsValidation($form)
	{
		$fields = RedshopbHelperField::getFields($this->scope, true);

		if (!empty($fields) && !empty($fields[$this->scope]))
		{
			$fields = $fields[$this->scope];

			foreach ($fields as $id => $field)
			{
				$form->setFieldAttribute('scope_field_' . $id, 'required', 'true', 'extrafields');
			}
		}
	}
}
