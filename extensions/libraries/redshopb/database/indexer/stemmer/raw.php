<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Snowball stemmer class for the Finder indexer package.
 *
 * @since  1.13.0
 */
class RedshopbDatabaseIndexerStemmerRaw extends RedshopbDatabaseIndexerStemmer
{
	/**
	 * Method to stem a token and return the root.
	 *
	 * @param   string  $token  The token to stem.
	 *
	 * @return  string  The root token.
	 *
	 * @since   1.13.0
	 */
	public function stem($token)
	{
		return $token;
	}
}
