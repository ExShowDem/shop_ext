<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Helper for database related things.
 *
 * @since  1.0
 */
abstract class RedshopbHelperDatabase
{
	/**
	 * Function to convert a string or array into a safe array to use for db queries
	 *
	 * @param   mixed   $values        Array or string to use as values
	 * @param   string  $filter        Filter to apply to the values
	 *                                 Available filters: 'integer' | 'string'
	 * @param   array   $removeValues  Items to remove/filter from the source array
	 *
	 * @return  array
	 */
	public static function filter($values, $filter = 'integer', $removeValues = array(''))
	{
		// Avoid null values
		if (null === $values)
		{
			return array();
		}

		// Convert comma separated values to arrays
		if (!is_array($values))
		{
			$values = (array) explode(',', $values);
		}

		// If all is selected remove filter
		if (in_array('*', $values))
		{
			return array();
		}

		// Remove undesired source values
		$values = array_diff($values, $removeValues);

		$filterer = new RedshopbDatabaseFilterArray($values, $filter);

		$filteredData = $filterer->filter();

		// Remove again undesired values from result
		return array_diff($filteredData, $removeValues);
	}

	/**
	 * Fast use proxy to filter integers
	 *
	 * @param   mixed  $data          Data to filter
	 * @param   array  $removeValues  Values that we want removed from the output
	 *
	 * @return  array
	 */
	public static function filterInteger($data, $removeValues = array(''))
	{
		return static::filter($data, 'integer', $removeValues);
	}

	/**
	 * Fast use proxy to filter strings
	 *
	 * @param   mixed  $data          Data to filter
	 * @param   array  $removeValues  Values that we want removed from the output
	 *
	 * @return  array
	 */
	public static function filterString($data, $removeValues = array(''))
	{
		return static::filter($data, 'string', $removeValues);
	}

	/**
	 * Function to easily add and rule out xref associations based on a new incoming array
	 *
	 * @param   string   $tableName      Main table class name
	 * @param   string   $mainColName    Column name of the main object
	 * @param   integer  $mainId         Main object id
	 * @param   string   $assocColName   Column name of the associated object (in the array)
	 * @param   string   $xrefTableName  Xref table class name
	 * @param   array    $newXref        Xref array of associatiated ids in the main object id
	 *
	 * @return  boolean
	 */
	public static function refreshXrefAssociation($tableName, $mainColName, $mainId, $assocColName, $xrefTableName, $newXref)
	{
		try
		{
			$table     = RedshopbTable::getAdminInstance($tableName);
			$xrefTable = RedshopbTable::getAdminInstance($xrefTableName);
		}
		catch (\Exception $exception)
		{
			// No valid table class name
			return false;
		}

		if (!$table->load($mainId))
		{
			// Cannot load main id
			return false;
		}

		$pluralAssocName = $assocColName . 's';

		if (!property_exists($table, $pluralAssocName) || !is_array($table->get($pluralAssocName)))
		{
			// Cannot get current xref array using the pluralized name of the associated object
			return false;
		}

		if (!property_exists($xrefTable, $mainColName) || !property_exists($xrefTable, $assocColName))
		{
			// No main or assoc column names in the xref table
			return false;
		}

		$idsToDelete = array_diff($table->get($pluralAssocName), $newXref);
		$idsToAdd    = array_diff($newXref, $table->get($pluralAssocName));

		// Adds the ids using the table save method
		if (!empty($idsToAdd))
		{
			foreach ($idsToAdd as $idToAdd)
			{
				if (isset($xrefTable->id))
				{
					$xrefTable->id = null;
				}

				$xrefTable->$mainColName  = null;
				$xrefTable->$assocColName = null;
				$xrefTable->reset();

				$data = array(
					$mainColName => $mainId,
					$assocColName => $idToAdd
				);

				$xrefTable->save($data);
			}
		}

		// Removes the ids using the table delete method
		if (!empty($idsToDelete))
		{
			foreach ($idsToDelete as $idToDelete)
			{
				if ($xrefTable->load(
					array(
						$mainColName => $mainId,
						$assocColName => $idToDelete
					)
				))
				{
					$xrefTable->delete();
				}
			}
		}

		return true;
	}
}
