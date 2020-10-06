<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Multilanguage;

/**
 * Redshopb route helper
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
abstract class RedshopbHelperRoute
{
	/**
	 * @var string
	 */
	private static $defaultView = 'dashboard';

	/**
	 * Check the url, and potentially adds an itemId
	 *
	 * @param   string $url the url
	 *
	 * @return string
	 */
	public static function getRoute($url)
	{
		$uri = new Uri($url);

		if ($uri->getVar('option') != 'com_redshopb'
			|| empty($uri->getVar('view')))
		{
			return $url;
		}

		if ($uri->getVar('view') == 'shop'
			&& $uri->getVar('layout') == 'product')
		{
			if (!$uri->getVar('collection'))
			{
				$uri->delVar('collection');
			}

			$uri->delVar('category_id');
		}

		// This part is useful if several menu items refer into the same entity then we need to show it and avoid sh404sef duplicates
		if (!empty($uri->getVar('Itemid')))
		{
			$itemMenu = Factory::getApplication()->getMenu()->getItem($uri->getVar('Itemid'));

			if (!empty($itemMenu->query))
			{
				$match = true;

				foreach ($itemMenu->query as $key => $value)
				{
					if (!$uri->hasVar($key))
					{
						$match = false;
						break;
					}

					$value = (string) (($key == 'id') ? (int) $value : $value);

					if ($uri->getVar($key) !== $value)
					{
						$match = false;
						break;
					}
				}

				// Nothing to do the link is correct
				if ($match)
				{
					return $url;
				}
			}
		}

		$itemId = self::findViewItemId($uri);

		if (!empty($itemId))
		{
			$uri->setVar('Itemid', $itemId);
		}

		return $uri->toString(array('path', 'query'));
	}

	/**
	 * Try to find exact itemid match for view
	 *
	 * @param   Uri $uri Uri instance
	 *
	 * @return integer
	 */
	protected static function findViewItemId($uri)
	{
		$view   = $uri->getVar('view', '');
		$layout = (string) $uri->getVar('layout', '');
		$id     = (integer) $uri->getVar('id', '');

		// If it's trying to get the URL for offers, returns the one for shop
		if ($view == 'shop' && $layout == 'offers')
		{
			$uri->setVar('layout', '');

			return self::findViewItemId($uri);
		}

		if (empty($view))
		{
			$view = self::$defaultView;
		}

		$language = Factory::getLanguage()->getTag();
		$app      = Factory::getApplication();
		$menus    = $app->getMenu('site');
		$active   = $menus->getActive();

		// Extracts the needles to search in the lookup array
		$needles                  = array();
		$needles['']              = '';
		$needles['empty']         = array('');
		$needles['view']          = $view;
		$needles['layout']        = $layout;
		$needles['id']            = array($id);
		$needles['view-plural']   = ($view != '' ? self::getPlural($view) : '');
		$needles['layout-plural'] = ($layout != '' ? self::getPlural($layout) : '');
		$needles['layout-parent'] = ($layout != '' ? self::getParentEntity($layout) : '');

		if ($needles['layout-parent'] != '' && $needles['id'][0] != '')
		{
			$needles['id-parent'] = self::getParentEntityIds($needles['id'][0], $needles['layout'], $needles['layout-parent']);
		}
		else
		{
			$needles['id-parent'] = '';
		}

		$lookup = self::getLookup($language);

		// ID parent lookup
		if ($needles['id'][0] != '' && ($needles['view-plural'] != '' || $needles['layout-plural'] != ''))
		{
			// Selects the entity name (plural), using the layout as a first priority (more specific) and the view as a second priority
			$entityPlural  = ($needles['layout-plural'] != '' ? $needles['layout-plural'] : $needles['view-plural']);
			$needles['id'] = array_merge($needles['id'], self::getParentIds($needles['id'][0], $entityPlural));
		}

		// Criteria (in hierarchical order) to lookup the menu item
		if ($view == 'shop' && $layout == 'product')
		{
			$needles['layout-parent-plural'] = ($needles['layout-parent'] != '' ? self::getPlural($needles['layout-parent']) : '');
			$searchCriteria                  = array(
				array('view' => 'view', 'layout' => 'layout', 'id' => 'id'),
				array('view' => 'view', 'layout' => 'layout', 'id' => 'empty'),
				array('view' => 'view', 'layout' => 'layout-parent', 'id' => 'id-parent'),
				array('view' => 'view', 'layout' => 'layout-parent', 'id' => 'empty'),
				array('view' => 'view', 'layout' => 'layout-parent-plural', 'id' => 'empty'),
				array('view' => 'view', 'layout' => '', 'id' => 'empty')
			);
		}
		elseif ($view == 'shop' && $layout == 'manufacturer')
		{
			$needles['manufacturerlist'] = 'manufacturerlist';
			$searchCriteria              = array(
				array('view' => 'view', 'layout' => 'layout', 'id' => 'id'),
				array('view' => 'manufacturerlist', 'layout' => '', 'id' => 'empty'),
				array('view' => 'view', 'layout' => '', 'id' => 'empty')
			);
		}
		elseif ($view == 'manufacturerlist')
		{
			$needles['shop'] = 'shop';
			$searchCriteria  = array(
				array('view' => 'view', 'layout' => '', 'id' => 'empty'),
				array('view' => 'shop', 'layout' => '', 'id' => 'empty')
			);
		}
		elseif ($view == 'shop' && $layout == 'category')
		{
			$searchCriteria = array(
				array('view' => 'view', 'layout' => 'layout', 'id' => 'id'),
				array('view' => 'view', 'layout' => 'layout-parent', 'id' => 'id-parent'),
				array('view' => 'view', 'layout' => 'layout', 'id' => 'empty'),
				array('view' => 'view', 'layout' => 'layout-plural', 'id' => 'empty'),
				array('view' => 'view', 'layout' => '', 'id' => 'empty')
			);
		}
		elseif ($view == 'shop' && $layout == 'categories')
		{
			$searchCriteria = array(
				array('view' => 'view', 'layout' => 'layout', 'id' => 'empty'),
				array('view' => 'view', 'layout' => '', 'id' => 'empty')
			);
		}
		elseif ($view == 'shop' && in_array($layout, array('delivery', 'confirm', 'pay', 'payment', 'receipt', 'shipping')))
		{
			$searchCriteria = array(
				array('view' => 'view', 'layout' => 'layout-parent', 'id' => 'empty'),
				array('view' => 'view', 'layout' => '', 'id' => 'empty')
			);
		}
		elseif ($view == 'shop' && empty($layout))
		{
			$searchCriteria = array(
				array('view' => 'view', 'layout' => '', 'id' => 'empty')
			);
		}
		else
		{
			$searchCriteria = array(
				array('view' => 'view', 'layout' => 'layout', 'id' => 'id'),
				array('view' => 'view', 'layout' => 'layout-parent', 'id' => 'id-parent'),
				array('view' => 'view', 'layout' => 'layout', 'id' => 'empty'),
				array('view' => 'view', 'layout' => 'layout-plural', 'id' => 'empty'),
				array('view' => 'view', 'layout' => '', 'id' => 'id'),
				array('view' => 'view', 'layout' => '', 'id' => 'empty'),
				array('view' => 'view-plural', 'layout' => 'layout', 'id' => 'id'),
				array('view' => 'view-plural', 'layout' => 'layout', 'id' => 'empty'),
				array('view' => 'view-plural', 'layout' => '', 'id' => 'id'),
				array('view' => 'view-plural', 'layout' => '', 'id' => 'empty')
			);
		}

		static $foundCriteria = array();
		$passedSearchCriteria = array();

		foreach ($searchCriteria as $criteria)
		{
			$needlesView      = $needles[$criteria['view']];
			$needlesLayout    = $needles[$criteria['layout']];
			$extendedCriteria = array(
				'view'   => $needlesView,
				'layout' => $needlesLayout
			);

			if (!empty($lookup[$needlesView][$needlesLayout]))
			{
				$needlesIds = $needles[$criteria['id']];

				if ($needlesIds)
				{
					// Explores all the available IDs including the parents'
					foreach ($needlesIds as $id)
					{
						$extendedCriteria['real_id'] = $id;
						$keyCriteria                 = serialize($extendedCriteria);

						if (array_key_exists($keyCriteria, $foundCriteria))
						{
							$result = $foundCriteria[$keyCriteria];
							goto foundResult;
						}

						$passedSearchCriteria[] = $extendedCriteria;

						if (!empty($lookup[$needlesView][$needlesLayout][$id]))
						{
							$itemId = $lookup[$needlesView][$needlesLayout][$id];
							$item   = $menus->getItem($itemId);
							$result = $item->id;
							goto foundResult;
						}
					}
				}
			}
			else
			{
				$keyCriteria = serialize($extendedCriteria);

				if (array_key_exists($keyCriteria, $foundCriteria))
				{
					$result = $foundCriteria[$keyCriteria];
					goto foundResult;
				}

				$passedSearchCriteria[] = $criteria;
			}
		}

		// If we have link to dashboard
		if (!empty($lookup['dashboard']))
		{
			$result = $lookup['dashboard'][''][''];
			goto foundResult;
		}

		// Check if the active menuitem matches the requested language
		if ($active
			&& ($language == '*' || in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled())
			&& !empty($active->query['layout']) && $active->query['layout'] != 'offers'
		)
		{
			$result = $active->id;
			goto foundResult;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);
		$result  = !empty($default->id) ? $default->id : null;

		foundResult:

		foreach ($passedSearchCriteria as $criteria)
		{
			$keyCriteria                 = serialize($criteria);
			$foundCriteria[$keyCriteria] = $result;
		}

		return $result;
	}

	/**
	 * init Lookup
	 *
	 * @param   string  $language  Language tag
	 *
	 * @throws  Exception
	 *
	 * @return  array
	 */
	protected static function getLookup($language)
	{
		static $lookup = array();

		// Prepare the reverse lookup array.
		if (!array_key_exists($language, $lookup))
		{
			$lookup[$language] = array();
			$dashboardId       = 0;

			$component = ComponentHelper::getComponent('com_redshopb');

			$attributes = array('component_id');
			$values     = array($component->id);

			$attributes[] = 'language';
			$langValues   = ['*'];

			if ($language != '*')
			{
				$langValues[] = $language;
			}

			$values[] = $langValues;

			$app   = Factory::getApplication();
			$menus = $app->getMenu('site');
			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view   = $item->query['view'];
					$layout = (isset($item->query['layout']) ? $item->query['layout'] : '');
					$id     = (isset($item->query['id']) ? $item->query['id'] : '');
					$param  = $item->getParams();

					if ($param->exists('is_default') && ! (bool) $param->get('is_default', false))
					{
						continue;
					}

					if ($view == 'dashboard' && $dashboardId)
					{
						continue;
					}

					if (!isset($lookup[$language][$view]))
					{
						$lookup[$language][$view] = array();
					}

					if (!isset($lookup[$language][$view][$layout]))
					{
						$lookup[$language][$view][$layout] = array();
					}

					if ($view == 'dashboard' && $item->params->get('companyid') == 0)
					{
						$dashboardId = $item->id;
					}

					if (empty($lookup[$language][$view][$layout][$id])
						// Several menu items with the same link? try to use one with access = 1 in case a user has an access for several levels
						|| ($menus->getItem($lookup[$language][$view][$layout][$id])->access != 1
						&& $item->access == 1))
					{
						$lookup[$language][$view][$layout][$id] = $item->id;
					}
				}
			}
		}

		return $lookup[$language];
	}

	/**
	 * Get all component menu items
	 *
	 * @return array
	 */
	protected static function getMenuItems()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu('site');

		$component = ComponentHelper::getComponent('com_redshopb');

		$attributes = array('component_id');
		$values     = array($component->id);

		$items = $menus->getItems($attributes, $values);

		return $items;
	}

	/**
	 * Get the plural of a certain entity (view, layout, etc)
	 *
	 * @param   string $entity Entity to get the plural from
	 *
	 * @return array
	 */
	protected static function getPlural($entity)
	{
		$word = RInflector::pluralize($entity);

		return ($word != '' ? $word : $entity);
	}

	/**
	 * Get the the parent ids of a certain entity (from the same entity)
	 *
	 * @param   int    $id     Child entity id
	 * @param   string $entity Entity name (plural)
	 *
	 * @return  array
	 */
	protected static function getParentIds($id, $entity)
	{
		static $parentEntities = array();

		if (!array_key_exists($entity, $parentEntities))
		{
			$parentEntities[$entity] = false;

			/** @var RedshopbModel */

			if (JFile::exists(JPATH_SITE . '/components/com_redshopb/models/' . strtolower($entity) . '.php'))
			{
				$model = RModelAdmin::getFrontInstance($entity, array(), 'com_redshopb');

				if ($model && method_exists($model, 'getParents'))
				{
					$parentEntities[$entity] = $model;
				}
			}
		}

		if (!is_object($parentEntities[$entity]))
		{
			return array();
		}

		$parents = $parentEntities[$entity]->getParents($id);

		if (empty($parents))
		{
			return array();
		}

		return $parents;
	}

	/**
	 * Get the the parent a certain entity (product/category, department/company, etc)
	 *
	 * @param   string $entity Entity to get the parent from
	 *
	 * @return string
	 */
	protected static function getParentEntity($entity)
	{
		switch ($entity)
		{
			case 'product':
				$result = 'category';
				break;
			case 'delivery':
			case 'confirm':
			case 'pay':
			case 'payment':
			case 'receipt':
			case 'shipping':
				$result = 'cart';
				break;

			default:
				$result = '';
		}

		return $result;
	}

	/**
	 * Get the the parent ids of a certain entity (from the parent entity)
	 *
	 * @param   int    $id        Child entity id
	 * @param   string $entity    Child entity name
	 * @param   string $parent    Parent entity name
	 * @param   int    $applicant Applicant id
	 *
	 * @return array
	 */
	protected static function getParentEntityIds($id, $entity, $parent, $applicant = 0)
	{
		$pluralEntity = self::getPlural($entity);

		/** @var RedshopbModel */
		$model = RModelAdmin::getFrontInstance($pluralEntity, array(), 'com_redshopb');

		if (JFile::exists(JPATH_SITE . '/components/com_redshopb/models/' . strtolower($pluralEntity) . '.php')
			&& $model
		)
		{
			// Looks up for the getParentEntityId function
			if (method_exists($model, 'getParentEntityId'))
			{
				$parentId = $model->getParentEntityId($id, $applicant);

				if ($parentId)
				{
					$pluralParent = self::getPlural($parent);
					$parentIds    = array_merge(array($parentId), self::getParentIds($parentId, $pluralParent));

					return $parentIds;
				}
			}
		}

		return array();
	}

	/**
	 * Replace Non URL Symbols
	 *
	 * @param   string $string URL string
	 *
	 * @return string
	 */
	public static function replaceNonURLSymbols($string)
	{
		return strtolower(
			trim(
				preg_replace(
					'~[^0-9a-z]+~i', '-',
					html_entity_decode(
						preg_replace(
							'~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1',
							htmlentities($string, ENT_QUOTES, 'UTF-8')
						), ENT_QUOTES, 'UTF-8'
					)
				), '-'
			)
		);
	}

	/**
	 * Get Redshop Menu Items
	 *
	 * @param   string $url URL
	 *
	 * @return string
	 */
	public static function findRedshopbMenuItem($url = '')
	{
		$itemId = 0;
		$uri    = Uri::getInstance($url);

		if ($uri->getVar('option') != 'com_redshopb')
		{
			return $itemId;
		}

		$language = Factory::getLanguage()->getTag();
		$lookup   = self::getLookup($language);

		$view   = (string) $uri->getVar('view', '');
		$layout = (string) $uri->getVar('layout', '');
		$id     = (string) $uri->getVar('id', '');

		if (isset($lookup[$view][$layout][$id]))
		{
			$itemId = $lookup[$view][$layout][$id];
		}

		return $itemId;
	}
}
