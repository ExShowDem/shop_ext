<?php
namespace Page\Frontend;

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Steps Class
 * @copyright   Copyright (C) 2012 - 2019 Aesir. E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class HolidaysPage
 * @package Page\Frontend
 * @since 2.5.1
 */
class HolidaysPage extends Redshopb2bPage
{
	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $URL = '/index.php?option=com_redshopb&view=holidays';

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $day = '#jform_day';

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $month = '#jform_month';

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $year = '#jform_year';

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $searchID = 'filter_search_holidays';

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $warningDay = 'Day should be an integer between 1 and 31';

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $warningMonth = 'Month should be an integer between 1 and 12';
}