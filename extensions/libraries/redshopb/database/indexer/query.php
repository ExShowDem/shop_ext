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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;

PluginHelper::importPlugin('vanir_search');

/**
 * Class RedshopbDatabaseIndexerQuery
 *
 * @since  1.13.0
 */
class RedshopbDatabaseIndexerQuery
{
	/**
	 * The query input string.
	 *
	 * @var    string
	 * @since  1.13.0
	 */
	public $input;

	/**
	 * The language of the query.
	 *
	 * @var    string
	 * @since  1.13.0
	 */
	public $language;

	/**
	 * The query string matching mode.
	 *
	 * @var    string
	 * @since  1.13.0
	 */
	public $mode;

	/**
	 * The included tokens.
	 *
	 * @var    array
	 * @since  1.13.0
	 */
	public $included = array();

	/**
	 * The operators used in the query input string.
	 *
	 * @var    array
	 * @since  1.13.0
	 */
	public $operators = array();

	/**
	 * The excluded tokens.
	 *
	 * @var    array
	 * @since  1.13.0
	 */
	public $excluded = array();

	/**
	 * Flag to show whether the query can return results.
	 *
	 * @var    boolean
	 * @since  1.13.0
	 */
	public $search = false;

	/**
	 * Flag as to whether or not we include synonyms
	 * @var bool|mixed
	 */
	public $searchSynonyms = true;

	/**
	 * @var boolean
	 * @since 1.12.77
	 */
	public $hasSpecialCharacter = false;

	/**
	 * Method to instantiate the query object.
	 *
	 * @param   array  $options  An array of query options.
	 *
	 * @throws  Exception on database error.
	 *
	 * @since  1.13.0
	 */
	public function __construct($options)
	{
		// Get the input string.
		$this->input          = isset($options['input']) ? $options['input'] : null;
		$this->searchSynonyms = isset($options['search_synonyms']) ? $options['search_synonyms'] : true;

		// Get the input language.
		$this->language = !empty($options['language']) ? $options['language'] : RedshopbDatabaseIndexerHelper::getDefaultLanguage();
		$this->language = RedshopbDatabaseIndexerHelper::getPrimaryLanguage($this->language);

		// Get the matching mode.
		$this->mode = 'AND';

		if (strlen($this->input) != mb_strlen($this->input, 'utf-8'))
		{
			$this->hasSpecialCharacter = true;
		}

		// Process the input string.
		$this->processString($this->input, $this->language, $this->mode);
	}

