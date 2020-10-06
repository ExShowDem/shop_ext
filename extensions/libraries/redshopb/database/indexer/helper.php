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
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class RedshopbDatabaseIndexerHelper
 *
 * @since  1.13.0
 */
class RedshopbDatabaseIndexerHelper
{
	/**
	 * The token stemmer object. The stemmer is set by whatever class
	 * wishes to use it but it must be an instance of FinderIndexerStemmer.
	 *
	 * @var		FinderIndexerStemmer
	 * @since	1.13.0
	 */
	public static $stemmer;

	/**
	 * Method to tokenize a text string.
	 *
	 * @param   string   $input   The input to tokenize.
	 * @param   string   $lang    The language of the input.
	 * @param   boolean  $phrase  Flag to indicate whether input could be a phrase. [optional]
	 *
	 * @return  RedshopbDatabaseIndexerToken|array  An array of RedshopbDatabaseIndexerToken objects.
	 *
	 * @since   1.13.0
	 */
	public static function tokenize($input, $lang, $phrase = false)
	{
		static $cache;
		$store = StringHelper::strlen($input) < 128 ? md5($input . '::' . $lang . '::' . $phrase) : null;

		// Check if the string has been tokenized already.
		if ($store && isset($cache[$store]))
		{
			return $cache[$store];
		}

		$tokens = array();
		$quotes = html_entity_decode('&#8216;&#8217;&#39;', ENT_QUOTES, 'UTF-8');

		// Get the simple language key.
		$lang = self::getPrimaryLanguage($lang);

		/*
		 * Parsing the string input into terms is a multi-step process.
		 *
		 * Regexes:
		 *  1. Remove plus, dash, period, and comma characters located before letter characters.
		 *  2. Remove plus, dash, period, and comma characters located after other characters.
		 *  3. Remove plus, period, and comma characters enclosed in alphabetical characters. Ungreedy.
		 *  4. Remove orphaned apostrophe, plus, dash, period, and comma characters.
		 *  5. Remove orphaned quote characters.
		 *  6. Replace the assorted single quotation marks with the ASCII standard single quotation.
		 *  7. Remove multiple space characters and replaces with a single space.
		 */

		$input = StringHelper::strtolower($input);
		$input = preg_replace('#(^|\s)[+-.,]+([\pL\pM]+)#mui', ' $1', $input);
		$input = preg_replace('#([\pL\pM\pN]+)[+-.,]+(\s|$)#mui', '$1 ', $input);
		$input = preg_replace('#([\pL\pM]+)[+.,]+([\pL\pM]+)#muiU', '$1 $2', $input);
		$input = preg_replace('#(^|\s)[\'+-.,]+(\s|$)#mui', ' ', $input);
		$input = preg_replace('#(^|\s)[\p{Pi}\p{Pf}]+(\s|$)#mui', ' ', $input);
		$input = preg_replace('#[' . $quotes . ']+#mui', '\'', $input);
		$input = preg_replace('#\s+#mui', ' ', $input);
		$input = StringHelper::trim($input);

		// Explode the normalized string to get the terms.
		$terms = explode(' ', $input);

		/*
		 * If we have Unicode support and are dealing with Chinese text, Chinese
		 * has to be handled specially because there are not necessarily any spaces
		 * between the "words". So, we have to test if the words belong to the Chinese
		 * character set and if so, explode them into single glyphs or "words".
		 */
		if ($lang === 'zh')
		{
			// Iterate through the terms and test if they contain Chinese.
			$count = count($terms);

			for ($i = 0; $i < $count; $i++)
			{
				$charMatches = array();
				$charCount   = preg_match_all('#[\p{Han}]#mui', $terms[$i], $charMatches);

				// Split apart any groups of Chinese characters.
				for ($j = 0; $j < $charCount; $j++)
				{
					$tSplit = StringHelper::str_ireplace($charMatches[0][$j], '', $terms[$i], false);

					if (!empty($tSplit))
					{
						$terms[$i] = $tSplit;
					}
					else
					{
						unset($terms[$i]);
					}

					$terms[] = $charMatches[0][$j];
				}
			}

			// Reset array keys.
			$terms = array_values($terms);
		}

		/*
		 * If we have to handle the input as a phrase, that means we don't
		 * tokenize the individual terms and we do not create the two and three
		 * term combinations. The phrase must contain more than one word!
		 */
		if ($phrase === true && count($terms) > 1)
		{
			// Create tokens from the phrase.
			$tokens[] = new RedshopbDatabaseIndexerToken($terms, $lang);
		}
		else
		{
			// Create tokens from the terms.
			$count = count($terms);

			for ($i = 0; $i < $count; $i++)
			{
				$tokens[] = new RedshopbDatabaseIndexerToken($terms[$i], $lang);
			}

			// Create two and three word phrase tokens from the individual words.
			$count = count($tokens);

			for ($i = 0; $i < $count; $i++)
			{
				// Setup the phrase positions.
				$i2 = $i + 1;
				$i3 = $i + 2;

				// Create the two word phrase.
				if ($i2 < $count && isset($tokens[$i2]))
				{
					// Tokenize the two word phrase.
					$token          = new RedshopbDatabaseIndexerToken(
						array($tokens[$i]->term, $tokens[$i2]->term), $lang, $lang === 'zh' ? '' : ' '
					);
					$token->derived = true;

					// Add the token to the stack.
					$tokens[] = $token;
				}

				// Create the three word phrase.
				if ($i3 < $count && isset($tokens[$i3]))
				{
					// Tokenize the three word phrase.
					$token          = new RedshopbDatabaseIndexerToken(
						array($tokens[$i]->term, $tokens[$i2]->term, $tokens[$i3]->term), $lang, $lang === 'zh' ? '' : ' '
					);
					$token->derived = true;

					// Add the token to the stack.
					$tokens[] = $token;
				}
			}
		}

		if ($store)
		{
			$cache[$store] = count($tokens) > 1 ? $tokens : array_shift($tokens);

			return $cache[$store];
		}

		return count($tokens) > 1 ? $tokens : array_shift($tokens);
	}

