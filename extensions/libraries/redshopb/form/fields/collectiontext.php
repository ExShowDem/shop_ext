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

FormHelper::loadFieldClass('text');

/**
 * ColectionText Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCollectionText extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CollectionText';
}
