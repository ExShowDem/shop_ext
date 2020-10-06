<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  Default
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

// No direct access
defined('_JEXEC') or die;

/**
 * Class PlgVanir_SearchAesir_Synonyms
 *
 * @since  1.0.0
 */
class PlgVanir_SearchAesir_Synonyms extends CMSPlugin
{
	/**
	 * getCommonQueryPart
	 *
	 * @param   \JDatabaseQuery     $query  Query
	 * @param   ReditemEntityField  $field  Field
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function getCommonQueryPart($query, $field)
	{
		$db = Factory::getDBo();

		$query->from($db->qn('#__reditem_items', 'i'));

		$unionQueries = array();

		foreach ($field->getTypes() as $i => $type)
		{
			$unionQueries[] = $db->getQuery(true)
				->select('ite.id')
				->select($db->qn('ite.' . $field->get('fieldcode')))
				->from($db->qn($type->getTableName(), 'ite'))
				->where('ite.entity = ' . $db->q('item'));
		}

		$searchQuery = array_shift($unionQueries);

		if (!empty($unionQueries))
		{
			foreach ($unionQueries as $unionQuery)
			{
				$searchQuery->union($unionQuery, true);
			}
		}

		$query->select($db->qn('sq.' . $field->get('fieldcode')))
			->leftJoin('(' . $searchQuery . ') AS sq ON sq.id = i.id')
			->where('sq.id IS NOT NULL')
			->where('i.type_id IN (' . implode(',', $field->getTypes()->ids()) . ')');
	}

	/**
	 * @param   string  $title  Title
	 *
	 * @return mixed
	 */
	protected function getSynonyms($title)
	{
		static $result = [];

		if (array_key_exists($title, $result))
		{
			return $result[$title];
		}

		$result[$title] = false;

		$db    = Factory::getDBo();
		$query = $db->getQuery(true)
			->select('f.fieldcode')
			->from($db->qn('#__reditem_fields', 'f'))
			->where('f.type = ' . $db->q('synonym'))
			->where('f.published = 1');

		$fieldCode = $db->setQuery($query)
			->loadResult();

		if (!$fieldCode)
		{
			return $result[$title];
		}

		$field = \ReditemEntityField::loadFromFieldcode($fieldCode);

		if (!$field->isLoaded() || empty($field->getTypes()))
		{
			return $result[$title];
		}

		$query = $db->getQuery(true)
			->select($db->qn('sq.' . $fieldCode))
			->where('title LIKE ' . $db->q($title));

		$this->getCommonQueryPart($query, $field);

		$words = $db->setQuery($query)->loadColumn();

		if (!$words)
		{
			return $result[$title];
		}

		$meanings = [];

		foreach ($words as $word)
		{
			if (empty($word))
			{
				continue;
			}

			$meanings = array_merge($meanings, json_decode($word, true));
		}

		$meanings = array_values(array_unique($meanings));

		if (empty($meanings))
		{
			return $result[$title];
		}

		$query = $db->getQuery(true)
			->select('title');

		$this->getCommonQueryPart($query, $field);

		$like = array();

		foreach ($meanings as $meaning)
		{
			$like[] = $db->qn('sq.' . $fieldCode) . ' LIKE ' . $db->q('%"' . $db->escape($meaning, true) . '"%');
		}

		$query->where('(' . implode(' OR ', $like) . ')');

		$result[$title] = $db->setQuery($query)->loadColumn();

		return $result[$title];
	}

	/**
	 * @param   RedshopbDatabaseIndexerQuery   $object  Search object
	 *
	 * @return void
	 */
	public function onVanirSearchProcessString(RedshopbDatabaseIndexerQuery $object)
	{
		if ($object->searchSynonyms)
		{
			foreach ($object->included as $item)
			{
				if (empty($item->synonyms))
				{
					$item->synonyms = array();
				}

				if (!$item->phrase)
				{
					$synonyms = $this->getSynonyms($item->term);

					if (empty($synonyms))
					{
						continue;
					}

					foreach ($synonyms as $key => $synonym)
					{
						if (array_key_exists($synonym, $object->included))
						{
							continue;
						}

						if (array_key_exists($synonym, $object->excluded))
						{
							continue;
						}

						if (!in_array($synonym, $item->synonyms))
						{
							$item->synonyms[] = strtolower($synonym);
						}
					}
				}
			}
		}
	}
}
