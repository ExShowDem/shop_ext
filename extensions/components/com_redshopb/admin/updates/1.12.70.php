<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.12.70
 */
class Com_RedshopbUpdateScript_1_12_70
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  void
	 */
	public function execute()
	{
		jimport('redshopb.table.table');

		$this->fillTableAliases('#__redshopb_collection', 'Collection');
	}

	/**
	 * Generates aliases for the specified table, using "alias" and "name" fields
	 *
	 * @param   string  $table         Table name
	 * @param   string  $tableClass    Table class name
	 * @param   array   $aliasColumns  Columns conforming the alias (separated by dashes)
	 * @param   array   $keys          Additional table keys conforming the alias to look upon them before creating it
	 *
	 * @return  void
	 */
	public function fillTableAliases($table, $tableClass, $aliasColumns = array('name'), $keys = array())
	{
		$db = Factory::getDbo();

		// Loads and save every product to generate their aliases
		$query = $db->getQuery(true);
		$query->select(array($db->qn('id')))
			->from($db->qn($table));

		if (!empty($keys))
		{
			foreach ($keys as $key)
			{
				$query->select($db->qn($key));
			}
		}

		if (!empty($aliasColumns))
		{
			foreach ($aliasColumns as $column)
			{
				$query->select($db->qn($column));
			}
		}

		$records = $db->setQuery($query)->loadObjectList();

		if (empty($records))
		{
			return;
		}

		foreach ($records as $record)
		{
			// Empties alias record to force re-generation
			$record->alias = '';

			$alias = $this->checkGenerateAlias($record, $table, $aliasColumns, $keys);

			$query->clear()
				->update($table)
				->set($db->qn('alias') . ' = ' . $db->quote($alias))
				->where($db->qn('id') . ' = ' . $record->id);
			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Generates a unique alias for a current table table a name
	 *
	 * @param   string  $record        Record row
	 * @param   string  $table         Table name
	 * @param   array   $aliasColumns  Columns conforming the alias (separated by dashes)
	 * @param   array   $keys          Additional table keys conforming the alias to look upon them before creating it
	 *
	 * @return  string
	 */
	public function checkGenerateAlias($record, $table, $aliasColumns = array('name'), $keys = array())
	{
		$db             = Factory::getDbo();
		$query          = $db->getQuery(true);
		$generatedAlias = '';
		$baseAlias      = '';
		$i              = 1;

		while ($generatedAlias == '')
		{
			if ($baseAlias == '')
			{
				if (!empty($aliasColumns))
				{
					$baseName = '';

					foreach ($aliasColumns as $column)
					{
						if ($record->{$column} != '')
						{
							$baseName .= $record->{$column} . '-';
						}
					}

					$baseName  = substr($baseName, 0, strlen($baseName) - 1);
					$baseAlias = OutputFilter::stringURLSafe($baseName);
					$baseAlias = preg_replace("/[&'#]/", '', $baseAlias);
				}

				if (trim(str_replace('-', '', $baseAlias)) == '')
				{
					$baseAlias = $table . '-' . $record->id;
				}

				$alias = $baseAlias;
			}
			else
			{
				$alias = $baseAlias . '-' . $i;
			}

			// Checks the generated alias is not already in use by other product
			$query->clear()
				->select(array($db->qn('id')))
				->from($db->qn($table))
				->where($db->qn('alias') . ' = ' . $db->quote($alias))
				->where($db->qn('id') . ' <> ' . $record->id);

			if (!empty($keys))
			{
				foreach ($keys as $key)
				{
					$query->where($db->qn($key) . ' = ' . $db->q($record->{$key}));
				}
			}

			$db->setQuery($query);

			if ($db->loadResult())
			{
				$i++;
			}
			else
			{
				$generatedAlias = $alias;
			}
		}

		return $generatedAlias;
	}
}
