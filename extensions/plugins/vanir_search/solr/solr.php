<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;

// No direct access
defined('_JEXEC') or die;

/**
 * Class PlgVanir_SearchSolr
 *
 * @since  0.0.1
 */
class PlgVanir_SearchSolr extends CMSPlugin
{
	/**
	 * @var PlgVanirSearchSolrBridge
	 */
	protected $solrBridge;

	/**
	 * @var  Registry
	 */
	protected $vanirConfig;

	/**
	 * @var  integer
	 */
	protected $fieldLimit;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Add the composer autoloader
		include_once __DIR__ . '/vendor/autoload.php';
		JLoader::registerPrefix('PlgVanirSearchSolr', __DIR__ . '/src');

		$solrConfig = new PlgVanirSearchSolrConfig;
		$configXml  = simplexml_load_file(__DIR__ . '/config.xml');

		$host           = (string) $configXml->host;
		$port           = (string) $configXml->port;
		$path           = (string) $configXml->path;
		$productsCore   = (string) $configXml->products_core;
		$categoriesCore = (string) $configXml->categories_core;

		$solrConfig->addEndpoint('RedshopbTableProduct', $host, $port, $path, $productsCore);
		$solrConfig->addEndpoint('RedshopbTableCategory', $host, $port, $path, $categoriesCore);

