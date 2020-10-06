<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
/**
 * Rsbmedia Component Manager Model
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.5
 */
class RsbmediaModelManager extends BaseDatabaseModel
{
	/**
	 * Get state
	 *
	 * @param   string  $property  Property to get its state
	 * @param   string  $default   The default state
	 *
	 * @return object
	 */
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input = Factory::getApplication()->input;

			$folder = $input->get('folder', '', 'path');
			$this->setState('folder', $folder);

			$fieldid = $input->get('fieldid', '');
			$this->setState('field.id', $fieldid);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}

		return parent::getState($property, $default);
	}

	/**
	 * Image Manager Popup
	 *
	 * @param   string  $base  The image directory to display
	 *
	 * @return string Folder list dropdown
	 *
	 * @since 1.5
	 */
	public function getFolderList($base = null)
	{
		// Get some paths from the request
		if (empty($base))
		{
			$base = COM_RSBMEDIA_BASE;
		}

		if ($this->getState('folder'))
		{
			$base .= '/' . $this->getState('folder');
		}

		$base               = str_replace(DIRECTORY_SEPARATOR, '/', $base);
		$comRsbmediaBaseUni = str_replace(DIRECTORY_SEPARATOR, '/', COM_RSBMEDIA_BASE);

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', true, true);

		$document = Factory::getDocument();
		$document->setTitle(Text::_('COM_RSBMEDIA_INSERT_IMAGE'));

		// Build the array of select options for the folder list
		$options[] = HTMLHelper::_('select.option', "", "/");

		foreach ($folders as $folder)
		{
			$folder    = str_replace($comRsbmediaBaseUni, "", str_replace(DIRECTORY_SEPARATOR, '/', $folder));
			$value     = substr($folder, 1);
			$text      = str_replace(DIRECTORY_SEPARATOR, "/", $folder);
			$options[] = HTMLHelper::_('select.option', $value, $text);
		}

		// Sort the folder list array
		if (is_array($options))
		{
			sort($options);
		}

		// Get asset and author id (use integer filter)
		$input = Factory::getApplication()->input;
		$asset = $input->get('asset', 0, 'integer');

		// For new items the asset is a string. Access always checks type first
		// so both string and integer are supported.
		if ($asset == 0)
		{
			$asset = $input->get('asset', 0, 'string');
		}

		$author = $input->get('author', 0, 'integer');

		// Create the drop-down folder select list
		$list = HTMLHelper::_(
			'select.genericlist',
			$options,
			'folderlist',
			'class="inputbox" size="1" onchange="ImageManager.setFolder(this.options[this.selectedIndex].value, \''
			. $asset . '\', '
			. $author . ')" ',
			'value',
			'text',
			$base
		);

		return $list;
	}

	/**
	 * Get Folder Tree
	 *
	 * @param   string  $base  The base path
	 *
	 * @return array
	 */
	public function getFolderTree($base = null)
	{
		// Get some paths from the request
		if (empty($base))
		{
			$base = COM_RSBMEDIA_BASE;
		}

		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_RSBMEDIA_BASE . '/');

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', true, true);

		$tree = array();

		foreach ($folders as $folder)
		{
			$folder   = str_replace(DIRECTORY_SEPARATOR, '/', $folder);
			$name     = substr($folder, strrpos($folder, '/') + 1);
			$relative = str_replace($mediaBase, '', $folder);
			$absolute = $folder;
			$path     = explode('/', $relative);
			$node     = (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);

			$tmp = &$tree;

			for ($i = 0, $n = count($path); $i < $n; $i++)
			{
				if (!isset($tmp['children']))
				{
					$tmp['children'] = array();
				}

				if ($i == $n - 1)
				{
					// We need to place the node
					$tmp['children'][$relative] = array('data' => $node, 'children' => array());

					break;
				}

				if (array_key_exists($key = implode('/', array_slice($path, 0, $i + 1)), $tmp['children']))
				{
					$tmp = &$tmp['children'][$key];
				}
			}
		}

		$tree['data'] = (object) array('name' => Text::_('COM_RSBMEDIA_MEDIA'), 'relative' => '', 'absolute' => $base);

		return $tree;
	}
}
