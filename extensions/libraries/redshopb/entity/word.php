<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Word Entity
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 * @since       1.0
 */
final class RedshopbEntityWord extends RedshopbEntity
{
	/**
	 * List of synonyms
	 *
	 * @var    array
	 * @since  1.6.110
	 */
	protected $synonyms;

	/**
	 * Get synonyms ids for current word
	 *
	 * @return  array
	 */
	public function getSynonymsIds()
	{
		$item = $this->getItem();

		if (!empty($item))
		{
			if (!$item->main_word)
			{
				$ids      = array();
				$meanings = (array) $item->synonyms;

				foreach ($meanings as $meaning)
				{
					$ids = array_merge($ids, (array) $meaning);
				}

				return $ids;
			}
			else
			{
				return (array) $item->synonyms;
			}
		}
		else
		{
			return array();
		}
	}

	/**
	 * Method for load word by string
	 *
	 * @param   string  $word  String of word
	 *
	 * @return  self
	 */
	public static function loadWord($word)
	{
		$table = RTable::getAdminInstance('word', array(), 'com_redshopb');

		if ($table->load(array('word' => (string) $word)))
		{
			$item = self::getInstance($table->id);
		}
		else
		{
			$item = self::getInstance();
		}

		$item->getItem();

		return $item;
	}

	/**
	 * Get all synonyms of current word
	 *
	 * @return  array  List of synonyms
	 */
	public function getSynonyms()
	{
		if (!isset($this->synonymsWords)
			|| null === $this->synonymsWords)
		{
			$this->loadSynonyms();
		}

		return $this->synonymsWords;
	}

	/**
	 * Method for load all synonyms of current word
	 *
	 * @return  self
	 */
	protected function loadSynonyms()
	{
		if (!$this->hasId())
		{
			return $this;
		}

		if (!isset($this->synonymsWords))
		{
			$this->synonymsWords = array($this->word);
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('sm.word')
			->from($db->qn('#__redshopb_word', 'sm'))
			->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx') . ' ON wsx.synonym_word_id = sm.id')
			->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx2') . ' ON wsx2.main_word_id = wsx.main_word_id')
			->leftJoin($db->qn('#__redshopb_word', 'sm2') . ' ON wsx2.synonym_word_id = sm2.id')
			->where('sm2.word LIKE ' . $db->quote('%' . $db->escape($this->word, true) . '%'))
			->group('sm.id');

		$results = $db->setQuery($query)
			->loadColumn();

		if ($results)
		{
			$this->synonymsWords = $results;
		}

		return $this;
	}
}
