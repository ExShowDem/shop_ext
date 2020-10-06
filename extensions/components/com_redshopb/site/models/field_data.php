<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Http\HttpFactory;

/**
 * Field Data Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelField_Data extends RedshopbModelAdmin
{
	/**
	 * @var  stdClass
	 */
	protected $wsItem = null;

	/**
	 * @var  integer
	 */
	protected $wsFieldId = null;

	/**
	 * @var  integer
	 */
	protected $wsItemId = null;

	/**
	 * Adds media field data
	 *
	 * @param   array   $data   Data to be stored
	 * @param   string  $scope  Scope
	 *
	 * @return integer  ID of the Field data
	 *
	 * @throws Exception
	 */
	public function addMediaFieldData($data, $scope = 'products')
	{
		$fieldDataTable = RTable::getInstance('Field_Data', 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true);
		$params         = array();
		$mediaRow       = array();

		$mediaRow['id']       = 0;
		$mediaRow['item_id']  = !empty($data['item_id']) ? $data['item_id'] : null;
		$mediaRow['field_id'] = !empty($data['field_id']) ? $data['field_id'] : null;

		// If ID is set, we load that field data
		if (!empty($data['id']))
		{
			if ($fieldDataTable->load((int) $data['id']))
			{
				$mediaRow['id'] = $fieldDataTable->id;
				$params         = json_decode($fieldDataTable->params, true);

				$mediaRow['item_id']  = !empty($mediaRow['item_id']) ? $mediaRow['item_id'] : $fieldDataTable->item_id;
				$mediaRow['field_id'] = !empty($mediaRow['field_id']) ? $mediaRow['field_id'] : $fieldDataTable->field_id;
			}
		}

		$field               = RedshopbHelperField::getFieldById((int) $mediaRow['field_id']);
		$defaultTableOptions = array_keys(get_object_vars($fieldDataTable));

		// Bind all external parameters
		foreach ($data as $paramsDataKey => $paramsDataValue)
		{
			if (!in_array($paramsDataKey, array($defaultTableOptions)))
			{
				if (is_array($paramsDataValue))
				{
					$paramsDataValue = implode(',', $paramsDataValue);
				}

				$params[$paramsDataKey] = $paramsDataValue;
			}
		}

		if (!$field)
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_REDSHOPB_FIELDS_FIELD_ID_DONT_EXIST', (int) $mediaRow['field_id']), 'error'
			);

			return 0;
		}

		$params['title']        = !empty($data['description']) ? $data['description'] : null;
		$params['external_url'] = !empty($data['media_url']) ? trim($data['media_url']) : null;

		// If not external url then we are receiving file which needs to be saved
		if (empty($params['external_url']) && !empty($data['media']))
		{
			$imageURL  = base64_decode($data['media']);
			$extension = !empty($data['media_extension']) ? $data['media_extension'] : null;

			if (empty($extension))
			{
				$mimeType  = RedshopbHelperMedia::getMimeType($imageURL);
				$split     = explode('/', $mimeType);
				$extension = $split[1];
			}

			// Get temporary path of joomla
			$joomlaConfig = Factory::getConfig();

			$tmpPath = $joomlaConfig->get('tmp_path', '');

			$fileName = md5(time()) . '.' . $extension;
			$fullPath = $tmpPath . '/' . $fileName;

			file_put_contents($fullPath, $imageURL);

			// Save image to products folder
			$params['internal_url'] = RedshopbHelperMedia::savingMedia($fullPath, $fileName, $mediaRow['id'], false, $scope, $field->type_alias);

			// Remove temporary image
			JFile::delete($fullPath);

			// Media upload failed for some reason
			if (!$params['internal_url'])
			{
				return 0;
			}
		}

		// If media name is not set we will use field name as the media name
		$mediaRow[$field->value_type] = !empty($data['name']) ? $data['name'] : $field->name;
		$mediaRow['params']           = $params;

		if (!$fieldDataTable->save($mediaRow))
		{
			throw new Exception($fieldDataTable->getError());
		}

		return $fieldDataTable->id;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!$item || is_null($item->id))
		{
			return false;
		}

		$fieldEntity = RedshopbEntityField::load($item->field_id);
		$typeEntity  = $fieldEntity->getType();

		$field = $fieldEntity->getItem();
		$type  = $typeEntity->getItem();

		$item->value          = '';
		$item->field_value_id = 0;
		$item->scope          = $field->scope;

		if ($type)
		{
			$item->value = $item->{$type->value_type};

			if ($type->value_type == 'field_value')
			{
				$item->field_value_id = (int) $item->{$type->value_type};

				if ($fieldEntity->getFieldValue($item->field_value_id))
				{
					$item->value = $fieldEntity->getFieldValue($item->field_value_id)->get('value');
				}
			}
		}

		$item->params = new Registry($item->params);
		$internalUrl  = $item->params->get('internal_url', null);

		if (!empty($internalUrl))
		{
			$item->params->set(
				'internal_url',
				Uri::root() . RedshopbHelperMedia::getFullMediaPath($internalUrl, RInflector::pluralize($field->scope), $type->alias)
			);
		}

		// Media fields binding
		$item->media_description  = $item->params->get('description');
		$item->media_internal_url = $item->params->get('internal_url', null);
		$item->media_external_url = $item->params->get('external_url');

		// Specific product id binding
		if ($field->scope == 'product')
		{
			$item->product_id = $item->item_id;
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data = array())
	{
		$field = RedshopbHelperField::getFieldById($data['field_id']);

		// If not multiple then we try to load that field first
		if (empty($data['id']) && $field->multiple_values != 1)
		{
			$fieldDataTable = RTable::getInstance('Field_Data', 'RedshopbTable');

			if ($fieldDataTable->load(array('item_id' => (int) $data['item_id'], 'field_id' => (int) $data['field_id'])))
			{
				$data['id'] = $fieldDataTable->id;
			}
		}

		// If value is set then we need to set it to corresponding field value type
		if (isset($data['value']))
		{
			if ($data['field_id'])
			{
				$type                    = RedshopbHelperField::getTypeById($field->type_id);
				$data[$type->value_type] = $data['value'];
			}
		}

		return parent::save($data);
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array|boolean
	 */
	public function validateWS($data)
	{
		// Sets the preliminary 'item_id' (it might be an external id so it will be set again later after parent::validateWS)
		if (isset($data['product_id']))
		{
			$data['item_id'] = $data['product_id'];
		}

		// Gets the field id from direct data (create/update) when available
		if (isset($data['field_id']) || $data['field_id'] == '')
		{
			$fieldItem = $this->getRelatedItemFromModel('Field', $data['field_id']);

			if (!$fieldItem)
			{
				return false;
			}

			$this->wsFieldId = $fieldItem->id;
		}

		// Gets the item (product) id from direct data (create/update) when available
		if (isset($data['product_id']) || $data['product_id'] == '')
		{
			$productItem = $this->getRelatedItemFromModel('Product', $data['product_id']);

			if (!$productItem)
			{
				return false;
			}

			$this->wsItemId = $productItem->id;
		}

		// Determines the right data to store in the value field in case the field contains a catalog of values (field_value)
		$field = RedshopbEntityField::load($this->wsFieldId);
		$type  = $field->getType();

		if ($type->value_type == 'field_value')
		{
			if (isset($data['value']))
			{
				unset($data['value']);
			}

			if (!isset($data['field_value_id']) || $data['field_value_id'] == '')
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_FIELD_VALUE_VALUE_ID_MISSING'), 'error');

				return false;
			}

			$fieldValueItem = $this->getRelatedItemFromModel('Field_Value', $data['field_value_id']);

			if (!$fieldValueItem)
			{
				return false;
			}

			$isSameFieldId  = ($fieldValueItem->field_id == $this->wsFieldId);
			$isRelatedField = ($fieldValueItem->field_id == $field->get('field_value_xref_id'));

			if (!$isSameFieldId && !$isRelatedField)
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf(
						'COM_REDSHOPB_FIELD_VALUE_VALUE_ID_NOT_MATCH',
						$data['field_value_id'],
						$this->wsFieldId
					),
					'error'
				);

				return false;
			}

			$productFieldData = $field->getFieldData('product', $this->wsItemId);

			$action = $this->getWSAction();

			if ('update' !== $action && !empty($productFieldData))
			{
				foreach ($productFieldData as $productFieldDataData)
				{
					if ($productFieldDataData->field_value_id == $fieldValueItem->id)
					{
						Factory::getApplication()->enqueueMessage(
							Text::sprintf('COM_REDSHOPB_FIELD_VALUE_VALUE_ALREADY_ADDED', $this->wsFieldId, $data['field_value_id']), 'error'
						);

						return false;
					}
				}
			}

			$data['value'] = $fieldValueItem->id;
		}

		$data = parent::validateWS($data);

		if (!$data)
		{
			return false;
		}

		// Sets the final 'item_id'
		if (isset($data['product_id']))
		{
			$data['item_id'] = $data['product_id'];
		}

		return $data;
	}

	/**
	 * Method to get a related item via RedshopbModelAdmin::getItemWS by data value
	 *
	 * @param   string  $modelName  name of the model instance to use
	 * @param   int     $dataValue  id value provided in the data
	 *
	 * @return boolean|mixed the related item record or false
	 */
	protected function getRelatedItemFromModel($modelName, $dataValue)
	{
		/** @var RedshopbModelAdmin $model */
		$model       = RedshopbModelAdmin::getFrontInstance($modelName);
		$relatedItem = $model->getItemWS($dataValue);

		if (!$relatedItem)
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf(
					'COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND',
					$dataValue
				),
				'error'
			);

			return false;
		}

		return $relatedItem;
	}

	/**
	 * Validate incoming data from the web service for creation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateCreateWS($data)
	{
		$data = parent::validateCreateWS($data);

		if (!$data)
		{
			return false;
		}

		$field = RedshopbEntityField::load($data['field_id']);

		if ($field->getItem()->multiple_values != 1
			&& count($field->getFieldData('product', $data['product_id'])))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_FIELD_ITEM_ALREADY_FILLED', $field->getItem()->scope), 'error');

			return false;
		}

		if (!empty($data['media']) && !empty($data['media_external_url']))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_FIELD_DATA_ERROR_MEDIA_SOURCE_CONFLICT'), 'error');

			return false;
		}

		$type          = $field->getType();
		$requiresMedia = (in_array($type->alias, array('videos', 'documents', 'field-images', 'files')));
		$hasMedia      = (!empty($data['media']) || !empty($data['media_external_url']));

		if ($requiresMedia && !$hasMedia)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_FIELD_DATA_ERROR_MEDIA_SOURCE_REQUIRED'), 'error');

			return false;
		}

		if (!empty($data['media']))
		{
			$data['file'] = $this->getTempFile($data['media']);
		}

		if (!empty($data['media_external_url']))
		{
			if (empty($data['params']) || !is_array($data['params']))
			{
				$data['params'] = array();
			}

			$data['params']['external_url'] = $data['media_external_url'];
		}

		if (!empty($data['media_description']))
		{
			if (empty($data['params']))
			{
				$data['params'] = array();
			}

			$data['params']['description'] = $data['media_description'];
		}

		return $data;
	}

	/**
	 * Method to upload an external URL to the server.
	 *
	 * @param   string  $url  the full URL of to be imported
	 *
	 * @return array
	 */
	private function getTempFile($url)
	{
		// Make the request
		$request  = HttpFactory::getHttp();
		$response = $request->get($url);

		// Save in a temporary file
		$tempfile = tempnam(sys_get_temp_dir(), 'php');
		file_put_contents($tempfile, $response->body);

		// Now forge the $__FILE info so we can save it with the ID when the time comes.
		$parts            = explode('/', $url);
		$file             = array();
		$file['name']     = array_pop($parts);
		$file['type']     = $response->headers['Content-Type'];
		$file['tmp_name'] = $tempfile;
		$file['error']    = (int) ($response->code != 200);
		$file['size']     = $response->headers['Content-Length'];

		return $file;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array|boolean
	 */
	public function validateUpdateWS($data)
	{
		// Gets the field data item for later processing
		$fieldDataModel = RedshopbModelAdmin::getFrontInstance('Field_Data');
		$this->wsItem   = $fieldDataModel->getItemWS($data['id'], false);

		if (!$this->wsItem || $this->wsItem->scope != 'product')
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data['id']), 'error');

			return false;
		}

		// Field and item (product) ids for later processing
		$this->wsFieldId = $this->wsItem->field_id;
		$this->wsItemId  = $this->wsItem->item_id;

		$hasMedia = (!empty($data['media']) || !empty($data['media_external_url']));

		if (!$hasMedia)
		{
			return parent::validateUpdateWS($data);
		}

		if (!empty($data['media']))
		{
			$data['file'] = $this->getTempFile($data['media']);

			$this->clearOldMedia($data);
		}

		if (!empty($data['media_external_url']))
		{
			if (empty($data['params']) || !is_array($data['params']))
			{
				$data['params'] = array();
			}

			$data['params']['external_url'] = $data['media_external_url'];
		}

		if (!empty($data['media_description']))
		{
			if (empty($data['params']))
			{
				$data['params'] = array();
			}

			$data['params']['description'] = $data['media_description'];
		}

		return parent::validateUpdateWS($data);
	}

	/**
	 * Method to remove media when an external source is provided in Update
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return void
	 */
	private function clearOldMedia($data)
	{
		$fieldData = RedshopbEntityField_Data::load($data['id']);
		$oldMedia  = $fieldData->getMediaData('internal_url');

		$field = $fieldData->getField();
		$type  = $field->getType();

		$section = RInflector::pluralize($field->scope);
		$media   = $type->alias;

		// Clean up old media
		if (!empty($oldMedia))
		{
			RedshopbHelperMedia::deleteMedia($oldMedia, 1, $section, $media);
		}
	}
}
