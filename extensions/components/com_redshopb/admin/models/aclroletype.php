<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * ACL Role Type Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.6.107
 */
class RedshopbModelACLRoleType extends RedshopbModelAdmin
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$data = $this->appendCorePermissions($data);

		if (!parent::save($data))
		{
			return false;
		}

		// Rename all group association with this role.
		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select($db->qn('joomla_group_id'))
			->from($db->qn('#__redshopb_role'))
			->where($db->qn('role_type_id') . ' = ' . $data['id'])
			->where($db->qn('company_id') . ' IS NULL');

		$query = $db->getQuery(true)
			->update($db->qn('#__usergroups'))
			->set($db->qn('title') . ' = ' . $db->quote($data['name']))
			->where($db->qn('id') . ' IN (' . $subQuery . ')');

		$db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Takes the current core permissions and re-adds them to the list of rules
	 *
	 * This makes sure that the core permissions are not removed when updaing the Aesir EC permissions
	 *
	 * @param   array   $data   Data to be saved
	 *
	 * @return   array
	 */
	protected function appendCorePermissions($data)
	{
		$rules = json_decode($data['allowed_rules']);
		$table = $this->getTable();

		$table->load($data['id']);

		$oldPermissions = json_decode($table->allowed_rules);

		$permissions = array_filter(
			$oldPermissions,
			function ($entry)
			{
				return substr($entry, 0, 4) === 'core';
			}
		);

		$data['allowed_rules'] = json_encode(array_merge($permissions, $rules));

		return $data;
	}
}
