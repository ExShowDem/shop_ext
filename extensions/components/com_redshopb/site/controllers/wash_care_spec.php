<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Wash and care spec Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerWash_Care_Spec extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_WASH_AND_CARE_SPEC';

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append      = parent::getRedirectToListAppend();
		$fromProduct = RedshopbInput::isFromProduct();

		// Append the tab name for the product view
		if ($fromProduct)
		{
			$append .= '&tab=washcarespecs';
		}

		// Append the Id for the product view
		$productId = RedshopbInput::getProductIdForm();

		if ($productId)
		{
			$append .= '&product_id=' . $productId;
		}

		return $append;
	}
}
