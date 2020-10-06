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
 * Reapply Company roles
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class ReapplyCompanyRolesApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
		$app = Factory::getApplication('site');
		$app->input->set('option', 'com_redshopb');

		JLoader::import('redshopb.library');
		RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

		$this->out('Select all companies and add roles without any user in it');

		// Select all companies and add roles without any user in it
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from($db->qn('#__redshopb_company', 'c'))
			->where($db->qn('c.name') . ' != ' . $db->q('ROOT'));

		$companies = $db->setQuery($query)->loadObjectList();

		/** @var RedshopbTableCompany $roleTable */
		$companyTable = RedshopbTable::getAdminInstance('Company');

		try
		{
			foreach ($companies as $company)
			{
				$this->out('Creating roles for company (' . $company->id . '): ' . $company->name);
				$companyTable->createRoles($company->id) ? $this->out('Company Roles created') : $this->out('Failed to create roles');
				RedshopbHelperACL::rebuildCompanyACL($company->id) ?
					$this->out('Company Permissions reset') : $this->out('Failed to reset company permissions');
			}
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
			$this->out($e->getTraceAsString());

			$db = Factory::getDBO();
			$this->out((string) $db->getQuery());
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('ReapplyCompanyRolesApplicationCli');
$instance->execute();
