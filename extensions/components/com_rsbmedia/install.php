<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redCORE',
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redcore'
	);

	if ($redcoreInstaller = JPath::find($searchPaths, 'install.php'))
	{
		require_once $redcoreInstaller;
	}
}

/**
 * Custom installation of Rsbmedia.
 *
 * @package     Rsbmedia
 * @subpackage  Install
 * @since       1.0
 */
class Com_RsbmediaInstallerScript extends Com_RedcoreInstallerScript
{
	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
		// Insert default params only on fresh install
		if ($type == 'install' || $type == 'discover_install')
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			// Allowed extensions for upload
			$uploadExtensions = array('bmp', 'BMP', 'gif', 'GIF', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG');

			// Max file size in MB
			$uploadMaxFileSize = '50';

			// Default path for storing files
			$filePath = 'files';

			// Default path for storing images
			$imagePath = 'images';

			// Restrict uploads for lower than manager users to just images if Fileinfo or MIME Magic isn't installed
			$restrictUploads = '1';

			// Check MIME for file which is uploading
			$checkMime = '1';

			// Image allowed list for upload
			$imageExtensions = array('bmp', 'BMP', 'gif', 'GIF', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG');

			// List of ignored extensions
			$ignoreExtensions = array('');

			// List of allowed MIMEs
			$uploadMime = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/bmp');

			// List of illegal MIMEs
			$uploadMimeIllegal = array('text/html');

			// Config params array for storing
			$params = array(
				'upload_extensions'   => implode(',', $uploadExtensions),
				'upload_maxsize'      => $uploadMaxFileSize,
				'file_path'           => $filePath,
				'image_path'          => $imagePath,
				'restrict_uploads'    => $restrictUploads,
				'check_mime'          => $checkMime,
				'image_extensions'    => implode(',', $imageExtensions),
				'ignore_extensions'   => implode(',', $ignoreExtensions),
				'upload_mime'         => implode(',', $uploadMime),
				'upload_mime_illegal' => implode(',', $uploadMimeIllegal)
			);

			$query->update($db->qn('#__extensions'))
				->set($db->qn('params') . ' = ' . $db->q(json_encode($params)))
				->where($db->qn('name') . ' = ' . $db->q('com_rsbmedia'));
			$db->setQuery($query);
			$db->execute();
		}

		parent::postflight($type, $parent);
	}
}
