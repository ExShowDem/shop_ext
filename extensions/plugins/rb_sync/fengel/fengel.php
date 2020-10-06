<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

require_once JPATH_LIBRARIES . '/redcore/bootstrap.php';
Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
RLoader::registerPrefix('Redshopb', JPATH_LIBRARIES . '/redshopb');
require_once __DIR__ . '/client/client.php';

/**
 * GetCustomer function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class PlgRb_SyncFengel extends CMSPlugin
{
	/**
	 * Source name from where this plugin gets his data
	 *
	 * @var string
	 */
	protected $src = 'RedshopbSync';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Client
	 *
	 * @var object
	 */
	protected $client;

	/**
	 * The options.
	 *
	 * @var  array
	 */
	public static $setWebServicesXref = array(
		'tag' => 'SetItemGroup',
		'company' => 'SetEndCustomer',
		'department' => 'SetDepartment',
		'user' => 'SetUser',
		'address' => 'SetShipToAddress',
		'collection' => 'SetCollection'
	);

	/**
	 * The options.
	 *
	 * @var  array
	 */
	public static $webServicesXref = array(
		'company' => 'fengel.customer',
		'customer_discount_group' => 'fengel.customer_discount_group',
		'customer_price_group' => 'fengel.customer_price_group',
		'product_discount' => 'fengel.product_discount',
		'product_discount_group' => 'fengel.product_discount_group',
		'product_discount_group_xref' => 'fengel.product_discount_group'
	);

	/**
	 * Search sync table and return true if item is fetched from webservice
	 *
	 * @param   string  $tableName          Table name is used as a cross reference with customer named web services
	 * @param   mixed   $pk                 An optional primary key value
	 * @param   mixed   $defaultPrimaryKey  Default primary key on the table object
	 *
	 * @return  boolean  True on success.
	 */
	public function isFromWebservice($tableName = '', $pk = null, $defaultPrimaryKey = null)
	{
		$return = false;
		$pk     = (is_null($pk)) ? $defaultPrimaryKey : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$tableName = str_replace('#__redshopb_', '', $tableName);

		if (!empty($tableName) && $tableName == 'address')
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('a.id')
				->from($db->qn('#__redshopb_address', 'a'))
				->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON c.address_id = a.id AND ' . $db->qn('c.deleted') . ' = 0')
				->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = c.id AND s.reference = ' . $db->q('fengel.customer'))
				->where('(c.type = ' . $db->q('customer') . ' AND s.local_id > 0)')
				->where('a.type = 2');

			if (is_array($pk) && count($pk) > 0)
			{
				// Sanitize input.
				$pkArray = RHelperArray::quote($pk);
				$query->where('a.id IN (' . implode(',', $pkArray) . ')');
			}
			elseif (!is_array($pk))
			{
				$query->where('a.id = ' . $db->q($pk));
			}
			else
			{
				return true;
			}

			$db->setQuery($query);

			if ($db->loadResult())
			{
				return true;
			}
		}
		elseif (!empty($tableName) && isset(self::$webServicesXref[$tableName]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('local_id')
				->from($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q(self::$webServicesXref[$tableName]));

			if (is_array($pk) && count($pk) > 0)
			{
				// Sanitize input.
				$pkArray = RHelperArray::quote($pk);
				$query->where('local_id IN (' . implode(',', $pkArray) . ')');
			}
			elseif (!is_array($pk))
			{
				$query->where('local_id = ' . $db->q($pk));
			}
			else
			{
				return true;
			}

			$db->setQuery($query);
			$returnId = (int) $db->loadResult();

			if ($tableName == 'company')
			{
				// Allow changes only End Customers
				if ($returnId && $pk != 0)
				{
					$query->clear()
						->select('type')
						->from($db->qn('#__redshopb_company'))
						->where($db->qn('deleted') . ' = 0');

					if (is_array($pk) && count($pk) > 0)
					{
						// Sanitize input.
						$pkArray = RHelperArray::quote($pk);
						$query->where('id IN (' . implode(',', $pkArray) . ')');
					}
					elseif (!is_array($pk))
					{
						$query->where('id = ' . $db->q($pk));
					}
					else
					{
						return true;
					}

					$db->setQuery($query);
					$result = $db->loadResult();

					if ($result == 'customer')
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}

			if ((isset($returnId) && $returnId > 0))
			{
				$return = true;
			}
		}

		return $return;
	}

	/**
	 * On wallet set
	 *
	 * @param   int  $userId  Id current wallet
	 * @param   int  $amount  Amount points current user
	 *
	 * @return boolean
	 */
	public function onWalletSet($userId, $amount)
	{
		$table = RTable::getInstance('User', 'RedshopbTable')
			->setOption('useTransaction', false);

		try
		{
			if (RedshopbApp::getConfig()->get('set_webservices', 0) == 0
				|| $table->getOption('forceWebserviceUpdate', false) === true) // Not execute if task use in Get webservices
			{
				return true;
			}

			if (!$table->load($userId))
			{
				return true;
			}

			$table->set('points', $amount);

			$client = $this->getClient();
			$lang   = Factory::getLanguage();
			$lang->load('com_redshopb', JPATH_SITE);

			$className = self::$setWebServicesXref['user'];

			if (!class_exists($className))
			{
				$path = __DIR__ . '/functions/' . $className . '.php';

				if (file_exists($path))
				{
					require_once $path;
				}

				if (!class_exists($className))
				{
					// This function is not supported by this plugin/client
					return true;
				}
			}

			$class = new $className;

			$result = $class
				->setClient($client)
				->store($table, false, false);
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			RedshopbHelperSync::addMessage($table->getError(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Called before delete().
	 *
	 * @param   object   $table     Store values
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 */
	public function onBeforeDeleteRedshopb($table, $pk = null, $children = true)
	{
		try
		{
			if (RedshopbApp::getConfig()->get('set_webservices', 0) == 0
				|| $table->getOption('forceWebserviceUpdate', false) === true) // Not execute if task use in Get webservices
			{
				return true;
			}

			$tableName = str_replace('#__redshopb_', '', $table->getTableName());

			if (!isset(self::$setWebServicesXref[$tableName])
				|| ($tableName == 'company' && $table->type == 'customer')
				|| ($tableName == 'address' && $table->type != 1 && $table->type != 3)
				|| ($tableName == 'tag' && !is_null($table->company_id)))
			{
				return true;
			}

			$client = $this->getClient();
			$lang   = Factory::getLanguage();
			$lang->load('com_redshopb', JPATH_SITE);
			$className = self::$setWebServicesXref[$tableName];

			if (!class_exists($className))
			{
				$path = __DIR__ . '/functions/' . $className . '.php';

				if (file_exists($path))
				{
					require_once $path;
				}

				if (!class_exists($className))
				{
					// This function is not supported by this plugin/client
					return true;
				}
			}

			$class = new $className;

			$result = $class
				->setClient($client)
				->delete($table, $pk, $children);
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return $result;
	}

	/**
	 * On Before Store Redshopb
	 *
	 * @param   object   $table        Store values
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  Exception
	 */
	public function onBeforeStoreRedshopb($table, $updateNulls = false)
	{
		try
		{
			$storingChanges = true;
			$typeExecute    = 'store';

			if (RedshopbApp::getConfig()->get('set_webservices', 0) == 0
				|| $table->getOption('forceWebserviceUpdate', false) === true // Not execute if task use in Get webservices
				|| $table->getOption('disableOnBeforeRedshopb', false) === true
				|| $table->getOption('notSetAddressSeparate', false) === true)
			{
				return true;
			}

			$tableName = str_replace('#__redshopb_', '', $table->getTableName());

			if ($tableName == 'address' && $table->get('type') == 2)
			{
				$address = RedshopbEntityAddress::getInstance($table->id)->bind($table->getProperties());

				$customer = $address->getCustomer();

				if (!$customer)
				{
					return true;
				}

				$addressTable   = $table;
				$storingChanges = false;

				switch ($customer->getType())
				{
					case 'employee':
						$table     = RTable::getInstance('User', 'RedshopbTable');
						$tableName = 'user';
						break;
					case 'department':
						$table     = RTable::getInstance('Department', 'RedshopbTable');
						$tableName = 'department';
						break;
					case 'company':
						$table     = RTable::getInstance('Company', 'RedshopbTable');
						$tableName = 'company';
						break;
				}

				if (!$table->load($customer->getId()))
				{
					return true;
				}

				if (!isset(self::$setWebServicesXref[$tableName])
					|| ($tableName == 'company' && $table->get('type') == 'customer'))
				{
					return true;
				}

				$table->set('country_id', $addressTable->get('country_id'));
				$table->set('address', $addressTable->get('address'));
				$table->set('address2', $addressTable->get('address2'));
				$table->set('zip', $addressTable->get('zip'));
				$table->set('city', $addressTable->get('city'));
			}
			elseif ($tableName == 'company' && $table->get('type') == 'customer' && $table->get('oldType') == 'end_customer')
			{
				$typeExecute = 'deleteInWebservice';
			}
			elseif (!isset(self::$setWebServicesXref[$tableName])
				|| ($tableName == 'company' && $table->get('type') == 'customer')
				|| ($tableName == 'tag' && !is_null($table->get('company_id'))))
			{
				return true;
			}

			$client = $this->getClient();
			$lang   = Factory::getLanguage();
			$lang->load('com_redshopb', JPATH_SITE);
			$className = self::$setWebServicesXref[$tableName];

			if (!class_exists($className))
			{
				$path = __DIR__ . '/functions/' . $className . '.php';

				if (file_exists($path))
				{
					require_once $path;
				}

				if (!class_exists($className))
				{
					// This function is not supported by this plugin/client
					return true;
				}
			}

			$class  = new $className;
			$result = $class->setClient($client)->{$typeExecute}($table, $updateNulls, $storingChanges);

			if (!$result)
			{
				throw new Exception;
			}
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return $result;
	}

	/**
	 * Get data and update relevant tables
	 *
	 * @param   string  $src              Src
	 * @param   string  $func             Func
	 * @param   object  $webserviceData   All current webservice data
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFuncRead($src, $func, &$webserviceData)
	{
		if ($src !== $this->src)
		{
			return false;
		}

		$className = $func;

		// Rejects the functions from other plugins
		if (strpos($className, 'Fengel') !== 0)
		{
			return false;
		}

		if (!class_exists($className))
		{
			$path = __DIR__ . '/functions/' . $className . '.php';

			if (file_exists($path))
			{
				require_once $path;
			}

			if (!class_exists($className))
			{
				// This plugin does not support given function
				return false;
			}
		}

		$lang = Factory::getLanguage();
		$lang->load('com_redshopb', JPATH_SITE);

		// Set webservice parameters
		$webserviceParams = new Registry($webserviceData->get('params'));
		$plugin           = $webserviceData->get('plugin');
		$pluginParams     = $webserviceParams->get($plugin);

		if ($pluginParams)
		{
			if (isset($pluginParams->extends_plugin_config)
				&& $pluginParams->extends_plugin_config == 1)
			{
				$this->params->loadArray($pluginParams);
			}

			$webserviceParams->set($plugin, null);
		}

		$webserviceData->set('params', $webserviceParams);

		$client = $this->getClient(
			$this->params->get('source', ''),
			$this->params->get('url', ''),
			$this->params->get('folder', '')
		);

		/** @var FunctionBase $class */
		$class = new $className;

		return $class
			->setClient($client)
			->read($webserviceData, $this->params);
	}

	/**
	 * Send data to the webservice
	 *
	 * @param   string  $func  function name to send data to
	 * @param   mixed   $data  data to send as parameter
	 *
	 * @return boolean
	 */
	public function onFuncWrite($func, $data)
	{
		$client    = $this->getClient();
		$className = $func;
		$lang      = Factory::getLanguage();
		$lang->load('com_redshopb', JPATH_SITE);

		if (!class_exists($className))
		{
			$path = __DIR__ . '/functions/' . $className . '.php';

			if (file_exists($path))
			{
				require_once $path;
			}

			if (!class_exists($className))
			{
				// This function is not supported by this plugin/client
				return true;
			}
		}

		/** @var FunctionBase $class */
		$class = new $className;

		return $class
			->setClient($client)
			->send($data);
	}

	/**
	 * Try to dump xml from client
	 *
	 * @param   object  $client  client
	 * @param   string  $func    function name
	 *
	 * @return boolean false if function not found in client
	 *
	 * @throws RuntimeException
	 */
	protected function dumpXml($client, $func)
	{
		if (method_exists($client, $func))
		{
			$xml = $client->{$func}('', '', '', '', '', '');

			if (!is_object($xml))
			{
				throw new RuntimeException(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			print_r($xml);
			exit;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Init and return client object
	 *
	 * @param   string  $source  Source for the current client
	 * @param   string  $url     WSDL URL of the current webservice when source = 'wsdl'
	 * @param   string  $folder  Folder to read files from when source = 'folder'
	 *
	 * @return FEngelClient
	 */
	protected function getClient($source = '', $url = '', $folder = '')
	{
		if (!$this->client)
		{
			$source = ($source == '' ? $this->params->get('source', 'wsdl') : $source);
			$url    = ($url == '' ? $this->params->get('url', FEngelClient::TEST_URL) : $url);
			$folder = ($folder == '' ? $this->params->get('folder', 'fengel') : $folder);

			$this->client = FEngelClient::getInstance($source, $url, $folder);
		}

		return $this->client;
	}

	/**
	 * Get data and update relevant tables
	 *
	 * @param   string  $src       Src
	 * @param   object  $cronItem  Cron Item
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFuncCronClearHashedKeys($src, $cronItem)
	{
		if ($src !== $this->src)
		{
			return false;
		}

		if (ucfirst($cronItem->plugin) != 'Fengel')
		{
			return false;
		}

		if (!class_exists($cronItem->name))
		{
			$className = ucfirst($cronItem->plugin) . $cronItem->name;
			$path      = __DIR__ . '/functions/' . $className . '.php';

			if (file_exists($path))
			{
				require_once $path;
			}

			if (!class_exists($className))
			{
				// This plugin does not support given function
				return false;
			}
		}

		/** @var FunctionBase $class */
		$class = new $className;

		return $class->clearHashKeys();
	}
}
