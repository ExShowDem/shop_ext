<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  cli_createPdfProductsheet
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Application\CMSApplication;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Create_Pdf_Productsheet
 *
 * @since       1.13.0
 */
class Create_Pdf_ProductsheetApplicationCli extends CliApplication
{
	/**
	 * @var   CMSApplication
	 * @since 1.13.2
	 */
	private $app;

	/**
	 * @var   array
	 * @since 1.13.0
	 */
	public $pIds;

	/**
	 * @var   array
	 * @since 1.13.0
	 */
	public $pdfFiles;

	/**
	 * @var  array
	 * @since 1.13.0
	 */
	public $productIdsToGenerate = array();

	/**
	 * @var stdClass
	 * @since 1.2.5
	 */
	public $mpdfSettings;

	/**
	 * @var   redshopbDatabaseRedis
	 * @since 1.13.0
	 */
	protected $storage;

	/**
	 * @var   array|false
	 * @since 1.13.0
	 */
	protected $keys;

	/**
	 * @var jDatabaseDriver
	 * @since 1.13.0
	 */
	protected $db;

	/**
	 * @var RedshopbLayoutFile
	 * @since 1.13.0
	 */
	protected $layoutFile;

	/**
	 * @var string
	 * @since 1.13.0
	 */
	protected $fileDir = JPATH_SITE . '/media/com_redshopb/automation/productsheets/';

	/**
	 * A method to start the cli script
	 * Optional parameters: [1]: SKU of the product to print (only this product) - no Redis used in this case
	 *
	 * @return void
	 * @since  1.13.0
	 * @throws Exception
	 */
	public function doExecute()
	{
		if (!$this->loadApplication())
		{
			$this->close(1);
		}

		$productSKU    = (empty($this->input->args) ? '' : $this->input->args[0]);
		$singleSKUMode = (empty($this->input->args) ? false : true);
		$this->out('Started');

		// Generates a single product
		if (empty($productSKU))
		{
			$productSKU = $this->findNextProductSKU();
		}

		if (empty($productSKU))
		{
			$this->out('No products found');

			$this->close(0);
		}

		$this->out('Generating a single product sheet: ' . $productSKU);

		if (!$this->loadSingleSKU($productSKU))
		{
			$errorText = 'Product SKU not found: ' . $productSKU;
			Log::add($errorText, Log::CRITICAL, 'pdf_productsheets');
			$this->out($errorText);

			$this->close(1);
		}

		$latestModDate = $this->runPdfProcess();

		// Stores the latest modification date of the process if it operated by batch
		if (!$singleSKUMode)
		{
			$this->storage->setKey('maxdatetime', $latestModDate, 'aesir-e-commerce:pdf', ':');
		}

		// Print message queue
		$this->logMessageQueue();

		$this->out('Done');

		$this->close(0);
	}

	/**
	 * Finds the next product SKU to generate PDF sheet to
	 *
	 * @since    2.5.0
	 *
	 * @return   string|false
	 */
	protected function findNextProductSKU()
	{
		$maxModifiedDateTime = false;
		$searchState         = [ 'list.limit' => 1 ];

		if (null !== $this->storage)
		{
			$maxModifiedDateTime = $this->storage->getKey('maxdatetime', 'aesir-e-commerce:pdf', ':');
		}

		if (!empty($maxModifiedDateTime))
		{
			// First it looks for any other products modified the same date as the last caught one, in case there was more than one
			$products = $this->getProductList([
					'filter.modified_date' => $maxModifiedDateTime,
					'list.limit'    => 0
				]
			);

			if ($products && count($products))
			{
				foreach ($products as $product)
				{
					// If one of the products here has been modified after its Redis date, that's the one we're looking for
					if ($this->isProductModifiedRedis($product))
					{
						return $product->sku;
					}
				}
			}

			// Next, it looks for the first product available with the lowest modification date after the one stored
			$searchState['filter.min_modified_date'] = $maxModifiedDateTime;
		}

		// Gets the product with the lowest modification date
		$products = $this->getProductList($searchState);

		if (!$products || !count($products))
		{
			return false;
		}

		return $products[0]->sku;
	}

	/**
	 * Checks if the product has been modified (in Redis) since its last date of modification
	 *
	 * @param   stdClass  $product       Product object
	 *
	 * @return  boolean
	 */
	protected function isProductModifiedRedis($product)
	{
		$modifiedDate = $this->storage->getKey($product->id, 'aesir-e-commerce:pdf', ':');

		if (!$modifiedDate || empty($modifiedDate) || $modifiedDate < $product->modified_date)
		{
			return true;
		}

		return false;
	}

