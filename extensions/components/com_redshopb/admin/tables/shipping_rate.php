<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Tag table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableShipping_Rate extends RedshopbTable
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_shipping_rates';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $shipping_configuration_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $countries;

	/**
	 * @var  string
	 */
	public $zip_start;

	/**
	 * @var  string
	 */
	public $zip_end;

	/**
	 * @var  float
	 */
	public $weight_start;

	/**
	 * @var  float
	 */
	public $weight_end;

	/**
	 * @var  float
	 */
	public $volume_start;

	/**
	 * @var  float
	 */
	public $volume_end;

	/**
	 * @var  float
	 */
	public $length_start;

	/**
	 * @var  float
	 */
	public $length_end;

	/**
	 * @var  float
	 */
	public $width_start;

	/**
	 * @var  float
	 */
	public $width_end;

	/**
	 * @var  float
	 */
	public $height_start;

	/**
	 * @var  float
	 */
	public $height_end;

	/**
	 * @var  float
	 */
	public $order_total_start;

	/**
	 * @var  float
	 */
	public $order_total_end;

	/**
	 * @var  string|array
	 */
	public $on_product;

	/**
	 * @var  string|array
	 */
	public $on_product_discount_group;

	/**
	 * @var  string
	 */
	public $on_category;

	/**
	 * @var  integer
	 */
	public $priority;

	/**
	 * @var  float
	 */
	public $price;

	/**
	 * @var  string
	 */
	public $shipping_location_info;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if ((float) $this->weight_end < (float) $this->weight_start)
		{
			$this->setError(
				Text::sprintf('COM_REDSHOPB_SHIPPING_RATE_ERROR_START_LARGER_THAN_END',
					Text::_('COM_REDSHOPB_SHIPPING_RATE_WEIGHT_START_LABEL'),
					Text::_('COM_REDSHOPB_SHIPPING_RATE_WEIGHT_END_LABEL')
				)
			);

			return false;
		}

		if ((float) $this->volume_end < (float) $this->volume_start)
		{
			$this->setError(
				Text::sprintf('COM_REDSHOPB_SHIPPING_RATE_ERROR_START_LARGER_THAN_END',
					Text::_('COM_REDSHOPB_SHIPPING_RATE_VOLUME_START_LABEL'),
					Text::_('COM_REDSHOPB_SHIPPING_RATE_VOLUME_END_LABEL')
				)
			);

			return false;
		}

		if ((float) $this->length_end < (float) $this->length_start)
		{
			$this->setError(
				Text::sprintf('COM_REDSHOPB_SHIPPING_RATE_ERROR_START_LARGER_THAN_END',
					Text::_('COM_REDSHOPB_SHIPPING_RATE_LENGTH_START_LABEL'),
					Text::_('COM_REDSHOPB_SHIPPING_RATE_LENGTH_END_LABEL')
				)
			);

			return false;
		}

		if ((float) $this->width_end < (float) $this->width_start)
		{
			$this->setError(
				Text::sprintf('COM_REDSHOPB_SHIPPING_RATE_ERROR_START_LARGER_THAN_END',
					Text::_('COM_REDSHOPB_SHIPPING_RATE_WIDTH_START_LABEL'),
					Text::_('COM_REDSHOPB_SHIPPING_RATE_WIDTH_END_LABEL')
				)
			);

			return false;
		}

		if ((float) $this->height_end < (float) $this->height_start)
		{
			$this->setError(
				Text::sprintf('COM_REDSHOPB_SHIPPING_RATE_ERROR_START_LARGER_THAN_END',
					Text::_('COM_REDSHOPB_SHIPPING_RATE_HEIGHT_START_LABEL'),
					Text::_('COM_REDSHOPB_SHIPPING_RATE_HEIGHT_END_LABEL')
				)
			);

			return false;
		}

		if ((float) $this->order_total_end < (float) $this->order_total_start)
		{
			$this->setError(
				Text::sprintf('COM_REDSHOPB_SHIPPING_RATE_ERROR_START_LARGER_THAN_END',
					Text::_('COM_REDSHOPB_SHIPPING_RATE_ORDER_TOTAL_START_LABEL'),
					Text::_('COM_REDSHOPB_SHIPPING_RATE_ORDER_TOTAL_END_LABEL')
				)
			);

			return false;
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		$return = parent::bind($src, $ignore);
		$this->setOption('storeNulls', true);

		if (is_array($this->on_product))
		{
			$this->on_product = implode(',', $this->on_product);
		}

		if (is_array($this->on_product_discount_group))
		{
			$this->on_product_discount_group = implode(',', $this->on_product_discount_group);
		}

		if (is_array($this->on_category))
		{
			$this->on_category = implode(',', $this->on_category);
		}

		if (is_array($this->countries))
		{
			$this->countries = implode(',', $this->countries);
		}

		return $return;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		return parent::store($updateNulls);
	}
}
