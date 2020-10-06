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

FormHelper::loadFieldClass('rlist');

/**
 * Company parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldDepartmentParent extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'DepartmentParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$companyId    = $this->getCompanyId();
		$departmentId = $this->getDepartmentId();

		// Initialize departments array
		$options = Array();

		// If a company is selected, selects only its departments
		if ($companyId)
		{
			// Initialise variables.
			$name = (string) $this->element['name'];

			$oldCat = $this->form->getValue($name);

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select(
				array(
					$db->qn('a.id', 'value'),
					$db->qn('a.name', 'text'),
					$db->qn('a.level'),
					$db->qn('c.name'),
					$db->qn('a.state')
				)
			)
				->from($db->qn('#__redshopb_department', 'a'))
				->where($db->qn('a.deleted') . ' = 0 AND ' . $db->qn('a.state') . ' IN (0,1)')
				->leftJoin(
					$db->qn('#__redshopb_department', 'b') . ' ON a.lft > b.lft AND a.rgt < b.rgt AND ' .
					$db->qn('b.deleted') . ' = 0 AND ' . $db->qn('b.state') . ' IN (0,1)'
				)
				->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = a.company_id AND ' . $db->qn('c.deleted') . ' = 0');

			// Prevent parenting to children of this item.
			$id = $this->form->getValue('id');

			if ($id)
			{
				$query->leftJoin(
					$db->qn('#__redshopb_department', 'p') . ' ON p.id = ' . (int) $id . ' AND ' .
					$db->qn('p.deleted') . ' = 0 AND ' . $db->qn('p.state') . ' = 1'
				)
					->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');
			}

			/**
			 * *bump*
			 * @TODO: limit available departments, but checking that current department (for HODs)
			 * can be saved without losing its parent department (because it's not visible for HODs)
			 */

			$query->where('a.company_id = ' . $companyId);

			if ($departmentId)
			{
				$query->where('a.id <> ' . $departmentId);
			}

			$query->group('a.id, a.name, a.level, a.lft, a.rgt, a.parent_id')
				->order('a.lft ASC');

			// Get the options.
			$db->setQuery($query);

			$options = $db->loadObjectList();
		}

		// Sets the "no department" option (parent)
		$firstOption        = new stdClass;
		$firstOption->value = 1;
		$firstOption->text  = Text::_('COM_REDSHOPB_NO_DEPARTMENT');
		$firstOption->name  = '';
		$firstOption->level = 1;
		$firstOption->state = 1;
		array_unshift($options, $firstOption);

		$count = count($options);

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0; $i < $count; $i++)
		{
			if (!$options[$i]->state)
			{
				$options[$i]->text = $options[$i]->text . ' [' . Text::_('JUNPUBLISHED') . ']';
			}

			$options[$i]->text = str_repeat('- ', $options[$i]->level - 1)
								. $options[$i]->text
								. ($options[$i]->name != '' ? ' (' . $options[$i]->name . ')' : '');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Get the company id.
	 *
	 * @return  integer  The company id
	 */
	protected function getCompanyId()
	{
		$input = Factory::getApplication()->input;
		$form  = $input->get('jform', array(), 'array');

		$view      = $input->get('view');
		$companyId = null;

		if ('department' === $view)
		{
			if (isset($form['company_id']))
			{
				$companyId = (int) $form['company_id'];
			}
		}

		if (isset($form['company_id']))
		{
			$companyId = (int) $form['company_id'];
		}

		return $companyId;
	}

	/**
	 * Get the current department id
	 *
	 * @return  integer  The department id
	 */
	protected function getDepartmentId()
	{
		$input        = Factory::getApplication()->input;
		$form         = $input->get('jform', array(), 'array');
		$view         = $input->get('view');
		$departmentId = null;

		if ('department' === $view)
		{
			if (isset($form['id']))
			{
				$departmentId = (int) $form['id'];
			}
		}

		if (isset($form['id']))
		{
			$departmentId = (int) $form['id'];
		}

		return $departmentId;
	}
}
