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
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Helper\MediaHelper;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * File Rsbmedia Controller
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.6
 */
class RsbmediaControllerFile extends BaseController
{
	/**
	 * Upload a file
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function upload()
	{
		$params = ComponentHelper::getParams('com_rsbmedia');

		// Check for request forgeries
		if (!Session::checkToken('request'))
		{
			$response = array(
				'status' => '0',
				'error' => Text::_('JINVALID_TOKEN')
			);
			echo json_encode($response);

			return;
		}

		// Get the user
		$user  = Factory::getUser();
		Log::addLogger(array('text_file' => 'upload.error.php'), Log::ALL, array('upload'));

		// Get some data from the request
		$file   = $this->input->files->get('Filedata', '', 'array');
		$folder = $this->input->get('folder', '', 'path');

		if ($_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('upload_max_filesize')) * 1024 * 1024
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('post_max_size')) * 1024 * 1024
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('memory_limit')) * 1024 * 1024)
		{
			$response = array(
				'status' => '0',
				'error' => Text::_('COM_RSBMEDIA_ERROR_WARNFILETOOLARGE')
			);
			echo json_encode($response);

			return;
		}

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');

		// Make the filename safe
		$file['name'] = JFile::makeSafe($file['name']);

		if (isset($file['name']))
		{
			// The request is valid
			$err = null;

			$filepath = JPath::clean(COM_RSBMEDIA_BASE . '/' . $folder . '/' . strtolower($file['name']));

			if (!(new MediaHelper)->canUpload($file, $err))
			{
				Log::add('Invalid: ' . $filepath . ': ' . $err, Log::INFO, 'upload');

				$response = array(
					'status' => '0',
					'error' => Text::_($err)
				);

				echo json_encode($response);

				return;
			}

			// Trigger the onContentBeforeSave event.
			PluginHelper::importPlugin('content');

			$objectFile = new CMSObject($file);
			$objectFile->filepath = $filepath;

			$app = Factory::getApplication();

			$result = $app->triggerEvent('onContentBeforeSave', array('com_rsbmedia.file', &$objectFile, true));

			if (in_array(false, $result, true))
			{
				$errors = $objectFile->getErrors();

				// There are some errors in the plugins
				Log::add('Errors before save: ' . $objectFile->filepath . ' : ' . implode(', ', $errors), Log::INFO, 'upload');

				$response = array(
					'status' => '0',
					'error' => Text::plural('COM_RSBMEDIA_ERROR_BEFORE_SAVE', count($errors), implode('<br />', $errors))
				);

				echo json_encode($response);

				return;
			}

			if (JFile::exists($objectFile->filepath))
			{
				// File exists
				Log::add('File exists: ' . $objectFile->filepath . ' by user_id ' . $user->id, Log::INFO, 'upload');

				$response = array(
					'status' => '0',
					'error' => Text::_('COM_RSBMEDIA_ERROR_FILE_EXISTS')
				);

				echo json_encode($response);

				return;
			}

			if (!JFile::upload($objectFile->tmp_name, $objectFile->filepath))
			{
				// Error in upload
				Log::add('Error on upload: ' . $objectFile->filepath, Log::INFO, 'upload');

				$response = array(
					'status' => '0',
					'error' => Text::_('COM_RSBMEDIA_ERROR_UNABLE_TO_UPLOAD_FILE')
				);

				echo json_encode($response);

				return;
			}
			else
			{
				// Trigger the onContentAfterSave event.
				$app->triggerEvent('onContentAfterSave', array('com_rsbmedia.file', &$objectFile, true));
				Log::add($folder, Log::INFO, 'upload');

				$response = array(
					'status' => '1',
					'error' => Text::sprintf('COM_RSBMEDIA_UPLOAD_COMPLETE', substr($objectFile->filepath, strlen(COM_RSBMEDIA_BASE)))
				);

				echo json_encode($response);

				return;
			}
		}
		else
		{
			$response = array(
				'status' => '0',
				'error' => Text::_('COM_RSBMEDIA_ERROR_BAD_REQUEST')
			);

			echo json_encode($response);

			return;
		}
	}
}
