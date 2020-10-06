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

/**
 * Sync table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableSync extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_sync';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('reference', 'remote_key', 'local_id', 'remote_parent_key');

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $reference;

	/**
	 * @var  string
	 */
	public $remote_key;

	/**
	 * @var  integer
	 */
	public $local_id;

	/**
	 * @var  integer
	 */
	public $remote_parent_key;

	/**
	 * @var  string
	 */
	public $hash_key;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;

	/**
	 * Method to delete sync records by company id
	 *
	 * @param   array  $references  array of reference key associated with sync records
	 * @param   array  $conditions  array of conditional clauses to
	 *
	 * @return boolean
	 */
	public function deleteSyncRecords($references, $conditions)
	{
		$db                = Factory::getDbo();
		$reverseReferences = array_reverse($references);

		// Lets start from the end of list and finish with the main entity
		foreach (array_reverse($conditions) AS $index => $condition)
		{
			if (!$condition)
			{
				continue;
			}

			$query = $db->getQuery(true)
				->delete($db->qn('#__redshopb_sync'));

			// DO NOT JOIN #__redshopb_sync table with any other table since it's very expensive for big data
			if ($condition instanceof JDatabaseQuery)
			{
				$condition = $db->setQuery($condition)
					->loadColumn();

				if (empty($condition))
				{
					continue;
				}

				$condition = implode(',', RHelperArray::quote($condition));
			}

			// All variables must be presented in quotes(like a string) so query executes instantly
			elseif (is_numeric($condition))
			{
				$condition = $db->q($condition);
			}

			if (is_array($reverseReferences[$index]))
			{
				// Sanitize input.
				$reverseReferences[$index] = implode(
					',',
					RHelperArray::quote($reverseReferences[$index])
				);
			}

			$query->where('(' . $db->qn('reference') . ' IN (' . $reverseReferences[$index] . ')'
				. ' AND ' . $db->qn('local_id') . ' IN (' . $condition . '))', 'OR'
			);

			if (!$db->setQuery($query)->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}
