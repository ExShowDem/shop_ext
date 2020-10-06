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

FormHelper::loadFieldClass('rlist');

/**
 * Field Scope Select Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       2.0
 */
class JFormFieldFieldscope extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Fieldscope';

	/**
	 * A static cache.
	 *
	 * @var array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();
		$items   = array();
		$items[] = $this->setOption('category', 'category');
		$items[] = $this->setOption('company', 'company');
		$items[] = $this->setOption('department', 'department');
		$items[] = $this->setOption('order', 'order');
		$items[] = $this->setOption('product', 'product');
		$items[] = $this->setOption('user', 'user');

		foreach ($items as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
		}

		return $options;
	}

	/**
	 * Set option
	 *
	 * @param   string  $value  Value
	 * @param   string  $text   Text
	 *
	 * @return  stdClass
	 */
	protected function setOption($value, $text)
	{
		$tmp        = new stdClass;
		$tmp->value = $value;
		$tmp->text  = $text;

		return $tmp;
	}
}
