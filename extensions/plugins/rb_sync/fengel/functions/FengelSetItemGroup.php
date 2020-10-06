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
 * Set Item Group function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetItemGroup extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.tag';

	/**
	 * Send data in Set webservice, then store
	 *
	 * @param   object   $table        Table class from current item
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function store($table = null, $updateNulls = false)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();

			// We do not want to translate
			$db->translate = false;

			$query                = $db->getQuery(true);
			$tableTranslateExists = false;
			$translates           = null;

			if ((int) $table->parent_id > 1)
			{
				$query->clear()
					->select(array('t.level', $db->qn('s.remote_key', 'ParentCode')))
					->from($db->qn('#__redshopb_tag', 't'))
					->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON t.id = s.local_id AND s.reference = ' . $db->q($this->syncName))
					->where('t.id = ' . (int) $table->parent_id);
				$db->setQuery($query);
				$parent = $db->loadObject();
				$parent->level++;
			}
			else
			{
				$parent             = new stdClass;
				$parent->level      = 1;
				$parent->ParentCode = '';
			}

			if ($table->id)
			{
				$code              = $this->getCode($table->id);
				$translationTables = RTranslationHelper::getInstalledTranslationTables();

				// Check existing translate table
				if (isset($translationTables[$table->getTableName()]))
				{
					$translationTable     = $translationTables[$table->getTableName()];
					$translateTableName   = RTranslationTable::getTranslationsTableName($translationTable->table, '')
						->setOption('lockingMethod', 'Sync');
					$tableTranslateExists = true;

					$query->clear()
						->select('*')
						->from($translateTableName);

					foreach ($translationTable->primaryKeys as $primaryKey)
					{
						$query->where($db->qn($primaryKey) . ' = ' . $db->q($table->$primaryKey));
					}

					$db->setQuery($query);
					$translates = $db->loadObjectList();
				}

				$setXml = '<status>update</status>';
			}
			else
			{
				$code   = '';
				$setXml = '<status>create</status>';
			}

			$setXml .= '<Code>' . $code . '</Code><Description>'
				. $table->title . '</Description><ParentCode>'
				. $parent->ParentCode . '</ParentCode><Level>'
				. ($parent->level - 1) . '</Level>';

			if ($tableTranslateExists && $translates)
			{
				$setXml .= '<ItemGroupTranslations>';

				foreach ($translates as $translate)
				{
					$setXml .= '<ItemGroupTranslation LanguageCode="'
						. $translate->rctranslations_language . '"><Description>'
						. $translate->title . '</Description></ItemGroupTranslation>';
				}

				$setXml .= '</ItemGroupTranslations>';
			}
			else
			{
				$setXml .= '<ItemGroupTranslations/>';
			}

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><ItemGroups><ItemGroup>' . $setXml . '</ItemGroup></ItemGroups>';
			$xml    = $this->client->setItemGroup($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
				}

				$table->setOption('disableOnBeforeRedshopb', true);

				if (!$table->store($updateNulls))
				{
					throw new Exception;
				}

				$table->setOption('disableOnBeforeRedshopb', false);

				if (isset($xml->ItemGroups->Code) && !$this->findSyncedId($this->syncName, (string) $xml->ItemGroups->Code))
				{
					// Insert new item in sync table
					$this->recordSyncedId($this->syncName, (string) $xml->ItemGroups->Code, $table->id);
				}
			}

			// We put translation check back on
			$db->translate = true;

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Send query to delete item in Set webservice, then delete in DB
	 *
	 * @param   object   $table  Table class from current item
	 * @param   integer  $pk     The primary key of the node to delete.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($table, $pk = null)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();
			$key  = $table->getKeyName();
			$pk   = (is_null($pk)) ? $table->$key : $pk;
			$code = $this->getCode($pk);

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><ItemGroups><ItemGroup><status>delete</status><Code>'
				. $code . '</Code></ItemGroup></ItemGroups>';
			$xml    = $this->client->setItemGroup($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
				}

				$this->deleteSyncedId($this->syncName, $code);
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Get tag Code
	 *
	 * @param   int  $id  Id current tag
	 *
	 * @return mixed
	 */
	public function getCode($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('s.remote_key', 'Code'))
			->from($db->qn('#__redshopb_sync', 's'))
			->where('s.reference = ' . $db->q($this->syncName))
			->where('local_id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadResult();
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
		return true;
	}
}
