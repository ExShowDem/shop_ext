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
 * Company parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCompanyParent extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CompanyParent';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	static protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$id = $this->form->getValue('id');

		if (!isset(self::$cache[$id][(int) RedshopbHelperACL::isSuperAdmin()]))
		{
			if (!isset(self::$cache[$id]))
			{
				self::$cache[$id] = array();
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select(
				array(
					$db->qn('a.id', 'value'),
					'IF(a.name2 IS NULL OR a.name2 = ' . $db->q('') . ', a.name, CONCAT_WS(' . $db->q(' ') . ', a.name, a.name2)) AS text',
					$db->qn('a.level'),
					$db->qn('a.state')
				)
			)
				->from($db->qn('#__redshopb_company', 'a'))
				->where($db->qn('a.deleted') . ' = 0')
				->leftJoin($db->qn('#__redshopb_company') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt AND ' . $db->qn('b.deleted') . ' = 0');

			// Prevent parenting to children of this item.
			if ($id)
			{
				$query->leftJoin($db->qn('#__redshopb_company', 'p') . ' ON p.id = ' . (int) $id . ' AND ' . $db->qn('p.deleted') . ' = 0')
					->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');

				$rowQuery = $db->getQuery(true)
					->select('a.id AS value, a.name AS text, a.level, a.parent_id')
					->from('#__redshopb_company AS a')
					->where($db->qn('a.deleted') . ' = 0')
					->where('a.id = ' . (int) $id);

				$db->setQuery($rowQuery);
				$row = $db->loadObject();
			}

			$query->where('a.state IN (0,1)');

			if (!RedshopbHelperACL::isSuperAdmin())
			{
				$user = Factory::getUser();
				$query->where('a.id IN (' . RedshopbHelperACL::listAvailableCompaniesAndParents($user->id) . ')');

				if (!$this->value)
				{
					$query->where('a.level >= ' . (int) RedshopbHelperUser::getUserLevel($user->id, 'joomla'));
					$this->value = RedshopbHelperUser::getUserCompanyId($user->id, 'joomla');
				}
				else
				{
					$this->readonly = 'true';
				}
			}

			$query->group('a.id, a.name, a.level, a.lft, a.rgt, a.parent_id')
				->order('a.lft ASC');

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
					$options[$i]->text = Text::_('COM_REDSHOPB_NO_COMPANY');
				}

				else
				{
					$options[$i]->text = str_repeat('- ', $options[$i]->level - 1) . $options[$i]->text;
				}

				$options[$i]->class = 'level_' . $options[$i]->level;
			}

			if (isset($row) && !isset($options[0]))
			{
				if ($row->parent_id == '1')
				{
					$parent        = new stdClass;
					$parent->value = '1';
					$parent->text  = Text::_('COM_REDSHOPB_NO_COMPANY');
					array_unshift($options, $parent);
				}
			}

			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);

			self::$cache[$id][(int) RedshopbHelperACL::isSuperAdmin()] = $options;
		}

		return self::$cache[$id][(int) RedshopbHelperACL::isSuperAdmin()];
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
