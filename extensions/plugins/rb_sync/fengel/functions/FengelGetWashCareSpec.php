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
 * Get WashCareSpec function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetWashCareSpec extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.wash_care_spec';

	/**
	 * @var string
	 */
	public $tableClassName = 'Wash_Care_Spec';

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
			$xml = $this->client->getWashCareSpec();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

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

			foreach ($xml->Types as $obj)
			{
				$this->counterTotal++;

				if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
					continue;
				}

				if (array_key_exists((string) $obj->Type->Code . '_' . (string) $obj->Type->TypeCode, $this->executed))
				{
					continue;
				}

				$this->counter++;

				if ((string) $obj->Type->Code == '')
				{
					continue;
				}

				$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');

				$isNew              = true;
				$row                = array();
				$row['code']        = (string) $obj->Type->Code;
				$row['type_code']   = (string) $obj->Type->TypeCode;
				$row['description'] = (string) $obj->Type->Description;

				if (isset($obj->Type->Languages->Language) && count($obj->Type->Languages->Language) > 0)
				{
					foreach ($obj->Type->Languages->Language as $language)
					{
						if ($language->Code == strtoupper($this->lang) && (string) $language->Description != '')
						{
							$row['description'] = (string) $language->Description;
						}
					}
				}

				$oldImage    = '';
				$unSerialize = array('image' => '');
				$itemData    = $this->findSyncedId($this->syncName, (string) $obj->Type->Code, (string) $obj->Type->TypeCode, true, $table);

				if ($itemData)
				{
					if (!$itemData->deleted && $table->load($itemData->local_id))
					{
						$unSerialize = RedshopbHelperSync::mbUnserialize($itemData->serialize);
						$oldImage    = $table->get('image');
						$isNew       = false;
					}

					// If item not exists, then user delete it, so lets skip it
					elseif ($itemData->deleted)
					{
						$this->recordSyncedId(
							$this->syncName, (string) $obj->Type->Codee, '', $obj->Type->TypeCode, false,
							0, $itemData->serialize, true
						);

						continue;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, (string) $obj->Type->Code, (string) $obj->Type->TypeCode);
					}
				}

				$row['state'] = 1;

				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				if (false && (string) $obj->Type->Path != $unSerialize['image'])
				{
					$unSerialize['image'] = (string) $obj->Type->Path;

					if ($table->get('image') != '' && $oldImage == $table->get('image'))
					{
						RedshopbHelperThumbnail::deleteImage($table->get('image'), 1);
					}

					$imageName    = str_replace(' ', '%20', (string) $obj->Type->Path);
					$row['image'] = '';

					if (RedshopbHelperMedia::checkExtension($imageName))
					{
						$fileNameClean = RedshopbHelperMedia::replaceSpecial($imageName);

						if (strpos(JFile::stripExt($table->image), JFile::stripExt($fileNameClean)) === false)
						{
							$row['image'] = RedshopbHelperThumbnail::savingImage(
								(string) $obj->Type->Path, (string) $obj->Type->Path, $table->id, true, 'wash_care_spec'
							);

							if ($row['image'] === false)
							{
								$table->delete($table->id);

								$this->recordSyncedId(
									$this->syncName, (string) $obj->Type->Code, $table->id, (string) $obj->Type->TypeCode, $isNew, 2
								);

								continue;
							}

							if (!$table->save($row))
							{
								throw new Exception($table->getError());
							}
						}
					}
				}

				if ($this->translationTable)
				{
					if (isset($obj->Type->Languages->Language) && count($obj->Type->Languages->Language) > 0)
					{
						foreach ($obj->Type->Languages->Language as $language)
						{
							if ((string) $language->Code != strtoupper($this->lang) && (string) $language->Description != '')
							{
								$langCode    = explode('-', (string) $language->Code);
								$langCode[0] = strtolower($langCode[0]);
								$langCode    = implode('-', $langCode);

								$result = $this->storeTranslation(
									$this->translationTable,
									$table,
									$langCode,
									array(
										'id' => $table->id,
										'description' => (string) $language->Description
									)
								);

								if ($result !== true)
								{
									throw new Exception($result);
								}
							}
						}
					}

					$result = $this->deleteNotSyncingLanguages($this->translationTable, $table);

					if ($result !== true)
					{
						throw new Exception($result);
					}
				}

				$serialize = serialize($unSerialize);

				$this->recordSyncedId(
					$this->syncName, (string) $obj->Type->Code, $table->id, (string) $obj->Type->TypeCode,
					$isNew, 0, $serialize, false, '', $table, 1
				);
			}

			if (!$this->goToNextPart)
			{
				$db->transactionCommit();
				$db->transactionStart();

				// Remove items that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);
				$this->deleteRowsNotPresentInRemote($this->syncName, '', array(2));

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
