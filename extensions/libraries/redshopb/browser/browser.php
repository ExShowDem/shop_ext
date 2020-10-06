<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Browser
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Router\Router;

/**
 * Custom Browser.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Browser
 * @since       1.0
 */
class RedshopbBrowser extends RBrowser
{
	const REDSHOPB_HISTORY = 'com_redshop.history';

	/**
	 * @var array
	 */
	protected $sefs = array();

	/**
	 * @var string
	 */
	protected $langCode;

	/**
	 * Constructor.
	 *
	 * @param   string  $history  The history name (also used for sessions).
	 */
	protected function __construct($history)
	{
		$this->history  = new RedshopbBrowserHistory($history);
		$this->sefs     = LanguageHelper::getLanguages('lang_code');
		$this->langCode = Factory::getLanguage()->getTag();
	}

	/**
	 * Clear the history until the uri.
	 * Two uris are equal if their view and id vars are the same.
	 *
	 * @param   mixed  $url  The uri until
	 *
	 * @return  void
	 */
	public function clearHistoryUntil($url = 'SERVER')
	{
		$history = $this->history->getQueue();

		if (empty($history))
		{
			return;
		}

		$uri        = $this->getUri($url);
		$view       = $uri->getVar('view');
		$id         = $uri->getVar('id');
		$newHistory = array();
		$found      = false;

		foreach ($history as $oldLink)
		{
			$oldUri       = $this->getUri($oldLink);
			$oldView      = $oldUri->getVar('view');
			$oldId        = $oldUri->getVar('id');
			$newHistory[] = $oldLink;

			if ($oldView === $view && $oldId === $id)
			{
				$found = true;
				break;
			}
		}

		if (!$found)
		{
			$newHistory = array();
		}

		$this->history->setQueue($newHistory);
	}

	/**
	 * Get Uri
	 *
	 * @param   string  $url  URL
	 *
	 * @return  Uri
	 */
	public function getUri($url = 'SERVER')
	{
		static $uriArray = array();

		if (array_key_exists($url, $uriArray))
		{
			return $uriArray[$url];
		}

		if ($url == 'SERVER')
		{
			$url = Uri::getInstance($url)->toString();
		}

		$menu = Factory::getApplication()->getMenu();

		// If it's not SEF link or SEF is disabled entirely we do not need to parse it
		if (!Factory::getConfig()->get('sef')
			|| strpos($url, 'index.php?') === 0)
		{
			$uri = new Uri($url);

			// If this link contains only Itemid, then we need to populate values from the related menu item
			if (strpos($url, 'index.php?') === 0
				&& empty($uri->getVar('option'))
				&& !empty($uri->getVar('Itemid')))
			{
				$menuItem = $menu->getItem($uri->getVar('Itemid'));

				if ($menuItem)
				{
					$uri->setQuery(array_replace($menuItem->query, $uri->getQuery(true)));
				}
			}

			$uriArray[$url] = $uri;

			return $uriArray[$url];
		}

		$activeItem = $menu->getActive();
		$active     = 0;

		if ($activeItem)
		{
			$active = $activeItem->id;
		}

		// This will enable both SEF and non-SEF URI to be parsed properly
		$router = clone Router::getInstance('site');
		$uri    = new Uri($url);

		$language = Factory::getLanguage();

		$lang = $uri->getVar('lang', $this->langCode);
		$uri->setVar('lang', $lang);

		if (isset($this->sefs[$lang]))
		{
			$lang = $this->sefs[$lang]->sef;
			$uri->setVar('lang', $lang);
		}

		$router->setVars(array(), false);
		$query = $router->parse($uri);
		$query = array_merge($query, $uri->getQuery(true));
		$uri->setQuery($query);

		// Using joomla router for parse urls can change active link
		if ($active)
		{
			$menu->setActive($active);
		}

		// We are removing format because of default value is csv if present and if not set
		// and we are never going to remember csv page in a browser history anyway
		$uri->delVar('format');

		$uriArray[$url] = $uri;

		$newLang = Factory::getLanguage();

		if ($language === $newLang)
		{
			return $uriArray[$url];
		}

		foreach ($language->getPaths() as $extension => $files)
		{
			foreach ($files AS $file => $value)
			{
				$basePath = substr($file, 0, strpos($file, '/language'));
				$newLang->load($extension, $basePath);
			}
		}

		return $uriArray[$url];
	}

	/**
	 * Browse the given uri.
	 *
	 * @param   string   $url            The uri
	 * @param   boolean  $duplicateLast  True to duplicate the last element if it's the same.
	 *
	 * @return  RBrowser
	 */
	public function browse($url = 'SERVER', $duplicateLast = false)
	{
		$uri    = $this->getUri($url);
		$urlGen = 'index.php?' . $uri->getQuery();
		$this->history->enqueue($urlGen, $duplicateLast);

		return $this;
	}

	/**
	 * Clear the browser history.
	 *
	 * @return  RBrowser
	 */
	public function clearHistory()
	{
		$this->history->clear();

		return $this;
	}
}
