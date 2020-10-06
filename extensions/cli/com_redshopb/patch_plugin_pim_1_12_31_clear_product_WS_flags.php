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
use Joomla\CMS\Plugin\PluginHelper;

// Error_reporting(0);
// ini_set('display_errors', 0);

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
class ClearProductWsFlagsApplicationCli extends CliApplication
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

		$this->out('Get Cron Item ID');

		// Delete roles without any user in it
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('node.*')
			->from($db->qn('#__redshopb_cron', 'node'))
			->where('node.name = ' . $db->q('GetProduct'))
			->where('node.plugin = ' . $db->q('pim'));

		$cronItem = $db->setQuery($query)
			->loadObject();

		$this->out('Cron Item ID is: ' . $cronItem->id);

		if ($cronItem)
		{
			$this->out('Starting to clear WS Flags for erp.pim.product');
			$this->clearWSFlags('Product', 'erp.pim.product');
			$this->out('Finished');

			$this->out('Starting to clear WS Flags for erp.pim.field_data');
			$this->clearWSFlags('Field_Data', 'erp.pim.field_data');
			$this->out('Finished');

			$this->out('Starting to clear WS Flags for erp.pim.media');
			$this->clearWSFlags('Media', 'erp.pim.media');
			$this->out('Finished');

			PluginHelper::importPlugin('rb_sync');
			$dispatcher = RFactory::getDispatcher();

			$this->out('Clearing Hashed keys');
			$result = $dispatcher->trigger('onFuncCronClearHashedKeys', array('RedshopbSync', $cronItem));

			$this->out('Result:');
			$this->out(json_encode($result));
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}

	/**
	 * Clearing WS Flags
	 *
	 * @param   string  $tableClassName  Table Class name
	 * @param   string  $syncName        Sync name
	 *
	 * @return  boolean
	 */
	public function clearWSFlags($tableClassName, $syncName)
	{
		$table     = RTable::getInstance($tableClassName, 'RedshopbTable');
		$tableName = $table->get('_tbl');
		$limit     = 5000;
		$start     = 0;

		$db        = Factory::getDbo();
		$syncQuery = $db->getQuery(true)
			->select('s.local_id, s.metadata, c.*')
			->from($db->qn('#__redshopb_sync', 's'))
			->leftJoin($db->qn($tableName, 'c') . ' ON c.id = s.local_id')
			->where('s.reference = ' . $db->q($syncName));

		while (true)
		{
			$this->out('Loading ' . $limit . ' rows starting from ' . $start);
			$results = $db->setQuery($syncQuery, $start, $limit)
				->loadObjectList();
			$start  += $limit;
			$this->out('Loaded ' . count($results) . ' rows');

			if ($results)
			{
				foreach ($results as $result)
				{
					$metaData = $result->metadata;

					if ($metaData)
					{
						$metaData = unserialize($metaData);

						// Reset user override flags
						$metaData['WSFlags'] = array();

						foreach ($metaData['WSProperties'] as $name => $WSProperty)
						{
							if (property_exists($result, $name))
							{
								$metaData['WSProperties'][$name] = $result->{$name};
							}
						}

						$metaData = serialize($metaData);

						$query = $db->getQuery(true)
							->update($db->qn('#__redshopb_sync'))
							->set('metadata = ' . $db->q($metaData))
							->where('reference = ' . $db->q($syncName))
							->where('local_id = ' . $db->q($result->local_id));

						try
						{
							$db->setQuery($query)->execute();
						}
						catch (Exception $e)
						{
							Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

							return false;
						}
					}
				}

				continue;
			}

			break;
		}

		return true;
	}
}

$instance = CliApplication::getInstance('ClearProductWsFlagsApplicationCli');
$instance->execute();
