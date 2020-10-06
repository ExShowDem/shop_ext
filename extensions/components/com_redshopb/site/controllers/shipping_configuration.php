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
 * Shipping Configuration Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerShipping_Configuration extends RedshopbControllerForm
{
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
			$append .= '&tab=shipping_configurations';
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
	 * Ajax call to get the shipping configuration
	 *
	 * @return  void
	 */
	public function ajaxshippingplugin()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$shippingPluginName      = $this->input->getString('shipping_name');
		$shippingConfigurationId = $this->input->getInt('shipping_configuration_id');

		$shippingConfigurationModel = RModel::getAdminInstance('Shipping_Configuration');
		$shippingConfigurationModel->getState();
		$shippingConfigurationModel->setState('shipping_name', $shippingPluginName);

		$shippingItem = $shippingConfigurationModel->getItem($shippingConfigurationId);

		if ($shippingItem->shipping_name != $shippingPluginName)
		{
			$shippingItem = $shippingConfigurationModel->getItem();
		}

		if ($shippingItem->shipping_name != '')
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
					'form' => RTranslationHelper::loadParamsForm($column, $element, $shippingItem, 'plugin'),
					'column' => $column,
					'translationForm' => true,
				),
				JPATH_ADMINISTRATOR . '/components/com_redcore/layouts'
			);
		}

		Factory::getApplication()->close();
	}
}