		$this->fieldLimit  = (int) $this->params->get('field_limit', 32766);
		$this->solrBridge  = new PlgVanirSearchSolrBridge($solrConfig);
		$this->vanirConfig = RedshopbApp::getConfig();
	}

	/**
	 * Method to index vanir products and categories
	 *
	 * @return void
	 */
	public function onAjaxSolrSync()
	{
		ini_set('memory_limit', $this->params->get('memory_override', '32M'));

		$this->loadLanguage();
		$sync     = new PlgVanirSearchSolrSync;
		$response = new RedshopbAjaxResponse;

		$response->productCount  = $sync->getRecordCount('product');
		$response->categoryCount = $sync->getRecordCount('category');
		$response->finished      = 0;
		$response->nextStep      = 0;

		$app   = Factory::getApplication();
		$start = $app->input->get('start', 0);
		$limit = (int) $this->params->get('sync_row_limit', 10);

		$response->productFinished = $app->getUserState('plg_vanir_search.productFinished', false);

		if (!$response->productFinished)
		{
			$nextStep = $sync->indexRecords($this->solrBridge, $start, $limit, $this->fieldLimit, 'product');
			$msg      = 'PLG_VANIR_SEARCH_SOLR_PRODUCTS_SYNCED';
			$msgType  = 'alert-info';

			if ($nextStep == $start)
			{
				$nextStep = 0;
				$msg      = 'PLG_VANIR_SEARCH_SOLR_PRODUCT_SYNC_COMPLETE';
				$msgType  = 'alert-success';

				if ($start > $response->productCount)
				{
					$start = $response->productCount;
				}
			}

			$msg = Text::_($msg);

			$response->setBody('<div class="alert ' . $msgType . '">' . $start . '/' . $response->productCount . ' ' . $msg . '</div>');
			$response->setMessageType($msgType);
			$response->setMessage($msg);
			$response->nextStep = $nextStep;

			echo json_encode($response);
			$app->close();
		}

		$response->categoryFinished = $app->getUserState('plg_vanir_search.categoryFinished', false);

		if (!$response->categoryFinished)
		{
			$nextStep = $sync->indexRecords($this->solrBridge, $start, $limit, $this->fieldLimit, 'category');
			$msg      = 'PLG_VANIR_SEARCH_SOLR_CATEGORIES_SYNCED';
			$msgType  = 'alert-info';

			if ($nextStep == $start)
			{
				$nextStep = 0;
				$msg      = 'PLG_VANIR_SEARCH_SOLR_CATEGORY_SYNC_COMPLETE';
				$msgType  = 'alert-success';

				if ($start > $response->categoryCount)
				{
					$start = $response->categoryCount;
				}
			}

			$msg = Text::_($msg);

			$response->setBody('<div class="alert ' . $msgType . '">' . $start . '/' . $response->categoryCount . ' ' . $msg . '</div>');
			$response->setMessageType($msgType);
			$response->setMessage($msg);
			$response->nextStep = $nextStep;

			echo json_encode($response);
			$app->close();
		}

		$msg     = Text::_('PLG_VANIR_SEARCH_SOLR_SYNC_COMPLETE');
		$msgType = 'alert-success';

		$response->setBody('<div class="alert ' . $msgType . '">' . $msg . '</div>');
		$response->setMessageType($msgType);
		$response->setMessage($msg);
		$response->finished = 1;

		echo json_encode($response);
		$app->setUserState('plg_vanir_search.categoryFinished', false);
		$app->setUserState('plg_vanir_search.productFinished', false);
		$app->close();
	}

	/**
	 * Method to index product and category records
	 *
	 * @param   Table    $table        table instance
	 * @param   boolean  $updateNulls  should null values be updated
	 *
	 * @return void
	 */
	public function onAfterStoreRedshopb($table, $updateNulls)
	{
		if (!$this->solrBridge->shouldIndex($table))
		{
			return;
		}

		$className = get_class($table);
		$id        = $table->get('id');

		$entity    = $this->getVanirEntity($className, $id);
		$endpoints = array($className);

		$this->solrBridge->indexEntity($entity, $endpoints, $this->fieldLimit);
	}

	/**
	 * Method to get an entity from a className/id
	 *
	 * @param   string  $className  name of the table class
	 * @param   int     $id         record PK
	 *
	 * @return  mixed
	 */
	private function getVanirEntity($className, $id)
	{
		$entityClass = 'PlgVanirSearchSolrEntityProduct';

		if ($className == 'RedshopbTableCategory')
		{
			$entityClass = 'PlgVanirSearchSolrEntityCategory';
		}

		return $entityClass::getInstance($id);
	}


	/**
	 * Method to search the database for a priority list of product ids
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  product search class
	 * @param   array                           $productIds     empty array of product ids
	 * @param   int                             $customerId     customer ID
	 * @param   string                          $customerType   customer type
	 *
	 * @return  void
	 */
	public function onVanirGetProductIdsBySearchCriteria(RedshopbDatabaseProductsearch $productSearch, &$productIds, $customerId, $customerType)
	{
		if (!$this->params->get('global_search_enabled', 0))
		{
			return;
		}

		$rows      = $this->params->get('set_rows_override', 5000);
		$resultSet = $this->solrBridge->searchProducts($productSearch, $rows);

		$response = $resultSet->getResponse();
		$results  = json_decode($response->getBody());

		foreach ($results->response->docs AS $doc)
		{
			$productIds[] = $doc->product_id[0];
		}
	}

	/**
	 * Method to get a list of category id based on the search terms
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  Product search class
	 * @param   array                           $categoryIds    Empty array of category ids
	 *
	 * @return  void
	 */
	public function onVanirGetCategories(RedshopbDatabaseProductsearch $productSearch, &$categoryIds)
	{
		if (!$this->params->get('global_search_enabled', 0))
		{
			return;
		}

		$rows                = $this->params->get('set_rows_override', 5000);
		$resultSet           = $this->solrBridge->searchCategories($productSearch, $rows);
		$response            = $resultSet->getResponse();
		$results             = json_decode($response->getBody());
		$companyId           = RedshopbHelperCompany::getCompanyIdByCustomer($productSearch->getCustomerId(), $productSearch->getCustomerType());
		$availableCategories = RedshopbHelperACL::listAvailableCategories(
			Factory::getUser()->id, false, 100, $companyId, false, 'comma', '', '', 0, 0, false, true
		);
		$availableCategories = explode(',', $availableCategories);

		foreach ($results->response->docs AS $doc)
		{
			if (in_array($doc->category_id[0], $availableCategories))
			{
				$categoryIds[] = $doc->category_id[0];
			}
		}
	}

	/**
	 * Method to conditionally disable default search
	 *
	 * @param   boolean  $useDefault  toggle param
	 *
	 * @return void
	 */
	public function onBeforeVanirSearchDefaultSearch($useDefault)
	{
		if (!$this->params->get('global_search_enabled', 0))
		{
			return;
		}

		$useDefault = false;
	}


	/**
	 * Method to capture the configuration and store it in xml file
	 *
	 * @param   string   $context  calling this event
	 * @param   Table    $table    table being saved
	 * @param   boolean  $isNew    is this a new record
	 * @param   array    $data     raw data being saved
	 *
	 * @return void
	 */
	public function onExtensionBeforeSave($context, $table, $isNew, $data = array())
	{
		if ((!isset($table->folder) || $table->folder !== 'vanir_search')
			|| (!isset($table->element) || $table->element != 'solr'))
		{
			return;
		}

		/** @var SimpleXMLElement $config */
		$config = simplexml_load_file(__DIR__ . '/config.xml');
		$params = json_decode($table->params);

		$this->updateConfigXmlProperty('host', $config, $params);
		$this->updateConfigXmlProperty('port', $config, $params);
		$this->updateConfigXmlProperty('path', $config, $params);
		$this->updateConfigXmlProperty('products_core', $config, $params);
		$this->updateConfigXmlProperty('categories_core', $config, $params);

		$table->params = json_encode($params);
		$config->saveXML(__DIR__ . '/config.xml');
	}

	/**
	 * Add property to the xml config file and remove from param object
	 *
	 * @param   string            $properyName  name of the property
	 * @param   simpleXmlElement  $config       config file element
	 * @param   stdClass          $params       params sent from the form
	 *
	 * @return void
	 */
	protected function updateConfigXmlProperty($properyName, $config, $params)
	{
		if (empty($params->{$properyName}))
		{
			unset($params->{$properyName});

			return;
		}

		$config->{$properyName}[0] = $params->{$properyName};
		unset($params->{$properyName});
	}

	/**
	 * Method to add the data to the form from the config xml
	 *
	 * @param   string     $context  the event is called from
	 * @param   CMSObject  $data     data being set to the form
	 *
	 * @return void
	 */
	public function onContentPrepareData($context, &$data)
	{
		if ((!isset($data->folder) || $data->folder !== 'vanir_search') || (!isset($data->element) || $data->element != 'solr'))
		{
			return;
		}

		/** @var SimpleXMLElement $config */
		$config       = simplexml_load_file(__DIR__ . '/config.xml');
		$params       = $data->params;
		$data->params = array_merge($params, (array) $config);
	}
}
