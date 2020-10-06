<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Rbchosen HTML class.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Html
 * @since       0.6.8
 */
abstract class JHtmlRbchosen
{
	/**
	 * Array containing information for loaded files
	 *
	 * @var  array
	 */
	protected static $loaded = false;

	/**
	 * Load the chosen library
	 * We use this to avoid Mootools dependency
	 *
	 * @param   string  $selector  CSS Selector to initalise selects
	 * @param   mixed   $debug     Enable debug mode?
	 *
	 * @return  void
	 *
	 * @since   0.6.8
	 */
	public static function chosen($selector = '.chosen', $debug = null)
	{
		// Only load once
		if (static::$loaded)
		{
			return;
		}

		// Add chosen.jquery.js language strings
		Text::script('JGLOBAL_SELECT_SOME_OPTIONS');
		Text::script('JGLOBAL_SELECT_AN_OPTION');
		Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');

		RHelperAsset::load('lib/chosen.jquery.min.js', 'redcore');
		RHelperAsset::load('lib/chosen.min.css', 'redcore');

		Factory::getDocument()->addScriptDeclaration("
			(function($){
				$(document).ready(function () {
					$('" . $selector . "').chosen({
						disable_search_threshold : 10,
						allow_single_deselect : true,
						width : '95%'
					});
				});
			})(jQuery);"
		);

		static::$loaded = true;

		return null;
	}
}
