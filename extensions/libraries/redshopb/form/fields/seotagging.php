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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('Textarea');

/**
 * Shipping methods Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.12.60
 */
class JFormFieldSeotagging extends JFormFieldTextarea
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Seotagging';

	/**
	 * @var string
	 */
	public $layout = 'redshopb.field.seotag';

	/**
	 * Method to get the textarea field input markup.
	 * Use the rows and columns attributes to specify the dimensions of the area.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.12.60
	 */
	protected function getInput()
	{
		// Translate placeholder text
		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled     = $this->disabled ? ' disabled' : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$columns      = $this->columns ? ' cols="' . $this->columns . '"' : 'cols="50"';
		$rows         = $this->rows ? ' rows="' . $this->rows . '"' : 'rows="5"';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$hint         = strlen($hint) ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
		$autofocus    = $this->autofocus ? ' autofocus' : '';
		$spellcheck   = $this->spellcheck ? '' : ' spellcheck="false"';
		$maxlength    = $this->maxlength ? ' maxlength="' . $this->maxlength . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		$onclick  = $this->onclick ? ' onclick="' . $this->onclick . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);

		$layout = !empty($this->element['layout']) ? $this->element['layout'] : $this->layout;
		$tags   = array();

		if (!empty($this->element['tags']))
		{
			$tags = explode(',', $this->element['tags']);
		}

		return RedshopbLayoutHelper::render(
			trim($layout),
			array(
				'id'           => $this->id,
				'name'         => $this->name,
				'columns'      => $columns,
				'rows'         => $rows,
				'autocomplete' => $autocomplete,
				'autofocus'    => $autofocus,
				'spellcheck'   => $spellcheck,
				'class'        => $class,
				'required'     => $required,
				'value'        => $this->value,
				'readonly'     => $readonly,
				'hint'         => $hint,
				'disabled'     => $disabled,
				'maxlength'    => $maxlength,
				'onchange'     => $onchange,
				'onclick'      => $onclick,
				'tags'         => $tags
			)
		);
	}
}
