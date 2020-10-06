<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Table\Table;

/**
 * [PlgVanirSearchSolrBridge description]
 *
 * @since __VERSION__
 */
class PlgVanirSearchSolrBridge
{
	/**
	 * @var PlgVanirSearchSolrConfig
	 */
	protected $solrConfig;

	/**
	 * @var \Solarium\Client
	 */
	protected $client = array();

	/**
	 * PlgVanirSearchSolrBridge constructor.
	 *
	 * @param   PlgVanirSearchSolrConfig  $solrConfig configuration manager
	 */
	public function __construct(PlgVanirSearchSolrConfig $solrConfig)
	{
		$this->solrConfig = $solrConfig;
	}

	/**
	 * Method to execute a SOLR search against the categories core
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  product search class
	 * @param   int                             $rows           number of rows to return
	 *
	 * @return \Solarium\QueryType\Select\Result\Result
	 */
	public function searchCategories($productSearch, $rows)
	{
		$solrSearch   = new PlgVanirSearchSolrSearch;
		$searchQuery  = $solrSearch->getSearchQuery($productSearch, ['category_name', 'category_description']);
		$returnFields = array('category_id');
		$endPoints    = array('RedshopbTableCategory');

		return $this->executeSolrSearch($searchQuery, '', $rows, $returnFields, $endPoints);
	}

	/**
	 * Method to execute a search query on solr server
	 *
	 * @param   string  $searchQuery   search query
	 * @param   string  $boostQuery    priority boost query
	 * @param   int     $rows          number of rows to return
	 * @param   array   $returnFields  list of fields to return in the results
	 * @param   array   $endPoints     list of endpoints to use for the query
	 *
	 * @return \Solarium\QueryType\Select\Result\Result
	 */
	protected function executeSolrSearch($searchQuery, $boostQuery, $rows, $returnFields = array(), $endPoints = array())
	{
		$client = $this->getClient($this->solrConfig->getConfigArray($endPoints));
		$query  = $client->createSelect();
		$query->setQuery($searchQuery);

		// Unfortunately SOLR defaults to 10 rows
		$query->setRows($rows);

		if (!empty($returnFields))
		{
			$query->setFields($returnFields);
		}

		if (!empty($boostQuery))
		{
			$edisMaxQuery = $query->getEDisMax();
			$edisMaxQuery->setQueryFields($boostQuery);
		}

		return $client->select($query);
	}

	/**
	 * Method to get a Solarium client.
	 *
	 * @param   array  $solrConfig  array
	 *
	 * @return \Solarium\Client
	 */
	private function getClient($solrConfig)
	{
		$key = md5(json_encode($solrConfig));

		if (isset($this->client[$key]) && ($this->client[$key] instanceof Solarium\Client))
		{
			return $this->client[$key];
		}

		$this->client[$key] = new Solarium\Client($solrConfig);

		return $this->client[$key];
	}


	/**
	 * Method to execute a SOLR search against the products core
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  product search class
	 * @param   int                             $rows           number of rows to return
	 *
	 * @return \Solarium\QueryType\Select\Result\Result
	 */
	public function searchProducts($productSearch, $rows)
	{
		$solrSearch   = new PlgVanirSearchSolrSearch;
		$searchQuery  = $solrSearch->getSearchQuery($productSearch);
		$returnFields = array('product_id');
		$endPoints    = array('RedshopbTableProduct');

		return $this->executeSolrSearch($searchQuery, '', $rows, $returnFields, $endPoints);
	}

	/**
	 * Method to check if we should pre index the current table record
	 *
	 * @param   Table  $table table instance
	 *
	 * @return boolean
	 */
	public function shouldIndex($table)
	{
		if (($table instanceof RedshopbTableProduct) || ($table instanceof RedshopbTableCategory))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to index an entity item
	 *
	 * @param   PlgVanirSearchSolrEntityProduct| PlgVanirSearchSolrEntityCategory $entity      entity to index
	 * @param   array                                                             $endPoints   list of endpoints to use for the query
	 * @param   int                                                               $fieldLimit  the maximum length of entity fields
	 *
	 * @return void
	 */
	public function indexEntity($entity, $endPoints, $fieldLimit)
	{
		$client = $this->getClient($this->solrConfig->getConfigArray($endPoints));

		if (!$this->hasRecords($client))
		{
			$this->buildSchema($entity, $endPoints);
		}

		$updateQuery = $client->createUpdate();
		$document    = $updateQuery->createDocument((array) $entity->getIndexItem($fieldLimit));
		$updateQuery->addDocument($document);
		$updateQuery->addCommit();

		$client->update($updateQuery);
	}

	/**
	 * Method to check if records have been saved already
	 *
	 * @param   \Solarium\Client  $client  Solr Client
	 *
	 * @return boolean
	 */
	private function hasRecords($client)
	{
		// Check if this is the first entity
		$query = $client->createSelect();
		$query->setQuery('*:*');
		$query->setOptions(array('start' => 0, 'rows' => 1));
		$checkResults = $client->execute($query)->getResponse();
		$responseBody = json_decode($checkResults->getBody());

		if ($responseBody->response->numFound == 0)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to dynamically build the SOLR schema
	 * This helps prevent missing field errors and wrong data types when storing
	 *
	 * @param   PlgVanirSearchSolrEntityProduct| PlgVanirSearchSolrEntityCategory $entity     entity to index
	 * @param   array                                                             $endPoints  list of endpoints to use for the query
	 *
	 * @return void
	 */
	public function buildSchema($entity, $endPoints)
	{
		$client = $this->getClient($this->solrConfig->getConfigArray($endPoints));

		// First we send an empty element with all the right data types
		$updateQuery = $client->createUpdate();
		$document    = $updateQuery->createDocument((array) $entity->getSchemaItem());
		$updateQuery->addDocument($document);
		$updateQuery->addCommit();
		$client->update($updateQuery);

		// Now we delete the empty element
		$deleteQuery = $client->createUpdate();
		$deleteQuery->addDeleteById($entity->getSchemaItemId());
		$deleteQuery->addCommit();
		$client->update($deleteQuery);
	}
}