	/**
	 * Gets a products list given a filter state
	 *
	 * @param   array  $stateParams  State array params
	 *
	 * @return  array|false
	 */
	protected function getProductList($stateParams)
	{
		// Default state
		$state = array(
			'list.ordering'     => 'p.modified_date',
			'list.direction'    => 'ASC',
			'list.limit'        => 0,
			'list.start'        => 0,
			'filter.ignore_acl' => true
		);

		if (!empty($stateParams) && count($stateParams))
		{
			foreach ($stateParams as $stateParam => $stateValue)
			{
				$state[$stateParam] = $stateValue;
			}
		}

		/** @var RedshopbModelProducts $model */
		$model = RedshopbModel::getFrontInstance('products');

		return $model->search($state);
	}

	/**
	 * Loads a single product in the job, using its SKU
	 *
	 * @param   string  $sku  Product SKU
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	protected function loadSingleSKU($sku)
	{
		if (empty($sku))
		{
			return false;
		}

		$product = RedshopbEntityProduct::getInstanceByField('sku', $sku);

		if (!$product->hasId())
		{
			return false;
		}

		// Loads only this product in the array
		$this->productIdsToGenerate = array($product->getId());

		return true;
	}

	/**
	 * Adds one row to the redis data.
	 *
	 * @param   integer $id        The product id
	 * @param   string  $timestamp The modified Timestamp
	 *
	 * @return void
	 * @since 1.13.0
	 */
	protected function setRedisData($id, $timestamp)
	{
		if (null === $this->storage)
		{
			return;
		}

		$this->storage->setKey($id, $timestamp, 'aesir-e-commerce:pdf', ':');
	}

	/**
	 * First steps for generating (data retrieval / pdf generation)
	 *
	 * @return string|false  Modification date of the latest product generated (for reporting back)
	 * @since  1.13.0
	 * @throws Exception
	 */
	protected function runPdfProcess()
	{
		if (empty($this->productIdsToGenerate))
		{
			return false;
		}

		$latestModDate = false;

		foreach ($this->productIdsToGenerate as $value)
		{
			$productEnt  = RedshopbEntityProduct::load($value);
			$productData = $this->setData($productEnt);
			$layoutData  = json_decode($this->layoutFile->render($productData));
			$this->generatePdf($this->sanitizeFileName($productEnt->get('sku')), $layoutData);

			$this->setRedisData($productData->product->id, $productEnt->get('modified_date'));
			$latestModDate = $productEnt->get('modified_date');

			RedshopbEntityProduct::clearInstance($value);
		}

		return $latestModDate;
	}

	/**
	 * Second step for generating (Generates the actual pdf file)
	 *
	 * ```
	 * $pid  = $ProductEntity->getItem()->id;
	 * $data = object {
	 *                  "styles" => array('first_path_to_stylesheet', 'second_path_to_stylesheet'),
	 *                  "pages" => array ('first_html_page', 'second_html_page'),
	 *                  "dpi" => array (dots per inch for images setup, per page - array)
	 *              };
	 *
	 * $this->generatePdf($pid, $data);
	 * ```
	 *
	 * @param   string   $name  Name of the genrated PDF for this product (without extension)
	 * @param   object   $data  The data generated by the layoutfile
	 *
	 * @return void
	 * @since  1.13.0
	 * @throws Exception
	 */
	protected function generatePdf($name, $data)
	{
		$this->checkSettings($data->settings);

		$options = (array) $this->mpdfSettings;

		$options['leftMargin']      = $options['marginLeft'];
		$options['rightMargin']     = $options['marginRight'];
		$options['topMargin']       = $options['marginTop'];
		$options['bottomMargin']    = $options['marginBottom'];
		$options['headerMargin']    = $options['marginHeader'] * Mpdf\Mpdf::SCALE;
		$options['footerMargin']    = $options['marginFooter'] * Mpdf\Mpdf::SCALE;
		$options['showFooterImage'] = false;
		$options['showHeaderImage'] = false;

		$mPDF = RedshopbHelperMpdf::getInstance('', '', $options);

		$style      = $data->styles;
		$pages      = $data->pages;
		$defaultDPI = isset($data->dpi) && is_array($data->dpi) ? $data->dpi[0] : 72;

		$countStyles = count($style);
		$countPages  = count($pages);

		for ($i = 0; $i < $countStyles; $i++)
		{
			$mPDF->WriteHTML(file_get_contents($style[$i]), 1);
		}

		for ($i = 0; $i < $countPages; $i++)
		{
			$mPDF->img_dpi = (isset($data->dpi) && isset($data->dpi[$i])) ? (int) $data->dpi[$i] : $defaultDPI;
			$mPDF->WriteHTML($pages[$i], 2);
			$i == ($countPages - 1) ?: $mPDF->AddPage();
		}

		$mPDF->Output($this->fileDir . $name . '.pdf');
	}

