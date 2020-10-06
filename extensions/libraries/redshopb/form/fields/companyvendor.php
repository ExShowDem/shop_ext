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
 * Company vendor Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCompanyVendor extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CompanyVendor';

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
		$db    = Factory::getDbo();
		$user  = Factory::getUser();
		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('a.id', 'value'),
					'IF(a.name2 IS NULL OR a.name2 = ' . $db->q('') . ', a.name, CONCAT_WS(' . $db->q(' ') . ', a.name, a.name2)) AS text',
					$db->qn('a.level'),
					$db->qn('a.state')
				)
			)
			->from($db->qn('#__redshopb_company', 'a'))
			->where($db->qn('a.deleted') . ' = 0')
			->leftJoin($db->qn('#__redshopb_company') . ' AS b ON b.lft BETWEEN a.lft AND a.rgt AND ' . $db->qn('b.deleted') . ' = 0')
			->where('a.state IN (0,1)')
			->group('a.id')
			->order('a.lft ASC');

		if (isset($this->element['company_id']))
		{
			$id = (int) $this->element['company_id'];
			$query->where('b.id = ' . (int) $id);

			$rowQuery = $db->getQuery(true)
				->select('a.parent_id')
				->from('#__redshopb_company AS a')
				->where($db->qn('a.deleted') . ' = 0')
				->where('a.id = ' . (int) $id);

			$row = $db->setQuery($rowQuery)
				->loadObject();
		}

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$query->where('a.id IN (' . RedshopbHelperACL::listAvailableCompaniesAndParents($user->id) . ')');
		}

		$options = $db->setQuery($query)
			->loadObjectList();
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

		return array_merge(parent::getOptions(), $options);
	}
}
