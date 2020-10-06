<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
/**
 * Send To Friend Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelSend_To_Friend extends RedshopbModelAdmin
{
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			$this->context . '.data',
			array()
		);

		return $data;
	}

	/**
	 * Send mail for friend
	 *
	 * @param   array  $data  User data
	 *
	 * @return  boolean
	 */
	public function sendMailForFriend($data)
	{
		$product = RedshopbHelperProduct::loadProduct($data['product_id']);

		if ($product)
		{
			$productData  = RedshopbHelperProduct::getProductsData(array($product->id => $product), array($product->id));
			$product      = $productData[$product->id];
			$siteName     = Factory::getConfig()->get('sitename');
			$customerId   = RedshopbHelperCompany::getCompanyB2C();
			$customerType = 'company';

			if (empty($customerId))
			{
				$app          = Factory::getApplication();
				$customerId   = $app->getUserStateFromRequest('shop.customer_id', 0);
				$customerType = $app->getUserStateFromRequest('shop.customer_type', '');
			}

			if ($customerId)
			{
				$currency = RedshopbHelperPrices::getCurrency($customerId, $customerType);

				if ($data['collection_id'])
				{
					$product->prices = RedshopbHelperPrices::getProductPrice(
						$product->id, $customerId, $customerType, $currency, array($data['collection_id'])
					);
				}
				else
				{
					$product->prices = RedshopbHelperPrices::getProductPrice(
						$product->id, $customerId, $customerType, $currency
					);
				}

				$product->discount = RedshopbHelperPrices::getDiscount(
					$product->id,
					RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType),
					$currency
				);
			}

			if (isset($data['category_id']) && in_array($data['category_id'], $product->categories))
			{
				$categoryId = $data['category_id'];
			}
			else
			{
				$categoryId = $product->categories[0];
			}

			$uri    = Uri::getInstance(Uri::base());
			$prefix = $uri->toString(array('scheme', 'host', 'port'));
			$link   = $prefix . RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=product&id='
				. $product->id . '&category_id=' . $categoryId . '&collection_id=' . $data['collection_id']
			);

			$body = RedshopbHelperTemplate::renderTemplate(
				'send-to-friend',
				'email',
				null,
				array (
					'product'  => $product,
					'data'     => $data,
					'siteName' => $siteName,
					'link'     => $link
				)
			);

			$subject = Text::sprintf('COM_REDSHOPB_SEND_TO_FRIEND_SUBJECT', $siteName, $product->name);

			if (RFactory::getMailer()->sendMail($data['your_email'], $data['your_name'], $data['friends_email'], $subject, $body, true))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}
}
