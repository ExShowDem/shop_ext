<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since       1.6.45
 */
class RedshopbLayoutFile extends RLayoutFile
{
	/**
	 * @var array
	 */
	protected static $layouts = array();

	/**
	 * @var array
	 */
	private static $includeStaticPaths = array();

	/**
	 * Method to instantiate the file-based layout.
	 *
	 * @param   string           $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   string           $basePath  Base path to use when loading layout files
	 * @param   Registry|array   $options   Optional custom options to load. Registry or array format [since 3.2]
	 *
	 * @return  RedshopbLayoutFile
	 */
	public static function getInstance($layoutId, $basePath = null, $options = null)
	{
		$key = md5(serialize(func_get_args()));

		if (!array_key_exists($key, self::$layouts))
		{
			self::$layouts[$key] = new static($layoutId, $basePath, $options);
		}

		return self::$layouts[$key];
	}

	/**
	 * Add one path to include in layout search. Proxy of addIncludePaths()
	 *
	 * @param   mixed  $path  The path to search for layouts
	 *
	 * @return  array paths
	 */
	public static function addIncludePathStatic($path)
	{
		// Convert the passed path(s) to add to an array.
		settype($path, 'array');

		// If we have new paths to add, do so.
		if (!empty($path))
		{
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				if (!in_array($dir, self::$includeStaticPaths))
				{
					array_unshift(self::$includeStaticPaths, $dir);
				}
			}
		}

		return self::$includeStaticPaths;
	}

	/**
	 * Method to finds the full real file path, checking possible overrides
	 * Parent method is protected, so current method change it for use not only in the class
	 *
	 * @return  string  The full path to the layout file
	 */
	public function getPath()
	{
		return parent::getPath();
	}

	/**
	 * Get the default array of include paths
	 *
	 * @return  array
	 */
	public function getDefaultIncludePaths()
	{
		$paths     = parent::getDefaultIncludePaths();
		$component = $this->options->get('component', null);

		// If component not found or component is not com_redshopb then add vanir default path
		if (empty($component) || $component != 'com_redshopb')
		{
			$newPaths = array();
			$count    = empty($component) ? 0 : 2;

			// (1 - highest priority) Received a custom high priority path
			if (!is_null($this->basePath))
			{
				$newPaths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
				$count++;
			}

			// (2) Component template overrides path
			$newPaths[] = JPATH_THEMES . '/' . Factory::getApplication()->getTemplate() . '/html/layouts/com_redshopb';

			// (3) Component path
			if ($this->options->get('client') == 0)
			{
				$newPaths[] = JPATH_SITE . '/components/com_redshopb/layouts';
			}
			else
			{
				$newPaths[] = JPATH_ADMINISTRATOR . '/components/com_redshopb/layouts';
			}

			array_splice($paths, 0, $count, $newPaths);
		}

		// Our library path comes after (2 - low priority) RedCORE layouts
		array_splice($paths, count($paths) - 2, 0, JPATH_LIBRARIES . '/redshopb/layouts');

		// More highest priorities from static addIncludePathStatic
		if (!empty(self::$includeStaticPaths))
		{
			foreach (self::$includeStaticPaths as $includePath)
			{
				// More priority still template and $this->basePath directory
				array_splice($paths, !is_null($this->basePath) ? 2 : 1, 0, rtrim($includePath, DIRECTORY_SEPARATOR));
			}
		}

		return $paths;
	}

	/**
	 * Refresh the list of include paths
	 *
	 * @return  void
	 */
	protected function refreshIncludePaths()
	{
		parent::refreshIncludePaths();
	}
}
