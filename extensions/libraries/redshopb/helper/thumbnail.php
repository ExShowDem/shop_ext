<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use ImageOptimizer\OptimizerFactory;
use Imagine\Gd\Imagine;
use Imagine\Gd\Image;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\Image\Image as JoomlaImage;

jimport('joomla.filesystem.folder');

/**
 * A thumbnail helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperThumbnail
{
	/**
	 * If false - error store in static value
	 *
	 * @var boolean
	 */
	public static $displayError = true;

	/**
	 * @var string
	 */
	private static $error = '';

	/**
	 * @var string
	 */
	public static $shaCurrentFile;

	/**
	 * @var integer
	 */
	protected static $spentTimeForRenderThumbs = 0;

	/**
	 * Set Enqueue Message
	 *
	 * @param   string  $message  Message
	 * @param   string  $type     Type message
	 *
	 * @return void
	 */
	private static function setEnqueueMessage($message, $type = 'error')
	{
		if (self::$displayError == true)
		{
			Factory::getApplication()->enqueueMessage($message, $type);
		}
		else
		{
			self::$error = $message;
		}
	}

	/**
	 * Get error
	 *
	 * @return string
	 */
	public static function getError()
	{
		return self::$error;
	}

	/**
	 * Create a thumbnail of the given image
	 *
	 * @param   string   $image   The relative path to the image
	 * @param   string   $folder  The folder name where to store the thumbnail
	 * @param   boolean  $reset   True to reset an existing thumbnail
	 *
	 * @return  mixed             The relative path to the thumbnail or FALSE
	 */
	public static function create($image, $folder, $reset = false)
	{
		$path = JPATH_ROOT . '/' . $image;

		if (!file_exists($path))
		{
			return false;
		}

		// Create the folder if not existing
		$folderPath = self::getFolderPath($folder);

		if (!is_dir($folderPath))
		{
			if (!JFolder::create($folderPath))
			{
				return false;
			}
		}

		// Get the thumbnail config
		$config = RedshopbEntityConfig::getInstance();
		$width  = $config->getThumbnailWidth();
		$height = $config->getThumbnailHeight();

		// If reset is not forced make sure the thumbnail is not already existing
		if (!$reset)
		{
			$filename      = pathinfo($path, PATHINFO_FILENAME);
			$fileExtension = pathinfo($path, PATHINFO_EXTENSION);
			$thumbFileName = $filename . '_' . $width . 'x' . $height . '.' . $fileExtension;
			$thumbPath     = $folderPath . '/' . $thumbFileName;

			if (file_exists($thumbPath))
			{
				return str_replace(JPATH_ROOT . '/', '', $thumbPath);
			}
		}

		$image = new JoomlaImage($path);

		try
		{
			$thumbs = $image->createThumbs(array($width . 'x' . $height), JoomlaImage::SCALE_FILL, $folderPath);
		}

		catch (Exception $e)
		{
			return false;
		}

		if (is_array($thumbs))
		{
			/** @var Joomla\Image\Image $image */
			$image = $thumbs[0];

			// We want a relative path
			return str_replace(JPATH_ROOT . '/', '', $image->getPath());
		}

		return false;
	}

	/**
	 * Get the thumb path to the image
	 *
	 * @param   string  $image   The relative path to the image
	 * @param   string  $folder  The folder name like 'product'
	 *
	 * @return  boolean|string   The relative path to the thumbnail or FALSE if not existing
	 */
	public static function getThumbPath($image, $folder)
	{
		$path       = JPATH_ROOT . '/' . $image;
		$folderPath = self::getFolderPath($folder);

		// Get the thumbnail config
		$config = RedshopbEntityConfig::getInstance();
		$width  = $config->getThumbnailWidth();
		$height = $config->getThumbnailHeight();

		$filename      = pathinfo($path, PATHINFO_FILENAME);
		$fileExtension = pathinfo($path, PATHINFO_EXTENSION);
		$thumbFileName = $filename . '_' . $width . 'x' . $height . '.' . $fileExtension;
		$thumbPath     = $folderPath . '/' . $thumbFileName;

		if (file_exists($thumbPath))
		{
			// We want a relative path
			return str_replace(JPATH_ROOT . '/', '', $thumbPath);
		}

		return false;
	}

	/**
	 * Get the full path to the given folder
	 *
	 * @param   string  $folder  The folder like 'product'
	 *
	 * @return  string           The full path to the folder
	 */
	public static function getFolderPath($folder)
	{
		return JPATH_ROOT . '/images/redshopb/' . $folder;
	}

	/**
	 * Delete thumb images and, if need, relative original image
	 *
	 * @param   string  $fileName    Name original image
	 * @param   int     $deleteOrig  Flag from delete original image
	 * @param   string  $section     Name section
	 * @param   string  $remotePath  Remote path
	 *
	 * @return  void
	 */
	public static function deleteImage($fileName, $deleteOrig = 1, $section = 'products', $remotePath = '')
	{
		$increment  = RedshopbHelperMedia::getIncrementFromFilename($fileName);
		$folderName = RedshopbHelperMedia::getFolderName($increment);

		// We do not delete remote images
		if ($deleteOrig && empty($remotePath))
		{
			$path = JPATH_SITE . '/media/com_redshopb/images/originals/' . $section . '/' . $folderName . '/' . $fileName;

			if (JFile::exists($path))
			{
				JFile::delete($path);
			}
		}

		$origPattern       = JFile::stripExt($fileName);
		$origPatternLength = strlen($origPattern);

		$thumbFolder = JPATH_SITE . '/media/com_redshopb/images/thumbs/' . $section . '/' . $folderName;

		if (JFolder::exists($thumbFolder))
		{
			$filesArray = JFolder::files($thumbFolder);

			for ($i = 0; $i < count($filesArray); $i++)
			{
				if (substr($filesArray[$i], 0, $origPatternLength) == $origPattern)
				{
					JFile::delete($thumbFolder . '/' . $filesArray[$i]);
				}
			}
		}
	}

	/**
	 * Gets the full image path
	 *
	 * @param   string  $fileName    Name file
	 * @param   string  $section     Section name
	 * @param   string  $remotePath  Remote path
	 *
	 * @return boolean|string
	 */
	public static function getFullImagePath($fileName, $section = 'products', $remotePath = '')
	{
		return RedshopbHelperMedia::getFullMediaPath($fileName, $section, 'images', $remotePath);
	}

	/**
	 * Generate thumb image and return full path thumb
	 *
	 * @param   string   $fileName    Name file
	 * @param   int      $width       Width file
	 * @param   int      $height      Height file
	 * @param   int      $quality     Quantity file
	 * @param   int      $crop        Flag crop file
	 * @param   string   $section     Section name
	 * @param   bool     $justPath    Return just path
	 * @param   string   $remotePath  Remote path
	 * @param   boolean  $isCli       [description]
	 *
	 * @return  boolean|string
	 */
	public static function originalToResize(
		$fileName, $width = 144, $height = 144, $quality = 100, $crop = 0, $section = 'products', $justPath = false, $remotePath = '', $isCli = false
	)
	{
		if (empty($fileName))
		{
			return false;
		}

		$executedTime       = microtime(true);
		$return             = true;
		$increment          = RedshopbHelperMedia::getIncrementFromFilename($fileName);
		$folderName         = RedshopbHelperMedia::getFolderName($increment);
		$imageOptimization  = RedshopbApp::getConfig()->get('image_optimization', 'no');
		$forceThumbnailSize = (RedshopbApp::getConfig()->get('force_thumbnail_size', '1') == '1' ? true : false);
		$optimizationEnable = false;

		switch ($imageOptimization)
		{
			case 'lazy':
				if (self::$spentTimeForRenderThumbs < 2)
				{
					$optimizationEnable = true;
				}
				break;
			case 'force':
				$optimizationEnable = true;
				break;
		}

		$fullFileName = self::makeFileName($fileName, $width, $height, $quality, $crop, $optimizationEnable, $forceThumbnailSize);

		switch ($section)
		{
			case 'field-images':
				$sourceFile = JPATH_SITE . '/media/com_redshopb/' . $section . '/originals/products/' . $folderName . '/' . $fileName;

				if (!empty($remotePath))
				{
					$sourceFile = JPATH_SITE . '/' . RedshopbHelperMedia::getFullMediaPath($fileName, 'products', $section, $remotePath);
				}

				$destFile = JPATH_SITE . '/media/com_redshopb/images/thumbs/' . $section . '/' . $folderName . '/' . $fullFileName;

				break;
			default:
				$sourceFile = JPATH_SITE . '/media/com_redshopb/images/originals/' . $section . '/' . $folderName . '/' . $fileName;

				if (!empty($remotePath))
				{
					$sourceFile = JPATH_SITE . '/' . RedshopbHelperMedia::getFullMediaPath($fileName, $section, 'images', $remotePath);
				}

				$destFile = JPATH_SITE . '/media/com_redshopb/images/thumbs/' . $section . '/' . $folderName . '/' . $fullFileName;

				break;
		}

		if (!JFile::exists($destFile))
		{
			if (!JFile::exists($sourceFile))
			{
				if (RedshopbHelperACL::isSuperAdmin())
				{
					self::setEnqueueMessage(Text::sprintf('COM_REDSHOPB_THUMBNAIL_SOURCE_FILE_NOT_EXIST', $sourceFile), 'warning');
				}

				$return = false;
			}
			else
			{
				RedshopbHelperMedia::makeFolder(JPATH_SITE . '/media/com_redshopb/images/thumbs/' . $section . '/' . $folderName);

				if ($optimizationEnable)
				{
					$fileNonOptName       = self::makeFileName($fileName, $width, $height, $quality, $crop, false, $forceThumbnailSize);
					$destNotOptimizedFile = JPATH_SITE . '/media/com_redshopb/images/thumbs/'
						. $section . '/' . $folderName . '/' . $fileNonOptName;

					// Create first normal thumb if not exists
					if (JFile::exists($destNotOptimizedFile))
					{
						if (!JFile::move($destNotOptimizedFile, $destFile))
						{
							$return = false;
						}
					}
					else
					{
						if (!self::makeImage($sourceFile, $destFile, $width, $height, $quality, $crop))
						{
							$return = false;
						}
					}

					if ($return)
					{
						// Extra file optimization - depends on binary files to work properly
						$factory   = new OptimizerFactory;
						$optimizer = $factory->get();
						$optimizer->optimize($destFile);
					}
				}
				else
				{
					$fileOptName       = self::makeFileName($fileName, $width, $height, $quality, $crop, true, $forceThumbnailSize);
					$destOptimizedFile = JPATH_SITE . '/media/com_redshopb/images/thumbs/'
						. $section . '/' . $folderName . '/' . $fileOptName;

					if (JFile::exists($destOptimizedFile))
					{
						JFile::delete($destOptimizedFile);
					}

					if (!self::makeImage($sourceFile, $destFile, $width, $height, $quality, $crop))
					{
						$return = false;
					}
				}
			}
		}

		self::$spentTimeForRenderThumbs += microtime(1) - $executedTime;

		if ($return)
		{
			if ($isCli)
			{
				return ($justPath ? '' : JPATH_ROOT . '/' ) .
					'media/com_redshopb/images/thumbs/' . $section . '/' . $folderName . '/' . $fullFileName;
			}

			return ($justPath ? '' : Uri::root()) . 'media/com_redshopb/images/thumbs/' . $section . '/' . $folderName . '/' . $fullFileName;
		}

		return false;
	}

	/**
	 * Generating thumb file
	 *
	 * @param   string  $sourceFile  Path original image
	 * @param   string  $destFile    Path destination thumb image
	 * @param   int     $width       With thumb
	 * @param   int     $height      Height thumb
	 * @param   int     $quality     Quality thumb
	 * @param   int     $crop        Method cropping thumb (0 - not crop, 1 - use swap, 2 - crop)
	 * @param   bool    $force       True for force create image.
	 *
	 * @return  boolean  True if file generated or false
	 */
	public static function makeImage($sourceFile, $destFile, $width, $height, $quality, $crop = 0, $force = false)
	{
		if (!JFile::exists($destFile) || $force)
		{
			if (!RedshopbHelperMedia::checkIsImage($sourceFile))
			{
				return false;
			}

			$originalMemoryLimit = '-1';

			// Memory limit bump to prevent image creation process breaking up
			if (RedshopbHelperSync::returnBytesFromIniValue(ini_get('memory_limit')) < RedshopbHelperSync::returnBytesFromIniValue('1024M'))
			{
				$originalMemoryLimit = ini_get('memory_limit');
				ini_set('memory_limit', '1024M');
			}

			$data     = file_get_contents($sourceFile);
			$resource = imagecreatefromstring($data);
			$imagine  = new Imagine;
			$image    = new Image($resource, new RGB, $imagine->getMetadataReader()->readFile($sourceFile));
			$box      = new Box($width, $height);
			$options  = array();

			if (!is_null($quality))
			{
				$options['quality'] = $quality;
			}

			if ($crop)
			{
				$mode = ImageInterface::THUMBNAIL_OUTBOUND;
			}
			else
			{
				$mode = ImageInterface::THUMBNAIL_INSET;
			}

			$finalThumbnail     = null;
			$forceThumbnailSize = (RedshopbApp::getConfig()->get('force_thumbnail_size', '1') == '1' ? true : false);
			$thumbnail          = $image->thumbnail($box, $mode);
			$thumbnailSize      = $thumbnail->getSize();
			$thumbnailWidth     = $thumbnailSize->getWidth();
			$thumbnailHeight    = $thumbnailSize->getHeight();

			// If the auto-generated thumbnail is smaller, it pastes it in a blank image (to force width/height without de-proportioning)
			if ($forceThumbnailSize && ($thumbnailWidth < $width || $thumbnailHeight < $height))
			{
				$finalThumbPalette = new RGB;
				$finalThumbBox     = new Box($width, $height);
				$finalThumbColor   = $finalThumbPalette->color('#fff', 100);

				$finalThumbnail = $imagine->create($finalThumbBox, $finalThumbColor);
				$finalThumbnail->paste($thumbnail, new Point(($width - $thumbnailWidth) / 2, ($height - $thumbnailHeight) / 2));

				$thumbnail = $finalThumbnail;
			}

			$thumbnail->save($destFile);

			unset($finalThumbnail);
			unset($thumbnail);
			unset($image);
			unset($imagine);

			// Memory limit back to normal
			if ($originalMemoryLimit != '-1')
			{
				ini_set('memory_limit', $originalMemoryLimit);
			}
		}

		return true;
	}

	/**
	 * Make full thumb file name
	 *
	 * @param   string  $fileName            Name file
	 * @param   string  $width               Width file
	 * @param   string  $height              Height file
	 * @param   int     $quality             Quantity file
	 * @param   int     $crop                Flag using crop from file
	 * @param   bool    $optimization        Optimization enable
	 * @param   bool    $forceThumbnailSize  Forced thumbnail size enabled
	 *
	 * @return string
	 */
	public static function makeFileName($fileName, $width, $height, $quality = 100, $crop = 0, $optimization = false, $forceThumbnailSize = true)
	{
		$fileNameExt   = JFile::getExt($fileName);
		$fileNameNoExt = JFile::stripExt($fileName);

		$fullFileName = $fileNameNoExt . '-' . $width . '-' . $height . '-' . $quality;

		if ($crop == 1)
		{
			$fullFileName .= '-c';
		}

		if ($optimization)
		{
			$fullFileName .= '-o';
		}

		if ($forceThumbnailSize)
		{
			$fullFileName .= '-fz';
		}

		$fullFileName .= '.' . $fileNameExt;

		return $fullFileName;
	}

	/**
	 * Saving File
	 *
	 * @param   string  $fullPath       Full path from current file
	 * @param   string  $imageName      Image name from current file
	 * @param   int     $increment      Increment
	 * @param   bool    $anotherServer  Flag use upload with another server
	 * @param   string  $section        Name section from file
	 *
	 * @return  boolean|string
	 */
	public static function savingImage($fullPath, $imageName = '', $increment = 0, $anotherServer = false, $section = 'products')
	{
		$result               = RedshopbHelperMedia::savingMedia($fullPath, $imageName, $increment, $anotherServer, $section);
		self::$shaCurrentFile = RedshopbHelperMedia::$shaCurrentFile;

		return $result;
	}

	/**
	 * Check File Error
	 *
	 * @param   string  $fileName   Name current file
	 * @param   int     $fileError  Number error
	 *
	 * @return  boolean
	 */
	public static function checkFileError($fileName, $fileError)
	{
		if (!$fileError)
		{
			return true;
		}

		switch ($fileError)
		{
			case 1:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_UPLOADED_FILE_EXCEEDS_UPLOAD_MAX_FILESIZE', $fileName)
				);
				break;

			case 2:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_UPLOADED_FILE_EXCEEDS_UPLOAD_MAX_FILESIZE_IN_HTML_FORM', $fileName)
				);
				break;

			case 3:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_ONLY_PARTIALLY_UPLOADED', $fileName)
				);
				break;

			case 4:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_NO_FILE_WAS_UPLOADED', $fileName)
				);
				break;

			case 6:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_MISSING_TEMPORARY_UPLOAD_FOLDER', $fileName)
				);
				break;

			case 7:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_FAILED_WRITE_FILE_TO_DISK', $fileName)
				);
				break;

			case 8:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_STOPPED_THE_FILE_UPLOAD', $fileName)
				);
				break;

			default:
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_UPLOAD_ERROR_CODE', $fileName, $fileError)
				);
		}

		return false;
	}

	/**
	 * Check is original file bigger than thumb or not
	 *
	 * @param   string  $fileName    Name file
	 * @param   int     $width       Width file
	 * @param   int     $height      Height file
	 * @param   int     $quality     Quantity file
	 * @param   int     $crop        Flag crop file
	 * @param   string  $section     Section name
	 * @param   string  $remotePath  Remote path
	 * @param   string  $isCli       [description]
	 *
	 * @return  array
	 */
	public static function originalIsBigger(
		$fileName, $width = 144, $height = 144, $quality = 100,
		$crop = 0, $section = 'products', $remotePath = '', $isCli = false
	)
	{
		$return = array('result' => false);

		if (empty($fileName))
		{
			return $return;
		}

		$thumbPath          = self::originalToResize($fileName, $width, $height, $quality, $crop, $section, true, $remotePath);
		$originalPath       = self::getFullImagePath($fileName, $section, $remotePath);
		$return['thumb']    = $isCli ? JPATH_ROOT . $originalPath : Uri::root() . $originalPath;
		$return['original'] = $return['thumb'];

		if (!JFile::exists(JPATH_ROOT . '/' . $originalPath) || !JFile::exists(JPATH_ROOT . '/' . $thumbPath))
		{
			return $return;
		}

		$return['originalInfo'] = getimagesize(JPATH_ROOT . '/' . $originalPath);
		$return['thumbInfo']    = getimagesize(JPATH_ROOT . '/' . $thumbPath);

		if ($return['originalInfo'][0] > $return['thumbInfo'][0] || $return['originalInfo'][1] > $return['thumbInfo'][1])
		{
			$return['result'] = true;
			$return['thumb']  = $isCli ? JPATH_ROOT . $thumbPath : Uri::root() . $thumbPath;
		}

		return $return;
	}

	/**
	 * Get safe alt
	 *
	 * @param   string  $alt             Alt text
	 * @param   string  $alternativeAlt  Alternative alt
	 *
	 * @return  string
	 *
	 * @since  1.13.0
	 */
	public static function safeAlt($alt, $alternativeAlt = null)
	{
		if (empty($alt) && !empty($alternativeAlt))
		{
			$alt = $alternativeAlt;
		}

		return htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
	}
}
