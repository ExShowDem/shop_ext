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
 * Get Item Group function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetItemGroup extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.itemgroup';

	/**
	 * @var string
	 */
	public $tableClassName = 'Category';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties, array(
				'image'
			)
		);
	}

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
			$xml = $this->client->getItemGroup();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

			// Fix flag from all old products as not synced
			$this->setSyncRowsAsExecuted($this->syncName);

			foreach ($xml as $obj)
			{
				$row      = array();
				$table    = RTable::getInstance($this->tableClassName, 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$isNew    = true;
				$itemData = $this->findSyncedId($this->syncName, (string) $obj->Code, '', true, $table);

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
							$this->syncName, (string) $obj->Code, '', '', false,
							0, $itemData->serialize, true
						);

						continue;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, (string) $obj->Code);
					}
				}

				$row['state']       = 1;
				$row['company_id']  = null;
				$row['template_id'] = null;
				$row['description'] = '';
				$row['name']        = (string) $obj->Description;

				if (isset($obj->ItemTranslations->ItemTranslation))
				{
					foreach ($obj->ItemTranslations->ItemTranslation as $itemTranslation)
					{
						$objItemTranslation = $itemTranslation->attributes();

						if (isset($objItemTranslation['LanguageCode'])
							&& $objItemTranslation['LanguageCode'] == strtoupper($this->lang)
							&& isset($itemTranslation->Description)
							&& (string) $itemTranslation->Description != '')
						{
							$row['name'] = (string) $itemTranslation->Description;
							break;
						}
					}
				}

				$parentId = $this->findSyncedId($this->syncName, (string) $obj->ParentCode);

				if ((string) $obj->ParentCode && $parentId)
				{
					$row['parent_id'] = $parentId;
				}
				else
				{
					$row['parent_id'] = 1;
				}

				// Set the new parent id if parent id not matched OR while New/Save as Copy .
				if ($table->parent_id != $row['parent_id'] || $table->id == 0)
				{
					$table->setLocation($row['parent_id'], 'last-child');
				}

				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				// Rebuild the path for the category:
				if (!$table->rebuildPath($table->id))
				{
					throw new Exception($table->getError());
				}

				// Rebuild the paths of the category's children:
				if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
				{
					throw new Exception($table->getError());
				}

				if ($this->translationTable)
				{
					if (isset($obj->ItemGroupTranslations->ItemGroupTranslation) && count($obj->ItemGroupTranslations->ItemGroupTranslation) > 0)
					{
						foreach ($obj->ItemGroupTranslations->ItemGroupTranslation as $itemTranslation)
						{
							$objItemTranslation = $itemTranslation->attributes();

							if (isset($objItemTranslation['LanguageCode'])
								&& (string) $objItemTranslation['LanguageCode'] != strtoupper($this->lang)
								&& isset($itemTranslation->Description)
								&& (string) $itemTranslation->Description != '')
							{
								$langCode = $this->getLanguageTag((string) $objItemTranslation['LanguageCode']);

								$result = $this->storeTranslation(
									$this->translationTable,
									$table,
									$langCode,
									array(
										'id' => $table->id,
										'name' => (string) $itemTranslation->Description
									)
								);

								if ($result !== true)
								{
									throw new Exception($result);
								}
							}
						}
					}

					$result = $this->storeTranslation(
						$this->translationTable,
						$table,
						'da-DK',
						array(
							'id' => $table->id,
							'name' => (string) $obj->Description
						)
					);

					if ($result !== true)
					{
						throw new Exception($result);
					}

					$result = $this->deleteNotSyncingLanguages($this->translationTable, $table);

					if ($result !== true)
					{
						throw new Exception($result);
					}
				}

				$this->recordSyncedId(
					$this->syncName, (string) $obj->Code, $table->id, '', $isNew, 0, '',
					false, '', $table, 1
				);
			}

			do
			{
				$result = $this->deleteRowsNotPresentInRemote($this->syncName, $this->tableClassName, array(), true);
			}
			while ($result && count($result) > 0);

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