	/**
	 * checkSettings
	 * This method makes sure all settings for Mpdf is set so it is possible for the class to run without problems.
	 *
	 * @param   stdClass  $settings  The settings object.
	 *
	 * @return  void
	 * @since   1.2.5
	 */
	private function checkSettings($settings)
	{
		$this->mpdfSettings = $settings;

		// Make sure all settings are set.
		$settingBulk = array(
			'mode'  => 'UTF-8',
			'format' => RedshopbApp::getConfig()->getString('default_pdf_orientation', 'A4'),
			'defaultFontSize' => 0,
			'defaultFont' => '',
			'orientation' => 'p',
			'marginLeft' => 15,
			'marginRight' => 15,
			'marginTop' => 16,
			'marginBottom' => 16,
			'marginHeader' => 9,
			'marginFooter' => 9
		);

		$explodedFormat = explode('|', $settingBulk['format']);

		// If found width and height then use values inside an array
		if (count($explodedFormat) == 2)
		{
			$settingBulk['format'] = $explodedFormat;
		}

		$this->setSettingBulk('mpdfSettings', $settingBulk);

		// If Margins are equal (Usually used for setting the margin to 0)
		if (isset($settings->marginAll))
		{
			$mga = $settings->marginAll;

			$settingBulk = array(
				'marginLeft' => $mga,
				'marginRight' => $mga,
				'marginTop' => $mga,
				'marginBottom' => $mga,
				'marginHeader' => $mga,
				'marginFooter' => $mga
			);

			$this->setSettingBulk('mpdfSettings', $settingBulk, true);
		}

	}

	/**
	 * @param   string  $scopeVar The variable set in the class scope.
	 * @param   array   $bulk     The array list of names to check in the scopeVar.
	 * @param   boolean $force    Forces the setting of the data.
	 *
	 * @return  void
	 * @since   1.2.5
	 */
	private function setSettingBulk($scopeVar, $bulk, $force = false)
	{
		foreach ($bulk as $name => $defaultValue)
		{
			if ($force)
			{
				$this->{$scopeVar}->{$name} = $defaultValue;
				continue;
			}

			$this->{$scopeVar}->{$name} = !isset($this->{$scopeVar}->{$name}) ? $defaultValue : $this->{$scopeVar}->{$name};
		}
	}

	/**
	 * Since there is alot of data that needs to be set this method is written to set them
	 *
	 * @param   RedshopbEntityProduct $productEnt A product
	 *
	 * @return stdClass
	 * @since  1.13.0
	 */
	protected function setData($productEnt)
	{
		$data               = new stdClass;
		$data->product      = $productEnt;
		$data->attributes   = $this->getData($productEnt, 'getAttributes', 'getAll');
		$data->descriptions = $this->getData($productEnt, 'getDescriptions', 'getAll');
		$data->manufacture  = $this->getData($productEnt, 'getManufacturer', 'getItem');
		$data->category     = $productEnt->getCategories()->getAll();
		$data->images       = $productEnt->getImages()->getAll();
		$data->customFields = $this->buildCustomFields(RedshopbHelperProduct::loadProductFields($productEnt->getItem()->id, true));
		$data->company      = $productEnt->getCompany()->getItem();

		return $data;
	}

	/**
	 * @param   object $ent     The Entity
	 * @param   string $func    The main function to call
	 * @param   string $subFunc The sub function to call
	 *
	 * @return  mixed
	 * @since   1.13.0
	 */
	protected function getData($ent, $func, $subFunc)
	{
		return is_null($ent->$func()) ? null : $ent->$func()->$subFunc();
	}

