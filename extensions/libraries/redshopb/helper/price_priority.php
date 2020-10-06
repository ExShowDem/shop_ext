<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;

/**
 * Redshop Price Priority Helper
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
class RedshopbHelperPrice_Priority
{
	/**
	 * @var integer
	 */
	public $salesType;

	/**
	 * @var integer
	 */
	public $date;

	/**
	 * @var integer
	 */
	public $quantity;

	/**
	 * @var integer
	 */
	public $discount;

	/**
	 * @var array
	 */
	public $orderPriority = array('salesType', 'date', 'quantity', 'discount');

	/**
	 * RedshopbHelperPrice_Priority constructor.
	 *
	 * @param   stdClass  $price  One price object
	 */
	public function __construct($price)
	{
		switch ($price->stype)
		{
			case 'campaign':
				$this->salesType = 4;
				break;
			case 'customer_price':
				$this->salesType = 3;
				break;
			case 'customer_price_group':
				$this->salesType = 2;
				break;
			case 'all_customers':
				$this->salesType = 1;
				break;
			default:
				$this->salesType = 0;
				break;
		}

		if ($price->starting_date != '0000-00-00 00:00:00' && $price->ending_date != '0000-00-00 00:00:00')
		{
			$this->date = 2;
		}
		elseif (($price->starting_date != '0000-00-00 00:00:00' && $price->ending_date == '0000-00-00 00:00:00')
			|| ($price->starting_date == '0000-00-00 00:00:00' && $price->ending_date != '0000-00-00 00:00:00'))
		{
			$this->date = 1;
		}
		else
		{
			$this->date = 0;
		}

		if ($price->quantity_min != null && $price->quantity_max != null)
		{
			$this->quantity = 2;
		}
		elseif (($price->quantity_min != null && $price->quantity_max == null)
			|| ($price->quantity_min == null && $price->quantity_max != null))
		{
			$this->quantity = 1;
		}
		else
		{
			$this->quantity = 0;
		}

		if ($price->allow_discount)
		{
			$this->discount = 1;
		}
		else
		{
			$this->discount = 0;
		}

		Factory::getApplication()
			->triggerEvent('onRedshopbPricePriorityConstructor', array($this, $price));
	}

	/**
	 * Get properties priority
	 *
	 * @return integer
	 */
	public function getPriority()
	{
		$priorityAmount = '';

		foreach ($this->orderPriority as $item)
		{
			if (property_exists($this, $item))
			{
				$priorityAmount .= (int) $this->{$item};
			}
			else
			{
				$priorityAmount .= 0;
			}
		}

		return intval($priorityAmount);
	}

	/**
	 * Get main priority, which related with type of price
	 *
	 * @return integer
	 */
	public function getMainPriority()
	{
		return intval($this->salesType);
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 */
	public function set($property, $value = null)
	{
		$previous        = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;

		return $previous;
	}
}
