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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Helper\MediaHelper;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Rsbmedia File Controller
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.5
 */
class RsbmediaControllerFile extends BaseController
{
	/**
	 * The folder we are uploading into
	 *
	 * @var   string
	 */
	protected $folder = '';

	/**
	 * Upload one or more files
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function upload()
	{
		$app = Factory::getApplication();

		// Check for request forgeries
		Session::checkToken('request') or jexit(Text::_('JINVALID_TOKEN'));
		$params = ComponentHelper::getParams('com_rsbmedia');

		// Get some data from the request
		$files        = $this->input->files->get('Filedata', '', 'array');
		$return       = $this->input->post->get('return-url', null, 'base64');
		$this->folder = $this->input->get('folder', '', 'path');

		// Set the redirect
		if ($return)
		{
			$this->setRedirect(base64_decode($return) . '&folder=' . $this->folder);
		}

		if (($params->get('upload_maxsize', 0) * 1024 * 1024) != 0)
		{
			if ($_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
				|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('upload_max_filesize')) * 1024 * 1024
				|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('post_max_size')) * 1024 * 1024
				|| (($_SERVER['CONTENT_LENGTH'] > (int) (ini_get('memory_limit')) * 1024 * 1024) && ((int) (ini_get('memory_limit')) != -1)))
			{
				$app->enqueueMessage(Text::_('COM_RSBMEDIA_ERROR_WARNFILETOOLARGE'), 'warning');

				return false;
			}
		}

		// Perform basic checks on file info before attempting anything
		foreach ($files as &$file)
		{
			$file['name'] = JFile::makeSafe(str_replace(' ', '_', $file['name']));

			$file['filepath'] = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_RSBMEDIA_BASE, $this->folder, $file['name'])));

			if ($file['error'] == 1)
			{
				$app->enqueueMessage(Text::_('COM_RSBMEDIA_ERROR_WARNFILETOOLARGE'), 'warning');

				return false;
			}

			if (($params->get('upload_maxsize', 0) * 1024 * 1024) != 0 && $file['size'] > ($params->get('upload_maxsize', 0) * 1024 * 1024))
			{
				$app->enqueueMessage(Text::_('COM_RSBMEDIA_ERROR_WARNFILETOOLARGE'), 'notice');

				return false;
			}

			if (JFile::exists($file['filepath']))
			{
				// A file with this name already exists
				$app->enqueueMessage(Text::_('COM_RSBMEDIA_ERROR_FILE_EXISTS'), 'warning');

				return false;
			}

			if (!isset($file['name']))
			{
				// No filename (after the name was cleaned by JFile::makeSafe)
				$this->setRedirect('index.php', Text::_('COM_RSBMEDIA_INVALID_REQUEST'), 'error');

				return false;
			}
		}

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');
		PluginHelper::importPlugin('content');

		foreach ($files as &$file)
		{
			// The request is valid
			$err = null;

			if (!(new MediaHelper)->canUpload($file, $err))
			{
				// The file can't be uploaded

				return false;
			}

			// Trigger the onContentBeforeSave event.
			$objectFile = new CMSObject($file);
			$result     = $app->triggerEvent('onContentBeforeSave', array('com_rsbmedia.file', &$objectFile, true));

			if (in_array(false, $result, true))
			{
				$errors = $objectFile->getErrors();

				// There are some errors in the plugins
				$app->enqueueMessage(
					Text::plural('COM_RSBMEDIA_ERROR_BEFORE_SAVE', count($errors), implode('<br />', $errors)),
					'warning'
				);

				return false;
			}

			if (!JFile::upload($objectFile->tmp_name, $objectFile->filepath))
			{
				// Error in upload
				$app->enqueueMessage(Text::_('COM_RSBMEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'), 'warning');

				return false;
			}
			else
			{
				// Trigger the onContentAfterSave event.
				$app->triggerEvent('onContentAfterSave', array('com_rsbmedia.file', &$objectFile, true));
				$this->setMessage(Text::sprintf('COM_RSBMEDIA_UPLOAD_COMPLETE', substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE))));
			}
		}

		return true;
	}

	/**
	 * Check that the user is authorized to perform this action
	 *
	 * @param   string  $action  The action to be performed (create or delete)
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function authoriseUser($action)
	{
		if (!Factory::getUser()->authorise('core.' . strtolower($action), 'com_rsbmedia'))
		{
			// User is not authorised
			Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_' . strtoupper($action) . '_NOT_PERMITTED'), 'warning');

			return false;
		}

		return true;
	}

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

		$app = Factory::getApplication();

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

		// Nothing to delete
		if (empty($paths))
		{
			return true;
		}

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');

		PluginHelper::importPlugin('content');

		$ret = true;

		foreach ($paths as $path)
		{
			if ($path !== JFile::makeSafe($path))
			{
				// Filename is not safe
				$filename = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');

				$app->enqueueMessage(
					Text::sprintf(
						'COM_RSBMEDIA_ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME',
						substr($filename, strlen(COM_RSBMEDIA_BASE))
					),
					'warning'
				);

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
						Text::plural('COM_RSBMEDIA_ERROR_BEFORE_DELETE',
							count($errors),
							implode('<br />', $errors)
						),
						'warning'
					);

					continue;
				}

				$ret &= JFile::delete($objectFile->filepath);

				// Trigger the onContentAfterDelete event.
				$dispatcher->trigger('onContentAfterDelete', array('com_rsbmedia.file', &$objectFile));
				$this->setMessage(Text::sprintf('COM_RSBMEDIA_DELETE_COMPLETE', substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE))));
			}
			elseif (is_dir($objectFile->filepath))
			{
				$contents = JFolder::files($objectFile->filepath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

				if (empty($contents))
				{
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger('onContentBeforeDelete', array('com_rsbmedia.folder', &$objectFile));

					if (in_array(false, $result, true))
					{
						$errors = $objectFile->getErrors();

						// There are some errors in the plugins
						$app->enqueueMessage(Text::plural('COM_RSBMEDIA_ERROR_BEFORE_DELETE', count($errors), implode('<br />', $errors)), 'warning');

						continue;
					}

					$ret &= JFolder::delete($objectFile->filepath);

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger('onContentAfterDelete', array('com_rsbmedia.folder', &$objectFile));
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

		return $ret;
	}
}
