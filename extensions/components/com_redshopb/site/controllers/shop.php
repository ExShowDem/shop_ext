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
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * Product Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0.0
 */
class RedshopbControllerShop extends RedshopbControllerAdmin
{
	/**
	 * @var   int  Number of companies shown per shop page
	 *
	 * @since 1.0.0
	 */
	protected $companiesPerPage;

	/**
	 * @var   int  Number of departments shown per shop page
	 *
	 * @since 1.0.0
	 */
	protected $departmentsPerPage;

	/**
	 * @var   int  Number of employees shown per shop page
	 *
	 * @since 1.0.0
	 */
	protected $employeesPerPage;

	/**
	 * @var   int  Products shown per page
	 *
	 * @since 1.0.0
	 */
	public $productsPerPage;

	/**
	 * @var   int  Categories shown per page
	 *
	 * @since 1.0.0
	 */
	public $categoriesPerPage;

	/**
	 * @var   int  Show top category level
	 *
	 * @since 1.12.70
	 */
	public $requireTopLevel = 0;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		$compConfig               = RedshopbApp::getConfig();
		$this->companiesPerPage   = $compConfig->get('shop_companies_per_page', 12);
		$this->departmentsPerPage = $compConfig->get('shop_departments_per_page', 12);
		$this->employeesPerPage   = $compConfig->get('shop_employees_per_page', 12);
		$this->categoriesPerPage  = $compConfig->get('shop_categories_per_page', 12);
		$this->productsPerPage    = $compConfig->get('shop_products_per_page', 12);
		$input                    = Factory::getApplication()->input;

		if (strtolower($input->get("task")) == "ajaxgetcategoriespage")
		{
			// Set only if we inside categories view
			if (!$input->getInt('id', 0))
			{
				$this->requireTopLevel = 1;
			}

			$this->categoriesPerPage = strval(
				$compConfig->get('category_number_of_categories_per_column', 20) * $compConfig->get('categories_number_of_columns_per_page', 2
				)
			);
		}

