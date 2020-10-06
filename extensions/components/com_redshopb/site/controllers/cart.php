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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

PluginHelper::importPlugin('redshipping');

/**
 * Cart Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCart extends RedshopbControllerAdmin
{
	/**
	 * Method for save a cart
	 *
	 * @return  void
	 */
	public function saveCart()
	{
		Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

		$cartName = $this->input->getString('name', '');
		$orderId  = $this->input->getInt('orderId', 0);
		$customer = $this->input->getBase64('customer', '');

		list($customerId, $customerType) = explode('_', base64_decode($customer));

		$cartId = $this->input->get('savedCartId', 0);

		if ($cartId == 'NEW')
		{
			$cartId = 0;
		}

		/** @var RedshopbModelCart $model */
		$model = $this->getModel('Cart');

		$msg     = 'COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_FAIL';
		$msgType = 'error';

		if ($model->saveCart($cartName, $orderId, $cartId, $customerId, $customerType))
		{
			$msg     = 'COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_SUCCESS';
			$msgType = 'success';
		}

		Factory::getApplication()->enqueueMessage(Text::_($msg), $msgType);

		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * Method for remove an item from cart.
	 *
	 * @return  boolean
	 */
	public function removeCartItem()
	{
		Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

		$cartId   = $this->input->getInt('id', 0);
		$cartItem = $this->input->getInt('cartItem', 0);

		$cartItemTable = RedshopbTable::getAutoInstance('Cart_Item');
		$redirect      = RedshopbRoute::_('index.php?option=com_redshopb&view=cart&id=' . $cartId, false);

		if (!$cartItemTable->load(array('id' => $cartItem, 'cart_id' => $cartId)))
		{
			$this->setRedirect($redirect, Text::_('COM_REDSHOPB_SAVED_CART_ITEM_NOT_FOUND'), 'warning');

			return false;
		}

		if (!$cartItemTable->delete())
		{
			$this->setRedirect($redirect, $cartItemTable->getError(), 'warning');

			return false;
		}

		$this->setRedirect($redirect, Text::_('COM_REDSHOPB_SAVED_CART_ITEM_DELETE_SUCCESSFULLY'));

		return true;
	}

	/**
	 * Method remove saved cart
	 *
	 * @return  void
	 */
	public function ajaxRemoveCart()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$return = array('status' => 0, 'msg' => Text::_('COM_REDSHOPB_SAVED_CART_ERROR_REMOVE_CART'));

		$cartId = $this->input->getInt('cartId', 0);
		$model  = $this->getModel('Cart');

		if ($model->removeCart($cartId))
		{
			$return['status'] = 1;
			$return['msg']    = Text::_('COM_REDSHOPB_SAVED_CART_SUCCESS_REMOVE_CART');
		}

		echo json_encode($return);

		Factory::getApplication()->close();
	}

	/**
	 * Method for checkout from saved cart
	 *
	 * @return  void
	 */
	public function ajaxCheckoutCart()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		echo $this->checkoutCart();

		Factory::getApplication()->close();
	}

	/**
	 * Method to load a saved cart and redirect to checkout
	 *
	 * @return  integer
	 */
	public function checkoutCart()
	{
		$cartId     = $this->input->getInt('cartId', 0);
		$cartEntity = RedshopbEntityCart::getInstance($cartId);

		if ($cartEntity->removeNotAvailableProducts())
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_SAVED_CART_WARNING_SOME_PRODUCTS_ARE_NOT_AVAILABLE'), 'warning');
		}

		$model = $this->getModel('Cart');
		$model->loadCart($cartId);

		$this->setRedirect(Route::_('index.php?option=com_redshopb&view=shop&layout=cart'));

		return $cartId;
	}

	/**
	 * Method for remove an item from cart.
	 *
	 * @return  void
	 */
	public function ajaxRemoveCartItem()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$cartId   = $this->input->getInt('id', 0);
		$cartItem = $this->input->getInt('cartItem', 0);

		$cartItemTable = RedshopbTable::getAutoInstance('Cart_Item');

		if (!$cartItemTable->load(array('id' => $cartItem, 'cart_id' => $cartId)))
		{
			echo 0;
			Factory::getApplication()->close();
		}

		echo $cartItemTable->delete();
		Factory::getApplication()->close();
	}

	/**
	 * Ajax method to get the rendered cart module
	 *
	 * @return void
	 */
	public function ajaxGetShoppingCart()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app          = Factory::getApplication();
		$ajaxResponse = new RedshopbAjaxResponse;

		$this->renderSessionCartModule($ajaxResponse);

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Method to render the cart module content and set it to the response body
	 *
	 * @param   RedshopbAjaxResponse  $ajaxResponse  the response object to set the rendered layout to
	 *
	 * @return boolean
	 */
	protected function renderSessionCartModule(RedshopbAjaxResponse $ajaxResponse)
	{
		$app          = Factory::getApplication();
		$config       = RedshopbEntityConfig::getInstance();
		$showTaxes    = $config->getBool('show_taxes_in_cart_module', true);
		$customerId   = $app->getUserState('shop.customer_id',  0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$showDiscCol  = $app->input->getInt('showDiscColumn', 1);
		$isCheckout   = $app->input->getBool('isCheckout', false);

		try
		{
			$cart       = RedshopbHelperCart::getCart($customerId, $customerType);
			$totals     = RedshopbHelperCart::getCustomerCartTotals($customerId, $customerType, $showTaxes);
			$taxes      = RedshopbHelperCart::getCustomerCartTaxes($customerId, $customerType);
			$taxSummary = RedshopbHelperCart::getCustomerCartTaxByName($customerId, $customerType);
			$fees       = RedshopbHelperShop::getChargeProducts('fee');

			$cart = $cart->toArray();

			$app->triggerEvent('onBeforeRenderVanirCartModule', array($customerId, $customerType, &$cart, &$totals, &$taxes, &$taxSummary, &$fees));

			$cart = new RedshopbHelperCart_Object($cart);

			$html = RedshopbLayoutHelper::render(
				'cart.module',
				array(
					'customerId'     => $customerId,
					'customerType'   => $customerType,
					'items'          => $cart->get('items', array()),
					'offers'         => $cart->get('offers', array()),
					'totals'         => $totals,
					'taxes'          => $taxSummary,
					'fees'           => $fees,
					'config'         => $config,
					'updateQuantity' => true,
					'isCheckout'     => $isCheckout,
					'showDiscColumn' => $showDiscCol
				)
			);

			$ajaxResponse->setBody($html);

			$ajaxResponse->quantity = RedshopbHelperCart::getCartItemQuantities();

			$formattedTotals = array();

			foreach ($totals as $currency => $total)
			{
				$formattedTotals[$currency] = RedshopbHelperProduct::getProductFormattedPrice($total, $currency);
			}

			$formattedTaxes = array();

			foreach ($taxSummary as $currency => $tax)
			{
				foreach ($tax as $taxData)
				{
					$formattedTaxes[$currency] = RedshopbHelperProduct::getProductFormattedPrice($taxData['tax'], $currency);
				}
			}

			$customTextPlugin = PluginHelper::getPlugin('vanir', 'product_custom_text');
			$items            = RedshopbHelperCart::getCart($customerId, $customerType)->get('items', array());

			foreach ($items as &$item)
			{
				if (isset($item['customText']) && $customTextPlugin)
				{
					$params                  = new Registry($customTextPlugin->params);
					$customTextLabel         = (string) $params->get('textLabel', Text::_('PLG_VANIR_PRODUCT_CUSTOM_TEXT_COLUMN_NAME'));
					$item['customTextLabel'] = $customTextLabel;
				}
			}

			$ajaxResponse->setData(
				array(
					'totals'           => $totals,
					'formatted_totals' => $formattedTotals,
					'formatted_taxes'  => $formattedTaxes,
					'taxes'            => $taxes,
					'taxSummary'       => $taxSummary,
					'items'            => $items,
				)
			);
		}
		catch (Exception $e)
		{
			header('HTTP/1.1 500 Internal Server Error');

			$ajaxResponse->setMessage($e->getMessage())
				->setMessageType('alert-error');

			return false;
		}

		return true;
	}

	/**
	 * Method to render the modal layout and set it to the response body
	 *
	 * @param   RedshopbAjaxResponse  $ajaxResponse  the response object to set the rendered layout to
	 * @param   object                $data          display data
	 *
	 * @return boolean
	 */
	protected function renderModal(RedshopbAjaxResponse $ajaxResponse, $data)
	{
		$data['message'] = $ajaxResponse->message;
		$html            = RedshopbLayoutHelper::render(
			'cart.modal',
			$data
		);

		$ajaxResponse->setModal($html);

		return true;
	}

	/**
	 * Method to add all products in view to shopping cart
	 *
	 * @return void
	 */
	public function ajaxAddProductsToShoppingCart()
	{
		$app            = Factory::getApplication();
		$input          = $app->input;
		$ajaxResponse   = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_ITEMS_ADDED_TO_CART'));
		$products       = $input->get('products', array(), 'array');
		$collection     = $input->getInt('collectionId', 0);
		$customerId     = $app->getUserState('shop.customer_id', 0);
		$customerType   = $app->getUserState('shop.customer_type', '');
		$hasError       = false;
		$resultMessages = array();
		$pModel         = RedshopbModel::getInstance('Product', 'RedshopbModel');
		$company        = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		$currency       = $company->currency_id;

		if (empty($products))
		{
			header('HTTP/1.1 400 Bad Request');
			$ajaxResponse->setMessage('COM_REDSHOPB_CART_ERROR_INVALID_PRODUCT_ID', true)
				->setMessageType('alert-error');

			echo json_encode($ajaxResponse);

			$app->close();
		}
		else
		{
			foreach ($products as $product)
			{
				if (is_object($product))
				{
					$productId = (int) $product->pid;
					$quantity  = (float) $product->quantity;
				}
				else
				{
					$productId = (int) $product['pid'];
					$quantity  = (float) $product['quantity'];
				}

				$accIds      = $pModel->getAccessoriesIds(
					$productId, null, true, 0, array($collection), 0, $customerId, $customerType, $currency, $company->id
				);
				$price       = RedshopbHelperProduct::getProductPrice($productId, $customerId, $customerType, $currency);
				$accessories = array();

				foreach ($accIds as $accId)
				{
					$accessories[] = array('id' => $accId, 'quantity' => $quantity);
				}

				$result = RedshopbHelperCart::addToCartById(
					$productId,
					0,
					$accessories,
					$quantity,
					$price,
					$currency,
					$customerId,
					$customerType,
					$collection,
					0
				);

				$resultMessages[] = '<p>' . $result['message'] . '</p>';

				if (!$hasError && $result['messageType'] != 'alert-success' && $result['messageType'] != 'alert-info')
				{
					$hasError = true;
				}
			}
		}

		$ajaxResponse->setMessage(implode("\n", $resultMessages));

		if ($hasError)
		{
			$ajaxResponse->setMessageType('alert-error');
		}

		$this->addComplimentaryProductsToShoppingCart($customerId, $customerType, $currency);
		$this->renderSessionCartModule($ajaxResponse);
		$this->adjustShopVendor($customerId, $customerType, $ajaxResponse->messageType);

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Method to add items to shopping cart
	 *
	 * @return void
	 */
	public function ajaxAddItemToShoppingCart()
	{
		// @TODO this is temporary disabled until we can refactor the products to use separate forms per product.
		// RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$input        = $app->input;
		$ajaxResponse = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_ITEMS_ADDED_TO_CART'));
		$productId    = $input->getInt('productId', $input->getInt('product_id', 0));

		if (empty($productId))
		{
			header('HTTP/1.1 400 Bad Request');
			$ajaxResponse->setMessage('COM_REDSHOPB_CART_ERROR_INVALID_PRODUCT_ID', true)
				->setMessageType('alert-error');

			echo json_encode($ajaxResponse);

			$app->close();
		}

		$productItems = $input->get('items', array(), 'array');
		$accessories  = $this->getItemAccessories();
		$customerId   = (int) $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$collection   = $input->getInt('collectionId', 0);
		$currency     = $input->getInt('currency', 0);

		// Product without variants
		if (empty($productItems))
		{
			$price       = $input->getFloat('price', 0);
			$quantity    = $input->getFloat('quantity', 1.00);
			$stockroomId = $input->getInt('stockroom', 0);

			// Format quantity decimal number follow decimal config.
			$quantity = RedshopbHelperProduct::decimalFormat($quantity, $productId);

			$result = RedshopbHelperCart::addToCartById(
				$productId,
				0,
				$accessories,
				$quantity,
				$price,
				$currency,
				$customerId,
				$customerType,
				$collection,
				0,
				$stockroomId
			);

			$ajaxResponse->setMessage($result['message'])->setMessageType($result['messageType']);

			if (RedshopbEntityConfig::getInstance()->get('add_to_cart_notification_type', 'modal') == 'modal')
			{
				$product = RedshopbEntityProduct::getInstance($productId);
				$ajaxResponse->setMessageType('modal');

				$productData["id"]    = $product->get('id');
				$productData['sku']   = $product->get('sku');
				$productData['name']  = $product->get('name');
				$productData['image'] = RedshopbHelperProduct::getProductImage($productData["id"]);

				if (isset($productData['image']))
				{
					if (!$productData['image']->remote_path) // If remote image
					{
						$productData['image'] = RedshopbHelperThumbnail::originalToResize($productData['image']->name, 200, 200);
					}
					else // If normal image
					{
						$productData['image'] = RedshopbHelperMedia::getMediaRemotePath(
							$productData['image']->name,
							$productData['image']->remote_path
						);
					}
				}
				else // If default image
				{
					/** @var   string   $defaultFileName */
					$defaultFileName      = RedshopbEntityConfig::getInstance()->get('thumbnail_default_no_image', '', 'string');
					$defaultImgPaths      = RedshopbHelperMedia::drawDefaultImgSub($defaultFileName);
					$productData['image'] = $defaultImgPaths['url'];
				}

				if ($product->get('attributes') !== null)
				{
					$productData["attributes"] = $product->get('attributes');
				}

				$this->renderModal($ajaxResponse, $productData);
			}

			$this->addComplimentaryProductsToShoppingCart($customerId, $customerType, $currency);
			$this->renderSessionCartModule($ajaxResponse);
			$this->adjustShopVendor($customerId, $customerType, $ajaxResponse->messageType);

			echo json_encode($ajaxResponse);

			$app->close();
		}

		$initItems = array();

		// First load all items info from DB - need for improve work
		foreach ($productItems as $itemId => $data)
		{
			if (!$itemId || empty($data['quantity']))
			{
				continue;
			}

			$initItems[] = array(
				'productId'   => $productId,
				'productItem' => $itemId
			);
		}

		RedshopbHelperCart::loadItems($initItems);
		$resultMessages = array();
		$hasError       = false;

		foreach ($productItems as $itemId => $data)
		{
			if (!$itemId || empty($data['quantity']))
			{
				continue;
			}

			// Format quantity follow decimal position format.
			$data['quantity'] = RedshopbHelperProduct::decimalFormat($data['quantity'], $productId);

			$dropDownSelected = isset($data['dropDownSelected']) ? $data['dropDownSelected'] : 0;
			$stockroomId      = isset($data['stockroom']) ? $data['stockroom'] : 0;

			$result = RedshopbHelperCart::addToCartById(
				$productId,
				(int) $itemId,
				$accessories,
				$data['quantity'],
				isset($data['price']) ? (float) $data['price'] : 0.0,
				(int) $data['currency'],
				$customerId,
				$customerType,
				$collection,
				$dropDownSelected,
				$stockroomId
			);

			$resultMessages[] = '<p>' . $result['message'] . '</p>';

			if (!$hasError && $result['messageType'] != 'alert-success')
			{
				$hasError = true;
			}
		}

		$ajaxResponse->setMessage(implode("\n", $resultMessages));

		if ($hasError)
		{
			$ajaxResponse->setMessageType('alert-error');
		}

		if (RedshopbEntityConfig::getInstance()->get('add_to_cart_notification_type', true))
		{
			if (empty($initItems) || empty($data['quantity']))
			{
				$product = RedshopbEntityProduct::getInstance($productId);
				$ajaxResponse->setMessageType('modal');
				$productData["id"]    = $product->get('id');
				$productData['sku']   = $product->get('sku');
				$productData['name']  = $product->get('name');
				$productData['image'] = RedshopbHelperProduct::getProductImage($productData["id"]);

				if (isset($productData['image']))
				{
					if (!$productData['image']->remote_path) // If remote image
					{
						$productData['image'] = RedshopbHelperThumbnail::originalToResize($productData['image']->name, 200, 200);
					}
					else // If normal image
					{
						$productData['image'] = RedshopbHelperMedia::getMediaRemotePath(
							$productData['image']->name,
							$productData['image']->remote_path
						);
					}
				}
				else // If default image
				{
					/** @var   string   $defaultFileName */
					$defaultFileName 	  = RedshopbEntityConfig::getInstance()->get('thumbnail_default_no_image', '', 'string');
					$defaultImgPaths 	  = RedshopbHelperMedia::drawDefaultImgSub($defaultFileName);
					$productData['image'] = $defaultImgPaths['url'];
				}

				if ($product->get('attributes') !== null)
				{
					$productData["attributes"] = $product->get('attributes');
				}
			}
			else
			{
				$productItem = RedshopbHelperProduct_Item::getAttributesValues($initItems[0]["productItem"]);
				$pks         = array_keys($productItem);

				$ajaxResponse->setMessageType('modal');

				$productData["id"]   = $initItems[0]["productItem"];
				$productData['sku']  = $productItem[$pks[0]]->sku;
				$productData['name'] = $productItem[$pks[0]]->value;

				$productData['image'] = RedshopbHelperProduct::getProductImage($productId);

				if (isset($productData['image']))
				{
					if (!$productData['image']->remote_path) // If remote image
					{
						$productData['image'] = RedshopbHelperThumbnail::originalToResize($productData['image']->name, 200, 200);
					}
					else // If normal image
					{
						$productData['image'] = RedshopbHelperMedia::getMediaRemotePath(
							$productData['image']->name,
							$productData['image']->remote_path
						);
					}
				}
				else // If default image
				{
					/** @var   string   $defaultFileName */
					$defaultFileName 	  = RedshopbEntityConfig::getInstance()->get('thumbnail_default_no_image', '', 'string');
					$defaultImgPaths 	  = RedshopbHelperMedia::drawDefaultImgSub($defaultFileName);
					$productData['image'] = $defaultImgPaths['url'];
				}

				$productData['complete_item'] = $productItem;
			}

			$this->renderModal($ajaxResponse, $productData);
		}

		$this->addComplimentaryProductsToShoppingCart($customerId, $customerType, $currency);
		$this->renderSessionCartModule($ajaxResponse);
		$this->adjustShopVendor($customerId, $customerType, $ajaxResponse->messageType);

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Method to makes sure that the shop vendor is correctly set
	 *
	 * @param   int     $customerId    customer ID
	 * @param   string  $customerType  customer type (company, department, employee)
	 * @param   string  $messageType   return message (alert-success, alert-error)
	 *
	 * @return void
	 */
	protected function adjustShopVendor($customerId, $customerType, $messageType)
	{
		$vendor = RedshopbHelperShop::getVendor();

		if (!is_null($vendor)
			|| $messageType == 'alert-error')
		{
			return;
		}

		$app = Factory::getApplication();

		switch (RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent'))
		{
			case 'main':
				$orderVendor        = RedshopbHelperCompany::getMain();
				$orderVendor->pType = 'company';
				$app->setUserState('shop.vendor', $orderVendor);
				break;
			case 'parent':
			default:
				$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);

				if (!$customerCompany)
				{
					break;
				}

				$orderVendor = RedshopbEntityCompany::getInstance($customerCompany->id)->getParent()->getItem();

				if (!$orderVendor)
				{
					$orderVendor     = new stdClass;
					$orderVendor->id = $customerCompany->id;
				}

				$orderVendor->pType = 'company';
				$app->setUserState('shop.vendor', $orderVendor);

				break;
		}
	}

	/**
	 * Method to get an array of accessory id/quantity objects
	 *
	 * @return array
	 */
	protected function getItemAccessories()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$accessories         = $input->get('accessorySelected', array(), 'array');
		$accessoryQuantities = $input->get('accessoryQuantity', array(), 'array');

		foreach ($accessories AS $key => $accessoryId)
		{
			$quantity = 1;

			if (!empty($accessoryQuantities[$key]))
			{
				$quantity = $accessoryQuantities[$key];
			}

			$accessories[$key] = (object) array('id' => $accessoryId, 'quantity' => $quantity);
		}

		return $accessories;
	}

	/**
	 * Method to add complementary products to the shopping cart
	 *
	 * @param   int     $customerId    customer ID
	 * @param   string  $customerType  customer type (company, department, employee)
	 * @param   int     $currency      currency ID
	 *
	 * @return void
	 */
	protected function addComplimentaryProductsToShoppingCart($customerId, $customerType, $currency)
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$complimentaryItems      = $input->get('complimentarySelected', array(), 'array');
		$complimentaryQuantities = $input->get('complimentaryQuantity', array(), 'array');
		$defaultQuantity         = $input->getFloat('quantity', 1.00);

		foreach ($complimentaryItems as $key => $complimentaryId)
		{
			if (!array_key_exists($key, $complimentaryQuantities))
			{
				$complimentaryQuantity = RedshopbHelperProduct::decimalFormat($defaultQuantity, $complimentaryId);
			}
			else
			{
				$complimentaryQuantity = RedshopbHelperProduct::decimalFormat($complimentaryQuantities[$key], $complimentaryId);
			}

			RedshopbHelperCart::addToCartById(
				$complimentaryId,
				0,
				null,
				$complimentaryQuantity,
				0,
				$currency,
				$customerId,
				$customerType,
				0,
				0,
				0
			);
		}
	}

	/**
	 * Method to update the cart quantity
	 *
	 * @return void
	 */
	public function ajaxUpdateShoppingCartQuantity()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		$hash         = $input->getString('cartItemHash', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$quantity     = $input->getFloat('quantity', 0.0);

		$response = RedshopbHelperCart::setItemQuantityByHash(
			$hash,
			$customerId,
			$customerType,
			$quantity
		);

		$ajaxResponse = new RedshopbAjaxResponse;

		if (!empty($response['message']))
		{
			$ajaxResponse->setMessage($response['message']);
		}

		if (!empty($response['messageType']))
		{
			$ajaxResponse->setMessageType($response['messageType']);
		}

		$app->triggerEvent(
			'onAfterAjaxUpdateShoppingCartQuantity',
			array(&$response, &$ajaxResponse, $hash, $customerId, $customerType, $quantity)
		);

		$this->renderSessionCartModule($ajaxResponse);

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Method to remove an item from shopping cart
	 *
	 * @return void
	 */
	public function ajaxRemoveItemFromShoppingCart()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app          = Factory::getApplication();
		$ajaxResponse = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_CART_REMOVED_ITEM_FROM_CART'));
		$input        = $app->input;
		$hash         = $input->getString('cartItemHash', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');

		try
		{
			$response = RedshopbHelperCart::removeFromCartByHash(
				$hash,
				$customerId,
				$customerType
			);

			$app->triggerEvent(
				'onAfterAjaxRemoveShoppingCartItem',
				array(&$response)
			);
		}
		catch (Exception $e)
		{
			header('HTTP/1.1 500 Internal Server Error');

			$ajaxResponse->setMessage($e->getMessage())
				->setMessageType('alert-error');
		}

		$this->renderSessionCartModule($ajaxResponse);

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Method to remove an offer from shopping cart
	 *
	 * @return void
	 */
	public function ajaxRemoveOfferFromShoppingCart()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$ajaxResponse = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_OFFER_REMOVED_OFFER_FROM_CART'));

		$input     = $app->input;
		$offerCode = $input->getString('offerCode', '');

		list($prefix, $customerType, $customerId, $offerId) = explode('_', $offerCode);

		try
		{
			RedshopbHelperCart::removeOfferFromCart($offerId, $customerId, $customerType);
		}
		catch (Exception $e)
		{
			header('HTTP/1.1 500 Internal Server Error');

			$ajaxResponse->setMessage($e->getMessage())
				->setMessageType('alert-error');
		}

		$this->renderSessionCartModule($ajaxResponse);

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Method to clear the shopping cart for the current user
	 *
	 * @return void
	 */
	public function ajaxClearShoppingCart()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$ajaxResponse = new RedshopbAjaxResponse;

		RedshopbHelperCart::clearCartFromSession(false);

		$this->renderSessionCartModule($ajaxResponse);

		$customerId   = $app->getUserState('shop.customer_id',  0);
		$customerType = $app->getUserState('shop.customer_type', '');

		$currency = RedshopbHelperPrices::getCurrency($customerId, $customerType);

		$ajaxResponse->totals           = array($currency => 0.00);
		$ajaxResponse->formatted_totals = array($currency => RedshopbHelperProduct::getProductFormattedPrice(0, $currency));

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * checkMinDeliveryDate
	 *
	 * @param   array                 $shippingDate  Shipping dates array
	 * @param   RedshopbAjaxResponse  $ajaxResponse  Ajax response
	 * @param   boolean               $isDelayOrder  Is delay order
	 *
	 * @return  array
	 *
	 * @since 1.12.82
	 */
	protected function checkMinDeliveryDate($shippingDate, $ajaxResponse, $isDelayOrder = false)
	{
		$shippingDateResult = array();
		$config             = RedshopbApp::getConfig();

		foreach ($shippingDate as $key => $item)
		{
			$answerKey                      = 'shipping_date' . ($isDelayOrder ? '_delay' : '') . '[' . $key . ']';
			$shippingDateResult[$answerKey] = true;

			if (empty($item))
			{
				continue;
			}

			list($customerType, $customerId) = explode('_', $key);
			$customerDeliveryDay             = new DateTime($item);

			if (!RedshopbHelperOrder::isShippingDateAvailable($item, $customerType, $customerId))
			{
				$shippingDateResult[$answerKey] = false;
				$ajaxResponse->setMessage(
					Text::sprintf(
						'COM_REDSHOPB_SHOP_SHIPPING_DATE_ALLOW_FROM',
						$customerDeliveryDay->format(Text::_('DATE_FORMAT_LC4'))
					),
					true
				);
				$ajaxResponse->setMessageType('alert-warning');

				continue;
			}

			if (!$config->getAllowSplittingOrder())
			{
				continue;
			}

			$minDate = RedshopbHelperStockroom::getMinimumDeliveryPeriodForOrder($customerId, $customerType, !$isDelayOrder);

			if ($minDate == -1)
			{
				$shippingDateResult[$answerKey] = false;
			}
			elseif ($minDate > 0)
			{
				$minDeliveryDay = new DateTime('today');
				$minDeliveryDay->modify('+' . (int) $minDate . ' day');

				if ($minDeliveryDay->format('Ymd') > $customerDeliveryDay->format('Ymd'))
				{
					$shippingDateResult[$answerKey] = false;
				}
			}

			if ($shippingDateResult[$answerKey] == false)
			{
				$ajaxResponse->setMessage('COM_REDSHOPB_SHOP_CAN_NOT_BE_DELIVERED_TO_SELECTED_DATE_DELAY', true);
				$ajaxResponse->setMessageType('alert-warning');
			}
		}

		return $shippingDateResult;
	}

	/**
	 * ajaxCheckDeliveryDate
	 *
	 * @return void
	 *
	 * @since 1.12.82
	 * @throws Exception
	 */
	public function ajaxCheckDeliveryDate()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$ajaxResponse = new RedshopbAjaxResponse;
		$config       = RedshopbApp::getConfig();

		if ($config->getInt('use_shipping_date', 0))
		{
			$shippingDate                     = $app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array');
			$ajaxResponse->shippingDateResult = array();

			if (!empty($shippingDate))
			{
				$ajaxResponse->shippingDateResult = $this->checkMinDeliveryDate($shippingDate, $ajaxResponse);
			}

			if ($config->getAllowSplittingOrder())
			{
				$shippingDateDelay = $app->getUserStateFromRequest('checkout.shipping_date_delay', 'shipping_date_delay', array(), 'array');

				if (!empty($shippingDateDelay))
				{
					$ajaxResponse->shippingDateResult = array_replace(
						$ajaxResponse->shippingDateResult, $this->checkMinDeliveryDate($shippingDateDelay, $ajaxResponse, true)
					);
				}
			}
		}

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * ajaxDelayItem
	 *
	 * @return void
	 *
	 * @since 1.12.82
	 * @throws Exception
	 */
	public function ajaxDelayItem()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		if (!RedshopbApp::getConfig()->getAllowSplittingOrder())
		{
			$app->close();
		}

		$ajaxResponse = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_CART_ITEM_MOVED_TO_DELAY_ORDER'));

		$hash         = $input->getString('cartItemHash', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$params       = array(
			'delayed_order' => 1
		);

		if (!RedshopbHelperCart::changeAdditionalParameters($customerId, $customerType, $hash, $params))
		{
			$ajaxResponse->setMessage(Text::_('COM_REDSHOPB_CART_ITEM_MOVED_TO_DELAY_NOT_FOUND'));
			$ajaxResponse->setMessageType('alert-error');
		}

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * ajaxItemBackToMainOrder
	 *
	 * @return void
	 *
	 * @since 1.12.82
	 * @throws Exception
	 */
	public function ajaxItemBackToMainOrder()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		if (!RedshopbApp::getConfig()->getAllowSplittingOrder())
		{
			$app->close();
		}

		$ajaxResponse = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_CART_ITEM_MOVED_TO_MAIN_ORDER'));
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$hash         = $input->getString('cartItemHash', '');
		$params       = array(
			'delayed_order' => 0
		);

		if (!RedshopbHelperCart::changeAdditionalParameters($customerId, $customerType, $hash, $params))
		{
			$ajaxResponse->setMessage(Text::_('COM_REDSHOPB_CART_ITEM_MOVED_TO_DELAY_NOT_FOUND'));
			$ajaxResponse->setMessageType('alert-error');
		}

		echo json_encode($ajaxResponse);

		$app->close();
	}
}
