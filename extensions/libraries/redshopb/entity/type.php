<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * Type of attribute entity.
 *
 * @since  2.0
 */
class RedshopbEntityType extends RedshopbEntity
{
	/**
	 * This function returns proper value of the field object by the type value property
	 *
	 * @param   int    $typeId             Type Id
	 * @param   mixed  $field              Field object or array
	 * @param   bool   $prepareForDisplay  Field object or array
	 *
	 * @return  string
	 */
	static public function getFieldValue($typeId, $field, $prepareForDisplay = false)
	{
		$value = null;

		$type      = self::getInstance($typeId);
		$valueType = $type->get('value_type', 'string_value');
		$valueType = ($valueType == 'field_value') ? 'string_value' : $valueType;

		if (is_object($field))
		{
			if (isset($field->{$valueType}))
			{
				$value = $field->{$valueType};
			}
		}
		elseif (is_array($field))
		{
			if (isset($field[$valueType]))
			{
				$value = $field[$valueType];
			}
		}

		// If we need to display values in a specific way
		if ($prepareForDisplay)
		{
			$value = self::prepareTypeValueForDisplay($typeId, $value);
		}

		return $value;
	}

	/**
	 * This function prepares Type Value for display
	 *
	 * @param   int    $typeId  Type Id
	 * @param   mixed  $value   Value for display
	 *
	 * @return  string
	 */
	static public function prepareTypeValueForDisplay($typeId, $value)
	{
		$type = self::getInstance($typeId);

		switch ($type->get('alias'))
		{
			case 'radioboolean':
				return ((int) $value) ? Text::_('JYES') : Text::_('JNO');
			default:
				return $value;
		}
	}
}
