<?php
/**
 * @package     RedCOMPONENT
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Sh404sefObserver\Observer;

defined('_JEXEC') or die;

use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Table\Observer\AbstractObserver;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Sh404sefObserver\Helper\PreprocessHelper;
use Sh404sefObserver\Helper\UrlHelper;

/**
 * Observer updater pattern implementation for Joomla
 *
 * @link   https://docs.joomla.org/JObserverUpdater
 * @since  1.0
 */
class Sh404sefObserver extends AbstractObserver
{
	/**
	 * @var null|boolean
	 */
	protected $sh404autoLoaded = null;

	/**
	 * @var array
	 */
	protected $toUpdate = [];

	/**
	 * @var array
	 */
	protected $toDelete = [];

	/**
	 * @var \JEventDispatcher
	 */
	protected $dispatcher;

	/**
	 * @var PreprocessHelper
	 */
	protected $preprocess;

	/**
	 * Constructor: Associates to $table $this observer
	 *
	 * @param   TableInterface  $table  Table to be observed
	 *
	 * @since  1.0
	 */
	public function __construct(TableInterface $table)
	{
		parent::__construct($table);

		$this->dispatcher = \RFactory::getDispatcher();
		$this->preprocess = PreprocessHelper::getInstance();

		if (class_exists('Sh404sefHelperCache'))
		{
			$this->sh404autoLoaded = true;
		}
	}

	/**
	 * Creates the associated observer instance and attaches it to the $observableObject
	 * Creates the associated tags helper class instance
	 * $typeAlias can be of the form "{variableName}.type", automatically replacing {variableName} with table-instance variables variableName
	 *
	 * @param   \JObservableInterface  $observableObject  The subject object to be observed
	 * @param   array                  $params            ( 'typeAlias' => $typeAlias )
	 *
	 * @return  Sh404sefObserver
	 *
	 * @since  1.0
	 */
	public static function createObserver(\JObservableInterface $observableObject, $params = array())
	{
		return new self($observableObject);
	}

	/**
	 * @param   array    $condition  A condition to add
	 * @param   boolean  $toDelete   To delete or to update
	 *
	 * @return  void
	 * @since   2.6.0
	 */
	public function addCondition($condition, $toDelete = false)
	{
		$this->preprocess->addCondition($condition, $toDelete);
	}

