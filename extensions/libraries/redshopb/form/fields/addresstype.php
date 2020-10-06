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

FormHelper::loadFieldClass('rlist');

/**
 * Type Address Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldAddressType extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'AddressType';

	/**
	 * @var boolean
	 */
	public $separateTypes = false;

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if (isset($this->element['separateTypes']) && $this->element['separateTypes'] == 'true')
		{
			$this->separateTypes = true;
		}

		if ($this->value == 2 && $this->separateTypes)
		{
			$this->readonly = 'true';
		}

		return parent::getInput();
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		if (($this->value == 2 && $this->separateTypes) || $this->separateTypes == false)
		{
			$options[] = HTMLHelper::_('select.option', '2', Text::_('JOPTION_SELECT_ADDRESS_REGULAR'));
		}

		if (($this->value != 2 && $this->separateTypes) || $this->separateTypes == false)
		{
			$options[] = HTMLHelper::_('select.option', '1', Text::_('JOPTION_SELECT_ADDRESS_SHIPPING'));
			$options[] = HTMLHelper::_('select.option', '3', Text::_('JOPTION_SELECT_ADDRESS_DEFAULT_SHIPPING'));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