		parent::__construct($config);
	}

	/**
	 * Save values in user session
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function savepath()
	{
		$app = Factory::getApplication();

		$app->getUserStateFromRequest('list.company_id', 'company_id', 0, 'int');
		$app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');
		$app->getUserStateFromRequest('list.rsbuser_id', 'rsbuser_id', 0, 'int');
		$app->setUserState('shop.customer_type', '');
		$app->setUserState('shop.customer_id', 0);
		$app->setUserState('shop.campaignItems', null);

		RedshopbHelperShop::checkImpersonationDepartment();

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.0.0
	 */
	protected function getRedirectToListAppend($append = '')
	{
		$app    = Factory::getApplication();
		$offers = $app->getUserState('shop.offers.filter_onsale');

		// Append the tab name for the company view
		if (!empty($offers))
		{
			$append .= '&layout=offers';
		}

		return $append;
	}

	/**
	 * Get the Route object for a redirect to list.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  string  Returns complete redirect link for shop mvc.
	 *
	 * @since   1.0.0
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->getBase64('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return RedshopbRoute::_($returnUrl . $append, false);
		}
		else
		{
			return RedshopbRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $append, false);
		}

	}

	/**
	 * Returns customer back to shopping from cart view
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function backtoshop()
	{
		$app    = Factory::getApplication();
		$vendor = RedshopbHelperShop::getVendor();

		$app->triggerEvent('RedshopbOnCheckoutBackToShop', array());

		if (!is_null($vendor))
		{
			$app->setUserState('list.company_id', $vendor->id);
			$app->setUserState('list.department_id', 0);
		}
		else
		{
			$app->getUserStateFromRequest('list.company_id', 'company_id', 0, 'int');
			$app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');
		}

		$app->getUserStateFromRequest('list.rsbuser_id', 'rsbuser_id', 0, 'int');

		RedshopbHelperShop::checkImpersonationDepartment();

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
	}

	/**
	 * Move to delivery
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function movetodelivery()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();

		$app->triggerEvent('RedshopbOnCheckoutMoveToDelivery', array());

		$app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array');
		$app->getUserStateFromRequest('checkout.shipping_date_delay', 'shipping_date_delay', array(), 'array');
		$app->getUserStateFromRequest('checkout.comment', 'comment', '', 'string');
		$app->getUserStateFromRequest('checkout.requisition', 'requisition', '', 'string');

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=delivery')));
		$this->redirect();
	}

	/**
	 * Move to shipping
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function movetoshipping()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();

		$app->triggerEvent('RedshopbOnCheckoutMoveToShipping', array());

		$app->setUserState('checkout.shipping_rate_id', '');
		$app->setUserState('checkout.shipping_rate_id_delay', '');
		$app->setUserState('checkout.pickup_stockroom_id', 0);

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=shipping')));
		$this->redirect();
	}

	/**
	 * Move to payment
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function movetopayment()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();

		$app->triggerEvent('RedshopbOnCheckoutMoveToPayment', array());

		$app->setUserState('checkout.payment_name', '');
		$app->setUserState('checkout.payment_delay_name', '');

		PluginHelper::importPlugin('redpayment');

		$app->triggerEvent('onRedpaymentResetExtraParameters');

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=payment')));
		$this->redirect();
	}

	/**
	 * Move to cart
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function movetocart()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$app->triggerEvent('RedshopbOnCheckoutMoveToCart', array());

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=cart')));
		$this->redirect();
	}

	/**
	 * Preform checkout action
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function checkout()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();

		$app->triggerEvent('RedshopbOnCheckoutMoveToCheckout', array());

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=cart')));

		$model = $this->getModel();
		$carts = $model->getCustomerOrders();

		if (empty($carts))
		{
			$this->redirect();
		}

		foreach ($carts as $cart)
		{
			if ($cart->customerType != 'employee' || !$cart->customerId)
			{
				continue;
			}

			$userCompany = RedshopbHelperUser::getUserCompany($cart->customerId);

			if (!$userCompany->useWallet)
			{
				continue;
			}

			$currencyId       = (is_numeric($cart->currency_id)) ? $cart->currency_id : RHelperCurrency::getCurrency($cart->currency)->id;
			$quantityMessages = array();

			foreach ($cart->regular->items as $product)
			{
				$productEntity  = RedshopbEntityProduct::load($product->product_id);
				$quantityStatus = $productEntity->checkQuantities($product->quantity);

				if (!$quantityStatus['isOK'])
				{
					$quantityMessages[] = Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' ' .
						RedshopbHelperUser::getUser($cart->customerId)->name . ': ' .
						Text::_($quantityStatus['msg']);

					continue;
				}
			}

			if (!empty($quantityMessages))
			{
				$this->setRedirect(
					$this->getRedirectToListRoute($this->getRedirectToListAppend()),
					implode('<br />', $quantityMessages),
					'error'
				);
			}

			// If there are an "Wallet" product inside cart. Validate client wallet with "Normal" product.
			if ($cart->isWalletCart)
			{
				$totalPrice = 0.0;

				foreach ($cart->regular->items as $product)
				{
					if (!$product->wallet)
					{
						$totalPrice += $product->price * $product->quantity;
					}
				}

				foreach ($cart->offers as $product)
				{
					if (!$product->wallet)
					{
						$totalPrice += $product->total;
					}
				}

				if (!RedshopbHelperUser::employeePurchase($cart->customerId, $totalPrice, $currencyId, $userCompany, true))
				{
					$this->setRedirect(
						$this->getRedirectToListRoute($this->getRedirectToListAppend()),
						Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' ' .
						RedshopbHelperUser::getUser($cart->customerId)->name . ': ' .
						Text::_('COM_REDSHOPB_SHOP_INSUFFICIENT_FUNDS'),
						'error'
					);

					break;
				}
			}
			elseif (!$cart->isWalletCart && !RedshopbHelperUser::employeePurchase($cart->customerId, $cart->total, $currencyId, $userCompany, true))
			{
				$this->setRedirect(
					$this->getRedirectToListRoute($this->getRedirectToListAppend()),
					Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' ' .
					RedshopbHelperUser::getUser($cart->customerId)->name . ': ' .
					Text::_('COM_REDSHOPB_SHOP_INSUFFICIENT_FUNDS'),
					'error'
				);

				break;
			}
		}

		$this->redirect();
	}

	/**
	 * Preform cart checkout action
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function cartcheckout()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();

		$app->triggerEvent('RedshopbOnCheckoutBeforeCartCheckout', array());

		$vendor          = RedshopbHelperShop::getVendor();
		$customerId      = $app->getUserState('shop.customer_id', 0);
		$customerType    = $app->getUserState('shop.customer_type', '');
		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);

		if (count(RedshopbHelperCart::getCartCustomers()) > 1)
		{
			if (RedshopbHelperACL::isSuperAdmin())
			{
				if ($customerCompany->type == 'customer')
				{
					$app->setUserState('shop.collect_customer_id', $customerCompany->id);
					$app->setUserState('shop.collect_customer_type', 'company');
				}
				else
				{
					$app->setUserState('shop.collect_customer_id', $vendor->id);
					$app->setUserState('shop.collect_customer_type', 'company');
				}
			}
			else
			{
				$userCompany    = RedshopbHelperUser::getUserCompany();
				$userDepartment = RedshopbHelperUser::getUserDepartment();

				if ($userCompany->type == 'customer' && is_null($userDepartment))
				{
					$app->setUserState('shop.collect_customer_id', $userCompany->id);
					$app->setUserState('shop.collect_customer_type', 'company');
				}
				elseif (!is_null($userDepartment))
				{
					$app->setUserState('shop.collect_customer_id', $userDepartment->id);
					$app->setUserState('shop.collect_customer_type', 'department');
				}
				else
				{
					$app->setUserState('shop.collect_customer_id', $userCompany->id);
					$app->setUserState('shop.collect_customer_type', 'company');
				}
			}
		}
		else
		{
			$app->setUserState('shop.collect_customer_id', null);
			$app->setUserState('shop.collect_customer_type', null);
		}

		$app->setUserState('checkout.shipping_date', array());
		$app->setUserState('checkout.shipping_date_delay', array());
		$app->setUserState('checkout.payment_name', '');
		$app->setUserState('checkout.payment_delay_name', '');
		$app->setUserState('checkout.shipping_rate_id', '');
		$app->setUserState('checkout.shipping_rate_id_delay', '');
		$app->setUserState('checkout.pickup_stockroom_id', 0);

		$app->triggerEvent('RedshopbOnCheckoutCartCheckout', array());

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=delivery')));
		$this->redirect();
	}

	/**
	 * Move to shipping page.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function shipping()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$prefix = !$this->validateDelivery() ? '&layout=delivery' : '&layout=shipping';

		$app = Factory::getApplication();
		PluginHelper::importPlugin('redshipping');
		$app->triggerEvent('onRedshopbCheckoutShipping', array($prefix));

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend($prefix)));
		$this->redirect();
	}

	/**
	 * Validate shipping variables
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function validateDelivery()
	{
		$app                  = Factory::getApplication();
		$input                = $app->input;
		$comment              = $app->getUserStateFromRequest('checkout.comment', 'comment', '', 'string');
		$requisition          = $app->getUserStateFromRequest('checkout.requisition', 'requisition', '', 'string');
		$deliveryAddressId    = $app->getUserState('checkout.delivery_address_id', 0) ?: $app->getUserStateFromRequest('checkout.delivery_address_id', 'delivery_address_id', 0, 'int');
		$context              = $this->getModel()->get('context');
		$context             .= '.shipping';
		$cache                = $app->getUserState($context, array());
		$config               = RedshopbEntityConfig::getInstance();
		$checkoutRegistration = $config->get('checkout_registration', 'registration_required');

		if (!$this->isValidShippingDate())
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_SHOP_SHIPPING_DATE_ALLOW_FROM', ''), 'warning');
			$app->setUserState($context, $cache);

			return false;
		}

		$user             = Factory::getUser();
		$allowGuestOrder  = ($checkoutRegistration != 'registration_required');
		$guestAddressData = $input->getArray();

		if ($user->guest && $allowGuestOrder && isset($guestAddressData['address']))
		{
			$customerId      = $app->getUserState('shop.customer_id', 0);
			$customerType    = $app->getUserState('shop.customer_type', '');
			$customerCompany = RedshopbEntityCompany::getInstanceByCustomer($customerId, $customerType);

			if ($customerCompany === false)
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_ERROR_UNKNOWN_COMPANY'), 'error');
				$app->setUserState($context, $cache);

				return false;
			}

			/** @var RedshopbTableAddress $addressTable */
			$addressTable = RedshopbTable::getAdminInstance('Address');

			$guestAddressData['customer_id']   = 0;
			$guestAddressData['customer_type'] = 'employee';

			$addressId = 0;

			// Check to see if the address has changed
			if (!empty($deliveryAddressId) &&  $deliveryAddressId !== $addressId && $addressId !== 'NO_ADDRESS_FIELDS')
			{
				$deliveryAddressId = $addressId;
			}

			$guestPhoneRequired = (bool) RedshopbEntityConfig::getInstance()->get('checkout_guest_phone_required', 0);

			if (!$deliveryAddressId)
			{
				// Checking this one separately since it is only required for guest checkout
				if ($guestPhoneRequired && empty($guestAddressData['phone']))
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_PHONE_NOT_SET'), 'error');

					$cache = $this->addAddressInputToCache($cache, $guestAddressData);
					$app->setUserState($context, $cache);

					return false;
				}

				$addressTable->bind($guestAddressData);

				if (!$addressTable->check() || !$addressTable->store())
				{
					$app->enqueueMessage($addressTable->getError(), 'error');

					$cache = $this->addAddressInputToCache($cache, $guestAddressData);

					$app->setUserState($context, $cache);

					return false;
				}

				$deliveryAddressId = $addressTable->get('id');
			}

			// Checking this one separately since it is only required for guest checkout
			if ($guestPhoneRequired && empty($guestAddressData['phone']))
			{
				if ($deliveryAddressId > 0)
				{
					$addressTable->load($deliveryAddressId);
				}

				if (empty($addressTable->get('phone')))
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_PHONE_NOT_SET'), 'error');

					$cache = $this->addAddressInputToCache($cache, $guestAddressData);
					$app->setUserState($context, $cache);

					return false;
				}
			}

			$app->setUserState('checkout.delivery_address_id', $deliveryAddressId);
		}

		if (!$deliveryAddressId)
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_DELIVERY_NOT_SET'), 'warning');
			$app->setUserState($context, $cache);

			return false;
		}

		$cache['shipping_date']       = (array) $app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array');
		$cache['shipping_date_delay'] = (array) $app->getUserStateFromRequest(
			'checkout.shipping_date_delay', 'shipping_date_delay', array(), 'array'
		);
		$cache['comment']             = $comment;
		$cache['requisition']         = $requisition;

		$app->setUserState($context, $cache);

		return true;
	}

	/**
	 * Method to store guest user inputs into session cache
	 *
	 * @param   array  $cache             data from the session
	 * @param   array  $guestAddressData  data from the user input
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	private function addAddressInputToCache($cache, $guestAddressData)
	{
		$model = $this->getModel();

		/** @var Form $form */
		$form   = $model->getForm();
		$fields = $form->getFieldset('checkout');

		foreach ($fields AS $name => $field)
		{
			if (!empty($guestAddressData[$name]))
			{
				$cache[$name] = $guestAddressData[$name];
			}
		}

		return $cache;
	}

	/**
	 * Method to make sure the requested shipping date is valid
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	private function isValidShippingDate()
	{
		$app    = Factory::getApplication();
		$config = RedshopbEntityConfig::getInstance();

		if (!$config->getInt('use_shipping_date', 0))
		{
			return true;
		}

		if (!$this->compareShippingDates((array) $app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array')))
		{
			return false;
		}

		if (RedshopbApp::getConfig()->getAllowSplittingOrder())
		{
			if (!$this->compareShippingDates(
				(array) $app->getUserStateFromRequest(
					'checkout.shipping_date_delay', 'shipping_date_delay', array(), 'array'
				)
			))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Compare Shipping Dates
	 *
	 * @param   array  $shippingDates  Shipping dates array
	 *
	 * @return boolean
	 *
	 * @since 1.12.82
	 */
	protected function compareShippingDates($shippingDates)
	{
		foreach ($shippingDates as $key => $value)
		{
			if ($value)
			{
				list($customerType, $customerId) = explode('_', $key);

				if (!RedshopbHelperOrder::isShippingDateAvailable($value, $customerType, $customerId))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Move to payment page.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function payment()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		if (!$this->validateDelivery())
		{
			$prefix = '&layout=delivery';
		}
		elseif (!$this->validateShipping())
		{
			$prefix = '&layout=shipping';
		}
		else
		{
			$prefix = '&layout=payment';
		}

		$app = Factory::getApplication();
		$app->triggerEvent('RedshopbOnCheckoutPayment', array($prefix));

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend($prefix)));

		$this->redirect();
	}

	/**
	 * Move to confirmation page.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function confirm()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		if (!$this->validateDelivery())
		{
			$prefix = '&layout=delivery';
		}
		elseif (!$this->validateShipping())
		{
			$prefix = '&layout=shipping';
		}
		elseif (!$this->validatePayment())
		{
			$prefix = '&layout=payment';
		}
		else
		{
			$this->storeInvoiceEmail();

			$prefix = '&layout=confirm';
		}

		$app = Factory::getApplication();
		$app->triggerEvent('RedshopbOnCheckoutConfirm', array($prefix));

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend($prefix)));
		$this->redirect();
	}

	/**
	 * Update order which has been editing via shop.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function updateorder()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$orderId = $app->getUserState('checkout.orderId', 0);

		$app->triggerEvent('RedshopbOnShopUpdateOrder', array($orderId));

		if ($orderId > 0)
		{
			$order = $this->getModel('Order')->getItem($orderId);
			$app->setUserState('checkout.shipping_date', array($order->customer_type . '_' . $order->customer_id => $order->shipping_date));
			$app->setUserState('checkout.comment', $order->comment);
			$app->setUserState('checkout.requisition', $order->requisition);
			$app->setUserState('checkout.payment_name', $order->payment_name);
			$app->setUserState('checkout.shipping_rate_id', $order->shipping_rate_id);

			if (isset($order->shipping_details['pickup_stockroom_id']))
			{
				$app->setUserState('checkout.pickup_stockroom_id', $order->shipping_details['pickup_stockroom_id']);
			}

			$app->setUserState('checkout.delivery_address_id', $order->delivery_address_id);

			if (count($this->saveShopOrder()) < 1)
			{
				$msg  = Text::_('COM_REDSHOPB_ORDER_EDIT_FAIL');
				$type = 'error';
			}
			else
			{
				$msg  = null;
				$type = null;
			}

			$this->setRedirect(
				RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . (int) $orderId, false),
				$msg,
				$type
			);
		}
		else
		{
			$order = null;
			$msg   = Text::_('COM_REDSHOPB_ORDER_EDIT_FAIL');
			$type  = 'error';
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=orders', false), $msg, $type);
		}

		$app->setUserState('shop.customer_id',  0);
		$app->setUserState('shop.customer_type',  '');
		$app->setUserState('shop.campaignItems', null);
		$app->setUserState('checkout.orderId', 0);
		$app->setUserState('checkout.delivery_address_id', 0);
		$app->setUserState('checkout.shipping_date', array());
		$app->setUserState('checkout.comment', '');
		$app->setUserState('checkout.requisition', '');
		$app->setUserState('checkout.payment_name', '');
		$app->setUserState('checkout.shipping_rate_id', '');
		$app->setUserState('checkout.pickup_stockroom_id', 0);
		$app->setUserState('list.rsbuser_id', 0);
		$app->setUserState('list.department_id', 0);
		$app->setUserState('list.company_id', 0);
		$app->setUserState('shop.user_redirection', 0);
		$app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array');
		$app->getUserStateFromRequest('checkout.comment', 'comment', '', 'string');
		$app->getUserStateFromRequest('checkout.requisition', 'requisition', '', 'string');
		$app->getUserStateFromRequest('checkout.payment_name', 'payment_name', '', 'string');
		$app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', '', 'string');

		RedshopbHelperShop::checkImpersonationDepartment();

		$app->triggerEvent('RedshopbOnShopAfterUpdateOrder', array($order));

		$this->redirect();
	}

	/**
	 * Validate payment
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function validatePayment()
	{
		$app            = Factory::getApplication();
		$cart           = RedshopbHelperCart::getFirstTotalPrice();
		$customerType   = $app->getUserState('shop.customer_type', '');
		$customerId     = $app->getUserState('shop.customer_id', 0);
		$companyId      = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$paymentMethods = RedshopbHelperOrder::getPaymentMethods($companyId, $cart[key($cart)], key($cart));
		$usingPayments  = RedshopbHelperOrder::isPaymentAllowed($paymentMethods);
		$return         = true;

		if ($usingPayments)
		{
			if (!$this->applyPaymentParameters($paymentMethods))
			{
				$return = false;
			}

			if (RedshopbApp::getConfig()->getAllowSplittingOrder()
				&& !$this->applyPaymentParameters($paymentMethods, '_delay'))
			{
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * applyPaymentParameters
	 *
	 * @param   array   $paymentMethods  Payment methods
	 * @param   string  $suffix          Field name suffix
	 *
	 * @return  boolean
	 *
	 * @since 1.12.82
	 */
	private function applyPaymentParameters($paymentMethods, $suffix = '')
	{
		$app         = Factory::getApplication();
		$paymentName = $app->getUserState('checkout.payment' . $suffix . '_name');
		$return      = true;

		if (!$paymentName)
		{
			if (count($paymentMethods) == 1)
			{
				$firstPayment = reset($paymentMethods);
				$paymentName  = $firstPayment->value;
			}
			else
			{
				$paymentName = $app->input->getString('payment' . $suffix . '_name', '');
			}

			if ($paymentName)
			{
				$paymentPluginName = explode('!', $paymentName);
				PluginHelper::importPlugin('redpayment', $paymentPluginName[0]);
				$app->triggerEvent('onRedpaymentApplyExtraParameters', array(&$return));
			}

			if ($return)
			{
				if ($paymentName)
				{
					$app->setUserState('checkout.payment' . $suffix . '_name', $paymentName);
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_PAYMENT_NOT_SET'), 'warning');

					$return = false;
				}
			}
		}

		return $return;
	}

	/**
	 * Validate shipping
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function validateShipping()
	{
		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$companyId    = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$cart         = RedshopbHelperCart::getFirstTotalPrice();

		$deliveryAddress   = new stdClass;
		$deliveryAddressId = $app->getUserState('checkout.delivery_address_id', 0);

		// Checking if delivery address is set in checkout process
		if ($deliveryAddressId > 0)
		{
			$deliveryAddress = RedshopbEntityAddress::getInstance($deliveryAddressId)->getExtendedData();
		}

		$shippingMethods = RedshopbHelperOrder::getShippingMethods($companyId, $deliveryAddress, $cart[key($cart)], key($cart));
		$usingShipping   = RedshopbHelperOrder::isShippingAllowed($shippingMethods);
		$return          = true;

		if ($usingShipping)
		{
			if (!$this->checkShippingRateId($shippingMethods))
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_SHIPPING_NOT_SET'), 'warning');

				$return = false;
			}

			if (RedshopbApp::getConfig()->getAllowSplittingOrder()
				&& !$this->checkShippingRateId($shippingMethods, '_delay'))
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_SHIPPING_NOT_SET'), 'warning');

				$return = false;
			}
		}

		$app->triggerEvent('onAESECValidateShipping', array(&$return, $customerId, $customerType, $deliveryAddressId));

		return $return;
	}

	/**
	 * Check Shipping Rate Id
	 *
	 * @param   array   $shippingMethods  Shipping methods
	 * @param   string  $suffix           Field suffix
	 *
	 * @return boolean
	 *
	 * @since 1.12.82
	 */
	protected function checkShippingRateId($shippingMethods, $suffix = '')
	{
		$app            = Factory::getApplication();
		$shippingRateId = $app->getUserStateFromRequest('checkout.shipping_rate_id' . $suffix, 'shipping_rate_id' . $suffix, 0, 'string');

		if (!$shippingRateId)
		{
			return false;
		}

		$selfPickupRateFound = false;

		if (!empty($shippingMethods))
		{
			foreach ($shippingMethods as $shippingMethod)
			{
				if ($shippingMethod->params->get('is_shipper', 0) == 0)
				{
					foreach ($shippingMethod->shippingRates as $shippingRate)
					{
						if ($shippingRate->id == $shippingRateId)
						{
							$app->getUserStateFromRequest('checkout.pickup_stockroom_id' . $suffix, 'pickup_stockroom_id' . $suffix, 0, 'int');
							$selfPickupRateFound = true;

							break(2);
						}
					}
				}
			}
		}

		if (!$selfPickupRateFound)
		{
			$app->setUserState('checkout.pickup_stockroom_id' . $suffix, 0);
		}

		return true;
	}

	/**
	 * Completes and sends order.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function completeorder()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$prefix = '';
		$app    = Factory::getApplication();
		$terms  = $app->input->getInt('terms', 1);

		$app->triggerEvent('RedshopbOnBeforeCheckoutCompleteOrder', array());

		if (!$this->validateDelivery())
		{
			$prefix = '&layout=delivery';
		}
		elseif (!$this->validateShipping())
		{
			$prefix = '&layout=shipping';
		}
		elseif (!$this->validatePayment())
		{
			$prefix = '&layout=payment';
		}
		elseif (!$terms)
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_SHOP_TERMS_AND_CONDITIONS_ACCEPT_ERROR'), 'warning');
			$prefix = '&layout=confirm';
		}

		if ($prefix)
		{
			$config       = RedshopbEntityConfig::getInstance();
			$checkoutMode = $config->get('checkout_mode', 'default', 'string');

			if ($checkoutMode != 'default')
			{
				$prefix = '&layout=cart';
			}

			$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend($prefix)));
			$this->redirect();
		}

		$this->storeInvoiceEmail();

		$paymentName       = $app->getUserState('checkout.payment_name', '');
		$model             = $this->getModel('shop');
		$orderIds          = $this->saveShopOrder();
		$orderExist        = false;
		$isPaymentRedirect = false;
		$multipleOrderIds  = '';
		$orderId           = 0;

		if (is_array($orderIds) && !empty($orderIds))
		{
			// B/C code, it was a typo in event naming. Should be removed at some point.
			$app->triggerEvent('RedshopbOnAfterCheckouSaveShopOrder', array($orderIds));

			$app->triggerEvent('RedshopbOnAfterCheckoutSaveShopOrder', array($orderIds));

			$count   = count($orderIds);
			$comment = $app->getUserStateFromRequest(
				'checkout.comment',
				'comment',
				'',
				'string'
			);

			$requisition = $app->getUserStateFromRequest(
				'checkout.requisition',
				'requisition',
				'',
				'string'
			);

			if ($count > 1)
			{
				$currency = RedshopbHelperOrder::areAllHavingSameCurrency($orderIds);

				/** @var RedshopbModelOrders $modelOrders */
				$modelOrders  = $this->getModel('orders');
				$orderCollect = RedshopbEntityConfig::getInstance()->get('order_collect', 0, 'int');

				if ($currency && $orderCollect)
				{
					$vendor            = RedshopbHelperShop::getVendor();
					$customerId        = $app->getUserState('shop.collect_customer_id', $app->getUserState('shop.customer_id', 0));
					$customerType      = $app->getUserState('shop.collect_customer_type', $app->getUserState('shop.customer_type', ''));
					$customer          = RedshopbEntityCustomer::getInstance($customerId, $customerType);
					$addressId         = $customer->getDeliveryAddress()->id;
					$deliveryAddressId = $app->getUserState(
						'checkout.delivery_address_id',
						$addressId
					);

					$app->setUserState('orders.customer_id', $customerId);
					$app->setUserState('orders.customer_type', $customerType);
					$app->setUserState('shop.campaignItems', null);
					$app->setUserState('orders.parentEntity', $vendor);

					$orderId    = $modelOrders->collectOrders($orderIds, $deliveryAddressId, $comment, $requisition);
					$orderExist = true;

					$shopperCompany = $customer->getCompany();

					if ($shopperCompany->order_approval)
					{
						RedshopbHelperOrder::checkOrderExpedite($orderId, $deliveryAddressId);
					}
					else
					{
						$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_ORDER_MAIL_COMPLETED_SUBJECT', $orderId));
					}

					// Send email notification for this order collection
					$this->sendMail($paymentName, $orderId);
				}
				else
				{
					// If collection cannot be done because of currency or collection is disabled, send individual emails
					foreach ($orderIds as $orderId)
					{
						$customerOrder     = RedshopbHelperOrder::getOrderCustomer($orderId);
						$customerId        = $customerOrder->customer_id;
						$customerType      = $customerOrder->customer_type;
						$customer          = RedshopbEntityCustomer::getInstance($customerId, $customerType);
						$addressId         = $customer->getDeliveryAddress()->id;
						$shopperCompany    = $customer->getCompany();
						$deliveryAddressId = $app->getUserState(
							'checkout.delivery_address_id',
							$addressId
						);

						if ($shopperCompany->order_approval)
						{
							RedshopbHelperOrder::checkOrderExpedite($orderId, $deliveryAddressId);
						}
						else
						{
							$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_ORDER_MAIL_COMPLETED_SUBJECT', $orderId));
						}

						$this->sendMail($paymentName, $orderId);

						$orderExist = true;
					}

					$multipleOrderIds = implode(',', $orderIds);
				}
			}
			elseif ($count == 1)
			{
				$orderId    = $orderIds[0];
				$orderExist = true;

				$this->sendMail($paymentName, $orderId);

				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_ORDER_MAIL_COMPLETED_SUBJECT', $orderId));
			}
		}

		if ($orderExist)
		{
			$model->clearCart(true);
			$app->setUserState('list.company_id', 0);
			$app->setUserState('list.department_id', 0);
			$app->setUserState('list.rsbuser_id', 0);
			$app->setUserState('shop.campaignItems', null);
			$app->setUserState('checkout.shipping_date', array());
			$app->setUserState('checkout.shipping_date_delay', array());
			$app->setUserState('checkout.comment', '');
			$app->setUserState('checkout.requisition', '');
			$app->setUserState('checkout.payment_name', '');
			$app->setUserState('checkout.payment_delay_name', '');
			$app->setUserState('checkout.shipping_rate_id', '');
			$app->setUserState('checkout.shipping_rate_id_delay', '');
			$app->setUserState('checkout.pickup_stockroom_id', 0);
			$app->setUserState('checkout.delivery_address_id', 0);
			$app->setUserState('checkout.orderId', 0);
			$app->setUserState('shop.vendor', null);

			// In case, user can impersonate, do not clean customer type and customer Id
			if (!RedshopbHelperACL::getPermissionInto('impersonate', 'order'))
			{
				$app->setUserState('shop.customer_id', 0);
				$app->setUserState('shop.customer_type', '');
			}

			RedshopbHelperShop::checkImpersonationDepartment();

			if ($multipleOrderIds != '')
			{
				$redirect = $this->getRedirectToListRoute(
					$this->getRedirectToListAppend('&layout=receipt&multipleOrderIds=' . $multipleOrderIds)
				);

				foreach (explode(',', $multipleOrderIds) as $key => $oid)
				{
					$token = RedshopbEntityOrder::load($oid)->get('token');

					$redirect .= 0 === $key ? "&token={$token}" : ",{$token}";
				}
			}
			else
			{
				if (!empty($paymentName))
				{
					// We used payment method so we are going to redirect to pay form
					$redirect          = $this->getRedirectToListRoute(
						$this->getRedirectToListAppend('&layout=pay&orderId=' . $orderId)
					);
					$isPaymentRedirect = true;
				}
				else
				{
					$redirect = $this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=receipt&orderId=' . $orderId));

					$redirect .= '&token=' . RedshopbEntityOrder::load($orderId)->get('token');
				}
			}
		}
		else
		{
			$redirect = $this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=confirm'));
		}

		$app->triggerEvent('RedshopbOnAfterCheckouCompleteOrder', array($orderId, $orderExist));

		if ($isPaymentRedirect)
		{
			PluginHelper::importPlugin('redpayment');
			$orderModel  = RModel::getAdminInstance('Order');
			$order       = (object) $orderModel->getItem($orderId)->getProperties();
			$paymentData = RedshopbHelperOrder::preparePaymentData($order);
			$app->triggerEvent('onRedpaymentAfterCheckoutRedirectPayment',
				array(
					$paymentData['payment_name'],
					$paymentData['extension_name'],
					$paymentData['owner_name'],
					$paymentData
				)
			);
		}

		$this->setRedirect($redirect)->redirect();
	}

	/**
	 * Proxy to the RedshopbHelperOrder::sendMail function
	 * allows us to only send mails instantly with specific payment types
	 *
	 * @param   string  $paymentName  name of the payment being used
	 * @param   int     $orderId      order id
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function sendMail($paymentName, $orderId)
	{
		$config                     = RedshopbApp::getConfig();
		$alwaysSendMail             = $config->getBool('order_notification_disregard_payment_status', false);
		list($actualName, $postfix) = explode('!', $paymentName);

		$actualName = strtolower($actualName);

		// Only send order mail notification if there are no payment is choose or its an offline payment
		if (!$alwaysSendMail && !empty($paymentName) && !in_array($actualName, array('bank_transfer', 'ean')))
		{
			return;
		}

		RedshopbHelperOrder::sendMail($orderId);
	}

	/**
	 * Complete shopping order.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function finish()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$menu    = $app->getMenu();
		$default = $menu->getDefault();

		$app->setUserState('checkout.orderId', 0);
		$this->setRedirect(
			Route::_($default->link . '&Itemid=' . $default->id, false)
		);
		$this->redirect();
	}

	/**
	 * Print order as PDF file.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function printpdf()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$id  = $app->getUserState('checkout.orderId', 0);

		if ($id <= 0)
		{
			$id = $app->input->get('orderId', 0, 'int');
		}

		if (!$id)
		{
			$multipleOrderIds = $app->input->get('multipleOrderIds', '', 'string');

			if ($multipleOrderIds != '')
			{
				$multipleOrders = explode(',', $multipleOrderIds);

				if (count($multipleOrders) > 0)
				{
					$id = $multipleOrders;
				}
			}
		}

		if ($id)
		{
			RedshopbHelperOrder::printPDF($id);
		}

		$app->close();
	}

	/**
	 * Set delivery address by address id
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxSetDeliveryAddress()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app            = Factory::getApplication();
		$id             = $app->input->getInt('delivery_address_id', 0);
		$responseObject = new RedshopbAjaxResponse;

		$app->setUserState('checkout.usebilling', $app->input->getBool('usebilling', false));
		$app->setUserState('checkout.delivery_address_id', $id);

		if ($id === 0)
		{
			header('HTTP/1.1 400 Bad Request');
			$responseObject->setMessage('COM_REDSHOPB_SHOP_DELIVERY_NOT_SET', true);
			$responseObject->setMessageType('alert-error');

			echo json_encode($responseObject);
			$app->close();
		}

		$responseObject->address = RedshopbEntityAddress::getInstance($id)->getExtendedData();
		$responseObject->setBody(RedshopbLayoutHelper::render('addresses.shipping_address', $responseObject->address));

		echo json_encode($responseObject);
		$app->close();
	}

	/**
	 * Save new delivery address for current customer or update current
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxSaveDeliveryAddress()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$app->setUserState('checkout.usebilling', false);

		$deliveryAddressId = $input->getInt('delivery_address_id', 0);
		$customerType      = $app->getUserState('shop.customer_type', 'employee');
		$customerId        = $app->getUserState('shop.customer_id', 0);

		$data         = array();
		$data['type'] = 1;

		if (!empty($deliveryAddressId))
		{
			$address      = RedshopbEntityAddress::getInstance($deliveryAddressId);
			$data['type'] = $address->getItem()->type;
			$customer     = $address->getCustomer();
			$customerId   = $customer->getId();
			$customerType = $customer->getType();
		}

		$responseObject = new RedshopbAjaxResponse(Text::_('COM_REDSHOPB_SHOP_ADDRESS_SUCCESS_UPDATE_DELIVERY'));

		if (empty($customerId))
		{
			header('HTTP/1.1 412 Precondition Failed');
			$responseObject->setMessage('COM_REDSHOPB_SHOP_DELIVERY_UNKNOWN_CUSTOMER_ID', true);
			$responseObject->setMessageType('alert-error');

			echo json_encode($responseObject);
			$app->close();
		}

		$action                = $this->input->getString('action', 'new');
		$data['address']       = $this->input->getString('address', '');
		$data['address2']      = $this->input->getString('address2', '');
		$data['name']          = $this->input->getString('name', '');
		$data['name2']         = $this->input->getString('name2', '');
		$data['country_id']    = $this->input->getInt('country_id', '');
		$data['state_id']      = $this->input->getInt('state', '');
		$data['zip']           = $this->input->getString('zip', '');
		$data['city']          = $this->input->getString('city', '');
		$data['phone']         = $this->input->getString('phone', '');
		$data['email']         = $this->input->getString('email', '');
		$data['customer_id']   = $customerId;
		$data['customer_type'] = $customerType;
		$data['type']          = $this->getAddressType($customerId, $customerType, $data['type'], ($action == 'new'));

		$this->validateAddressValues('address', $data['address'], $responseObject);
		$this->validateAddressValues('zip', $data['zip'], $responseObject);
		$this->validateAddressValues('city', $data['city'], $responseObject);
		$this->validateAddressValues('country', $data['country_id'], $responseObject);
		$this->validateAddressValues('type', $data['type'], $responseObject);

		// Check if we are creating new or updating existing one
		if ($action != 'new')
		{
			$data['id'] = $deliveryAddressId;
		}

		$responseObject->data = $data;

		$table = RedshopbTable::getAdminInstance('Address');

		if (!$table->save($data))
		{
			$responseObject->setMessageType('alert-error');
			$responseObject->setMessage($table->getError());

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($responseObject);
			$app->close();
		}

		$app->setUserState('checkout.delivery_address_id', $table->id);

		$responseObject->address = RedshopbEntityAddress::getInstance($table->id)->bind($table->getProperties())->getExtendedData();
		$responseObject->setBody(RedshopbLayoutHelper::render('addresses.shipping_address', $responseObject->address));

		echo json_encode($responseObject);
		$app->close();
	}

	/**
	 * Method to validate required address fields
	 *
	 * @param   string                $name            name of the field
	 * @param   mixed                 $value           input value for the field
	 * @param   RedshopbAjaxResponse  $responseObject  ajax response object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function validateAddressValues($name, $value, $responseObject)
	{
		if (!empty($value))
		{
			return;
		}

		header('HTTP/1.1 400 Bad Request');
		$responseObject->setMessage('COM_REDSHOPB_SHOP_ADDRESS_MISSING_' . strtoupper($name), true);
		$responseObject->setMessageType('alert-warning');
		$responseObject->{$name} = $value;

		echo json_encode($responseObject);
		Factory::getApplication()->close();
	}

	/**
	 * Method for getting address type using customer data and current input.
	 *
	 * @param   integer $customerId    [description]
	 * @param   string  $customerType  [description]
	 * @param   string  $typeFromInput [description]
	 * @param   boolean $isNew         [description]
	 *
	 * @return  integer|string
	 *
	 * @since   1.0.0
	 */
	private function getAddressType($customerId, $customerType, $typeFromInput, $isNew)
	{
		$table        = RedshopbTable::getAdminInstance('Address');
		$loadSettings = array('customer_id' => $customerId, 'customer_type' => $customerType, 'type' => 3);

		// Try to find default shipping available
		if (!$table->load($loadSettings))
		{
			// If not found - return 3 "default shipping address"
			return 3;
		}

		if ($isNew)
		{
			// Then return type 1 "regular not default"
			return 1;
		}

		return $typeFromInput;
	}

	/**
	 * Ajax call to get change drop down attribute from product
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxChangeDropDownAttribute()
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$productId    = $input->getInt('product_id', 0);
		$collectionId = $input->getInt('collection_id', 0);
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$layout       = $input->getVar('forlayout', '');
		$thumbStyle   = $input->getString('thumbStyle', 'images');

		$placeOrderPermission = RedshopbHelperACL::getPermission('place', 'order');

		$dropDownSelected = $app->getUserStateFromRequest(
			'list.drop_down_selected.' . $customerType . '_' . $customerId . '_' . $productId,
			'drop_down_selected',
			0,
			'int'
		);

		if ($productId > 0)
		{
			/** @var RedshopbModelShop $shopModel */
			$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
			$shopModel->setState('product_collection', $collectionId);
			$shopModel->setState('filter.product_collection', $collectionId);
			$shopModel->setState('filter.product_id', $productId);

			// Search for sale items
			// $shopModel->setState('filter.onsale', $onSale);
			$items = $shopModel->getItems();

			$preparedItems = $shopModel->prepareItemsForShopView($items, $customerId, $customerType, $collectionId);

			if (!empty($preparedItems->prices) && $collectionId)
			{
				// Update prices array if collection is used
				$prices = array();

				foreach ($preparedItems->prices as $price)
				{
					if (!array_key_exists($price->product_id, $prices))
					{
						$prices[$price->product_id] = array();
					}

					$prices[$price->product_id][$price->id] = $price;
				}

				$preparedItems->prices = $prices;
			}

			$config              = RedshopbApp::getConfig();
			$thumbWidth          = null;
			$thumbHeight         = null;
			$displayWashCareLink = true;

			switch ($layout)
			{
				case 'products':
					$thumbWidth          = $config->get('product_image_width', 256);
					$thumbHeight         = $config->get('product_image_height', 256);
					$displayWashCareLink = false;

					break;
			}

			echo RedshopbLayoutHelper::render('shop.attributesvariants', array(
					'collectionId'         => $collectionId,
					'productId'            => $productId,
					'displayProductImages' => true,
					'productImages'        => $shopModel::getProductImages(array($productId), array($productId => $dropDownSelected)),
					'displayAccessories'   => true,
					'accessories'          => $preparedItems->accessories,
					'displayWashCareLink'  => $displayWashCareLink,
					'dropDownSelected'     => $dropDownSelected,
					'staticTypes'          => $preparedItems->staticTypes,
					'dynamicTypes'         => $preparedItems->dynamicTypes,
					'issetItems'           => $preparedItems->issetItems,
					'issetDynamicVariants' => $preparedItems->issetDynamicVariants,
					'prices'               => $preparedItems->prices,
					'showStockAs'          => $preparedItems->showStockAs,
					'currency'             => $preparedItems->currency,
					'customerId'           => $customerId,
					'customerType'         => $customerType,
					'thumbWidth'           => $thumbWidth,
					'thumbHeight'          => $thumbHeight,
					'placeOrderPermission' => $placeOrderPermission,
					'thumbStyle'           => $thumbStyle
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to update attribute options, images and accessories
	 * on option change.
	 *
	 * @return  void
	 *
	 * @since   1.12.43
	 * @throws  Exception
	 */
	public function ajaxOnAttributeValueChange()
	{
		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$config       = RedshopbApp::getConfig();
		$input        = $app->input;
		$productId    = $input->getInt('productId', 0);
		$collectionId = $input->getInt('collectionId', 0);
		$currencyId   = $input->getInt('currencyId', 0);
		$thumbStyle   = $input->getString('thumbStyle', 'images');
		$attrValues   = $input->get('attrValues', array(), 'array');
		$tmp          = array();
		$isMain       = $input->getInt('isMain', 0);
		$mainValue    = '';
		$result       = new stdClass;
		$shopModel    = $this->getModel();

		// Set collection to shop model
		$shopModel->setState('filter.product_collection', $collectionId);

		foreach ($attrValues as $attrValue)
		{
			if (is_object($attrValue))
			{
				$id  = $attrValue->aId;
				$val = $attrValue->aValue;
			}
			else
			{
				$id  = $attrValue['aId'];
				$val = $attrValue['aValue'];
			}

			if ($isMain && $id == $isMain)
			{
				$mainValue = $val;
			}

			$tmp[$id] = $val;
		}

		$attrValues = $tmp;

		if ($isMain)
		{
			$thumbWidth         = $config->get('product_image_width', 256);
			$thumbHeight        = $config->get('product_image_height', 256);
			$productImages      = $shopModel->getProductImages(array($productId), array($productId => $mainValue));
			$result->imagesHtml = RedshopbLayoutHelper::render(
				'tags.product.' . $thumbStyle,
				array(
					'isAjax'        => true,
					'productId'     => $productId,
					'collectionId'  => $collectionId,
					'productImages' => $productImages,
					'width'         => $thumbWidth,
					'height'        => $thumbHeight
				)
			);

			$productAccessories = $shopModel->getAccessories(
				array($productId),
				array($productId => $mainValue),
				0, $customerId, $customerType,
				$currencyId
			);

			if (!empty($productAccessories[$productId]))
			{
				$result->accessoriesHtml = RedshopbHelperProduct::renderAccessoriesDropdown($productAccessories[$productId], $productId);
			}
			else
			{
				$result->accessoriesHtml = '';
			}
		}

		$result->attributesData = array();

		/** @var RedshopbModelProduct $pModel */
		$pModel    = RModelAdmin::getInstance('Product', 'RedshopbModel', array('ignore_request' => true));
		$arrValues = array_values($attrValues);
		$attrData  = $pModel->getAttributes($productId, $arrValues, true, false);
		$pItemId   = RedshopbEntityProduct_Item::getInstanceByAttributeValues($arrValues)->get('id');

		if ($pItemId)
		{
			$price = RedshopbHelperPrices::getProductItemPrice($pItemId, $customerId, $customerType, $currencyId, array($collectionId));
		}
		else
		{
			$price = null;
		}

		// Set product item
		$result->productItemId = $pItemId;

		// Set product item price
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

		foreach ($attrData as $attr)
		{
			$options  = array(HTMLHelper::_('select.option', 0, Text::_('JSELECT'), 'value', 'text'));
			$values   = array_keys($attr['values']);
			$values   = ArrayHelper::toInteger($values);
			$selected = (int) $attrValues[$attr['id']];
			$selected = in_array($selected, $values) ? $selected : null;

			foreach ($attr['values'] as $id => $value)
			{
				$options[] = HTMLHelper::_('select.option', $id, $value, 'value', 'text');
			}

			$tmp       = new stdClass;
			$tmp->aId  = $attr['id'];
			$tmp->html = HTMLHelper::_('select.options', $options, 'value', 'text', $selected);

			$result->attributesData[] = $tmp;
		}

		echo json_encode($result);

		$app->close();
	}

	/**
	 * Method to do an ajax refresh of the checkout page
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxRefreshCheckout()
	{
		// Validate ajax request
		RedshopbHelperAjax::validateAjaxRequest();

		$ajaxResponse = new RedshopbAjaxResponse;

		try
		{
			$ajaxResponse->setBody($this->renderCustomerBasket());
		}
		catch (Exception $e)
		{
			header('HTTP/1.1 500 Internal Server Error');

			$ajaxResponse->setMessage($e->getMessage())
				->setMessageType('alert-error');
		}

		echo json_encode($ajaxResponse);

		Factory::getApplication()->close();
	}

	/**
	 * Method to do an ajax refresh of the shipping methods
	 *
	 * @return   void
	 */
	public function ajaxRefreshShipping()
	{
		// Validate ajax request
		RedshopbHelperAjax::validateAjaxRequest();
		$ajaxResponse = new RedshopbAjaxResponse;

		$app = Factory::getApplication();

		try
		{
			$ajaxResponse->setBody($this->renderShippingMethods());
		}
		catch (Exception $e)
		{
			header('HTTP/1.1 500 Internal Server Error');

			$ajaxResponse->setMessage($e->getMessage())
				->setMessageType('alert-error');
		}

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Get the address based on the stored delivery id or construct it based on input (for guest users)
	 *
	 * @throws Exception If {@see Factory::getApplication()} fails
	 *
	 * @return stdClass
	 *
	 * @since 2.2.0
	 */
	private function getAddress()
	{
		$app = Factory::getApplication();

		$deliveryAddressId = $app->input->getInt('delivery_address_id', 0);

		/** @var RedshopbEntityAddress $deliveryAddress */
		$deliveryAddress = RedshopbEntityAddress::getInstance($deliveryAddressId);

		if ($deliveryAddress->isLoaded())
		{
			return $deliveryAddress->getExtendedData();
		}

		$user = RedshopbEntityUser::loadActive(true);

		if (false == $user->getJoomlaUser()->guest)
		{
			return $user->getDeliveryAddress(true)->getExtendedData();
		}

		$post            = $app->input->post;
		$deliveryAddress = new stdClass;

		$deliveryAddress->name       = $post->getString('name');
		$deliveryAddress->email      = $post->get('email');
		$deliveryAddress->phone      = $post->getString('phone');
		$deliveryAddress->address    = $post->getString('address');
		$deliveryAddress->address2   = $post->getString('address2');
		$deliveryAddress->city       = $post->getString('city');
		$deliveryAddress->zip        = $post->getString('zip');
		$deliveryAddress->state_id   = $post->getString('state_id');
		$deliveryAddress->country_id = $post->getInt('country_id');

		return $deliveryAddress;
	}

	/**
	 * Method to render customer baskets
	 *
	 * @return string
	 */
	private function renderCustomerBasket()
	{
		$app = Factory::getApplication();

		/** @var RedshopbModelShop $model */
		$model  = $this->getModel('Shop');
		$config = RedshopbEntityConfig::getInstance();

		$settings              = new stdClass;
		$settings->layout      = 'checkout.customer_basket';
		$settings->displayData = (object) array(
			'config'              => RedshopbEntityConfig::getInstance(),
			'state'               => $model->getState(),
			'customerOrders'      => $model->getCustomerOrders(),
			'form'                => $model->getCustomForm('cartitems'),
			'showStockAs'         => RedshopbHelperStockroom::getStockVisibility(),
			'delivery'            => $config->get('stockroom_delivery_time', 'hour'),
			'return'              => base64_encode('index.php?option=com_redshopb&view=shop&layout=cart'),
			'orderId'             => $app->getUserState('checkout.orderId', 0),
			'feeProducts'         => RedshopbHelperShop::getChargeProducts('fee'),
			'view'                => 'shop',
			'quantityfield'       => 'quantity',
			'isEmail'             => false,
			'canEdit'             => true,
			'showToolbar'         => false,
			'total'               => false,
			'checkbox'            => false,
			'userCart'            => true,
			'showDeliveryAddress' => false,
			'lockquantity'        => false,
			'showCartHeader'      => true,
			'user'                => Factory::getUser(),
			'shippingRateId'      => $app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', '', 'string')
		);

		$app->triggerEvent('onBeforeRedshopbRenderCustomerBasket', array($settings));

		return RedshopbLayoutHelper::render($settings->layout, (array) $settings->displayData);
	}

	/**
	 * Method to render shipping methods
	 *
	 * @return   string
	 */
	private function renderShippingMethods()
	{
		$app = Factory::getApplication();

		$app->triggerEvent('onAECBeforeRenderShippingMethods');

		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customer     = RedshopbHelperCompany::getCustomerCompanyByCustomer($customerId, $customerType);
		$companyId    = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$cart         = RedshopbHelperCart::getFirstTotalPrice();

		$shippingMethods = RedshopbHelperOrder::getShippingMethods(
			$companyId,
			$this->getAddress(),
			$cart[key($cart)],
			key($cart)
		);

		$displayData = array(
			'showTitle' => true,
			'options' => array(
				'shippingMethods' => $shippingMethods,
				'extensionName' => 'com_redshopb',
				'ownerName' => implode(',', RedshopbEntityCompany::getInstance($companyId)->getPriceGroups()->ids()),
				'name' => 'shipping_rate_id',
				'value' => $app->getUserState('checkout.shipping_rate_id', ''),
				'id' => 'shipping_rate_id',
				'attributes' => '',
				'customer' => $customer
			)
		);

		return RedshopbLayoutHelper::render('checkout.shipping_form', $displayData);
	}

	/**
	 * Reset shopper selection.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function changevendor()
	{
		$app = Factory::getApplication();
		$app->triggerEvent('onBeforeRedshopbChangeVendor');

		RedshopbHelperShop::unsetVendor();

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
	}

	/**
	 * Reset employee selection.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function changecustomer()
	{
		$app = Factory::getApplication();
		$app->triggerEvent('onBeforeRedshopbChangeCustomer');

		$companiesVendor = RedshopbEntityConfig::getInstance()
			->get('vendor_of_companies', 'parent');

		if ($companiesVendor != 'parent')
		{
			$app->setUserState('list.company_id', 0);
			$app->setUserState('list.department_id', 0);
		}

		$app->setUserState('list.rsbuser_id', 0);
		$app->setUserState('shop.customer_type', '');
		$app->setUserState('shop.customer_id', 0);
		$app->setUserState('shop.campaignItems', null);
		$app->setUserState('shop.filter', null);

		RedshopbHelperShop::checkImpersonationDepartment();

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
	}

	/**
	 * Unset shop vendor.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function vendorunset()
	{
		$app = Factory::getApplication();
		$app->triggerEvent('onBeforeRedshopbVendorUnset');

		$app->setUserState('list.company_id', RedshopbHelperUser::getUserCompanyId());
		$app->setUserState('list.department_id', 0);
		$app->setUserState('list.rsbuser_id', 0);
		$app->setUserState('shop.vendor', null);
		$app->setUserState('shop.customer_type', '');
		$app->setUserState('shop.customer_id', 0);
		$app->setUserState('shop.campaignItems', null);
		$app->setUserState('shop.filter', null);

		/** @var RedshopbModelShop $model */
		$model = $this->getModel();
		$model->clearCart(true);

		RedshopbHelperShop::checkImpersonationDepartment();

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
	}

	/**
	 * Saves shop order.
	 *
	 * @return  array|boolean Order ids or false on error saving.
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	private function saveShopOrder()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		/** @var RedshopbModelShop $modelShop */
		$modelShop      = $this->getModel('shop');
		$customerOrders = $modelShop->getCustomerOrders();
		$orderIds       = array();

		if (!empty($customerOrders))
		{
			$oneCustomerOrder = count($customerOrders) === 1;
			$session          = Factory::getSession();
			$app              = Factory::getApplication();

			$collectiveOrder             = null;
			$regularCustomerOrder        = null;
			$regularCustomerDelayedOrder = null;

			foreach ($customerOrders as $customerOrder)
			{
				if (isset($customerOrder->regular->items) && !empty($customerOrder->regular->items))
				{
					$cartId = $session->get('saved_cart.' . $customerOrder->customerType . '.' . $customerOrder->customerId, null, 'redshopb');

					if ($cartId && !empty($customerOrder->regular->items))
					{
						$db    = Factory::getDbo();
						$query = $db->getQuery(true)
							->select('*')
							->from($db->qn('#__redshopb_cart'))
							->where('id = ' . (int) $cartId);

						$savedCart = $db->setQuery($query, 0, 1)
							->loadObject();

						if ($savedCart)
						{
							$query->clear()
								->select('sci.*')
								->from($db->qn('#__redshopb_cart_item', 'sci'))
								->where($db->qn('sci.cart_id') . ' = ' . (int) $cartId);

							$cartItems = $db->setQuery($query)
								->loadObjectList();

							if (!empty($cartItems))
							{
								$foundItem        = false;
								$foundAccessories = array();

								foreach ($cartItems as $key => $cartItem)
								{
									if ($cartItem->parent_cart_item_id)
									{
										if (!isset($foundAccessories[$cartItem->parent_cart_item_id]))
										{
											$foundAccessories[$cartItem->parent_cart_item_id] = array();
										}

										$foundAccessories[$cartItem->parent_cart_item_id][] = $cartItem->product_id;
										unset($cartItems[$key]);
									}
								}

								foreach ($cartItems as $cartItem)
								{
									$foundItem = false;

									foreach ($customerOrder->regular->items as $item)
									{
										if ($cartItem->product_id == $item->product_id
											&& (int) $cartItem->product_item_id == (int) $item->product_item_id
											&& (int) $cartItem->collection_id == (int) $item->collection_id
											&& $cartItem->quantity == $item->quantity)
										{
											$idsAccessories = array();

											if (!empty($item->accessories))
											{
												foreach ($item->accessories as $accessory)
												{
													$idsAccessories[] = $accessory['accessory_id'];
												}
											}

											if (array_key_exists($cartItem->id, $foundAccessories)
												&& !empty($idsAccessories)
												&& count(array_diff($foundAccessories[$cartItem->id], $idsAccessories)) == 0
												&& count(array_diff($idsAccessories, $foundAccessories[$cartItem->id])) == 0)
											{
												$foundItem = true;
											}
											elseif (!array_key_exists($cartItem->id, $foundAccessories)
												&& empty($idsAccessories))
											{
												$foundItem = true;
											}
										}
									}

									if (!$foundItem)
									{
										break;
									}
								}

								if ($foundItem)
								{
									$query->clear()
										->update($db->qn('#__redshopb_cart'))
										->set('last_order = ' . $db->q(Date::getInstance()->toSql()))
										->where('id = ' . (int) $cartId);

									if (!$db->setQuery($query)->execute())
									{
										$app->enqueueMessage($db->getErrorMsg(), 'error');
										$db->transactionRollback();

										return false;
									}
								}
							}
						}
					}

					// Exclude delay products
					$regularCustomerOrder = RedshopbHelperOrder::getSpecificCartProducts($customerOrder->regular);
					$collectiveOrder      = $regularCustomerOrder;

					// Store delay products
					$regularCustomerDelayedOrder = RedshopbHelperOrder::getSpecificCartProducts($customerOrder->regular, true);
				}

				if (isset($customerOrder->offers) && !empty($customerOrder->offers))
				{
					$valuesToAdd = array(
						'totalFinal', 'hiddenTotalFinal', 'subtotalWithoutDiscounts', 'hiddenSubtotalWithoutDiscounts'
					);

					foreach ($customerOrder->offers as $offer)
					{
						if (!$collectiveOrder)
						{
							$collectiveOrder           = $offer;
							$collectiveOrder->offer_id = array($collectiveOrder->offer_id);

							continue;
						}

						if (isset($collectiveOrder->offer_id) && !empty($collectiveOrder->offer_id))
						{
							$collectiveOrder->offer_id[] = $offer->offer_id;
						}
						else
						{
							$collectiveOrder->offer_id = array($offer->offer_id);
						}

						$collectiveOrder->items = array_merge($collectiveOrder->items, $offer->items);

						foreach ($valuesToAdd AS $valueToAdd)
						{
							if (isset($collectiveOrder->{$valueToAdd}) && isset($offer->{$valueToAdd}))
							{
								$collectiveOrder->{$valueToAdd} += $offer->{$valueToAdd};
							}
						}
					}
				}

				// Store orders
				if (!empty($collectiveOrder))
				{
					$orderId = (int) RedshopbHelperOrder::storeOrder($collectiveOrder, $oneCustomerOrder);

					if ($orderId != 0)
					{
						$orderIds[] = $orderId;
					}
				}

				// Store delayed order if relevant
				if (!empty($regularCustomerDelayedOrder->items))
				{
					$app->input->set('shipping_date', $app->getUserState('checkout.shipping_date_delay'));
					$app->input->set('payment_name', $app->getUserState('checkout.payment_delay_name'));
					$app->input->set('shipping_rate_id', $app->getUserState('checkout.shipping_rate_id_delay'));
					$app->setUserState('checkout.pickup_stockroom_id', $app->getUserState('checkout.pickup_stockroom_id_delay'));

					$orderId = (int) RedshopbHelperOrder::storeOrder(array($regularCustomerDelayedOrde), $oneCustomerOrder);

					if ($orderId != 0)
					{
						$orderIds[] = $orderId;
					}
				}
			}
		}

		return $orderIds;
	}

	/**
	 * Remove product accessories from Cart
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function ajaxRemoveAccessories()
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$accessories  = $input->get('accessories', array(), 'array');
		$hash         = $input->getString('cartItemHash', '');
		$customerId   = $input->getUserState('shop.customer_id',  0);
		$customerType = $input->getUserState('shop.customer_type', '');

		/** @var   RedshopbModelShop $model */
		$model = RModelAdmin::getInstance('Shop', 'RedshopbModel');

		foreach ($accessories as $quantity)
		{
			if ($quantity <= 0)
			{
				$model->removeFromCart($hash, $customerId, $customerType);
			}
			else
			{
				$model->updateCartProductQuantity($hash, $customerId, $customerType, $quantity);
			}
		}

		$app->close();
	}

	/**
	 * Function to set customer as a Company
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function sobcompany()
	{
		$app        = Factory::getApplication();
		$customerId = $app->getUserStateFromRequest('list.company_id', 'company_id', 0, 'int');

		$app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');
		$app->getUserStateFromRequest('list.rsbuser_id', 'rsbuser_id', 0, 'int');
		$app->setUserState('shop.customer_id', $customerId);
		$app->setUserState('shop.customer_type', 'company');
		$app->setUserState('shop.campaignItems', null);

		$this->doRedirect($app);
	}

	/**
	 * Method to check redirect to a return URL if it exists or to the default list route
	 *
	 * @param   CMSApplication  $app  The application used to redirect
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function doRedirect($app)
	{
		$returnUrl = $app->input->getBase64('return', null);

		if (!empty($returnUrl))
		{
			$this->setRedirect(base64_decode($returnUrl));
			$this->redirect();
		}

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
		$this->redirect();
	}

	/**
	 * Function to set customer as a Department
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function sobdepartment()
	{
		$app        = Factory::getApplication();
		$customerId = $app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');

		$app->getUserStateFromRequest('list.company_id', 'company_id', 0, 'int');
		$app->getUserStateFromRequest('list.rsbuser_id', 'rsbuser_id', 0, 'int');
		$app->setUserState('shop.customer_id', $customerId);
		$app->setUserState('shop.customer_type', 'department');
		$app->setUserState('shop.campaignItems', null);

		$this->doRedirect($app);
	}

	/**
	 * Function to set customer as a Employee
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function sobemployee()
	{
		$app        = Factory::getApplication();
		$customerId = $app->getUserStateFromRequest('list.rsbuser_id', 'rsbuser_id', 0, 'int');
		$wallet     = RedshopbHelperWallet::getUserWalletId($customerId);

		$app->getUserStateFromRequest('list.company_id', 'company_id', 0, 'int');
		$app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');

		if (empty($wallet))
		{
			$this->doRedirect($app);
		}

		$app->setUserState('shop.customer_id', $customerId);
		$app->setUserState('shop.customer_type', 'employee');
		$app->setUserState('shop.campaignItems', null);

		$this->doRedirect($app);
	}

	/**
	 * Get collection product items.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetCollectionItems()
	{
		$app   = Factory::getApplication();
		$user  = RedshopbHelperCommon::getUser();
		$input = $app->input;

		$collectionId = $input->getInt('collectionId', 0);
		$start        = $input->getInt('start', 0);
		$limit        = $input->getInt('limit', 0);
		$onSale       = $input->getInt('onSale', 0);
		$search       = $input->getString('search', '');
		$category     = $input->getInt('category', 0);
		$flatDisplay  = $input->getString('flat_display', '');
		$collection   = $input->getInt('collection', 0);
		$filters      = $input->get('filter', array(), 'array');

		if ($user->b2cMode)
		{
			$placeOrderPermission = true;
		}
		else
		{
			$placeOrderPermission = RedshopbHelperACL::getPermission('place', 'order');
		}

		echo RedshopbHelperCollection::getShopCollectionProducts(
			$placeOrderPermission, $collectionId, $start, $limit, $onSale, $search, $category, $flatDisplay, $collection, $filters
		);

		$app->close();
	}

	/**
	 * Get was and care modal
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxWashAndCare()
	{
		$app        = Factory::getApplication();
		$productId  = $app->input->get('productId', 0, 'int');
		$flatAttrId = $app->input->getInt('flatAttrId', 0);
		$model      = $this->getModel('shop');
		$logos      = $this->getModel('logos');
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);

		$logos->setState('type', array('Ecotex', 'EUFlower'));
		$query->select(
			array(
				'pav.*',
				'pa.product_id',
				'pa.type_id',
				'pd.description_intro',
				'pd.description',
			)
		)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = pa.product_id')
			->leftJoin($db->qn('#__redshopb_product_descriptions', 'pd') . ' ON pd.product_id = p.id AND pd.main_attribute_value_id IS NULL')
			->where($db->qn('pav.id') . ' = ' . (int) $flatAttrId)
			->where($db->qn('pa.product_id') . ' = ' . (int) $productId);
		$dropdown = $db->setQuery($query)->loadObject();

		$query->clear()
			->select('pc.*')
			->from($db->qn('#__redshopb_product_composition', 'pc'))
			->where('pc.product_id = ' . (int) $productId)
			->where('(pc.flat_attribute_value_id = ' . (int) $flatAttrId . ' OR pc.flat_attribute_value_id IS NULL)')
			->order('pc.flat_attribute_value_id DESC');
		$compositions = $db->setQuery($query)->loadObjectList();

		$text = RedshopbHelperCollection::getProductItemValueFromType($dropdown->type_id, $dropdown, true);

		echo RedshopbLayoutHelper::render('shop.wash', array(
				'washProductItem'  => $model->getWashProduct($productId, $flatAttrId),
				'items'             => $model->getWash($productId),
				'productDropdown'   => $text,
				'compositions'      => $compositions,
				'productId'         => $productId,
				'flatAttrId'        => $flatAttrId,
				'logos'             => $logos->getItems(),
				'imageWidth'        => 450,
				'imageHeight'       => 450,
				'wAcInfoWidth'      => 50,
				'wAcInfoHeight'     => 50,
				'logosWidth'        => 150,
				'logosHeight'       => 80,
				'quality'           => 100,
				'imageBigWidth'     => 1500,
				'imageBigHeight'    => 1500,
				'description_intro' => $dropdown->description_intro,
				'description'       => RedshopbHelperProduct::getProductDescription($dropdown->description)
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for refreshing cart page upon change.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetZoomImage()
	{
		$app        = Factory::getApplication();
		$mediaId    = $app->input->get('mediaId', 0, 'int');
		$productId  = $app->input->get('productId', 0, 'int');
		$flatAttrId = $app->input->getInt('flatAttrId', 0);

		echo RedshopbLayoutHelper::render('shop.zoomimage', array(
				'productId'      => $productId,
				'flatAttrId'     => $flatAttrId,
				'mediaId'        => $mediaId,
				'imageWidth'     => 450,
				'imageHeight'    => 450,
				'quality'        => 100,
				'imageBigWidth'  => 1500,
				'imageBigHeight' => 1500
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for refreshing cart page upon change.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxRefreshCart()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('Shop');

		$app->input->set('view', 'shop');

		echo RedshopbLayoutHelper::render(
			'checkout.customer_basket',
			array(
				'state'          => $model->getState(),
				'customerOrders' => $model->getCustomerOrders(),
				'form'           => $model->getCustomForm('cartitems'),
				'showStockAs'    => $model->getStockVisibility(),
				'showToolbar'    => false,
				'total'          => false,
				'quantityfield'  => 'quantity',
				'checkbox'       => false
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for refreshing drop down attributes in shop filters.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxRefreshDropDowns()
	{
		$app          = Factory::getApplication();
		$collectionId = $app->input->get('collection_id', 0, 'int');
		$model        = $this->getModel();
		$form         = $model->getForm();
		$fields       = $form->getFieldset();
		$field        = $fields['filter_attribute_flat_display'];
		$value        = $app->getUserState('shop.dropdowns_current_state');

		if ($collectionId)
		{
			$field->__set('collection_id', $collectionId);
		}

		$field->setValue($value);

		echo $field->renderField();
		$app->close();
	}

	/**
	 * Ajax function for getting companies page in shop view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function ajaxGetCompaniesPage()
	{
		$app                 = Factory::getApplication();
		$filterShopName      = $app->getUserState('filter.shopname', '');
		$companyId           = $app->getUserState('shop.company_id', 0);
		$userId              = Factory::getUser()->id;
		$page                = $app->input->get('page', 0, 'int');
		$numberOfPages       = $app->input->get('noPages', 0, 'int');
		$config              = RedshopbApp::getConfig();
		$companiesPerPage    = $config->getInt('shop_companies_per_page', 12);
		$start               = ($page - 1) * $companiesPerPage;
		$companies           = null;
		$subCompaniesCount   = 0;
		$subDepartmentsCount = 0;
		$subEmployeesCount   = 0;

		if ($config->getInt('impersonation_company', 1))
		{
			$companies = RedshopbHelperACL::listAvailableCompanies(
				$userId,
				'objectList',
				$filterShopName ? 0 : $companyId,
				'',
				'redshopb.company.view',
				$filterShopName ? $filterShopName : '',
				false,
				false,
				false,
				true,
				$start,
				$companiesPerPage
			);

			if ($companies)
			{
				$ids = array();

				foreach ($companies as $company)
				{
					$ids[] = $company->id;
				}

				$subCompaniesCount = RedshopbHelperCompany::getSubCompaniesCount($ids, true, false);

				if ($config->getInt('impersonation_department', 1))
				{
					$subDepartmentsCount = RedshopbHelperCompany::getDepartmentsCount($ids, true, false);
				}

				if ($config->getInt('impersonation_user', 1))
				{
					$subEmployeesCount = RedshopbHelperCompany::getEmployeesCount($ids, true, false);
				}
			}
		}

		echo RedshopbLayoutHelper::render('shop.pages.companies',
			array(
				"companies"           => $companies,
				"currentCompanyId"    => $companyId,
				"showPagination"      => true,
				"numberOfPages"       => $numberOfPages,
				"currentPage"         => $page,
				"ajaxJS"              => "JAjaxCompaniesPageUpdate(event)",
				"subCompaniesCount"   => $subCompaniesCount,
				"subDepartmentsCount" => $subDepartmentsCount,
				"subEmployeesCount"   => $subEmployeesCount,
				"returnUrl"           => null
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for getting departments page in shop view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetDepartmentsPage()
	{
		$app                = Factory::getApplication();
		$filterShopName     = $app->getUserState('filter.shopname', '');
		$companyId          = $app->getUserState('shop.company_id', 0);
		$departmentId       = $app->getUserState('shop.department_id', 0);
		$userId             = Factory::getUser()->id;
		$page               = $app->input->get('page', 0, 'int');
		$numberOfPages      = $app->input->get('noPages', 0, 'int');
		$config             = RedshopbApp::getConfig();
		$departmentsPerPage = $config->getInt('shop_departments_per_page', 12);
		$start              = ($page - 1) * $departmentsPerPage;
		$departments        = null;

		if ($config->getInt('impersonation_department', 1))
		{
			$departments = RedshopbHelperACL::listAvailableDepartments(
				$userId,
				'objectList',
				$filterShopName ? 0 : $companyId,
				false,
				$filterShopName ? 0 : $departmentId,
				'',
				'redshopb.department.view',
				$filterShopName ? $filterShopName : '',
				$start,
				$departmentsPerPage
			);
		}

		echo RedshopbLayoutHelper::render('shop.pages.departments',
			array(
				"departments"         => $departments,
				"currentDepartmentId" => $departmentId,
				"showPagination"      => true,
				"numberOfPages"       => $numberOfPages,
				"currentPage"         => $page,
				"ajaxJS"              => "JAjaxDepartmentsPageUpdate(event)"
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for getting employees page in shop view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetEmployeesPage()
	{
		$app              = Factory::getApplication();
		$filterShopName   = $app->getUserState('filter.shopname', '');
		$companyId        = $app->getUserState('shop.company_id', 0);
		$departmentId     = $app->getUserState('shop.department_id', 0);
		$employeeId       = $app->getUserState('shop.employee_id');
		$page             = $app->input->get('page', 0, 'int');
		$numberOfPages    = $app->input->get('noPages', 0, 'int');
		$config           = RedshopbApp::getConfig();
		$employeesPerPage = $config->getInt('shop_employees_per_page', 12);
		$start            = ($page - 1) * $employeesPerPage;
		$employees        = null;

		if ($config->getInt('impersonation_user', 1))
		{
			$employees = RedshopbHelperACL::listAvailableEmployees(
				$filterShopName ? 0 : $companyId,
				$filterShopName ? 0 : $departmentId,
				'objectList',
				'',
				$filterShopName ? $filterShopName : '',
				$start,
				$employeesPerPage
			);
		}

		echo RedshopbLayoutHelper::render('shop.pages.employees',
			array(
				"employees"         => $employees,
				"currentEmployeeId" => $employeeId,
				"showPagination"    => true,
				"numberOfPages"     => $numberOfPages,
				"currentPage"       => $page,
				"ajaxJS"            => "JAjaxEmployeesPageUpdate(event)"
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for getting categories page in shop view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetCategoriesPage()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app           = Factory::getApplication();
		$return        = $this->getReturnObject();
		$customerId    = $app->getUserState('shop.customer_id', 0);
		$customerType  = $app->getUserState('shop.customer_type', '');
		$companyId     = $app->getUserState('shop.company_id', 0);
		$page          = $app->input->getInt('page', 0);
		$numberOfPages = $app->input->getInt('noPages', 0);
		$categoryId    = $app->input->getInt('id', 1);
		$collections   = RedshopbHelperCollection::getCustomerCollectionsForShop($customerId, $customerType);
		$start         = 0;
		$perPage       = 10;

		// Is it categories or category?
		$jInput = $app->input;
		$layout = $jInput->get('layout');

		if ($layout == 'categories')
		{
			$perPage = $this->categoriesPerPage;

			$start = ($page - 1) * $perPage;
			$start = ($start < 0) ? 0 : $start;
		}
		elseif ($layout == 'category')
		{
			$perPage = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);
			$start   = ($page - 1) * $perPage;
		}

		$return->html = RedshopbLayoutHelper::render('shop.pages.categories',
			array(
				'showPagination' => true,
				'numberOfPages'  => $numberOfPages,
				'currentPage'    => $page,
				'ajaxJS'         => 'redSHOPB.shop.updateCategoriesPage(event);',
				'categories'     => RedshopbHelperCategory::getCustomerCategories(
					$categoryId, $collections, $companyId, 'objectList', $start, $perPage, true, 0, (boolean) $this->requireTopLevel, $layout
				)
			)
		);

		echo json_encode($return);
		$app->close();
	}

	/**
	 * Ajax function for getting category products page in shop view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetProductListPage()
	{
		$app    = Factory::getApplication();
		$input  = $app->input;
		$id     = $input->getInt('id', 0);
		$layout = $input->getCmd('layout', $app->getUserState('shop.layout', ''));

		if ($layout)
		{
			$itemKey = $layout . '_' . $id;
		}
		else
		{
			$itemKey = $app->input->get('category_id', 0, 'int');
		}

		$collections  = $input->get('collection_id', '', 'string');
		$collectionId = $input->getInt('collection', 0);
		$page         = $app->getUserStateFromRequest('shop.productlist.page.' . $itemKey, 'page', 1, 'int');
		$config       = RedshopbEntityConfig::getInstance();
		$showAs       = $input->getString(
			'show_as',
			$app->getUserState(
				'shop.show.' . $layout . '.ProductsAs',
				$config->get('show_products_as', 'list')
			)
		);

		// Set shop layout
		$app->setUserState('shop.show.' . $layout . '.ProductsAs', $showAs);

		$productsPerPage    = $input->getInt('product_category_limit', $app->getUserState('shop.productLimit', $this->productsPerPage));
		$start              = ($page - 1) * $productsPerPage;
		$start              = ($start < 0) ? 0 : $start;
		$collectionProducts = array();
		$productSearch      = new RedshopbDatabaseProductsearch;
		$collectionIds      = empty($collections) ? array() : explode(',', $collections);
		$productsCount      = 0;
		$maxProductCount    = 0;
		$numberOfPages      = $input->get('noPages', 0, 'int');
		$user               = RedshopbHelperCommon::getUser();

		if (!$collectionId && !empty($collectionIds))
		{
			$collectionId = (int) $collectionIds[0];
		}

		if (!empty($collectionIds))
		{
			foreach ($collectionIds as $cid)
			{
				$products = $productSearch->getProductForProductListLayout($start, $productsPerPage, $cid);
				$count    = $productSearch->getProductCount($collectionId);

				// Set collection products
				$collectionProducts[$cid] = $products;

				if ($count > 0)
				{
					$productsCount += $count;

					// Taking max count from collection products for pagination
					if ($count > $maxProductCount)
					{
						$maxProductCount = $count;
					}
				}
			}
		}
		else
		{
			// Set collection products
			$collectionProducts[0] = $productSearch->getProductForProductListLayout($start, $productsPerPage);
		}

		if (!$numberOfPages && $maxProductCount > 0)
		{
			$numberOfPages = ceil($maxProductCount / $productsPerPage);
		}

		$extThis                       = new stdClass;
		$extThis->dropDownTypes        = array();
		$extThis->staticTypes          = array();
		$extThis->collectionProducts   = $collectionProducts;
		$extThis->collectionId         = $collectionId;
		$extThis->placeOrderPermission = ($user->b2cMode || RedshopbHelperACL::getPermission('place', 'order'));

		$jsonReturn       = new stdClass;
		$jsonReturn->html = RedshopbHelperTemplate::renderTemplate('product-list-collection', 'shop', null,
			array(
				"collectionProducts" => $collectionProducts,
				"showPagination"     => true,
				"numberOfPages"      => $numberOfPages,
				"currentPage"        => $page,
				"ajaxJS"             => 'redSHOPB.shop.updatePage(event, ' . (int) RedshopbApp::isUseAjaxReadMorePagination() . ');',
				"showAs"             => $showAs,
				"collectionId"       => $collectionId,
				'extThis'            => $extThis
			)
		);

		$paginationVariables = $app->getUserState('shop.pagination.variables.' . $itemKey, array());
		$url                 = 'index.php?option=com_redshopb&view=shop&layout=' . $layout;

		if ($id > 1)
		{
			$url .= '&id=' . $id;
		}

		if (!empty($paginationVariables))
		{
			foreach ($paginationVariables as $paginationVariableName => $paginationVariableNameValue)
			{
				$url .= '&' . $paginationVariableName . '=' . $paginationVariableNameValue;
			}
		}

		if ($page > 1)
		{
			$url .= '&page=' . $page;
		}

		if (!RedshopbApp::isUseAjaxReadMorePagination())
		{
			$jsonReturn->urlPath = RedshopbRoute::_($url, false);
		}

		if ($collectionId)
		{
			$jsonReturn->collectionId = $collectionId;
		}

		if (RedshopbApp::isUseAjaxReadMorePagination())
		{
			$app->setUserState('shop.productlist.page.' . $itemKey, 1);
		}

		$json = json_encode($jsonReturn);

		if ($json === false)
		{
			if (json_last_error() == JSON_ERROR_UTF8)
			{
				echo json_encode($this->utf8ize($jsonReturn));
			}
		}
		else
		{
			echo $json;
		}

		$app->close();
	}

	/**
	 * Display categories via AJAX
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxDisplayCategories()
	{
		$document   = Factory::getDocument();
		$viewType   = $document->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$view       = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
		$model      = $this->getModel($viewName);

		// Get/Create the model
		if ($model)
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		echo $view->display();

		Factory::getApplication()->close();
	}

	/**
	 * Print products list.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function ajaxSetUpPrintList()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('shop');
		$uComp = RedshopbHelperUser::getUserCompany();
		$main  = RedshopbApp::getMainCompany();
		$json  = array('showModal' => 0);

		if (null === $uComp || $main->get('id') == $uComp->id)
		{
			$form     = $model->getForm();
			$fieldset = $form->getFieldset('printProductList');
			$html     = '';

			foreach ($fieldset as $field)
			{
				$html .= $field->renderField();
			}

			$json['html']      = $html;
			$json['showModal'] = 1;
		}
		else
		{
			$language     = Factory::getUser()->getParam('language', 'da-DK');
			$currency     = $uComp->currency_id;
			$html         = '<input id="Form_currency_id" name="Form[currency_id]" type="hidden" value="' . $currency . '" />';
			$html        .= '<input id="Form_language" name="Form[language]" type="hidden" value="' . $language . '" />';
			$json['html'] = $html;
		}

		echo json_encode($json);

		$app->close();
	}

	/**
	 * Print product list pdf file.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxPrintProductsList()
	{
		// Increase timeout for products list
		set_time_limit(360);

		$app       = Factory::getApplication();
		$model     = $this->getModel();
		$language  = $app->input->get('list_language', '', 'string');
		$currency  = $app->input->getInt('list_currency', 38);
		$showStock = $app->input->getInt('showStock', 0) > 0;

		// Setting model state
		$model->setState('filter.onsale', $app->input->get('onSale', null, 'int'));
		$model->setState('filter.search_shop_products', $app->input->get('search', null, 'string'));
		$model->setState('filter.product_tag', $app->input->get('tag', null, 'array'));
		$model->setState('filter.product_category_Brand', $app->input->get('brand', null, 'array'));
		$model->setState('filter.product_category_Products', $app->input->get('categoryProducts', null, 'array'));
		$model->setState('filter.product_category_SubBrand', $app->input->get('subBrand', null, 'array'));
		$model->setState('filter.attribute_flat_display', $app->input->get('color', null, 'string'));

		// Language selection
		if ($language == '')
		{
			$company  = RedshopbHelperUser::getUserCompany();
			$language = Factory::getLanguage()->getTag();

			if (!empty($company))
			{
				$language = $company->site_language;
			}
		}

		$db                           = Factory::getDbo();
		$db->forceLanguageTranslation = $language;

		// Load common and local language files
		$lang = Factory::getLanguage();

		if ($lang->getTag() != $language)
		{
			$lang->setLanguage($language);

			// Load language file
			$lang->load('com_redshopb', JPATH_SITE, $language, true, true)
			|| $lang->load('com_redshopb', JPATH_SITE . "/components/com_redshopb", $language, true, true);
		}

		$products = $model->getPrintProductsList($showStock, $currency);

		if (!is_null($model->getState('filter.attribute_flat_display')))
		{
			RedshopbHelperShop::generateProductListPDF($products, false);
		}
		else
		{
			RedshopbHelperShop::generateProductListPDF($products, true);
		}

		$app->close();
	}

	/**
	 * redSHOPB2B get description of an attribute
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function ajaxAttributeDescription()
	{
		$app        = Factory::getApplication();
		$productId  = $app->input->getInt('productId', 0);
		$flatAttrId = $app->input->getInt('flatAttrId', 0);

		if (!$productId || !$flatAttrId)
		{
			echo json_encode(array());
			$app->close();
		}

		$description = RedshopbHelperProduct_Attribute::getAttributeDescription($productId, $flatAttrId);

		if (false === $description)
		{
			echo json_encode(array());
			$app->close();
		}

		echo json_encode($description);

		$app->close();
	}

	/**
	 * Method to load the filters form
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxGetFilters()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$response = new RedshopbAjaxResponse;
		$response->setBody($this->getFilterModuleHtml());

		echo json_encode($response);
		Factory::getApplication()->close();
	}

	/**
	 * Renders the product variant modal
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 2.1.1
	 */
	public function ajaxGetProductVariants()
	{
		$app = Factory::getApplication();

		$post = $app->input->post;

		$product = RedshopbEntityProduct::getInstance($post->getInt('id'));

		/** @var RedshopbModelProduct $productModel */
		$productModel = RedshopbModel::getAutoInstance('Product');

		/** @var RedshopbModelShop $shopModel */
		$shopModel = RedshopbModel::getAutoInstance('Shop');

		$extThis                       = new stdClass;
		$extThis->product              = $shopModel->prepareItemsForShopView(
			array($product->getItem()),
			$post->getInt('customerId'),
			$post->getString('customerType'),
			$post->getInt('collectionId')
		);
		$extThis->productAttributes    = $productModel->getAttributes($product->getId(), array(), true, false);
		$extThis->placeOrderPermission = $post->getBool('placeOrderPermission');

		$products                = new stdClass;
		$products->productImages = unserialize(gzuncompress(base64_decode($post->getBase64('images'))));

		$displayData = array(
			'productId'          => $product->getId(),
			'product'            => $product->getItem(),
			'price'              => unserialize(gzuncompress(base64_decode($post->getBase64('price')))),
			'currency'           => $post->getString('currency'),
			'collectionId'       => $post->getInt('collectionId'),
			'volumePricingClass' => $post->getString('volumePricingClass'),
			'cartPrefix'         => $post->getString('cartPrefix'),
			'extThis'            => $extThis,
			'products'           => $products,
			'width'              => $post->getInt('imageWidth'),
			'height'             => $post->getInt('imageHeight'),
			'productLink'        => $post->getString('link'),
			'isShop'             => true,
		);

		$data = array(
			'productId' => $product->getId(),
			'body' => RedshopbHelperTemplate::render('templates.product-variants.product-variants', $displayData)
		);

		$response = new RedshopbAjaxResponse;
		$response->setData($data);
		echo json_encode($response);
		$app->close();
	}

	/**
	 * Method to load a module HTML
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getFilterModuleHtml()
	{
		// Get module parameters
		jimport('joomla.application.module.helper');
		Factory::getApplication()->input->set('lazyloaded', true);

		$module                   = $this->getModuleById();
		$params                   = json_decode($module->params, true);
		$params['loadedFromAjax'] = 1;

		return ModuleHelper::renderModule($module, $params);
	}

	/**
	 * Get module by id
	 *
	 * @return  stdClass
	 *
	 * @throws  ErrorException
	 *
	 * @since   1.0.0
	 */
	private function getModuleById()
	{
		$moduleId = Factory::getApplication()->input->getInt('modId', 0);

		if (empty($moduleId))
		{
			return ModuleHelper::getModule('redshopb_filter');
		}

		$modules = ModuleHelper::getModuleList();

		foreach ($modules AS $module)
		{
			if ($module->id != $moduleId)
			{
				continue;
			}

			if ($module->module != 'mod_redshopb_filter')
			{
				throw new ErrorException(Text::_('COM_REDSHOPB_ERROR_INVALID_MODULE_ID'));
			}

			return $module;
		}
	}

	/**
	 * Method for run ajax filter.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxFilter()
	{
		$app      = Factory::getApplication();
		$input    = $app->input;
		$view     = $input->getCmd('view', 'shop');
		$layout   = $input->getCmd('layout', $app->getUserState('shop.layout', ''));
		$location = 'index.php?option=com_redshopb&view=' . $view . '&layout=' . $layout;
		$id       = $input->getInt('id', 0);

		if ($id !== 0)
		{
			$location .= '&id=' . $id;
		}

		$itemKey      = $layout . '_' . $id;
		$filterSearch = $this->prepareSearch($itemKey);

		if (!empty($filterSearch))
		{
			$location .= '&search=' . $filterSearch;
		}

		$location           .= '&Itemid=' . $input->getCmd('Itemid', '101');
		$filterUrl           = array();
		$filterCategory      = $input->get('filter_category', array(), 'array');
		$filterManufacturers = array_filter((array) $input->get('filter_manufacturer', array(), 'array'));
		$filterTag           = $input->get('filter_tag', array(), 'array');
		$filterCampaign      = $input->getInt('filter_campaign_price', 0);
		$filterPrice         = $input->getString('filter_price', '');
		$filterStock         = $input->getInt('filter_stock', 0);
		$filterFieldsets     = $input->get('filter', array(), 'array');
		$filterAttributes    = $input->get('filter_attribute', array(), 'array');
		$config              = RedshopbEntityConfig::getInstance();
		$page                = $input->get('page', 1, 'int');
		$numberOfPages       = $input->get('noPages', 0, 'int');
		$collectionId        = $input->getString('collection_id', '');
		$collectionIds       = explode(',', $collectionId);
		$showAs              = $input->getString(
			'show_as',
			$app->getUserState('shop.show.' . $layout . '.ProductsAs', $config->get('show_products_as', 'list'))
		);

		if (!empty($filterCategory))
		{
			$filteredCategoriesIds = array_filter($filterCategory);

			if (!empty($filteredCategoriesIds))
			{
				$filterUrl[] = 'c[' . implode(':', $filteredCategoriesIds) . ']';
			}
		}

		if (!empty($filterManufacturers))
		{
			$filterUrl[] = 'b[' . implode(':', $filterManufacturers) . ']';
		}

		if (!empty($filterCampaign))
		{
			$filterUrl[] = 'cp[' . $filterCampaign . ']';
		}

		if (!empty($filterPrice))
		{
			$filterUrl[] = 'p[' . $filterPrice . ']';
		}

		if (!empty($filterStock))
		{
			$filterUrl[] = 's[' . $filterStock . ']';
		}

		if (!empty($filterTag))
		{
			$filterUrl[] = 't[' . implode(':', (array) $filterTag) . ']';
		}

		if (!empty($filterAttributes))
		{
			foreach ($filterAttributes as $attribute => $values)
			{
				$filterUrl[] = 'a-' . $attribute . '[' . implode(':', (array) $values) . ']';
			}
		}

		switch ($layout)
		{
			case 'manufacturer':
				$filterManufacturers = $id;
				break;
		}

		$oldCategories = $app->getUserState('shop.categoryfilter.' . $itemKey, array());
		$app->setUserState('shop.categoryfilter.' . $itemKey, $filterCategory);
		$app->setUserState('mod_filter.search.' . $itemKey, $filterSearch);
		$app->setUserState('shop.tag.' . $itemKey, $filterTag);
		$app->setUserState('shop.campaign_price.' . $itemKey, $filterCampaign);
		$app->setUserState('shop.price_range.' . $itemKey, $filterPrice);
		$app->setUserState('shop.in_stock.' . $itemKey, $filterStock);
		$app->setUserState('shop.manufacturer.' . $itemKey, $filterManufacturers);
		$app->setUserState('shop.productlist.page.' . $itemKey, $page);

		if (!RedshopbApp::isUseAjaxReadMorePagination())
		{
			$app->setUserState('shop.productlist.page.' . $itemKey, $page);
		}

		$app->setUserState('shop.attributefilter.' . $itemKey, $filterAttributes);
		$isMultipleCategories  = (count($filterCategory) > 1);
		$isDifferentCategories = (json_encode($filterCategory) !== json_encode($oldCategories));

		if ($isMultipleCategories
			|| $isDifferentCategories)
		{
			$groupedFields = RedshopbHelperField::getFields('product');
			$allFields     = array();

			// We collect all fields from all scopes if more than one scope is provided
			foreach ($groupedFields as $scopeName => $fieldsInScope)
			{
				foreach ($fieldsInScope as $field)
				{
					$allFields[] = $field;
				}
			}

			if (!empty($allFields))
			{
				foreach ($allFields as $field)
				{
					RedshopbHelperFilter::setFilterDataToSession($field->id, null, 'filter.' . $itemKey);
				}
			}
		}
		else
		{
			// Set filter data values in case client remove some filter.
			foreach ($filterFieldsets as $filter => $filterData)
			{
				if (!empty($filterData))
				{
					if (is_array($filterData))
					{
						$filterData = RedshopbHelperFilter::filterFields($filterData);

						if (!empty($filterData))
						{
							$filterUrl[] = $filter . '[' . implode(':', $filterData) . ']';
						}
					}
					else
					{
						$filterUrl[] = $filter . '[' . $filterData . ']';
					}
				}

				RedshopbHelperFilter::setFilterDataToSession($filter, $filterData, 'filter.' . $itemKey);
			}
		}

		$productsPerPage = $input->getInt('product_category_limit', $app->getUserState('shop.productLimit', $this->productsPerPage));
		$start           = ($page - 1) * $productsPerPage;
		$start           = ($start < 0) ? 0 : $start;
		$productSearch   = new RedshopbDatabaseProductsearch(array('itemKey' => $itemKey));

		try
		{
			$collectionProducts = array();
			$productsCount      = 0;
			$maxProductCount    = 0;
			$collectionId       = $collectionIds[0];
			$user               = RedshopbHelperCommon::getUser();

			// Get products list.
			if (!empty($collectionIds))
			{
				foreach ($collectionIds as $cid)
				{
					$cid      = (int) $cid;
					$products = $productSearch->getProductForProductListLayout($start, $productsPerPage, $cid);
					$count    = $productSearch->getProductCount($cid);

					// Set collection products
					$collectionProducts[$cid] = $products;

					if ($count > 0)
					{
						$productsCount += $count;

						if ($count > $maxProductCount)
						{
							$maxProductCount = $count;
						}
					}
				}
			}

			if (!$numberOfPages && $maxProductCount > 0)
			{
				$numberOfPages = ceil($maxProductCount / $productsPerPage);
			}

			if (!$page)
			{
				$page = 1;
			}

			$extThis                       = new stdClass;
			$extThis->dropDownTypes        = array();
			$extThis->staticTypes          = array();
			$extThis->placeOrderPermission = ($user->b2cMode || RedshopbHelperACL::getPermission('place', 'order'));

			$html = RedshopbHelperTemplate::renderTemplate('product-list-collection', 'shop', null,
				array(
					"collectionProducts" => $collectionProducts,
					"showPagination"     => true,
					"numberOfPages"      => $numberOfPages,
					"currentPage"        => $page,
					"ajaxJS"             => "redSHOPB.shop.updatePage(event, " . (int) RedshopbApp::isUseAjaxReadMorePagination() . ");",
					"showAs"             => $showAs,
					"collectionId"       => $collectionId,
					'extThis'            => $extThis
				)
			);

			$moduleHtml = $this->getFilterModuleHtml();

			$result = array(
				'page'          => $page,
				'showAs'        => $showAs,
				'numberOfPages' => $numberOfPages,
				'html'          => $html,
				'moduleHtml'    => $moduleHtml,
				'productsCount' => $productsCount . ' ' . Text::_('COM_REDSHOPB_CATEGORY_NUMBER_OF_PRODUCTS'),
				'location'      => RedshopbRoute::_($location),
				'urlPath'       => RedshopbRoute::_(
					'index.php?option=com_redshopb&view=shop&layout=' . $layout
						. (!empty($id) ? '&id=' . $id : '')
						. (!empty($filterSearch) ? '&search=' . $filterSearch : '')
						. (!empty($filterUrl) ? '&f=' . implode('', $filterUrl) : '')
						. ($page > 1 ? '&page=' . $page : ''),
					false
				)
			);

			$paginationVariables = array();

			if (!empty($filterUrl))
			{
				$paginationVariables['f'] = implode('', $filterUrl);
			}

			if (!empty($filterSearch))
			{
				$paginationVariables['search'] = $filterSearch;
			}

			$app->setUserState('shop.pagination.variables.' . $itemKey, $paginationVariables);

			$json = json_encode($result);

			if ($json === false)
			{
				if (json_last_error() == JSON_ERROR_UTF8)
				{
					echo json_encode($this->utf8ize($result));
				}
				else
				{
					echo json_encode(array('message' => json_last_error(), 'messageType' => 'alert-error'));
				}
			}
			else
			{
				echo $json;
			}
		}
		catch (Exception $e)
		{
			echo json_encode(array('message' => $e->getMessage(), 'messageType' => 'alert-error'));
		}

		$app->close();
	}

	/**
	 * Convert strings to utf-8
	 *
	 * @param   array|string|object  $mixed  Variable for json
	 *
	 * @return  array|string|object
	 *
	 * @since   1.0.0
	 */
	protected function utf8ize($mixed)
	{
		if (is_array($mixed))
		{
			foreach ($mixed as $key => $value)
			{
				$mixed[$key] = $this->utf8ize($value);
			}
		}
		elseif (is_object($mixed))
		{
			foreach ($mixed as $key => $value)
			{
				$mixed->{$key} = $this->utf8ize($value);
			}
		}
		elseif (is_string($mixed))
		{
			return mb_convert_encoding($mixed, 'UTF-8', 'auto');
		}

		return $mixed;
	}


	/**
	 * Method to perform and ajax search
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function ajaxSearch()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app             = Factory::getApplication();
		$useSimpleSearch = $app->input->getBool('simple_search', false);
		$searchWord      = $this->prepareSearch('productlist_0');

		// Hard code relevance search for ajax
		$layout = 'ajax_search';
		$app->setUserState('shop.layout', $layout);
		$app->setUserState('shop.show.' . $layout . '.SortByDir', 'asc');
		$app->setUserState('shop.show.' . $layout . '.SortBy', 'relevance');

		$collectionId  = $app->input->get('collection_id', 0);
		$productSearch = new RedshopbDatabaseProductsearch(array('useSimpleSearch' => $useSimpleSearch));

		$isQuickOrder = !is_null($app->input->get('isQuickOrder')) ? $app->input->get('isQuickOrder') : false;
		$result       = $productSearch->getProductForProductListLayout(0, 10, $collectionId, $isQuickOrder);

		if (empty($result))
		{
			$result = new stdClass;
		}

		$result->categories = array();
		$layout             = $app->input->getCmd('result_layout', 'result-list');

		if ($layout == 'linked-list')
		{
			$result->categories = $productSearch->getCategories(0, 10);
		}

		$result->searchTerm = $searchWord;
		$result->count      = $productSearch->getProductCount($collectionId);

		if ($result->count == 0 && ($layout == 'linked-list' ? count($result->categories) == 0 : true))
		{
			$result->html = RedshopbLayoutHelper::render('shop.search.noresult', array('result' => $result));
		}
		else
		{
			$result->html = RedshopbLayoutHelper::render('shop.search.' . $layout, array('result' => $result));
		}

		$json = json_encode($result);

		if ($json === false)
		{
			if (json_last_error() == JSON_ERROR_UTF8)
			{
				echo json_encode($this->utf8ize($result));
			}
		}
		else
		{
			echo $json;
		}

		$app->close();
	}

	/**
	 * Method to prepare the application state for search
	 *
	 * @param   string  $itemKey     Item key
	 * @param   bool    $flushState  Flush state
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function prepareSearch($itemKey, $flushState = false)
	{
		$app        = Factory::getApplication();
		$input      = $app->input;
		$searchWord = trim(
			$input->getString(
				'filter_search',
				$input->getString(
					'search',
					''
				)
			)
		);

		$oldSearch = $app->getUserState('mod_filter.search.' . $itemKey, '');
		$app->setUserState('mod_filter.search.' . $itemKey, '');

		if ($searchWord != $oldSearch || $flushState)
		{
			$app->setUserState('shop.categoryfilter', null);
			$app->setUserState('shop.tag', null);
			$app->setUserState('shop.attributefilter', null);
			$app->setUserState('shop.campaign_price', null);
			$app->setUserState('shop.price_range', null);
			$app->setUserState('shop.in_stock', null);
			$app->setUserState('shop.manufacturer', null);

			$groupedFields = RedshopbHelperField::getFields('product');
			$allFields     = array();
			$registry      = Factory::getSession()->get('registry');

			// We collect all fields from all scopes if more than one scope is provided
			foreach ($groupedFields as $scopeName => $fieldsInScope)
			{
				foreach ($fieldsInScope as $field)
				{
					$allFields[] = $field;
				}
			}

			if (!empty($allFields))
			{
				foreach ($allFields as $field)
				{
					$registry->set('filter.' . $itemKey . '.' . $field->id, null);
				}
			}
		}

		if ($input->getInt('clean_search_field'))
		{
			$searchWord = null;
			$input->set('search', null);
			$input->set('filter_search', null);
		}

		$app->setUserState('mod_filter.search.' . $itemKey, $searchWord);

		return $searchWord;
	}

	/**
	 * Set search world after redirect in the url
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function search()
	{
		$app        = Factory::getApplication();
		$itemKey    = $app->getUserState('shop.itemKey', 0);
		$searchWord = $this->prepareSearch($itemKey, true);

		if ($itemKey != 'productlist_0')
		{
			$app->setUserState('mod_filter.search.' . $itemKey, null);
		}
		else
		{
			// Reset page number when start new search
			$app->setUserState('shop.productlist.page', null);
		}

		$user    = Factory::getUser();
		$userId  = $user->get('id');
		$session = Factory::getSession();

		$prevSearched = $session->get('prevSearched.' . $userId, '', 'redshopb');

		if ($prevSearched)
		{
			$searchWord = $prevSearched;

			$session->set('prevSearched.' . $userId, '', 'redshopb');
		}

		$app->setUserState('mod_filter.search.productlist_0', $searchWord);
		$this->setRedirect($this->getRedirectToListRoute('&layout=productlist&search=' . $searchWord));
		$this->redirect();
	}

	/**
	 * redSHOPB2B shop filter function.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function filter()
	{
		$app                 = Factory::getApplication();
		$input               = $app->input;
		$filterCategory      = $input->get_Array('filter_category', array());
		$filterManufacturers = $input->get_Array('filter_manufacturer', array());
		$filterSearch        = $input->getString('filter_search', $input->getString('mod_redshopb_search_searchword', ''));
		$filterTag           = $input->get_Array('filter_tag', array());
		$filterCampaign      = $input->getString('filter_campaign_price', 0);
		$filterPrice         = $input->getString('filter_price', '');
		$filterStock         = $input->getString('filter_stock', 0);
		$filterFieldsets     = $input->get('filter', array(), 'array');
		$filterAttributes    = $input->get_Array('filter_attribute', array());

		// Collect filter data
		$itemKey = $app->getUserState('shop.itemKey', 0);

		// Store filter data in session.
		$app->setUserState('shop.categoryfilter.' . $itemKey, $filterCategory);
		$app->setUserState('mod_filter.search.' . $itemKey, $filterSearch);
		$app->setUserState('shop.tag.' . $itemKey, $filterTag);
		$app->setUserState('shop.campaign_price.' . $itemKey, $filterCampaign);
		$app->setUserState('shop.price_range.' . $itemKey, $filterPrice);
		$app->setUserState('shop.manufacturer.' . $itemKey, $filterManufacturers);
		$app->setUserState('shop.in_stock.' . $itemKey, $filterStock);
		$app->setUserState('shop.attributefilter.' . $itemKey, $filterAttributes);

		if (!empty($filterFieldsets))
		{
			foreach ($filterFieldsets as $filter => $filterData)
			{
				RedshopbHelperFilter::setFilterDataToSession($filter, $filterData, 'filter.' . $itemKey);
			}
		}

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));

		$this->redirect();
	}

	/**
	 * Open category view for given category
	 *
	 * Redirect to category view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function showCategory()
	{
		$app       = Factory::getApplication();
		$config    = RedshopbEntityConfig::getInstance();
		$category  = $app->input->getInt('id', 0);
		$showAs    = $app->input->getString('show_as', $config->get('show_products_as', 'list'));
		$sortBy    = $app->input->getString('sort_by');
		$showCount = $app->input->getInt('product_category_limit', 12);
		$sortDir   = $app->input->getString('sort_dir', 'asc');

		$app->setUserState('shop.show.category.ProductsAs', $showAs);
		$app->setUserState('shop.show.category.SortBy', $sortBy);
		$app->setUserState('shop.show.category.SortByDir', $sortDir);
		$app->setUserState('shop.productLimit', $showCount);

		if (!empty($category))
		{
			$app->setUserState('shop.category', $category);
			$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=category&id=' . $category)));
		}
		else
		{
			$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
		}

		$this->redirect();
	}

	/**
	 * Open product view for given category
	 *
	 * Redirect to product layout.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function showProduct()
	{
		$app        = Factory::getApplication();
		$product    = $app->input->getInt('product', 0);
		$collection = $app->input->getInt('collection', 0);

		if (!empty($product))
		{
			$app->setUserState('shop.showProduct', $product);
			$app->setUserState('shop.showCollection', $collection);
			$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend('&layout=product&id=' . $product)));
		}
		else
		{
			$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
		}

		$this->redirect();
	}

	/**
	 * Ajax function for getting favorites window for product
	 *
	 * Favorites window HTML page
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function ajaxGetFavorites()
	{
		$app = Factory::getApplication();
		$db  = Factory::getDbo();

		$productId  = $app->input->get('product_id', 0, 'int');
		$customerId = $app->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');

		if (!$customerId
			|| $app->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string') != 'employee')
		{
			$app->close();
		}

		$query = $db->getQuery(true);
		$query->select($db->qn('favoritelist_id', 'id'))
			->from($db->qn('#__redshopb_favoritelist_product_xref'))
			->where($db->qn('product_id') . ' = ' . (int) $productId);
		$db->setQuery($query);
		$productLists = $db->loadColumn(0);

		/** @var RedshopbModelMyfavoritelists $favoriteListModel */
		$favoriteListModel = RModelAdmin::getInstance('Myfavoritelists', 'RedshopbModel');
		$favoriteListModel->setState('filter.user_id', $customerId);

		echo RedshopbLayoutHelper::render('myfavoritelists.productselection',
			array(
				'productId' => $productId,
				'lists' => $favoriteListModel->getItems(),
				'productLists' => $productLists
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for setting a product favorite
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function ajaxSetFavorite()
	{
		$app = Factory::getApplication();
		$db  = Factory::getDbo();

		$customerId = $app->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');

		if (!$customerId
			|| $app->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string') != 'employee')
		{
			$app->close();
		}

		$productId      = $app->input->get('product_id', 0, 'int');
		$favoritelistId = $app->input->get('favoritelist_id', 0, 'int');
		$added          = ($app->input->get('added', 'false', 'string') == 'true' ? true : false);
		$rowExists      = false;

		$query = $db->getQuery(true);
		$query->select($db->qn(array('fpx.product_id')))
			->from(($db->qn('#__redshopb_favoritelist_product_xref', 'fpx')))
			->join('inner', $db->qn('#__redshopb_favoritelist', 'f') . ' ON f.id = fpx.favoritelist_id')
			->where($db->qn('fpx.product_id') . ' = ' . (int) $productId)
			->where($db->qn('fpx.favoritelist_id') . ' = ' . (int) $favoritelistId)
			->where($db->qn('f.user_id') . ' = ' . (int) $customerId);
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$rowExists = true;
		}

		$queryExists = false;
		$query->clear();

		if ($added && !$rowExists)
		{
			$queryExists = true;
			$query->insert($db->qn('#__redshopb_favoritelist_product_xref'))
				->columns($db->qn(array('product_id', 'favoritelist_id')))
				->values($productId . ', ' . $favoritelistId);
		}
		elseif (!$added && $rowExists)
		{
			$queryExists = true;
			$query->delete($db->qn('#__redshopb_favoritelist_product_xref'))
				->where($db->qn('product_id') . ' = ' . (int) $productId)
				->where($db->qn('favoritelist_id') . ' = ' . (int) $favoritelistId);
		}

		if ($queryExists)
		{
			$db->setQuery($query);
			$db->execute();
		}

		$app->close();
	}

	/**
	 * Ajax function for creating a favorite list and adding a product as a favorite
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxCreateFavorite()
	{
		$app = Factory::getApplication();
		$db  = Factory::getDbo();

		$customerId = $app->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');

		if (!$customerId
			|| $app->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string') != 'employee')
		{
			$app->close();
		}

		$productId        = $app->input->get('product_id', 0, 'int');
		$favoritelistName = trim($app->input->get('favoritelist_name', '', 'string'));

		if ($favoritelistName != '')
		{
			$table = RedshopbTable::getAdminInstance('Myfavoritelist');
			$table->save(
				array(
					'name'    => $favoritelistName,
					'user_id' => $customerId
				)
			);

			$query = $db->getQuery(true)
				->insert($db->qn('#__redshopb_favoritelist_product_xref'))
				->columns($db->qn(array('product_id', 'favoritelist_id')))
				->values($productId . ', ' . $table->id);
			$db->setQuery($query);
			$db->execute();
		}

		$app->close();
	}

	/**
	 * Get Send To Friend Form
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function getSendToFriendForm()
	{
		$productId    = $this->input->getInt('id', 0);
		$categoryId   = $this->input->getInt('category_id', 0);
		$collectionId = $this->input->getInt('collection_id', 0);
		$isSend       = $this->input->getInt('send', 0);
		$model        = $this->getModel('Send_To_Friend');
		$form         = $model->getForm();

		// Set model context
		$model->set('context', 'com_redshopb.edit.product_send_friend.' . $categoryId . '_' . $productId . '_' . $collectionId);

		if (!$isSend)
		{
			echo RedshopbLayoutHelper::render('shop.sendtofriend',
				array(
					'productId'    => $productId,
					'form'         => $form,
					'categoryId'   => $categoryId,
					'collectionId' => $collectionId
				)
			);
		}
	}

	/**
	 * Send mail to friend
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function sendToFriend()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app          = Factory::getApplication();
		$data         = $this->input->post->get('Form', array(), 'array');
		$productId    = $this->input->getInt('id', 0);
		$categoryId   = $this->input->getInt('category_id', 0);
		$collectionId = $this->input->getInt('collection_id', 0);
		$context      = 'com_redshopb.edit.product_send_friend.' . $categoryId . '_' . $productId . '_' . $collectionId;
		$model        = $this->getModel('Send_To_Friend');
		$link         = 'index.php?option=com_redshopb&tmpl=component&task=shop.getSendToFriendForm&id='
			. $productId . '&category_id=' . $categoryId . '&collection_id=' . $collectionId;

		// Setting model context
		$model->set('context', $context);

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$this->setRedirect(RedshopbRoute::_($link, false), $model->getError(), 'error');

			return false;
		}

		$user = Factory::getUser();

		if (!$user->guest)
		{
			$form->removeField('captcha', null);
			$data['your_email'] = $user->email;
		}

		// Save the data in the session.
		$app->setUserState($context . '.data', $data);

		$validate = $model->validate($form, $data);

		if ($validate === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			$n      = count($errors);

			// Push up to three validation messages out to the user.
			for ($i = 0; $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
		}

		$data['product_id']    = $productId;
		$data['category_id']   = $categoryId;
		$data['collection_id'] = $collectionId;

		if ($model->sendMailForFriend($data))
		{
			// Flush the data from the session
			$app->setUserState($context . '.data', null);
			$app->enqueueMessage(Text::_('COM_REDSHOPB_SEND_TO_FRIEND_SUCCESSFULLY'));
			$link .= '&send=1';
		}
		else
		{
			$app->enqueueMessage($model->getError(), 'warning');
		}

		$this->setRedirect(RedshopbRoute::_($link, false));

		return true;
	}

	/**
	 * Update prices via ajax
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function ajaxUpdatePrices()
	{
		$app           = Factory::getApplication();
		$input         = $app->input;
		$productIds    = $input->get('product_ids', array(), 'array');
		$quantities    = $input->get('quantities', array(), 'array');
		$collectionIds = $input->get('collection_ids', array(), 'array');

		if (!is_array($productIds))
		{
			$productIds = array((int) $productIds);
		}

		if (!is_array($quantities))
		{
			$quantities = array((int) $quantities);
		}

		if (!is_array($collectionIds))
		{
			$collectionIds = array((int) $collectionIds);
		}

		$customerId      = $app->getUserState('shop.customer_id', 0);
		$customerType    = $app->getUserState('shop.customer_type', '');
		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		$company         = RedshopbEntityCompany::getInstance($customerCompany->id);
		$currency        = $company->getCustomerCurrency();
		$prices          = array();
		$selectedPrices  = array();

		foreach ($productIds AS $index => $id)
		{
			// Skip duplicate products
			if (array_key_exists($id, $selectedPrices))
			{
				continue;
			}

			$selectedPrices[$id] = true;
			$prices[$id]         = RedshopbHelperPrices::getProductPrice(
				$id,
				$customerId,
				$customerType,
				$currency,
				array($collectionIds[$index]),
				null,
				null,
				$quantities[$index],
				false,
				true
			);

			if (!empty($prices[$id]->price))
			{
				if ($prices[$id]->price > 0)
				{
					$prices[$id]->displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
						(float) $prices[$id]->price,
						$currency
					);
				}
				else
				{
					$prices[$id]->displayPrice = '';
				}

				$prices[$id]->quantity_min = (int) $prices[$id]->quantity_min;
				$prices[$id]->quantity_max = (int) $prices[$id]->quantity_max;
				$prices[$id]->format       = RedshopbHelperProduct::getCurrency($currency);
			}
		}

		echo json_encode($prices);
		$app->close();
	}

	/**
	 * Loads prices from a list of products
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function ajaxLoadExternalPrice()
	{
		$app             = Factory::getApplication();
		$return          = array();
		$prices          = array();
		$input           = $app->input;
		$productIds      = $input->get('product_ids', array(), 'array');
		$quantities      = $input->get('quantities', array(), 'array');
		$customerId      = $app->getUserState('shop.customer_id', 0);
		$customerType    = $app->getUserState('shop.customer_type', '');
		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
		$company         = RedshopbEntityCompany::getInstance($customerCompany->id);
		$currency        = $company->getCustomerCurrency();

		if (!is_array($productIds))
		{
			$productIds = array((int) $productIds);
		}

		if (!is_array($quantities))
		{
			$quantities = array((int) $quantities);
		}

		$productArray = array();

		foreach ($productIds as $i => $productId)
		{
			$hidePrice = false;
			$price     = 0;

			RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $productId));

			if ($hidePrice)
			{
				$return['P' . $productId]                = new stdClass;
				$return['P' . $productId]->originalPrice = 0;
				$return['P' . $productId]->price         = RedshopbHelperProduct::getProductFormattedPrice(0, $currency);
				$return['P' . $productId]->totalPrice    = 0;

				continue;
			}

			$quantity = 1;

			if (array_key_exists($i, $quantities))
			{
				$quantity = (int) $quantities[$i];
			}

			$productArray[(int) $productId] = $quantity;
		}

		if (!count($productArray))
		{
			echo json_encode($return);
			$app->close();
		}

		RFactory::getDispatcher()->trigger(
			'onRedshopbPriceLoad',
			array(
				$customerCompany->id,
				$productArray,
				&$prices
			)
		);

		$total = array('C' . $currency => 0);

		foreach ($prices as $productId => $price)
		{
			$return['P' . $productId] = new stdClass;

			if (!is_null($price))
			{
				$total['C' . $currency] += $price->price * $productArray[$productId];

				$return['P' . $productId]->price               = RedshopbHelperProduct::getProductFormattedPrice($price->price, $currency);
				$return['P' . $productId]->originalPrice       = $price->price;
				$return['P' . $productId]->totalPrice          = $price->price * $productArray[$productId];
				$return['P' . $productId]->totalPriceFormatted = RedshopbHelperProduct::getProductFormattedPrice(
					$price->price * $productArray[$productId],
					$currency
				);

				if (property_exists($price, 'unit_price'))
				{
					$unitMeasure = RedshopbEntityUnit_Measure::getInstanceByField('alias', $price->unit_measure_code);

					$return['P' . $productId]->originalUnitPrice = $price->unit_price;
					$return['P' . $productId]->unitPrice         = 'Pris pr. ' . $unitMeasure->get('name') . ': ' .
						RedshopbHelperProduct::getProductFormattedPrice($price->unit_price, $currency);
				}
			}
		}

		foreach ($total as $currency => $totalCurrency)
		{
			$total[$currency] = array(
				'total'          => $totalCurrency,
				'totalFormatted' => RedshopbHelperProduct::getProductFormattedPrice($totalCurrency, substr($currency, 1))
			);
		}

		$return['total'] = $total;

		echo json_encode($return);
		$app->close();
	}

	/**
	 * Function for updating products limit on list.
	 *
	 * @return  void
	 *
	 * @since   1.12.62
	 */
	public function updateProductsLimit()
	{
		$app    = Factory::getApplication();
		$layout = $app->input->getString('layout', '');
		$id     = $app->input->getInt('id', 0);
		$limit  = $app->input->getInt(
			'product_shop_limit',
			$app->getUserState(
				'shop.productLimit',
				RedshopbApp::getConfig()->get('shop_products_per_page', 12)
			)
		);

		$app->setUserState('shop.productLimit', $limit);
		$app->setUserState('shop.productlist.page.' . $layout . '_' . $id, 1);

		$this->setRedirect($this->getRedirectToListRoute($this->getRedirectToListAppend()));
	}

	/**
	 * Ajax method for calculating total with and without tax for products in list.
	 *
	 * @return  void
	 *
	 * @since   1.12.65
	 */
	public function ajaxGetProductsTotal()
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$products     = $input->get('products', array(), 'array');
		$currency     = $input->getInt('currency', 0);
		$collection   = $input->getInt('collection', 0);
		$total        = 0.0;
		$totalWtax    = 0.0;
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');

		$alreadyProcessedProducts = array();

		foreach ($products as $product)
		{
			// The following 2 if blocks removes malformed inputs
			if (!isset($product['id']) || !isset($product['quantity']))
			{
				continue;
			}

			// Prevent same product being accounted for again
			if (in_array($product['id'], $alreadyProcessedProducts))
			{
				continue;
			}

			$alreadyProcessedProducts[] = $product['id'];

			$qArr = RedshopbHelperCart::splitQuantityMultiplications(
				(int) $product['id'], 0, (float) $product['quantity'],
				$currency, $customerId, $customerType, $collection
			);

			foreach ($qArr as $quantity)
			{
				$pPrice     = RedshopbHelperPrices::getProductPrice(
					(int) $product['id'], $customerId, $customerType,
					$currency, array($collection), '', 0, $quantity
				);
				$total     += $pPrice->price * $quantity;
				$totalWtax += $pPrice->price_with_tax * $quantity;
			}
		}

		echo json_encode(
			array(
				'total'        => RedshopbHelperProduct::getProductFormattedPrice($total, $currency),
				'totalWithTax' => RedshopbHelperProduct::getProductFormattedPrice($totalWtax, $currency)
			)
		);

		$app->close();
	}

	/**
	 * Stores the invoice email in a user state for retrieval later
	 *
	 * @return   null
	 */
	private function storeInvoiceEmail()
	{
		if (!RedshopbEntityConfig::getInstance()->get('show_invoice_email_field', 0))
		{
			return null;
		}

		$app = Factory::getApplication();

		if (array_key_exists('invoice_email', $app->input->getArray()))
		{
			$app->setUserState('checkout.invoice_email_toggle', $app->input->get('invoice_email_toggle'));
			$app->setUserState('checkout.invoice_email', $app->input->getString('invoice_email', ''));
		}
	}

	/**
	 * Updates the shipping_rate_id user state
	 *
	 * @return   void
	 */
	public function ajaxUpdateShippingRateId()
	{
		$app            = Factory::getApplication();
		$shippingRateId = $app->input->get('shippingRateId', '');
		$selfPickup     = false;

		$app->setUserState('checkout.shipping_rate_id', $app->input->get('shippingRateId', ''));

		if ($shippingRateId)
		{
			$shippingRate = RedshopbShippingHelper::getShippingRateById($shippingRateId);

			if ($shippingRate->shipping_name == 'self_pickup')
			{
				$selfPickup = true;
			}
		}

		echo json_encode(
			array(
				'selfPickup'  => $selfPickup
			)
		);

		$app->close();
	}
}
