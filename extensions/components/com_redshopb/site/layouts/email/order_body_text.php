<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = (object) $displayData;


echo sprintf(
	nl2br($data->text),
	$data->customerCompany,
	$data->host,
	$data->orderLink,
	$data->orderId
);

