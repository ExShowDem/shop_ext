<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\FormField;

FormHelper::loadFieldClass('list');

/**
 * Field to upload one or multiple files.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldFileUploader extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'FileUploader';

	/**
	 * Layout to render
	 *
	 * @var  string
	 */
	protected $layout = 'fields.fileuploader';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$layout = !empty($this->element['layout']) ? $this->element['layout'] : $this->layout;

		return RedshopbLayoutHelper::render(
			$layout,
			array(
				'id'       => $this->id,
				'element'  => $this->element,
				'field'    => $this,
				'name'     => $this->name,
				'required' => $this->required,
				'value'    => $this->value
			)
		);
	}
}
