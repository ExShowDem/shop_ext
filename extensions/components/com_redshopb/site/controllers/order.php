<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Order Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerOrder extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_ORDER';

	/**
	 * Force Check payment now
	 *
	 * @return  boolean
	 *
	 * @since  1.13.0
	 */
	public function checkPayment()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$id      = $input->getInt('id');
		$payment = RApiPaymentHelper::getPaymentByExtensionId('com_redshopb', $id);

		if ($payment)
		{
			$status = RApiPaymentHelper::checkPayment($payment->id);

			if (!empty($status))
			{
				$app->enqueueMessage($status['message'], !empty($status['type']) ? $status['type'] : 'message');
			}
		}

		// Redirect to the item screen
		$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($id)));

		return true;
	}

	/**
	 * Capture payment
	 *
	 * @return  boolean
	 *
	 * @since  1.13.0
	 */
	public function capturePayment()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$id      = $input->getInt('id');
		$payment = RApiPaymentHelper::getPaymentByExtensionId('com_redshopb', $id);

		if ($payment)
		{
			$status = RApiPaymentHelper::capturePayment($payment->id);

			if ($status)
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_PAYMENT_CAPTURE_PAYMENT_SUCCESS'));
			}
			else
			{
				$lastLog = RApiPaymentHelper::getLastPaymentLog($id);
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_ORDER_PAYMENT_CAPTURE_PAYMENT_FAILED', $lastLog->message_text), 'error');
			}
		}

		// Redirect to the item screen
		$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($id)));

		return true;
	}

	/**
	 * Refund payment
	 *
	 * @return  boolean
	 *
	 * @since  1.13.0
	 */
	public function refundPayment()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$id      = $input->getInt('id');
		$payment = RApiPaymentHelper::getPaymentByExtensionId('com_redshopb', $id);

		if ($payment)
		{
			$status = RApiPaymentHelper::refundPayment($payment->id);

			if ($status)
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_PAYMENT_REFUND_PAYMENT_SUCCESS'));
			}
			else
			{
				$lastLog = RApiPaymentHelper::getLastPaymentLog($id);
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_ORDER_PAYMENT_REFUND_PAYMENT_FAILED', $lastLog->message_text), 'error');
			}
		}

		// Redirect to the item screen
		$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($id)));

		return true;
	}

	/**
	 * Delete payment
	 *
	 * @return  boolean
	 *
	 * @since  1.13.0
	 */
	public function deletePayment()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$id      = $input->getInt('id');
		$payment = RApiPaymentHelper::getPaymentByExtensionId('com_redshopb', $id);

		if ($payment)
		{
			$status = RApiPaymentHelper::deletePayment($payment->id);

			if ($status)
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_PAYMENT_DELETE_PAYMENT_SUCCESS'));
			}
			else
			{
				$lastLog = RApiPaymentHelper::getLastPaymentLog($id);
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_ORDER_PAYMENT_DELETE_PAYMENT_FAILED', $lastLog->message_text), 'error');
			}
		}

		// Redirect to the item screen
		$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($id)));

		return true;
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
		$append     = parent::getRedirectToItemAppend($recordId, $urlVar);
		$parentId   = RedshopbInput::getField('customer_id');
		$parentType = RedshopbInput::getField('customer_type');

		if ($parentId)
		{
			$append .= '&customer_id=' . $parentId;
			$append .= '&customer_type=' . $parentType;
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return string The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append      = parent::getRedirectToListAppend();
		$fromCompany = RedshopbInput::isFromCompany();
		$fromUser    = RedshopbInput::isFromUser();

		// Append the tab name for the company view
		if ($fromCompany)
		{
			$append .= '&tab=companies';
		}

		if ($fromUser)
		{
			$append .= '&tab=orders';
		}

		return $append;
	}

	/**
	 * Print order as PDF file.
	 *
	 * @return void
	 */
	public function printPDF()
	{
		$app = Factory::getApplication();
		$id  = $app->input->get->get('id', 0, 'int');
		RedshopbHelperOrder::printPDF(array($id));
		$app->close();
	}

	/**
	 * Method to add a new record.
	 * Since we use shop for creating order, we are redirecting user to shop view.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function add()
	{
		$app      = Factory::getApplication();
		$input    = $app->input;
		$fromUser = $input->getInt('from_user', 0);

		if ($fromUser)
		{
			$jForm   = $input->post->get('jform', array(), 'array');
			$rUserId = isset($jForm['user_id']) ? (int) $jForm['user_id'] : 0;

			if (!$rUserId)
			{
				$rUserId = (int) RedshopbHelperUser::getUserRSid(Factory::getUser()->id);
			}
		}
		else
		{
			$rUserId = (int) RedshopbHelperUser::getUserRSid(Factory::getUser()->id);
		}

		if ($rUserId)
		{
			$app->setUserState('list.rsbuser_id', $rUserId);
			$app->setUserState('shop.customer_id', $rUserId);
			$companyId    = RedshopbHelperUser::getUserCompanyId($rUserId, 'redshopb', false);
			$departmentId = RedshopbHelperUser::getUserDepartmentId($rUserId, 'redshopb', false);

			if ($companyId)
			{
				$app->setUserState('list.company_id', (int) $companyId);
			}

			if ($departmentId)
			{
				$app->setUserState('list.department_id', (int) $departmentId);
			}

			$app->setUserState('shop.customer_type', 'employee');
			$app->setUserState('shop.user_redirection', 1);
		}
		elseif (RedshopbHelperACL::isSuperAdmin())
		{
			$app->setUserState('list.rsbuser_id', 0);
			$app->setUserState('list.department_id', 0);
			$app->setUserState('list.company_id', 0);
			$app->setUserState('shop.customer_id', 0);
			$app->setUserState('shop.customer_type', '');
			$app->setUserState('shop.user_redirection', 1);
		}

		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false));
		$this->redirect();
	}

	/**
	 * Edit Order Items by clearing shopping cart and populating it with order items
	 *
	 * @return void
	 */
	public function editOrderItems()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app           = Factory::getApplication();
		$modelOrders   = RModel::getAdminInstance('Orders');
		$orderId       = $app->input->get('id', 0, 'int');
		$customerOrder = $modelOrders->getCustomerOrder($orderId);
		/** @var RedshopbModelShop $modelShop */
		$modelShop = RModelAdmin::getInstance('Shop', 'RedshopbModel');

		$app->setUserState('shop.customer_id', $customerOrder->customer_id);
		$app->setUserState('shop.customer_type', $customerOrder->customer_type);
		$company = RedshopbHelperCompany::getCompanyIdByCustomer($customerOrder->customer_id, $customerOrder->customer_type, false);

		if ($customerOrder->customer_type == 'department')
		{
			$department = $customerOrder->customer_id;
			$employee   = 0;
		}
		elseif ($customerOrder->customer_type == 'employee')
		{
			$employee   = $customerOrder->customer_id;
			$department = RedshopbHelperUser::getUserDepartmentId($employee, 'redshopb', false);
		}
		else
		{
			$employee   = 0;
			$department = 0;
		}

		$app->setUserState('list.rsbuser_id', $employee);
		$app->setUserState('list.department_id', $department);
		$app->setUserState('list.company_id', $company);
		$app->setUserState('checkout.orderId', $orderId);
		$app->setUserState('checkout.delivery_address_id', $customerOrder->delivery_address_id);
		$app->setUserState('checkout.comment', $customerOrder->comment);
		$app->setUserState('checkout.requisition', $customerOrder->requisition);
		$app->setUserState('checkout.payment_name', $customerOrder->payment_name);
		$app->setUserState('checkout.shipping_rate_id', $customerOrder->shipping_rate_id);
		$app->setUserState('shop.user_redirection', 1);
		$app->getUserStateFromRequest('checkout.comment', 'comment', $customerOrder->comment, 'string');
		$app->getUserStateFromRequest('checkout.requisition', 'requisition', $customerOrder->requisition, 'string');
		$app->getUserStateFromRequest('checkout.payment_name', 'payment_name', $customerOrder->payment_name, 'string');
		$app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', $customerOrder->shipping_rate_id, 'string');
		$modelShop->clearCart(true);
		$fees = RedshopbHelperShop::getChargeProducts('fee');

		if (!empty($customerOrder->regular->items))
		{
			foreach ($customerOrder->regular->items as $item)
			{
				if (in_array($item->product_item_id, $fees))
				{
					continue;
				}

				if (isset($item->quantity) && (int) $item->quantity > 0)
				{
					$modelShop->addNewCartItem(
						$item->product_id,
						$item->product_item_id,
						$item->accessories,
						$item->quantity,
						$item->price,
						$item->currency,
						$customerOrder->customer_id,
						$customerOrder->customer_type
					);
				}
			}
		}

		if (!empty($customerOrder->offers))
		{
			$myofferModel = $this->getModel('Myoffer');

			foreach ($customerOrder->offers AS $offer)
			{
				$myofferModel->loadCart($offer->id, true);
			}
		}

		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop'), Text::_('COM_REDSHOPB_ORDER_MESSAGE_CHANGE_ORDER_ITEMS'));
		$this->redirect();
	}

	/**
	 * Cancel order edit
	 *
	 * @return void
	 */
	public function cancelEditOrderItems()
	{
		$app     = Factory::getApplication();
		$orderId = $app->getUserState('checkout.orderId', 0);
		$app->setUserState('shop.customer_id',  0);
		$app->setUserState('shop.customer_type',  '');
		$app->setUserState('checkout.orderId', 0);
		$app->setUserState('checkout.delivery_address_id', 0);
		$app->setUserState('checkout.comment', '');
		$app->setUserState('checkout.requisition', '');
		$app->setUserState('checkout.payment_name', '');
		$app->setUserState('checkout.shipping_rate_id', '');
		$app->setUserState('list.rsbuser_id', 0);
		$app->setUserState('list.department_id', 0);
		$app->setUserState('list.company_id', 0);
		$app->setUserState('shop.user_redirection', 0);
		$app->getUserStateFromRequest('checkout.comment', 'comment', '', 'string');
		$app->getUserStateFromRequest('checkout.requisition', 'requisition', '', 'string');
		$app->getUserStateFromRequest('checkout.payment_name', 'payment_name', '', 'string');
		$app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', '', 'string');

		/** @var RedshopbModelShop $modelShop */
		$modelShop = RModelAdmin::getInstance('Shop', 'RedshopbModel');
		$modelShop->clearCart(true);

		if (!$orderId)
		{
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=orders'));
		}
		else
		{
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . $orderId, false));
		}

		$this->redirect();
	}

	/**
	 * Save order items from ajax call.
	 *
	 * JSON array {result : 0/1, msg : string}
	 *
	 * @return void
	 */
	public function ajaxSaveOrderItems()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app      = Factory::getApplication();
		$items    = $this->input->get('items', array(), 'array');
		$orderId  = $this->input->get('orderid', 0, 'int');
		$reqItems = array();

		/** @var RedshopbModelOrder $model */
		$model = $this->getModel();

		foreach ($items as $item)
		{
			$tmp               = explode('_', $item);
			$reqItems[$tmp[0]] = $tmp[1];
		}

		echo json_encode($model->saveOrderItems($orderId, $reqItems));

		$app->close();
	}

	/**
	 * Refund an order.
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function refund()
	{
		$id    = $this->input->getInt('id', 0);
		$model = $this->getModel('Order');
		$order = $model->getItem($id);

		if ($order->status == 2)
		{
			try
			{
				$order->status = 3;

				if ($order->customer_type == 'employee')
				{
					if (is_string($order->total_price))
					{
						if (strstr($order->total_price, ","))
						{
							$order->total_price = str_replace(".", "", $order->total_price);
							$order->total_price = str_replace(",", ".", $order->total_price);
						}

						if (preg_match("#([0-9\.]+)#", $order->total_price, $match))
						{
							$order->total_price = floatval($match[0]);
						}
						else
						{
							$order->total_price = floatval($order->total_price);
						}
					}
					else
					{
						$order->total_price = (float) $order->total_price;
					}

					if (!$model->refund($order))
					{
						throw new Exception;
					}
				}

				if (!$model->save(ArrayHelper::fromObject($order)))
				{
					throw new Exception;
				}

				$message = Text::_('COM_REDSHOPB_ORDER_REFUND_SUCCESS');
			}
			catch (Exception $e)
			{
				$message = Text::_('COM_REDSHOPB_ORDER_REFUND_ERROR');
			}
		}
		else
		{
			$message = Text::_('COM_REDSHOPB_ORDER_REFUND_ONLY_CANCELED');
			$msgType = 'error';
		}

		if (isset($msgType))
		{
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=order&layout=edit&id=' . $id, false), $message, $msgType);
		}
		else
		{
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=order&layout=edit&id=' . $id, false), $message);
		}

		$this->redirect();
	}

	/**
	 * Sends mail for a placed order
	 *
	 * @return void
	 */
	public function hiddenSendMailOrder()
	{
		$orderId = $this->input->get('orderid', 0, 'int');

		if ($orderId)
		{
			RedshopbHelperOrder::sendMail($orderId);
		}

		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=orders', false));
	}

	/**
	 * Method for put all items in order into cart.
	 *
	 * @return  void
	 */
	public function ajaxCheckoutCartFromOrder()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$orderId = $app->input->getInt('id', 0);
		$model   = $this->getModel('Order');

		echo (int) $model->loadCartFromOrder($orderId);

		$app->close();
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$app  = Factory::getApplication();
		$data = $app->input->getArray();

		PluginHelper::importPlugin('vanir');
		$app->triggerEvent('onRedshopbOrderSave', array(&$data['jform']));

		return parent::save();
	}
}
