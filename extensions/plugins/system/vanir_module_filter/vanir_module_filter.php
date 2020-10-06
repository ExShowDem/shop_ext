<?php
/**
 * @package     Vanir.Plugin
 * @subpackage  System.Vanir_Modules_Filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('redshopb.library');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;
/**
 * Plugin to filter modules that will be shown in Vanir views.
 *
 * @since  1.0.0
 */
class PlgSystemVanir_Module_Filter extends CMSPlugin
{
	/**
	 * Category form name.
	 *
	 * @const
	 */
	const FORM_NAME_MODULE = 'com_modules.module';

	/**
	 * Show module in all the views.
	 *
	 * @const  integer
	 */
	const SHOW_MODE_ALWAYS = 1;

	/**
	 * Show module only in specified views.
	 *
	 * @const  integer
	 */
	const SHOW_MODE_SPECIFIC_VIEWS = 2;

	/**
	 * Show always in category views.
	 *
	 * @const  integer
	 */
	const SHOW_IN_CATEGORY_MODE_ALWAYS = 1;

	/**
	 * Show always in category views except for specified categories.
	 *
	 * @const  integer
	 */
	const SHOW_IN_CATEGORY_MODE_ALWAYS_FOR = 2;

	/**
	 * Never show in category views.
	 *
	 * @const  integer
	 */
	const SHOW_IN_CATEGORY_MODE_NEVER = 3;

	/**
	 * Never show in category views except for specified categories.
	 *
	 * @const  integer
	 */
	const SHOW_IN_CATEGORY_MODE_NEVER_FOR = 4;

	/**
	 * Show always in category views.
	 *
	 * @const  integer
	 */
	const SHOW_IN_ITEM_MODE_ALWAYS = 1;

	/**
	 * Show always in category views except for specified categories.
	 *
	 * @const  integer
	 */
	const SHOW_IN_ITEM_MODE_ALWAYS_FOR = 2;

	/**
	 * Never show in category views.
	 *
	 * @const  integer
	 */
	const SHOW_IN_ITEM_MODE_NEVER = 3;

	/**
	 * Never show in category views except for specified categories.
	 *
	 * @const  integer
	 */
	const SHOW_IN_ITEM_MODE_NEVER_FOR = 4;

	/**
	 * Never show in category views.
	 *
	 * @const  integer
	 */
	const SHOW_IN_ITEM_MODE_ALWAYS_FOR_PRODUCTS_OF_CATEGORIES = 5;

	/**
	 * Never show in category views except for specified categories.
	 *
	 * @const  integer
	 */
	const SHOW_IN_ITEM_MODE_NEVER_FOR_PRODUCTS_OF_CATEGORIES = 6;

	/**
	 * Used to instantiate the application.
	 * @var mixed
	 */
	protected $app;

	/**
	 * Toggles lanuage autoloading on/off
	 * @var boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Contains the path to this plugin
	 * @var mixed
	 */
	protected $pluginPath;

	/**
	 * Sets the $app property
	 * @param   mixed  $subject  Contains a subject
	 * @param   array  $config   Contains a config array
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->app = Factory::getApplication();
	}

	/**
	 * triggered just after the module list is fetched from the database.
	 * Can be used to manipulate the module list by adding/removing modules, or even changing the modules data.
	 *
	 * @param   array  $modules  Modules that will be shown in this page.
	 *
	 * @return  void
	 */
	public function onAfterModuleList(&$modules)
	{
		if (!$this->isEnabledView())
		{
			return;
		}

		foreach ($modules as $position => $module)
		{
			if ($this->isDisabledModule($module))
			{
				unset($modules[$position]);
			}
		}
	}

	/**
	 * Inject the module params into module edit form.
	 *
	 * @param   Form    $form  The form to be altered.
	 * @param   mixed   $data  The associated data for the form.
	 *
	 * @return  boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$this->app->isAdmin() || !$form instanceof Form)
		{
			return true;
		}

		if ('module' === Factory::getApplication()->input->get('view'))
		{
			$doc = Factory::getDocument();

			$doc->addScript(Uri::root(true) . '/plugins/system/vanir_module_filter/js/admin.min.js');
		}

		if ($form->getName() === static::FORM_NAME_MODULE && !$this->injectModuleParams($form, $data))
		{
			return false;
		}

		return true;
	}

	/**
	 * Injects module parameters into module form.
	 *
	 * @param   Form    $form  Form instance
	 * @param   mixed   $data  The associated data for the form.
	 *
	 * @return  boolean
	 */
	private function injectModuleParams($form, $data)
	{
		$formsFolder = $this->getPluginPath() . '/forms';

		if (!is_dir($formsFolder))
		{
			return true;
		}

		Form::addFormPath($formsFolder);

		return $form->loadFile('module_vanir_params', true);
	}

	/**
	 * Check if this is an AJAX request
	 *
	 * @return  boolean
	 */
	private function isAjaxRequest()
	{
		return strtolower($this->app->input->server->get('HTTP_X_REQUESTED_WITH', '')) == 'xmlhttprequest';
	}

	/**
	 * Check if current view is html.
	 *
	 * @return  boolean
	 */
	private function isHtmlDocument()
	{
		return Factory::getDocument()->getType() === 'html';
	}

