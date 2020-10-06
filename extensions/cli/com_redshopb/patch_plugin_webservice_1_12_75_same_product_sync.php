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
 * This script is only for webservice plugin. It will fill remote parent field with belonging product ID as it might have different source
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class SameProductSyncApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');
		$db = Factory::getDbo();

		$this->out('Update Product Images sync so we dont get duplicates');
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_sync', 's') . ', ' . $db->qn('#__redshopb_media', 'm'))
			->set($db->qn('s.remote_parent_key') . ' = ' . $db->qn('m.product_id'))
			->where($db->qn('s.reference') . ' = ' . $db->q('erp.webservice.product_images'))
			->where($db->qn('s.local_id') . ' = ' . $db->qn('m.id'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
		}

		$this->out('Update Product Descriptions sync so we dont get duplicates');
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_sync', 's') . ', ' . $db->qn('#__redshopb_product_descriptions', 'pd'))
			->set($db->qn('s.remote_parent_key') . ' = ' . $db->qn('pd.product_id'))
			->where($db->qn('s.reference') . ' = ' . $db->q('erp.webservice.product_descriptions'))
			->where($db->qn('s.local_id') . ' = ' . $db->qn('pd.id'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
		}

		$this->out('Update Fields data sync so we dont get duplicates');
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_sync', 's') . ', ' . $db->qn('#__redshopb_field_data', 'fd'))
			->set($db->qn('s.remote_parent_key') . ' = ' . $db->qn('fd.item_id'))
			->where($db->qn('s.reference') . ' = ' . $db->q('erp.webservice.field_data'))
			->where($db->qn('s.local_id') . ' = ' . $db->qn('fd.id'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('SameProductSyncApplicationCli');
$instance->execute();
