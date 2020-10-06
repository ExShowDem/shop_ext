<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 * Field Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       2.0
 */
class RedshopbControllerField extends RedshopbControllerForm
{
	/**
	 * Get Fields ordering via AJAX
	 *
	 * @return void
	 */
	public function ajaxGetOrderingField()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		/** @var  $model RedshopbModelField */
		$model = $this->getModel();
		$data  = $app->input->get('Form', array(), 'array');

		/** @var Form $form */
		$form = $model->getForm(array(), false);

		$form->bind($data);

		echo $form->getInput('ordering');
		$app->close();
	}

	/**
	 * For auto-submit form when client choose type
	 *
	 * @return void
	 */
	public function setType()
	{
		$app      = Factory::getApplication();
		$recordId = $app->input->getInt('id', 0);
		$data     = $app->input->get_Array('jform', array());

		$app->setUserState('com_redshopb.edit.field.data', $data);

		$redirect = RedshopbRoute::_(
			'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false
		);

		$this->setRedirect($redirect);
	}

	/**
	 * Ajax call to get users tab content.
	 *
	 * @return  void
	 */
	public function ajaxFieldValues()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$fieldId = $input->getInt('id');

		if ($fieldId)
		{
			$field       = RedshopbEntityField::load($fieldId);
			$dataFieldId = ($field->getItem()->field_value_xref_id != 0 ? $field->getItem()->field_value_xref_id : $fieldId);
			$model       = RModelAdmin::getInstance('Field_Values', 'RedshopbModel');
			$model->set('filterFormName', 'filter_filed_values_field');
			$state = $model->getState();
			$model->setState('filter.field_id', $dataFieldId);
			$formName   = 'adminFormFieldValues';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('field.field_values', array(
					'state' => $state,
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filterForm' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=field&model=field_values'),
					'return' => base64_encode('index.php?option=com_redshopb&view=field&layout=edit&id='
						. $fieldId . '&tab=field_values'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Get Fields groups via AJAX
	 *
	 * @return void
	 */
	public function ajaxGetFieldGroups()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		/** @var  $model RedshopbModelField */
		$model = $this->getModel();
		$data  = $app->input->get('Form', array(), 'array');

		/** @var Form $form */
		$form = $model->getForm(array(), false);

		$form->bind($data);

		echo $form->getInput('field_group_id');
		$app->close();
	}
}
