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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Tag Parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTagParent extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'TagParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('id', 'value'),
				$db->qn('name', 'text'),
				$db->qn('level'),
				$db->qn('state')
			)
		)
			->from($db->qn('#__redshopb_tag'));

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$companies = RedshopbHelperACL::listAvailableCompanies(Factory::getUser()->id, 'comma', 0, '', 'redshopb.company.view', '', true);
			$query->where(
				'(' . $db->qn('company_id') . ' IN(' . $companies . ')' .
				' OR ' . $db->qn('company_id') . ' IS NULL)'
			);
		}

		$query->where($db->qn('state') . ' IN (0,1)')
			->order($db->qn('lft') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		$count   = count($options);

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0; $i < $count; $i++)
		{
			if (!$options[$i]->state)
			{
				$options[$i]->text = $options[$i]->text . ' [' . Text::_('JUNPUBLISHED') . ']';
			}

			// Translate ROOT
			if ($options[$i]->level == 0)
			{
				$options[$i]->text = Text::_('COM_REDSHOPB_NO_TAG');
			}
			else
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level - 1) . $options[$i]->text;
			}

			$options[$i]->class = 'level_' . $options[$i]->level;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		if ((string) $this->element['emptystart'] == 'true')
		{
			return '<div id="redshopb-parent-tags"></div><div id="redshopb-parent-tags-loading">' .
				HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') . '</div>';
		}

		$html = array();

		// Get the field options.
		$options = (array) $this->getOptions();

		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1'
			|| (string) $this->readonly == 'true'
			|| (string) $this->disabled == '1'
			|| (string) $this->disabled == 'true'
		)
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}
		else
			// Create a regular list.
		{
			$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}
}
