<?php
/**
 * @package     Aesir-E-commerce
 * @subpackage  Page
 * @copyright   Copyright (C) 2016 - 2018 Aesir-E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Frontend;

class tagPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $url = "index.php?option=com_redshopb&view=tags";

	/**
	 * @var string
	 */
	public static $saveSuccessMessage = "Tag successfully submitted.";

	/**
	 * @var string
	 */
	public static $saveEditSuccess = "Tag successfully saved.";

	/**
	 * @var string
	 */
	public static $tagSearch = 'filter_search_tags';

	/**
	 * @var string
	 */
	public static $acceptDelete = "//button[@onclick=\"Joomla.submitbutton('tags.delete')\"]";

	/**
	 * @var string
	 */
	public static $tagModal = "#tagsModal";
}