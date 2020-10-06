<?php
/**
 * @package    Redshopb2b.Cli
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CliApplication;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

/**
 * This script will checkin all checked out items in database
 *
 * @package  Redshopb2b.Cli
 * @since    1.0.19
 */
class Globalcheckin extends CliApplication
{
	/**
	 * @var JDatabaseDriver
	 */
	private $db;

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{
		// Print a blank line.
		$this->out();

		$this->out('============================');
		$this->out('Joomla Global Check-in');
		$this->out('============================');

		$this->db = Factory::getDbo();

		$tables = $this->getTables();

		foreach ($tables as $table)
		{
			$count = $this->checkin($table);

			if ($count)
			{
				$this->out(sprintf('Checked in %d row(s) from %s', $count, $table));
			}
		}

		$this->out('Done !');
	}

	/**
	 * Get all eligible tables
	 *
	 * @return array
	 */
	private function getTables()
	{
		$tables = $this->db->getTableList();

		foreach ($tables as $i => $tn)
		{
			// Make sure we get the right tables based on prefix
			if (stripos($tn, $this->get('dbprefix')) !== 0)
			{
				unset($tables[$i]);
				continue;
			}

			$fields = $this->db->getTableColumns($tn);

			// Make sure it can actually be checked out
			if (!(isset($fields['checked_out']) && isset($fields['checked_out_time'])))
			{
				unset($tables[$i]);
				continue;
			}
		}

		return $tables;
	}

	/**
	 * Checkin a table
	 *
	 * @param   string  $table  table name
	 *
	 * @return integer number of checked in rows
	 */
	private function checkin($table)
	{
		$count = $this->getCheckedOutCount($table);

		if ($count)
		{
			$this->checkinTable($table);
		}

		return $count;
	}

	/**
	 * Get number of checked out rows
	 *
	 * @param   string  $table  table name
	 *
	 * @return mixed
	 */
	private function getCheckedOutCount($table)
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from($this->db->quoteName($table))
			->where('checked_out > 0');

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Check in a table
	 *
	 * @param   string  $table  table name
	 *
	 * @return boolean
	 */
	private function checkinTable($table)
	{
		$nullDate = $this->db->getNullDate();

		// Disable foreign key checks
		$this->db->setQuery('SET foreign_key_checks = 0');
		$this->db->execute();

		$query = $this->db->getQuery(true)
			->update($this->db->quoteName($table))
			->set('checked_out = 0')
			->set('checked_out_time = ' . $this->db->quote($nullDate));

		$this->db->setQuery($query);
		$this->db->execute();

		// Re-enable foreign key checks
		$this->db->setQuery('SET foreign_key_checks = 1');
		$this->db->execute();

		return true;
	}
}

CliApplication::getInstance('Globalcheckin')->execute();
