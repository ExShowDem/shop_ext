<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Conversion Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.6.26
 */
class RedshopbControllerConversion extends RedshopbControllerForm
{
	/**
	 * Method for load conversion sets form
	 *
	 * @return  void
	 */
	public function ajaxLoadConversionForm()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$productAttributeId = $app->input->getInt('product_attribute', 0);

		if (!$productAttributeId)
		{
			$app->close();
		}

		$model = $this->getModel();
		$form  = $model->getForm();

		$form->setValue('product_attribute_id', null, $productAttributeId);
		$action = RedshopbRoute::_('index.php?option=com_redshopb&task=conversion.ajaxSaveConversion');

		echo RedshopbLayoutHelper::render('product.conversionsets.form', array('form' => $form, 'action' => $action));

		$app->close();
	}

	/**
	 * Method for save Conversion
	 *
	 * @return  void
	 */
	public function ajaxSaveConversion()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$model = $this->getModel();

		$data = $app->input->post->get('jform', array(), 'Array');

		if ($model->save($data))
		{
			echo $model->getState($model->getName() . '.id');
		}
		else
		{
			echo 0;
		}

		$app->close();
	}

	/**
	 * Method for removing Conversion
	 *
	 * @return  void
	 */
	public function ajaxRemoveConversion()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$id                 = $app->input->getInt('id', 0);
		$productAttributeId = $app->input->getInt('product_attribute_id', 0);

		echo (int) RedshopbHelperConversion::removeConversionSet($id, $productAttributeId);

		$app->close();
	}
}
