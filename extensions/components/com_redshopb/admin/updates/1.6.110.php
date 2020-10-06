<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6.99
 */
class Com_RedshopbUpdateScript_1_6_110
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		// New global permissions to insert
		$newPermissions = array(
			'redshopb.order.manage.own',
		);

		$db    = Factory::getDbo();
		$query = $db->getQuery();

		// Application asset
		$query->select(array('id'))
			->from($db->qn('#__assets'))
			->where($db->qn('name') . ' = ' . $db->quote('com_redshopb'));
		$db->setQuery($query);
		$joomlaAssetId = $db->loadResult();

		foreach ($newPermissions as $permission)
		{
			// Looks for the roles to add the new permissions to
			$query->clear()
				->select(
					array(
						$db->qn('rt.id'),
						$db->qn('rt.allowed_rules'),
						$db->qn('aa.id', 'access_id')
					)
				)
				->from($db->qn('#__redshopb_role_type', 'rt'))
				->join(
					'inner',
					$db->qn('#__redshopb_acl_simple_access_xref', 'sax') . ' ON ' . $db->qn('sax.role_type_id') . ' = ' . $db->qn('rt.id')
				)
				->join('inner', $db->qn('#__redshopb_acl_access', 'aa') . ' ON ' . $db->qn('aa.id') . ' = ' . $db->qn('sax.access_id'))
				->where($db->qn('rt.company_role') . ' = 0')
				->where($db->qn('sax.scope') . ' = ' . $db->q('global'))
				->where($db->qn('aa.name') . ' = ' . $db->q($permission));
			$db->setQuery($query);
			$roles = $db->loadObjectList();

			if (!$roles)
			{
				continue;
			}

			foreach ($roles as $role)
			{
				$added        = false;
				$allowedRules = json_decode($role->allowed_rules);

				if (is_null($allowedRules) || !$allowedRules)
				{
					$allowedRules = array();
				}

				if (!array_search($permission, $allowedRules, true))
				{
					$allowedRules[] = $permission;
					$added          = true;
				}

				// Inserts the new allowed rules into the role type json for it
				$query->clear()
					->update($db->qn('#__redshopb_role_type'))
					->set($db->qn('allowed_rules') . ' = ' . $db->q(json_encode($allowedRules)))
					->where($db->qn('id') . ' = ' . $role->id);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}

				if (!$added)
				{
					continue;
				}

				// Selects all the available company roles to get the new rules added to them
				$query->clear()
					->select(
						array(
							$db->qn('r.id')
						)
					)
					->from($db->qn('#__redshopb_role', 'r'))
					->join('inner', $db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('rt.id') . ' = ' . $db->qn('r.role_type_id'))
					->join('inner', $db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('r.company_id'))
					->where($db->qn('rt.id') . ' = ' . $role->id);
				$db->setQuery($query);
				$companyRoles = $db->loadObjectList();

				if (!$companyRoles)
				{
					continue;
				}

				foreach ($companyRoles as $companyRole)
				{
					$query->clear()
						->insert($db->qn('#__redshopb_acl_rule'))
						->columns($db->qn('access_id') . ',' . $db->qn('role_id') . ',' . $db->qn('joomla_asset_id') . ',' . $db->qn('granted'))
						->values((int) $role->access_id . ',' . (int) $companyRole->id . ',' . (int) $joomlaAssetId . ',1');
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}
		}

		return true;
	}
}
