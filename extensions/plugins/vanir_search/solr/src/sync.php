<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
/**
 * [PlgVanirSearchSolrSync description]
 *
 * @since __VERSION__
 */
class PlgVanirSearchSolrSync
{
	/**
	 * Method to index products
	 *
	 * @param   PlgVanirSearchSolrBridge  $solrBridge  bridge class to solr client
	 * @param   int                       $start       db query limit start
	 * @param   int                       $limit       Number of records to index
	 * @param   int                       $fieldLimit  the maximum length of entity fields
	 * @param   string                    $entityName  'product' or 'category'
	 *
	 * @return integer next start increment
	 */
	public function indexRecords($solrBridge, $start, $limit, $fieldLimit, $entityName = 'product')
	{
		$entityName = strtolower($entityName);
		$app        = Factory::getApplication();
		$finished   = $app->getUserState('plg_vanir_search.' . $entityName . 'Finished', false);

		if ($finished)
		{
			return $start;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->qn('#__redshopb_' . $entityName))
			->where('name != ' . $db->q('Root'));

		$ids = $db->setQuery($query, $start, $limit)->loadColumn();

		if (empty($ids))
		{
			$app = Factory::getApplication();
			$app->setUserState('plg_vanir_search.' . $entityName . 'Finished', true);

			return $start;
		}

		$entityClass = 'PlgVanirSearchSolrEntity' . ucfirst($entityName);

		foreach ($ids AS $id)
		{
			$entity   = new $entityClass($id);
			$endpoint = array('RedshopbTable' . ucfirst($entityName));

			$solrBridge->indexEntity($entity, $endpoint, $fieldLimit);
		}

		return $start + $limit;
	}

	/**
	 * Method to get total number of product records
	 *
	 * @param	string  $entityName [description]
	 *
	 * @return integer
	 */
	public function getRecordCount($entityName)
	{
		$entityName = strtolower($entityName);

		$app   = Factory::getApplication();
		$count = $app->getUserState('plg_vanir_search.' . $entityName . 'Count', null);

		if (!is_null($count))
		{
			return $count;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from($db->qn('#__redshopb_' . $entityName));

		$count = $db->setQuery($query)->loadResult();

		if (empty($count))
		{
			$productCount = 0;
		}

		$app->setUserState('plg_vanir_search.' . $entityName . 'Count', $count);

		return $count;
	}

	/**
	 * Method to prepare text for indexing
	 *
	 * @param   string  $text         the text to prepare
	 * @param   int     $fieldLimit   the maximum length of the text
	 *
	 * @return string
	 */
	public static function prepareText($text, $fieldLimit)
	{
		$preparedText = mb_strtolower($text, 'UTF-8');

		$preparedText = preg_replace('@[\x00-\x08\x0B\x0C\x0E-\x1F]@', ' ', $preparedText);

		if (strlen($preparedText) > $fieldLimit)
		{
			$preparedText = substr($preparedText, 0, $fieldLimit);
		}

		return $preparedText;
	}
}
