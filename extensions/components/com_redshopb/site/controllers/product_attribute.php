<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Product Attribute Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct_Attribute extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_PRODUCT_ATTRIBUTE';

	/**
	 * Get the Route object for a redirect to item.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToItemRoute($append = null)
	{
		$redirectUrl = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $append;
		$productId   = $this->input->get('product_id', 0, 'int');

		if ($productId)
		{
			$redirectUrl .= '&product_id=' . (int) $productId;
		}

		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			$redirectUrl .= '&return=' . $return;
		}

		return RedshopbRoute::_($redirectUrl, false);
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
		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			return RedshopbRoute::_(base64_decode($return), false);
		}

		return parent::getRedirectToListRoute($append);
	}

	/**
	 * Overridden to normalize the input for WS
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->normalizeImageInput();

		return parent::save($key, $urlVar);
	}
}
