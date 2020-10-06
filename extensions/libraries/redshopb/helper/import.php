<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * A Import helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperImport
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @param   array  $columns    List of columns for import
	 * @param   array  $row        List of values that failed to import
	 * @param   int    $rowNumber  Number or the current row
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public static function getErrorRowOutput($columns, $row, $rowNumber)
	{
		$output = array();

		// Prepare data with same columns
		foreach ($columns as $columnValue)
		{
			$output[] = '<strong>' . $columnValue . ':</strong> ' .
						(htmlentities($row[strtolower($columnValue)], ENT_NOQUOTES | ENT_IGNORE, 'utf-8'));
		}

		$output = implode(', ', $output);

		return '<br/>' . Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_ROW_OUTPUT', $rowNumber + 2) . ' ' . $output;
	}
}
