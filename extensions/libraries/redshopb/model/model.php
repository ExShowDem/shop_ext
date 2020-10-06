<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Redshopb base Model
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Base
 * @since       1.0
 */
class RedshopbModel extends RModel
{
	/**
	 * Get a frontend Model instance
	 *
	 * @param   string  $name    Model name
	 * @param   array   $config  An optional array of configuration
	 * @param   string  $option  Component name, use for call model from modules
	 *
	 * @return  RModel           Model instance
	 */
	public static function getFrontInstance($name, array $config = array(), $option = 'com_redshopb')
	{
		return self::getAutoInstance($name, 0, $config, $option);
	}

	/**
	 * Get a backend model instance
	 *
	 * @param   string  $name    Model name
	 * @param   array   $config  An optional array of configuration
	 * @param   string  $option  Component name, use for call model from modules
	 *
	 * @return  RModel           Model instance
	 */
	public static function getAdminInstance($name, array $config = array(), $option = 'com_redshopb')
	{
		return self::getAutoInstance($name, 1, $config, $option);
	}

	/**
	 * Get a model instance.
	 *
	 * @param   string  $name    Model name
	 * @param   mixed   $client  Client. null = auto, 1 = admin, 0 = frontend
	 * @param   array   $config  An optional array of configuration
	 * @param   string  $option  Component name, use for call model from modules
	 *
	 * @return  RModel           The model
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function getAutoInstance($name, $client = null, array $config = array(), $option = 'auto')
	{
		if ($option === 'auto')
		{
			$option = Factory::getApplication()->input->getString('option', '');

			// Add com_ to the element name if not exist
			$option = (strpos($option, 'com_') === 0 ? '' : 'com_') . $option;

			if ($option == 'com_installer')
			{
				$installer = Installer::getInstance();
				$option    = $installer->manifestClass->getElement($installer);
			}
		}

		$componentName = ucfirst(strtolower(substr($option, 4)));
		$prefix        = $componentName . 'Model';

		if (is_null($client))
		{
			$client = (int) Factory::getApplication()->isClient('administrator');
		}

		// Admin
		if ($client === 1)
		{
			self::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/models', $prefix);
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/tables');
		}

		// Site
		elseif ($client === 0)
		{
			self::addIncludePath(JPATH_SITE . '/components/' . $option . '/models', $prefix);
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/tables');
		}
		else
		{
			throw new InvalidArgumentException(
				sprintf('Cannot instanciate the model %s in component %s. Invalid client %s.', $name, $option, $client)
			);
		}

		$model = self::getInstance($name, $prefix, $config);

		/*
		@NOTE
		JModel - Interface
		BaseDatabaseModel - Abstract class
		*/
		if (!$model instanceof JModel && !$model instanceof BaseDatabaseModel)
		{
			throw new InvalidArgumentException(
				sprintf('Cannot instanciate the model %s in component %s. Invalid client %s.', $name, $option, $client)
			);
		}

		return $model;
	}

	/**
	 * Get's a model from
	 *
	 * @param   string  $modelName  The model name
	 * @param   array   $config     An optional array of configuration
	 *
	 * @return RModel               In the form array (client => 0, name => 1)
	 *
	 * @throws  Exception
	 *
	 * @since   2.0
	 */
	public static function getInstanceFromString($modelName, $config = array())
	{
		// Only the name has been returned try to load an autoinstance
		if (false === strpos($modelName, ':'))
		{
			return static::getAutoInstance($modelName, null, $config, 'com_redshopb');
		}

		$parts = explode(':', $modelName);

		if (count($parts) >= 2)
		{
			$modelName = $parts[1];

			if ('admin' == $parts[0])
			{
				return static::getAdminInstance($modelName, $config, 'com_redshopb');
			}

			if ('site' === $parts[0])
			{
				return static::getFrontInstance($modelName, $config, 'com_redshopb');
			}
		}

		throw new Exception(__METHOD__ . ': Unable to get model from string: ' . $modelName);
	}
}
