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
/**
 * Layouts Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.13.0
 */
class RedshopbModelLayout_List extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 *
	 * @since  1.13.0
	 */
	protected $filterFormName = 'filter_layout_list';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 *
	 * @since  1.13.0
	 */
	protected $limitField = 'layout_item_limit';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 *
	 * @since  1.13.0
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 *
	 * @since  1.13.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'layout_id', 'layoutpathway'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since  1.13.0
	 */
	protected function populateState($ordering = 'layout_id', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   1.13.0
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$overrides     = $this->getOverrides();
		$directionList = $this->getState('list.direction');
		$direction     = !empty($directionList) ? $directionList : 'ASC';

		if (strtolower($direction) == 'asc')
		{
			ksort($overrides, SORT_NATURAL);
		}
		else
		{
			krsort($overrides, SORT_NATURAL);
		}

		$overrides = array_slice($overrides, $limitstart, $limit);

		return $overrides;
	}

	/**
	 * Returns a record count for the query.
	 *
	 * @param   JDatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   1.13.0
	 */
	protected function _getListCount($query)
	{
		return count($this->getOverrides());
	}

	/**
	 * Get Layout overrides
	 *
	 * @return array
	 *
	 * @since 1.13.0
	 */
	protected function getOverrides()
	{
		static $overrides = null;

		if (is_array($overrides))
		{
			return $overrides;
		}

		$layoutPatch         = RedshopbLayoutFile::getInstance('fake')
			->getDefaultIncludePaths();
		$overrides           = array();
		$filterLayoutPathway = $this->getState('filter.layoutpathway');
		$search              = $this->getState('filter.search_layout_list');
		$allowedLayouts      = array();
		$defaultTemplate     = Factory::getApplication()->getTemplate();

		foreach ($layoutPatch as $path)
		{
			if (!JFolder::exists($path))
			{
				continue;
			}

			$path                         = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
			$relativePath                 = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $path);
			$foundLayouts                 = JFolder::files($path, '.php', true, true);
			$exactPath                    = false;
			$standardTemplateLayoutFolder = false;

			if ($path == JPATH_THEMES . DIRECTORY_SEPARATOR . $defaultTemplate . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'layouts')
			{
				$standardTemplateLayoutFolder = true;
			}

			if (!empty($filterLayoutPathway) && $relativePath == $filterLayoutPathway)
			{
				$exactPath = true;
			}

			if (!empty($foundLayouts))
			{
				foreach ($foundLayouts as $foundLayout)
				{
					$relativeFilePath = str_replace(
						array('\\', '/', $path . DIRECTORY_SEPARATOR, '.php', DIRECTORY_SEPARATOR),
						array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, '', '', '.'),
						$foundLayout
					);

					$correctRelativeLayoutsFolderPath = $relativePath;

					// Found overridden some component layout
					if ($standardTemplateLayoutFolder && stripos($relativeFilePath, 'com_') === 0)
					{
						$explodedRelativePath = explode('.', $relativeFilePath);
						$extension            = array_shift($explodedRelativePath);

						if ($extension == 'com_redshopb')
						{
							continue;
						}

						$relativeFilePath                  = implode('.', $explodedRelativePath);
						$correctRelativeLayoutsFolderPath .= DIRECTORY_SEPARATOR . $extension;
					}

					if (!empty($search) && stripos($relativeFilePath, $search) === false)
					{
						continue;
					}

					if ($exactPath)
					{
						$allowedLayouts[$relativeFilePath] = true;
					}

					if (!array_key_exists($relativeFilePath, $overrides))
					{
						$overrides[$relativeFilePath] = array();
					}

					$overrides[$relativeFilePath][] = $correctRelativeLayoutsFolderPath;
				}
			}
		}

		if (!empty($filterLayoutPathway))
		{
			$overrides = array_intersect_key($overrides, $allowedLayouts);
		}

		return $overrides;
	}
}
