<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$order = RedshopbEntityOrder::load($orderId);

$paymentStatus = RApiPaymentStatus::getStatusUndefined();

if ($order->isLoaded())
{
	$paymentStatus = RApiPaymentStatus::getStatusLabel($order->get('payment_status'));
}

echo $paymentStatus;
