<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

/**
 * Transalations helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperTranslations
{
	/**
	 * stored languages
	 * @var  array
	 */
	protected static $storedLanguages = array();

	/**
	 * Check Translate Existing
	 *
	 * @param   object        $translationTable  Table parameters
	 * @param   array|object  $original          Values original item
	 * @param   string        $languageTag       Language tag
	 * @param   array         $translateValues   Translate values
	 *
	 * @return boolean|mixed
	 */
	public static function storeTranslation($translationTable, $original, $languageTag, $translateValues)
	{
		$original           = (array) $original;
		$rcTransOriginals   = RTranslationTable::createOriginalValueFromColumns($original, $translationTable->columns);
		$now                = Date::getInstance();
		$nowFormatted       = $now->toSql();
		$uniqueKey          = array();
		$translateTableName = RTranslationTable::getTranslationsTableName($translationTable->table, '');
		$db                 = Factory::getDbo();
		$query              = $db->getQuery(true)
			->select('rctranslations_id')
			->from($db->qn($translateTableName))
			->where('rctranslations_language = ' . $db->q($languageTag));

		foreach ($translationTable->primaryKeys as $primaryKey)
		{
			$uniqueKey[$primaryKey] = $original[$primaryKey];
			$query->where($db->qn($primaryKey) . ' = ' . $db->q($original[$primaryKey]));
		}

		$translationId = $db->setQuery($query)->loadResult();

		if ($translationId)
		{
			$query->clear()
				->update($db->qn($translateTableName))
				->where('rctranslations_id = ' . (int) $translationId)
				->set('rctranslations_modified = ' . $db->q($nowFormatted))
				->set('rctranslations_modified_by = ' . $db->q(Factory::getUser()->get('id')))
				->set('rctranslations_originals = ' . $db->q($rcTransOriginals))
				->set('rctranslations_language = ' . $db->q($languageTag));

			foreach ($translationTable->columns as $column)
			{
				if (isset($translateValues[$column]))
				{
					$query->set($db->qn($column) . ' = ' . $db->q($translateValues[$column]));
				}
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}
		}
		else
		{
			$values = array();

			foreach ($translateValues as $translateValue)
			{
				$values[] = $db->q($translateValue);
			}

			$query->clear()
				->insert($db->qn($translateTableName))
				->columns(
					implode(', ', array_keys($translateValues))
					. ', rctranslations_modified, rctranslations_state, rctranslations_originals, rctranslations_language, rctranslations_modified_by'
				)
				->values(
					implode(', ', $values)
					. ', ' . $db->q($nowFormatted) . ', 1'
					. ', ' . $db->q($rcTransOriginals)
					. ', ' . $db->q($languageTag)
					. ', ' . $db->q(Factory::getUser()->get('id'))
				);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}
		}

		$uniqueKey = json_encode($uniqueKey);

		if (!isset(self::$storedLanguages[$translateTableName]))
		{
			self::$storedLanguages[$translateTableName] = array();
		}

		if (!isset(self::$storedLanguages[$translateTableName][$uniqueKey]))
		{
			self::$storedLanguages[$translateTableName][$uniqueKey] = array();
		}

		self::$storedLanguages[$translateTableName][$uniqueKey][] = $languageTag;

		return true;
	}

	/**
	 * check if language available
	 *
	 * @param   string  $langCode  language code
	 *
	 * @return  boolean
	 */
	public static function checkLanguageAvailable($langCode = '')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('lang_code')
			->from($db->qn('#__languages'));
		$db->setQuery($query);
		$languages = $db->loadObjectList();

		$langAvailable = 0;

		foreach ($languages as $language)
		{
			if ($language->lang_code == $langCode)
			{
				$langAvailable = 1;
			}
		}

		if ($langAvailable == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
