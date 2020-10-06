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
 * Get Logos function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetLogos extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.logos';

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
		$db           = Factory::getDbo();
		$start        = microtime(1);
		$goToNextPart = false;
		$counter      = 0;

		try
		{
			$xml = $this->client->getLogos();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$lang                 = RTranslationHelper::getSiteLanguage();
			$translationTables    = RTranslationHelper::getInstalledTranslationTables();
			$tableTranslateExists = false;

			// Check existing translate table
			if (isset($translationTables['#__redshopb_logos']))
			{
				$translationTable     = $translationTables['#__redshopb_logos'];
				$tableTranslateExists = true;
			}

			$query = $db->getQuery(true)
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetWashCareSpec'));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				// Fix flag from all old items as not synced
				$query = $db->getQuery(true)
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where('reference = ' . $db->q($this->syncName));

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 1')
					->where('name = ' . $db->q('GetLogos'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select(array('s.*', 'CONCAT_WS(' . $db->q('_') . ', s.remote_key, s.remote_parent_key) AS concat_id'))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.execute_sync = 0');
				$db->setQuery($query);
				$executed      = $db->loadObjectList('remote_key');
				$countExecuted = count($executed);
			}

			foreach ($xml->Logo as $obj)
			{
				foreach ($obj as $typeName => $type)
				{
					$storeId = null;

					foreach ($type->Path as $onePath)
					{
						if ($countExecuted > 0 && isset($executed[md5((string) $onePath) . '_' . (string) $typeName]))
						{
							$counter++;
							$storeId = $executed[md5((string) $onePath) . '_' . (string) $typeName]->local_id;
							continue;
						}

						if ((string) $onePath == '')
						{
							continue;
						}

						$attributes = $onePath->attributes();

						if ($typeName == 'Ecotex')
						{
							if (strtoupper($lang) != (string) $attributes['Language'])
							{
								continue;
							}
						}

						$isNew = true;
						$table = RTable::getInstance('Logos', 'RedshopbTable')
							->setOption('forceWebserviceUpdate', true)
							->setOption('lockingMethod', 'Sync');
						$counter++;
						$row = array(
							'type' => (string) $typeName,
							'brand_id' => null
						);

						if ($typeName == 'ProductPDF')
						{
							$row['brand_id'] = $this->findSyncedId('fengel.category', (string) $attributes['Brand'], 'Brand');

							if (!$row['brand_id'])
							{
								RedshopbHelperSync::addMessage(
									Text::sprintf('PLG_RB_SYNC_FENGEL_LOGO_NOT_STORED_SYNC_CATEGORY_FIRST', (string) $onePath), 'warning'
								);
								continue;
							}
						}

						$id = $this->findSyncedId($this->syncName, md5((string) $onePath), (string) $typeName);

						if ($id)
						{
							if ($table->load($id))
							{
								$isNew = false;
							}
							else
							{
								$this->deleteSyncedId($this->syncName, md5((string) $onePath), (string) $typeName);
							}
						}

						if (!$table->save($row))
						{
							throw new Exception($table->getError());
						}

						$folderName = RedshopbHelperMedia::getFolderName($table->id);

						if ($table->image == ''
							|| !JFile::exists(JPATH_SITE . '/media/com_redshopb/images/originals/logos/' . $folderName . '/' . $table->image))
						{
							$row['image'] = RedshopbHelperThumbnail::savingImage((string) $onePath, (string) $onePath, $table->id, true, 'logos');

							if ($row['image'] === false)
							{
								throw new Exception;
							}
						}
						else
						{
							$imageName    = str_replace(' ', '%20', (string) $onePath);
							$row['image'] = '';

							if (RedshopbHelperMedia::checkExtension($imageName))
							{
								$fileNameClean = RedshopbHelperMedia::replaceSpecial($imageName);

								if (strpos(JFile::stripExt($table->image), JFile::stripExt($fileNameClean)) === false)
								{
									$row['image'] = RedshopbHelperThumbnail::savingImage(
										(string) $onePath, (string) $onePath, $table->id, true, 'logos'
									);

									if ($row['image'] === false)
									{
										throw new Exception;
									}
								}
								else
								{
									unset($row['image']);
								}
							}
						}

						if (!$table->save($row))
						{
							throw new Exception($table->getError());
						}

						$this->recordSyncedId($this->syncName, md5((string) $onePath), $table->id, (string) $typeName, $isNew);

						$storeId = $table->id;

						if (microtime(1) - $start >= 15)
						{
							$goToNextPart = true;
							break;
						}
					}

					// Store other translates
					if ($tableTranslateExists && (string) $typeName == 'Ecotex')
					{
						$table = RTable::getInstance('Logos', 'RedshopbTable')
							->setOption('forceWebserviceUpdate', true)
							->setOption('lockingMethod', 'Sync');

						foreach ($type->Path as $onePath)
						{
							if ($countExecuted > 0 && isset($executed[md5((string) $onePath) . '_' . (string) $typeName]))
							{
								$counter++;
								continue;
							}

							$attributes = $onePath->attributes();

							if ((string) $onePath == '' || strtoupper($lang) == (string) $attributes['Language'])
							{
								continue;
							}

							$counter++;

							if (!$this->findSyncedId($this->syncName, md5('3' . (string) $onePath), (string) $typeName))
							{
								if ($table->load($storeId))
								{
									$isNew = true;
									$image = RedshopbHelperThumbnail::savingImage((string) $onePath, (string) $onePath, $storeId, true, 'logos');

									if ($image === false)
									{
										throw new Exception;
									}

									$langCode    = explode('-', (string) $attributes['Language']);
									$langCode[0] = strtolower($langCode[0]);
									$langCode    = implode('-', $langCode);

									$result = $this->storeTranslation(
										$translationTable,
										$table,
										$langCode,
										array(
											'id' => $table->id,
											'image' => $image
										)
									);

									if ($result !== true)
									{
										throw new Exception($result);
									}
								}
							}
							else
							{
								$isNew = false;
							}

							$this->recordSyncedId($this->syncName, md5('3' . (string) $onePath), $storeId, (string) $typeName, $isNew);

							if (microtime(1) - $start >= 15)
							{
								$goToNextPart = true;
								break;
							}
						}

						if ((int) $table->id > 0)
						{
							$result = $this->deleteNotSyncingLanguages($translationTable, $table);

							if ($result !== true)
							{
								throw new Exception($result);
							}
						}
					}
				}
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		// In last part if some sync data not exists in new sync -> delete it
		if (!$goToNextPart)
		{
			$query->clear()
				->select('s.local_id')
				->from($db->qn('#__redshopb_sync', 's'))
				->where('s.reference = ' . $db->q($this->syncName))
				->where('s.execute_sync = 1');

			$results = $db->setQuery($query)->loadColumn();

			if ($results)
			{
				$query->clear()
					->delete($db->qn('#__redshopb_logos'))
					->where('id IN (' . implode(',', $results) . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->delete($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

				$db->setQuery($query)->exeecute();
			}

			$query->clear()
				->update($db->qn('#__redshopb_cron'))
				->set('execute_sync = 0')
				->where('name = ' . $db->q('GetLogos'));

			$db->setQuery($query)->execute();
		}

		if ($goToNextPart)
		{
			$countInXml = count($xml->Types);
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter, $countInXml));

			return array('parts' => $countInXml - $counter, 'total' => $countInXml);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}

		return true;
	}
}
