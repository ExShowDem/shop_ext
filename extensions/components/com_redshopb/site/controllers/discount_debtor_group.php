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
 * Discount debtor group Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerDiscount_Debtor_Group extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_DISCOUNT_DEBTOR_GROUP';

	/**
	 * Ajax call to get child companies from a certain company
	 *
	 * @return  void
	 */
	public function ajaxcompanies()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$companyId = $this->input->getString('companyid');
		$companies = @json_decode($this->input->getString('companies'));

		$model = $this->getModel();
		$form  = $model->getForm();
		$form->setFieldAttribute('customer_ids', 'emptystart', 'false');
		$form->setFieldAttribute('customer_ids', 'companyid', $companyId);
		$form->setValue('customer_ids', '', $companies);

		echo $form->getInput('customer_ids');

		Factory::getApplication()->close();
	}
}
