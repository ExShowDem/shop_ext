<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
/**
 * Shipping Rate Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelShipping_Rate extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!empty($item->on_product) && !is_array($item->on_product))
		{
			$item->on_product = explode(',', $item->on_product);
		}

		if (!empty($item->on_product_discount_group) && !is_array($item->on_product_discount_group))
		{
			$item->on_product_discount_group = explode(',', $item->on_product_discount_group);
		}

		if (!empty($item->on_category) && !is_array($item->on_category))
		{
			$item->on_category = explode(',', $item->on_category);
		}

		if (!empty($item->countries) && !is_array($item->countries))
		{
			$item->countries = explode(',', $item->countries);
		}

		return $item;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$isShipper = Factory::getApplication()->input->getInt('is_shipper', 1);

		if ($isShipper)
		{
			$data['shipping_location_info'] = "";
		}
		else
		{
			$data['countries']                 = "";
			$data['zip_start']                 = "";
			$data['zip_end']                   = "";
			$data['weight_start']              = "";
			$data['weight_end']                = "";
			$data['volume_start']              = "";
			$data['volume_end']                = "";
			$data['length_start']              = "";
			$data['length_end']                = "";
			$data['width_start']               = "";
			$data['width_end']                 = "";
			$data['height_start']              = "";
			$data['height_end']                = "";
			$data['order_total_start']         = "";
			$data['order_total_end']           = "";
			$data['on_product']                = "";
			$data['on_product_discount_group'] = "";
			$data['on_category']               = "";
			$data['priority']                  = "0";
			$data['price']                     = "0";
		}

		if (!isset($data['on_product']) || is_null($data['on_product']))
		{
			$data['on_product'] = '';
		}

		if (!isset($data['on_product_discount_group']) || is_null($data['on_product_discount_group']))
		{
			$data['on_product_discount_group'] = '';
		}

		if (!isset($data['on_category']) || is_null($data['on_category']))
		{
			$data['on_category'] = '';
		}

		if (!isset($data['countries']) || is_null($data['countries']))
		{
			$data['countries'] = '';
		}

		$data = parent::validate($form, $data, $group);

		return $data;
	}
}
