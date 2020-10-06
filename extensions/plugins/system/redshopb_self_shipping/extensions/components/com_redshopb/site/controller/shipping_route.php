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
use Joomla\CMS\Language\Text;

/**
 * Shipping Route Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerShipping_Route extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_SHIPPING_ROUTE';

	/**
	 * Ajax call to get addresses based on selected Company.
	 *
	 * @return  void
	 * @throws Exception
	 */
	public function ajaxGetFieldAddresses()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app       = Factory::getApplication();
		$model     = $this->getModel('Shipping_Route', 'RedshopbModel', array('ignore_request' => false));
		$input     = $app->input;
		$companyId = $input->getInt('company_id', 0);

		if (empty($companyId))
		{
			echo Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_SELECT_COMPANY');
			$app->close();
		}

		$form = $model->getForm();
		$item = $model->getItem($input->getInt('id', 0));

		if ($item)
		{
			$form->setValue('addresses', null, $item->addresses);
		}

		$form->setValue('company_id', null, $companyId);
		echo $form->getInput('addresses');

		$app->close();
	}
}
