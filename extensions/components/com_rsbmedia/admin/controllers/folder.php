<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Client\ClientHelper;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Folder Rsbmedia Controller
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.5
 */
class RsbmediaControllerFolder extends BaseController
{
	/**
	 * Deletes paths from the current path
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function delete()
	{
		Session::checkToken('request') or jexit(Text::_('JINVALID_TOKEN'));

		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Get some data from the request
		$tmpl   = $this->input->get('tmpl');
		$paths  = $this->input->get('rm', array(), 'array');
		$folder = $this->input->get('folder', '', 'path');

		$redirect = 'index.php?option=com_rsbmedia&folder=' . $folder;

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}

		$this->setRedirect($redirect);

		// Just return if there's nothing to do
		if (empty($paths))
		{
			return true;
		}

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');

		$ret = true;

		PluginHelper::importPlugin('content');

		if (count($paths))
		{
			foreach ($paths as $path)
			{
				if ($path !== JFile::makeSafe($path))
				{
					$dirname = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
					$app->enqueueMessage(Text::sprintf('COM_RSBMEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_WARNDIRNAME', substr($dirname, strlen(COM_RSBMEDIA_BASE))), 'warning');

					continue;
				}

				$fullPath   = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_RSBMEDIA_BASE, $folder, $path)));
				$objectFile = new CMSObject(array('filepath' => $fullPath));

				if (is_file($objectFile->filepath))
				{
					// Trigger the onContentBeforeDelete event.
					$result = $app->triggerEvent('onContentBeforeDelete', array('com_rsbmedia.file', &$objectFile));

					if (in_array(false, $result, true))
					{
						$errors = $objectFile->getErrors();

						// There are some errors in the plugins
						$app->enqueueMessage(
							Text::plural(
								'COM_RSBMEDIA_ERROR_BEFORE_DELETE',
								count($errors),
								implode('<br />', $errors)
							),
							'warning'
						);

						continue;
					}

					$ret &= JFile::delete($objectFile->filepath);

					// Trigger the onContentAfterDelete event.
					$app->triggerEvent('onContentAfterDelete', array('com_rsbmedia.file', &$objectFile));
					$this->setMessage(Text::sprintf('COM_RSBMEDIA_DELETE_COMPLETE', substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE))));
				}
				elseif (is_dir($objectFile->filepath))
				{
					$contents = JFolder::files($objectFile->filepath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

					if (empty($contents))
					{
						// Trigger the onContentBeforeDelete event.
						$result = $app->triggerEvent('onContentBeforeDelete', array('com_rsbmedia.folder', &$objectFile));

						if (in_array(false, $result, true))
						{
							$errors = $objectFile->getErrors();

							// There are some errors in the plugins
							$app->enqueueMessage(
								Text::plural(
									'COM_RSBMEDIA_ERROR_BEFORE_DELETE',
									count($errors),
									implode('<br />', $errors)
								),
								'warning'
							);

							continue;
						}

						$ret &= !JFolder::delete($objectFile->filepath);

						// Trigger the onContentAfterDelete event.
						$app->triggerEvent('onContentAfterDelete', array('com_rsbmedia.folder', &$objectFile));
						$this->setMessage(Text::sprintf('COM_RSBMEDIA_DELETE_COMPLETE', substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE))));
					}
					else
					{
						// This makes no sense...
						$app->enqueueMessage(
							Text::sprintf(
								'COM_RSBMEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY',
								substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE))
							),
							'warning'
						);
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Create a folder
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function create()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$user = Factory::getUser();
		$app  = Factory::getApplication();

		$folder      = $this->input->get('foldername', '');
		$folderCheck = (string) $this->input->get('foldername', null, 'raw');
		$parent      = $this->input->get('folderbase', '', 'path');

		$this->setRedirect('index.php?option=com_rsbmedia&folder=' . $parent . '&tmpl=' . $this->input->get('tmpl', 'index'));

		if (strlen($folder) > 0)
		{
			// Set FTP credentials, if given
			ClientHelper::setCredentialsFromRequest('ftp');

			$this->input->set('folder', $parent);

			if (($folderCheck !== null) && ($folder !== $folderCheck))
			{
				$this->setMessage(Text::_('COM_RSBMEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'));

				return false;
			}

			$path = JPath::clean(COM_RSBMEDIA_BASE . '/' . $parent . '/' . $folder);

			if (!is_dir($path) && !is_file($path))
			{
				// Trigger the onContentBeforeSave event.
				$objectFile = new CMSObject(array('filepath' => $path));
				PluginHelper::importPlugin('content');
				$result = $app->triggerEvent('onContentBeforeSave', array('com_rsbmedia.folder', &$objectFile, true));

				if (in_array(false, $result, true))
				{
					$errors = $objectFile->getErrors();

					// There are some errors in the plugins
					$app->enqueueMessage(
						Text::plural('COM_RSBMEDIA_ERROR_BEFORE_SAVE',
							count($errors),
							implode('<br />', $errors)
						),
						'warning'
					);

					return false;
				}

				if (JFolder::create($objectFile->filepath))
				{
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					JFile::write($objectFile->filepath . "/index.html", $data);

					// Trigger the onContentAfterSave event.
					$app->triggerEvent('onContentAfterSave', array('com_rsbmedia.folder', &$objectFile, true));
					$this->setMessage(Text::sprintf('COM_RSBMEDIA_CREATE_COMPLETE', substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE))));
				}
			}

			$this->input->set('folder', ($parent) ? $parent . '/' . $folder : $folder);
		}
		else
		{
			// File name is of zero length (null).
			$app->enqueueMessage(Text::_('COM_RSBMEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'), 'warning');

			return false;
		}

		return true;
	}
}
