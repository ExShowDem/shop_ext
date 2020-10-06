<?php
/**
 * @package     Aesir-E-commerce
 * @subpackage  Page
 * @copyright   Copyright (C) 2016 - 2018 Aesir-E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Frontend;
class NewsletterPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = 'index.php?option=com_redshopb&view=newsletter_lists';

	/**
	 * @var string
	 */
	public static $newLetterSearch = 'filter_search_newsletter_lists';
}