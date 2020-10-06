<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Application\CliApplication;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

/**
 * Clean products and category duplicate images cli application.
 * It accepts 2 arguments:
 * 1. Folder start -> number of folders to skip before is starts processing
 * 2. Folder limit -> number of folders to process at one script execution
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class DeleteRolesApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		$this->out('Select all roles and delete roles without any user in it');

		// Delete roles without any user in it
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('r.*')
			->from($db->qn('#__redshopb_role', 'r'))
			->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.company_id = r.company_id AND umc.role_id = r.role_type_id')
			->where($db->qn('umc.user_id') . ' IS NULL')
			->where($db->qn('r.company_id') . ' IS NOT NULL');

		$rolesWithoutUsers = $db->setQuery($query)->loadObjectList();
		$this->out('Number of roles for delete: ' . count($rolesWithoutUsers));

		if ($rolesWithoutUsers)
		{
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
			$app = Factory::getApplication('site');
			$app->input->set('option', 'com_redshopb');

			JLoader::import('redshopb.library');
			RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

			/** @var RedshopbTableRole $roleTable */
			$roleTable = RedshopbTable::getAdminInstance('Role');

			foreach ($rolesWithoutUsers as $role)
			{
				if (!$roleTable->delete($role->id))
				{
					$this->out('Role ID not deleted: ' . $role->id);
				}
			}
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('DeleteRolesApplicationCli');
$instance->execute();
