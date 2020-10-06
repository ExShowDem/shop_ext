<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Install
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Log\Log;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redCORE/extensions',
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redcore'
	);

	$redcoreInstaller = JPath::find($searchPaths, 'install.php');

	if ($redcoreInstaller)
	{
		require_once $redcoreInstaller;
	}
}

/**
 * Custom installation of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Install
 * @since       1.0
 */
class Com_RedshopbInstallerScript extends Com_RedcoreInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @throws RuntimeException
	 *
	 * @return  boolean
	 */
	public function preflight($type, $parent)
	{
		if (version_compare(JVERSION, '3.8', '<'))
		{
			throw new RuntimeException("This version of Joomla (" . JVERSION . ") isn't supported by this version of Vanir");
		}

		if (method_exists('Com_RedcoreInstallerScript', 'preflight') && !parent::preflight($type, $parent))
		{
			return false;
		}

		// Modify the "enabled" status on plugins to make sure it doesn't overwrite the database setting.
		if ($type == 'update')
		{
			if (isset($this->manifest->plugins->plugin))
			{
				$db = Factory::getDbo();

				foreach ($this->manifest->plugins->plugin AS $plugin)
				{
					$query = $db->getQuery(true);
					$query->select('enabled')
						->from('#__extensions')
						->where($db->qn('element') . ' = ' . $db->q((string) $plugin['name']))
						->where($db->qn('folder') . ' = ' . $db->q((string) $plugin['group']));

					$result             = $db->setQuery($query)->loadResult();
					$plugin['disabled'] = $result ? 0 : 1;
				}
			}
		}

		return true;
	}

	/**
	 * Recursively copy the contents of some folder into another one
	 *
	 * @param   string  $folder  Origin folder
	 * @param   string  $dest    Destination folder (will be created if it doesn't exist)
	 *
	 * @return  void
	 */
	protected function copyRecursive($folder, $dest)
	{
		if (!file_exists($dest))
		{
			mkdir($dest);
		}

		$files = glob($folder . '/*');

		foreach ($files as $file)
		{
			$destfile = $dest . str_replace($folder, '', $file);

			if (is_dir($file))
			{
				$this->copyRecursive($file, $destfile);
			}
			else
			{
				copy($file, $destfile);
			}
		}
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 */
	public function postflight($type, $parent)
	{
		$db = Factory::getDbo();

		// Move CLI scripts
		if (!file_exists(JPATH_SITE . '/cli/com_redshopb'))
		{
			mkdir(JPATH_SITE . '/cli/com_redshopb');
		}

		$files = glob(JPATH_SITE . '/components/com_redshopb/cli/*.*');

		foreach ($files as $file)
		{
			$fileToGo = str_replace(JPATH_SITE . '/components/com_redshopb/cli', JPATH_SITE . '/cli/com_redshopb', $file);

			copy($file, $fileToGo);
			chmod($fileToGo, 0755);
		}

		// Updates to the asset table must occur only during install, never update
		if ($type == 'install' || $type == 'discover_install')
		{
			self::assignAssets();
		}

		if (!parent::postflight($type, $parent))
		{
			return false;
		}

		$path = JPATH_ADMINISTRATOR . '/components/com_redshopb';

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$manifest = $parent->get('manifest');
		$dbDriver = strtolower($db->name);

		if ($dbDriver == 'mysqli')
		{
			$dbDriver = 'mysql';
		}

		if ($type == 'install' || $type == 'discover_install')
		{
			// Insert main type company by default
			self::installMainTypeCompany();

			$nodes = $manifest->translationsSql->file;

			if ($nodes)
			{
				foreach ($nodes as $node)
				{
					$fCharset   = (strtolower($node->attributes()->charset) == 'utf8') ? 'utf8' : '';
					$fDriver    = strtolower($node->attributes()->driver);
					$parts      = explode('/', $node);
					$fNameParts = $parts[count($parts) - 1];
					$fNameArray = explode('.', $fNameParts);
					$table      = $db->getPrefix() . $fNameArray[0] . '_rctranslations';

					$query = $db->getQuery(true);
					$query->select('COUNT(*)')
						->from('information_schema.tables')
						->where('table_schema = ' . $db->q(Factory::getConfig()->get('db')))
						->where('table_name = ' . $db->q($table));

					// Checking if translations can be imported (table exists)
					if ($db->setQuery($query)->loadResult())
					{
						if ($fDriver == 'mysqli')
						{
							$fDriver = 'mysql';
						}

						if ($fCharset == 'utf8' && $fDriver == $dbDriver)
						{
							$result = $this->executeFileQueries($path . '/' . (string) $node);

							if ($result === false)
							{
								return false;
							}

							break;
						}
					}
				}
			}
		}
		elseif ($type == 'update')
		{
			$schemaPathes = $manifest->update->schemas->schemapath;

			if ($schemaPathes)
			{
				foreach ($schemaPathes as $schemaPath)
				{
					$fType = strtolower($schemaPath->attributes()->type);

					if ($fType == 'mysqli')
					{
						$fType = 'mysql';
					}

					if ($fType == $dbDriver)
					{
						$files = str_replace('.sql', '', JFolder::files($path . '/' . $schemaPath . '/translations', '\.sql$'));
						usort($files, 'version_compare');

						if (!count($files))
						{
							return false;
						}

						if (!empty($this->oldVersion))
						{
							foreach ($files as $file)
							{
								if (version_compare($file, $this->oldVersion) > 0)
								{
									$result = $this->executeFileQueries($path . '/' . $schemaPath . '/translations/' . $file . '.sql');

									if ($result === false)
									{
										return false;
									}
								}
							}
						}

						break;
					}
				}
			}
		}

		// Initializes or tries to initialize ACL roles
		RedshopbHelperACL::initializeACL();

		return true;
	}

	/**
	 * Method to uninstall the component
	 *
	 * @param   InstallerAdapter  $parent  class calling this method
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function uninstall($parent)
	{
		JLoader::import('redshopb.library');

		// Selects any main company and deletes it (cascade deletes any children and below)
		$companiesModel = RedshopbModel::getFrontInstance('Companies');
		$companiesModel->getState();
		$companiesModel->setState('filter.parent_id', 1);
		$companies = $companiesModel->getItems();

		if ($companies && count($companies))
		{
			$companyTable = RedshopbTable::getAdminInstance('Company');

			foreach ($companies as $company)
			{
				$companyTable->load($company->id);
				$companyTable->delete($company->id, true, false);
			}
		}

		parent::uninstall($parent);
	}

	/**
	 * Insert company type main in db
	 *
	 * @return boolean
	 */
	public static function installMainTypeCompany()
	{
		JLoader::import('redshopb.library');

		$db = Factory::getDbo();

		$companyTable = RedshopbTable::getAdminInstance('Company', array(), 'com_redshopb');
		$companyData  = array(
			'name' => 'Main Company',
			'type' => 'main',
			'level' => 1,
			'path' => 'main',
			'alias' => 'main',
			'state' => 1,
			'parent_id' => 1,
			'customer_number' => 'main'
		);

		if (!$companyTable->save($companyData))
		{
			Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR'), Log::WARNING, 'jerror');

			return false;
		}

		$companyId    = $companyTable->id;
		$addressTable = RedshopbTable::getAdminInstance('Address', array(), 'com_redshopb');
		$addressData  = array(
			'customer_type' => 'company',
			'customer_id' => $companyId,
			'type' => 2
		);

		if (!$addressTable->save($addressData))
		{
			Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR'), Log::WARNING, 'jerror');

			return false;
		}

		$query = $db->getQuery(true);
		$query->update($db->qn('#__redshopb_company'))
			->set($db->qn('address_id') . ' = ' . (int) $addressTable->id)
			->where($db->qn('id') . ' = ' . $companyId);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Assign company and department assets to the asset table
	 *
	 * @return void
	 */
	public static function assignAssets()
	{
		$db = Factory::getDbo();

		// Checks if the component was installed in the assets table
		$query = $db->getQuery(true);
		$query->select(array('id'))
			->from($db->qn('#__assets'))
			->where($db->qn('name') . ' = ' . $db->quote('com_redshopb'));
		$db->setQuery($query);

		$rootAssetID = $db->loadResult();

		if (!$rootAssetID)
		{
			// Register the component container just under root in the assets table.
			$asset            = Table::getInstance('Asset');
			$asset->name      = 'com_redshopb';
			$asset->parent_id = 1;
			$asset->rules     = '{}';
			$asset->title     = 'com_redshopb';
			$asset->setLocation(1, 'last-child');
			$asset->store();

			// Captures the newly created asset ID
			$rootAssetID = $asset->id;
		}

		// Register the ROOT company just under the component in the assets table.
		$asset            = Table::getInstance('Asset');
		$asset->name      = 'com_redshopb.company.1';
		$asset->parent_id = $rootAssetID;
		$asset->rules     = '{}';
		$asset->title     = 'ROOT';
		$asset->setLocation($rootAssetID, 'last-child');
		$asset->store();

		// Update Company table with the asset ID
		$query = $db->getQuery(true);
		$query->update('#__redshopb_company')
			->set($db->qn('asset_id') . ' = ' . (int) $asset->id)
			->where($db->qn('id') . ' = 1')
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);
		$db->execute();

		// Register the Main Company
		$rootCompanyAssetID = $asset->id;
		$asset              = Table::getInstance('Asset');
		$asset->name        = 'com_redshopb.company.2';
		$asset->parent_id   = $rootCompanyAssetID;
		$asset->rules       = '{}';
		$asset->title       = 'MAIN COMPANY';
		$asset->setLocation($rootCompanyAssetID, 'last-child');
		$asset->store();

		// Update company table with the asset ID
		$query = $db->getQuery(true);
		$query->update($db->qn('#__redshopb_company'))
			->set($db->qn('asset_id') . ' = ' . (int) $asset->id)
			->where($db->qn('id') . ' = 2')
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);
		$db->execute();

		// Register the ROOT department
		$rootCompanyAssetID = $asset->id;
		$asset              = Table::getInstance('Asset');
		$asset->name        = 'com_redshopb.department.1';
		$asset->parent_id   = $rootCompanyAssetID;
		$asset->rules       = '{}';
		$asset->title       = 'ROOT';
		$asset->setLocation($rootCompanyAssetID, 'last-child');
		$asset->store();

		// Update Department table with the asset ID
		$query = $db->getQuery(true);
		$query->update($db->qn('#__redshopb_department'))
			->set($db->qn('asset_id') . ' = ' . (int) $asset->id)
			->where($db->qn('id') . ' = 1')
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * execute File Queries
	 *
	 * @param   string  $path  Path sql translation file
	 *
	 * @return boolean
	 */
	public function executeFileQueries($path)
	{
		if (JFile::exists($path))
		{
			$queryString = file_get_contents($path);

			// Graceful exit and rollback if read not successful
			if ($queryString === false)
			{
				Log::add(Text::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'), Log::WARNING, 'jerror');

				return false;
			}

			$queries = JDatabaseDriver::splitSql($queryString);

			if (count($queries) == 0)
			{
				// No queries to process
				return 0;
			}

			$db = Factory::getDbo();

			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						Log::add(
							Text::sprintf(
								'JLIB_INSTALLER_ERROR_SQL_ERROR',
								$e->getMessage() . " <br />SQL = <pre> " . $db->getQuery()->__toString() . '</pre>'
							),
							Log::WARNING,
							'jerror'
						);

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Post process and update full cron file
	 *
	 * @return true
	 */
	public function postProcessCron()
	{
		jimport('redshopb.table.webservices');
		jimport('redshopb.table.table');
		jimport('redshopb.table.nested');

		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->update(array($db->qn('#__redshopb_cron', 'c'), $db->qn('#__redshopb_cron', 'cp')))
			->set($db->qn('c.parent_id') . ' = ' . $db->qn('cp.id'))
			->where($db->qn('c.parent_alias') . ' = ' . $db->qn('cp.alias'))
			->where($db->qn('c.parent_id') . ' = 0')
			->where($db->qn('c.parent_alias') . ' <> ' . $db->q(''));

		$db->setQuery($query);
		$db->execute();

		// @var RedshopbTableCron $cronTable

		$cronTable = RedshopbTable::getAdminInstance('Cron', array(), 'com_redshopb');
		$cronTable->rebuild();

		return true;
	}
}
