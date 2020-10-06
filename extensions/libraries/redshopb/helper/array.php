<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Array utility class for doing all sorts of odds and ends with arrays.
 *
 * @since  2.0
 */
abstract class RedshopbHelperArray
{
	/**
	 * Convert associative array into attributes.
	 * Example:
	 * 		array('size' => '50', 'name' => 'myfield')
	 * 	would be:
	 * 		size="50" name="myfield"
	 *
	 * @param   array  $array  Associative array to convert
	 *
	 * @return  string
	 */
	public static function toAttributes(array $array)
	{
		$attributes = '';

		foreach ($array as $attribute => $value)
		{
			if (null !== $value)
			{
				$attributes .= ' ' . $attribute . '="' . (string) $value . '"';
			}
		}

		return trim($attributes);
	}
}
