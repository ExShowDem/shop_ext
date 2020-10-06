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
 * Categories Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTemplategroup extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Templategroup';

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
		$items[] = $this->setOption('email', 'email');
		$items[] = $this->setOption('shop', 'shop');

		/**
		 * Why ?
		 * $items[] = $this->setOption('email_tag', 'email_tag');
		 * $items[] = $this->setOption('shop_tag', 'shop_tag');
		*/

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
