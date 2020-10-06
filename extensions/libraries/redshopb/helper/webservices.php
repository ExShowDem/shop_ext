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

/**
 * Represents a menu node (link).
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Menu
 * @since       1.0
 */
final class RedshopbHelperWebservices
{
	/**
	 * @var boolean
	 */
	protected static $savedTempFolder = false;

	/**
	 * Adds WS data to the query using a specific field and mapping
	 *
	 * @param   JDatabaseQuery  $query           The input query from getListQuery function
	 * @param   array           $fields          Field names to map - a field can be an array of field => alias
	 * @param   array<array>    $wsMaps          Web service mappings (array corresponding to each field)
	 * @param   string          $tablePrefix     Prefix in use for the table being queried
	 * @param   integer         $start           Limit start (for pagination)
	 * @param   integer         $limit           Record limit
	 * @param   string          $order           Order by field
	 * @param   string          $orderDirection  Order by direction
	 *
	 * @return  void
	 */
	public static function addWSDataQuery(&$query, $fields, $wsMaps, $tablePrefix, $start = 0, $limit = 1, $order = '', $orderDirection = '')
	{
		$db = Factory::getDbo();

		// Array to store the final field names
		$fieldNames = array();

		// Array to store the field aliases: an alias of the field when it exists or the field with table prefix
		$fieldAliases = array();

		foreach ($fields as $field)
		{
			if (is_array($field))
			{
				$fieldName  = array_keys($field)[0];
				$fieldAlias = array_values($field)[0];
			}
			else
			{
				$fieldName  = $field;
				$fieldAlias = $tablePrefix . '.' . $field;
			}

			$query->select('CONCAT(' . $db->qn($fieldAlias) . ') AS ' . $db->qn($fieldName . '_str'));

			$fieldNames[]   = $fieldName;
			$fieldAliases[] = $fieldAlias;
		}

		$queryLimit = ($limit > 0 ? ' LIMIT ' . $start . ', ' . $limit : '');

		$query2 = clone $query;
		$query  = $db->getQuery(true);

		$query->select('qorig.*')
			->from(
				'(' . $query2 . $queryLimit . ') AS ' . $db->qn('qorig')
			);

		$pos = 0;

		foreach ($wsMaps as $wsMap)
		{
			if (count($wsMap))
			{
				$j = 0;

				foreach ($wsMap as $source => $sourceMap)
				{
					$select = array();

					foreach ($sourceMap as $syncMap)
					{
						$j++;
						$select[] = 'CONCAT(' . $db->q($source) . ', ' . $db->q('.') . ', '
									. $db->qn('sync_' . $fieldNames[$pos] . '_' . $j . '.remote_key') . ')';
						$query->join(
							'left',
							$db->qn('#__redshopb_sync', 'sync_' . $fieldNames[$pos] . '_' . $j)
							. ' FORCE INDEX (' . $db->qn('idx_reference_local_id') . ') ON ' .
							$db->qn('qorig.' . $fieldNames[$pos] . '_str') . ' = ' .
							$db->qn('sync_' . $fieldNames[$pos] . '_' . $j . '.local_id') . ' AND ' .
							$db->qn('sync_' . $fieldNames[$pos] . '_' . $j . '.reference') . ' = ' .
							$db->q($syncMap)
						);
					}

					if (count($select) > 1)
					{
						$selectAdd = '';

						foreach ($select as $k => $selectLine)
						{
							if ($k < count($select) - 1)
							{
								if ($k)
								{
									$selectAdd .= ', ';
								}

								$selectAdd .= 'IFNULL(';
							}
							else
							{
								$selectAdd .= ', ';
							}

							$selectAdd .= $selectLine;

							if ($k == count($select) - 1)
							{
								$selectAdd .= str_repeat(')', count($select) - 1);
							}
						}
					}
					else
					{
						$selectAdd = $select[0];
					}

					$query->select($selectAdd . ' AS ' . $db->qn($fieldNames[$pos] . '_' . $source));
				}
			}

			$pos++;
		}

		// Adds ordering criteria to the final query, removing the prefix since it will be for the final query
		if ($order != '')
		{
			if (strpos($order, '.'))
			{
				$order = 'qorig.' . explode('.', $order)[1];
			}

			$query->order($order, $orderDirection);
		}
	}

	/**
	 * Sets ws items from related sync data
	 *
	 * @param   object  $item   The item to be added data to
	 * @param   string  $field  Field name to map
	 * @param   array   $wsMap  Web service mapping
	 *
	 * @return  void
	 */
	public static function addWSItemData(&$item, $field, $wsMap)
	{
		$item->{$field . '_syncref'} = array();

		if (count($wsMap))
		{
			foreach ($wsMap as $source => $sourceMap)
			{
				if (isset($item->{$field . '_' . $source}) && $item->{$field . '_' . $source} != '')
				{
					$item->{$field . '_syncref'}[] = $item->{$field . '_' . $source};
				}
			}
		}
	}

	/**
	 *  Validate web service data for a fk value
	 *
	 * @param   array   $data      Data to be validated
	 * @param   string  $external  External to validate (i.e. "category")
	 *
	 * @return  array|false
	 */
	public static function validateExternalId($data, $external)
	{
		$external      = strtolower($external);
		$externalModel = RedshopbModel::getFrontInstance(ucfirst($external));

		if (!isset($data[$external . '_id']) || $data[$external . '_id'] == '')
		{
			return false;
		}

		$item = $externalModel->getItemFromWSData($data[$external . '_id']);

		if ($item)
		{
			$data[$external . '_id'] = $item->id;
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data[$external . '_id']), 'error');

