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
 * Clean products and category duplicate images cli application.
 * It accepts 2 arguments:
 * 1. Folder start -> number of folders to skip before is starts processing
 * 2. Folder limit -> number of folders to process at one script execution
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class UserMultiCompanyApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		// Pull old relations from db
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('ru.*, r.role_type_id')
			->from($db->qn('#__redshopb_user', 'ru'))
			->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = ru.id')
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ru.joomla_user_id = ug.user_id')
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
			->where($db->qn('umc.user_id') . ' IS NULL');

		$users = $db->setQuery($query)->loadObjectList();

		foreach ($users as $user)
		{
			$query = $db->getQuery(true)
				->insert($db->qn('#__redshopb_user_multi_company'))
				->set($db->qn('user_id') . ' = ' . $user->id)
				->set($db->qn('company_id') . ' = ' . $user->company_id)
				->set($db->qn('role_id') . ' = ' . $user->role_type_id)
				->set($db->qn('main') . ' = 1');

			$db->setQuery($query)->execute();
		}

		$this->out('Added user - user_multi_company relation');

		// We will add references in the orders table for the existing orders
		foreach ($users as $user)
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_order'))
				->set($db->qn('user_company_id') . ' = ' . $user->company_id)
				->where($db->qn('customer_type') . ' = ' . $db->q('employee'))
				->where($db->qn('customer_id') . ' = ' . $user->id);

			$db->setQuery($query)->execute();
		}

		$this->out('Added user_company_id to the orders table');

		// We will take care of company customer type for the existing orders
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_order'))
			->set($db->qn('user_company_id') . ' = ' . $db->qn('customer_company'))
			->where($db->qn('customer_type') . ' = ' . $db->q('company'));

		$db->setQuery($query)->execute();

		$this->out('Added user_company_id to the orders table for companies');
		$this->out('Dropping company_id from redshopb_user table if needed');

		$columns = $db->getTableColumns('#__redshopb_user');

		if (!empty($columns['company_id']))
		{
			// Now Delete the column
			$db->setQuery('ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__rs_user_fk2`')->execute();
			$db->setQuery('ALTER TABLE `#__redshopb_user` DROP INDEX `#__rs_user_fk2`')->execute();
			$db->setQuery('ALTER TABLE `#__redshopb_user` DROP `company_id`')->execute();

			$this->out('Dropped company_id from redshopb_user table.');
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('UserMultiCompanyApplicationCli');
$instance->execute();
