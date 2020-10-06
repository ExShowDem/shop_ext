<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

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
 * This script will reset all WS Flags for product data including
 * 'Product' => 'erp.pim.product', 'Field_Data' => 'erp.pim.field_data', 'Media' => 'erp.pim.media'
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class ChangeDBIndexesApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		$db    = Factory::getDbo();
		$query = 'ALTER TABLE `#__redshopb_sync`
	DROP INDEX `idx_remote_key`,
	DROP INDEX `idx_reference`,
	DROP INDEX `idx_execute_sync`,
	ADD INDEX `idx_execute_sync` (`execute_sync` ASC, `reference` ASC),
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`reference`, `remote_key`, `remote_parent_key`) USING BTREE';

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$this->out('ERROR: ' . $e->getMessage());

			return;
		}

		$this->out('Indexes changed successfully for table #__redshopb_sync');

		$query = 'ALTER TABLE `#__redshopb_field_data` ADD INDEX `idx_subitem_id` (`subitem_id`);';

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$this->out('ERROR: ' . $e->getMessage());

			return;
		}

		$this->out('Indexes changed successfully for table #__redshopb_field_data');

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('ChangeDBIndexesApplicationCli');
$instance->execute();
