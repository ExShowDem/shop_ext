<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Uri\Uri;

jimport('joomla.filesystem.folder');

JLoader::import('redshopb.3rdparty.imagine.library');
JLoader::import('redshopb.3rdparty.image-optimizer.library');

/**
 * A media helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperMedia
{
	/**
	 * If false - error store in static value
	 *
	 * @var boolean
	 */
	public static $displayError = true;

	/**
	 * Error string container
	 *
	 * @var string
	 */
	private static $error = '';

	/**
	 * Hash string of the last generated file
	 *
	 * @var string
	 */
	public static $shaCurrentFile;

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
	 * Delete media files and, if need, thumbnail image
	 *
	 * @param   string  $fileName    Name original filename
	 * @param   int     $deleteOrig  Flag from delete original file
	 * @param   string  $section     Name section
	 * @param   string  $media       Name section
	 *
	 * @return  void
	 */
	public static function deleteMedia($fileName, $deleteOrig = 1, $section = 'products', $media = 'images')
	{
		$increment  = self::getIncrementFromFilename($fileName);
		$folderName = self::getFolderName($increment);

		if ($deleteOrig)
		{
			$path = JPATH_SITE . '/media/com_redshopb/' . $media . '/originals/' . $section . '/' . $folderName . '/' . $fileName;

			if (JFile::exists($path))
			{
				JFile::delete($path);
			}
		}

		$origPattern       = JFile::stripExt($fileName);
		$origPatternLength = strlen($origPattern);

		$thumbFolder = JPATH_SITE . '/media/com_redshopb/' . $media . '/thumbs/' . $section . '/' . $folderName;

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
	 * Gets the full media path
	 *
	 * @param   string  $fileName    Name file
	 * @param   string  $section     Section name
	 * @param   string  $media       Media folder name
	 * @param   string  $remotePath  Remote path
	 *
	 * @return boolean|string
	 */
	public static function getFullMediaPath($fileName, $section = 'products', $media = 'images', $remotePath = '')
	{
		if (!empty($remotePath))
		{
			return self::getMediaRemotePath($fileName, $remotePath, false);
		}

		$increment  = self::getIncrementFromFilename($fileName);
		$folderName = self::getFolderName($increment);

		return 'media/com_redshopb/' . $media . '/originals/' . $section . '/' . $folderName . '/' . $fileName;
	}

	/**
	 * Gets the full image path
	 *
	 * @param   string  $fileName      Name file
	 * @param   string  $remotePath    Remote path
	 * @param   bool    $addLocalPath  Remote path
	 *
	 * @return boolean|string
	 */
	public static function getMediaRemotePath($fileName, $remotePath = '', $addLocalPath = true)
	{
		return self::parseMediaRemotePath($remotePath, $addLocalPath) . '/' . $fileName;
	}

	/**
	 * Gets the full image path
	 *
	 * @param   string  $remotePath    Remote path
	 * @param   bool    $addLocalPath  Remote path
	 *
	 * @return boolean|string
	 */
	public static function parseMediaRemotePath($remotePath = '', $addLocalPath = true)
	{
		if (empty($remotePath))
		{
			return '';
		}

		$remotePath = rtrim($remotePath, '/');

		if (substr($remotePath, 0, strlen('http://')) === 'http://' || substr($remotePath, 0, strlen('https://')) === 'https://')
		{
			return $remotePath;
		}

		return $addLocalPath ? JPATH_ROOT . '/' . $remotePath : $remotePath;
	}

	/**
	 * Saving Media File
	 *
	 * @param   string  $fullPath         Full path from current file
	 * @param   string  $fileName         Video name from current file
	 * @param   int     $increment        Increment
	 * @param   bool    $anotherServer    Flag use upload with another server
	 * @param   string  $section          Name section from file
	 * @param   string  $media            Media name
	 * @param   bool    $checkExtensions  Media name
	 * @param   bool    $checkMime        Media name
	 *
	 * @return boolean|string
	 */
	public static function savingMedia(
		$fullPath, $fileName = '', $increment = 0, $anotherServer = false, $section = 'products',
		$media = 'images', $checkExtensions = true, $checkMime = true
	)
	{
		$allowedMimeTypes = array();

		switch ($media)
		{
			case 'images':
			case 'field-images':
				if ($checkExtensions && !self::checkExtension($fileName))
				{
					return false;
				}

				$allowedMimeTypes = array('image/gif', 'image/jpeg', 'image/png', 'text/plain');
				break;
			case 'documents':
				if ($checkExtensions && !self::checkExtension($fileName, 'txt,csv,pdf,doc,docx,xls,xlsx,ppt,pptx'))
				{
					return false;
				}

				$allowedMimeTypes = array(
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					'application/msword',
					'application/vnd.ms-excel',
					'application/vnd.ms-powerpoint',
					'application/pdf',
					'text/csv',
					'text/pdf',
					'text/plain',
				);
				break;
			case 'videos':
				if ($checkExtensions
					&& !self::checkExtension(
						$fileName,
						'3gp,asf,avi,divx,flv,m3u8,m4v,mkv,mov,mpe,mp4,mpeg,mpeg4,ogv,ts,qt,webm,wm,wmv,wmx'
					)
				)
				{
					return false;
				}

				$allowedMimeTypes = array(
					'application/annodex',
					'application/mp4',
					'application/ogg',
					'application/vnd.rn-realmedia',
					'application/x-matroska',
					'video/3gpp',
					'video/3gpp2',
					'video/annodex',
					'video/divx',
					'video/flv',
					'video/h264',
					'video/mp4',
					'video/mp4v-es',
					'video/MP2T',
					'video/mpeg',
					'video/mpeg-2',
					'video/mpeg4',
					'video/msvideo',
					'video/ogg',
					'video/ogm',
					'video/quicktime',
					'video/ty',
					'video/vdo',
					'video/vivo',
					'video/vnd.rn-realvideo',
					'video/vnd.vivo',
					'video/webm',
					'video/x-bin',
					'video/x-cdg',
					'video/x-divx',
					'video/x-dv',
					'video/x-flv',
					'video/x-la-asf',
					'video/x-m4v',
					'video/x-matroska',
					'video/x-motion-jpeg',
					'video/x-ms-asf',
					'video/x-ms-dvr',
					'video/x-ms-wm',
					'video/x-ms-wmv',
					'video/x-msvideo',
					'video/x-sgi-movie',
					'video/x-tivo',
					'video/avi',
					'video/x-ms-asx',
					'video/x-ms-wvx',
					'video/x-ms-wmx',
					'application/x-mpegURL',
					'text/plain'
				);
				break;

			case 'files':

				// We do not check mime or file extensions for files type
				$checkExtensions = false;
				$checkMime       = false;
				break;
			default:

				self::setEnqueueMessage(Text::_('COM_REDSHOPB_THUMBNAIL_ERROR_MEDIA_NO_SUPPORTED'));

				return false;
		}

		$destinationFile = self::copyMediaFile($fullPath, $fileName, $increment, $anotherServer, $section, $media);

		if (!$destinationFile)
		{
			return false;
		}

		// Get the folder path
		$folderName        = self::getFolderName($increment);
		$destinationFolder = JPATH_SITE . '/media/com_redshopb/' . $media . '/originals/' . $section . '/' . $folderName;
		$destinationPath   = $destinationFolder . '/' . $destinationFile;
		$checkFile         = true;

		// We check a width in image media file so we have an extended function for it
		if (in_array($media, array('images', 'field-images')))
		{
			$checkFile = self::checkIsImage($destinationPath);
		}
		elseif ($checkMime)
		{
			$checkFile = self::checkIsMimeAllowed($destinationPath, $allowedMimeTypes);
		}

		if (!$checkFile)
		{
			self::setEnqueueMessage(Text::sprintf('COM_REDSHOPB_THUMBNAIL_ERROR_MOVING_FILE', $fullPath, $destinationPath), 'warning');
			JFile::delete($destinationPath);

			return false;
		}

		if (in_array($media, array('images', 'field-images')))
		{
			self::prepareStoredImage($destinationPath);
		}

		return $destinationFile;
	}

	/**
	 * Get Remote File Size from remote Headers
	 *
	 * @param   string  $url  Remote file URL
	 *
	 * @return boolean|integer
	 */
	public static function getRemoteFileSize($url)
	{
		if (substr($url, 0, 4) == 'http')
		{
			$headers = get_headers($url, 1);

			// Failed to reach remote media
			if (empty($headers))
			{
				return 0;
			}

			$x = array_change_key_case($headers, CASE_LOWER);

			// If there is a redirect, we will fetch the second one
			if (strpos(strtoupper($x[0]), ' 200 OK') === false)
			{
				$x = $x['content-length'][1];
			}
			else
			{
				$x = $x['content-length'];
			}
		}
		else
		{
			$x = filesize($url);
		}

		return $x;
	}

	/**
	 * Copy any Media File
	 *
	 * @param   string  $fullPath       Full path from current file
	 * @param   string  $fileName       File name from current file
	 * @param   int     $increment      Increment
	 * @param   bool    $anotherServer  Flag use upload with another server
	 * @param   string  $section        Name section from file
	 * @param   string  $media          Media folder name
	 *
	 * @return boolean|string
	 */
	public static function copyMediaFile($fullPath, $fileName = '', $increment = 0, $anotherServer = false, $section = 'products', $media = 'images')
	{
		$fileNameClean = self::replaceSpecial($fileName);

		// Get the folder path
		$folderName        = self::getFolderName($increment);
		$destinationFolder = JPATH_SITE . '/media/com_redshopb/' . $media . '/originals/' . $section . '/' . $folderName;

		if (!self::makeFolder($destinationFolder))
		{
			return false;
		}

		// Make the filename
		$destinationFile = self::addIncrement($fileNameClean, $increment);
		$destinationFile = self::checkUniqueName($destinationFolder, $destinationFile);
		$destinationPath = $destinationFolder . '/' . $destinationFile;

		if ($anotherServer)
		{
			$fullPath = str_replace(' ', '%20', $fullPath);

			try
			{
				$binaryData = file_get_contents($fullPath);
			}
			catch (Exception $e)
			{
				// Add fail message to the fullPath string which will be outputed in the Message queue
				$fullPath  .= ' ' . $e->getMessage();
				$binaryData = false;
			}

			if ($binaryData === false)
			{
				self::setEnqueueMessage(
					Text::sprintf(
						'COM_REDSHOPB_THUMBNAIL_ERROR_FETCHING_REMOTE_FILE', $fullPath
					),
					'warning'
				);

				return false;
			}

			if (function_exists('mb_strlen'))
			{
				$downloadedSize = mb_strlen($binaryData, '8bit');
			}
			else
			{
				$downloadedSize = strlen($binaryData);
			}

			// Read remote files size
			$fileSize = self::getRemoteFileSize($fullPath);

			if ($fileSize != $downloadedSize)
			{
				self::setEnqueueMessage(
					Text::sprintf(
						'COM_REDSHOPB_THUMBNAIL_ERROR_FETCHING_COMPLETE_REMOTE_FILE', $fullPath, $downloadedSize, $fileSize
					),
					'warning'
				);

				return false;
			}

			if (file_put_contents($destinationPath, $binaryData) === false)
			{
				self::setEnqueueMessage(
					Text::sprintf(
						'COM_REDSHOPB_THUMBNAIL_ERROR_MOVING_FILE_TO_DIRECTORY', $fullPath, $destinationPath
					),
					'warning'
				);

				return false;
			}
		}
		else
		{
			if (!JFile::copy($fullPath, $destinationPath))
			{
				self::setEnqueueMessage(
					Text::sprintf(
						'COM_REDSHOPB_THUMBNAIL_ERROR_MOVING_FILE_TO_DIRECTORY', $fullPath, $destinationPath
					),
					'warning'
				);

				return false;
			}
		}

		self::$shaCurrentFile = sha1_file($destinationPath);

		return $destinationFile;
	}

	/**
	 * Get Increment From File Name
	 *
	 * @param   string  $fileName  Name file
	 *
	 * @return mixed
	 */
	public static function getIncrementFromFilename($fileName)
	{
		preg_match_all('/-([0-9]{1,})+/', $fileName, $matches);
		$increment = array_pop($matches[1]);

		return $increment;
	}

	/**
	 * Take unique name from current file
	 *
	 * @param   string  $folderPath  Folder path
	 * @param   string  $file        Name current file
	 *
	 * @return string
	 */
	public static function checkUniqueName($folderPath, $file)
	{
		if (JFile::exists($folderPath . '/' . $file))
		{
			$increment           = self::getIncrementFromFilename($file);
			$fileNameExt         = JFile::getExt($file);
			$fileNameNoExt       = JFile::stripExt($file);
			$fileNameNoIncrement = substr($fileNameNoExt, 0, strrpos($fileNameNoExt, '-'));
			$num                 = 1;

			while (JFile::exists($folderPath . '/' . $fileNameNoIncrement . $num . '-' . $increment . '.' . $fileNameExt))
			{
				$num++;
			}

			$file = $fileNameNoIncrement . $num . '-' . $increment . '.' . $fileNameExt;
		}

		return $file;
	}

	/**
	 * Add increment from file name
	 *
	 * @param   string  $file       File name
	 * @param   int     $increment  Increment
	 *
	 * @return string
	 */
	public static function addIncrement($file, $increment)
	{
		$fileNameExt   = JFile::getExt($file);
		$fileNameNoExt = JFile::stripExt($file);

		return $fileNameNoExt . '-' . $increment . '.' . $fileNameExt;
	}

	/**
	 * Get folder name from current file
	 *
	 * @param   int  $increment  Increment from folder
	 *
	 * @return string
	 */
	public static function getFolderName($increment)
	{
		$start      = floor(($increment / 100) - 0.001) * 100;
		$end        = ceil($increment / 100) * 100;
		$folderName = (($start) + 1) . '-' . $end;

		return $folderName;
	}

	/**
	 * Make folder
	 *
	 * @param   string  $folderPath  Folder Path
	 *
	 * @return boolean
	 */
	public static function makeFolder($folderPath)
	{
		if (!JFolder::exists($folderPath))
		{
			if (!JFolder::create($folderPath, 0755))
			{
				self::setEnqueueMessage(Text::sprintf('COM_REDSHOPB_THUMBNAIL_ERROR_CREATE_FOLDER', $folderPath));

				return false;
			}

			if (!JFile::exists($folderPath . '/index.html'))
			{
				$content = '<html><body bgcolor="#ffffff"></body></html>';
				JFile::write($folderPath . '/index.html', $content);
			}
		}

		return true;
	}

	/**
	 * Exclude all wrong symbols, prepare normal name
	 *
	 * @param   string  $fileName  Name file
	 *
	 * @return string
	 */
	public static function replaceSpecial($fileName)
	{
		$fileExt          = JFile::getExt($fileName);
		$fileNameNoExt    = JFile::stripExt(basename($fileName));
		$fileNameNoExt    = preg_replace("/[&'#]/", '', $fileNameNoExt);
		$fileNameNoExt    = OutputFilter::stringURLSafe($fileNameNoExt);
		$fileNameNoExt    = substr($fileNameNoExt, 0, 30);
		$fileExtLowerCase = strtolower($fileExt);
		$fileName         = $fileNameNoExt . '.' . $fileExtLowerCase;

		return $fileName;
	}

	/**
	 * Check extension
	 *
	 * @param   string  $fileName   Name uploaded file
	 * @param   string  $extAccept  Accepted extensions
	 *
	 * @return boolean
	 */
	public static function checkExtension($fileName, $extAccept = 'jpeg,jpg,png,gif')
	{
		$validFileExtensions = explode(',', $extAccept);
		$fileExtension       = JFile::getExt($fileName);
		$extOk               = false;

		foreach ($validFileExtensions as $value)
		{
			if (preg_match("/$value/i", $fileExtension))
			{
				$extOk = true;
			}
		}

		if ($extOk == false)
		{
			if (empty($fileExtension))
			{
				self::setEnqueueMessage(Text::_('COM_REDSHOPB_THUMBNAIL_ERROR_NO_FILE_EXTENSION'));
			}
			else
			{
				self::setEnqueueMessage(
					Text::sprintf('COM_REDSHOPB_THUMBNAIL_INCORRECT_FILE_TYPE', $fileName, $extAccept)
				);
			}

			return false;
		}

		return true;
	}

	/**
	 * Check uploading file
	 *
	 * @param   string  $tmpPath           Temp path from current video
	 * @param   array   $allowedMimeTypes  Allowed mime types
	 *
	 * @return boolean
	 */
	public static function checkIsMimeAllowed($tmpPath, $allowedMimeTypes = array('image/gif', 'image/jpeg', 'image/png'))
	{
		$mime = self::getMimeType($tmpPath);

		if (!in_array($mime, $allowedMimeTypes))
		{
			self::setEnqueueMessage(
				Text::sprintf('COM_REDSHOPB_MEDIA_FILE_MIME_TYPE_NOT_SUPPORTED', $tmpPath, $mime), 'warning'
			);

			return false;
		}

		return true;
	}

	/**
	 * Get Mime type
	 *
	 * @param   mixed  $file  File information.
	 *
	 * @return  string
	 */
	public static function getMimeType($file)
	{
		if (!is_array($file) && class_exists('finfo'))
		{
			$finfo = new finfo(FILEINFO_MIME);
			$type  = $finfo->buffer($file);
		}
		elseif (function_exists('finfo_open'))
		{
			// We have fileinfo
			$finfo = finfo_open(FILEINFO_MIME);
			$type  = finfo_file($finfo, $file['tmp_name']);

			finfo_close($finfo);
		}
		elseif (function_exists('mime_content_type'))
		{
			// We have mime magic.
			$type = mime_content_type($file['tmp_name']);
		}
		else
		{
			$type = $file['type'];
		}

		// Resolves problem of adding charset to the mime type
		$type = explode(';', $type);

		return $type[0];
	}

	/**
	 * Check uploading image
	 *
	 * @param   string  $tmpPath  Temp path from current image
	 *
	 * @return boolean
	 */
	public static function checkIsImage($tmpPath)
	{
		if (!filesize($tmpPath))
		{
			self::setEnqueueMessage(
				Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_NO_WIDTH_OR_HEIGHT_DETECTED', $tmpPath), 'warning'
			);

			return false;
		}

		$imageInfo = getimagesize($tmpPath);

		if (!is_int($imageInfo[0]) || !is_int($imageInfo[1]))
		{
			self::setEnqueueMessage(
				Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_NO_WIDTH_OR_HEIGHT_DETECTED', $tmpPath), 'warning'
			);

			return false;
		}

		if (array_search($imageInfo['mime'], array('image/gif', 'image/jpeg', 'image/png')) === false)
		{
			self::setEnqueueMessage(
				Text::sprintf('COM_REDSHOPB_MEDIA_IMAGE_MIME_TYPE_NOT_SUPPORTED', $tmpPath, $imageInfo['mime']), 'warning'
			);

			return false;
		}

		return true;
	}

	/**
	 * Submethod for making default image
	 *
	 * @param   string   $defaultFileName  Default file name
	 * @param   int      $width            Width of image.
	 * @param   int      $height           Height of image
	 * @param   string   $text             Text for generate
	 * @param   string   $textColor        Color of text.
	 * @param   string   $backgroundColor  Color of background.
	 *
	 * @return  array    $paths            Paths of generated image.
	 */
	public static function drawDefaultImgSub(
		&$defaultFileName = null,
		$width = 144,
		$height = 144,
		$text = 'No image available',
		$textColor = '#999999',
		$backgroundColor = '#dfdfdf'
	)
	{
		if ($defaultFileName)
		{
			$fileName        = $width . 'x' . $height . '_' . (md5($defaultFileName)) . '.' . JFile::getExt(JPATH_SITE . '/' . $defaultFileName);
			$defaultFileName = JPATH_SITE . '/' . $defaultFileName;
		}
		else
		{
			$fileName = $width . 'x' . $height . '_' . md5($text . $textColor . $backgroundColor) . '.jpg';
		}

		$filePathAbs = JPATH_SITE . '/media/com_redshopb/images/default/' . $fileName;
		$filePath    = Uri::root() . 'media/com_redshopb/images/default/' . $fileName;

		$paths = array(
			'dir' => $filePathAbs,
			'url' => $filePath
		);

		return $paths;
	}

	/**
	 * Method for generate an image with text
	 *
	 * @param   integer   $width            Width of image.
	 * @param   integer   $height           Height of image
	 * @param   string    $text             Text for generate
	 * @param   string    $textColor        Color of text.
	 * @param   string    $backgroundColor  Color of background.
	 * @param   boolean   $recreate         Force re-create file.
	 *
	 * @return  string                     Path of generated image.
	 */
	public static function drawDefaultImg(
		$width = 144,
		$height = 144,
		$text = '',
		$textColor = '#999999',
		$backgroundColor = '#dfdfdf',
		$recreate = false
	)
	{
		$defaultFileName = RedshopbEntityConfig::getInstance()->get('thumbnail_default_no_image', '', 'string');

		$imgPaths	 = self::drawDefaultImgSub($defaultFileName, $width, $height, $text, $textColor, $backgroundColor);
		$filePathAbs = $imgPaths['dir'];
		$filePath	 = $imgPaths['url'];

		$altText = RedshopbHelperThumbnail::safeAlt($text);

		if (JFile::exists($filePathAbs))
		{
			if (!$recreate)
			{
				return "<img src=\"{$filePath}\" width=\"{$width}\" height=\"{$height}\" alt=\"{$altText}\" />";
			}
			else
			{
				JFile::delete($filePathAbs);
			}
		}

		if ($defaultFileName && RedshopbHelperThumbnail::makeImage($defaultFileName, $filePathAbs, $width, $height, 100, false))
		{
			return "<img src=\"{$filePath}\" width=\"{$width}\" height=\"{$height}\" alt=\"{$altText}\" />";
		}

		// Generate image
		$imagine = new Imagine\Gd\Imagine;
		$palette = new Imagine\Image\Palette\RGB;

		$backgroundColor = $palette->color($backgroundColor);
		$imageBox        = new Imagine\Image\Box($width, $height);
		$imageCenter     = new Imagine\Image\Point\Center($imageBox);

		// Calculate font-size (point). Rule: 15% height of image.
		$fontSize   = (float) ($height * 0.175 * 0.75);
		$fontPath   = JPATH_SITE . '/media/com_redshopb/fonts/Arial.ttf';
		$textColor  = $palette->color($textColor);
		$textFont   = new Imagine\Gd\Font($fontPath, $fontSize, $textColor);
		$textBox    = $textFont->box($text);
		$textCenter = new Imagine\Image\Point\Center($textBox);

		// Pre-calculate text box height
		$tmpString = '';
		$tmpRow    = 1;
		$tmpResult = '';

		foreach (explode(' ', $text) as $tmpWord)
		{
			$testString = $tmpResult . ' ' . $tmpWord;
			$testBox    = imagettfbbox($textFont->getSize(), 0, $textFont->getFile(), $testString);

			if ($testBox[2] > $width)
			{
				$tmpResult .= ($tmpResult == '' ? '' : "\n") . $tmpWord;
				$tmpRow++;
			}
			else
			{
				$tmpResult .= ($tmpResult == '' ? '' : ' ') . $tmpWord;
			}
		}

		$textboxHeight = $textBox->getHeight() * $tmpRow;

		// Clean up memory
		unset($tmpString);
		unset($tmpRow);
		unset($tmpResult);

		// Make sure text pointer not outside of image. This cause Fatal error
		$centerX = $imageCenter->getX() - $textCenter->getX();
		$centerX = ($centerX < $textFont->getSize()) ? $textFont->getSize() : $centerX;
		$centerY = $imageCenter->getY() - $textCenter->getY();
		$centerY = $centerY - ($textboxHeight / 2);
		$centerY = ($centerY < $textFont->getSize()) ? $textFont->getSize() : $centerY;

		$draweredPoint = new Imagine\Image\Point($centerX, $centerY);

		$image = $imagine->create($imageBox, $backgroundColor);
		$image->draw()->text($text, $textFont, $draweredPoint, 0, ($width - $textFont->getSize()));
		$image->save($filePathAbs);
		$altText = RedshopbHelperThumbnail::safeAlt($text);

		return "<img src=\"{$filePath}\" width=\"{$width}\" height=\"{$height}\" alt=\"{$altText}\" />";
	}

	/**
	 * Method for processing on store images in system.
	 *
	 * @param   string  $path  Image path.
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	public static function prepareStoredImage($path)
	{
		if (empty($path) || !self::checkIsImage($path))
		{
			return;
		}

		// Resize image base on configuration.
		$storedMaxWidth  = RedshopbApp::getConfig()->get('stored_max_width', null);
		$storedMaxHeight = RedshopbApp::getConfig()->get('stored_max_height', null);

		if ($storedMaxWidth || $storedMaxHeight)
		{
			// Calculate width height
			$imageInfo   = getimagesize($path);
			$origWidth   = $imageInfo[0];
			$origHeight  = $imageInfo[1];
			$widthRatio  = $storedMaxWidth / $origWidth;
			$heightRatio = $storedMaxHeight / $origHeight;

			// Just resize image if image size is larger than maximum configuration.
			if (($widthRatio > 0 && $widthRatio < 1) || ($heightRatio > 0 && $heightRatio < 1))
			{
				$ratio = 0.0;

				if ($widthRatio)
				{
					$ratio = $widthRatio;
				}

				if ($heightRatio && $heightRatio > $widthRatio)
				{
					$ratio = $heightRatio;
				}

				$newWidth  = ceil($origWidth * $ratio);
				$newHeight = ceil($origHeight * $ratio);

				RedshopbHelperThumbnail::makeImage($path, $path, $newWidth, $newHeight, null, 0, true);
			}
		}

		// Auto-optimization base on config
		if (RedshopbApp::getConfig()->get('auto_optimize_stored', 1))
		{
			// Extra file optimization - depends on binary files to work properly
			$factory   = new \ImageOptimizer\OptimizerFactory;
			$optimizer = $factory->get();
			$optimizer->optimize($path);
		}
	}
}
