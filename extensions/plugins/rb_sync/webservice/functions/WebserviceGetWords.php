<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Table\Table;

require_once __DIR__ . '/base.php';

/**
 * Get Words function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetWords extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.word';

	/**
	 * @var string
	 */
	public $tableClassName = 'Word';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.0.0&option=redshopb&view=synonym'
			. '&api=hal&list[ordering]=id&list[direction]=ASC';

		parent::__construct();
	}

	/**
	 * Method for synchronize an single tag
	 *
	 * @param   object  $item   Tag object data
	 * @param   Table   $table  Table object
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function synchronizeItem($item, $table)
	{
		$remoteId = $item->id;

		// If another sync process for this tag is running. Skip this.
		if (isset($this->executed[$remoteId . '_']))
		{
			return false;
		}

		$row           = array();
		$isNew         = true;
		$ignoreLocalId = false;
		$newLocalId    = '';
		$itemData      = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey     = RedshopbHelperSync::generateHashKey($item, 'object');

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);

			// Returning true so we can mark other stored sync IDs so we don't loose them
			return true;
		}

		if (!$itemData)
		{
			$wordData = $this->findSyncedId($this->syncName, $item->word, '', true, $table);

			if ($wordData && !$wordData->deleted && $table->load($wordData->local_id))
			{
				$isNew         = true;
				$newLocalId    = $wordData->local_id;
				$ignoreLocalId = true;
				$this->deleteSyncedId($this->syncName, $item->word);
			}
		}
		else
		{
			if ($itemData)
			{
				if (!$itemData->deleted && $table->load($itemData->local_id))
				{
					$isNew = false;
				}

				// If item not exists, then user delete it, so lets skip it
				elseif ($itemData->deleted)
				{
					$this->skipItemUpdate($itemData);

					return false;
				}
				else
				{
					$this->deleteSyncedId($this->syncName, $remoteId);
				}
			}
		}

		$row['word']   = (string) $item->word;
		$row['shared'] = (int) $item->shared;

		$cloneTable = clone $table;

		// Avoid fire error for duplicate word
		if ($isNew && $cloneTable->load(array('word' => $row['word'])))
		{
			$table = $cloneTable;
		}

		if (isset($item->meanings) && !empty($item->meanings))
		{
			$row['main_word'] = 0;
			$row['meanings']  = (array) $item->meanings;

			foreach ($row['meanings'] as $key => $meaning)
			{
				$cloneTable = clone $table;

				if (is_string($meaning))
				{
					if (!$cloneTable->load(array('word' => $meaning)))
					{
						$cloneTable->reset();
						$cloneTable->set('id', 0);

						if (!$cloneTable->save(array('word' => $meaning)))
						{
							RedshopbHelperSync::addMessage($table->getError(), 'warning');

							return false;
						}

						$this->recordSyncedId(
							$this->syncName,
							$meaning,
							$cloneTable->get('id'),
							$remoteParentKey = '',
							true,
							$newSyncStatus   = 0,
							$serialize       = '',
							false,
							'',
							$cloneTable,
							$mainReference   = 1
						);
					}

					$row['meanings'][$key] = $cloneTable->get('id');
				}
			}

			$table->setOption('word_synonym.store', false);
		}
		else
		{
			$row['main_word'] = 1;
			$row['synonyms']  = (array) $item->synonyms;

			foreach ($row['synonyms'] as $key => $synonym)
			{
				$cloneTable = clone $table;

				if (is_string($synonym))
				{
					if (!$cloneTable->load(array('word' => $synonym)))
					{
						$cloneTable->reset();
						$cloneTable->set('id', 0);

						if (!$cloneTable->save(array('word' => $synonym)))
						{
							RedshopbHelperSync::addMessage($table->getError(), 'warning');

							return false;
						}

						$this->recordSyncedId(
							$this->syncName,
							$synonym,
							$cloneTable->get('id'),
							$remoteParentKey = '',
							true,
							$newSyncStatus   = 0,
							$serialize       = '',
							false,
							'',
							$cloneTable,
							$mainReference   = 1
						);
					}

					$row['synonyms'][$key] = $cloneTable->get('id');
				}
			}
		}

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		$changedProperties = $table->get('changedProperties', array());

		// Store synonym data
		if (isset($item->meanings) && !empty($item->meanings))
		{
			$changedProperties['meanings'] = $table->get('meanings', array());
		}

		// Store main word data
		else
		{
			$changedProperties['synonyms'] = $table->get('synonyms', array());
		}

		$changedProperties['main_word'] = $table->get('main_word', 1);
		$table->set('changedProperties', $changedProperties);

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName,
			$remoteId,
			$table->get('id'),
			$remoteParentKey = '',
			$isNew,
			$newSyncStatus   = 0,
			$serialize       = '',
			$ignoreLocalId,
			$newLocalId,
			$table,
			$mainReference   = 1,
			$hashedKey
		);

		return true;
	}
}
