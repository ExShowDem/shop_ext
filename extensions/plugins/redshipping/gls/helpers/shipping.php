<?php
/**
 * @package     Aesir\E-Commerce\Plugin\Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

require_once __DIR__ . '/ParcelShopSearch.php';
require_once __DIR__ . '/YourGLS.php';
require_once __DIR__ . '/InterLine.php';

/**
 * Handles GLS shipping
 *
 * @since 2.1.0
 */
class ShippingHelperGLS extends RedshopbShippingPluginHelperShipping
{
	const SERVICE_NONE = 0;

	const SERVICE_YOURGLS = 1;

	const SERVICE_INTERLINE = 2;

	/**
	 * @var Registry
	 *
	 * @since 2.1.0
	 */
	private $extendedParams;

	/**
	 * ShippingHelperGLS constructor.
	 *
	 * @param   Registry|null $params Plugin parameters
	 *
	 */
	public function __construct(Registry $params = null)
	{
		parent::__construct($params);

		$this->extendedParams = $params;
	}

	/**
	 * Outputs parcel shops based on the customers billing address
	 *
	 * @param   array  $data         Display data from the shipping methods layout
	 * @param   object $shippingRate Shipping rate used
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public function extend($data, $shippingRate)
	{
		$app = Factory::getApplication();
		$this->configure($shippingRate);

		$user         = RedshopbEntityUser::loadActive(true);
		$customer     = $data['options']['customer'];
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		// If customer is b2c, we need to get billing address from user, not company
		$getCustomerData = ($customer->b2c && $user->isLoaded());

		if ($getCustomerData)
		{
			$customerEntity = RedshopbEntityCustomer::getInstance($customerId, $customerType);
			$userAdress     = $customerEntity->getAddress()->getExtendedData();

			$address = $userAdress->address;
			$zip     = $userAdress->zip;
			$country = $userAdress->country;
		}
		else
		{
			$address = $customer->address;
			$zip     = $customer->zip;
			$country = $customer->country;
		}

		$search         = new ParcelShopSearch($this->extendedParams);
		$previousSearch = $app->getUserState('shipping.gls-parcel-search-options', array());
		$services       = $this->extendedParams->get('delivery_services', array('parcelshop'));

		if (!empty($previousSearch))
		{
			$address = $previousSearch['address'] ?: $customer->address;
			$zip     = $previousSearch['zip']     ?: $customer->zip;
			$country = $previousSearch['country'] ?: $customer->country;
		}

		$layoutData = in_array('parcelshop', $services)
			? $search->search($address, $zip, $country)
			: array();

		$layoutData['delivery_services'] = $services;
		$layoutData['shipping_rate_id']  = $shippingRate->id;
		$layoutData['saved_user_state']  = $app->getUserState('shipping.gls-' . $shippingRate->id, '');

		RedshopbLayoutFile::addIncludePathStatic(JPATH_PLUGINS . '/redshipping/gls/layouts');

		return RedshopbLayoutHelper::render("gls.list", $layoutData);

	}

	/**
	 * Parcel shop search
	 *
	 * @param   CMSApplication $app Joomla application instance
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public function searchForParcelShops($app)
	{
		$search = new ParcelShopSearch($this->extendedParams);

		$data = $search->ajaxSearch($app);

		RedshopbLayoutFile::addIncludePathStatic(JPATH_PLUGINS . '/redshipping/gls/layouts');

		return RedshopbLayoutHelper::render("gls.shops", $data);
	}

	/**
	 * Generate GLS parcel labels using ShopDeliveryService
	 *
	 * @param   RedshopbEntityOrder $order        Order we are generating the label for
	 * @param   string              $parcelShopId The parcel shop selected during checkout
	 * @param   string              $service      GLS shipping service
	 *
	 * @throws Exception
	 *
	 * @return mixed|void
	 *
	 * @since 2.1.0
	 */
	public function generateLabel(RedshopbEntityOrder $order, $parcelShopId, $service)
	{
		switch ($this->extendedParams->get('service', 0))
		{
			case self::SERVICE_YOURGLS :
				$yourgls = new YourGLS($order, $this->extendedParams);
				$request = $yourgls->createRequest($parcelShopId, $service);

				$response      = $yourgls->callWebservice($request);
				$consignmentId = array_key_exists('ConsignmentId', $response)
							? $response['ConsignmentId']
							: null;

				$yourgls->updateOrder(
					array(
						'webservice_failed' => true === empty($response),
						'delivery_service' => $service,
						'parcelshop_id' => $parcelShopId,
						'consignment_id' => $consignmentId,
					)
				);

				if ($consignmentId)
				{
					$this->sendMail($response['PDF'], $consignmentId);
				}

				return $response;
			case self::SERVICE_INTERLINE :
				$interline = new InterLine($this->extendedParams);

				$interline->createCSV($order, $parcelShopId);
				break;
		}
	}

	/**
	 * Finds a specific parcel shop based on their ID
	 *
	 * @param   string $id GLS Parcelshop ID
	 *
	 * @return boolean|SimpleXMLElement
	 *
	 * @since 2.1.0
	 */
	public function getParcelshop($id)
	{
		$search = new ParcelShopSearch($this->extendedParams);

		return $search->getParcelshop($id);
	}

	/**
	 * Replaces the plugin parameters with the parameters from the shipping method
	 *
	 * @param   object $shippingRate Shipping rate
	 *
	 * @return void
	 *
	 * @since 2.1.1
	 */
	private function configure($shippingRate)
	{
		$config = RedshopbEntityShipping_Configuration::getInstance(
			$shippingRate->shipping_configuration_id
		);

		$this->extendedParams = new Registry($config->getItem()->params);
	}

	/**
	 * Sends a mail with the PDF from the response and saves it on the server
	 *
	 * @param   string  $pdf           PDF
	 * @param   string  $consignmentId Consignment ID
	 *
	 * @return void
	 *
	 * @since 2.3.0
	 */
	private function sendMail($pdf, $consignmentId)
	{
		$config = Factory::getConfig();

		$sender = array(
			$config->get('mailfrom'),
			$config->get('fromname')
		);

		$recipient = $this->extendedParams->get('email_recipient', '');
		$pathParam = $this->extendedParams->get('pdf_path', '');

		$path       = JPATH_BASE . '/' . $pathParam . '/' . $consignmentId . '.pdf';
		$pdfFile    = fopen($path, 'w');
		$pdfDecoded = base64_decode($pdf);

		// No point continuing if no file pointer resource (should never happen with correct settings)
		if ($pdfFile === false)
		{
			return;
		}

		fwrite($pdfFile, $pdfDecoded);
		fclose($pdfFile);

		if ($recipient && !empty($sender))
		{
			$mailer = RFactory::getMailer();
			$mailer->setSender($sender);
			$mailer->addRecipient(array($recipient));

			$emailHeader = sprintf(
				Text::_('PLG_REDSHIPPING_GLS_MAIL_HEADER'),
				$consignmentId
			);

			$emailBody = sprintf(
				Text::_('PLG_REDSHIPPING_GLS_MAIL_BODY'),
				$consignmentId
			);

			$mailer->addAttachment(realpath($path));
			$mailer->setSubject($emailHeader);
			$mailer->setBody($emailBody);

			$mailer->Send();
		}
	}
}
