<?php
/**
 * @package     Aesir\E-Commerce\Plugin\Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Default shipping Redshipping plugin.
 *
 * @since 2.1.0
 *
 * @property ShippingHelperGLS $shippingHelper
 */
class PlgRedshippingGLS extends RedshopbShippingPluginBase
{
	/**
	 * @var string
	 */
	protected $shippingName = 'gls';

	/**
	 * Generate GLS Parcel Labels
	 *
	 * @param   integer $orderId Used for getting the order
	 *
	 * @throws Exception If {@see Factory::getApplication()} fails
	 *
	 * @return null
	 *
	 * @since 2.1.0
	 */
	public function onRedshopbAfterOrderStore($orderId)
	{
		if ($this->params->get('service', 0) === $this->shippingHelper::SERVICE_NONE)
		{
			return null;
		}

		$input   = Factory::getApplication()->input;
		$session = Factory::getSession();

		$shippingRateId = $input->get('shipping_rate_id', '');

		$service = $input->get('gls_delivery_service-' . $shippingRateId, $session->get('gls_delivery_service', null, 'redshipping_gls'));

		if (null !== $service)
		{
			$this->shippingHelper->generateLabel(
				RedshopbEntityOrder::getInstance($orderId),
				$input->getString('ParcelShopId', $session->get('gls_parcelshop_id', null, 'redshipping_gls')),
				$service
			);
		}

		$session->clear('gls_delivery_service', 'redshipping_gls');
		$session->clear('gls_parcelshop_id', 'redshipping_gls');
	}

	/**
	 * Validate the GLS Parcel Shop selection
	 *
	 * @param   boolean $return            Return value for the validation method
	 * @param   integer $customerId        Customer id of the current customer
	 * @param   string  $customerType      Customer type of the current customer
	 * @param   integer $deliveryAddressId Delivery address id for the current customer
	 *
	 * @throws Exception If {@see Factory::getApplication()} fails
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function onAESECValidateShipping(&$return, $customerId, $customerType, $deliveryAddressId)
	{
		$app        = Factory::getApplication();
		$shippingId = $app->input->get('shipping_rate_id');
		$service    = $app->input->get('gls_delivery_service-' . $shippingId);

		switch ($service)
		{
			case 'parcelshop':
				$shippingId   = $app->input->get('shipping_rate_id');
				$parcelShopId = $app->input->get('ParcelShopId');
				$shippingRate = RedshopbShippingHelper::getShippingRateById($shippingId);

				if ($shippingRate->shipping_name === $this->shippingName && empty($parcelShopId))
				{
					$app->enqueueMessage(Text::_('PLG_REDSHIPPING_GLS_NO_SHOP_SELECTED'), 'error');
					$return = false;
				}

				$customer       = RedshopbEntityCustomer::getInstance($customerId, $customerType);
				$updatedService = $service;
				$user           = RedshopbEntityUser::loadActive(true);

				if ($customer->getCompany()->get('b2c', 0) && !$user->isLoaded())
				{
					$updatedService .= '_guest';
				}

				if ($updatedService === 'parcelshop')
				{
					$address = $customer->getAddress()->getExtendedData();
				}
				else
				{
					$address = RedshopbEntityAddress::getInstance($deliveryAddressId)->getExtendedData();
				}

				$name    = $address->name;
				$nameArr = explode(' ', $name);
				$name2   = $address->name2;

				if ($shippingRate->shipping_name === $this->shippingName && empty($name2) && count($nameArr) < 2)
				{
					$app->enqueueMessage(Text::_('PLG_REDSHIPPING_GLS_NO_LAST_NAME'), 'error');
					$return = false;
				}

				break;
			case 'private':
			case 'business':
				break;
		}
	}

	/**
	 * Prints info for a parcel shop
	 *
	 * @param   object   $order Order data
	 * @param   string   $html  HTML output
	 *
	 * @throws Exception If {@see Factory::getApplication()} fails
	 *
	 * @return null
	 *
	 * @since 2.1.0
	 */
	public function onAESECExtendedShippingInfo($order, &$html)
	{
		$shipping = RedshopbShippingHelper::getShippingRateById($order->shipping_rate_id ?? $order->shippingRateId);

		if ($shipping->shipping_name !== $this->shippingName)
		{
			return null;
		}

		$orderId    = Factory::getApplication()->input->getInt('orderId', $order->id ?? 0);
		$layoutData = new stdClass;

		if (0 !== $orderId)
		{
			$layoutData->order = RedshopbEntityOrder::getInstance($orderId);

			$params = new Registry($layoutData->order->get('params'));

			$layoutData->parcelshop    = $this->shippingHelper->getParcelshop($params->get('gls_parcelshop_id'));
			$layoutData->consignmentId = $params->get('gls_consignment_id', null);
		}
		else
		{
			$session = Factory::getSession();

			$layoutData->parcelshop = $this->shippingHelper->getParcelshop(
				$session->get('gls_parcelshop_id', null, 'redshipping_gls')
			);
		}

		RedshopbLayoutFile::addIncludePathStatic(JPATH_PLUGINS . '/redshipping/gls/layouts');
		$html = RedshopbLayoutHelper::render('gls.delivery', $layoutData);
	}

