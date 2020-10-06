<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * [PlgVanirSearchSolrSearch description]
 *
 * @since __VERSION__
 */
class PlgVanirSearchSolrSearch
{
	/**
	 * Method to get a formatted SOLR query string
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  Product search class
	 * @param   array                           $includeFields  Optional list of field names to include (when not empty only these will be searchable)
	 *
	 * @return string false if there is no search term
	 */
	public function getSearchQuery($productSearch, $includeFields = array())
	{
		$indexQuery = $productSearch->indexerQuery;

		$reversePriority = $this->getReversePriority($productSearch);

		if (!$reversePriority)
		{
			return '';
		}

		$boostFields = [];

		foreach ($reversePriority AS $boost => $fields)
		{
			$boost        = (++$boost) * 100;
			$searchFields = $this->getSearchFields($productSearch, $fields, $includeFields);

			foreach ($searchFields AS $fieldName)
			{
				$andQuery   = array();
				$orQuery    = array();
				$fieldQuery = [];

				foreach ($indexQuery->included AS $term)
				{
					$queryString = $this->getTermQuery($term, $boost);

					if ($term->phrase == 1 || $term->term == $indexQuery->input)
					{
						$phraseQuery = $this->getPhraseQuery($term, $indexQuery, $boost);

						if (!empty($phraseQuery))
						{
							$orQuery[] = '(' . $phraseQuery . ')';
						}

						if (!count($term->synonyms))
						{
							continue;
						}
					}

					$andQuery[] = $queryString;
				}

				if (!empty($orQuery))
				{
					foreach ($orQuery as $queryTerm)
					{
						$fieldQuery[] = $queryTerm;
					}
				}

				if (!empty($andQuery))
				{
					$queryTerm = implode(' AND ', $andQuery);

					if (!empty($orQuery))
					{
						$queryTerm = '(' . $queryTerm . ')';
					}

					$fieldQuery[] = $queryTerm;
				}

				if (empty($fieldQuery))
				{
					return '';
				}

				$fieldQuery = implode(' OR ', $fieldQuery);

				// Standard boost
				$boostFields[] = $fieldName
					. ($productSearch->indexerQuery->hasSpecialCharacter ? '' : '_ac')
					. ':(' . $fieldQuery . ')^' . $boost;
			}
		}

		return implode(' OR ', array_reverse($boostFields));
	}

	/**
	 * Method to prep a term for use in a SOLR query
	 *
	 * @param   string  $term  the term to prep
	 *
	 * @return string
	 */
	private function cleanTerm($term)
	{
		return str_replace(array(' ', '"'), array('\ ', '\"'), $term);
	}

	/**
	 * Method to get a phrase query with boosts according to term weight
	 *
	 * @param   RedshopbDatabaseIndexerToken  $term       to generate query from
	 * @param   RedshopbDatabaseIndexerQuery  $indexQuery the entire query
	 * @param   integer                       $boost      Default boost
	 *
	 * @return string
	 */
	private function getPhraseQuery($term, $indexQuery, $boost = 100)
	{
		if ($term->term == $indexQuery->input)
		{
			$boost += 10;
		}

		$cleanTerm = $this->cleanTerm($term->term);

		$queries   = array();
		$queries[] = $cleanTerm . '^' . ($boost + $term->weight * 4);
		$queries[] = $cleanTerm . '*^' . ($boost + $term->weight * 3);
		$queries[] = '*' . $cleanTerm . '*^' . ($boost + $term->weight * 2);
		$queries[] = $cleanTerm . '~^' . ($boost / 100);

		return implode(' OR ', $queries);
	}

	/**
	 * Method to get a query with synonyms for non-phrase terms
	 *
	 * @param   RedshopbDatabaseIndexerToken  $term   to generate query from
	 * @param   integer                       $boost  Default boost
	 *
	 * @return string
	 */
	private function getTermQuery($term, $boost = 0)
	{
		$cleanTerm = $this->cleanTerm($term->term);
		$search    = [
			$cleanTerm . '^' . ($boost + 8),
			$cleanTerm . '*^' . ($boost + 7),
			'*' . $cleanTerm . '*^' . ($boost + 6),
			$cleanTerm . '~^' . ($boost / 100)
		];

		if (!empty($term->synonyms))
		{
			foreach ($term->synonyms AS $synonym)
			{
				$search = array_merge($search, [
						$synonym . '^' . ($boost + 8),
						$synonym . '*^' . ($boost + 7),
						'*' . $synonym . '*^' . ($boost + 6),
						$synonym . '~^' . ($boost / 100)
					]
				);
			}
		}

		return '(' . implode(' OR ', $search) . ')';
	}

	/**
	 * Method to get a structured boost query string
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  Product search class
	 * @param   array                           $includeFields  Optional list of field names to include (when not empty only these will be searchable)
	 *
	 * @return string
	 */
	public function getBoostQuery(RedshopbDatabaseProductsearch $productSearch, $includeFields = array())
	{
		$reversePriority = $this->getReversePriority($productSearch);

		if (!$reversePriority)
		{
			return '';
		}

		$boostFields = array();

		foreach ($reversePriority AS $boost => $fields)
		{
			$boost++;

			$searchFields = $this->getSearchFields($productSearch, $fields, $includeFields);

			foreach ($searchFields AS $fieldName)
			{
				// Standard boost
				$boostFields[] = $fieldName . ($productSearch->indexerQuery->hasSpecialCharacter ? '' : '_ac') . '^' . ($boost * 100);
			}
		}

		if (empty($boostFields))
		{
			return '';
		}

		return implode(' ', array_reverse($boostFields));
	}

	/**
	 * Method to get the search criteria in reverse priority
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  Product search class
	 *
	 * @return array|boolean false if there are no search criteria
	 */
	private function getReversePriority($productSearch)
	{
		$criteria = $productSearch->getSearchCriteria();

		if (empty($criteria))
		{
			return false;
		}

		$reversePriority = array_values(array_reverse($criteria));

		return $reversePriority;
	}

	/**
	 * Method to get a list of searchable field names
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  Product search class
	 * @param   array                           $fields         List of field definitions
	 * @param   array                           $includeFields  Optional list of field names to include (when not empty only these will be searchable)
	 *
	 * @return array
	 */
	public function getSearchFields(RedshopbDatabaseProductsearch $productSearch, $fields = array(), $includeFields = array())
	{
		$searchableExtraFields = $productSearch->getSearchableExtraFields();
		$indexQuery            = $productSearch->indexerQuery;

		$searchFields = array();

		foreach ($fields AS $field)
		{
			if (is_numeric($field->name) && !array_key_exists($field->name, $searchableExtraFields))
			{
				continue;
			}

			if ($field->name == 'extra_fields')
			{
				foreach ($searchableExtraFields AS $extraField)
				{
					$searchable = false;

					foreach ($indexQuery->included AS $term)
					{
						if (in_array($extraField->value_type, array('float_value', 'int_value'))
							&& !is_numeric($term->term))
						{
							continue;
						}

						$searchable = true;
					}

					if (!empty($includeFields) && !in_array($extraField->id, $includeFields))
					{
						$searchable = false;
					}

					if (!empty($searchable))
					{
						$searchFields[] = $extraField->id;
					}
				}

				continue;
			}

			if (!empty($includeFields) && !in_array($field->name, $includeFields))
			{
				continue;
			}

			$searchFields[] = $field->name;
		}

		return $searchFields;
	}
}
