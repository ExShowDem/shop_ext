<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Words Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWords extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_words';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'word_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'sm';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'sm.id', 'id',
				'sm.word', 'word',
				'sm.shared', 'shared',
				'search_words',
				'word_shared',
				'scope'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$ordering  = is_null($ordering) ? 'sm.id' : $ordering;
		$direction = is_null($direction) ? 'DESC' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('sm.*')
			->select('IF(wsx.main_word_id IS NOT NULL, 1, 0) AS main_word')
			->from($db->qn('#__redshopb_word', 'sm'))
			->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx') . ' ON wsx.main_word_id = sm.id')
			->group('sm.id');

		// Filter by share
		$synonymShared = $this->getState('filter.word_shared');

		if ($synonymShared == '0' || $synonymShared == 'false')
		{
			$query->where($db->qn('sm.shared') . ' = 0');
		}
		elseif ($synonymShared == '1' || $synonymShared == 'true')
		{
			$query->where($db->qn('sm.shared') . ' = 1');
		}

		$scope          = $this->getState('filter.scope');
		$wsxsJoinExists = false;

		switch ($scope)
		{
			case 'main_word':
				$query->where('wsx.main_word_id != 0');
				break;
			case 'synonym':
				$wsxsJoinExists = true;
				$query->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsxs') . ' ON wsxs.synonym_word_id = sm.id')
					->where('wsx.main_word_id IS NULL');
				break;
		}

		// Filter search
		$search = $this->getState('filter.search_words');

		if (!empty($search))
		{
			$like = 'LIKE ' . $db->quote('%' . $db->escape($search, true) . '%');

			switch ($scope)
			{
				// This case help select main words, when user write synonym
				case 'main_word':
					$query->leftJoin($db->qn('#__redshopb_word', 'sm2') . ' ON wsx.synonym_word_id = sm2.id')
						->where('(sm.word ' . $like . ' OR sm2.word ' . $like . ')');
					break;

				// This case help select synonyms, when user write main word
				case 'synonym':
					if (!$wsxsJoinExists)
					{
						$query->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsxs') . ' ON wsxs.synonym_word_id = sm.id');
					}

					$query->leftJoin($db->qn('#__redshopb_word', 'sm2') . ' ON wsxs.main_word_id = sm2.id')
						->where('(sm.word ' . $like . ' OR sm2.word ' . $like . ')');
					break;
				default:
					$query->where('sm.word ' . $like);
					break;
			}
		}

		// Filter above some word id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('sm.id') . ' > ' . (int) $previousId);
		}

		$avoidId = $this->getState('filter.avoid_id', null);

		if (is_numeric($avoidId))
		{
			$query->where('sm.id != ' . (int) $avoidId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'sm.id';
		$direction = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$staticCache = &$this->getStaticCache();

		$hash = $this->getStateHash();

		if (isset($staticCache[$hash]))
		{
			return $staticCache[$hash];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (!empty($items))
		{
			$mainIds    = array(0);
			$synonymIds = array(0);

			foreach ($items as $key => $item)
			{
				if (!$item->main_word)
				{
					$synonymIds[$item->id] = $key;
				}
				else
				{
					$mainIds[$item->id] = $key;
				}
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('w.word as synonym')
				->select('w.id as synonym_id')
				->select('wsx2.synonym_word_id')
				->select('w3.word AS main_word')
				->select('NULL AS main_word_id')
				->from($db->qn('#__redshopb_word', 'w'))
				->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx') . ' ON wsx.synonym_word_id = w.id')
				->leftJoin($db->qn('#__redshopb_word', 'w3') . ' ON w3.id = wsx.main_word_id')
				->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx2') . ' ON wsx2.main_word_id = wsx.main_word_id')
				->where('(wsx2.synonym_word_id IN (' . implode(',', array_keys($synonymIds)) . ') AND wsx2.synonym_word_id != w.id)');

			$subQuery = $db->getQuery(true)
				->select('w2.word as synonym')
				->select('w2.id as synonym_id')
				->select('NULL AS synonym_word_id')
				->select('NULL AS main_word')
				->select('wsx3.main_word_id')
				->from($db->qn('#__redshopb_word', 'w2'))
				->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx3') . ' ON wsx3.synonym_word_id = w2.id')
				->where('wsx3.main_word_id IN (' . implode(',', array_keys($mainIds)) . ')');

			$query->union($subQuery);

			$results = $db->setQuery($query)
				->loadObjectList();

			if ($results)
			{
				foreach ($results as $result)
				{
					if (isset($result->synonym_word_id))
					{
						$key = $synonymIds[$result->synonym_word_id];
					}
					else
					{
						$key = $mainIds[$result->main_word_id];
					}

					if (!isset($items[$key]->synonyms))
					{
						$items[$key]->synonyms = array();
					}

					if (!isset($items[$key]->synonyms_ids))
					{
						$items[$key]->synonyms_ids = array();
					}

					if ($items[$key]->id != $result->synonym_id && !in_array($result->synonym_id, $items[$key]->synonyms_ids))
					{
						$items[$key]->synonyms[]     = $result->synonym;
						$items[$key]->synonyms_ids[] = $result->synonym_id;
					}

					if (!isset($items[$key]->main_words))
					{
						$items[$key]->main_words = array();
					}

					if (isset($result->main_word) && !in_array($result->main_word, $items[$key]->main_words))
					{
						$items[$key]->main_words[] = $result->main_word;
					}
				}
			}
		}

		$staticCache[$hash] = $items ? $items : false;

		return $staticCache[$hash];
	}
}
