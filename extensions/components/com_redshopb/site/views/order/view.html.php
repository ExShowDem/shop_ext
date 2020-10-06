<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
/**
 * Order View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewOrder extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var object
	 */
	protected $state;

	/**
	 * @var  object
	 */
	protected $orderitems;

	/**
	 * Prevents edition from every field except status
	 *
	 * @var  boolean
	 */
	protected $editionBlocked = false;

	/**
	 * Status update allowed
	 *
	 * @var  boolean
	 */
	protected $statusUpdate = true;

	/**
	 * Form fieldset
	 *
	 * @var  string
	 */
	protected $formFieldset = 'order_general';

	/**
	 * Is customer available
	 *
	 * @var  boolean
	 */
	protected $customerAvailable = false;

	/**
	 * Prepared Payment data
	 *
	 * @var object
	 */
	public $paymentData = null;

	/**
	 * @var array  List of Shipping methods available for this user
	 */
	public $shippingMethods = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		/** @var RedshopbModelOrder $model */
		$model       = $this->getModel();
		$this->state = $model->getState();
		$item        = $this->get('Item');

		if ($item)
		{
			$this->customerAvailable = RedshopbHelperShop::checkCustomerAvailable($item->customer_type, $item->customer_id);

			if ($item->status && $this->customerAvailable)
			{
				// If a record exists and status > 0, blocks edition
				$this->editionBlocked = true;

				// If a record exists and status > 0, blocks edition by using the read only fieldset
				$this->formFieldset = 'order_general_readonly';
			}

			$companyId       = $item->company_id;
			$deliveryAddress = RedshopbEntityAddress::getInstance($item->delivery_address_id)->getExtendedData();
			$price           = $item->total_price;
			$currency        = $item->currency;

			$this->shippingMethods = RedshopbHelperOrder::getShippingMethods($companyId, $deliveryAddress, $price, $currency);
		}

		$this->item = $item;

		// State formFieldset use for get right form
		$this->state->set('formFieldset', $this->formFieldset);
		$this->form = $this->get('Form');

		// If the user cannot change status, it disables the status field
		if (!RedshopbHelperOrder::canChangeStatus($this->item->id))
		{
			$this->form->setFieldAttribute('status', 'readonly', 'true');
		}

		/** @var RedshopbModelOrders $ordersModel */
		$ordersModel = RModel::getAdminInstance('Orders');

		$this->customerOrder             = $ordersModel->getCustomerOrder($this->item->id);
		$this->cartItemsForm             = $ordersModel->getCustomForm('cartitems');
		$this->paymentData               = RedshopbHelperOrder::preparePaymentData($this->item, false);
		$url                             = Uri::getInstance()->toString(array('scheme', 'host', 'port'))
			. RedshopbRoute::_('index.php?option=com_redshopb&view=order&layout=edit&id=' . $this->item->id, false);
		$this->paymentData['url_cancel'] = $url;
		$this->paymentData['url_accept'] = $url;

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_ORDER');
		$state = $isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group  = new RToolbarButtonGroup;
		$group2 = new RToolbarButtonGroup('pull-right');

		if (RedshopbHelperACL::getPermission('manage', 'order', Array('edit', 'edit.own'), true)
			&& empty($this->item->collected_by_id)
			&& $this->customerAvailable)
		{
			$save         = RToolbarBuilder::createSaveButton('order.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('order.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			if (!$this->editionBlocked && !RedshopbHelperOrder::isLog($this->item->id))
			{
				$editItems = RToolbarBuilder::createStandardButton(
					'order.editOrderItems',
					Text::_('COM_REDSHOPB_ORDER_CHANGE_ORDER_ITEMS'),
					'btn-success',
					'icon-plus-sign',
					false
				);
				$group2->addButton($editItems);
			}
		}

		$finalStatuses = array(
			RApiPaymentStatus::getStatusCompleted(),
			RApiPaymentStatus::getStatusCanceled_Reversal(),
			RApiPaymentStatus::getStatusDenied(),
			RApiPaymentStatus::getStatusExpired(),
			RApiPaymentStatus::getStatusRefunded(),
			RApiPaymentStatus::getStatusReversed(),
		);

		if (RedshopbHelperACL::isSuperAdmin()
			&& !empty($this->item->payment_name)
			&& !in_array($this->item->payment_status, $finalStatuses))
		{
			$payment       = RApiPaymentHelper::getPaymentByExtensionId('com_redshopb', $this->item->id);
			$onlinePayment = true;

			if ($payment)
			{
				$paymentParams = RApiPaymentHelper::getPaymentParams($payment->payment_name, $payment->extension_name, $payment->owner_name);

				if ($paymentParams && $paymentParams->params->get('offline_payment', 0))
				{
					$onlinePayment = false;
				}
			}

			$group3 = new RToolbarButtonGroup('', true, 'icon-cogs', Text::_('COM_REDSHOPB_ORDER_PAYMENT_OPTIONS'));

			if ($onlinePayment)
			{
				$button = new RToolbarButtonStandard('COM_REDSHOPB_ORDER_PAYMENT_CHECK_PAYMENT', 'order.checkPayment', '', 'icon-refresh', false);
				$group3->addButton($button);

				$button = new RToolbarButtonStandard('COM_REDSHOPB_ORDER_PAYMENT_CAPTURE_PAYMENT', 'order.capturePayment', '', 'icon-money', false);
				$group3->addButton($button);

				$button = new RToolbarButtonStandard('COM_REDSHOPB_ORDER_PAYMENT_REFUND_PAYMENT', 'order.refundPayment', '', 'icon-money', false);
				$group3->addButton($button);

				$button = new RToolbarButtonStandard('COM_REDSHOPB_ORDER_PAYMENT_DELETE_PAYMENT', 'order.deletePayment', '', 'icon-money', false);
				$group3->addButton($button);
			}
			else
			{
				$button = new RToolbarButtonStandard('COM_REDSHOPB_ORDER_PAYMENT_CAPTURE_PAYMENT', 'order.capturePayment', '', 'icon-money', false);
				$group3->addButton($button);

				$button = new RToolbarButtonStandard('COM_REDSHOPB_ORDER_PAYMENT_DELETE_PAYMENT', 'order.deletePayment', '', 'icon-money', false);
				$group3->addButton($button);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('order.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('order.cancel');
		}

		if ($this->item->status == 2 && $this->customerAvailable)
		{
			$refund = RToolbarBuilder::createStandardButton(
				'order.refund',
				Text::_('COM_REDSHOPB_ORDER_REFUND'),
				'btn-primary',
				'icon-money',
				false
			);
			$group2->addButton($refund);
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group)
			->addGroup($group2);

		if (isset($group3))
		{
			$toolbar->addGroup($group3);
		}

		return $toolbar;
	}
}
