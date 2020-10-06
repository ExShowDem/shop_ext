<?php
/**
 * @package     Sh-404sef_Observer
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2016 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Sh404sefObserver\Helper;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * @since  2.6.0
 */
class UrlHelper
{
	/**
	 * @param   string  $from     From
	 * @param   string  $to       To
	 * @param   array   $actions  Actions
	 *
	 * @return void
	 * @since  2.6.0
	 */
	public function replaceUrl($from, $to, $actions = ['alias', 'meta', 'redirect'])
	{
		$db = Factory::getDbo();

		foreach ((array) $actions as $action)
		{
			switch ($action)
			{
				case 'meta':
				case 'alias':
					$table = $action == 'meta' ? '#__sh404sef_metas' : '#__sh404sef_aliases';

					$query = $db->getQuery(true)
						->select('id')
						->from($db->qn($table))
						->where('newurl = ' . $db->q($from));

					// Only if 'from' exists then it make sense to update and delete something
					if ($db->setQuery($query, 0, 1)->loadResult())
					{
						$query = $db->getQuery(true)
							->delete($db->qn($table))
							->where('newurl = ' . $db->q($to));

						$db->setQuery($query)->execute();

						$query = $db->getQuery(true)
							->update($db->qn($table))
							->set('newurl = ' . $db->q($to))
							->where('newurl = ' . $db->q($from));

						$db->setQuery($query)->execute();
					}
					break;
				case 'redirect':
					// If somewhere found redirect to old sef link then change that to a none-sef url
					// as it's following better on sh404sef
					$query = $db->getQuery(true)
						->update($db->qn('#__sh404sef_aliases'))
						->set('newurl = ' . $db->q($to))
						->where('newurl = ' . $db->q($from));

					$db->setQuery($query)->execute();

					// Get SEF URL
					$query = $db->getQuery(true)
						->select('oldurl')
						->from($db->qn('#__sh404sef_urls'))
						->where('newurl = ' . $db->q($to));

					// Delete redirect to itself if exists
					$query = $db->getQuery(true)
						->delete($db->qn('#__sh404sef_aliases'))
						->where('alias IN (' . $query . ')');

					$db->setQuery($query)->execute();

					$this->setRedirectIfDoesNotExist($from, $to);
					break;
			}
		}
	}

	/**
	 * @return integer
	 */
	public function getAliasTableMaxOrdering()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('MAX(ordering)')
			->from($db->qn('#__sh404sef_aliases'));

		return 1 + (int) $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * @param   string  $from  From
	 * @param   string  $to    To
	 *
	 * @return void
	 */
	public function setRedirectIfDoesNotExist($from, $to)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id, newurl')
			->from($db->qn('#__sh404sef_aliases'))
			->where('alias = ' . $db->q($from));

		$aliases = $db->setQuery($query)->loadObjectList('id');

		if (empty($aliases))
		{
			$insertObject = (object) [
				'newurl' => $to,
				'alias' => $from,
				'ordering' => $this->getAliasTableMaxOrdering()
			];

			$db->insertObject('#__sh404sef_aliases', $insertObject);
		}
		else
		{
			$one = array_pop($aliases);

			if ($one->newurl != $to)
			{
				$updateObject = (object) [
					'newurl' => $to,
					'id' => $one->id
				];

				$db->updateObject('#__sh404sef_aliases', $updateObject, ['id']);
			}

			if (!empty($aliases))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__sh404sef_aliases'))
					->where('id IN (' . implode(',', array_keys($aliases)) . ')');

				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * @param   array|integer  $ids         Ids
	 * @param   string         $table       Table name
	 * @param   boolean        $withItself  With itself
	 *
	 * @return array
	 * @since  2.6.0
	 */
	public static function getChildrenItems($ids, $table, $withItself = false)
	{
		$ids   = ArrayHelper::toInteger((array) $ids);
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('children.*')
			->from($db->qn($table, 'parent'))
			->leftJoin($db->qn($table, 'children') . ' ON children.lft BETWEEN parent.lft AND parent.rgt')
			->where('parent.id IN (' . implode(',', $ids) . ')')
			->group('children.id');

		if (!$withItself)
		{
			$query->where('children.id NOT IN (' . implode(',', $ids) . ')');
		}

		return (array) $db->setQuery($query)
			->loadObjectList('id');
	}

	/**
	 * @param   integer  $id  Id
	 *
	 * @return void
	 */
	public function deleteUrlById($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__sh404sef_urls'))
			->where('id = ' . (int) $id);

		$db->setQuery($query)->execute();
	}

	/**
	 * @param   integer  $id Id
	 *
	 * @return void
	 */
	public function deleteAliasById($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__sh404sef_aliases'))
			->where('id = ' . (int) $id);

		$db->setQuery($query)->execute();
	}
}
