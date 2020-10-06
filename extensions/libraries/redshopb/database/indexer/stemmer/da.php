<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Danish stemmer class for Smart Search indexer.
 *
 * Improve search on Danish sites by reducing words to their stem. Based on
 * the Danish stemming algorithm outlined on:
 *
 * http://snowball.tartarus.org/algorithms/danish/stemmer.html
 *
 * @since  1.13.0
 */
class RedshopbDatabaseIndexerStemmerDa extends RedshopbDatabaseIndexerStemmer
{
	/**
	 * All danish vowels
	 * @var   array
	 */
	protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'y', 'æ', 'å', 'ø');

	/**
	 * helper, contains stringified list of vowels
	 * @var string
	 */
	protected $plainVowels;

	/**
	 * The word we are stemming
	 * @var string
	 */
	protected $word;

	/**
	 * The original word, use to check if word has been modified
	 * @var string
	 */
	protected $originalWord;

	/**
	 * RV value
	 * @var string
	 */
	protected $rv;

	/**
	 * RV index (based on the beginning of the word)
	 * @var integer
	 */
	protected $rvIndex;

	/**
	 * R1 value
	 * @var integer
	 */
	protected $r1;

	/**
	 * R1 index (based on the beginning of the word)
	 * @var integer
	 */
	protected $r1Index;

	/**
	 * R2 value
	 * @var integer
	 */
	protected $r2;

	/**
	 * R2 index (based on the beginning of the word)
	 * @var integer
	 */
	protected $r2Index;

	/**
	 * Search
	 *
	 * @param   array  $suffixes  Suffixes
	 * @param   int    $offset    Offset
	 *
	 * @return boolean|mixed
	 *
	 * @since   1.13.0
	 */
	protected function search($suffixes, $offset = 0)
	{
		$length = StringHelper::strlen($this->word);

		if ($offset > $length)
		{
			return false;
		}

		foreach ($suffixes as $suffixe)
		{
			$position = StringHelper::strrpos($this->word, $suffixe, $offset);

			if (($position !== false)
				&& ((StringHelper::strlen($suffixe) + $position) == $length))
			{
				return $position;
			}
		}

		return false;
	}

	/**
	 * R1 is the region after the first non-vowel following a vowel, or the end of the word if there is no such non-vowel.
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	protected function region1()
	{
		list($this->r1Index, $this->r1) = $this->regionx($this->word);
	}

	/**
	 * R2 is the region after the first non-vowel following a vowel in R1, or the end of the word if there is no such non-vowel.
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	protected function region2()
	{
		list($index, $value) = $this->regionx($this->r1);
		$this->r2            = $value;
		$this->r2Index       = $this->r1Index + $index;
	}

	/**
	 * Common function for R1 and R2
	 * Search the region after the first non-vowel following a vowel in $word, or the end of the word if there is no such non-vowel.
	 * R1 : $in = $this->word
	 * R2 : $in = R1
	 *
	 * @param   string  $in  String
	 *
	 * @return  array
	 *
	 * @since   1.13.0
	 */
	protected function regionx($in)
	{
		$length = StringHelper::strlen($in);

		// Defaults
		$value = '';
		$index = $length;

		// We search all vowels
		$vowels = array();

		for ($i = 0; $i < $length; $i++)
		{
			$letter = StringHelper::substr($in, $i, 1);

			if (in_array($letter, static::$vowels))
			{
				$vowels[] = $i;
			}
		}

		// Search the non-vowel following a vowel
		foreach ($vowels as $position)
		{
			$after  = $position + 1;
			$letter = StringHelper::substr($in, $after, 1);

			if (!in_array($letter, static::$vowels))
			{
				$index = $after + 1;
				$value = StringHelper::substr($in, ($after + 1));
				break;
			}
		}

		return array($index, $value);
	}

	/**
	 * Main function to get the STEM of a word
	 * The word in param MUST BE IN UTF-8
	 *
	 * @param   string  $word  Current word
	 *
	 * @throws \Exception
	 * @return NULL|string
	 *
	 * @since   1.13.0
	 */
	public function stem($word)
	{
		$this->word = StringHelper::strtolower($word);

		// R2 is not used: R1 is defined in the same way as in the German stemmer
		$this->region1();

		// Then R1 is adjusted so that the region before it contains at least 3 letters.
		if ($this->r1Index < 3)
		{
			$this->r1Index = 3;
			$this->r1      = StringHelper::substr($this->word, 3);
		}

		// Do each of steps 1, 2 3 and 4.
		$this->step1();
		$this->step2();
		$this->step3();
		$this->step4();

		return $this->word;
	}

	/**
	 * Define a valid s-ending as one of
	 * a   b   c   d   f   g   h   j   k   l   m   n   o   p   r   t   v   y   z   å
	 *
	 * @param   string  $word  Word
	 *
	 * @return  boolean
	 *
	 * @since   1.13.0
	 */
	private function hasValidSEnding($word)
	{
		$lastLetter = StringHelper::substr($word, -1, 1);

		return in_array($lastLetter, array('a', 'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 't', 'v', 'y', 'z', 'å'));
	}

	/**
	 * Step 1
	 * Search for the longest among the following suffixes in R1, and perform the action indicated.
	 *
	 * @return true
	 *
	 * @since   1.13.0
	 */
	private function step1()
	{
		/*
		 * @NOTE
		 * hed   ethed   ered   e   erede   ende   erende   ene   erne   ere   en   heden   eren   er   heder   erer
		 * heds   es   endes   erendes   enes   ernes   eres   ens   hedens   erens   ers   ets   erets   et   eret
		 * delete
		*/
		$position = $this->search(
			array(
				'erendes', 'erende', 'hedens', 'erede', 'ethed', 'heden', 'endes', 'erets', 'heder', 'ernes',
				'erens', 'ered', 'ende', 'erne', 'eres', 'eren', 'eret', 'erer', 'enes', 'heds',
				'ens', 'ene', 'ere', 'ers', 'ets', 'hed', 'es', 'et', 'er', 'en', 'e'
			), $this->r1Index
		);

		if ($position !== false)
		{
			$this->word = StringHelper::substr($this->word, 0, $position);

			return true;
		}

		// Delete if preceded by a valid s-ending
		$position = $this->search(array('s'), $this->r1Index);

		if ($position !== false)
		{
			$word = StringHelper::substr($this->word, 0, $position);

			if ($this->hasValidSEnding($word))
			{
				$this->word = $word;
			}

			return true;
		}

		return true;
	}

	/**
	 * Step 2
	 * Search for one of the following suffixes in R1, and if found delete the last letter.
	 *      gd   dt   gt   kt
	 *
	 * @return void
	 *
	 * @since   1.13.0
	 */
	private function step2()
	{
		if ($this->search(array('gd', 'dt', 'gt', 'kt'), $this->r1Index) !== false)
		{
			$this->word = StringHelper::substr($this->word, 0, -1);
		}
	}

	/**
	 * Step 3:
	 *
	 * @return boolean
	 *
	 * @since   1.13.0
	 */
	private function step3()
	{
		// If the word ends igst, remove the final st.
		if ($this->search(array('igst')) !== false)
		{
			$this->word = StringHelper::substr($this->word, 0, -2);
		}

		/*
		 * Search for the longest among the following suffixes in R1, and perform the action indicated.
		 *
		 * @NOTE
		 * ig   lig   elig   els
		 * delete, and then repeat step 2
		*/
		$position = $this->search(array('elig', 'lig', 'ig', 'els'), $this->r1Index);

		if ($position !== false)
		{
			$this->word = StringHelper::substr($this->word, 0, $position);
			$this->step2();

			return true;
		}

		//  løst
		//  replace with løs
		if ($this->search(array('løst'), $this->r1Index) !== false)
		{
			$this->word = StringHelper::substr($this->word, 0, -1);
		}

		return true;
	}

	/**
	 * Step 4: undouble
	 * If the word ends with double consonant in R1, remove one of the consonants.
	 *
	 * @return boolean
	 *
	 * @since   1.13.0
	 */
	private function step4()
	{
		$length = StringHelper::strlen($this->word);

		if (!($length - 1 >= $this->r1Index))
		{
			return false;
		}

		$lastLetter = StringHelper::substr($this->word, -1, 1);

		if (in_array($lastLetter, self::$vowels))
		{
			return false;
		}

		$beforeLastLetter = StringHelper::substr($this->word, -2, 1);

		if ($lastLetter == $beforeLastLetter)
		{
			$this->word = StringHelper::substr($this->word, 0, -1);
		}

		return true;
	}
}