	/**
	 * Build customfields into an array.
	 *
	 * @param   array $fields The field values
	 *
	 * @return array $data
	 * @since  1.13.0
	 */
	protected function buildCustomFields($fields)
	{
		$fieldCount = count($fields);
		$data       = array();

		for ($i = 0; $i < $fieldCount; $i++)
		{
			$data['full'][]                    = $fields[$i];
			$data['simple'][$fields[$i]->name] = $fields[$i]->value;
		}

		return $data;
	}

	/**
	 * Load basic application
	 *
	 * @return  boolean
	 * @since   1.13.2
	 * @throws  Exception
	 */
	private function loadApplication()
	{
		try
		{
			$this->app = Factory::getApplication('site');
		}
		catch (Exception $exception)
		{
			$errorText = 'Error while loading Joomla Application: ' . $exception->getMessage();
			Log::add($errorText, Log::CRITICAL, 'pdf_productsheets');
			$this->out($errorText);

			return false;
		}

		// Load libraries
		JLoader::import('redshopb.library');
		JLoader::import('mpdf', JPATH_SITE . '/libraries/mpdf');
		JLoader::import('joomla.log');

		// Add log entries to do different stuff
		Log::addLogger(
			array(
				'text_file'         => 'cli_create_pdf_productsheet_log.php',
				'text_entry_format' => '{DATE} {TIME} - {PRIORITY} -- {MESSAGE}'
			),
			Log::ALL,
			array('pdf_productsheets')
		);

		// Load Library language
		$defLang           = ComponentHelper::getParams('com_languages')->get('site');
		$lang              = new Language($defLang);
		Factory::$language = $lang;

		// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
		$lang->load('com_redshopb', JPATH_SITE, null, false, false)
		// Fallback to the com_redshopb file in the default language
		|| $lang->load('com_redshopb', JPATH_SITE, null, true);

		$this->layoutFile = new RedshopbLayoutFile('shop.cli.productsheet');
		$this->db         = Factory::getdbo();

		// Make sure the directory where the pdf's are exists
		is_dir($this->fileDir) ?: $this->prepFullRun();

		// Initializes redis storage
		try
		{
			$this->storage = new RedshopbDatabaseRedis;
		}
		catch (Exception $exception)
		{
			if ($exception->getCode() !== 590 && $exception->getCode() !== 591)
			{
				throw $exception;
			}

			$this->storage = null;
		}

		return true;
	}

	/**
	 * resets all keys and recreates the tmp/productsheets/ folder.
	 *
	 * @return void
	 *
	 * @since 1.13.0
	 */
	private function prepFullRun()
	{
		Log::add('The directory: ' . $this->fileDir . ' was not found, generates pdf for all products', Log::WARNING, 'pdf_productsheets');

		if (!mkdir($this->fileDir, 0755, true))
		{
			Log::add('Could not create Directory: ' . $this->fileDir, Log::CRITICAL, 'pdf_productsheets');

			$this->close(0);
		}
	}

	/**
	 * @param   string  $name  Original name to be sanitized
	 *
	 * @return  string
	 *
	 * @since 1.13.2
	 */
	private function sanitizeFileName($name)
	{
		$name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $name);
		$name = mb_ereg_replace("([\.]{2,})", '', $name);

		return $name;
	}

	/**
	 * Prints the message queue and logs it
	 *
	 * @return  void
	 *
	 * @since  1.13.2
	 */
	private function logMessageQueue()
	{
		$messages = $this->app->getMessageQueue();

		if (!empty($messages))
		{
			$this->out('Printing message queue');

			foreach ($messages as $msg)
			{
				switch ($msg['type'])
				{
					case 'message':
						$typeMessage = 'success';
						Log::add($msg['message'], Log::INFO, 'pdf_productsheets');
						break;
					case 'notice':
						$typeMessage = 'info';
						Log::add($msg['message'], Log::NOTICE, 'pdf_productsheets');
						break;
					case 'error':
						$typeMessage = 'important';
						Log::add($msg['message'], Log::ERROR, 'pdf_productsheets');
						break;
					case 'warning':
						$typeMessage = 'warning';
						Log::add($msg['message'], Log::WARNING, 'pdf_productsheets');
						break;
					default:
						$typeMessage = $msg['type'];
						Log::add($msg['message'], Log::DEBUG, 'pdf_productsheets');
				}

				$this->out($typeMessage . ': ' . $msg['message']);
			}
		}
	}
}

try
{
	$instance = CliApplication::getInstance('Create_Pdf_ProductsheetApplicationCli');
	$instance->execute();
}
catch (Throwable $e)
{
	print_r($e);
}
