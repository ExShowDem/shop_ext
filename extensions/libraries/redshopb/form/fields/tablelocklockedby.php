<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Table Lock Locked by Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTableLockLockedBy extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Tablelocklockedby';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$items   = $this->getTableLockLockedBy();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->id, $item->name);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of tables in table lock table.
	 *
	 * @return  array  An array of table names.
	 */
	protected function getTableLockLockedBy()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT tl.locked_by as id, u.name')
			->from($db->qn('#__redshopb_table_lock', 'tl'))
			->leftJoin($db->qn('#__users', 'u') . ' ON u.id = tl.locked_by');

		return $db->setQuery($query)
			->loadObjectList();
	}
}
