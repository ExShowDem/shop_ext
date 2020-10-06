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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Quick Order Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerQuick_Order extends RedshopbControllerForm
{
	/**
	 * Method for search products by AJAX method
	 *
	 * @return  void
	 */
	public function ajaxSearchProducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app     = Factory::getApplication();
		$term    = $app->input->getString('term', '');
		$results = RedshopbHelperQuick_Order::searchItems($term);

		if (!empty($results))
		{
			echo json_encode($results, true);
		}
		else
		{
			echo json_encode(array());
		}

		$app->close();
	}

	/**
	 * Method for return an formatted price
	 *
	 * @return  void
	 */
	public function ajaxGetFormattedPrice()
	{
		$app = Factory::getApplication();

		$price    = $app->input->getFloat('price', 0.0);
		$currency = $app->input->getInt('currency', 38);

		echo RedshopbHelperProduct::getProductFormattedPrice($price, $currency);

		$app->close();
	}

	/**
	 * Method for return an formatted quantity with unit measure
	 *
	 * @return  void
	 */
	public function ajaxGetQuantityWithUnitMeasure()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$quantity  = $this->input->getFloat('quantity', 0);
		$productId = $this->input->getInt('productId', 0);

		$unitMeasure = RedshopbEntityProduct::getInstance($productId)->getUnitMeasure();

		echo RedshopbHelperProduct::decimalFormat($quantity, $productId) . $unitMeasure->get('name');

		Factory::getApplication()->close();
	}

	/**
	 * Method for add these items in Quick Order to cart and checkout
	 *
	 * @return  void
	 */
	public function order()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Add comment to checkout
		$comment = $this->input->getString('comment', '');

		if (!empty($comment))
		{
			Factory::getApplication()->setUserState('checkout.comment', $comment);
		}

		$redirectOption = RedshopbEntityConfig::getInstance()->get('quick_order_checkout_redirect', 'cart');

		$redirect = RedshopbRoute::_('index.php?option=' . $this->option . '&view=shop&layout=' . $redirectOption, false);
		$this->setRedirect($redirect);
		$this->redirect();
	}

	/**
	 * Method to render the attributes for a product
	 *
	 * @return  void
	 */
	public function ajaxGetAttributeInputs()
	{
		$app            = Factory::getApplication();
		$productId      = $app->input->getInt('productId', 0);
		$responseObject = new RedshopbAjaxResponse;

		if (empty($productId))
		{
			header('HTTP/1.1 400 Bad Request');
			$responseObject->setMessage('COM_REDSHOPB_SHOP_DELIVERY_NOT_SET', true);
			$responseObject->setMessageType('alert-error');

			echo json_encode($responseObject);
			$app->close();
		}

		$productEntity = RedshopbEntityProduct::getInstance($productId);
		$attributes    = $productEntity->getAttributes();

		$responseObject->setBody(RedshopbLayoutHelper::render('quickorder.product_attributes', array('product' => $productEntity, 'attributes' => $attributes)));

		echo json_encode($responseObject);
		$app->close();
	}

	/**
	 * Ajax method to get the product item based on selected attribute values
	 *
	 * @return  void
	 */
	public function ajaxGetProductItem()
	{
		$app             = Factory::getApplication();
		$customerId      = $app->getUserState('shop.customer_id', 0);
		$customerType    = $app->getUserState('shop.customer_type', '');
		$attributeValues = $app->input->get('attributeValues', array());
		$currencyId      = $app->input->get('currencyId', 0);
		$collectionId    = $app->input->get('collectionId', null);
		$result          = new stdClass;

		$productItem           = RedshopbEntityProduct_Item::getInstanceByAttributeValues($attributeValues);
		$productItemId         = $productItem->get('id');
		$result->productItemId = $productItemId;

		if ($productItemId)
		{
			$price = RedshopbHelperPrices::getProductItemPrice($productItemId, $customerId, $customerType, $currencyId, array($collectionId));
		}
		else
		{
			$price = null;
		}

		if ($price)
		{
			$result->price          = $price->price;
			$result->priceFormatted = RedshopbHelperProduct::getProductFormattedPrice($price->price, $currencyId);
		}
		else
		{
			$result->price          = 0.0;
			$result->priceFormatted = RedshopbHelperProduct::getProductFormattedPrice(0.0, $currencyId);
		}

		echo json_encode($result);
		$app->close();
	}

	/**
	 * Ajax render custom text input field
	 *
	 * @return  void
	 */
	public function ajaxRenderCustomText()
	{
		$app          = Factory::getApplication();
		$productId    = $app->input->get('productId', 0);
		$ajaxResponse = new RedshopbAjaxResponse;
		$data         = array('productId' => $productId);
		$html         = RedshopbLayoutHelper::render('tags.product.custom_text', $data);

		$ajaxResponse->setBody($html);

		echo json_encode($ajaxResponse);

		$app->close();
	}
}
