<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
/**
 * Word Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWord extends RedshopbModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);
		$item       = ArrayHelper::toObject($properties, CMSObject::class);

		$item->synonyms = array();

		if (!empty($properties['synonyms']))
		{
			$item->synonyms = $properties['synonyms'];
		}

		if (!$item->main_word)
		{
			$item->meanings = array_keys($item->synonyms);
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Method to get a single record using possible related data from the web service and optionally adding related data to it
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  false|object             Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$result = parent::getItemWS($pk, $addRelatedData);

		if ($result === false)
		{
			return false;
		}

		if (!empty($result->synonyms))
		{
			$firstSynonym = reset($result->synonyms);
			$synonyms     = array();

			if (is_array($firstSynonym))
			{
				foreach ($result->synonyms as $meaningId => $synonymsIds)
				{
					$meaningWord   = RedshopbEntityWord::getInstance($meaningId)
						->get('word');
					$synonymsWords = array();

					foreach ($synonymsIds as $synonymId)
					{
						$synonymsWords[] = RedshopbEntityWord::getInstance($synonymId)
							->get('word');
					}

					$synonyms[$meaningWord] = $synonymsWords;
				}
			}
			else
			{
				foreach ($result->synonyms as $synonymId)
				{
					if ($synonymId == $result->id)
					{
						continue;
					}

					$synonyms[] = RedshopbEntityWord::getInstance($synonymId)
						->get('word');
				}
			}

			$result->synonyms = $synonyms;
		}

		return $result;
	}

	/**
	 * Share/Un share items
	 *
	 * @param   mixed    $pks    id or array of ids of items to be shared/un shared
	 * @param   integer  $value  New desired state
	 *
	 * @return  boolean
	 */
	public function share($pks = null, $value = 1)
	{
		// Sanitize the ids.
		$pks = ArrayHelper::toInteger((array) $pks);

		if (empty($pks))
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

			return false;
		}

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_word'))
				->set('shared = ' . (int) $value)
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query)
				->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Share an item via web service
	 *
	 * @param   mixed  $pk  PK to be found to share (internal id)
	 *
	 * @return  record id | false
	 */
	public function shareWS($pk)
	{
		$this->operationWS = true;

		try
		{
			$pks = array($pk);
			$this->share($pks, 1);
		}
		catch (Exception $e)
		{
			return false;
		}

		return $pk;
	}

	/**
	 * Un share WS an item via web service
	 *
	 * @param   mixed  $pk  PK to be found to share (internal id)
	 *
	 * @return  record id | false
	 */
	public function unshareWS($pk)
	{
		$this->operationWS = true;

		try
		{
			$pks = array($pk);
			$this->share($pks, 0);
		}
		catch (Exception $e)
		{
			return false;
		}

		return $pk;
	}
}
