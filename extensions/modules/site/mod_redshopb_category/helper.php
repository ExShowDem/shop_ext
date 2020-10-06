<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_vanir_category
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
/**
 * Helper for mod_vanir_category
 *
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_vanir_category
 * @since       1.0.0
 */
class ModVanirCategoryHelper
{
	/**
	 * Auto mode
	 */
	const MOD_AUTO = 0;

	/**
	 * Fixed mode
	 */
	const MOD_FIXED = 1;

	/**
	 * Method for get data for module.
	 *
	 * @param   Registry  $params  Module params
	 *
	 * @return  RedshopbEntityCategory    Category entity data.
	 */
	public static function getData(&$params)
	{
		$mode = (int) $params->get('mode', 1);

		if ($mode === self::MOD_FIXED)
		{
			return self::prepareCategory(self::getFixedCategory($params->get('category_id', 0)));
		}

		return self::prepareCategory(self::getAutoModeCategory());
	}

	/**
	 * Method for check current page is product view?
	 *
	 * @return  boolean  True if current page is product detail view.
	 */
	protected static function isProductView()
	{
		$input  = Factory::getApplication()->input;
		$option = $input->getCmd('option', null);
		$view   = $input->getCmd('view', null);
		$layout = $input->getCmd('layout', null);
		$id     = $input->getInt('id', null);

		return ($option === 'com_redshopb' && $view === 'shop' && $layout === 'product' && $id) ? true : false;
	}

	/**
	 * Method for check current page is category view?
	 *
	 * @return  boolean  True if current page is product detail view.
	 */
	protected static function isCategoryView()
	{
		$input  = Factory::getApplication()->input;
		$option = $input->getCmd('option', null);
		$view   = $input->getCmd('view', null);
		$layout = $input->getCmd('layout', null);
		$id     = $input->getInt('id', null);

		return ($option === 'com_redshopb' && $view === 'shop' && $layout === 'category' && $id) ? true : false;
	}

	/**
	 * Get the active category when mode is set to auto
	 *
	 * @return  RedshopbEntityCategory
	 */
	protected static function getAutoModeCategory()
	{
		$input    = Factory::getApplication()->input;
		$id       = $input->getInt('id');
		$category = RedshopbEntityCategory::getInstance();

		if (!$id)
		{
			return $category;
		}

		if (self::isCategoryView())
		{
			return $category->load($id);
		}

		if (self::isProductView())
		{
			$product    = RedshopbEntityProduct::getInstance($id);
			$categories = $product->getCategories();

			if ($categories->isEmpty())
			{
				return $category;
			}

			foreach ($categories as $categoryData)
			{
				return $categoryData;
			}
		}

		return $category;
	}

	/**
	 * Method for get category entity with fixed ID
	 *
	 * @param   int  $id  ID of category.
	 *
	 * @return  RedshopbEntityCategory  Category entity object.
	 */
	protected static function getFixedCategory($id = 0)
	{
		$category = RedshopbEntityCategory::getInstance();

		if (!$id)
		{
			return $category;
		}

		return $category->load($id);
	}

	/**
	 * Method for prepare category data.
	 *
	 * @param   RedshopbEntityCategory  $category  Category entity data
	 *
	 * @return  RedshopbEntityCategory             Category object data
	 */
	protected static function prepareCategory($category)
	{
		if (!$category->isLoaded())
		{
			return $category;
		}

		$category->link = Route::_(
			RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=category&id=' . (int) $category->getId()), false
		);

		return $category;
	}
}
