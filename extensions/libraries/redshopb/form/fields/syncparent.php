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

FormHelper::loadFieldClass('rlist');

/**
 * Sync parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldSyncParent extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'SyncParent';

	/**
	 * A static cache.
	 *
	 * @var array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.name AS text, a.level')
			->from('#__redshopb_cron AS a')
			->leftJoin($db->quoteName('#__redshopb_cron') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Prevent parenting to children of this item.
		$id = $this->form->getValue('id');

		if ($id)
		{
			$query->leftJoin($db->quoteName('#__redshopb_cron') . ' AS p ON p.id = ' . (int) $id)
				->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');

			$rowQuery = $db->getQuery(true)
				->select('a.id AS value, a.name AS text, a.level, a.parent_id')
				->from('#__redshopb_cron AS a')
				->where('a.id = ' . (int) $id);

			$db->setQuery($rowQuery);
			$row = $db->loadObject();
		}

		$query->where('a.state IN (0,1)')
			->group('a.id, a.name, a.level, a.lft, a.rgt, a.parent_id')
			->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();
		$count   = count($options);

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0; $i < $count; $i++)
		{
			// Translate ROOT
			if ($options[$i]->level == 0)
			{
				$options[$i]->text = Text::_('JGLOBAL_ROOT_PARENT');
			}

			else
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level - 1) . $options[$i]->text;
			}
		}

		if (isset($row) && !isset($options[0]))
		{
			if ($row->parent_id == '1')
			{
				$parent       = new stdClass;
				$parent->text = Text::_('JGLOBAL_ROOT_PARENT');
				array_unshift($options, $parent);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
