<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\LanguageHelper;

/**
 * GetCustomer function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
abstract class FengelFunctionBase extends RedshopbHelperSync
{
	/**
	 * The FEngel client.
	 *
	 * @var  FEngelClient
	 */
	protected $client;

	/**
	 * @var string
	 */
	public $pluginName = 'fengel';

	/**
	 * @var string
	 */
	public $tableClassName;

	/**
	 * @var string
	 */
	public $tableName = '';

	/**
	 * @var string
	 */
	public $translationTable;

	/**
	 * @var string
	 */
	public $cronName;

	/**
	 * @var string
	 */
	public $lang = 'en-GB';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if ($this->tableClassName)
		{
			$table           = RTable::getInstance($this->tableClassName, 'RedshopbTable');
			$this->tableName = $table->get('_tbl');
			$nestedClass     = (stripos(get_parent_class($table), 'nested') === false ? false : true);

			if ($nestedClass)
			{
				$this->avoidOverrideWSProperties = array_merge(
					$this->avoidOverrideWSProperties, array(
						'lft', 'rgt', 'level', 'path'
					)
				);
			}

			$this->translationTable = $this->getSyncTranslationTable($this->tableName);
		}

		if (!$this->cronName)
		{
			$this->cronName = substr(get_class($this), 6);
		}

		$this->lang = RTranslationHelper::getSiteLanguage();
	}

	/**
	 * Set the client.
	 *
	 * @param   FEngelClient  $client  The client
	 *
	 * @return  FunctionBase
	 */
	public function setClient(FEngelClient $client)
	{
		$this->client = $client;

		return $this;
	}

	/**
	 * Kind of a dummy function to mimick redSOURCE interface so we can reuse later...
	 *
	 * @param   string  $fieldName  The field name in source
	 * @param   string  $value      The value associated
	 *
	 * @return object
	 */
	protected final function map($fieldName, $value)
	{
		$field         = new stdClass;
		$field->dbname = $fieldName;
		$field->value  = $value;

		return $field;
	}

	/**
	 * Transforms the 'fields' array to simple associative array (un-redSOURCING it)
	 *
	 * @param   array  $data  array of fields rows
	 *
	 * @return array
	 */
	protected final function fieldstoArray($data)
	{
		$res = array();

		foreach ($data as $row)
		{
			$newrow = array();

			foreach ($row as $key => $field)
			{
				$newrow[$key] = $field->value;
			}

			$res[] = $newrow;
		}

		return $res;
	}

	/**
	 * Get unique value
	 *
	 * @param   string  $nameField   Name field
	 * @param   string  $nameValue   Name value
	 * @param   int     $id          Current id item
	 * @param   bool    $checkEmail  Checking email
	 *
	 * @return string  unique name value
	 */
	public function getUnique($nameField, $nameValue, $id, $checkEmail = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('joomla_user_id'))
			->from($db->qn('#__redshopb_user', 'ru'))
			->where($db->qn('ru.id') . ' = ' . $db->q($id));
		$db->setQuery($query);
		$joomlaUserId = $db->loadResult();

		$query = $db->getQuery(true)
			->select($db->qn($nameField))
			->from($db->qn('#__users', 'u'));

		if ($joomlaUserId)
		{
			$query->where('u.id <> ' . (int) $joomlaUserId);
		}

		if ($checkEmail)
		{
			$emailName   = $nameValue[0];
			$emailDomain = $nameValue[1];
			$query->where($db->qn($nameField) . ' = ' . $db->q($emailName . '@' . $emailDomain));
		}
		else
		{
			$query->where($db->qn($nameField) . ' = ' . $db->q($nameValue));
		}

		$db->setQuery($query);

		while ($result = $db->loadResult())
		{
			$query->clear('where');

			if ($joomlaUserId)
			{
				$query->where('u.id <> ' . (int) $joomlaUserId);
			}

			if ($checkEmail)
			{
				$emailName = StringHelper::increment(str_replace('@' . $emailDomain, '', $result), 'dash');
				$query->where($db->qn($nameField) . ' = ' . $db->q($emailName . '@' . $emailDomain));
			}
			else
			{
				$nameValue = StringHelper::increment($result, 'dash');
				$query->where($db->qn($nameField) . ' = ' . $db->q($nameValue));
			}

			$db->setQuery($query);
		}

		if ($checkEmail)
		{
			return $emailName . '@' . $emailDomain;
		}
		else
		{
			return $nameValue;
		}
	}

	/**
	 * Returns address id from address table, creating it if necessary
	 *
	 * @param   array  $row  data to retrieve / create address from
	 *
	 * @return integer  address id
	 *
	 * @throws Exception
	 */
	protected function getAddress($row)
	{
		$address = RTable::getInstance('Address', 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true)
			->setOption('lockingMethod', 'Sync');

		if (!$address->load(
			array(
				'address' => (string) $row['address'],
				'address2' => (isset($row['address2']) ? (string) $row['address2'] : ''),
				'zip' => (string) $row['zip'],
				'city' => (string) $row['city'],
				'country_id' => (string) $row['country_id'],
			)
		))
		{
			// Create it
			$address = RTable::getInstance('Address', 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			if (!($address->save(
				array(
					'address'  => (string) $row['address'],
					'address2' => (string) $row['address2'],
					'zip' => (string) $row['zip'],
					'city' => (string) $row['city'],
					'country_id' => (string) $row['country_id'],

				)
			)))
			{
				throw new Exception($address->getError());
			}
		}

		return $address->id;
	}

	/**
	 * Reset Field Not Syncing Languages from current item
	 *
	 * @param   object        $translationTable  Table parameters
	 * @param   array|object  $original          Values original item
	 * @param   array         $fields            Names reset fields
	 *
	 * @return boolean|string
	 */
	public function resetFieldNotSyncingLanguages($translationTable, $original, $fields = array())
	{
		$original           = (array) $original;
		$translateTableName = RTranslationTable::getTranslationsTableName($translationTable->table, '');
		$uniqueKey          = array();

		foreach ($translationTable->primaryKeys as $primaryKey)
		{
			$uniqueKey[$primaryKey] = $original[$primaryKey];
		}

		$uniqueKey = json_encode($uniqueKey);

		if (isset(self::$storedLanguages[$translateTableName][$uniqueKey])
			&& is_array(self::$storedLanguages[$translateTableName][$uniqueKey])
			&& self::$storedLanguages[$translateTableName][$uniqueKey] > 0)
		{
			$languages = array();
			$db        = Factory::getDbo();
			$query     = $db->getQuery(true)
				->update($translateTableName);

			foreach (self::$storedLanguages[$translateTableName][$uniqueKey] as $oneLang)
			{
				$languages[] = $db->q($oneLang);
			}

			foreach ($fields as $field)
			{
				$query->set($db->qn($field) . ' = ' . $db->q(''));
			}

			$query->where('rctranslations_language NOT IN ('
				. implode(', ', $languages) . ')'
			);

			$uniqueKey = json_decode($uniqueKey);

			foreach ($uniqueKey as $key => $value)
			{
				$query->where($db->qn($key) . ' = ' . $db->q($value));
			}

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}
		}

		return true;
	}

	/**
	 * Generate GUID
	 *
	 * @param   string  $namespace  Namespace
	 *
	 * @return string
	 */
	public static function generateGUID($namespace = '')
	{
		$guid  = '';
		$uid   = uniqid('', true);
		$data  = $namespace;
		$data .= $_SERVER['REQUEST_TIME'];
		$data .= $_SERVER['HTTP_USER_AGENT'];

		if (isset($_SERVER['SERVER_ADDR']))
		{
			$data .= $_SERVER['SERVER_ADDR'];
		}

		$data .= $_SERVER['SERVER_PORT'];
		$data .= $_SERVER['REMOTE_ADDR'];
		$data .= $_SERVER['REMOTE_PORT'];
		$hash  = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
		$guid  = '{' . substr($hash,  0,  8) .
			'-' . substr($hash,  8,  4) .
			'-' . substr($hash, 12,  4) .
			'-' . substr($hash, 16,  4) .
			'-' . substr($hash, 20, 12) .
			'}';

		return $guid;
	}

	/**
	 * Get properly formatted language tag
	 *
	 * @param   string  $languageTag  Language tag received from web service
	 *
	 * @return string
	 */
	public function getLanguageTag($languageTag = '')
	{
		$langCode    = explode('-', (string) $languageTag);
		$langCode[0] = strtolower($langCode[0]);

		if (empty($langCode[1]))
		{
			$languages = LanguageHelper::getLanguages();

			// Search from the list of installed languages
			foreach ($languages as $language)
			{
				if ($language->sef == $langCode[0])
				{
					return $language->lang_code;
				}
			}

			// If everything else fails we use same two letters
			$langCode[1] = $langCode[0];
		}

		$langCode[1] = strtoupper($langCode[1]);

		return implode('-', $langCode);
	}

	/**
	 * Delete users
	 *
	 * @param   array  $results  Array id joomla users for deleting
	 *
	 * @return boolean
	 */
	public function deleteUsers($results = array())
	{
		// Prepare the logout options.
		$options = array(
			'clientid' => 0
		);
		$results = ArrayHelper::toInteger($results);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_user'))
			->where('joomla_user_id IN (' . implode(',', $results) . ')');

		$db->setQuery($query)->execute();

		foreach ($results as $result)
		{
			$user = User::getInstance($result);
			$user->delete();
			$this->app->logout($result, $options);
		}

		return true;
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
	abstract public function read(&$webserviceData, $params);
}
