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
use Joomla\CMS\Router\Route;

/**
 * Product Composition Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct_Composition extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_PRODUCT_COMPOSITION';

	/**
	 * Get the Route object for a redirect to item.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToItemRoute($append = null)
	{
		$redirectUrl = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $append;

		$productId = $this->input->get('product_id', 0, 'int');

		if ($productId)
		{
			$redirectUrl .= '&product_id=' . (int) $productId;
		}

		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			$redirectUrl .= '&return=' . $return;
		}

		return RedshopbRoute::_($redirectUrl, false);
	}

	/**
	 * Get the Route object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			return RedshopbRoute::_(base64_decode($return), false);
		}

		return parent::getRedirectToListRoute($append);
	}

	/**
	 * Get main attribute list for given product id.
	 *
	 * @return void
	 */
	public function ajaxgetmainattributes()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$model     = RedshopbModel::getInstance('Product_Composition', 'RedshopbModel');
		$app       = Factory::getApplication();
		$productId = $app->input->post->getInt('product_id', 0);
		$model->setState('product_id', $productId);
		$form  = $model->getForm();
		$field = $form->getField('flat_attribute_value_id');

		echo $field->renderField();

		$app->close();
	}
}
