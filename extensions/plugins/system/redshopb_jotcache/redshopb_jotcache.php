<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::import('redshopb.library');

/**
 * Redshopb JotCache System Plugin
 *
 * @package     Aesir.E-Commerce
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemRedshopb_Jotcache extends CMSPlugin
{
	/**
	 * onRedshopbBeforeRenderTemplate
	 *
	 * @param   string  $templateDesc   Template content
	 * @param   string  $group          Template group
	 * @param   string  $scope          Template Scope
	 * @param   int     $templateId     Template id
	 * @param   object  $entityData     Entity data
	 *
	 * @return  void
	 * @throws Exception
	 *
	 * @since 1.13.0
	 */
	public function onRedshopbBeforeRenderTemplate(&$templateDesc, $group, $scope, $templateId, $entityData)
	{
		if ($group == 'shop'
			&& $scope == 'product-list-collection')
		{
			$this->setVanirSessionHash();
		}
	}

	/**
	 * After routing, checks login/logout status, current company or template set, and redirects to set a new layout if needed
	 *
	 * @return  void
	 * @throws Exception
	 *
	 * @since   1.13.0
	 */
	public function onAfterRoute()
	{
		$input = Factory::getApplication()->input;

		if ($input->getCmd('option') == 'com_redshopb'
			&& $input->getCmd('view') == 'shop')
		{
			$this->setVanirSessionHash();
		}
	}

	/**
	 * @since  2.1.0
	 * @throws Exception
	 * @return  void
	 */
	protected function setVanirSessionHash()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if (!$app->isClient('site')
			|| $input->getMethod() == 'POST'
			|| !Factory::getUser()->guest
			|| JDEBUG
			|| !PluginHelper::isEnabled('system', 'jotcache'))
		{
			return;
		}

		$shopSessionHash = array();
		$layout          = $input->getCmd('layout');
		$itemKey         = null;
		$id              = $input->getInt('id', 0);

		if (!$layout)
		{
			$layout = $app->getUserState('shop.layout');
		}

		if ($layout)
		{
			$itemKey = $layout . '_' . $id;
		}
		else
		{
			$itemKey = $app->getUserState('shop.itemKey');

			if (!$itemKey)
			{
				$layout  = 'default';
				$itemKey = $layout . '_' . $id;
			}
			else
			{
				$layout = explode('_', $itemKey)[0];
			}
		}

		$shopSessionHash[] = $layout;
		$shopSessionHash[] = $itemKey;
		$shopSessionHash[] = $app->getUserState('shop.show.' . $layout . '.SortByDir', 'asc');
		$shopSessionHash[] = $app->getUserState('shop.show.' . $layout . '.SortBy', 'name');
		$shopSessionHash[] = $app->getUserState('shop.show.' . $layout . '.ProductsAs', '');
		$shopSessionHash[] = $app->getUserState('shop.categoryfilter.' . $itemKey, array());
		$shopSessionHash[] = $app->getUserState('shop.customer_type', '');
		$shopSessionHash[] = $app->getUserState('shop.customer_id', 0);
		$shopSessionHash[] = $app->getUserState('mod_filter.search.' . $itemKey, '');
		$shopSessionHash[] = $app->getUserState('shop.manufacturer.' . $itemKey, array());
		$shopSessionHash[] = $app->getUserState('shop.tag.' . $itemKey, array());
		$shopSessionHash[] = $app->getUserState('shop.campaign_price.' . $itemKey, '');
		$shopSessionHash[] = $app->getUserState('shop.price_range.' . $itemKey, '');
		$shopSessionHash[] = $app->getUserState('shop.in_stock.' . $itemKey, 0);
		$shopSessionHash[] = $app->getUserState('filter.' . $itemKey, '');
		$shopSessionHash[] = $app->getUserState('shop.productlist.page.' . $itemKey, 0);
		$shopSessionHash[] = $app->getUserState('shop.attributefilter.' . $itemKey, array());

		if (true === RedshopbEntityConfig::getInstance()->getBool('no_pagination', false)
			&& 'category' == $layout)
		{
			$shopSessionHash[] = $app->getUserState('shop.pagination.limit.category_' . $id);
		}

		$app->triggerEvent('enrichRedshopbJotCacheHash', [&$shopSessionHash]);

		$md5SessionHash = md5(serialize($shopSessionHash));

		if (!$app->getUserState('shop.itemKey') == $itemKey
			|| $app->getUserState('vanir.shop.session.hash') != $md5SessionHash)
		{
			$app->setUserState('shop.layout', $layout);
			$app->setUserState('shop.itemKey', $itemKey);
			$app->setUserState('vanir.shop.session.hash', $md5SessionHash);
		}

		$dispatcher = \JEventDispatcher::getInstance();

		$reflectionMethods = new ReflectionProperty($dispatcher, '_methods');
		$reflectionMethods->setAccessible(true);
		$methods = $reflectionMethods->getValue($dispatcher);
		$reflectionMethods->setAccessible(false);

		$reflectionObservers = new ReflectionProperty($dispatcher, '_observers');
		$reflectionObservers->setAccessible(true);
		$observers     = $reflectionObservers->getValue($dispatcher);
		$observersKeys = $methods['onafterroute'];
		$reflectionMethods->setAccessible(false);

		foreach ($observersKeys as $observersKey)
		{
			if (get_class($observers[$observersKey]) == 'plgSystemJotCache')
			{
				$jotCacheObj = $observers[$observersKey];

				$reflectionJotCache = new ReflectionProperty($jotCacheObj, 'cache');
				$reflectionJotCache->setAccessible(true);

				$cache  = $reflectionJotCache->getValue($jotCacheObj);
				$config = Factory::getConfig();

				$id = md5(
					$cache->options['uri'] . '-' . $cache->options['browser'] . $cache->options['cookies']
					. $cache->options['sessionvars'] . '#' . $md5SessionHash
				);

				$cache->fname = md5('-' . $id . '-' . $config->get('secret') . '-' . $config->get('language', 'en-GB'));
				$reflectionId = new ReflectionProperty($cache, 'id');
				$reflectionId->setAccessible(true);
				$reflectionId->setValue($cache, $id);
				$reflectionId->setAccessible(false);

				$reflectionJotCache->setValue($jotCacheObj, $cache);
				$reflectionJotCache->setAccessible(false);
			}
		}
	}
}
