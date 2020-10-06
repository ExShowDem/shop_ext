<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

/**
 * A Order helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
class RedshopbHelperOrder
{
	/**
	 * Print pdf document for given order id.
	 *
	 * @param   array|int  $orderIds  Order ids
	 *
	 * @return  string|null           PDF document or null on failure
	 */
	public static function printPDF($orderIds)
	{
		$orderIds = ArrayHelper::toInteger((array) $orderIds);

		/** @var  RedshopbModelOrder $model */
		$model = RModel::getAdminInstance('Order');

		/** @var  RedshopbModelOrders $modelOrders */
		$modelOrders = RModel::getAdminInstance('Orders');

		PluginHelper::importPlugin('vanir');

		$customer    = new stdClass;
		$endCustomer = new stdClass;

		// Start pdf code
		$mPDF       = RedshopbHelperMpdf::getInstance();
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/redcore/css/component.min.css');
		$mPDF->WriteHTML($stylesheet, 1);

		$stylesheet = file_get_contents(JPATH_ROOT . '/media/com_redshopb/css/pdf_order.css');
		$mPDF->WriteHTML($stylesheet, 1);

		$mPDF->SetTitle(Text::_('COM_REDSHOPB_PDF_ORDER'));
		$mPDF->SetSubject(Text::_('COM_REDSHOPB_PDF_ORDER'));

		foreach ($orderIds as $orderId)
		{
			// New page per each order
			$mPDF->AddPage();

			$order       = (object) $model->getItem($orderId)->getProperties();
			$endCustomer = self::getEntityFromCustomer($order->customer_id, $order->customer_type);
			$customer    = RedshopbHelperCompany::getCustomerCompanyByCustomer($order->customer_id, $order->customer_type);
			$addressId   = (int) $order->delivery_address_id;

			if ($addressId != 0 && isset($endCustomer))
			{
				$deliveryAddress = RedshopbEntityAddress::getInstance($addressId)->getExtendedData();

				if (empty($deliveryAddress->name))
				{
					$deliveryAddress->name = $endCustomer->name;
				}
			}
			elseif (isset($endCustomer))
			{
				$deliveryAddress             = new stdClass;
				$deliveryAddress->name       = $endCustomer->name;
				$deliveryAddress->address    = $endCustomer->address;
				$deliveryAddress->city       = $endCustomer->city;
				$deliveryAddress->zip        = $endCustomer->zip;
				$deliveryAddress->country    = Text::_($endCustomer->country);
				$deliveryAddress->state_name = $endCustomer->state_name;
			}
			else
			{
				return null;
			}

			// Order UI fields
			$orderEmployee        = null;
			$orderDepartment      = null;
			$orderCompany         = RedshopbEntityCompany::load($customer->id);
			$orderVendor          = RedshopbEntityCompany::getInstance($orderCompany->id)->getVendor()->getItem();
			$orderVendor->address = RedshopbEntityAddress::getInstance($orderVendor->address_id)->getExtendedData();

			switch ($order->customer_type)
			{
				case 'employee':
					$orderEmployee   = $endCustomer;
					$orderDepartment = RedshopbHelperUser::getUserDepartment($orderEmployee->id);
					break;

				case 'department':
					$orderDepartment = $endCustomer;
					break;

				default:
					break;
			}

			$html = RedshopbLayoutHelper::render(
				'order.pdf.topreceipt',
				array(
					'deliveryAddress'  => $deliveryAddress,
					'customer'         => $customer,
					'endCustomer'      => $endCustomer,
					'comment'          => $order->comment,
					'requisition'      => $order->requisition,
					'orderEmployee'    => $orderEmployee,
					'orderDepartment'  => $orderDepartment,
					'orderCompany'     => $orderCompany,
					'orderVendor'      => $orderVendor,
					'order'            => $order,
				)
			);

			$customerOrder = $modelOrders->getCustomerOrder($orderId);

			// Prepare order data event
			RFactory::getDispatcher()->trigger('onVanirPrepareOrderBeforePrintPDF', array($orderId, $customerOrder));

			$html .= RedshopbLayoutHelper::render(
				'order.pdf.receipt.products',
				array(
					'customerorders' => array($customerOrder),
					'orderid'        => $orderId,
				)
			);

			$mPDF->WriteHTML($html, 2);
		}

		if (count($orderIds) == 1)
		{
			$mPDF->Output('Order ' . $customer->name . '-' . $endCustomer->name . '.pdf', 'D');
		}
		else
		{
			$mPDF->Output('Orders on ' . Date::getInstance('now')->toISO8601() . '.pdf', 'D');
		}

		return null;
	}

	/**
	 * Get order items query.
	 *
	 * @param   integer  $orderId  Order id.
	 *
	 * @return  JDatabaseQuery     Database object or false when query fails.
	 */
	public static function getOrderItemsQuery($orderId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('oi') . '.*'
				)
			)
			->from($db->qn('#__redshopb_order_item', 'oi'))
			->leftJoin($db->qn('#__redshopb_product_item') . ' AS pi ON pi.id = oi.product_item_id')
			->where($db->qn('oi.order_id') . ' = ' . (int) $orderId);

		return $query;
	}

	/**
	 * Gets order delivery address.
	 *
	 * @param   int  $orderId  Order id.
	 *
	 * @return  integer            Delivery address id.
	 */
	public static function getDeliveryAddress($orderId)
	{
		if (!$orderId)
		{
			return 0;
		}

		return (int) RedshopbEntityOrder::getInstance($orderId)->get('delivery_address_id', 0);
	}

	/**
	 * Get order which is sent from level above.
	 *
	 * @param   int $orderId Order id for getting sent log.
	 *
	 * @return integer Original order sent to upper level.
	 */
	public static function getExpeditedOrder($orderId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('order_id'))
			->from($db->qn('#__redshopb_order_logs'))
			->where($db->qn('new_order_id') . ' = ' . (int) $orderId)
			->where($db->qn('log_type') . ' = ' . $db->q('expedite'));
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get customer based on customer type
	 *
	 * @param   int     $customerId    Customer Id
	 * @param   string  $customerType  Customer Type
	 *
	 * @return mixed
	 */
	public static function getEntityFromCustomer($customerId, $customerType)
	{
		switch ($customerType)
		{
			case 'employee':
				return RedshopbHelperUser::getUser($customerId);

			case 'company':
				$user    = Factory::getUser();
				$company = RedshopbEntityCompany::getInstance($customerId);

				if (!$user->guest || !$company->isB2C())
				{
					return RedshopbHelperCompany::getCompanyById($customerId);
				}

				$guestEmployeeId = $company->getGuestEmployeeId();

				if (empty($guestEmployeeId))
				{
					$guestEmployeeId = 0;
				}

				return RedshopbHelperUser::getUser($guestEmployeeId);

			case 'department':
				return RedshopbHelperDepartment::getDepartmentById($customerId);

			default:
				return null;
		}
	}

	/**
	 * Check if orders are in pending status.
	 *
	 * @param   array  $orderIds  Order ids.
	 *
	 * @return boolean
	 */
	public static function areAllPending($orderIds = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT ' . $db->qn('status'))
			->from($db->qn('#__redshopb_order'))
			->where($db->qn('id') . ' IN (' . implode(',', $orderIds) . ')');
		$db->setQuery($query);

		$statuses = $db->loadColumn();

		return count($statuses) == 1 && ((int) $statuses[0]) == 0 ? true : false;
	}

	/**
	 * Check if order is a log type.
	 *
	 * @param   int  $orderId  Order id.
	 *
	 * @return  boolean
	 */
	public static function isLog($orderId = 0)
	{
		if ($orderId == 0 || !is_numeric($orderId))
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('order_id')
			->from($db->qn('#__redshopb_order_logs'))
			->where($db->qn('new_order_id') . ' = ' . (int) $orderId);

		$result = $db->setQuery($query)->loadColumn();

		return empty($result) ? false : true;
	}

	/**
	 * Check if all delivery addresses are same for given orders.
	 *
	 * @param   array  $orderIds  Order ids.
	 *
	 * @return integer|boolean If order have different addresses, result is false, otherwise is delivery address id.
	 */
	public static function areAllFromSameDeliveryAddress($orderIds = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT ' . $db->qn('delivery_address_id'))
			->from($db->qn('#__redshopb_order'))
			->where($db->qn('id') . ' IN (' . implode(',', $orderIds) . ')');
		$addresses = $db->setQuery($query)->loadColumn();

		return count($addresses) != 1 ? false : $addresses[0];
	}

	/**
	 * Get customer which made this order.
	 *
	 * @param   int  $orderId  Order id.
	 *
	 * @return  object         Customer object with id and type.
	 */
	public static function getOrderCustomer($orderId = 0)
	{
		if ((int) $orderId == 0)
		{
			return null;
		}

		$order = RedshopbEntityOrder::getInstance($orderId);

		if (!$order->isValid())
		{
			return null;
		}

		$result                = new stdClass;
		$result->cid           = $order->get('customer_id');
		$result->ctype         = $order->get('customer_type');
		$result->customer_id   = $order->get('customer_id');
		$result->customer_type = $order->get('customer_type');

		return $result;
	}

	/**
	 * Get the author user id (Joomla id) of an order
	 *
	 * @param   integer  $orderId  Order id.
	 *
	 * @return  integer|null   Id of Author if success. Null otherwise.
	 */
	public static function getOrderAuthor($orderId)
	{
		if ((int) $orderId == 0)
		{
			return null;
		}

		$order = RedshopbEntityOrder::getInstance($orderId);

		if (!$order->isValid())
		{
			return null;
		}

		return $order->get('created_by');
	}

	/**
	 * Check if orders comes from same entity or by given entity type.
	 *
	 * @param   array   $orderIds     Orders ids.
	 * @param   string  $companyType  Entity type.
	 *
	 * @return  object|null           Return null if orders are from different entities or company object if they come from same.
	 */
	public static function areAllFromSameCompany($orderIds = array(), $companyType = 'end_customer')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Does not look at "deleted" field or company on purpose, so it can always trace back the company even if it was deleted
		$query->select(
			array(
				$db->qn('c.id', 'company_id'),
				$db->qn('c.type', 'company_type'),
				$db->qn('c.parent_id', 'parent_id')
			)
		)
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('o.customer_company'))
			->where($db->qn('o.id') . ' IN (' . implode(',', $orderIds) . ')')
			->group($db->qn('c.id'));

		$orderCustomers = $db->setQuery($query)->loadObjectList();

		$companyId = 0;

		foreach ($orderCustomers as $orderCustomer)
		{
			$comparedCompanyId = $orderCustomer->company_id;

			if ($companyType == 'customer' && $orderCustomer->company_type == 'end_customer')
			{
				$comparedCompanyId = $orderCustomer->parent_id;
			}

			if ($companyId)
			{
				if ($companyId != $comparedCompanyId)
				{
					return null;
				}
			}
			else
			{
				$companyId = $comparedCompanyId;
			}
		}

		if ($companyId)
		{
			return RedshopbHelperCompany::getCompanyById($companyId, false);
		}

		return null;
	}

	/**
	 * Check if all orders have same currency.
	 *
	 * @param   array  $orderIds  Orders ids.
	 *
	 * @return  mixed             False if some order has different currency, currency id if all have the same.
	 */
	public static function areAllHavingSameCurrency($orderIds = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT ' . $db->qn('currency_id'))
			->from($db->qn('#__redshopb_order'))
			->where($db->qn('id') . ' IN (' . implode(',', $orderIds) . ')');

		$currencies = $db->setQuery($query)->loadColumn();

		return count($currencies) > 1 ? false : $currencies[0];
	}

	/**
	 * Send Email With Changed Payment Status
	 *
	 * @param   int  $orderId  Order id
	 *
	 * @return  boolean
	 *
	 * @deprecated   1.13.0
	 */
	public static function sendEmailWithChangedPaymentStatus($orderId)
	{
		return true;
	}

	/**
	 * Get Company Mail Recipient List
	 *
	 * @param   object  $company             Company object
	 * @param   object  $customerDepartment  Customer department object
	 * @param   int     $orderAuthor         Order author id
	 * @param   int     $orderPurchaser      Order purchaser id
	 *
	 * @return  array|boolean
	 *
	 * @since   1.13.0
	 */
	protected static function getCompanyMailRecipientList($company, $customerDepartment = null, $orderAuthor = 0, $orderPurchaser = 0)
	{
		$db            = Factory::getDbo();
		$config        = RedshopbApp::getConfig();
		$sendAdmins    = $config->getBool('order_notification_admin', true);
		$sendHODs      = $config->getBool('order_notification_hod', true);
		$sendSales     = $config->getBool('order_notification_sales', true);
		$sendAuthor    = $config->getBool('order_notification_author', true);
		$sendPurchaser = $config->getBool('order_notification_purchaser', true);
		$result        = array(
			'ccs'        => array(),
			'recipients' => array()
		);

		// If no users are selected in backend for notification sending, it skips the whole process
		if (!$sendAdmins
			&& !$sendHODs
			&& !$sendSales
			&& (!$sendAuthor || !$orderAuthor)
			&& (!$sendPurchaser || !$orderPurchaser)
		)
		{
			return false;
		}

		$unions      = array();
		$mainCompany = RedshopbHelperCompany::getMain();

		// Get company admins
		if ($sendAdmins === true)
		{
			self::getAdminMailRecipient($db, $company, $unions);
		}

		if (!is_null($customerDepartment) && $sendHODs === true)
		{
			// Get hods
			$subQuery = $db->getQuery(true)
				->select(
					array(
						$db->qn('ju.email', 'email'),
						$db->qn('rt.type', 'userType'),
						$db->qn('c.id', 'company')
					)
				)
				->from($db->qn('#__redshopb_user', 'ru'))
				->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
				->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
				->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
				->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
				->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
				->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('umc.company_id'))
				->innerJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('d.company_id'))
				->innerJoin(
					$db->qn('#__redshopb_department', 'dp') .
					' ON ' . $db->qn('dp.level') . ' <= ' . $db->qn('d.level') .
					' AND' . $db->qn('dp.lft') . ' <= ' . $db->qn('d.lft') .
					' AND ' . $db->qn('dp.rgt') . ' >= ' . $db->qn('d.rgt')
				)
				->where($db->qn('rt.type') . ' = ' . $db->q('hod'))
				->where($db->qn('d.id') . ' = ' . (int) $customerDepartment->id)
				->where($db->qn('ru.department_id') . ' = ' . $db->qn('dp.id'))
				->where('ru.use_company_email = 0')
				->where('ru.send_email = 1');
			$unions[] = $subQuery;
		}

		// Get sales persons for given company
		if ($sendSales === true)
		{
			$subQuery = $db->getQuery(true)
				->select(
					array(
						$db->qn('ju.email', 'email'),
						$db->qn('rt.type', 'userType'),
						$db->qn('umc.company_id', 'company')
					)
				)
				->from($db->qn('#__redshopb_user', 'ru'))
				->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
				->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
				->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
				->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
				->innerJoin($db->qn('#__redshopb_company_sales_person_xref', 'csp') . ' ON ' . $db->qn('csp.user_id') . ' = ' . $db->qn('ru.id'))
				->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
				->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('csp.company_id'))
				->where($db->qn('rt.type') . ' = ' . $db->q('sales'))
				->where($db->qn('c.id') . ' = ' . (int) $company->id)
				->where($db->qn('umc.company_id') . ' = ' . (int) $mainCompany->id)
				->where('ru.use_company_email = 0')
				->where('ru.send_email = 1');
			$unions[] = $subQuery;
		}

		// Get order author
		if ($sendAuthor === true)
		{
			self::getAuthorMailRecipient($db, $orderAuthor, $unions);
		}

		// Get order purchaser
		if ($orderPurchaser && $sendPurchaser === true)
		{
			$subQuery = $db->getQuery(true)
				->select(
					array(
						$db->qn('ju.email', 'email'),
						$db->qn('rt.type', 'userType'),
						$db->qn('c.id', 'company')
					)
				)
				->from($db->qn('#__users', 'ju'))
				->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
				->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
				->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
				->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('r.company_id'))
				->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
				->where($db->qn('ru.id') . ' = ' . (int) $orderPurchaser)
				->where('ru.use_company_email = 0')
				->where('ru.send_email = 1');
			$unions[] = $subQuery;
		}

		// Gather all users
		$users = self::getAllUsers($db, $unions);

		if (empty($users))
		{
			return false;
		}

		if ($company->id != $mainCompany->id)
		{
			foreach ($users as $user)
			{
				if ($user->company == $mainCompany->id && $user->userType == 'admin')
				{
					$result['ccs'][] = $user->email;

					continue;
				}

				$result['recipients'][] = $user->email;
			}
		}
		else
		{
			foreach ($users as $user)
			{
				$result['recipients'][] = $user->email;
			}
		}

		return $result;
	}

	/**
	 * Send email for new order notification
	 *
	 * @param   int  $orderId  Order id.
	 *
	 * @return  void
	 */
	public static function sendMail($orderId)
	{
		$order = RedshopbTable::getAdminInstance('Order');

		if (!$order->load($orderId))
		{
			return;
		}

		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($order->customer_id, $order->customer_type, false);
		$vendorCompany   = RedshopbHelperCompany::getCompanyById($customerCompany->parent, false);

		$sendCustomerMail = $customerCompany->send_mail;
		$sendVendorMail   = $vendorCompany->send_mail;

		RFactory::getDispatcher()->trigger('onAECBeforeSendMail', array(&$sendCustomerMail, &$sendVendorMail));

		// Checks if vendor company wants to receive emails
		if ($sendVendorMail)
		{
			$recipients = self::getCompanyMailRecipientList($vendorCompany);

			if ($recipients)
			{
				self::sendMailOrderCompany($order, $vendorCompany, $customerCompany, $vendorCompany, $recipients, false);
			}
		}

		// Checks if customer company wants to receive emails (including impersonation)
		if (!$sendCustomerMail)
		{
			return;
		}

		$customerDepartment = RedshopbHelperDepartment::getDepartmentByCustomer($order->customer_id, $order->customer_type);
		$orderAuthor        = static::getOrderAuthor($orderId);
		$orderPurchaser     = ($order->customer_type == 'employee' ? $order->customer_id : 0);

		if ($order->customer_type == 'employee' && $order->customer_id == $orderAuthor)
		{
			$orderAuthor = 0;
		}

		// This is a guest account then
		if ($order->customer_type == 'company' && !$orderAuthor)
		{
			$recipient = self::getGuestMailRecipient($order);

			if ($recipient)
			{
				self::sendMailOrderCompany($order, $vendorCompany, $customerCompany, $vendorCompany, $recipient);
			}
		}

		$recipients = self::getCompanyMailRecipientList($customerCompany, $customerDepartment, $orderAuthor, $orderPurchaser);

		if (!$recipients)
		{
			return;
		}

		self::sendMailOrderCompany($order, $customerCompany, $customerCompany, $vendorCompany, $recipients);
	}

	/**
	 * Sends email for new order notification to an specific company
	 *
	 * @param   object   $order            Order
	 * @param   object   $company          Company to notify (either purchaser or vendor)
	 * @param   object   $customerCompany  Purchaser company
	 * @param   object   $vendorCompany    Vendor company
	 * @param   array    $recipients       Recipient list
	 * @param   boolean  $sendInvoice      Set the invoice email as additional receiver
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	private static function sendMailOrderCompany($order, $company, $customerCompany, $vendorCompany, $recipients = array(), $sendInvoice = true)
	{
		if (empty($recipients))
		{
			return;
		}

		/** @var   JMail   $mailer */
		self::sendMailHelperSettings($order, $orderId, $body, $scheme, $host, $mailer);

		// Is this order need expedite. Use for email body.
		$needExpedite = true;

		// Customer is Main Company or Order does not have payment
		if ($company->type == 'main' || empty($order->payment_name))
		{
			$needExpedite = false;
		}
		// This order has payment.
		elseif (!empty($order->payment_name))
		{
			$pluginName = explode('!', $order->payment_name);

			// Try to get payment param for "offline_payment" check
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true)
				->select($db->qn('params'))
				->from($db->qn('#__extensions', 'e'))
				->where($db->qn('e.type') . ' = ' . $db->quote('plugin'))
				->where($db->qn('e.element') . ' = ' . $db->quote($pluginName[0]))
				->where($db->qn('e.folder') . ' = ' . $db->quote('redpayment'));
			$plugin = $db->setQuery($query)->loadResult();

			if ($plugin)
			{
				$plugin = new Registry($plugin);

				if ($plugin->get('offline_payment', 0) == 1)
				{
					$needExpedite = false;
				}
			}
		}

		$view          = 'orders';
		$layout        = null;
		$appendOrderId = false;

		if ($needExpedite === false)
		{
			$text          = Text::_('COM_REDSHOPB_ORDER_MAIL_COMPLETED_BODY');
			$subject       = sprintf(
				Text::_('COM_REDSHOPB_ORDER_MAIL_COMPLETED_SUBJECT'),
				str_pad($orderId, 6, '0', STR_PAD_LEFT)
			);
			$view          = 'shop';
			$layout        = 'receipt';
			$appendOrderId = true;
		}
		else
		{
			$text = Text::_(($order->status == 6 ? 'COM_REDSHOPB_ORDER_MAIL_PLACED_EXPEDITED_BODY' :
				'COM_REDSHOPB_ORDER_MAIL_PLACED_REGULAR_BODY')
			);

			$subject = sprintf(
				Text::_(
					($order->status == 6 ?
						'COM_REDSHOPB_ORDER_MAIL_PLACED_EXPEDITED_SUBJECT'
						: 'COM_REDSHOPB_ORDER_MAIL_PLACED_REGULAR_SUBJECT'),
					''
				),
				str_pad($orderId, 6, '0', STR_PAD_LEFT)
			);
		}

		/** @var RedshopbModelUsers $usersModel */
		$modelOrders   = RModelAdmin::getInstance('Orders', 'RedshopbModel');
		$customerOrder = $modelOrders->getCustomerOrder($orderId);
		$endCustomer   = null;

		// Order UI fields
		$orderEmployee          = null;
		$orderDepartment        = null;
		$vendorCompany->address = RedshopbEntityAddress::getInstance($vendorCompany->addressId)->getExtendedData();
		$customerAt             = '';

		// Introduction
		self::sendMailHelperBodyText($body, $text, $customerCompany, $host, $scheme, $orderId, $view, $layout, $appendOrderId);

		// Getting shopping customer
		switch ($order->customer_type)
		{
			case 'employee':
				$endCustomer = RedshopbHelperUser::getUser($order->customer_id);
				$department  = RedshopbHelperUser::getUserDepartment($order->customer_id);

				if ($customerCompany->type == 'end_customer')
				{
					if (!is_null($department))
					{
						$customerAt = $department->name;
					}
					else
					{
						$customerAt = $customerCompany->name;
					}
				}
				else
				{
					$customerAt = $customerCompany->name;
				}

				// Filling up order UI fields for employee orders
				$orderEmployee   = $endCustomer;
				$orderDepartment = $department;

				break;

			case 'department':
				$endCustomer = RedshopbHelperDepartment::getDepartmentById($order->customer_id);

				if ($customerCompany->type == 'end_customer')
				{
					$customerAt = $customerCompany->name;
				}
				else
				{
					$customerAt = $customerCompany->name;
				}

				// Filling up order UI fields for department orders
				$orderDepartment = $endCustomer;

				break;

			case 'company':
				$endCustomer = RedshopbHelperCompany::getCompanyById($order->customer_id);

				if ($customerCompany->type == 'end_customer')
				{
					$customerAt = $endCustomer->name;
				}
				else
				{
					$customerAt = '';
				}

				break;

			default:
				break;
		}

		$deliveryAddressId = (int) $order->delivery_address_id;
		$deliveryAddress   = RedshopbEntityAddress::getInstance($deliveryAddressId)->getExtendedData();

		if (!is_null($endCustomer))
		{
			$deliveryAddress->customer = $endCustomer->name;
		}

		$comment              = $order->comment;
		$requisition          = $order->requisition;
		$stockroomPickupTitle = '';
		$stockroomPickupId    = null;

		if (!empty($order->shipping_details) && is_string($order->shipping_details))
		{
			$registry                = new Joomla\Registry\Registry($order->shipping_details);
			$order->shipping_details = $registry->toArray();
		}

		if (!empty($order->shipping_details['pickup_stockroom_id']))
		{
			$stockroomPickupId    = $order->shipping_details['pickup_stockroom_id'];
			$stockroomPickupTitle = RedshopbEntityStockroom::getInstance($order->shipping_details['pickup_stockroom_id'])->get('name');
		}

		$body .=
			RedshopbLayoutHelper::render('shop.checkout.topreceipt', array(
					'deliveryAddress'      => $deliveryAddress,
					'endCustomer'          => $endCustomer,
					'customerAt'           => $customerAt,
					'comment'              => $comment,
					'requisition'          => $requisition,
					'orderEmployee'        => $orderEmployee,
					'orderDepartment'      => $orderDepartment,
					'orderCompany'         => $customerCompany,
					'orderVendor'          => $vendorCompany,
					'output'               => 'email',
					'shippingDate'         => $order->shipping_date,
					'order'                => $order,
					'orderId'              => $orderId,
					'paymentName'          => $order->payment_name,
					'paymentTitle'         => self::getPaymentMethodTitle($order->customer_company, $order->payment_name),
					'shippingRateId'       => $order->shipping_rate_id,
					'shippingRateTitle'    => RedshopbShippingHelper::getShippingRateName($order->shipping_rate_id, true, $order->currency, $orderId),
					'stockroomPickupTitle' => $stockroomPickupTitle,
					'stockroomPickupId'    => $stockroomPickupId
				)
			);

		self::sendMailHelperLayoutRender($body, $customerOrder, $orderId, $modelOrders);

		self::sendMailHelperFooter($body, $scheme, $host, 'COM_REDSHOPB_EMAIL_NOT_DISPLAYING_CORRECTLY', 'orders', $mailer, $recipients, $subject);

		if (RedshopbEntityConfig::getInstance()->get('show_invoice_email_field', 0) && $sendInvoice)
		{
			self::addAlternateInvoiceRecipient($mailer);
		}

		$app = Factory::getApplication();
		$app->triggerEvent('onRedshopbBeforeOrderMailSend', array($order, &$mailer));
		$mailer->Send();
		$app->triggerEvent('onRedshopbAfterOrderMailSend', array());
	}

	/**
	 * Checks if all orders are checked out.
	 *
	 * @param   array  $orderIds  Order ids.
	 *
	 * @return  boolean
	 */
	public static function areAllCheckedOut($orderIds = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('id'))
			->from($db->qn('#__redshopb_order'))
			->where($db->qn('id') . ' IN (' . implode(',', $orderIds) . ')')
			->where($db->qn('checked_out') . ' IS NOT NULL')
			->where($db->qn('checked_out') . ' != 0');
		$result = $db->setQuery($query)->loadColumn();

		return !empty($result) ? false : true;
	}

	/**
	 * Get order purchaser.
	 *
	 * @param   object  $order  Order object.
	 *
	 * @return  object          Company purchaser for given order.
	 */
	public static function getPurchaser($order)
	{
		$purchaser = RedshopbHelperCompany::getCompanyByCustomer($order->customer_id, $order->customer_type, false);

		if (empty($purchaser))
		{
			$purchaser = RedshopbHelperCompany::getCompanyById($order->customer_company, false);
		}

		return $purchaser;
	}

	/**
	 * Get order purchaser.
	 *
	 * @param   integer  $orderId  ID of the order
	 *
	 * @return  object             Purchaser company for given order.
	 */
	public static function getPurchaserByOrderId($orderId)
	{
		return self::getPurchaser(self::getOrderCustomer($orderId));
	}

	/**
	 * Get order vendor.
	 *
	 * @param   object  $order  Order object
	 *
	 * @return  object          Company vendor for given order.
	 */
	public static function getVendor($order)
	{
		switch (RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent'))
		{
			case 'main':
				$vendorCompany = RedshopbHelperCompany::getMain();
				break;

			case 'parent':
			default:
				$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($order->customer_id, $order->customer_type, false);

				if ($customerCompany->parent == '')
				{
					return null;
				}

				$vendorCompany = RedshopbHelperCompany::getCompanyById($customerCompany->parent);
		}

		return $vendorCompany;
	}

	/**
	 * Get order vendor.
	 *
	 * @param   integer  $orderId  ID of the order
	 *
	 * @return  object             Company vendor for given order.
	 */
	public static function getVendorbyOrderId($orderId)
	{
		return self::getVendor(self::getOrderCustomer($orderId));
	}

	/**
	 * Get Payment Methods
	 *
	 * @param   integer  $companyId  ID of the company
	 * @param   float    $price      Price amount to calculate payment fee
	 * @param   string   $currency   Currency of the order
	 *
	 * @return  array                List of Payment methods
	 */
	public static function getPaymentMethods($companyId, $price, $currency)
	{
		static $paymentMethods;

		if (!isset($paymentMethods))
		{
			$paymentMethods = array();
		}

		if (isset($paymentMethods[$companyId]))
		{
			return $paymentMethods[$companyId];
		}

		$paymentMethods[$companyId] = array();

		if (RBootstrap::getConfig('enable_payment', 0) != 1)
		{
			return array();
		}

		PluginHelper::importPlugin('redpayment');
		$app = Factory::getApplication();
		$db  = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(array($db->qn('pg.id'), 'p.element'))
			->from($db->qn('#__redshopb_customer_price_group', 'pg'))
			->leftJoin(
				$db->qn('#__redshopb_customer_price_group_xref', 'pgx') . ' ON ' .
				$db->qn('pgx.price_group_id') . ' = ' .
				$db->qn('pg.id')
			)
			->leftJoin(
				$db->qn('#__redcore_payment_configuration', 'pc') . ' ON pc.extension_name = ' . $db->q('com_redshopb')
				. ' AND pc.owner_name = pg.id'
			)
			->leftJoin($db->qn('#__extensions', 'p') . ' ON pc.payment_name = p.element')
			->where($db->qn('p.type') . '= ' . $db->q('plugin'))
			->where($db->qn('p.folder') . '= ' . $db->q('redpayment'))
			->where('p.enabled = 1')
			->where('pg.state = 1')
			->where('pc.state = 1')
			->where($db->qn('pgx.customer_id') . ' = ' . (int) $companyId);

		$priceGroups = $db->setQuery($query)->loadObjectList();

		if (empty($priceGroups))
		{
			return array();
		}

		foreach ($priceGroups as $priceGroup)
		{
			// We need to change the logic a bit since one company can belong to multiple debtors with different payment gateway merchant_id
			$paymentList = array();

			$app->triggerEvent('onRedpaymentListPayments', array('com_redshopb', $priceGroup->id, &$paymentList));

			if (empty($paymentList))
			{
				continue;
			}

			foreach ($paymentList as $payment)
			{
				if ($priceGroup->element != $payment->value)
				{
					continue;
				}

				$payment->value       .= '!' . $priceGroup->id;
				$fee                   = $payment->helper->getPaymentFee($price);
				$payment->paymentFee   = $fee;
				$payment->originalText = $payment->text;
				$payment->text         = !empty($fee) ?
					Text::sprintf(
						'COM_REDSHOPB_ORDER_PAYMENT_FEE_AMOUNT',
						$payment->text,
						RHelperCurrency::getFormattedPrice($fee, $currency)
					) :
					$payment->text;

				$paymentMethods[$companyId][] = $payment;
			}
		}

		return $paymentMethods[$companyId];
	}

	/**
	 * Get Payment Method Fee
	 *
	 * @param   integer $companyId           ID of the company
	 * @param   float   $price               Price amount to calculate payment fee
	 * @param   string  $currency            Currency of the order
	 * @param   string  $redshopbPaymentName Payment name
	 *
	 * @return float
	 */
	public static function getPaymentMethodFee($companyId, $price, $currency, $redshopbPaymentName)
	{
		$paymentMethods = self::getPaymentMethods($companyId, $price, $currency);

		if (empty($paymentMethods))
		{
			return 0.0;
		}

		foreach ($paymentMethods as $paymentMethod)
		{
			if ($paymentMethod->value == $redshopbPaymentName)
			{
				return $paymentMethod->paymentFee;
			}
		}

		return 0;
	}

	/**
	 * Get Shipping Methods
	 *
	 * @param   integer $companyId       ID of the company
	 * @param   object  $deliveryAddress Delivery address
	 * @param   float   $price           Price amount to calculate shipping fee
	 * @param   string  $currency        Currency of the order
	 *
	 * @return array List of Shipping methods
	 */
	public static function getShippingMethods($companyId, $deliveryAddress, $price, $currency)
	{
		static $shippingMethods;

		if (!isset($shippingMethods))
		{
			$shippingMethods = array();
		}

		if (isset($shippingMethods[$companyId]))
		{
			return $shippingMethods[$companyId];
		}

		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		if (empty($deliveryAddress))
		{
			$customer        = RedshopbEntityCustomer::getInstance($customerId, $customerType);
			$deliveryAddress = $customer->getDeliveryAddress()->getExtendedData();
		}

		PluginHelper::importPlugin('redshipping');
		$app = Factory::getApplication();

		$shippingMethods[$companyId] = array();

		$priceGroups = RedshopbEntityCompany::getInstance($companyId)->getPriceGroups()->ids();
		$cart        = RedshopbHelperCart::getCart($customerId, $customerType)->get('items', array());

		if (empty($priceGroups))
		{
			return array();
		}

		foreach ($priceGroups as $priceGroup)
		{
			// We need to change the logic a bit since one company can belong to multiple debtors with different shipping gateway merchant_id
			$shippingList = array();
			$app->triggerEvent('onRedShippingListShipping', array('com_redshopb', $priceGroup, $deliveryAddress, $cart, &$shippingList));

			if (empty($shippingList))
			{
				continue;
			}

			foreach ($shippingList as $shipping)
			{
				$shipping->value       .= '!' . $priceGroup;
				$shipping->originalText = $shipping->text;

				$shippingMethods[$companyId][] = $shipping;
			}
		}

		return $shippingMethods[$companyId];
	}

	/**
	 * Get Payment Method Title
	 *
	 * @param   integer $companyId    ID of the company
	 * @param   string  $paymentName  Payment name
	 * @param   bool    $addedFeeText Added Payment fee text
	 *
	 * @return  string
	 */
	public static function getPaymentMethodTitle($companyId, $paymentName, $addedFeeText = true)
	{
		$cart             = RedshopbHelperCart::getFirstTotalPrice();
		$availableMethods = self::getPaymentMethods($companyId, $cart[key($cart)], key($cart));

		if (empty($availableMethods))
		{
			return '';
		}

		foreach ($availableMethods as $availableMethod)
		{
			if ($availableMethod->value == $paymentName)
			{
				return $addedFeeText ? $availableMethod->text : $availableMethod->originalText;
			}
		}

		return '';
	}

	/**
	 * Get Payment Methods
	 *
	 * @param   object $order      Order row
	 * @param   bool   $autoSubmit Auto submit payment form
	 *
	 * @return array List of Payment data needed for payment processing
	 */
	public static function preparePaymentData($order, $autoSubmit = true)
	{
		if (empty($order))
		{
			return array();
		}

		// We pull out payment name and owner name from payment_name field
		$paymentOptions = explode('!', $order->payment_name);
		$paymentName    = trim($paymentOptions[0]);
		$ownerName      = isset($paymentOptions[1]) ? $paymentOptions[1] : '';
		$url            = Uri::getInstance()->toString(array('scheme', 'host', 'port'))
			. RedshopbRoute::_(
				'index.php?option=com_redshopb&view=shop&layout=receipt&orderId=' . $order->id .
				(isset($order->token) ? '&token=' . $order->token : ''), false
			);

		Factory::getApplication()->triggerEvent('onRedshopbBeforeOrderPayment', array(&$order));

		return array(
			// Configuration data
			'payment_name'     => $paymentName,
			'extension_name'   => 'com_redshopb',
			'owner_name'       => $ownerName,
			'autoSubmit'       => $autoSubmit,
			'url_cancel'       => $url,
			'url_accept'       => $url,

			// Order data
			'order_name'       => Text::_('COM_REDSHOPB_ORDER') . ' ' . $order->id,
			'order_id'         => $order->id,
			'amount_original'  => $order->total_price,
			'amount_total'     => $order->total_price,
			'customer_note'    => $order->comment,
			'currency'         => $order->currency,
			'amount_shipping'  => $order->shipping_price,
			'shipping_details' => RedshopbShippingHelper::getShippingRateName($order->shipping_rate_id, true, $order->currency, $order->id),
		);
	}

	/**
	 * Get Payment Methods
	 *
	 * @param   int  $orderId Order Id
	 * @param   int  $status  Order status
	 * @param   bool $isNew   Is new order
	 *
	 * @return  void
	 */
	public static function freeChildOrdersFromCanceledProcess($orderId, $status, $isNew)
	{
		if ($status != 2 || $isNew || !self::isLog($orderId))
		{
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Free child orders from canceled collection or expedition
		// Make all child orders pending
		$query->update($db->qn('#__redshopb_order'))
			->set($db->qn('status') . ' = 0')
			->where($db->qn('id') . ' IN (' . implode(',', RedshopbHelperOrder_Log::getChildOrders(array($orderId))) . ')');
		$db->setQuery($query)->execute();

		// Delete connections from order_log table
		$query->clear()
			->delete($db->qn('#__redshopb_order_logs'))
			->where($db->qn('new_order_id') . ' = ' . (int) $orderId);
		$db->setQuery($query)->execute();
	}

	/**
	 * Check if payment methods are allowed
	 *
	 * @param   array $paymentMethods List of payment methods
	 *
	 * @return boolean
	 */
	public static function isPaymentAllowed($paymentMethods)
	{
		if (empty($paymentMethods))
		{
			return false;
		}

		$totalFinalPrice = RedshopbHelperCart::getTotalFinalPrice();

		if (empty($totalFinalPrice))
		{
			return true;
		}

		foreach ($totalFinalPrice as $currency => $price)
		{
			if ($currency === 'PTS')
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if shipping methods are allowed
	 *
	 * @param   array $shippingMethods List of shipping methods
	 *
	 * @return boolean
	 */
	public static function isShippingAllowed($shippingMethods)
	{
		if (empty($shippingMethods))
		{
			return false;
		}

		$totalFinalPrice = RedshopbHelperCart::getTotalFinalPrice();

		if (empty($totalFinalPrice))
		{
			return true;
		}

		foreach ($totalFinalPrice as $currency => $price)
		{
			if ($currency === 'PTS')
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Store customer order.
	 *
	 * @param   object    $customerOrder       Customer order
	 * @param   boolean   $allowExpedite       Allow order expedite
	 * @param   integer   $deliveryAddressId   Id of the selected delivery address for this order
	 *                                         If not present it will try to load it from user state
	 * @param   string    $requisition         Requisition; If not present it will try to load it from request
	 * @param   string    $comment             Comment; If not present it will try to load it from request
	 * @param   string    $shippingDate        Shipping Date
	 * @param   array     $shippingDetails     Shipping details array
	 * @param   boolean   $storeCustomerIp     Store customer IP
	 *
	 * @throws Exception
	 *
	 * @return integer Stored order id. 0 if order is not stored.
	 */
	public static function storeOrder(
		$customerOrder, $allowExpedite = false, $deliveryAddressId = 0, $requisition = '', $comment = '', $shippingDate = null,
		$shippingDetails = null, $storeCustomerIp = true
	)
	{
		$app    = Factory::getApplication();
		$config = RedshopbEntityConfig::getInstance();

		if (!$config->getInt('place_order', 1))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_CAN_NOT_BE_PLACED'), 'warning');

			return 0;
		}

		if (is_array($customerOrder))
		{
			$customerOrder = ArrayHelper::toObject($customerOrder);
		}

		$data         = array();
		$modelOrder   = RedshopbModel::getFrontInstance('Order');
		$isNew        = true;
		$orderPayment = false;
		$itemsIds     = array();
		$db           = Factory::getDbo();
		$currencyObj  = RedshopbHelperProduct::getCurrency($customerOrder->currency);
		$userRSid     = RedshopbHelperUser::getUserRSid();
		$vanirUser    = RedshopbEntityUser::getInstance($userRSid)->loadItem();
		$userCompany  = $vanirUser->getSelectedCompany();

		try
		{
			$db->transactionStart();

			$params = new Registry;

			$data['id']                  = 0;
			$data['created_by']          = 0;
			$data['status']              = 0;
			$data['delivery_address_id'] = (!$deliveryAddressId ? $app->getUserState('checkout.delivery_address_id', 0) : $deliveryAddressId);

			$company = RedshopbHelperCompany::getCustomerCompanyByCustomer($customerOrder->customerId, $customerOrder->customerType);

			if (empty($data['delivery_address_id']))
			{
				$data['delivery_address_id'] = RedshopbHelperCompany::getCustomerCompanyByCustomer(
					$customerOrder->customerId,
					$customerOrder->customerType
				)->addressId;
			}

			$addressInfo = RedshopbEntityAddress::getInstance($data['delivery_address_id'])->getExtendedData();

			$data['delivery_address_name']         = $addressInfo->name;
			$data['delivery_address_name2']        = $addressInfo->name2;
			$data['delivery_address_city']         = $addressInfo->city;
			$data['delivery_address_country']      = Text::_($addressInfo->country);
			$data['delivery_address_state']        = $addressInfo->state_name;
			$data['delivery_address_address']      = $addressInfo->address;
			$data['delivery_address_address2']     = $addressInfo->address2;
			$data['delivery_address_zip']          = $addressInfo->zip;
			$data['delivery_address_country_code'] = $addressInfo->country_code;
			$data['delivery_address_state_code']   = $addressInfo->state_code;
			$data['delivery_address_type']         = $addressInfo->address_type;
			$data['delivery_address_code']         = $addressInfo->address_code;
			$data['delivary_address_phone']        = $addressInfo->phone;

			$data['user_company_id'] = $userCompany ? $userCompany->get('id') : null;
			$data['customer_id']     = $customerOrder->customerId;
			$data['customer_type']   = $customerOrder->customerType;
			$data['customer_name']   = RedshopbHelperShop::getCustomerName($data['customer_id'], $data['customer_type']);
			$data['customer_name2']  = RedshopbHelperShop::getCustomerName2($data['customer_id'], $data['customer_type']);
			$data['customer_email']  = $addressInfo->email;
			$data['customer_phone']  = $addressInfo->phone;

			$data['customer_company'] = $customerOrder->customerId;

			if ($data['customer_type'] == 'department')
			{
				$data['customer_company']    = RedshopbHelperDepartment::getCompanyId($data['customer_id']);
				$data['customer_department'] = $customerOrder->customerId;
			}
			elseif ($data['customer_type'] == 'employee')
			{
				$data['customer_company'] = RedshopbHelperUser::getUserCompanyId($data['customer_id']);
				$department               = RedshopbHelperUser::getUserDepartmentId($data['customer_id']);

				if (!empty($department))
				{
					$data['customer_department'] = $department;
				}

				$data['user_erp_id'] = RedshopbHelperShop::getCustomerErpId($data['customer_id'], 'employee');
			}

			$data['company_erp_id'] = RedshopbHelperShop::getCustomerErpId($data['customer_company'], 'company');

			if (isset($data['customer_department']))
			{
				$data['department_erp_id'] = RedshopbHelperShop::getCustomerErpId($data['customer_department'], 'department');
			}

			$orderId = $app->getUserState('checkout.orderId', 0);

			if ($orderId > 0)
			{
				$order = $modelOrder->getItem($orderId);

				if ($order->customer_type == $customerOrder->customerType && $order->customer_id == $customerOrder->customerId)
				{
					$data['id']               = $order->id;
					$data['created_by']       = $order->created_by;
					$data['status']           = $order->status;
					$data['payment_status']   = $order->payment_status;
					$data['payment_title']    = $order->payment_title;
					$data['shipping_rate_id'] = $order->shipping_rate_id;
					$data['shipping_price']   = $order->shipping_price;
					$isNew                    = false;
				}
			}

			if ($storeCustomerIp && $isNew && !empty($_SERVER['REMOTE_ADDR']))
			{
				$data['ip_address'] = $_SERVER['REMOTE_ADDR'];
			}

			$data['comment']     = (
			$comment == ''
				? $app->getUserStateFromRequest('checkout.comment', 'comment', '', 'string')
				: $comment
			);
			$data['requisition'] = (
			$requisition == ''
				? $app->getUserStateFromRequest('checkout.requisition', 'requisition', '', 'string')
				: $requisition
			);

			$data['shipping_date'] = $shippingDate;

			if (!$shippingDate)
			{
				$shippingDates = $app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array');

				if (array_key_exists($data['customer_type'] . '_' . $data['customer_id'], $shippingDates))
				{
					$data['shipping_date'] = $shippingDates[$data['customer_type'] . '_' . $data['customer_id']];
				}
			}

			$data['payment_name']     = $app->getUserStateFromRequest('checkout.payment_name', 'payment_name', '', 'string');
			$data['shipping_rate_id'] = $app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', null, 'string');

			if ($shippingDetails)
			{
				$data['shipping_details'] = $shippingDetails;
			}
			else
			{
				$data['shipping_details'] = array(
					'pickup_stockroom_id' => $app->getUserState('checkout.pickup_stockroom_id', 0)
				);
			}

			$data['total_price']   = $customerOrder->totalFinal;
			$data['discount']      = $customerOrder->discount;
			$data['discount_type'] = $customerOrder->discount_type;
			$taxList               = array();
			$taxAmount             = 0;

			if ($isNew && !empty($data['shipping_rate_id']))
			{
				$data['shipping_price'] = RedshopbShippingHelper::getShippingRatePrice($data['shipping_rate_id']);
				RFactory::getDispatcher()->trigger('onAECsetShippingPrice', array(&$data['shipping_price'], $data['shipping_rate_id']));

				$data['total_price'] += (float) $data['shipping_price'];
			}

			$productList      = array();
			$quantityMessages = array();

			foreach ($customerOrder->items as $item)
			{
				$productEntity  = RedshopbEntityProduct::load($item->product_id);
				$quantityStatus = $productEntity->checkQuantities($item->quantity);

				if (!$quantityStatus['isOK'])
				{
					$quantityMessages[] = $quantityStatus['msg'];
					continue;
				}

				if (!array_key_exists($item->product_id, $productList))
				{
					$productList[$item->product_id] = array(
						'product_name' => $item->product_name,
						'final_price'  => $item->final_price
					);
				}
				else
				{
					$productList[$item->product_id]['final_price'] += $item->final_price;
				}
			}

			if (!empty($quantityMessages))
			{
				throw new Exception(implode('<br />', $quantityMessages));
			}

			// Include product taxes
			foreach ($productList as $productId => $item)
			{
				$taxes = RedshopbHelperTax::getTaxRates($data['customer_id'], $data['customer_type'], $productId, true);

				if ($taxes)
				{
					foreach ($taxes as $tax)
					{
						$singleTax             = new stdClass;
						$singleTax->name       = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item['product_name']);
						$singleTax->tax_rate   = $tax->tax_rate;
						$singleTax->tax        = $item['final_price'] * $tax->tax_rate;
						$singleTax->product_id = $productId;

						if (array_key_exists('shipping_price', $data) && (float) $data['shipping_price'] > 0)
						{
							$singleTax->tax += (float) $data['shipping_price'] * $tax->tax_rate;
						}

						$taxList[]  = $singleTax;
						$taxAmount += $singleTax->tax;
					}
				}
			}

			// Get global taxes
			$taxes = RedshopbHelperTax::getTaxRates($data['customer_id'], $data['customer_type']);

			if ($taxes)
			{
				foreach ($taxes as $tax)
				{
					$singleTax           = new stdClass;
					$singleTax->name     = $tax->name;
					$singleTax->tax_rate = $tax->tax_rate;
					$singleTax->tax      = $customerOrder->totalFinal * $tax->tax_rate;

					if (array_key_exists('shipping_price', $data) && (float) $data['shipping_price'] > 0)
					{
						$singleTax->tax += (float) $data['shipping_price'] * $tax->tax_rate;
					}

					$taxList[]  = $singleTax;
					$taxAmount += $singleTax->tax;
				}
			}

			$data['total_price'] += $taxAmount;

			$data['currency']    = $currencyObj->alpha3;
			$data['currency_id'] = $currencyObj->id;
			$shopperCompany      = RedshopbHelperCompany::getCompanyByCustomer($data['customer_id'], $data['customer_type']);

			if ($isNew && !empty($data['payment_name']))
			{
				$data['payment_status']   = RApiPaymentStatus::getStatusCreated();
				$data['payment_title']    = static::getPaymentMethodTitle($data['customer_company'], $data['payment_name'], false);
				$paymentFee               = static::getPaymentMethodFee(
					$data['customer_company'], $data['total_price'], $data['currency'], $data['payment_name']
				);
				$data['total_price']     += (float) $paymentFee;
				$data['total_price_paid'] = 0;
			}

			if ($data['customer_type'] == 'employee' && $shopperCompany->useWallet)
			{
				// Check funds before proceeding with the purchase
				if (!$customerOrder->isWalletCart
					&& !RedshopbHelperUser::employeePurchase(
						$data['customer_id'],
						$data['total_price'],
						$data['currency_id'],
						$shopperCompany, true
					)
				)
				{
					throw new Exception(
						Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' ' .
						RedshopbHelperUser::getUser($customerOrder->customerId)->name . ': ' .
						Text::_('COM_REDSHOPB_SHOP_INSUFFICIENT_FUNDS')
					);
				}

				$orderPayment = true;
			}

			if (!empty($data['payment_name']))
			{
				$pluginName = explode('!', $data['payment_name']);
				PluginHelper::importPlugin('redpayment', $pluginName[0]);
			}

			$preValidateOrder = RFactory::getDispatcher()
				->trigger('onRedshopbPreValidateOrder', array(compact(array_keys(get_defined_vars()))));

			if (!empty($preValidateOrder))
			{
				foreach ($preValidateOrder as $oneResult)
				{
					if (is_array($oneResult))
					{
						extract($oneResult);
					}
				}
			}

			$data['token'] = bin2hex(random_bytes(16));

			if ($config->get('show_invoice_email_field', 0) && $app->getUserState('checkout.invoice_email_toggle') != 1)
			{
				$params->set('invoice_email', $app->getUserState('checkout.invoice_email', ''));
			}

			$data['params'] = $params->toString();

			$form = $modelOrder->getForm();

			if (!$modelOrder->validate($form, $data))
			{
				throw new Exception($modelOrder->getError());
			}

			if ($modelOrder->save($data))
			{
				$orderId = (int) $modelOrder->getState($modelOrder->getName() . '.id');

				$itemAttributesTable = RedshopbTable::getInstance('Order_Item_Attribute', 'RedshopbTable');
				$orderItems          = null;
				$query               = $db->getQuery(true);

				if (!$isNew)
				{
					$query->clear()
						->delete($db->qn('#__redshopb_order_item'))
						->where($db->qn('order_id') . ' = ' . (int) $orderId);
					$db->setQuery($query)->execute();

					$query->clear()
						->delete($db->qn('#__redshopb_order_tax'))
						->where($db->qn('order_id') . ' = ' . (int) $orderId);
					$db->setQuery($query)->execute();
					$query->clear();
				}

				if (!empty($taxList))
				{
					$table = $modelOrder->getTable('Order_Tax');

					foreach ($taxList as $tax)
					{
						$table->reset();
						$table->id = null;

						$item = array(
							'name'     => $tax->name,
							'tax_rate' => $tax->tax_rate,
							'price'    => $tax->tax,
							'order_id' => $orderId
						);

						if (isset($tax->product_id))
						{
							$item['product_id'] = $tax->product_id;
						}

						// Insert new order item
						if (!$table->save($item))
						{
							throw new Exception($table->getError());
						}
					}
				}

				if (!empty($customerOrder->offer_id))
				{
					foreach ($customerOrder->offer_id AS $offerId)
					{
						RedshopbHelperOffers::changeOfferStatus($offerId, 'ordered');
					}
				}

				$walletPriceTotal = 0.0;
				$table            = $modelOrder->getTable('Order_Item');

				foreach ($customerOrder->items as $item)
				{
					$table->reset();
					$table->id = null;
					$item      = (array) $item;
					$stockroom = RedshopbEntityStockroom::getInstance($item['stockroomId']);

					if ($item['wallet'])
					{
						$walletPriceTotal += (float) $item['final_price'];
					}

					$item['order_id']       = $orderId;
					$item['stockroom_id']   = $stockroom->get('id');
					$item['stockroom_name'] = $stockroom->get('name');
					$collectionName         = '';
					$collectionEprId        = '';

					// Stockroom: Reduce quantity amount of stockroom for product variant
					if ($item['stockroom_id'] && $item['product_item_id'])
					{
						$stockroomData = RedshopbHelperStockroom::getProductItemStockroomData(
							(int) $item['product_item_id'],
							array($item['stockroom_id'])
						);

						if (!empty($stockroomData))
						{
							$stockroomProductItemTable = RedshopbTable::getAdminInstance('Stockroom_Product_Item_Xref');

							if (!$stockroomProductItemTable->load(
								array(
									'stockroom_id'    => $item['stockroom_id'],
									'product_item_id' => $item['product_item_id']
								)
							)
							)
							{
								throw new Exception($stockroomProductItemTable->getError());
							}

							if (!$stockroomProductItemTable->unlimited)
							{
								if ($company->stockroom_verification && $stockroomProductItemTable->amount < $item['quantity'])
								{
									throw new Exception(Text::_('COM_REDSHOPB_ORDER_ERROR_REDUCE_AMOUNT_STOCK'));
								}

								$stockroomProductItemTable->amount = (float) $stockroomProductItemTable->amount - (float) $item['quantity'];

								if (!$stockroomProductItemTable->store())
								{
									throw new Exception($stockroomProductItemTable->getError());
								}
							}
						}
					}
					// Stockroom: Reduce quantity amount of stockroom for product
					elseif ($item['stockroom_id'] && $item['product_id'])
					{
						$stockroomData = RedshopbHelperStockroom::getProductStockroomData(
							(int) $item['product_id'],
							(int) $item['stockroom_id']
						);

						if (!empty($stockroomData))
						{
							$stockroomProductTable = RedshopbTable::getAdminInstance('Stockroom_Product_Xref');

							if (!$stockroomProductTable->load(
								array(
									'stockroom_id' => $item['stockroom_id'],
									'product_id'   => $item['product_id']
								)
							)
							)
							{
								throw new Exception($stockroomProductTable->getError());
							}

							if (!$stockroomProductTable->unlimited)
							{
								if ($company->stockroom_verification && $stockroomProductTable->amount < $item['quantity'])
								{
									throw new Exception(Text::_('COM_REDSHOPB_ORDER_ERROR_REDUCE_AMOUNT_STOCK'));
								}

								$stockroomProductTable->amount = (float) $stockroomProductTable->amount - (float) $item['quantity'];

								if (!$stockroomProductTable->store())
								{
									throw new Exception($stockroomProductTable->getError());
								}
							}
						}
					}

					if ((int) $item['collectionId'] == 0)
					{
						unset($item['collectionId']);
					}

					if (isset($item['collectionId']) && !is_null($item['collectionId']))
					{
						$collectionId            = $item['collectionId'];
						$item['collection_id']   = $collectionId;
						$item['collection_name'] = RedshopbHelperCollection::getName($collectionId);
						$collectionName          = $item['collection_name'];

						$query->clear()
							->select(
								$db->qn('remote_key')
							)
							->from($db->qn('#__redshopb_sync'))
							->where(
								$db->qn('local_id') . ' = ' . (int) $collectionId . ' AND ' . $db->qn('reference') . ' = ' . $db->q('erp.collection')
							);

						$item['collection_erp_id'] = $db->setQuery($query)->loadResult();
						$collectionEprId           = $item['collection_erp_id'];
					}

					$itemsIds[$item['product_item_id']] = $item['quantity'];

					// Insert new order item
					if (!$table->save($item))
					{
						throw new Exception($table->getError());
					}

					$itemId = $table->id;

					foreach ($item['accessories'] as $accessory)
					{
						$accessory['parent_id'] = $itemId;
						$table->reset();
						$table->id                           = null;
						$accessory['product_id']             = $accessory['productId'];
						$accessory['price_without_discount'] = $accessory['price'];
						$accessory['order_id']               = $orderId;

						if (array_key_exists('quantity', $accessory))
						{
							$accessory['quantity'] *= $item['quantity'];
						}
						else
						{
							$accessory['quantity'] = $item['quantity'];
						}

						if (isset($collectionId))
						{
							$accessory['collection_id']     = $collectionId;
							$accessory['collection_name']   = $collectionName;
							$accessory['collection_erp_id'] = $collectionEprId;
						}

						// Insert new accessory item
						if (!$table->save($accessory))
						{
							throw new Exception($table->getError());
						}
					}

					$i = 0;

					foreach ($item['attributesDefault'] as $attributeName => $attributeObj)
					{
						$itemAttributesTable->reset();
						$itemAttributesTable->id    = null;
						$attribute                  = array();
						$attribute['order_item_id'] = $itemId;
						$attribute['ordering']      = $i;
						$i++;
						$attribute['name'] = $attributeName;
						$attribute['sku']  = $attributeObj->sku;

						if ($attributeObj->type > 0)
						{
							if ($attributeObj->type == 1)
							{
								$attribute['float_value'] = $attributeObj->value;
							}
							else
							{
								$attribute['int_value'] = $attributeObj->value;
							}
						}
						else
						{
							$attribute['string_value'] = $attributeObj->value;
						}

						if (!$itemAttributesTable->save($attribute))
						{
							throw new Exception($itemAttributesTable->getError());
						}
					}
				}

				if ($orderPayment)
				{
					$totalPrice = $data['total_price'];

					if ($customerOrder->isWalletCart)
					{
						// Reduce wallet price from total price.
						$totalPrice = $totalPrice - $walletPriceTotal;

						// Wallet funds increase if purchased product has marked wallet.
						$userModel = RedshopbModel::getInstance('User', 'RedshopbModel');

						if (!$userModel->credit($data['customer_id'], $data['currency_id'], $walletPriceTotal))
						{
							throw new Exception(
								Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' ' .
								RedshopbHelperUser::getUser($customerOrder->customerId)->name . ': ' .
								Text::_('COM_REDSHOPB_SHOP_FUNDS_ERROR_CAN_NOT_INCREASE_FUNDS')
							);
						}
					}

					if (!RedshopbHelperUser::employeePurchase($data['customer_id'], $totalPrice, $data['currency_id'], $shopperCompany))
					{
						// Wallet funds debit
						throw new Exception(
							Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' ' .
							RedshopbHelperUser::getUser($customerOrder->customerId)->name . ': ' .
							Text::_('COM_REDSHOPB_SHOP_INSUFFICIENT_FUNDS')
						);
					}
				}

				// If we are editing existing order, don't expedite this order
				if ($isNew)
				{
					if ($allowExpedite && $shopperCompany->order_approval)
					{
						$expedition = self::checkOrderExpedite($orderId, $data['delivery_address_id']);
					}
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_UPDATE_SUCCESS'));
				}

				Log::add(
					Text::sprintf(
						'COM_REDSHOPB_LOGS_ORDER', $data['customer_type'], $data['customer_id'],
						($isNew ? Text::_('COM_REDSHOPB_LOGS_ORDER_CREATE') : Text::_('COM_REDSHOPB_LOGS_ORDER_UPDATE')),
						$orderId, RedshopbEntityOrder::getInstance()->getStatusName($data['status'])
					), Log::INFO, 'ORDER'
				);

				$app->triggerEvent('onRedshopbAfterOrderStore', array($orderId, __METHOD__));
			}
			else
			{
				throw new Exception($modelOrder->getError());
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			$db->transactionRollback();
			$orderId = 0;
		}

		return $orderId;
	}

	/**
	 * Checks the address that needs to be expedited and calls the expedition process
	 *
	 * @param   int $orderId           ID of the order
	 * @param   int $deliveryAddressId Delivery Address ID of the order
	 *
	 * @return boolean
	 */
	public static function checkOrderExpedite($orderId, $deliveryAddressId)
	{
		if (!RedshopbEntityConfig::getInstance()->get('order_expedition', 0))
		{
			return false;
		}

		/** @var \RedshopbModelOrders $modelOrders */
		$modelOrders   = RedshopbModel::getFrontInstance('Orders');
		$vendorCompany = static::getVendorbyOrderId($orderId);

		if (!$vendorCompany->order_approval)
		{
			return $modelOrders->expediteOrders(array($orderId));
		}

		$customer          = RedshopbEntityCustomer::getInstance($vendorCompany->id, RedshopbEntityCustomer::TYPE_COMPANY);
		$expediteAddressId = $customer->getDeliveryAddress()->id;

		if (!$expediteAddressId)
		{
			$expediteAddressId = $deliveryAddressId;
		}

		return $modelOrders->expediteOrders(array($orderId), $expediteAddressId);
	}

	/**
	 * Determines if the logged in user can change the status of an order
	 *
	 * @param   integer  $orderId  Order id
	 *
	 * @return  boolean
	 */
	public static function canChangeStatus($orderId)
	{
		// Super admins can change status, always
		if (RedshopbHelperACL::isSuperAdmin())
		{
			return true;
		}

		$user = RedshopbHelperUser::getUser();

		// Non-super users which are not b2b users cannot change status
		if (!$user || is_null($user->company))
		{
			return false;
		}

		// Purchasers cannot change their own orders
		if (static::getPurchaserByOrderId($orderId) == $user->company)
		{
			return false;
		}

		$company        = RedshopbHelperCompany::getCompanyById($user->company);
		$companyAssetId = $company->asset_id;

		// Checks the proper ACL permission on any other case (b2b and non super user)
		return RedshopbHelperACL::getPermission('statusupdate', 'order', array(), true, $companyAssetId);
	}

	/**
	 * GetPaymentExtraInformation
	 *
	 * @param   int $orderId Order id
	 *
	 * @return  array
	 */
	public static function getPaymentExtraInformation($orderId)
	{
		$order = RedshopbEntityOrder::getInstance($orderId);

		if (!$order->isValid() || empty($order->get('payment_name', '')))
		{
			return array();
		}

		$paymentFields = array();

		$params = new Registry($order->params);

		$paymentNameParams = explode('!', $order->get('payment_name'));
		PluginHelper::importPlugin('redpayment', $paymentNameParams[0]);
		$results = RFactory::getDispatcher()->trigger('onRedpaymentMaskForRenderExtraParameters');

		if (empty($results))
		{
			return array();
		}

		foreach ($results as $result)
		{
			if (!is_array($result))
			{
				continue;
			}

			foreach ($result as $oneField)
			{
				if (!$params->exists($oneField->key))
				{
					continue;
				}

				$oneField->value               = $params->get($oneField->key);
				$paymentFields[$oneField->key] = $oneField;
			}
		}

		return $paymentFields;
	}

	/**
	 * Get Payment Extra Information From Payment Name
	 *
	 * @param   string $paymentName Payment name
	 *
	 * @return array
	 */
	public static function getPaymentExtraInformationFromPaymentName($paymentName)
	{
		$paymentNameParams = explode('!', $paymentName);
		PluginHelper::importPlugin('redpayment', $paymentNameParams[0]);
		$results       = RFactory::getDispatcher()->trigger('onRedpaymentMaskForRenderExtraParameters');
		$paymentFields = array();

		$app = Factory::getApplication();

		$extension = '';

		if (isset($paymentNameParams[1]))
		{
			$extension = '.' . $paymentNameParams[1];
		}

		$paymentParameters = $app->getUserState('redpayment.' . $paymentNameParams[0] . $extension . '.data', array());
		$params            = new Registry(array('payment' => $paymentParameters));

		if (empty($results))
		{
			return array();
		}

		foreach ($results as $result)
		{
			if (!is_array($result))
			{
				continue;
			}

			foreach ($result as $oneField)
			{
				if (!$params->exists($oneField->key))
				{
					continue;
				}

				$oneField->value               = $params->get($oneField->key);
				$paymentFields[$oneField->key] = $oneField;
			}
		}

		return $paymentFields;
	}
	/**
	 * Method to get shipping detail.
	 *
	 * @param   object  $item  The id of the primary key.
	 *
	 * @return  void
	 */
	public static function loadShippingDetails(&$item)
	{
		if (property_exists($item, 'shipping_details'))
		{
			$registry = new Registry;
			$registry->loadString($item->shipping_details);
			$item->shipping_details = $registry->toArray();
		}
	}
	/**
	 * Method to get shipping detail from stockroom.
	 *
	 * @param   object  $item  The id of the primary key.
	 *
	 * @return  void
	 */
	public static function loadShippingDetailsStockroom(&$item)
	{
		if (array_key_exists('pickup_stockroom_id', $item->shipping_details))
		{
			$itemData                      = new stdClass;
			$itemData->pickup_stockroom_id = $item->shipping_details['pickup_stockroom_id'];
			$fieldModel                    = RedshopbModel::getAdminInstance('Stockroom');
			$fieldTable                    = $fieldModel->getTable();
			$fieldModel->addWSItemData(
				$itemData, 'pickup_stockroom_id', $fieldTable->get('_tbl'), $fieldTable->getKeyName(), $fieldTable->get('wsSyncMapPK')
			);
			$item->shipping_details['pickup_stockroom_id_others'] = $itemData->shipping_details_syncref;
		}
	}
	/**
	 * Method to get author employee ID.
	 *
	 * @param   object  $item  The id of the primary key.
	 *
	 * @return  void
	 */
	public static function loadAuthorEmployeeId(&$item)
	{
		if (isset($item->created_by))
		{
			$employee = RedshopbEntityUser::getInstance()->loadItem('joomla_user_id', $item->created_by);

			$item->author_employee_id = $employee->isLoaded() ? $employee->getId() : null;
		}
	}
	/**
	 * Method to get shipping detail.
	 *
	 * @param   object   $db      Database data.
	 * @param   object   $query   The id of the primary key.
	 * @param   array    $result  Message if not successful in creating db .
	 *
	 * @return  boolean
	 */
	public static function executeDbQueryError($db, $query, $result)
	{
		if (!$db->setQuery($query)->execute())
		{
			$db->transactionRollback();
			$result['success'] = 0;
			$result['msg']     = $db->getErrorMsg();

			return false;
		}

		return true;
	}
	/**
	 * Query to get guest customer email.
	 *
	 * @param   RTable   $order   Order information.
	 *
	 * @return  false|array
	 */
	public static function getGuestMailRecipient($order)
	{
		$db     = Factory::getDbo();
		$result = array();
		$query  = $db->getQuery(true)
			->select(
				array(
					$db->qn('a.email', 'email'),
					$db->q('company') . ' AS ' . $db->qn('userType'),
					$db->q($order->customer_id) . ' AS ' . $db->qn('company')
				)
			)
			->from($db->qn('#__redshopb_address', 'a'))
			->where($db->qn('a.id') . ' = ' . (int) $order->delivery_address_id);

		$users = $db->setQuery($query)->loadObjectList();

		if (empty($users))
		{
			return false;
		}

		foreach ($users as $user)
		{
			$result['recipients'][] = $user->email;
		}

		return $result;
	}
	/**
	 * Query to get admin email.
	 *
	 * @param   object   $db       Database data.
	 * @param   object   $company  Object data with company values.
	 * @param   array    $unions   Array data with user data.
	 *
	 * @return  void
	 */
	public static function getAdminMailRecipient($db, $company, &$unions)
	{
		$subQuery = $db->getQuery(true)
			->select(
				array(
					$db->qn('ju.email', 'email'),
					$db->qn('rt.type', 'userType'),
					$db->qn('c.id', 'company')
				)
			)
			->from($db->qn('#__redshopb_user', 'ru'))
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
			->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
			->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('umc.company_id'))
			->where($db->qn('rt.type') . ' = ' . $db->q('admin'))
			->where($db->qn('c.id') . ' = ' . (int) $company->id)
			->where('ru.use_company_email = 0')
			->where('ru.send_email = 1');
		$unions[] = $subQuery;
	}
	/**
	 * Query to get admin email.
	 *
	 * @param   object   $db           Database data.
	 * @param   int      $orderAuthor  Order author id.
	 * @param   array    $unions       Array data with user data.
	 *
	 * @return  void
	 */
	public static function getAuthorMailRecipient($db, $orderAuthor, &$unions)
	{
		if ($orderAuthor)
		{
			$subQuery = $db->getQuery(true)
				->select(
					array(
						$db->qn('ju.email', 'email'),
						$db->qn('rt.type', 'userType'),
						$db->qn('c.id', 'company')
					)
				)
				->from($db->qn('#__users', 'ju'))
				->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
				->leftJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
				->leftJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
				->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('r.company_id'))
				->leftJoin($db->qn('#__redshopb_user', 'ru') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ru.joomla_user_id'))
				->where($db->qn('ju.id') . ' = ' . (int) $orderAuthor)
				->where('(ru.use_company_email = 0 OR c.id IS NULL)')
				->where('(ru.send_email = 1 OR c.id IS NULL)');
			$unions[] = $subQuery;
		}
	}
	/**
	 * Gathering all users.
	 *
	 * @param   object   $db      Database data.
	 * @param   array    $unions  Array data with user data.
	 *
	 * @return  false|array
	 */
	public static function getAllUsers($db, $unions)
	{
		$query = array_shift($unions);

		if (!empty($unions))
		{
			foreach ($unions as $union)
			{
				$query->unionDistinct($union);
			}
		}

		$users = $db->setQuery($query)
			->loadObjectList('email');

		return $users;
	}
	/**
	 * Send order/return order mail helper settings.
	 *
	 * @param   object  $order     Database data.
	 * @param   int     $orderId   Array data with user data.
	 * @param   string  $body      Database data.
	 * @param   string  $scheme    Database data.
	 * @param   string  $host      Database data.
	 * @param   Mail    $mailer    Database data.
	 *
	 * @return  void
	 */
	public static function sendMailHelperSettings($order, &$orderId, &$body, &$scheme, &$host, &$mailer)
	{
		$orderId     = $order->id;
		$mainCompany = RedshopbHelperCompany::getMain();
		$sender      = RedshopbApp::getConfig()->get('mailfrom', '');
		$uri         = Uri::getInstance();
		$scheme      = $uri->getScheme();
		$host        = $uri->getHost();

		if ($sender == '')
		{
			$jConfig = Factory::getConfig();
			$sender  = $jConfig->get('mailfrom');
		}

		$mailer = RFactory::getMailer();
		$mailer->setSender(array($sender, $mainCompany->name));
		$body = '';

		$body .= '<style>'
			. file_get_contents(JPATH_ROOT . '/media/com_redshopb/css/mailorder.css')
			. '</style>';

		$body .= RedshopbLayoutHelper::render('shop.checkout.emailheader');

		$body .= '<div class="all">';
	}
	/**
	 * Send order/return order mail helper 2.
	 *
	 * @param   object  $body              Database data.
	 * @param   string  $text              Array data with user data.
	 * @param   object  $customerCompany   Database data.
	 * @param   string  $host              Database data.
	 * @param   string  $scheme            Database data.
	 * @param   int     $orderId           Database data.
	 * @param   string  $view              View for the order link
	 * @param   string  $layout            Layout for the order link
	 * @param   bool    $appendOrderId     Should the order id be appended to the route?
	 *
	 * @return  void
	 */
	public static function sendMailHelperBodyText(&$body, $text, $customerCompany, $host, $scheme, $orderId, $view, $layout = null, $appendOrderId = false)
	{
		$route = 'index.php?option=com_redshopb&view=' . $view;

		if (null !== $layout)
		{
			$route .= '&layout=' . $layout;
		}

		if (true === $appendOrderId)
		{
			$route .= '&orderId=' . $orderId;
		}

		$token = RedshopbEntityOrder::load($orderId)->get('token');

		if (null !== $token)
		{
			$route .= '&token=' . $token;
		}

		$body .= RedshopbLayoutFile::getInstance(
			'email.order_body_text'
		)->render(
			array(
			'text' => $text,
			'customerCompany' => $customerCompany->name,
			'host' => $host,
			'orderLink' => $scheme . '://' . $host . RedshopbRoute::_($route),
			'orderId' => str_pad($orderId, 6, '0', STR_PAD_LEFT)
			)
		);
	}
	/**
	 * Send order/return order mail helper Layout render.
	 *
	 * @param   string  $body           Database data.
	 * @param   object  $customerOrder  Array data with user data.
	 * @param   int     $orderId        Database data.
	 * @param   string  $modelOrders    Database data.
	 *
	 * @return  void
	 */
	public static function sendMailHelperLayoutRender(&$body, $customerOrder, $orderId, $modelOrders)
	{
		$config = RedshopbEntityConfig::getInstance();

		$body .=
			RedshopbLayoutHelper::render(
				'checkout.customer_basket',
				array(
					'customerOrders' => array($customerOrder),
					'orderId'        => $orderId,
					'form'           => $modelOrders->getCustomForm('cartitems'),
					'showToolbar'    => false,
					'edit'           => false,
					'checkbox'       => false,
					'quantityfield'  => 'quantity',
					'pdf'            => false,
					'lockquantity'   => true,
					'output'         => 'email',
					'isEmail'        => true,
					'delivery'       => $config->get('stockroom_delivery_time', 'hour')
				)
			);
	}
	/**
	 * Send order/return order mail helper 4.
	 *
	 * @param   string  $body            Database data.
	 * @param   string  $scheme          Array data with user data.
	 * @param   string  $host            Database data.
	 * @param   string  $languageString  Database data.
	 * @param   string  $rout            Database data.
	 * @param   Mail    $mailer          Database data.
	 * @param   array   $recipients      Database data.
	 * @param   string  $subject         Database data.
	 *
	 * @return  void
	 */
	public static function sendMailHelperFooter(&$body, $scheme, $host, $languageString, $rout, &$mailer, $recipients, $subject)
	{
		$body .= '<p>' .
			sprintf(
				Text::_($languageString),
				$scheme . '://' . $host . RedshopbRoute::_('index.php?option=com_redshopb&view=' . $rout)
			)
			. '</p>';

		$body .= '</div>';

		$body .= RedshopbLayoutHelper::render('shop.checkout.emailfooter');

		$mailer->addRecipient($recipients['recipients']);
		$mailer->addCc($recipients['ccs']);

		$mailer->isHtml(true);
		$mailer->Encoding = 'base64';
		$mailer->setSubject($subject);
		$mailer->setBody($body);
	}

	/**
	 * isShippingDateAvailable
	 *
	 * @param   string   $value         Checking date
	 * @param   string   $customerType  Customer type
	 * @param   integer  $customerId    Customer id
	 *
	 * @return boolean
	 * @since  1.12.82
	 */
	public static function isShippingDateAvailable($value, $customerType, $customerId)
	{
		$date           = new DateTime($value);
		$redshopbConfig = RedshopbApp::getConfig();
		$weekDay        = $date->format('w');
		$minDate        = $redshopbConfig->getInt('shipping_skip_from_today', 0);

		$minimumShippingDate = new DateTime;

		if ($minDate)
		{
			$minimumShippingDate->modify('+' . $minDate . 'day');
		}

		if ($minimumShippingDate->format('Ymd') > $date->format('Ymd'))
		{
			return false;
		}

		if (!$redshopbConfig->getBool('shipping_include_saturday', 1) && $weekDay == 6)
		{
			return false;
		}

		if (!$redshopbConfig->getBool('shipping_include_sunday', 1) && $weekDay == 0)
		{
			return false;
		}

		if (!$redshopbConfig->getBool('shipping_include_day_off', 1))
		{
			/** @var RedshopbEntityCompany $vendor */
			$vendor   = RedshopbHelperCompany::getVendorCompanyByCustomer($customerId, $customerType);
			$holidays = RedshopbHelperStockroom::getHolidays($vendor->getAddress()->get('country_id'));

			if (in_array($date->format('m-d'), $holidays['annual'])
				|| in_array($date->format('Y-m-d'), $holidays['date']))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * getSpecificCartProducts
	 *
	 * @param   object   $oneCustomerOrder  One customer order
	 * @param   boolean  $getDelay          Set true is delay products needed
	 *
	 * @return object
	 * @since 1.12.82
	 */
	public static function getSpecificCartProducts($oneCustomerOrder, $getDelay = false)
	{
		if (empty($oneCustomerOrder->items))
		{
			return $oneCustomerOrder;
		}

		$customerOrder = clone $oneCustomerOrder;

		foreach ($customerOrder->items as $key => $oneProduct)
		{
			if (isset($oneProduct->params)
				&& $oneProduct->params instanceof Joomla\Registry\Registry
				&& $oneProduct->params->get('delayed_order', 0) == ($getDelay ? 0 : 1))
			{
				$customerOrder->total      -= $oneProduct->final_price;
				$customerOrder->totalFinal -= $oneProduct->final_price;

				if (isset($oneProduct->hiddenPrice))
				{
					$minusPrice = $oneProduct->hiddenPrice * $oneProduct->quantity;

					if (isset($customerOrder->hiddenAccessoriesPrice))
					{
						$minusPrice += $customerOrder->hiddenAccessoriesPrice * $oneProduct->quantity;
					}

					$customerOrder->hiddenTotal      -= $minusPrice;
					$customerOrder->hiddenTotalFinal -= $minusPrice;
				}

				unset($customerOrder->items[$key]);
			}
		}

		$customerOrder->items = array_values($customerOrder->items);

		return $customerOrder;
	}

	/**
	 * Adds the invoice email to the recipients
	 *
	 * @param   JMail   $mailer   Mailer used for adding the recipient
	 *
	 * @return boolean
	 */
	private static function addAlternateInvoiceRecipient(&$mailer)
	{
		$app = Factory::getApplication();

		$invoiceEmail = $app->getUserState('checkout.invoice_email', '');

		if (empty($invoiceEmail))
		{
			return false;
		}

		$mailer->addRecipient($invoiceEmail);

		return true;
	}
}