	/**
	 * Method to get the base word of a token. This method uses the public
	 * {@link FinderIndexerHelper::$stemmer} object if it is set. If no stemmer is set,
	 * the original token is returned.
	 *
	 * @param   string  $token  The token to stem.
	 * @param   string  $lang   The language of the token.
	 *
	 * @return  string  The root token.
	 *
	 * @since   1.13.0
	 */
	public static function stem($token, $lang)
	{
		// Trim apostrophes at either end of the token.
		$token = StringHelper::trim($token, '\'');

		// Trim everything after any apostrophe in the token.
		$pos = StringHelper::strpos($token, '\'');

		if ($pos !== false)
		{
			$token = StringHelper::substr($token, 0, $pos);
		}

		return RedshopbDatabaseIndexerStemmer::getInstance($lang)
			->stem($token);
	}

	/**
	 * Method to get the default language for the site.
	 *
	 * @return  string  The default language string.
	 *
	 * @since  1.13.0
	 */
	public static function getDefaultLanguage()
	{
		static $lang;

		// We need to go to com_languages to get the site default language, it's the best we can guess.
		if (empty($lang))
		{
			$lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		return $lang;
	}

	/**
	 * Method to parse a language/locale key and return a simple language string.
	 *
	 * @param   string  $lang  The language/locale key. For example: en-GB
	 *
	 * @return  string  The simple language string. For example: en
	 *
	 * @since  1.13.0
	 */
	public static function getPrimaryLanguage($lang)
	{
		static $data;

		// Only parse the identifier if necessary.
		if (!isset($data[$lang]))
		{
			if (is_callable(array('Locale', 'getPrimaryLanguage')))
			{
				// Get the language key using the Locale package.
				$data[$lang] = Locale::getPrimaryLanguage($lang);
			}
			else
			{
				// Get the language key using string position.
				$data[$lang] = StringHelper::substr($lang, 0, StringHelper::strpos($lang, '-'));
			}
		}

		return $data[$lang];
	}
}
