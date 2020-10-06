<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Type Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelType extends RedshopbModelAdmin
{
	/**
	 * Method to get a single record using the alias as a reference
	 *
	 * @param   string  $alias  The alias to be retrieved
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItemAlias($alias)
	{
		$item = parent::getItemAlias($alias);

		if (!$item)
		{
			return false;
		}

		$item->values = ($item->value_type == 'field_value') ? true : false;

		return $item;
	}
}
