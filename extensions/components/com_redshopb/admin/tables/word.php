<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Word table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableWord extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_word';

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'shared'
	);

	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'word_synonym.store' => true
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.word'
		),
		'b2b' => array(
			'erp.webservice.word'
		)
	);

	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var [type]
	 */
	public $word;

	/**
	 * @var null
	 */
	public $shared = null;

	/**
	 * @var array
	 */
	protected $synonyms = array();

	/**
	 * @var integer
	 */
	protected $main_word = 1;

	/**
	 * @var array
	 */
	protected $meanings = array();

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->synonyms  = array();
		$this->meanings  = array();
		$this->main_word = 1;

		parent::reset();
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (!empty($this->synonyms))
		{
			// Sanitize synonyms
			$this->synonyms = array_values(array_unique($this->synonyms, SORT_STRING));
		}

		if (!empty($this->meanings))
		{
			// Sanitize meanings
			$this->meanings = array_values(array_unique($this->meanings, SORT_STRING));
		}

		if (empty($this->word))
		{
			$this->setError(Text::_('COM_REDSHOPB_WORD_CANNOT_BE_EMPTY'));

			return false;
		}

		$this->word = mb_strtolower($this->word);

		// Make sure there is no other record with the same word
		$cloneTable = clone $this;

		if ($cloneTable->load(array('word' => $this->word)) && $cloneTable->id != $this->id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_WORD_ALREADY_EXISTS', $this->word));

			return false;
		}

		return true;
	}

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return boolean True on success.
	 */
	public function beforeStore($updateNulls = false)
	{
		if (!parent::beforeStore($updateNulls))
		{
			return false;
		}

		if ($this->id)
		{
			$cloneTable = clone $this;

			if ($cloneTable->load($this->id))
			{
				if ($this->main_word != $cloneTable->main_word)
				{
					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->delete('#__redshopb_word_synonym_xref');

					if ($cloneTable->main_word == 0)
					{
						$query->where($db->qn('synonym_word_id') . ' = ' . (int) $this->id);
					}
					else
					{
						$query->where($db->qn('main_word_id') . ' = ' . (int) $this->id);
					}

					try
					{
						$db->setQuery($query)->execute();
					}
					catch (Exception $e)
					{
						$this->setError($e->getMessage());

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		if (!parent::store($updateNulls))
		{
			return false;
		}

		if ($this->getOption('word_synonym.store') && !$this->storeWordSynonymsXref())
		{
			return false;
		}

		return true;
	}

	/**
	 * Store the word synonyms references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeWordSynonymsXref()
	{
		if (!isset($this->meanings))
		{
			return true;
		}

		// Delete all items
		$db = Factory::getDbo();

		if ($this->main_word == 0)
		{
			$query = $db->getQuery(true)
				->delete('#__redshopb_word_synonym_xref')
				->where($db->qn('synonym_word_id') . ' = ' . (int) $this->id);

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			if (!is_array($this->meanings) || count($this->meanings) <= 0)
			{
				return true;
			}

			$query = $db->getQuery(true)
				->insert($db->qn('#__redshopb_word_synonym_xref'))
				->columns('main_word_id, synonym_word_id');

			foreach ($this->meanings as $meaning)
			{
				if (is_numeric($meaning))
				{
					$query->values((int) $meaning . ',' . (int) $this->id);
				}
				else
				{
					$cloneTable = clone $this;
					$meaning    = mb_strtolower($meaning);

					if (!$cloneTable->load(array('word' => $meaning)))
					{
						$cloneTable = clone $this;
						$cloneTable->reset();
						$cloneTable->set('id', 0);
						$cloneTable->getOption('word_synonym.store', false);

						if (!$cloneTable->save(array('word' => $meaning, 'main_word' => 1, 'shared' => $this->shared)))
						{
							$this->setError($cloneTable->getError());

							return false;
						}

						$query->values((int) $cloneTable->get('id') . ',' . (int) $cloneTable->get('id'));
					}

					$query->values((int) $cloneTable->get('id') . ',' . (int) $this->id);
				}
			}

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			$query = $db->getQuery(true)
				->delete('#__redshopb_word_synonym_xref')
				->where($db->qn('main_word_id') . ' = ' . (int) $this->id);

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			if (!is_array($this->synonyms) || count($this->synonyms) <= 0)
			{
				return true;
			}

			$query        = $db->getQuery(true)
				->insert($db->qn('#__redshopb_word_synonym_xref'))
				->columns('main_word_id, synonym_word_id');
			$foundSynonym = false;

			foreach ($this->synonyms as $synonym)
			{
				if (is_numeric($synonym))
				{
					if ((int) $synonym != (int) $this->id)
					{
						$query->values((int) $this->id . ',' . (int) $synonym);
						$foundSynonym = true;
					}
				}

				// Create new synonym from string
				elseif (is_string($synonym))
				{
					$cloneTable = clone $this;
					$synonym    = mb_strtolower($synonym);

					if (!$cloneTable->load(array('word' => $synonym)))
					{
						$cloneTable = clone $this;
						$cloneTable->reset();
						$cloneTable->set('id', 0);
						$cloneTable->getOption('word_synonym.store', false);

						if (!$cloneTable->save(array('word' => $synonym, 'main_word' => 0, 'shared' => $this->shared)))
						{
							$this->setError($cloneTable->getError());

							return false;
						}
					}

					$query->values((int) $this->id . ',' . (int) $cloneTable->get('id'));
					$foundSynonym = true;
				}
			}

			if ($foundSynonym)
			{
				$query->values((int) $this->id . ',' . (int) $this->id);

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					$this->setError($e->getMessage());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('main_word_id')
			->from($db->qn('#__redshopb_word_synonym_xref'))
			->where('main_word_id = ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->main_word = 1;
		}
		else
		{
			$this->main_word = 0;
		}

		if (!$this->loadWordSynonyms())
		{
			return false;
		}

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Load the synonyms related to this word
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadWordSynonyms()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('wsx1.synonym_word_id')
			->from($db->qn('#__redshopb_word_synonym_xref', 'wsx1'))
			->where('wsx1.synonym_word_id != ' . (int) $this->id);

		if ($this->main_word)
		{
			$query->where($db->qn('wsx1.main_word_id') . ' = ' . (int) $this->id);

			$synonyms = $db->setQuery($query)
				->loadColumn();

			if (!is_array($synonyms))
			{
				$synonyms = array();
			}

			$this->synonyms = $synonyms;
		}
		else
		{
			$query->select('wsx2.main_word_id')
				->leftJoin($db->qn('#__redshopb_word_synonym_xref', 'wsx2') . ' ON wsx2.main_word_id = wsx1.main_word_id')
				->where('wsx2.synonym_word_id = ' . (int) $this->id);

			$synonyms = $db->setQuery($query)
				->loadObjectList();

			if ($synonyms)
			{
				foreach ($synonyms as $synonym)
				{
					if (!array_key_exists($synonym->main_word_id, $this->synonyms))
					{
						$this->synonyms[$synonym->main_word_id] = array();
					}

					if ($synonym->synonym_word_id == $this->id)
					{
						continue;
					}

					$this->synonyms[$synonym->main_word_id][] = $synonym->synonym_word_id;
				}
			}
			else
			{
				$this->synonyms = array();
			}
		}

		return true;
	}
}
