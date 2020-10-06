<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;

JLoader::import('redshopb.library');

PluginHelper::importPlugin('vanir');

Form::addFormPath(JPATH_SITE . '/components/com_redshopb/models/forms');
RedshopbModel::addIncludePath(JPATH_SITE . '/components/com_redshopb/models');
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

/**
 * Payment helper class.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  helper.redpayment
 * @since       1.6
 */
class RApiPaymentExtensionHelperCom_Redshopb
{
	/**
	 * On payment status change
	 *
	 * @param   object  $paymentOriginal  Old (original) payment object
	 * @param   object  $paymentNew       New payment object
	 *
	 * @return  null
	 */
	public function paymentStatusChanged($paymentOriginal, $paymentNew)
	{
		// Save status as before, we do nothing
		if ($paymentOriginal->status == $paymentNew->status)
		{
			return null;
		}

		$status = $this->getVanirStatus($paymentNew->status);
		$app    = Factory::getApplication();

		if (is_null($status))
		{
			$app->triggerEvent('onRedshopbPaymentStatusChanged', array($paymentOriginal, $paymentNew, $status));

			return null;
		}

		if ($paymentOriginal->status == RApiPaymentStatus::getStatusAuthorized() && $paymentNew->status == RApiPaymentStatus::getStatusCreated())
		{
			// IPN was faster than our redirect
			return null;
		}

		$orderData                     = array();
		$orderData['id']               = $paymentNew->order_id;
		$orderData['status']           = $status;
		$orderData['payment_status']   = $paymentNew->status;
		$orderData['total_price_paid'] = $status == 1 ? $paymentNew->amount_paid : 0;

		$table = RTable::getAdminInstance('Order', array(), 'com_redshopb');
		$table->save($orderData);

		if ($status == 2)
		{
			// Free child orders from canceled collection or expedition
			RedshopbHelperOrder::freeChildOrdersFromCanceledProcess($paymentNew->order_id, $status, false);
		}

		$plugin = RApiPaymentHelper::getPaymentParams($paymentNew->payment_name, $paymentNew->extension_name, $paymentNew->owner_name);

		if (isset($paymentNew->order_id) && $plugin)
		{
			// If we have enabled always sending order notifications, we do not need to send it again after paying
			$config              = RedshopbApp::getConfig();
			$ignorePaymentStatus = $config->getBool('order_notification_disregard_payment_status', false);

			// Send order details mail after complete online payment
			if (!$ignorePaymentStatus
				&& $paymentNew->status == RApiPaymentStatus::getStatusAuthorized()
				&& !in_array($plugin->element, array('bank_transfer', 'ean')))
			{
				$sendEmail = true;

				$app->triggerEvent(
					'onBeforeSendEmailRedshopbPaymentStatusConfirmed',
					array($paymentNew, $paymentOriginal, $status, &$sendEmail)
				);

				if ($sendEmail)
				{
					RedshopbHelperOrder::sendMail($paymentNew->order_id);
				}
			}
		}

		$app->triggerEvent('onRedshopbPaymentStatusChanged', array($paymentOriginal, $paymentNew, $status));
	}

	/**
	 * Method to convert the Payment status to a vanir status code
	 *
	 * @param   string  $paymentStatus  payment status
	 *
	 * @return integer|null
	 */
	private function getVanirStatus($paymentStatus)
	{
		$status = null;

		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
		switch ($paymentStatus)
		{
			case RApiPaymentStatus::getStatusAuthorized():
			case RApiPaymentStatus::getStatusCompleted():
			case RApiPaymentStatus::getStatusCanceled_Reversal():
				$status = 1;

				break;
			case RApiPaymentStatus::getStatusFailed():
			case RApiPaymentStatus::getStatusExpired():
			case RApiPaymentStatus::getStatusDenied():
				$status = 2;

				break;
			case RApiPaymentStatus::getStatusCreated():
			case RApiPaymentStatus::getStatusPending():
			case RApiPaymentStatus::getStatusProcessed():
				$status = 0;

				break;
			case RApiPaymentStatus::getStatusRefunded():
			case RApiPaymentStatus::getStatusReversed():
				$status = 3;

				break;
		}

		return $status;
	}

	/**
	 * On after handle refund payment from payment gateway
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 * @param   bool    $isRefunded   If refund is successful then this flag should be true
	 *
	 * @return  void
	 */
	public function afterHandleRefundPayment($ownerName, $paymentName, $data, $isRefunded)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}

	/**
	 * On after handle capture payment from payment gateway
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 * @param   bool    $isCaptured   If process is successful then this flag should be true
	 *
	 * @return  void
	 */
	public function afterHandleCapturePayment($ownerName, $paymentName, $data, $isCaptured)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}

	/**
	 * On after handle delete payment from payment gateway
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 * @param   bool    $isDeleted    If process is successful then this flag should be true
	 *
	 * @return  void
	 */
	public function afterHandleDeletePayment($ownerName, $paymentName, $data, $isDeleted)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}

	/**
	 * On after handle process authorization from payment gateway
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 * @param   bool    $isAccepted   If process is successful then this flag should be true
	 *
	 * @return  void
	 */
	public function afterHandleProcess($ownerName, $paymentName, $data, $isAccepted)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}

	/**
	 * On after handle accept request from payment gateway redirect
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 *
	 * @return  void
	 */
	public function afterHandleAcceptRequest($ownerName, $paymentName, $data)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}

	/**
	 * On after handle cancel request from payment gateway redirect
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 *
	 * @return  void
	 */
	public function afterHandleCancelRequest($ownerName, $paymentName, $data)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}

	/**
	 * On after handle callback request from payment gateway IPN
	 *
	 * @param   string  $ownerName    Owner name
	 * @param   string  $paymentName  Payment name
	 * @param   object  $data         Data needed to preform refund
	 *
	 * @return  void
	 */
	public function afterHandleCallback($ownerName, $paymentName, $data)
	{
		// Redshopb order statuses
		// 0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level
	}
}
