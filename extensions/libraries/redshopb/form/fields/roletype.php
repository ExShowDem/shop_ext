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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * RoleType Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldRoleType extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'RoleType';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Get the roles types.
		$items = $this->getRoleTypes();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of roles types.
	 *
	 * @return  array  An array of role types.
	 */
	protected function getRoleTypes()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('id', 'identifier'),
						$db->qn('name', 'data')
					)
				)
				->from($db->qn('#__redshopb_role_type'))
				->where($db->qn('company_role') . ' = 0')
				->where($db->qn('hidden') . ' = 0')
				->order('name');

			if (RedshopbHelperACL::getPermission('manage', 'company'))
			{
				$user = Factory::getUser();

				if (!RedshopbHelperACL::getPermission('manage', 'mainwarehouse') && !RedshopbHelperACL::isSuperAdmin())
				{
					$query->where($db->qn('type') . ' NOT IN (' . $db->q('sales') . ')');
				}
			}
			else
			{
				$query->where($db->qn('type') . ' NOT IN (' . $db->q('admin') . ',' . $db->q('sales') . ')');
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
