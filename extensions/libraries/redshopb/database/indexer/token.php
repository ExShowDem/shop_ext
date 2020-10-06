<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Token class for the Finder indexer package.
 *
 * @since  1.13.0
 */
class RedshopbDatabaseIndexerToken
{
	/**
	 * This is the term that will be referenced in the terms table and the
	 * mapping tables.
	 *
	 * @var    string
	 * @since  1.13.0
	 */
	public $term;

	/**
	 * The stem is used to match the root term and produce more potential
	 * matches when searching the index.
	 *
	 * @var    string
	 * @since  1.13.0
	 */
	public $stem;

	/**
	 * If the token is numeric, it is likely to be short and uncommon so the
	 * weight is adjusted to compensate for that situation.
	 *
	 * @var    boolean
	 * @since  1.13.0
	 */
	public $numeric;

	/**
	 * Flag for phrase tokens.
	 *
	 * @var    boolean
	 * @since  1.13.0
	 */
	public $phrase;

	/**
	 * The length is used to calculate the weight of the token.
	 *
	 * @var    integer
	 * @since  1.13.0
	 */
	public $length;

	/**
	 * The weight is calculated based on token size.
	 *
	 * @var    integer
	 * @since  1.13.0
	 */
	public $weight;

	/**
	 * The simple language identifier for the token.
	 *
	 * @var    string
	 * @since  1.13.0
	 */
	public $language;

	/**
	 * List of synonyms
	 *
	 * @var array
	 * @since  1.13.0
	 */
	public $synonyms = array();

	/**
	 * Method to construct the token object.
	 *
	 * @param   mixed   $term    The term as a string for words or an array for phrases.
	 * @param   string  $lang    The simple language identifier.
	 * @param   string  $spacer  The space separator for phrases. [optional]
	 *
	 * @since  1.13.0
	 */
	public function __construct($term, $lang, $spacer = ' ')
	{
		$this->language = $lang;

		// Tokens can be a single word or an array of words representing a phrase.
		if (is_array($term))
		{
			// Populate the token instance.
			$this->term    = implode($spacer, $term);
			$this->stem    = implode($spacer, array_map(array('RedshopbDatabaseIndexerHelper', 'stem'), $term, array($lang)));
			$this->numeric = false;
			$this->phrase  = true;
			$this->length  = StringHelper::strlen($this->term);

			/*
			 * Calculate the weight of the token.
			 *
			 * 1. Length of the token up to 30 and divide by 30, add 1.
			 * 2. Round weight to 4 decimal points.
			 */
			$this->weight = (($this->length >= 30 ? 30 : $this->length) / 30) + 1;
			$this->weight = round($this->weight, 4);
		}
		else
		{
			// Populate the token instance.
			$this->term    = $term;
			$this->stem    = RedshopbDatabaseIndexerHelper::stem($this->term, $lang);
			$this->numeric = (is_numeric($this->term) || (bool) preg_match('#^[0-9,.\-\+]+$#', $this->term));
			$this->phrase  = false;
			$this->length  = StringHelper::strlen($this->term);

			/*
			 * Calculate the weight of the token.
			 *
			 * 1. Length of the token up to 15 and divide by 15.
			 * 2. If numeric, multiply weight by 1.5.
			 * 3. Round weight to 4 decimal points.
			 */
			$this->weight = (($this->length >= 15 ? 15 : $this->length) / 15);
			$this->weight = ($this->numeric == true ? $this->weight * 1.5 : $this->weight);
			$this->weight = round($this->weight, 4);
		}
	}
}
