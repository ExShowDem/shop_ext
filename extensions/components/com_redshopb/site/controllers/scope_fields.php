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

/**
 * Scope Fields Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerScope_Fields extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_SCOPE_FIELDS';

	/**
	 * Saves price for collection product item
	 *
	 * @return  void
	 */
	public function ajaxnewfielddatarow()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$fieldId = $input->getInt('field_id', 0);

		if (!empty($fieldId))
		{
			$field = RedshopbHelperField::getFieldById($fieldId);
			$model = $this->getModel();
			$form  = $model->getSingleFieldForm($field);

			echo $form->getInput('scope_field_' . $field->id, 'extrafields');
		}

		$app->close();
	}
}
