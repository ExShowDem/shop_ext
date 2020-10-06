<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

/**
 * Terms Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTerms extends RedshopbModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	         = $this->getDbo();
		$query       = $db->getQuery(true);
		$joomlaQuery = clone $query;
		$search      = $this->getState('filter.search_terms', '');

		$joomlaQuery->select(
			array (
				'CONCAT(' . $db->qn('c.title') . ',\' [Category: \', ' . $db->qn('cat.title') . ',\', Content: Joomla]\') AS ' . $db->qn('name'),
				'CONCAT(\'content.\',' . $db->qn('c.id') . ') AS ' . $db->qn('id')
			)
		)
			->from($db->qn('#__content', 'c'))
			->innerJoin($db->qn('#__categories', 'cat') . ' ON ' . $db->qn('c.catid') . ' = ' . $db->qn('cat.id'))
			->where($db->qn('c.state') . ' = 1');

		if (!empty($search))
		{
			$joomlaQuery->where($db->qn('c.title') . ' LIKE ' . $db->q('%' . $search . '%'));
		}

		if (ComponentHelper::isInstalled('com_reditem'))
		{
			$aesirQuery = clone $query;
			$aesirQuery->select(
				array (
					'CONCAT(' . $db->qn('i.title') . ',\' [Type: \', ' . $db->qn('t.title') . ',\', Content: Aesir]\') as ' . $db->qn('name'),
					'CONCAT(\'aesir.\',' . $db->qn('i.id') . ') AS ' . $db->qn('id')
				)
			)
				->from($db->qn('#__reditem_items', 'i'))
				->innerJoin($db->qn('#__reditem_types', 't') . ' ON ' . $db->qn('t.id') . ' = ' . $db->qn('i.type_id'))
				->where($db->qn('i.published') . ' = 1');

			if (!empty($search))
			{
				$aesirQuery->where($db->qn('i.title') . ' LIKE ' . $db->q('%' . $search . '%'));
			}

			$joomlaQuery->union($aesirQuery);
		}

		$query->select('*')->from($joomlaQuery, 'data');

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'id';
		$direction = !empty($directionList) ? $directionList : 'DESC';
		$query->order('data.' . $db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
