<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Composition function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetComposition extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.product_composition';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Composition';

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();

		try
		{
			$xml = $this->client->getCompositionB2B();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$strUpperLangTag = strtoupper($this->lang);

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->isCronExecuted($this->cronName))
			{
				// We set them with a flag so we can delete the ones which are not present in the latest Sync xml
				$this->setSyncRowsAsExecuted($this->syncName);

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);
			}
			else
			{
				// Get list executed in previous sync items because this is multipart process
				$this->executed = $this->getPreviousSyncExecutedList($this->syncName);
				$this->counter  = count($this->executed);
			}

			$this->counterTotal = count($xml->Item);

			foreach ($xml->Item as $obj)
			{
				if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
					break;
				}

				$attributes           = $obj->attributes();
				$no                   = (string) $attributes['No'];
				$skipTranslationStore = false;

				if (isset($obj->Compositions->Composition))
				{
					$compositions = $obj->Compositions->Composition;
				}
				elseif (isset($obj->Compositions))
				{
					$compositions = $obj->Compositions;
				}
				else
				{
					continue;
				}

				$productId = $this->findSyncedId('fengel.product', $no);

				if (!$productId)
				{
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_REDSHOP_SYNC_FENGEL_PRODUCT_NOT_FOUND', $no), 'warning');
					continue;
				}

				foreach ($compositions as $composition)
				{
					$compositionAttributes = $composition->attributes();
					$langCode              = strtoupper($this->getLanguageTag((string) $compositionAttributes['LanguageCode']));
					$colorCode             = '';

					if ($strUpperLangTag != $langCode)
					{
						continue;
					}

					$row = array(
						'type' => (string) $compositionAttributes['Type'],
						'quality' => (string) $compositionAttributes['Quality'],
						'product_id' => $productId,
						'flat_attribute_value_id' => null
					);

					if (isset($compositionAttributes['ColourCode']))
					{
						$colorCode = (string) $compositionAttributes['ColourCode'];
						$flatId    = $this->findSyncedId('fengel.attribute', 'Farve_' . $colorCode, $no);

						if ($flatId)
						{
							$row['flat_attribute_value_id'] = $flatId;
						}
						else
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf(
									'PLG_RB_SYNC_FENGEL_PRODUCT_TYPE_NOT_FOUND', (string) $compositionAttributes['ColourCode'] . ' (' . $no . ')'
								),
								'warning'
							);
							continue;
						}
					}

					if (array_key_exists($colorCode . '_' . (string) $compositionAttributes['Unique-type-id'] . '_' . $no, $this->executed))
					{
						$skipTranslationStore = true;
						break;
					}

					$this->counter++;
					$table = RTable::getAdminInstance($this->tableClassName)
						->setOption('lockingMethod', 'Sync');
					$isNew = true;
					$id    = $this->findSyncedId($this->syncName, $colorCode . '_' . (string) $compositionAttributes['Unique-type-id'], $no);

					if ($id)
					{
						if ($table->load($id))
						{
							$isNew = false;
						}
						else
						{
							$this->deleteSyncedId($this->syncName, $colorCode . '_' . (string) $compositionAttributes['Unique-type-id'], $no);
						}
					}

					if (!$table->save($row))
					{
						throw new Exception($table->getError());
					}

					$this->recordSyncedId(
						$this->syncName, $colorCode . '_' . (string) $compositionAttributes['Unique-type-id'], $table->id, $no, $isNew
					);
				}

				if (!$this->translationTable || $skipTranslationStore)
				{
					continue;
				}

				$itemIds = array();

				foreach ($compositions as $composition)
				{
					$compositionAttributes = $composition->attributes();
					$langCode              = $this->getLanguageTag((string) $compositionAttributes['LanguageCode']);
					$colorCode             = '';

					if ($strUpperLangTag == $langCode)
					{
						continue;
					}

					if (isset($compositionAttributes['ColourCode']))
					{
						$colorCode = (string) $compositionAttributes['ColourCode'];

						if (!$this->findSyncedId('fengel.attribute', 'Farve_' . $colorCode, $no))
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf(
									'PLG_RB_SYNC_FENGEL_PRODUCT_TYPE_NOT_FOUND', (string) $compositionAttributes['ColourCode'] . ' (' . $no . ')'
								),
								'warning'
							);
							continue;
						}
					}

					$id = $this->findSyncedId($this->syncName, $colorCode . '_' . (string) $compositionAttributes['Unique-type-id'], $no);

					if (!$id)
					{
						continue;
					}

					$table = RTable::getAdminInstance($this->tableClassName)
						->setOption('lockingMethod', 'Sync');

					if (!$table->load($id))
					{
						continue;
					}

					$langCode    = explode('-', $langCode);
					$langCode[0] = strtolower($langCode[0]);
					$langCode    = implode('-', $langCode);

					$result = $this->storeTranslation(
						$this->translationTable,
						$table,
						$langCode,
						array(
							'id' => $table->id,
							'type' => (string) $compositionAttributes['Type'],
							'quality' => (string) $compositionAttributes['Quality']
						)
					);

					if ($result !== true)
					{
						throw new Exception($result);
					}

					$itemIds[$id] = $id;
				}

				if (count($itemIds) > 0)
				{
					$table = RTable::getAdminInstance($this->tableClassName)
						->setOption('lockingMethod', 'Sync');

					foreach ($itemIds as $itemId)
					{
						$table->load($itemId);
						$result = $this->deleteNotSyncingLanguages($this->translationTable, $table);

						if ($result !== true)
						{
							throw new Exception($result);
						}
					}
				}
			}

			// In last part delete not using items
			if (!$this->goToNextPart)
			{
				$db->transactionCommit();
				$db->transactionStart();

				// Remove items that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);

				// We are setting cron as finished (no more parts)
				$this->setCronAsFinished($this->cronName);
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		return $this->outputResult();
	}
}
