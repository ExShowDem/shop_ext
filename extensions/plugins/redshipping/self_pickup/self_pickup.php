<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Self pickup Redshipping plugin.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Shipping
 * @since       1.6
 */
class PlgRedshippingSelf_Pickup extends RedshopbShippingPluginBase
{
	/**
	 * @var string
	 */
	protected $shippingName = 'self_pickup';
}
