<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\LanguageHelper;

/**
 * Snowball stemmer class for the Finder indexer package.
 *
 * @since  1.13.0
 */
class RedshopbDatabaseIndexerStemmerSnowball extends RedshopbDatabaseIndexerStemmer
{
	/**
	 * @var   string
	 */
	protected $function;

	/**
	 * @var   string
	 */
	protected $lang;

	/**
	 * RedshopbDatabaseIndexerStemmerStemmerSnowball constructor.
	 *
	 * @param   string  $lang  Language tag
	 *
	 * @since   1.13.0
	 */
	public function __construct($lang = '*')
	{
		parent::__construct($lang);

		$this->lang = $lang;

		// If language is All then try to get site default language.
		if ($this->lang == '*')
		{
			$languages  = LanguageHelper::getLanguages();
			$this->lang = isset($languages[0]->sef) ? $languages[0]->sef : '*';
		}

		// Get the stem function from the language string.
		switch ($lang)
		{
			// Danish stemmer.
			case 'da':
				$this->function = 'stem_danish';
				break;

			// German stemmer.
			case 'de':
				$this->function = 'stem_german';
				break;

			// English stemmer.
			default:
			case 'en':
				$this->function = 'stem_english';
				break;

			// Spanish stemmer.
			case 'es':
				$this->function = 'stem_spanish';
				break;

			// Finnish stemmer.
			case 'fi':
				$this->function = 'stem_finnish';
				break;

			// French stemmer.
			case 'fr':
				$this->function = 'stem_french';
				break;

			// Hungarian stemmer.
			case 'hu':
				$this->function = 'stem_hungarian';
				break;

			// Italian stemmer.
			case 'it':
				$this->function = 'stem_italian';
				break;

			// Norwegian stemmer.
			case 'nb':
				$this->function = 'stem_norwegian';
				break;

			// Dutch stemmer.
			case 'nl':
				$this->function = 'stem_dutch';
				break;

			// Portuguese stemmer.
			case 'pt':
				$this->function = 'stem_portuguese';
				break;

			// Romanian stemmer.
			case 'ro':
				$this->function = 'stem_romanian';
				break;

			// Russian stemmer.
			case 'ru':
				$this->function = 'stem_russian_unicode';
				break;

			// Swedish stemmer.
			case 'sv':
				$this->function = 'stem_swedish';
				break;

			// Turkish stemmer.
			case 'tr':
				$this->function = 'stem_turkish_unicode';
				break;
		}
	}

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
		// Stem the token if it is not in the cache.
		if (!isset($this->cache[$this->lang][$token]))
		{
			if (!empty($this->function))
			{
				$function = $this->function;

				// Stem the word if the stemmer method exists.
				$this->cache[$this->lang][$token] = function_exists($function) ? $function($token) : $token;
			}
			else
			{
				$this->cache[$this->lang][$token] = $token;
			}
		}

		return $this->cache[$this->lang][$token];
	}
}
