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
 * Category Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCategory extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_CATEGORY';

	/**
	 * Get parent categories for give company.
	 *
	 * Prints HTML select for parent categories.
	 *
	 * @return void
	 */
	public function ajaxGetParentCategories()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app     = Factory::getApplication();
		$company = $app->input->getInt('company_id', 0);
		$parent  = $app->input->getInt('parent_id', 0);
		$model   = $this->getModel();
		$form    = $model->getForm();

		$form->setFieldAttribute('parent_id', 'emptystart', 'false');
		$form->setFieldAttribute('parent_id', 'companyid', $company);
		$form->setValue('parent_id', '', $parent);

		echo $form->getInput('parent_id');

		$app->close();
	}

	/**
	 * Overridden to normalize the input for WS
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->normalizeImageInput();

		return parent::save($key, $urlVar);
	}

	/**
	 * Method for adding a field assocation through ajax.
	 *
	 * @return  integer|void
	 */
	public function ajaxAddFieldAssociation()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app        = Factory::getApplication();
		$fieldId    = $app->input->getInt('field_id', 0);
		$categoryId = $app->input->getInt('item_id', 0);

		if (!$categoryId || !$fieldId)
		{
			return 0;
		}

		$model = $this->getModel('Category');

		echo (int) $model->addFieldAssociation($categoryId, $fieldId);

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

		$app        = Factory::getApplication();
		$fieldId    = $app->input->getInt('field_id', 0);
		$categoryId = $app->input->getInt('item_id', 0);

		if (!$categoryId || !$fieldId)
		{
			return 0;
		}

		$model = $this->getModel('Category');

		echo (int) $model->removeFieldAssociation($categoryId, $fieldId);

		$app->close();
	}
}
