<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Unit Measure Entity.
 *
 * @since  2.0
 */
class RedshopbEntityUnit_Measure extends RedshopbEntity
{
	/**
	 * Get item from the database
	 *
	 * @return  mixed  Object / null
	 */
	public function getItem()
	{
		$item = parent::getItem();

		if (!$item)
		{
			$item = new stdClass;

			$item->step             = 'any';
			$item->decimal_position = 0;
			$this->item             = $item;

			return $item;
		}

		if (!isset($this->item->step))
		{
			$this->item->step = substr(number_format(0, $this->item->decimal_position), 0, -1) . '1';
		}

		return $this->item;
	}
}
