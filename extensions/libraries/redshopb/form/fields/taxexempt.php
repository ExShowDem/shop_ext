<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Company Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTaxexempt extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Taxexempt';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		if ($this->disabled || $this->readonly)
		{
			foreach ($this->getOptions() as $option)
			{
				if ($this->value == $option->value)
				{
					return $option->text;
				}
			}

			return Text::_('JNO');
		}
		else
		{
			if (empty($this->layout))
			{
				throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
			}

			return $this->getRenderer($this->layout)->render($this->getLayoutData());
		}
	}
}
