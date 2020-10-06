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

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Plugin\PluginHelper;

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

/**
 * This script is only for DJe Portal server. The server has been using FTP relations for images, with this patch we have
 * dropped the relations and are using new direct schema
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class MoveImagesFromFtpSyncApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		$this->out('Selecting all related ftp sync records');

		// Delete roles without any user in it
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('s.*, s2.serialize as serialize_ftp')
			->from($db->qn('#__redshopb_sync', 's'))
			->leftJoin(
				$db->qn('#__redshopb_sync', 's2') . ' ON s2.remote_key = s.remote_parent_key AND s2.reference = ' . $db->q('erp.ftpsync.file')
			)
			->where($db->qn('s.reference') . ' = ' . $db->q('erp.pim.media'));

		$references = $db->setQuery($query)->loadObjectList();
		$this->out('Number of references for transfer: ' . count($references));

		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
		$app = Factory::getApplication('site');
		$app->input->set('option', 'com_redshopb');

		JLoader::import('redshopb.library');

		if ($references)
		{
			foreach ($references as $reference)
			{
				if ($reference->serialize_ftp)
				{
					$serializeFtp = unserialize($reference->serialize_ftp);

					if (!empty($serializeFtp))
					{
						$serialize = array(
							'modify'     => $serializeFtp['modify'],
							'size'       => $serializeFtp['size'],
							'image'      => $reference->remote_parent_key,
						);

						$serialize = serialize($serialize);

						$query = $db->getQuery(true)
							->update('#__redshopb_sync')
							->set('serialize = ' . $db->q($serialize))
							->where($db->qn('reference') . ' = ' . $db->q('erp.pim.media'))
							->where($db->qn('remote_key') . ' = ' . $db->q($reference->remote_key))
							->where($db->qn('remote_parent_key') . ' = ' . $db->q($reference->remote_parent_key))
							->where($db->qn('local_id') . ' = ' . $db->q($reference->local_id))
							->where($db->qn('main_reference') . ' = ' . $db->q($reference->main_reference));

						$result = $db->setQuery($query)->execute();

						if (!$result)
						{
							$this->out('This update failed: ' . (string) $query);
						}
					}
				}
			}
		}

		$this->out('Finished with image update');
		$this->out('Updating remote path on media table');

		$query = $db->getQuery(true)
			->update('#__redshopb_media')
			->set('remote_path = ' . $db->q('media/com_redshopb/dje/pim/Pics'))
			->where($db->qn('remote_path') . ' = ' . $db->q('media/com_redshopb/pim/Pics'));

		$result = $db->setQuery($query)->execute();

		if (!$result)
		{
			$this->out('This update failed: ' . (string) $query);
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('MoveImagesFromFtpSyncApplicationCli');
$instance->execute();
