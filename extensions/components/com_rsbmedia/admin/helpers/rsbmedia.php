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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Helper\MediaHelper;
/**
 * RsbmediaHelper class
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.5
 * @deprecated  4.0  Use JHelperRsbmedia instead
 */
abstract class RsbmediaHelper
{
	/**
	 * Checks if the file is an image
	 *
	 * @param   string  $fileName  The filename
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use JHelperRsbmedia::isImage instead
	 */
	public static function isImage($fileName)
	{
		Log::add('RsbmediaHelper::isImage() is deprecated. Use MediaHelper::isImage() instead.', Log::WARNING, 'deprecated');
		$mediaHelper = new MediaHelper;

		return $mediaHelper->isImage($fileName);
	}

	/**
	 * Gets the file extension for the purpose of using an icon.
	 *
	 * @param   string  $fileName  The filename
	 *
	 * @return  string  File extension
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaHelper::gJetTypeIcon instead
	 */
	public static function getTypeIcon($fileName)
	{
		Log::add('RsbmediaHelper::getTypeIcon() is deprecated. Use MediaHelper::getTypeIcon() instead.', Log::WARNING, 'deprecated');
		$mediaHelper = new MediaHelper;

		return $mediaHelper->getTypeIcon($fileName);
	}

	/**
	 * Checks if the file can be uploaded
	 *
	 * @param   array   $file  File information
	 * @param   string  $err   An error message to be returned
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaHelper::canUpload instead
	 */
	public static function canUpload($file, $err = '')
	{
		Log::add('RsbmediaHelper::canUpload() is deprecated. Use MediaHelper::canUpload() instead.', Log::WARNING, 'deprecated');
		$mediaHelper = new MediaHelper;

		return $mediaHelper->canUpload($file, 'com_rsbmedia');
	}

	/**
	 * Method to parse a file size
	 *
	 * @param   integer  $size  The file size in bytes
	 *
	 * @return  string  The converted file size
	 *
	 * @since   1.6
	 * @deprecated  4.0  Use JHtmlNumber::bytes() instead
	 */
	public static function parseSize($size)
	{
		Log::add('RsbmediaHelper::parseSize() is deprecated. Use JHtmlNumber::bytes() instead.', Log::WARNING, 'deprecated');

		return HTMLHelper::_('number.bytes', $size);
	}

	/**
	 * Calculate the size of a resized image
	 *
	 * @param   integer  $width   Image width
	 * @param   integer  $height  Image height
	 * @param   integer  $target  Target size
	 *
	 * @return  array  The new width and height
	 *
	 * @since   3.2
	 * @deprecated  4.0  Use MediaHelper::imageResize instead
	 */
	public static function imageResize($width, $height, $target)
	{
		Log::add('RsbmediaHelper::countFiles() is deprecated. Use MediaHelper::countFiles() instead.', Log::WARNING, 'deprecated');
		$mediaHelper = new MediaHelper;

		return $mediaHelper->imageResize($width, $height, $target);
	}

	/**
	 * Counts the files and directories in a directory that are not php or html files.
	 *
	 * @param   string  $dir  Directory name
	 *
	 * @return  array  The number of files and directories in the given directory
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaHelper::countFiles instead
	 */
	public static function countFiles($dir)
	{
		Log::add('RsbmediaHelper::countFiles() is deprecated. Use MediaHelper::countFiles() instead.', Log::WARNING, 'deprecated');
		$mediaHelper = new MediaHelper;

		return $mediaHelper->countFiles($dir);
	}
}