			return false;
		}

		return $data;
	}

	/**
	 *  Saves the image in $data['image'] to a tmp file and returns it in $data array as $data['image_file'] in the format of an uploaded file
	 *
	 * @param   array   $data              Data to be validated
	 * @param   string  $fieldName         Name of the expected input field with the image
	 * @param   bool    $simulateMultiple  Simulates a multiple file input array
	 * @param   string  $imageUploadData   Data of the uploaded image (in this case a field is not expected in the array, but the actual data
	 *
	 * @return  array|false
	 */
	public static function getTempImageURL($data, $fieldName = 'imageFileUpload', $simulateMultiple = false, $imageUploadData = '')
	{
		$app = Factory::getApplication();

		$imageRef  = Text::_('COM_REDSHOPB_WEBSERVICE_IMAGE_UPLOADED');
		$imageData = base64_decode($imageUploadData);
		$imageName = RFilesystemFile::getUniqueName();

		// If no data is sent, resets the 'image' field and continues regular operation
		if (empty($data['image']) && empty($imageUploadData))
		{
			if (isset($data['image']))
			{
				unset($data['image']);
			}

			return $data;
		}

		if (!empty($data['image']))
		{
			$url  = $data['image'];
			$aURL = parse_url($data['image']);

			// If the URL path needs to, it encodes it
			if (!empty($aURL['path']) && str_replace('%2F', '/', rawurlencode(rawurldecode($aURL['path']))) != $aURL['path'])
			{
				$url = $aURL['scheme'] . '://' .
					(!empty($aURL['username']) && !empty($aURL['password']) ? $aURL['username'] . ':' . $aURL['password'] . '@' : '') .
					$aURL['host'] .
					($aURL['port'] != '80' || $aURL['port'] != '443' ? ':' . $aURL['port'] : '') .
					(!empty($aURL['path']) ? str_replace('%2F', '/', rawurlencode($aURL['path'])) : '') .
					(!empty($aURL['query']) ? '?' . $aURL['query'] : '') .
					(!empty($aURL['fragment']) ? '#' . $aURL['fragment'] : '');
			}

			$imageRef = Text::sprintf('COM_REDSHOPB_WEBSERVICE_IMAGE_URL_NAME', $data['image']);

			// Forcing HTTP protocol v.1.1
			$context   = stream_context_create(
				array(
					'http' => array(
						'protocol_version' => '1.1',
						'timeout' => 1
					)
				)
			);
			$imageData = file_get_contents($url, false, $context);
			$imageName = '';
		}

		if (!$imageData || $imageData == '')
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_IMAGE_NOT_LOADED', $imageRef), 'error');

			return false;
		}

		$mimeType = RedshopbHelperMedia::getMimeType($imageData);
		$split    = explode('/', $mimeType);

		if (!isset($split[1]))
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_IMAGE_TYPE_ERROR', $imageRef), 'error');

			return false;
		}

		if ($imageName == '')
		{
			$imageName = OutputFilter::stringURLSafe(JFile::stripExt(basename($data['image'])));
		}

		$imageExtension = $split[1] == 'octet-stream' ? JFile::getExt($data['image']) : $split[1];
		$imageName     .= '.' . $imageExtension;

		if (!RedshopbHelperMedia::checkExtension($imageName))
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_IMAGE_TYPE_ERROR', $imageRef), 'error');

			return false;
		}

		$imagePath = self::saveTempImage($imageData, $imageName);

		if (!$imagePath)
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_IMAGE_TYPE_ERROR', $imageRef), 'error');

			return false;
		}

		if (isset($data['image']))
		{
			unset($data['image']);
		}

		// Saves image file in a similar format as file upload to reuse the functions in save() model function
		if ($simulateMultiple)
		{
			$data['image_file'] = array(
				$fieldName => array (
					array (
						'name' => $imageName,
						'tmp_name' => $imagePath
					)
				)
			);

			return $data;
		}

		$data['image_file'] = array(
			$fieldName => array (
				'name' => $imageName,
				'tmp_name' => $imagePath
			)
		);

		return $data;
	}

	/**
	 *  Saves the image in to a tmp file
	 *
	 * @param   string  $imageData  Image data
	 * @param   string  $imageName  Image name to save the file
	 *
	 * @return  string|false
	 */
	public static function saveTempImage($imageData, $imageName = '')
	{
		$tmpPath = Factory::getApplication()->get('tmp_path') . '/com_redshopb';

		if (!self::$savedTempFolder)
		{
			self::$savedTempFolder = $tmpPath . '/' . uniqid();

			while (JFolder::exists(self::$savedTempFolder))
			{
				null;
			}

			JFolder::create(self::$savedTempFolder);
			register_shutdown_function(
				function ()
				{
					RedshopbHelperWebservices::deleteTmpFiles();
				}
			);
		}

		$imagePath = self::$savedTempFolder . '/' . $imageName;
		$fp        = fopen($imagePath, 'x');

		if ($fp)
		{
			fwrite($fp, $imageData);
			fclose($fp);
		}

		if (!RedshopbHelperMedia::checkIsImage($imagePath))
		{
			return false;
		}

		RedshopbHelperMedia::prepareStoredImage($imagePath);

		return $imagePath;
	}

	/**
	 * Delete temp files on shutdown
	 *
	 * @return void
	 */
	public static function deleteTmpFiles()
	{
		JFolder::delete(self::$savedTempFolder);
	}
}
