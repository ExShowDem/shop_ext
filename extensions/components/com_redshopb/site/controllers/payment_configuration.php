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
 * Payment Configuration Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerPayment_Configuration extends RedshopbControllerForm
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$lang = Factory::getLanguage();
		$lang->load('com_redcore', JPATH_ADMINISTRATOR)
		|| $lang->load('com_redcore', JPATH_ADMINISTRATOR, RTranslationHelper::getSiteLanguage());
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		// Append the tab name for the company view
		if (RedshopbInput::isFromPriceDebtorGroup())
		{
			$append .= '&tab=payment_configurations';
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$priceDebtorGroupId = RedshopbInput::getField('owner_name');

		if ($priceDebtorGroupId)
		{
			$append .= '&jform[price_debtor_group_id]=' . $priceDebtorGroupId;
		}

		return $append;
	}

	/**
	 * Ajax call to get the payment configuration
	 *
	 * @return  void
	 */
	public function ajaxpaymentplugin()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$paymentPluginName      = $this->input->getString('payment_name');
		$paymentConfigurationId = $this->input->getInt('payment_configuration_id');

		$paymentConfigurationModel = RModel::getAdminInstance('Payment_Configuration', array(), 'com_redcore');
		$paymentConfigurationModel->getState();
		$paymentConfigurationModel->setState('payment_name', $paymentPluginName);

		$paymentItem = $paymentConfigurationModel->getItem($paymentConfigurationId);

		if ($paymentItem->payment_name != $paymentPluginName)
		{
			$paymentItem = $paymentConfigurationModel->getItem();
		}

		if ($paymentItem->payment_name != '')
		{
			$lang = Factory::getLanguage();
			$lang->load('com_plugins', JPATH_ADMINISTRATOR)
				|| $lang->load('com_plugins', JPATH_ADMINISTRATOR, RTranslationHelper::getSiteLanguage());

			$element                 = new RTranslationContentElement('com_plugins', '');
			$element->name           = 'plugins';
			$element->extension_name = 'com_plugins';
			$column                  = array('name' => 'params', 'formname' => 'plugin');
			echo RedshopbLayoutHelper::render(
				'translation.params',
				array(
					'form' => RTranslationHelper::loadParamsForm($column, $element, $paymentItem, 'plugin'),
					'column' => $column,
					'translationForm' => true,
				),
				JPATH_ADMINISTRATOR . '/components/com_redcore/layouts'
			);
		}

		Factory::getApplication()->close();
	}
}