	/**
	 * Method to process the query input string and extract required, optional,
	 * and excluded tokens; taxonomy filters; and date filters.
	 *
	 * @param   string  $input  The query input string.
	 * @param   string  $lang   The query input language.
	 * @param   string  $mode   The query matching mode.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.13.0
	 * @throws  Exception on database error.
	 */
	protected function processString($input, $lang, $mode)
	{
		// Clean up the input string.
		$input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
		$input = StringHelper::strtolower($input);
		$input = preg_replace('#\s+#mi', ' ', $input);
		$input = StringHelper::trim($input);

		// Check if we have a query string.
		if (!empty($input))
		{
			$this->search = true;
		}

		// Container for search terms and phrases.
		$terms   = array();
		$phrases = array();

		/*
		 * Extract the tokens enclosed in double quotes so that we can handle
		 * them as phrases.
		 */
		if (StringHelper::strpos($input, '"') !== false)
		{
			$matches = array();

			// Extract the tokens enclosed in double quotes.
			if (preg_match_all('#\"([^"]+)\"#mi', $input, $matches))
			{
				/*
				 * One or more phrases were found so we need to iterate through
				 * them, tokenize them as phrases, and remove them from the raw
				 * input string before we move on to the next processing step.
				 */
				foreach ($matches[1] as $key => $match)
				{
					// Find the complete phrase in the input string.
					$pos = StringHelper::strpos($input, $matches[0][$key]);
					$len = StringHelper::strlen($matches[0][$key]);

					// Add any terms that are before this phrase to the stack.
					if (StringHelper::trim(StringHelper::substr($input, 0, $pos)))
					{
						$terms = array_merge($terms, explode(' ', StringHelper::trim(StringHelper::substr($input, 0, $pos))));
					}

					// Strip out everything up to and including the phrase.
					$input = StringHelper::substr($input, $pos + $len);

					// Clean up the input string again.
					$input = preg_replace('#\s+#mi', ' ', $input);
					$input = StringHelper::trim($input);

					// Get the number of words in the phrase.
					$parts = explode(' ', $match);

					// Check if the phrase is longer than three words.
					if (count($parts) > 3)
					{
						/*
						 * If the phrase is longer than three words, we need to
						 * break it down into smaller chunks of phrases that
						 * are less than or equal to three words. We overlap
						 * the chunks so that we can ensure that a match is
						 * found for the complete phrase and not just portions
						 * of it.
						 */
						 $count = count($parts);

						for ($i = 0; $i < $count; $i += 2)
						{
							// Set up the chunk.
							$chunk = array();

							// The chunk has to be assembled based on how many
							// pieces are available to use.
							switch ($count - $i)
							{
								/*
								 * If only one word is left, we can break from
								 * the switch and loop because the last word
								 * was already used at the end of the last
								 * chunk.
								 */
								case 1:
									break 2;

								// If there words are left, we use them both as
								// the last chunk of the phrase and we're done.
								case 2:
									$chunk[] = $parts[$i];
									$chunk[] = $parts[$i + 1];
									break;

								// If there are three or more words left, we
								// build a three word chunk and continue on.
								default:
									$chunk[] = $parts[$i];
									$chunk[] = $parts[$i + 1];
									$chunk[] = $parts[$i + 2];
									break;
							}

							// If the chunk is not empty, add it as a phrase.
							if (count($chunk))
							{
								$phrases[] = implode(' ', $chunk);
								$terms[]   = implode(' ', $chunk);
							}
						}
					}
					else
					{
						// The phrase is <= 3 words so we can use it as is.
						$phrases[] = $match;
						$terms[]   = $match;
					}
				}
			}
		}

		// Add the remaining terms if present.
		if (!empty($input))
		{
			$terms = array_merge($terms, explode(' ', $input));
		}

		// An array of our boolean operators. $operator => $translation
		$operators = array(
			'AND' => StringHelper::strtolower(Text::_('COM_REDSHOPB_QUERY_OPERATOR_AND')),
			'OR' => StringHelper::strtolower(Text::_('COM_REDSHOPB_QUERY_OPERATOR_OR')),
			'NOT' => StringHelper::strtolower(Text::_('COM_REDSHOPB_QUERY_OPERATOR_NOT'))
		);

		// If language debugging is enabled you need to ignore the debug strings in matching.
		if (JDEBUG)
		{
			$debugStrings = array('**', '??');
			$operators    = str_replace($debugStrings, '', $operators);
		}

		/*
		 * Iterate through the terms and perform any sorting that needs to be
		 * done based on boolean search operators. Terms that are before an
		 * and/or/not modifier have to be handled in relation to their operator.
		 */
		 $count = count($terms);

		for ($i = 0; $i < $count; $i++)
		{
			// Check if the term is followed by an operator that we understand.
			if (isset($terms[$i + 1]) && in_array($terms[$i + 1], $operators))
			{
				// Get the operator mode.
				$op = array_search($terms[$i + 1], $operators);

				// Handle the AND operator.
				if ($op === 'AND' && isset($terms[$i + 2]))
				{
					// Tokenize the current term.
					$token = RedshopbDatabaseIndexerHelper::tokenize($terms[$i], $lang, true);
					$token = $this->getTokenData($token);

					// Set the required flag.
					$token->required = true;

					// Add the current token to the stack.
					$this->included[$token->term] = $token;

					// Skip the next token (the mode operator).
					$this->operators[] = $terms[$i + 1];

					// Tokenize the term after the next term (current plus two).
					$other = RedshopbDatabaseIndexerHelper::tokenize($terms[$i + 2], $lang, true);
					$other = $this->getTokenData($other);

					// Set the required flag.
					$other->required = true;

					// Add the token after the next token to the stack.
					$this->included[$other->term] = $other;

					// Remove the processed phrases if possible.
					$pk = array_search($terms[$i], $phrases);

					if ($pk !== false)
					{
						unset($phrases[$pk]);
					}

					$pk = array_search($terms[$i + 2], $phrases);

					if ($pk !== false)
					{
						unset($phrases[$pk]);
					}

					// Remove the processed terms.
					unset($terms[$i]);
					unset($terms[$i + 1]);
					unset($terms[$i + 2]);

					// Adjust the loop.
					$i += 2;
					continue;
				}

				// Handle the OR operator.
				elseif ($op === 'OR' && isset($terms[$i + 2]))
				{
					// Tokenize the current term.
					$token = RedshopbDatabaseIndexerHelper::tokenize($terms[$i], $lang, true);
					$token = $this->getTokenData($token);

					// Set the required flag.
					$token->required = false;

					// Add the current token to the stack.
					$this->included[$token->term] = $token;

					// Skip the next token (the mode operator).
					$this->operators[] = $terms[$i + 1];

					// Tokenize the term after the next term (current plus two).
					$other = RedshopbDatabaseIndexerHelper::tokenize($terms[$i + 2], $lang, true);
					$other = $this->getTokenData($other);

					// Set the required flag.
					$other->required = false;

					// Add the token after the next token to the stack.
					$this->included[$other->term] = $other;

					// Remove the processed phrases if possible.
					$pk = array_search($terms[$i], $phrases);

					if ($pk !== false)
					{
						unset($phrases[$pk]);
					}

					$pk = array_search($terms[$i + 2], $phrases);

					if ($pk !== false)
					{
						unset($phrases[$pk]);
					}

					// Remove the processed terms.
					unset($terms[$i]);
					unset($terms[$i + 1]);
					unset($terms[$i + 2]);

					// Adjust the loop.
					$i += 2;
					continue;
				}
			}

			// Handle an orphaned OR operator.
			elseif (isset($terms[$i + 1]) && array_search($terms[$i], $operators) === 'OR')
			{
				// Skip the next token (the mode operator).
				$this->operators[] = $terms[$i];

				// Tokenize the next term (current plus one).
				$other = RedshopbDatabaseIndexerHelper::tokenize($terms[$i + 1], $lang, true);
				$other = $this->getTokenData($other);

				// Set the required flag.
				$other->required = false;

				// Add the token after the next token to the stack.
				$this->included[$other->term] = $other;

				$pk = array_search($terms[$i + 1], $phrases);

				// Remove the processed phrase if possible.
				if ($pk !== false)
				{
					unset($phrases[$pk]);
				}

				// Remove the processed terms.
				unset($terms[$i]);
				unset($terms[$i + 1]);

				// Adjust the loop.
				$i++;
				continue;
			}

			// Handle the NOT operator.
			elseif (isset($terms[$i + 1]) && array_search($terms[$i], $operators) === 'NOT')
			{
				// Skip the next token (the mode operator).
				$this->operators[] = $terms[$i];

				// Tokenize the next term (current plus one).
				$other = RedshopbDatabaseIndexerHelper::tokenize($terms[$i + 1], $lang, true);
				$other = $this->getTokenData($other);

				// Set the required flag.
				$other->required = false;

				// Add the next token to the stack.
				$this->excluded[$other->term] = $other;

				// Remove the processed phrase if possible.
				$pk = array_search($terms[$i + 1], $phrases);

				if ($pk !== false)
				{
					unset($phrases[$pk]);
				}

				// Remove the processed terms.
				unset($terms[$i]);
				unset($terms[$i + 1]);

				// Adjust the loop.
				$i++;
				continue;
			}
		}

		/*
		 * Iterate through any search phrases and tokenize them. We handle
		 * phrases as autonomous units and do not break them down into two and
		 * three word combinations.
		 */
		 $count = count($phrases);

		for ($i = 0; $i < $count; $i++)
		{
			// Tokenize the phrase.
			$token = RedshopbDatabaseIndexerHelper::tokenize($phrases[$i], $lang, true);
			$token = $this->getTokenData($token);

			// Set the required flag.
			$token->required = true;

			// Add the current token to the stack.
			$this->included[$token->term] = $token;

			// Remove the processed term if possible.
			$pk = array_search($phrases[$i], $terms);

			if ($pk !== false)
			{
				unset($terms[$pk]);
			}

			// Remove the processed phrase.
			unset($phrases[$i]);
		}

		/*
		 * Handle any remaining tokens using the standard processing mechanism.
		 */
		if (!empty($terms))
		{
			// Tokenize the terms.
			$terms  = implode(' ', $terms);
			$tokens = RedshopbDatabaseIndexerHelper::tokenize($terms, $lang, false);

			// Make sure we are working with an array.
			$tokens = is_array($tokens) ? $tokens : array($tokens);

			// Get the token data and required state for all the tokens.
			foreach ($tokens as $token)
			{
				// Get the token data.
				$token = $this->getTokenData($token);

				// Set the required flag for the token.
				$token->required = $mode === 'AND' ? ($token->phrase ? false : true) : false;

				// Add the token to the appropriate stack.
				$this->included[$token->term] = $token;
			}
		}

		// Add synonyms
		if ($this->searchSynonyms && !empty($this->included))
		{
			foreach ($this->included as $item)
			{
				$item->synonyms = array();

				if (!$item->phrase)
				{
					$synonyms = (array) RedshopbEntityWord::loadWord($item->term)
						->getSynonyms();

					if (!empty($synonyms))
					{
						// Exclude synonyms from the main list
						foreach ($synonyms as $key => $synonym)
						{
							if (array_key_exists($synonym, $this->included))
							{
								unset($synonyms[$key]);
								continue;
							}

							if (array_key_exists($synonym, $this->excluded))
							{
								unset($synonyms[$key]);
								continue;
							}
						}

						$item->synonyms = array_values($synonyms);
					}
				}
			}
		}

		Factory::getApplication()->triggerEvent('onVanirSearchProcessString', [$this]);

		return true;
	}

	/**
	 * Method to get the base and similar term ids and, if necessary, suggested
	 * term data from the database. The terms ids are identified based on a
	 * 'like' match in MySQL and/or a common stem. If no term ids could be
	 * found, then we know that we will not be able to return any results for
	 * that term and we should try to find a similar term to use that we can
	 * match so that we can suggest the alternative search query to the user.
	 *
	 * @param   RedshopbDatabaseIndexerHelper  $token  A RedshopbDatabaseIndexerHelper object.
	 *
	 * @return  RedshopbDatabaseIndexerHelper  A RedshopbDatabaseIndexerHelper object.
	 *
	 * @since  1.13.0
	 */
	protected function getTokenData($token)
	{
		return $token;
	}
}
