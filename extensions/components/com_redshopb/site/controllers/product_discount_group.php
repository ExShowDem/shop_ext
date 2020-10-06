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
 * Discount Product group Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct_Discount_Group extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_DISCOUNT_PRODUCT_GROUP';

	/**
	 * Ajax call to get child companies from a certain company
	 *
	 * @return  void
	 */
	public function ajaxproducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$companyId = $this->input->getInt('companyid', 0);
		$products  = @json_decode($this->input->getString('products'));

		$model = $this->getModel();
		$form  = $model->getForm();
		$form->setFieldAttribute('product_ids', 'emptystart', 'false');
		$form->setFieldAttribute('product_ids', 'companyid', $companyId);
		$form->setValue('product_ids', '', $products);

		echo $form->getInput('product_ids');

		Factory::getApplication()->close();
	}
}
