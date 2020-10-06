<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

/**
 * Redshop ACL Model
 *
 * @package     Redshop.Component
 * @subpackage  Models.ACL
 * @since       2.0
 *
 */
class RedshopbModelTools extends RedshopbModelAdmin
{
	/**
	 * Resets all redshopb data to default values
	 *
	 * @return  boolean
	 */
	public function redshopbDefaults()
	{
		$app  = Factory::getApplication();
		$db   = Factory::getDBO();
		$lang = Factory::getLanguage();

		// Load language file for content elements
		$lang->load('com_redcore', JPATH_ADMINISTRATOR, null, true, true)
		|| $lang->load('com_redcore', JPATH_ADMINISTRATOR . "/components/com_redcore", null, true, true);

		try
		{
			$db->transactionStart();
			$db->setQuery('SET FOREIGN_KEY_CHECKS = 0')
				->execute();
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models', 'UsersModel');

			/*
			 * Step 1: Delete Joomla user groups connected to redshopb roles
			 */
			$query = $db->getQuery(true);
			$query->select($db->qn('r.joomla_group_id'))
				->from($db->qn('#__redshopb_role', 'r'));
			$db->setQuery($query);
			$roles = $db->loadColumn();

			/** @var UsersModelGroup $userGroupModel */
			$userGroupModel = BaseDatabaseModel::getInstance('Group', 'UsersModel', array('ignore_request' => true, 'dbo' => $db));
			$userGroupModel->delete($roles);

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_GROUPS_SUCCESS'), 'message');

			/*
			 * Step 2: Delete Joomla users connected to redshopb users
			 */
			$query = $db->getQuery(true);
			$query->select($db->qn('u.joomla_user_id'))
				->from($db->qn('#__redshopb_user', 'u'));
			$db->setQuery($query);
			$users = $db->loadColumn();

			/** @var UsersModelUser $userModel */
			$userModel = BaseDatabaseModel::getInstance('User', 'UsersModel', array('ignore_request' => true, 'dbo' => $db));
			$userModel->delete($users);

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_USERS_SUCCESS'), 'message');

			/*
			 * Step 3: Delete Assets from Joomla asset table
			 */
			$query = $db->getQuery(true);
			$query->delete($db->qn('#__assets'))
				->where(
					$db->qn('name') . ' LIKE ' . $db->q('com_redshopb.company.%') . ' OR ' . $db->qn('name')
					. ' LIKE ' . $db->q('com_redshopb.department.%')
				);
			$db->setQuery($query);
			$db->execute();

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_ASSETS_SUCCESS'), 'message');

			/*
			 * Step 4: Execute truncate data on all redshopb tables
			 */
			$sqlfile = JPATH_ADMINISTRATOR . '/components/com_redshopb/sql/install/mysql/uninstall.sql';
			$buffer  = file_get_contents($sqlfile);
			$buffer  = str_replace('DROP TABLE IF EXISTS', 'TRUNCATE TABLE', $buffer);
			$queries = JDatabaseDriver::splitSql($buffer);

			$this->executeQueries($queries, $db);

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_TRUNCATE_SUCCESS'), 'message');

			/*
			 * Step 5: Delete all translation data
			 */
			$translationTables = RTranslationTable::getInstalledTranslationTables(true);

			foreach ($translationTables as $translationTable)
			{
				if ($translationTable->option == 'com_redshopb')
				{
					RTranslationTable::purgeTables($translationTable->id);
				}
			}

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_CONTENT_ELEMENTS_SUCCESS'), 'message');

			/*
			 * Step 6: Execute initial data insert
			 */
			$this->executeSqlFile(JPATH_ADMINISTRATOR . '/components/com_redshopb/sql/install/mysql/data.sql', $db);
			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_INITIALIZE_SUCCESS'), 'message');

			/*
			 * Step 7: Execute additional data insert
			 */
			$this->executeSqlFile(JPATH_ADMINISTRATOR . '/components/com_redshopb/sql/install/mysql/translations/redshopb_country.sql', $db);
			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_ADDITIONAL_DATA_SUCCESS'), 'message');

			/*
			 * Step 8: Assign assets created to the assets table
			 */
			require_once JPATH_ADMINISTRATOR . '/components/com_redshopb/install.php';
			Com_RedshopbInstallerScript::assignAssets();
			Com_RedshopbInstallerScript::installMainTypeCompany();

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_ASSETS_INITIALIZE_SUCCESS'), 'message');

			/*
			 * Step 9: Deletes all logo and product images in redshopb media
			 */
			$folders = array(
				JPATH_ROOT . '/media/com_redshopb/images/originals/categories',
				JPATH_ROOT . '/media/com_redshopb/images/originals/conv_product_attr_value',
				JPATH_ROOT . '/media/com_redshopb/images/originals/manufacturers',
				JPATH_ROOT . '/media/com_redshopb/images/originals/product_attr_value',
				JPATH_ROOT . '/media/com_redshopb/images/originals/products',
				JPATH_ROOT . '/media/com_redshopb/images/originals/tags',
				JPATH_ROOT . '/media/com_redshopb/images/originals/wash_care_spec',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/categories',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/conv_product_attr_value',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/manufacturers',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/product_attr_value',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/products',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/tags',
				JPATH_ROOT . '/media/com_redshopb/images/thumbs/wash_care_spec',
			);

			foreach ($folders as $folder)
			{
				if (JFolder::exists($folder))
				{
					if (!JFolder::delete($folder))
					{
						$app->enqueueMessage(Text::sprintf('LIB_REDCORE_INSTALLER_ERROR_FAILED_TO_DELETE', $folder), 'error');
					}
				}
			}

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_IMAGES_SUCCESS'), 'message');
			$db->setQuery('SET FOREIGN_KEY_CHECKS = 1')
				->execute();
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->setQuery('SET FOREIGN_KEY_CHECKS = 1')
				->execute();
			$db->transactionRollback();

			$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_FAILURE'), 'error');
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$app->enqueueMessage(Text::_('COM_REDSHOPB_TOOLS_RESET_SUCCESS'), 'message');

		return true;
	}

	/**
	 * Executes all queries for given path
	 *
	 * @param   string  $file  Path to sql file
	 * @param   object  $db    Database object
	 *
	 * @return  void
	 */
	public function executeSqlFile($file, &$db)
	{
		if (is_file($file))
		{
			$buffer = file_get_contents($file);

			// Create an array of queries from the sql file
			$queries = JDatabaseDriver::splitSql($buffer);

			$this->executeQueries($queries, $db);
		}
	}

	/**
	 * Resets all redshopb data to default values
	 *
	 * @param   array   $queries  Split queries to execute
	 * @param   object  $db       Database object
	 *
	 * @return  void
	 */
	public function executeQueries($queries, &$db)
	{
		if (!empty($queries))
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);

					if (!$db->execute())
					{
						Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), Log::WARNING, 'jerror');
					}
				}
			}
		}
	}
}
