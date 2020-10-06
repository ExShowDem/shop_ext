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
 * A Report helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperReport
{
	/**
	 * Get the role types ids.
	 *
	 * @param   int  $periodGroup  Selected period group
	 *
	 * @return  string  An array of role type id as keys and name as values.
	 */
	public static function getDatePeriodFormat($periodGroup = 1)
	{
		switch ($periodGroup)
		{
			case 0:
				return 'd F Y';
			case 1:
				return 'F Y';
			case 2:
				return 'Y';
			default:
				return 'F Y';
		}
	}
}
