<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
// Error_reporting(0);
// ini_set('display_errors', 0);

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
 * This script will set pim ID for fields data records as the combination of product_id and field_id
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class AddPimIdFieldsDataApplicationCli extends CliApplication
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
		$syncHelper = new RedshopbHelperSync;

		$this->out('Get all erp.pim.field_data with old reference_key');
		$db          = Factory::getDbo();
		$searchQuery = $db->getQuery(true)
			->select('s.remote_key, fd.*')
			->from($db->qn('#__redshopb_sync', 's'))
			->leftJoin($db->qn('#__redshopb_field_data', 'fd') . ' ON fd.id = s.local_id')
			->where('s.reference = ' . $db->q('erp.pim.field_data'))
			->where('s.remote_key like ' . $db->q('extra_field_%'));
		$fieldIds    = array();
		$productIds  = array();

		while ($results = $db->setQuery($searchQuery, 0, 100000)->loadObjectList())
		{
			$this->out('Fetched ' . count($results) . ' rows');

			if ($results)
			{
				foreach ($results as $result)
				{
					if (empty($fieldIds[$result->field_id]))
					{
						$fieldIds[$result->field_id] = $syncHelper->findSyncedLocalId('erp.pim.field', $result->field_id);
					}

					if (empty($productIds[$result->item_id]))
					{
						$productIds[$result->item_id] = $syncHelper->findSyncedLocalId('erp.pim.product', $result->item_id);
					}

					$pimFieldId   = $fieldIds[$result->field_id];
					$pimProductId = $productIds[$result->item_id];
					$remoteId     = $pimProductId . '_' . $pimFieldId;

					if (!$pimFieldId || !$pimProductId)
					{
						$this->out('Could not load remote key for ' . ($pimFieldId ? 'product ' . $result->item_id : 'field ' . $result->field_id));
						continue;
					}

					$query = $db->getQuery(true)
						->update($db->qn('#__redshopb_sync'))
						->set('remote_key = ' . $db->q($remoteId))
						->where('reference = ' . $db->q('erp.pim.field_data'))
						->where('remote_key = ' . $db->q($result->remote_key));

					try
					{
						$db->setQuery($query)->execute();
					}
					catch (Exception $e)
					{
						$this->out('Failed Updating field ' . $result->id . ' with old reference_key ' . $result->remote_key . ' to ' . $remoteId);
						$this->out($e->getMessage());

						break;
					}
				}
			}

			$this->out('Updated ' . count($results) . ' rows');
		}

		$this->out('Finished fields data.');
		$this->out('Create erp.pim.product_description references for each product_description reference');
		$searchQuery = $db->getQuery(true)
			->select('s.remote_key, pd.*')
			->from($db->qn('#__redshopb_sync', 's'))
			->leftJoin($db->qn('#__redshopb_product_descriptions', 'pd') . ' ON pd.product_id = s.local_id')
			->leftJoin(
				$db->qn('#__redshopb_sync', 's2') . ' ON s2.reference = ' . $db->q('erp.pim.product_description')
				. ' AND s2.remote_key = s.remote_key'
			)
			->where('s.reference = ' . $db->q('erp.pim.product'))
			->where('s2.reference IS NULL');

		while ($results = $db->setQuery($searchQuery, 0, 100000)->loadObjectList())
		{
			$this->out('Fetched ' . count($results) . ' product rows');

			if ($results)
			{
				foreach ($results as $result)
				{
					$remoteId  = $result->remote_key;
					$hashedKey = RedshopbHelperSync::generateHashKey($result->description, 'string');

					try
					{
						$syncHelper->recordSyncedId(
							'erp.pim.product_description', $remoteId, $result->id, '',
							1, 0, '', false, '', null, 1, $hashedKey
						);
					}
					catch (Exception $e)
					{
						$this->out('Failed creating product description reference ' . $result->id);
						$this->out($e->getMessage());

						break;
					}
				}
			}

			$this->out('Created ' . count($results) . ' rows');
		}

		$this->out('Finished.');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('AddPimIdFieldsDataApplicationCli');
$instance->execute();
