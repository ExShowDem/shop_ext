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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Country assignment Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCountryAssignment extends JFormFieldRlist
{
	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		$countries = $this->formControl . $this->group . '_'
			. (isset($this->element['countryField']) ? (string) $this->element['countryField'] : 'countries');

		$script = "
			jQuery(document).ready(function(){
				countriesHide(jQuery('#" . $this->id . "').val());
				jQuery('#" . $this->id . "').change(function(){
					countriesHide(jQuery(this).val());
				})
			});
			function countriesHide(val){
			console.log(val);
				if (val == 0 || val == '-'){
					jQuery('#" . $countries . "-lbl, #" . $countries . "_chzn').hide();
				}else{
					jQuery('#" . $countries . "-lbl, #" . $countries . "_chzn').show();
				}
			}
		";

		Factory::getDocument()->addScriptDeclaration($script);

		return parent::getInput();
	}

	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CountryAssignment';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_REDSHOPB_CONFIG_COUNTRY_ASSIGNMENT_ALL'));
		$options[] = HTMLHelper::_('select.option', '-', Text::_('COM_REDSHOPB_CONFIG_COUNTRY_ASSIGNMENT_DEFAULT_COUNTRY'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('COM_REDSHOPB_CONFIG_COUNTRY_ASSIGNMENT_INCLUDE'));
		$options[] = HTMLHelper::_('select.option', '-1', Text::_('COM_REDSHOPB_CONFIG_COUNTRY_ASSIGNMENT_EXCLUDE'));

		return array_merge(parent::getOptions(), $options);
	}
}
