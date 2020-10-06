<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Filesync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Uri\Uri;

/**
 * Transform api output
 *
 * @package  Redshopb
 *
 * @since    1.6.24
 */
class RbfilesyncHelperTransformImage extends RApiHalTransformBase
{
	/**
	 * Method to transform an internal representation to an external one.
	 *
	 * @param   mixed  $definition  Field definition.
	 *
	 * @return mixed Transformed value.
	 */
	public static function toExternal($definition)
	{
		// This is already converted to image type so we are returning it as is
		if (!is_array($definition))
		{
			return $definition;
		}

		return Uri::root(true) . '/' . RedshopbHelperThumbnail::getFullImagePath($definition['name'], $definition['section']);
	}

	/**
	 * Method to transform an external representation to an internal one.
	 *
	 * @param   mixed  $definition  Field definition.
	 *
	 * @return mixed Transformed value.
	 */
	public static function toInternal($definition)
	{
		// This is already converted to image type so we are returning it as is
		if (!is_array($definition))
		{
			return $definition;
		}

		$id = (int) preg_replace('/\D/', '', $definition['id']);

		if ($definition['fullPath'])
		{
			return RedshopbHelperThumbnail::savingImage(
				$definition['fullPath'], $definition['fullPath'], $id, true, $definition['section']
			);
		}
		else
		{
			return '';
		}
	}
}
