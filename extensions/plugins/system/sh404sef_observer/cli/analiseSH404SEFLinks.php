<?php
/**
 * @package     Sh4040observer.Cli
 *
 * @copyright   Copyright (C) 2012 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
error_reporting(0);
ini_set('display_errors', 0);

const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/../defines.php'))
{
	require_once dirname(__DIR__) . '/../defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__) . '/..');
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

define('JDEBUG', 0);
$_SERVER['REQUEST_METHOD'] = 'GET';

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Language\LanguageHelper;
use Sh404sefObserver\Helper\UrlHelper;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

// Host 'localhost/' uses especially for sh404sef for avoid redirect here
$_SERVER['HTTP_HOST'] = 'localhost/';
Factory::getApplication('site');

/**
 * FixSefLinksApplicationCli
 *
 * @since       2.6.0
 */
class FixSefLinksApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 * @since 2.6.0
	 * @throws Exception
	 */
	public function doExecute()
	{
		$this->out('Started');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__users'))
			->order('id asc');

		$userId = $db->setQuery($query, 0, 1)->loadResult();

		$config = Factory::getConfig();

		// Login as first user and set his as root user during this session
		$config->set('root_user', $userId);

		$user = User::getInstance($userId);

		Factory::getSession()->set('user', $user);

		// Detect default language
		$params = ComponentHelper::getParams('com_languages');

		$config->set('language', $params->get('site', $this->get('language', 'en-GB')));

		$app = Factory::getApplication('site');
		PluginHelper::importPlugin('system');

		$app->triggerEvent('onAfterInitialise');

		$router = new SiteRouter;

		$enabledPlugins = PluginHelper::getPlugin('sh404sef_observer');

		if (empty($enabledPlugins)
			|| !PluginHelper::isEnabled('system', 'sh404sef_observer'))
		{
			$this->out('No activated plugins found');

			return;
		}

		$params = new Registry(PluginHelper::getPlugin('system', 'sh404sef_observer')->params);

		$find = [];

		foreach ($enabledPlugins as $enabledPlugin)
		{
			$find[] = 'newurl LIKE ' . $db->q('index.php?option=com_' . $enabledPlugin->name . '%');
		}

		$query = $db->getQuery(true)
			->delete($db->qn('#__sh404sef_urls'))
			->where('rank != 0')
			->where('dateadd = ' . $db->q('0000-00-00'))
			->where('(' . implode(' OR ', $find) . ')');

		$db->setQuery($query)->execute();

		$chunkQuery = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__sh404sef_urls'))
			->where('rank = 0')
			->where('(' . implode(' OR ', $find) . ')')
			->order('id ASC');

		$offset = 0;
		$limit  = 100;

		PluginHelper::importPlugin('sh404sef_observer');

		$languages = LanguageHelper::getLanguages('sef');

		$defLang = $config->get('language');

		$urlHelper = new UrlHelper;

		while ($results = $db->setQuery($chunkQuery, $offset, $limit)->loadObjectList())
		{
			foreach ($results as $result)
			{
				$uri  = new Uri($result->newurl);
				$lang = (string) $uri->getVar('lang');

				if (!empty($lang))
				{
					if (array_key_exists($lang, $languages))
					{
						if ($languages[$lang]->lang_code != $config->get('language'))
						{
							$config->set('language', $languages[$lang]->lang_code);
							Factory::$language = null;
						}
					}
					else
					{
						if ($defLang != $config->get('language'))
						{
							$config->set('language', $defLang);
							Factory::$language = null;
						}
					}
				}
				else
				{
					if ($defLang != $config->get('language'))
					{
						$config->set('language', $defLang);
						Factory::$language = null;
					}
				}

				$check = $app->triggerEvent('onCheckIfRelatedEntityStillExist', [$uri]);

				// Delete URLs to entities which no longer exist
				if (in_array(false, $check, true))
				{
					if ($params->get('deleted_entities', 'redirect_to_home') == 'redirect_to_home')
					{
						$urlHelper->setRedirectIfDoesNotExist($result->oldurl, sh404SEF_HOMEPAGE_CODE);
					}

					$query = $db->getQuery(true)
						->delete($db->qn('#__sh404sef_urls'))
						->where('id = ' . (int) $result->id);

					$db->setQuery($query)->execute();

					Sh404sefHelperCache::removeUrlFromCache([$result->newurl]);

					continue;
				}

				$component  = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $uri->getVar('option'));
				$compRouter = $router->getComponentRouter($component);
				$routeQuery = $compRouter->preprocess((array) $uri->getQuery(true));

				$uri->setQuery($routeQuery);

				$sortedUrl = Sh404sefHelperUrl::sortURL($uri->toString());

				if ($result->newurl != $sortedUrl)
				{
					Sh404sefHelperCache::removeUrlFromCache([$result->newurl]);

					$urlHelper->replaceUrl($result->newurl, $sortedUrl, ['alias', 'meta']);

					$query = $db->getQuery(true)
						->select('id, oldurl')
						->from($db->qn('#__sh404sef_urls'))
						->where('newurl = ' . $db->q($sortedUrl));

					$theSameUrl = $db->setQuery($query, 0, 1)->loadObject();

					// Hmm, a record with the same URL is existing
					if (!empty($theSameUrl))
					{
						if ($theSameUrl->oldurl != $result->oldurl)
						{
							$urlHelper->replaceUrl($result->oldurl, $sortedUrl, ['redirect']);
						}

						$query = $db->getQuery(true)
							->delete($db->qn('#__sh404sef_urls'))
							->where('id = ' . (int) $result->id);

						$db->setQuery($query)->execute();
					}
					else
					{
						$query = $db->getQuery(true)
							->update($db->qn('#__sh404sef_urls'))
							->where('id = ' . (int) $result->id)
							->set('newurl = ' . $db->q($sortedUrl));

						$db->setQuery($query)->execute();
					}
				}
			}

			$offset += $limit;

			$this->out('Checked ' . $offset . ' records');
		}

		$query = $db->getQuery(true)
			->select('newurl, COUNT(*) as countNewUrl')
			->from($db->qn('#__sh404sef_urls'))
			->group('newurl')
			->where('(' . implode(' OR ', $find) . ')')
			->having('countNewUrl > 1');

		$items = $db->setQuery($query)->loadColumn();

		$allDuplicates = [];

		foreach ($items as $item)
		{
			$query = $db->getQuery(true)
				->select('id, oldurl')
				->from($db->qn('#__sh404sef_urls'))
				->where('newurl = ' . $db->q($item))
				->order('rank DESC, cpt ASC');

			$duplicates = $db->setQuery($query)
				->loadObjectList();

			if (!empty($duplicates))
			{
				// Left one with the smallest rank
				$theMain = array_pop($duplicates);

				foreach ($duplicates as $duplicate)
				{
					if ($theMain->oldurl != $duplicate->oldurl)
					{
						$urlHelper->replaceUrl($duplicate->oldurl, $item, ['redirect']);
					}

					$allDuplicates[] = $duplicate->id;
				}
			}
		}

		if (!empty($allDuplicates))
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__sh404sef_urls'))
				->where('id IN (' . implode(',', $allDuplicates) . ')');

			$db->setQuery($query)->execute();
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

CliApplication::getInstance('FixSefLinksApplicationCli')
	->execute();
