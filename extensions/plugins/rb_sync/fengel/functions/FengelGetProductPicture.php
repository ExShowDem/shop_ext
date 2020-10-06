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
use Joomla\CMS\Date\Date;

require_once __DIR__ . '/base.php';

/**
 * Get Product Pictures function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetProductPicture extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.media';

	/**
	 * @var string
	 */
	public $tableClassName = 'Media';

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
		$counter      = 0;
		$start        = microtime(1);
		$goToNextPart = false;

		try
		{
			$itemNo = '';
			$db->transactionStart();
			$query        = $db->getQuery(true);
			$isOneProduct = false;

			if (is_object($webserviceData) && isset($webserviceData->productId) && $this->client->sendParameters)
			{
				$query->select('remote_key')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q('fengel.product'))
					->where('local_id = ' . (int) $webserviceData->productId);
				$db->setQuery($query);
				$itemNo = $db->loadResult();
			}

			if ($itemNo && $itemNo != '')
			{
				$isOneProduct = true;
			}

			$xml = $this->client->Red_GetItemPicture('', $itemNo);

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			if (!$this->isCronExecuted($this->cronName))
			{
				// Fix flag from all old items as not synced
				$this->setSyncRowsAsExecuted($this->syncName, ($isOneProduct ? (int) $webserviceData->productId : null));
				$this->setCronAsExecuted($this->cronName);
				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$executed      = $this->getPreviousSyncExecutedList($this->syncName, ($isOneProduct ? (int) $webserviceData->productId : null));
				$countExecuted = count($executed);
			}

			foreach ($xml as $obj)
			{
				if (isset($obj->Pictures->Picture) && count($obj->Pictures->Picture) > 0)
				{
					if ($isOneProduct)
					{
						$productId = (int) $webserviceData->productId;
					}
					else
					{
						$productId = $this->findSyncedId('fengel.product', (string) $obj->No);

						if (!$productId)
						{
							RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', (string) $obj->No), 'warning');
							continue;
						}
					}

					foreach ($obj->Pictures->Picture as $picture)
					{
						$counter ++;

						if ($countExecuted > 0 && isset($executed[(string) $picture->ID . '_' . $productId]))
						{
							continue;
						}

						$table   = RTable::getInstance($this->tableClassName, 'RedshopbTable')
							->setOption('forceWebserviceUpdate', true)
							->setOption('lockingMethod', 'Sync');
						$isNew   = true;
						$allData = $this->findSyncedId($this->syncName, (string) $picture->ID, $productId, true, $table);

						if ($allData)
						{
							if (!$allData->deleted && $table->load($allData->local_id))
							{
								$isNew = false;
							}

							// If item not exists, then user delete it, so lets skip it
							elseif ($allData->deleted)
							{
								$this->recordSyncedId(
									$this->syncName, (string) $picture->ID, '', $productId, false,
									0, $allData->serialize, true
								);

								continue;
							}
							else
							{
								$this->deleteSyncedId($this->syncName, (string) $picture->ID, $productId);
							}
						}

						if ((string) $picture->Picturedata->PicturePath)
						{
							$row               = array();
							$row['product_id'] = $productId;
							$now               = Date::getInstance();
							$nowFormatted      = $now->toSql();
							$row['alt']        = (string) $picture->Picturedata->Description;
							$row['state']      = 1;

							switch ((string) $picture->Picturedata->View)
							{
								case 'Back':
									$row['view'] = 2;
									break;
								case 'Front':
									$row['view'] = 1;
									break;
								default:
									$row['view'] = 0;
							}

							$attributeId = $this->findSyncedId(
								'fengel.attribute', 'Farve_' . (string) $picture->Picturedata->ColorCode, (string) $obj->No
							);

							if ((string) $picture->Picturedata->ColorCode != ''
								&& $attributeId
							)
							{
								$row['attribute_value_id'] = $attributeId;
							}
							else
							{
								$row['attribute_value_id'] = null;
							}

							if ($isNew == true)
							{
								$row['created_date'] = $nowFormatted;

								if (!$table->save($row))
								{
									throw new Exception($table->getError());
								}
							}

							// Saving image
							$row['name'] = RedshopbHelperThumbnail::savingImage(
								(string) $picture->Picturedata->PicturePath, (string) $picture->Picturedata->PicturePath, $table->get('id'), true
							);

							if ($row['name'] === false)
							{
								$table->delete($table->get('id'));

								$this->recordSyncedId(
									$this->syncName, (string) $picture->ID, $table->get('id'),
									$productId, $isNew, 2, '', false, '', $table, 1
								);

								continue;
							}

							if ($table->get('name') != '')
							{
								$folderName = RedshopbHelperMedia::getFolderName($table->get('id'));
								$sourceFile = JPATH_SITE . '/media/com_redshopb/images/originals/products/' . $folderName . '/' . $table->get('name');

								if (JFile::exists($sourceFile))
								{
									if (sha1_file($sourceFile) != RedshopbHelperThumbnail::$shaCurrentFile)
									{
										RedshopbHelperThumbnail::deleteImage($table->get('name'));
									}
									else
									{
										RedshopbHelperThumbnail::deleteImage($row['name']);
										unset($row['name']);
									}
								}
							}

							if (!$table->save($row))
							{
								throw new Exception($table->getError());
							}

							$this->recordSyncedId(
								$this->syncName, (string) $picture->ID, $table->id, $productId,
								$isNew, 0, RedshopbHelperThumbnail::$shaCurrentFile, false, '', $table, 1
							);
						}

						if (microtime(1) - $start >= 15)
						{
							$goToNextPart = true;
							break;
						}
					}
				}

				if ($goToNextPart)
				{
					break;
				}
			}

			// In last part if some sync users not exists in new sync -> delete it
			if (!$goToNextPart)
			{
				$query->clear()
					->select('m.name, m.remote_path')
					->from($db->qn('#__redshopb_media', 'm'))
					->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON m.id = s.local_id')
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.execute_sync = 1');

				if ($isOneProduct)
				{
					$query->where('s.remote_parent_key = ' . $db->q((int) $webserviceData->productId));
				}

				$results = $db->setQuery($query)->loadColumn();

				if ($results)
				{
					foreach ($results as $result)
					{
						RedshopbHelperThumbnail::deleteImage($result);
					}
				}

				$subQuery = $db->getQuery(true)
					->select('local_id')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

				if ($isOneProduct)
				{
					$subQuery->where('remote_parent_key = ' . $db->q((int) $webserviceData->productId));
				}

				$query->clear()
					->delete($db->qn('#__redshopb_media'))
					->where('id IN (' . $subQuery . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->delete($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync IN (1,2)');

				if ($isOneProduct)
				{
					$subQuery->where('remote_parent_key = ' . $db->q((int) $webserviceData->productId));
				}

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 0')
					->where('name = ' . $db->q('GetProductPicture'));

				$db->setQuery($query)->execute();
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		if ($goToNextPart)
		{
			$countInXml = 0;

			foreach ($xml->ItemPicture as $obj)
			{
				if ($obj->Pictures->Picture)
				{
					foreach ($obj->Pictures->Picture as $picture)
					{
						if ((string) $picture->Picturedata->PicturePath)
						{
							$countInXml++;
						}
					}
				}
			}

			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter, $countInXml));

			return array('parts' => $countInXml - $counter, 'total' => $countInXml);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}
	}
}
