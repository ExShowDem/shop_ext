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

use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock;
use Sh404sefObserver\Helper\UrlHelper;
use Joomla\Registry\Registry;

// Host 'localhost/' uses especially for sh404sef for avoid redirect here
$_SERVER['HTTP_HOST'] = 'localhost/';
Factory::getApplication('site');

/**
 * Sef404ObserverProcessApplicationCli
 *
 * @since       2.6.0
 */
class Sef404ObserverProcessApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function doExecute()
	{
		$this->out('Started');

		$app = Factory::getApplication();

		if (!PluginHelper::isEnabled('system', 'sh404sef_observer'))
		{
			return;
		}

		PluginHelper::importPlugin('system');

		$app->triggerEvent('onAfterInitialise');

		$store = new FlockStore(Factory::getConfig()->get('tmp_path'));

		$factory = new Lock\Factory($store);

		$lock = $factory->createLock('sh404sef-observer');

		if (!$lock->acquire())
		{
			return;
		}

		try
		{
			PluginHelper::importPlugin('sh404sef_observer');
			$params = new Registry(PluginHelper::getPlugin('system', 'sh404sef_observer')->params);
			$db     = Factory::getDbo();
			$router = Router::getInstance('site');

			$observerQuery = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__sh404sef_observer'))
				->order('id asc');

			$queues = $db->setQuery($observerQuery, 0, 10)->loadObjectList();

			$urlHelper = new UrlHelper;

			while (!empty($queues))
			{
				foreach ($queues as $queue)
				{
					$data = json_decode($queue->data, true);

					if (!empty($data))
					{
						foreach ($data as $task => $conditions)
						{
							if (empty($conditions))
							{
								continue;
							}

							switch ($task)
							{
								case 'toUpdate':
									$query = $this->getCommonQuery($conditions)
										->select('*')
										->from($db->qn('#__sh404sef_urls'))

										// Do not touch custom changes
										->where('dateadd = ' . $db->q('0000-00-00'));

									$results = $db->setQuery($query)->loadObjectList('id');

									if (empty($results))
									{
										break;
									}

									$cachedNonSefUrls = [];

									foreach ($results as $result)
									{
										$uri = new Uri($result->newurl);

										$component  = preg_replace('/[^A-Z0-9_\.-]/i', '', $uri->getVar('option'));
										$compRouter = $router->getComponentRouter($component);
										$routeQuery = $compRouter->preprocess((array) $uri->getQuery(true));

										$withOldItemId = $result->newurl;

										$uri->setQuery($routeQuery);

										$sortedUrl = Sh404sefHelperUrl::sortURL($uri->toString());
										$redirects = [];

										if ($result->newurl != $sortedUrl)
										{
											$result->newurl = $sortedUrl;

											if ($result->rank == 0)
											{
												$urlHelper->replaceUrl($withOldItemId, $result->newurl, ['alias', 'meta']);
											}

											// If found duplicates of the same non-SEF URL, then need to leave only one
											$query = $db->getQuery(true)
												->select('*')
												->from($db->qn('#__sh404sef_urls'))
												->where('newurl = ' . $db->q($result->newurl))
												->order('rank ASC, cpt DESC');

											$duplicates = $db->setQuery($query)
												->loadObjectList();

											if (!empty($duplicates))
											{
												$first = true;

												foreach ($duplicates as $duplicate)
												{
													// The first one is the best to use
													if ($first && $result->rank >= $duplicate->rank)
													{
														if ($result->oldurl != $duplicate->oldurl)
														{
															$redirects[] = $result->oldurl;
														}

														Sh404sefHelperCache::removeUrlFromCache([$result->newurl]);
														$urlHelper->deleteUrlById($result->id);
														unset($results[$result->id]);
														$result = $duplicate;
														$first  = false;
													}
													else
													{
														if ($result->oldurl != $duplicate->oldurl)
														{
															$redirects[] = $duplicate->oldurl;
														}

														$urlHelper->deleteUrlById($duplicate->id);

														if (array_key_exists($duplicate->id, $results))
														{
															unset($results[$duplicate->id]);
														}
													}
												}
											}
										}

										// Purge cache for specific none-sef urls
										Sh404sefHelperCache::removeUrlFromCache([$withOldItemId]);

										// Make sense to rebuild it if we have not do it recently
										if (!array_key_exists($result->newurl, $cachedNonSefUrls))
										{
											// Delete a record to force render new SEF URL, it will be created again on $router->build()
											$urlHelper->deleteUrlById($result->id);

											// Do magic
											$router->build($result->newurl);

											$query = $db->getQuery(true)
												->select('*')
												->from($db->qn('#__sh404sef_urls'))
												->where('newurl = ' . $db->q($result->newurl));

											$cachedNonSefUrls[$result->newurl] = $db->setQuery($query)->loadObject();
										}

										$justCreatedRecord = $cachedNonSefUrls[$result->newurl];

										if (!empty($justCreatedRecord))
										{
											if ($justCreatedRecord->rank != $result->rank)
											{
												// Make sure that new generated record gets its own rank back
												$query = $db->getQuery(true)
													->update($db->qn('#__sh404sef_urls'))
													->set('rank = ' . (int) $result->rank)
													->where('newurl = ' . $db->q($result->newurl));

												$db->setQuery($query)->execute();
											}

											if ($justCreatedRecord->oldurl != $result->oldurl)
											{
												$redirects[] = $result->oldurl;
											}

											$letRemoveHomeAlias = Factory::getApplication()
												->triggerEvent('onSh404SefObserverLetRemoveHomeAlias', [$uri]);

											if (in_array(false, $letRemoveHomeAlias, true))
											{
												$urlHelper->replaceUrl($justCreatedRecord->oldurl, sh404SEF_HOMEPAGE_CODE, ['redirect']);
											}
											else
											{
												// Make sure that we do not have redirect for this SEF URL to the home page
												$query = $db->getQuery(true)
													->delete($db->qn('#__sh404sef_aliases'))
													->where('newurl = ' . $db->q(sh404SEF_HOMEPAGE_CODE))
													->where('alias = ' . $db->q($justCreatedRecord->oldurl));

												$db->setQuery($query)->execute();
											}

											foreach ($redirects as $redirect)
											{
												$query = $db->getQuery(true)
													->select('id, newurl')
													->from($db->qn('#__sh404sef_urls'))
													->where('oldurl = ' . $db->q($redirect));

												$duplicatesBySEFURL = $db->setQuery($query)->loadObjectList();

												// Set for all duplicated the same SEF URL as for the main
												if (!empty($duplicatesBySEFURL))
												{
													$query = $db->getQuery(true)
														->set('oldurl = ' . $db->q($justCreatedRecord->oldurl))
														->update($db->qn('#__sh404sef_urls'))
														->where('oldurl = ' . $db->q($redirect));

													$db->setQuery($query)->execute();

													foreach ($duplicatesBySEFURL as $item)
													{
														if (array_key_exists($item->id, $results))
														{
															$results[$item->id]->oldurl = $justCreatedRecord->oldurl;
														}
													}
												}

												if (in_array(false, $letRemoveHomeAlias, true))
												{
													$urlHelper->replaceUrl($redirect, sh404SEF_HOMEPAGE_CODE, ['redirect']);
												}
												else
												{
													$urlHelper->replaceUrl($redirect, $result->newurl, ['redirect']);
												}
											}
										}
									}
									break;
								case 'toDelete':
								case 'toHome':
									$subQuery = $this->getCommonQuery($conditions);

									$query = clone $subQuery;
									$query->select('*')
										->from($db->qn('#__sh404sef_urls'))
										->where('rank = 0')
										->order('oldurl');

									$results = $db->setQuery($query)
										->loadObjectList('id');

									$toPurgeCache = [];

									// Make sure that we still have a record with rank 0 in case we have duplicates
									if (!empty($results))
									{
										$idsToDelete = implode(',', array_keys($results));

										foreach ($results as $result)
										{
											$query = $db->getQuery(true)
												->select('*')
												->from($db->qn('#__sh404sef_urls'))
												->where('oldurl = ' . $db->q($result->oldurl))
												->where('rank > 0')
												->where('id NOT IN (' . $idsToDelete . ')')
												->order('rank ASC');

											$minRank = $db->setQuery($query, 0, 1)->loadObject();

											if (empty($minRank))
											{
												continue;
											}

											$toPurgeCache[] = $minRank->newurl;

											$urlHelper->replaceUrl($result->newurl, $minRank->newurl, ['alias', 'meta']);

											$query = $db->getQuery(true)
												->update($db->qn('#__sh404sef_urls'))
												->set('rank = IF (id = ' . (int) $minRank->id . ', 0, ' . (int) $minRank->rank . ')')
												->set('cpt = IF (id = ' . (int) $minRank->id . ', ' . (int) $result->cpt . ', cpt)')
												->where('id IN (' . implode(',', [(int) $minRank->id, (int) $result->id]) . ')');

											$db->setQuery($query)->execute();
										}
									}

									$query = clone $subQuery;
									$query->select('newurl')
										->from($db->qn('#__sh404sef_urls'));

									$results = (array) $db->setQuery($query)
										->loadColumn();

									if ($params->get('deleted_entities', 'redirect_to_home') == 'redirect_to_home'
										|| $task == 'toHome')
									{
										$query = clone $subQuery;
										$query->select('oldurl')
											->from($db->qn('#__sh404sef_urls'))
											->group('oldurl');

										$sefUrls = (array) $db->setQuery($query)
											->loadColumn();

										foreach ($sefUrls as $sefUrl)
										{
											$urlHelper->setRedirectIfDoesNotExist($sefUrl, sh404SEF_HOMEPAGE_CODE);
										}
									}

									// Cleanup meta table
									$query = clone $subQuery;
									$query->delete($db->qn('#__sh404sef_metas'));

									$db->setQuery($query)->execute();

									// Cleanup alias table
									$query = clone $subQuery;
									$query->delete($db->qn('#__sh404sef_aliases'));

									$db->setQuery($query)->execute();

									// Cleanup URL table
									$query = clone $subQuery;
									$query->delete($db->qn('#__sh404sef_urls'));

									$db->setQuery($query)->execute();

									$results = array_merge($results, $toPurgeCache);

									if (!empty($results))
									{
										// Cleanup cache
										Sh404sefHelperCache::removeUrlFromCache($results);
									}
									break;
								case 'removeHomeAlias':
									foreach ($conditions as $condition)
									{
										if (empty($condition['from'])
											|| empty($condition['to']))
										{
											break;
										}

										$query = $db->getQuery(true)
											->select('a.id as alias_id, b.id as link_id, b.newurl')
											->from($db->qn('#__sh404sef_aliases', 'a'))
											->leftJoin($db->qn('#__sh404sef_urls', 'b') . ' ON b.oldurl = a.alias')
											->where('a.newurl = ' . $db->q(sh404SEF_HOMEPAGE_CODE))
											->where('b.id BETWEEN ' . (int) $condition['from'] . ' AND ' . (int) $condition['to']);

										$items = $db->setQuery($query)->loadObjectList();

										if (empty($items))
										{
											break;
										}

										foreach ($items as $item)
										{
											$letRemoveHomeAlias = Factory::getApplication()
												->triggerEvent('onSh404SefObserverLetRemoveHomeAlias', [new Uri($item->newurl)]);

											if (in_array(false, $letRemoveHomeAlias, true))
											{
												continue;
											}

											Sh404sefHelperCache::removeUrlFromCache([$item->newurl]);

											$urlHelper->deleteAliasById($item->alias_id);
										}
									}
									break;
							}
						}
					}

					$query = $db->getQuery(true)
						->delete($db->qn('#__sh404sef_observer'))
						->where('id = ' . (int) $queue->id);

					$db->setQuery($query)->execute();
				}

				$queues = $db->setQuery($observerQuery, 0, 10)->loadObjectList();
			}
		}

		finally
		{
			$lock->release();
		}

		$this->out('Done');
		$this->out();
	}

	/**
	 * @param   array  $conditions  Conditions
	 *
	 * @return JDatabaseQuery
	 * @since  2.6.0
	 */
	protected function getCommonQuery($conditions)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$or    = [];

		foreach ($conditions as $condition)
		{
			if (!empty($condition['option']))
			{
				$and = [$db->qn('newurl') . ' LIKE ' . $db->q('index.php?option=' . $condition['option'] . '%')];
			}
			else
			{
				$and = [];
			}

			foreach ($condition as $name => $value)
			{
				if ($name == 'option')
				{
					continue;
				}

				$and[] = $db->qn('newurl') . ' LIKE ' . $db->q('%&' . $name . '=' . $value . '%');
			}

			$or[] = '(' . implode(' AND ', $and) . ')';
		}

		return $query->where('(' . implode(' OR ', $or) . ')');
	}
}

CliApplication::getInstance('Sef404ObserverProcessApplicationCli')
	->execute();