	/**
	 * @return Table|TableInterface
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Pre-processor for $table->store($updateNulls)
	 *
	 * @param   boolean  $updateNulls  The result of the load
	 * @param   string   $tableKey     The key of the table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onBeforeStore($updateNulls, $tableKey)
	{
		$tableKey = (array) $tableKey;

		if (!$this->check404sefStatus() || count($tableKey) > 1)
		{
			return;
		}

		$this->toDelete = [];
		$this->toUpdate = [];
		$tableKey       = reset($tableKey);
		$pk             = $this->table->get($tableKey);

		// Update item
		if ($pk)
		{
			switch ($this->table->get('_tbl'))
			{
				case '#__menu':
					$link = $this->table->get('link');

					$cloneTable = clone $this->table;

					if (!$cloneTable->load($pk))
					{
						return;
					}

					$tableAfterLoad = $cloneTable->getProperties(true);

					if ($this->table->get('client_id') == 0
						&& $this->table->get('type') == 'component'
						&& $link)
					{
						$options = (array) (new Uri($link))->getQuery(true);

						if (!$tableAfterLoad['link'])
						{
							$this->addCondition(['Itemid' => $pk]);
						}
						else
						{
							$prevOptions = (array) (new Uri($tableAfterLoad['link']))
								->getQuery(true);

							if (count(array_diff_assoc($options, $prevOptions)) != 0
								|| count(array_diff_assoc($prevOptions, $options)) != 0
								|| $tableAfterLoad['published'] != $this->table->get('published'))
							{
								if ($this->table->get('published') == 1)
								{
									$this->dispatcher->trigger('onSh404sefObserverChangesOnMenuItem', array($this, $link));
								}

								$this->addCondition(['Itemid' => $pk]);
							}
						}
					}

					// Menu item is un-publishing all its children, then check them all
					if ($this->table->get('client_id') == 0
						&& $this->table->get('published') != 1
						&& $tableAfterLoad['published'] == 1)
					{
						$menuItems = UrlHelper::getChildrenItems((int) $pk, '#__menu');

						if (!empty($menuItems))
						{
							foreach ($menuItems as $menuItem)
							{
								if ($menuItem->type == 'component')
								{
									$this->addCondition(['Itemid' => $menuItem->id]);
								}
							}
						}
					}

					break;
				default:
					$this->dispatcher->trigger('onSh404sefObserverBeforeStoreTable', array($this, $pk));
					break;
			}
		}

		// Is new item
		else
		{
			switch ($this->table->get('_tbl'))
			{
				case '#__menu':
					$link = $this->table->get('link');

					if ($this->table->get('client_id') == 0
						&& $this->table->get('type') == 'component'
						&& $this->table->get('published') == 1
						&& $link)
					{
						$this->dispatcher->trigger('onSh404sefObserverChangesOnMenuItem', array($this, $link));
					}
					break;
			}
		}
	}

	/**
	 * @param   boolean  $result  Result
	 *
	 * @return void
	 * @since  2.6.0
	 */
	public function onAfterStore(&$result)
	{
		$this->processSefUrl();
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onBeforeDelete($pk = null)
	{
		if (!$this->check404sefStatus())
		{
			return;
		}

		$this->toDelete = [];
		$this->toUpdate = [];

		switch ($this->table->get('_tbl'))
		{
			case '#__menu':
				$tblKey = $this->table->get('_tbl_key');

				if (is_null($pk) && $this->table->get($tblKey))
				{
					$pk = array($tblKey => $this->table->get($tblKey));
				}
				elseif (!is_array($pk))
				{
					$pk = array($tblKey => $pk);
				}

				foreach ($pk as $key => $value)
				{
					if (!$value)
					{
						continue;
					}

					$cloneTable = clone $this->table;

					if ($this->table->get($key) != $value)
					{
						if (!$cloneTable->load(array($key => $value)))
						{
							continue;
						}
					}

					if ($cloneTable->get('client_id') == 0)
					{
						$this->addCondition(['Itemid' => $value]);
					}
				}
				break;
			default:
				$this->dispatcher->trigger('onSh404sefObserverBeforeDeleteItem', array($this, $pk));
				break;
		}
	}

	/**
	 * @param   mixed  $pk  ID
	 *
	 * @return  void
	 * @since   2.6.0
	 */
	public function onAfterDelete($pk)
	{
		$this->processSefUrl();
	}

	/**
	 * Check whether sh404sef is installed, and whether it is correctly autoloaded
	 *
	 * @return  boolean
	 *
	 * @since  1.0
	 */
	public function check404sefStatus()
	{
		// If the required classes are not autoloaded for some reason, we want to add the autoloader ourselves
		if (is_null($this->sh404autoLoaded))
		{
			$this->sh404autoLoaded = false;

			if (!ComponentHelper::isInstalled('com_sh404sef'))
			{
				return $this->sh404autoLoaded;
			}

			if (!defined('SH404SEF_AUTOLOADER_LOADED'))
			{
				define('SH404SEF_AUTOLOADER_LOADED', 1);
				$autoloaderFile = JPATH_ADMINISTRATOR . '/components/com_sh404sef/helpers/autoloader.php';

				if (file_exists($autoloaderFile))
				{
					spl_autoload_unregister("__autoload");

					include $autoloaderFile;
					spl_autoload_register(array('Sh404sefAutoloader', 'doAutoload'));

					if (function_exists("__autoload"))
					{
						spl_autoload_register("__autoload");
					}
				}
				else
				{
					// Autoloader file doesn't exist for some reason, so we can't load the required classes
					return $this->sh404autoLoaded;
				}
			}

			if (!defined('SH404SEF_BASE_CLASS_LOADED'))
			{
				$baseClassFile = JPATH_ADMINISTRATOR . '/components/com_sh404sef/sh404sef.class.php';

				if (is_readable($baseClassFile))
				{
					require_once $baseClassFile;
				}
				else
				{
					return $this->sh404autoLoaded;
				}
			}

			if (!defined('SHLIB_ROOT_PATH'))
			{
				if (!\JFile::exists(JPATH_ROOT . '/plugins/system/shlib/shlib.php'))
				{
					return $this->sh404autoLoaded;
				}

				require_once JPATH_ROOT . '/plugins/system/shlib/shlib.php';
				$config      = array('type' => 'system', 'name' => 'shlib', 'params' => '');
				$shLibPlugin = new \plgSystemShlib($this->dispatcher, $config);
				$shLibPlugin->onAfterInitialise();
			}

			if (\Sh404sefFactory::getConfig()->Enabled)
			{
				$this->sh404autoLoaded = true;
			}
		}

		return $this->sh404autoLoaded;
	}

	/**
	 * Process SEF URLs
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	protected function processSefUrl()
	{
		if (!$this->check404sefStatus())
		{
			return;
		}

		$this->preprocess->processSefUrl();
	}
}
