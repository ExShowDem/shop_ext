<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.

 */
error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserHelper;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

/**
 * Switch Locking system
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class SwitchLockingSystemApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		try
		{
			JLoader::import('joomla.filesystem.file');
			JLoader::import('joomla.filesystem.folder');
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
			$app = Factory::getApplication('site');
			$app->input->set('option', 'com_redshopb');

			JLoader::import('redshopb.library');
			RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

			$this->out('Select all sync rows with flags set');
			$db        = Factory::getDbo();
			$syncQuery = $db->getQuery(true)
				->select('s.*')
				->from($db->qn('#__redshopb_sync', 's'))
				->where($db->qn('s.metadata') . ' != ' . $db->q(''));
			$limit     = 5000;
			$start     = 0;
			$date      = date('Y-m-d H:i:s');

			$this->out('Initialize sync folder classes from rb_sync');
			$referenceTable = array();
			$tableFolder    = JFolder::files(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables', '\.php$');

			foreach ($tableFolder as $tableName)
			{
				$className  = str_replace('.php', '', $tableName);
				$table      = Table::getInstance($className, 'RedshopbTable');
				$references = $table->get('wsSyncMapPK');

				if (empty($references))
				{
					continue;
				}

				foreach ($references as $reference)
				{
					foreach ($reference as $referenceName)
					{
						$referenceTable[$referenceName] = $table->get('_tbl');
					}
				}
			}

			$this->out('We found ' . count($referenceTable) . ' sync reference tables');
			print_r($referenceTable);

			$this->out('Loading existing locks');
			$query         = $db->getQuery(true)
				->select('tl.*')
				->from($db->qn('#__redshopb_table_lock', 'tl'));
			$results       = $db->setQuery($query)->loadObjectList();
			$existingLocks = array();

			foreach ($results as $result)
			{
				$existingLocks[$result->table_name][$result->table_id][$result->column_name] = 1;
			}

			$this->out('Found ' . count($results) . ' existing locks');
			$this->out('Searching for ERP user');
			$userId = UserHelper::getUserId('erp');
			$this->out('ERP user id ' . $userId);

			while (true)
			{
				$this->out('Loading ' . $limit . ' rows starting from ' . $start);
				$results = $db->setQuery($syncQuery, $start, $limit)
					->loadObjectList();
				$start  += $limit;
				$this->out('Loaded ' . count($results) . ' rows');

				if ($results)
				{
					foreach ($results as $result)
					{
						$metaData = $result->metadata;

						if ($metaData)
						{
							$metaData = unserialize($metaData);

							foreach ($metaData['WSFlags'] as $name => $flag)
							{
								if ($flag && !isset($existingLocks[$referenceTable[$result->reference]][$result->local_id][$name]))
								{
									// We will not set null locks in this script because it is probably part of the bug
									if ($metaData['WSProperties'][$name] !== null)
									{
										// Some fields do not need to be locked but were locked from old system
										if (in_array($name, array('isNew', 'hits', 'alias')))
										{
											continue;
										}

										if ($referenceTable[$result->reference])
										{
											$query = $db->getQuery(true)
												->insert($db->qn('#__redshopb_table_lock'))
												->set($db->qn('table_name') . ' = ' . $db->q($referenceTable[$result->reference]))
												->set($db->qn('table_id') . ' = ' . $db->q($result->local_id))
												->set($db->qn('column_name') . ' = ' . $db->q($name))
												->set($db->qn('locked_date') . ' = ' . $db->q($date))
												->set($db->qn('locked_method') . ' = ' . $db->q('Webservice'));

											if ($userId)
											{
												$query->set($db->qn('locked_by') . ' = ' . $db->q($userId));
											}

											try
											{
												$db->setQuery($query)->execute();
												$existingLocks[$referenceTable[$result->reference]][$result->local_id][$name] = 1;
											}
											catch (Exception $e)
											{
												$this->out($e->getMessage());

												break;
											}
										}
										else
										{
											$this->out(
												'We didnt find reference table for: ' . $result->reference
												. ' remote_key: ' . $result->remote_key . ' local_id: ' . $result->local_id
											);
										}
									}
								}
							}
						}
					}

					continue;
				}

				break;
			}
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('SwitchLockingSystemApplicationCli');
$instance->execute();
