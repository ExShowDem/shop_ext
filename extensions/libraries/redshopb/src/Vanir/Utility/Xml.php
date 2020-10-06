<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Utility;

use SimpleXMLElement;

defined('_JEXEC') or die;

/**
 * Class Xml
 *
 * @package  Vanir\Utility
 * @since    2.0
 */
class Xml
{
	/**
	 * Method to get a SimpleXmlElement attributes as an array
	 *
	 * @param   SimpleXMLElement  $xml  node to return attributes for
	 *
	 * @return array
	 */
	public static function getAttributes(SimpleXMLElement $xml)
	{
		$attr = (array) $xml->attributes();

		if (is_null($attr))
		{
			return array();
		}

		return array_shift($attr);
	}
}
