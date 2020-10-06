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
 * Get Category function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetCategory extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.category';

	/**
	 * @var string
	 */
	public $tableClassName = 'Tag';

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
			$xml = $this->client->getCategories();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

			// Fix flag from all old products as not synced
			$this->setSyncRowsAsExecuted($this->syncName);

			foreach ($xml->Category as $obj)
			{
				$attributes = $obj->attributes();
				$type       = (string) $attributes['Name'];
				$i          = 0;
				$mainName   = array();
				$itemIds    = array();

				foreach ($obj->Name as $category)
				{
					$i++;
					$row      = array();
					$table    = RTable::getInstance($this->tableClassName, 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');
					$isNew    = true;
					$name     = (string) $category;
					$itemData = $this->findSyncedId($this->syncName, $name, $type, true, $table);

					if ($itemData)
					{
						if (!$itemData->deleted && $table->load($itemData->local_id))
						{
							$isNew = false;
						}

						// If item not exists, then user delete it, so lets skip it
						elseif ($itemData->deleted)
						{
							$this->recordSyncedId(
								$this->syncName, $name, '', $type, false,
								0, $itemData->serialize, true
							);

							continue;
						}
						else
						{
							$this->deleteSyncedId($this->syncName, $name, $type);
						}
					}

					$row['state']      = 1;
					$row['name']       = $name;
					$row['type']       = $type;
					$row['company_id'] = null;
					$mainName[$i]      = $name;

					if (!$table->save($row))
					{
						throw new Exception($table->getError());
					}

					$id           = $table->get('id');
					$itemIds[$id] = $id;

					$this->recordSyncedId(
						$this->syncName, $name, $id, $type, $isNew, 0, '',
						false, '', $table, 1
					);
				}

				if ($this->translationTable)
				{
					$i = 0;

					foreach ($obj->Translations as $translations)
					{
						$i++;

						if (!array_key_exists($i, $mainName))
						{
							continue;
						}

						$id = $this->findSyncedId($this->syncName, $mainName[$i], $type);

						if ($id)
						{
							foreach ($translations as $translation)
							{
								$objItemTranslation = $translation->attributes();

								if (isset($objItemTranslation['Language'])
									&& (string) $objItemTranslation['Language'] != strtoupper($this->lang)
									&& (string) $translation != '')
								{
									$langCode    = explode('-', (string) $objItemTranslation['Language']);
									$langCode[0] = strtolower($langCode[0]);
									$langCode    = implode('-', $langCode);
									$table       = RTable::getInstance($this->tableClassName, 'RedshopbTable')
										->setOption('forceWebserviceUpdate', true)
										->setOption('lockingMethod', 'Sync');

									if ($table->load($id))
									{
										$result = $this->storeTranslation(
											$this->translationTable,
											$table,
											$langCode,
											array(
												'id' => $table->id,
												'name' => (string) $translation
											)
										);

										if ($result !== true)
										{
											throw new Exception($result);
										}
									}
								}
								elseif (isset($objItemTranslation['Language'])
									&& (string) $objItemTranslation['Language'] == strtoupper($this->lang)
									&& (string) $translation != '')
								{
									$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
										->setOption('forceWebserviceUpdate', true)
										->setOption('lockingMethod', 'Sync');

									if ($table->load($id))
									{
										if (!$table->save(array('name' => (string) $translation)))
										{
											throw new Exception($table->getError());
										}
									}
								}
							}
						}
					}

					if (count($itemIds) > 0)
					{
						$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
							->setOption('forceWebserviceUpdate', true)
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
			}

			// Remove items that were not present in the XML data
			$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

		return true;
	}
}
