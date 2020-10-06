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
 * Addresses Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerField_Group extends RedshopbControllerForm
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
	 * Method for adding a field assocation through ajax.
	 *
	 * @return  integer|void
	 */
	public function ajaxAddFieldAssociation()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$fieldId      = $app->input->getInt('field_id', 0);
		$fieldGroupId = $app->input->getInt('item_id', 0);

		if (!$fieldGroupId || !$fieldId)
		{
			return 0;
		}

		$model = $this->getModel('Field_Group');

		echo (int) $model->addField($fieldGroupId, $fieldId);

		$app->close();
	}

	/**
	 * Method for removing a field assocation through ajax.
	 *
	 * @return  integer|void
	 */
	public function ajaxRemoveFieldAssociation()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$fieldId      = $app->input->getInt('field_id', 0);
		$fieldGroupId = $app->input->getInt('item_id', 0);

		if (!$fieldGroupId || !$fieldId)
		{
			return 0;
		}

		$model = $this->getModel('Field_Group');

		echo (int) $model->removeField($fieldId);

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

		echo $form->getInput('field_association');
		$app->close();
	}
}