	/**
	 * Adds the GLS status row
	 *
	 * @param   string $html HTML output
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function onAESECViewOrdersExtendTableHead(&$html)
	{
		$html = '<th class="nowrap text-center">' . Text::_('PLG_REDSHIPPING_GLS_TABLE_HEADER') . '</th>';
	}

	/**
	 * Shows the response status of the GLS call when the order was placed
	 *
	 * @param   string $html HTML output
	 * @param   object $item Order to display status for
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function onAESECViewOrdersExtendTableBody(&$html, $item)
	{
		$params = new Registry($item->params);

		$status = $params->get('gls_webservice_failed');

		$body = false === $status
			? '<label class="badge badge-success">' . Text::_('PLG_REDSHIPPING_GLS_TABLE_BADGE_SUCCESS') . '</label>'
			: '<label class="badge badge-important ">' . Text::_('PLG_REDSHIPPING_GLS_TABLE_BADGE_FAILED') . '</label>';

		if (null === $status)
		{
			$body = '<label class="badge">' . Text::_('PLG_REDSHIPPING_GLS_TABLE_BADGE_NON_GLS') . '</label>';
		}

		$html = "<td class='footable-visible text-center'>{$body}</td>";
	}

	/**
	 * Saves GLS selections when using Default checkout
	 *
	 * @param   string $prefix Layout prefix
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function RedshopbOnCheckoutConfirm($prefix) // @codingStandardsIgnoreLine
	{
		if ('&layout=confirm' !== $prefix)
		{
			return;
		}

		$this->setGLSSession();
	}

	/**
	 * Saves GLS selections when using Default checkout
	 *
	 * @param   string $prefix Layout prefix
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function RedshopbOnCheckoutPayment($prefix) // @codingStandardsIgnoreLine
	{
		if ('&layout=payment' !== $prefix)
		{
			return;
		}

		$this->setGLSSession();
	}

	/**
	 * Stores GLS selections in the session
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	private function setGLSSession()
	{
		/** @var Input $input */
		$input   = Factory::getApplication()->input;
		$session = Factory::getSession();

		if ($input->exists('gls_delivery_service'))
		{
			$session->set('gls_delivery_service', $input->get('gls_delivery_service'), 'redshipping_gls');
		}

		if ($input->exists('ParcelShopId'))
		{
			$session->set('gls_parcelshop_id', $input->get('ParcelShopId'), 'redshipping_gls');
		}
	}

	/**
	 * Ajax to store the currently selected extended value
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 2.3.0
	 */
	public function onAjaxGlsPickOption()
	{
		$app        = Factory::getApplication();
		$value      = $app->input->getString('value', '');
		$shippingId = $app->input->getString('shipping_id', '');

		if ($value && $shippingId)
		{
			$app->setUserState('shipping.gls-' . $shippingId, $value);
		}

		$app->close();
	}

	/**
	 * Ajax to get parcel shop list
	 *
	 * @throws Exception If {@see Factory::getApplication()} fails
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function onAjaxGlsGetParcelShops()
	{
		$app = Factory::getApplication();

		echo $this->shippingHelper->searchForParcelShops($app);

		$app->close();
	}
}
