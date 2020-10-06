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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/**
 * Return Order Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerReturn_Order extends RedshopbControllerForm
{
	/**
	 * Object model.
	 *
	 * @var    object
	 */
	private $model;

	/**
	 * Ajax call to get products based on selected order.
	 *
	 * @return  void
	 */
	public function ajaxGetOrderProducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$model = $this->getModel('Return_Order', 'RedshopbModel', array('ignore_request' => false));
		$input = $app->input;

		$orderId           = $input->getInt('order_id', 0);
		$jform             = $input->get('jform', array(), 'array');
		$jform['order_id'] = $orderId;
		$input->set('jform', $jform);

		$products = $model->getProductsFormField();
		echo $products;

		$app->close();
	}

	/**
	 * Get the Route object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		return RedshopbRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $append, false);
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$return                      = parent::save($key, $urlVar);
		$model                       = $this->getModel();
		$input                       = Factory::getApplication()->input;
		$formData                    = $input->get('jform', '', 'array');
		$formData['return_order_id'] = $model->getState($this->context . '.id');
		$orderId                     = $formData['order_id'];
		$order                       = RedshopbTable::getAdminInstance('Order');

		if (!$order->load($orderId))
		{
			return false;
		}

		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($order->customer_id, $order->customer_type, false);
		$vendorCompany   = RedshopbHelperCompany::getCompanyById($customerCompany->parent, false);
		$orderAuthor     = RedshopbHelperOrder::getOrderAuthor($orderId);
		$unions          = array();
		$db              = Factory::getDbo();

		// Get company admins
		RedshopbHelperOrder::getAdminMailRecipient($db, $vendorCompany, $unions);

		// Get order author
		RedshopbHelperOrder::getAuthorMailRecipient($db, $orderAuthor, $unions);

		// Gather all users
		$users = RedshopbHelperOrder::getAllUsers($db, $unions);

		if (empty($users))
		{
			return false;
		}

		$recipients = array();

		foreach ($users as $user)
		{
			$recipients['recipients'][] = $user->email;
		}

		if (empty($recipients))
		{
			return false;
		}

		self::sendReturnOrderMail($order, $customerCompany, $formData, $recipients);

		return $return;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		if (!isset($this->model))
		{
			$this->model = parent::getModel($name, $prefix, $config);
		}

		return $this->model;
	}

	/**
	 * Sends email for new order notification to an specific company
	 *
	 * @param   object  $order            Order
	 * @param   object  $customerCompany  Purchaser company
	 * @param   mixed   $formData         Form data value.
	 * @param   array   $recipients       Recipient list
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	private static function sendReturnOrderMail($order, $customerCompany, $formData, $recipients = array())
	{
		if (empty($recipients))
		{
			return;
		}

		RedshopbHelperOrder::sendMailHelperSettings($order, $orderId, $body, $scheme, $host, $mailer);

		/** @var RedshopbModelOrders $modelOrders */
		$modelOrders   = RModelAdmin::getInstance('Orders', 'RedshopbModel');
		$customerOrder = $modelOrders->getCustomerOrder($orderId);

		foreach ($customerOrder->regular->items as $item)
		{
			if ($item->id === $formData['order_item_id'])
			{
				$productName = $item->product_name;
			}
		}

		$text    = Text::_('COM_REDSHOPB_RETRUN_ORDER_MAIL_COMPLETED_BODY');
		$subject = sprintf(
			Text::_('COM_REDSHOPB_RETRUN_ORDER_MAIL_COMPLETED_SUBJECT'),
			str_pad($orderId, 6, '0', STR_PAD_LEFT)
		);

		RedshopbHelperOrder::sendMailHelperBodyText($body, $text, $customerCompany, $host, $scheme, $orderId, 'return_order');

		RedshopbHelperOrder::sendMailHelperLayoutRender($body, $customerOrder, $orderId, $modelOrders);

		$body .=
			RedshopbLayoutHelper::render(
				'order.returnordermail',
				array(
					'productName'          => isset($productName) ? $productName : '',
					'returnOrderId'        => $formData['return_order_id'],
					'returnOrderQuantity'  => $formData['quantity'],
					'comment'              => $formData['comment'],
				)
			);

		RedshopbHelperOrder::sendMailHelperFooter($body, $scheme, $host, 'COM_REDSHOPB_RETRUN_ORDER_MAIL_NOT_DISPLAYING_CORRECTLY',
			'return_order', $mailer, $recipients, $subject
		);

		$mailer->Send();
	}
}
