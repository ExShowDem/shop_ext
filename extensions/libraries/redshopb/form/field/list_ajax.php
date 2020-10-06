<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Form.Field
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

JLoader::import('redshopb.library');

/**
 * AJAX list field.
 *
 * @since  3.0
 */
class RedshopbFormFieldList_Ajax extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'List_Ajax';

	/**
	 * @var  string
	 */
	protected $layout = 'redshopb.field.select2_ajax';

	/**
	 * Cached array of the items.
	 *
	 * @var  array
	 */
	protected static $options = array();

	/**
	 * @var integer
	 */
	protected $limit = 5;

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$layout             = !empty($this->element['layout']) ? (string) $this->element['layout'] : $this->layout;
		$limit              = !empty($this->element['limit']) ? (string) $this->element['limit'] : $this->limit;
		$resetFilterChanged = isset($this->element['resetWhenDynamicFilterChanged'])
			? RHelperString::toBool((string) $this->element['resetWhenDynamicFilterChanged']) : false;

		return RedshopbLayoutHelper::render(
			$layout,
			array(
				'id'             => $this->id,
				'disabled'       => $this->disabled,
				'dynamicFilters' => $this->getDynamicFilters(),
				'resetWhenDynamicFilterChanged' => $resetFilterChanged,
				'element'        => $this->element,
				'field'          => $this,
				'multiple'       => $this->multiple,
				'name'           => $this->name,
				'options'        => (array) $this->getOptions(),
				'readonly'       => $this->readonly,
				'required'       => $this->required,
				'value'          => $this->value,
				'limit'          => $limit,
				'hint'           => $this->translateHint ? Text::_($this->hint) : $this->hint
			)
		);
	}

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$hash = md5($this->name . $this->element);
		$type = strtolower($this->type);

		if (isset(static::$options[$type][$hash]))
		{
			return static::$options[$type][$hash];
		}

		$items          = array();
		$formattedItems = array();
		$valuesInList   = array();
		$allowCreate    = isset($this->element['allowCreate']) ? RHelperString::toBool((string) $this->element['allowCreate']) : false;
		$identifier     = isset($this->element['identifier']) ? (string) $this->element['identifier'] : 'id';

		if ($this->value)
		{
			$model = RedshopbModel::getInstanceFromString((string) $this->element['model']);

			$modelState = array(
				'filter.' . $identifier => $this->value
			);

			$items = $model->search($modelState);
		}

		foreach ($items as $item)
		{
			$value            = $item->{$identifier};
			$text             = $item->{$this->element['property']};
			$formattedItems[] = (object) array('value' => $value, 'text' => $text);

			if (!is_array($this->value))
			{
				if ($this->value == $value)
				{
					$valuesInList[] = $value;
				}
			}
			else
			{
				if (in_array($value, $this->value))
				{
					$valuesInList[] = $value;
				}
			}
		}

		if ($this->value && $allowCreate && count($valuesInList) < count((array) $this->value))
		{
			if (is_array($this->value))
			{
				foreach ($this->value as $item)
				{
					if (!in_array($item, $valuesInList))
					{
						array_unshift($formattedItems, (object) array('value' => $item, 'text' => $item));
					}
				}
			}
			else
			{
				array_unshift($formattedItems, (object) array('value' => $this->value, 'text' => $this->value));
			}
		}

		static::$options[$type][$hash] = $formattedItems;

		return $formattedItems;
	}

	/**
	 * Get dynamic filters applied to this field.
	 *
	 * @return  array
	 */
	protected function getDynamicFilters()
	{
		$filtersJson = $this->getAttribute('dynamicFilters', null);

		if (empty($filtersJson))
		{
			return array();
		}

		return json_decode(str_replace('\'', '"', $filtersJson), true);
	}
}