	/**
	 * Check if this we have to perform any action for current view
	 *
	 * @return  boolean
	 */
	private function isEnabledView()
	{
		return !$this->isAjaxRequest() && $this->isHtmlDocument() && $this->isVanirView();
	}

	/**
	 * [isVanir description]
	 *
	 * @return  boolean  [description]
	 */
	private function isVanirView()
	{
		$option = $this->app->input->get('option');

		return $option === 'com_redshopb';
	}

	/**
	 * [isDisabledModule description]
	 *
	 * @param   stdClass  $module  Module to check
	 *
	 * @return  boolean
	 */
	private function isDisabledModule($module)
	{
		$moduleParams = new Registry($module->params);

		$showMode = (int) $moduleParams->get('vanir_show_mode', self::SHOW_MODE_ALWAYS);

		if ($showMode === self::SHOW_MODE_ALWAYS)
		{
			return false;
		}

		if ($this->isModuleDisabledInCategoryView($module))
		{
			return true;
		}

		if ($this->isModuleDisabledInItemView($module))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if a module is disabled in category view.
	 *
	 * @param   stdClass  $module  Module
	 *
	 * @return  boolean
	 */
	private function isModuleDisabledInCategoryView($module)
	{
		if (!$this->isVanirView())
		{
			return false;
		}

		if ($this->app->input->get('layout') !== 'category')
		{
			return false;
		}

		$moduleParams = new Registry($module->params);

		$showMode = (int) $moduleParams->get('vanir_show_in_category_mode', self::SHOW_IN_CATEGORY_MODE_ALWAYS);

		if ($showMode === self::SHOW_IN_CATEGORY_MODE_ALWAYS)
		{
			return false;
		}

		if ($showMode === self::SHOW_IN_CATEGORY_MODE_NEVER)
		{
			return true;
		}

		$currentCategoryId = $this->app->input->getInt('id');

		if (!$currentCategoryId)
		{
			return false;
		}

		$categories = array_filter((array) $moduleParams->get('vanir_show_categories', array()));

		if ($showMode === self::SHOW_IN_CATEGORY_MODE_ALWAYS_FOR)
		{
			return !in_array($currentCategoryId, $categories);
		}

		return $categories ? in_array($currentCategoryId, $categories) : false;
	}

	/**
	 * Check if a module is disabled in item view.
	 *
	 * @param   stdClass  $module  Module
	 *
	 * @return  boolean
	 */
	private function isModuleDisabledInItemView($module)
	{
		if (!$this->isVanirView())
		{
			return false;
		}

		if ($this->app->input->get('layout') !== 'product')
		{
			return false;
		}

		$moduleParams = new Registry($module->params);

		$showMode = (int) $moduleParams->get('vanir_show_in_item_mode', self::SHOW_IN_ITEM_MODE_ALWAYS);

		if ($showMode === self::SHOW_IN_ITEM_MODE_ALWAYS)
		{
			return false;
		}

		if ($showMode === self::SHOW_IN_ITEM_MODE_NEVER)
		{
			return true;
		}

		$currentItemId = $this->app->input->getInt('id');

		if (!$currentItemId)
		{
			return false;
		}

		if ($showMode === self::SHOW_IN_ITEM_MODE_ALWAYS_FOR_PRODUCTS_OF_CATEGORIES
			|| $showMode === self::SHOW_IN_ITEM_MODE_NEVER_FOR_PRODUCTS_OF_CATEGORIES)
		{
			$pathway                   = $this->app->getPathWay()->getPathway();
			$productCategories         = array();
			$filteredProductCategories = array_filter((array) $moduleParams->get('vanir_for_product_of_categories', array()));
			$inFilteredCategories      = false;

			foreach ($pathway as $breadcrumb)
			{
				if (strpos($breadcrumb->link, 'layout=category') !== false)
				{
					$link = str_replace('index.php?', '', $breadcrumb->link);
					parse_str($link, $keyValuePairs);
					array_push($productCategories, $keyValuePairs['id']);
				}
			}

			foreach ($productCategories as $productCategory)
			{
				if (in_array($productCategory, $filteredProductCategories))
				{
					$inFilteredCategories = true;
				}
			}

			if ($showMode === self::SHOW_IN_ITEM_MODE_ALWAYS_FOR_PRODUCTS_OF_CATEGORIES)
			{
				return !$inFilteredCategories;
			}

			if ($showMode === self::SHOW_IN_ITEM_MODE_NEVER_FOR_PRODUCTS_OF_CATEGORIES)
			{
				return $inFilteredCategories;
			}
		}

		$items = array_filter((array) $moduleParams->get('vanir_show_items', array()));

		if ($showMode === self::SHOW_IN_ITEM_MODE_ALWAYS_FOR)
		{
			return !in_array($currentItemId, $items);
		}

		return $items ? in_array($currentItemId, $items) : false;
	}

	/**
	 * Gets the path of the plugin and sets the $pluginPath property
	 *
	 * @return   mixed  Sets the $pluginPath property
	 */
	protected function getPluginPath()
	{
		if (null === $this->pluginPath)
		{
			$reflection = new \ReflectionClass($this);

			$this->pluginPath = dirname($reflection->getFileName());
		}

		return $this->pluginPath;
	}
}
