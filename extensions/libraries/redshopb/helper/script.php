<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Config
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Redshopb Configuration
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Config
 * @since       1.6.25
 */
class RedshopbHelperScript
{
	/**
	 * javascript strings for configuration variables
	 *
	 * @var    array
	 */
	protected static $jsStrings = array();

	/**
	 * Stores redshopb configuration strings in the JavaScript language store.
	 *
	 * @param   string  $key    The Javascript config string key.
	 * @param   string  $value  The Javascript config string value.
	 *
	 * @return  string
	 */
	public static function script($key = null, $value = null)
	{
		// Add the key to the array if not null.
		if ($key !== null)
		{
			// Assign key to the value
			self::$jsStrings[strtoupper($key)] = $value;
		}

		return self::$jsStrings;
	}

	/**
	 * Set javascript strings
	 *
	 * @return  void
	 */
	public static function scriptDeclaration()
	{
		static $isLoad = false;

		if ($isLoad)
		{
			return;
		}

		HTMLHelper::script('com_redshopb/redshopb.js', array('framework' => false, 'relative' => true));

		Factory::getDocument()->addScriptDeclaration('
			(function() {
				var RedshopStrings = ' . json_encode(self::script()) . ';
				if (typeof redSHOPB == "undefined") {
					redSHOPB = {};
					redSHOPB.RSConfig = {
						configStrings: {},
						\'_\': function(key, def) {
							return typeof this.configStrings[key.toUpperCase()] !== \'undefined\' ? this.configStrings[key.toUpperCase()] : def;
						},
						load: function(object) {
							for (var key in object) {
								this.configStrings[key.toUpperCase()] = object[key];
							}

							return this;
						}
					};
					redSHOPB.RSConfig.strings = RedshopStrings;
				}
				else {
					redSHOPB.RSConfig.load(RedshopStrings);
				}
			})();'
		);
		$isLoad = true;
	}
}
