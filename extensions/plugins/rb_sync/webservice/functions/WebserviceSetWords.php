<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\LanguageHelper;

require_once __DIR__ . '/base.php';

/**
 * Set Words function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceSetWords extends WebserviceFunctionBase
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
	 * @var string
	 */
	public $writeListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.0.0'
							. '&option=redshopb&view=synonym&api=hal';

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
		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @throws Exception
	 *
	 * @return  boolean
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();
		$this->setDefaultCronParams($webserviceData, $params);

		try
		{
			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->isCronExecuted($this->cronName))
			{
				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);
			}

			$db->transactionStart();

			$query = $db->getQuery(true)
				->select('sm.*')
				->from($db->qn('#__redshopb_word', 'sm'))
				->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx') . ' ON wsx.main_word_id = sm.id')
				->where('sm.shared = 1')
				->where('wsx.main_word_id != 0')
				->leftJoin(
					$db->qn('#__redshopb_sync', 's') . ' ON ' . $db->qn('s.local_id') . ' = ' . $db->qn('sm.id')
					. ' AND s.reference = ' . $db->q($this->syncName)
				)
				->where('s.local_id IS NULL')
				->group('sm.id')
				->order('sm.id ASC');

			$mainWords = $db->setQuery($query)
				->loadObjectList('id');

			if ($mainWords)
			{
				$this->counterTotal = count($mainWords);
				$url                = $this->client->serverUrl . $this->writeListUrl . '&access_token=' . $this->client->getAccessToken();
				$httpHeaders        = array();

				$translationFallback = 'true';
				$httpHeaders[]       = 'X-Webservice-Translation-Fallback: ' . $translationFallback;

				$defLangData = LanguageHelper::getLanguages('lang_code');
				$defLangData = $defLangData[$this->default_lang];
				$langTag     = $defLangData->lang_code . ',' . $defLangData->sef . ';q=1';

				$httpHeaders[] = 'Accept-Language: ' . $langTag;

				$query->clear()
					->select('sm.word, wsx.main_word_id AS id')
					->from($db->qn('#__redshopb_word', 'sm'))
					->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx') . ' ON wsx.synonym_word_id = sm.id')
					->where('sm.id != wsx.main_word_id')
					->where('wsx.main_word_id IN (' . implode(',', array_keys($mainWords)) . ')');

				$synonyms = $db->setQuery($query)
					->loadObjectList();

				if ($synonyms)
				{
					foreach ($synonyms as $synonym)
					{
						if (!isset($mainWords[$synonym->id]->synonyms))
						{
							$mainWords[$synonym->id]->synonyms = array();
						}

						$mainWords[$synonym->id]->synonyms[] = $synonym->word;
					}
				}

				foreach ($mainWords as $mainWord)
				{
					$this->counter++;
					$postFields = array(
						'word' => $mainWord->word,
						'shared' => true
					);

					if (isset($mainWord->synonyms))
					{
						foreach ($mainWord->synonyms as $key => $synonym)
						{
							$postFields['synonyms[' . $key . ']'] = $synonym;
						}
					}

					if (!function_exists('curl_exec'))
					{
						throw new Exception(Text::_('PLG_RB_SYNC_WEBSERVICE_ERROR_CURL_FEATURE_NOT_AVAILABLE'));
					}

					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);

					// Set return transfer
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

					curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);

					$result = curl_exec($curl);

					$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);

					if ($httpCode != 201)
					{
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_CURL_RETURN_STATUS', $result), 'error');

						continue;
					}

					$result = json_decode($result);

					$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');
					$table->load($mainWord->id);
					$table->setChangedProperties();

					$this->recordSyncedId(
						$this->syncName,
						$result->result,
						$mainWord->id,
						$remoteParentKey = '',
						$isNew           = true,
						$newSyncStatus   = 0,
						$serialize       = '',
						$ignoreLocalId   = false,
						$newLocalId      = '',
						$table,
						$mainReference   = 1
					);

					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						break;
					}
				}
			}

			if (!$this->goToNextPart)
			{
				$this->setCronAsFinished($this->cronName);
			}

			$db->transactionCommit();
		}
		catch (Exception $error)
		{
			$db->transactionRollback();

			if ($error->getMessage())
			{
				RedshopbHelperSync::addMessage($error->getMessage(), 'error');
			}

			return false;
		}

		return $this->outputResult();
	}
}
