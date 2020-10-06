<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('rlist');

/**
 * CategoriesProductsSort Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCategoryProductsSort extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since   1.13.0
	 */
	public $type = 'CategoryProductsSort';

	/**
	 * @var string
	 */
	protected $configLayout = 'category';

	/**
	 * Method to attach a Joomla\CMS\Form\Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.13.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		$this->__set('configLayout', $element['configLayout']);

		return true;
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'configLayout':
				$this->$name = (string) $value;
				break;
			default:
				parent::__set($name, $value);
				break;
		}
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.13.0
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options     = (array) $this->getOptions();
		$matchOption = false;

		if ($this->value)
		{
			foreach ($options as $option)
			{
				if ($this->value == $option->value)
				{
					$matchOption = true;
					break;
				}
			}
		}

		if (!$matchOption)
		{
			$option      = reset($options);
			$this->value = $option->value;
		}

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			if (count($options) > 1)
			{
				$html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			}

			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}
		// Create a regular list.
		else
		{
			if (count($options) > 1)
			{
				$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
			}

			$html[] = '<span class="sort-by-dir" onclick="jQuery(\'#sortdir\').val(\'desc\');jQuery(\'#adminForm\').submit();">' .
				'<i class="icon icon-sort-by-attributes"></i></span>';
		}

		return implode($html);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   1.13.0
	 */
	protected function getLabel()
	{
		$options = (array) $this->getOptions();

		if (count($options) > 1)
		{
			return parent::getLabel();
		}

		return '';
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.13.0
	 */
	protected function getOptions()
	{
		$allowedOrderByFields = RedshopbApp::getConfig()
			->getAllowedOrderByFields($this->configLayout);
		$options              = array();

		foreach ($allowedOrderByFields as $allowedOrderByField)
		{
			switch ($allowedOrderByField)
			{
				case 'sku':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('COM_REDSHOPB_SKU'));
					break;
				case 'name':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('COM_REDSHOPB_PRODUCT_NAME'));
					break;
				case 'price':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('COM_REDSHOPB_PRODUCT_PRICE'));
					break;
				case 'relevance':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('COM_REDSHOPB_SHOP_SORT_BY_RELEVANCE'));
					break;
				case 'most_popular':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('COM_REDSHOPB_SHOP_SORT_BY_MOST_POPULAR'));
					break;
				case 'most_purchased':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('COM_REDSHOPB_SHOP_SORT_BY_MOST_PURCHASED'));
					break;
				case '':
					$options[] = HTMLHelper::_('select.option', $allowedOrderByField, Text::_('JOPTION_SORT_BY'));
					break;
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
