<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Return Order Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReturn_Order extends RedshopbModelAdmin
{
	/**
	 * Get Products form field with selected order id values
	 *
	 * @return  string
	 */
	public function getProductsFormField()
	{
		$this->getState();
		$form    = $this->getForm();
		$orderId = Factory::getApplication()->input->get('order_id', null);

		if (empty($orderId))
		{
			return Text::_('COM_REDSHOPB_ORDER_MISSING');
		}
		else
		{
			$form->setFieldAttribute('order_item_id', 'order_id', $orderId);

			return $form->getInput('order_item_id');
		}
	}
}
