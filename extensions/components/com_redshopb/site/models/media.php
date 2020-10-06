<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
/**
 * Media Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMedia extends RedshopbModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed         Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);
		$item       = ArrayHelper::toObject($properties, CMSObject::class);

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		// Gets the image URL
		if ($item->id && !empty($item->name))
		{
			$increment      = RedshopbHelperMedia::getIncrementFromFilename($item->name);
			$folderName     = RedshopbHelperMedia::getFolderName($increment);
			$item->imageurl = Uri::root() . 'media/com_redshopb/images/originals/products/' . $folderName . '/' . $item->name;
		}

		return $item;
	}

	/**
	 * Method to get a query list for same type
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getImageQuery()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('m.*, pav.string_value AS color_name')
			->from($db->qn('#__redshopb_media', 'm'))
			->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = m.attribute_value_id')
			->order($db->qn('m.ordering') . ' ASC')
			->order('pav.ordering');

		return $query;
	}

	/**
	 * Delete specific image
	 *
	 * @param   int  $imageID  Image id
	 *
	 * @return  object
	 */
	public function deleteImage($imageID)
	{
		$ret   = new stdClass;
		$table = RedshopbTable::getInstance('Media', 'RedshopbTable');
		$table->load((int) $imageID);
		$mediaName = $table->name;

		$success = $table->delete($imageID);

		if ($success && $mediaName != '')
		{
			RedshopbHelperThumbnail::deleteImage($mediaName, 1, 'products', $table->remote_path);
		}

		$ret->success = $success;

		// Deletes the main table sync items
		$wsMap = $table->get('wsSyncMapPK');

		if (count($wsMap))
		{
			$wsRefs     = array();
			$syncHelper = new RedshopbHelperSync;

			foreach ($wsMap as $wsSyncMapFieldsRefs)
			{
				$wsRefs = array_merge($wsRefs, $wsSyncMapFieldsRefs);
			}

			foreach ($wsRefs as $wsRef)
			{
				$syncData = $syncHelper->findSyncedLocalId($wsRef, $imageID, true);

				if ($syncData)
				{
					$syncHelper->deleteSyncedId($wsRef, $syncData->remote_key, '', $syncData->main_reference);
				}
			}
		}

		return $ret;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @throws Exception
	 *
	 * @return  object True on success, False on error.
	 */
	public function save($data)
	{
		jimport('joomla.filesystem.file');
		$ret             = new stdClass;
		$ret->updatedRow = '';
		$ret->message    = '';
		$ret->id         = 0;
		$clearRemotePath = false;

		// Image file temporary storage
		$files = Factory::getApplication()->input->files->get('jform', array(), 'array');

		if (isset($data['image_file']))
		{
			// File from web service
			$files = $data['image_file'];
			unset($data['image_file']);
		}

		$file                                  = null;
		$success                               = true;
		RedshopbHelperThumbnail::$displayError = false;

		try
		{
			if (isset($data['attribute_value_id']) && $data['attribute_value_id'] == '')
			{
				unset($data['attribute_value_id']);
			}

			if (count($files) > 0)
			{
				$file = $files['productImage'][0];

				if (!RedshopbHelperThumbnail::checkFileError($file['name'], $file['error'])
					|| !RedshopbHelperMedia::checkExtension($file['name'])
					|| !RedshopbHelperMedia::checkIsImage($file['tmp_name']))
				{
					throw new Exception(RedshopbHelperThumbnail::getError());
				}
			}

			$table        = RedshopbTable::getInstance('Media', 'RedshopbTable');
			$now          = Date::getInstance();
			$nowFormatted = $now->toSql();

			if ((int) $data['id'] > 0)
			{
				if ($table->isLockedByWebservice((int) $data['id']))
				{
					throw new Exception(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'));
				}

				// Former data
				$success = $table->load((int) $data['id']);
				$table->loadWebserviceRelation((int) $data['id']);

				// Delete old if exists
				if ($success
					&& isset($file['name']))
				{
					$clearRemotePath = true;
					RedshopbHelperThumbnail::deleteImage($table->get('name'), 1, 'products', $table->remote_path);
				}

				// If user clear the alternative text. Auto-generate this
				if (empty($data['alt']))
				{
					$product = RedshopbEntityProduct::getInstance($data['product_id']);

					if (empty($data['attribute_value_id']))
					{
						$data['alt'] = $product->get('name');
					}
					else
					{
						$attribute = RedshopbTable::getInstance('Product_Attribute_Value', 'RedshopbTable');
						$attribute->load((int) $data['attribute_value_id']);

						$data['alt'] = $product->get('name') . ' ' . $attribute->string_value;
					}
				}
			}
			else
			{
				$data['created_date'] = $nowFormatted;

				if (!$table->save($data))
				{
					throw new Exception($table->getError());
				}

				$data['id'] = $table->id;
			}

			// Store file.
			if ($success && isset($file['name']))
			{
				// Saving image
				$data['name'] = RedshopbHelperThumbnail::savingImage((string) $file['tmp_name'], (string) $file['name'], $table->id, false);

				if ($data['name'] === false)
				{
					$table->delete();
					throw new Exception(RedshopbHelperThumbnail::getError());
				}
			}

			// Process on media alternative name
			if (!isset($data['alt']) || !$data['alt'])
			{
				$product = RedshopbEntityProduct::getInstance($data['product_id']);

				if (empty($data['attribute_value_id']))
				{
					$data['alt'] = $product->get('name');
				}
				else
				{
					$attribute = RedshopbTable::getInstance('Product_Attribute_Value', 'RedshopbTable');
					$attribute->load((int) $data['attribute_value_id']);

					$data['alt'] = $product->get('name') . ' ' . $attribute->string_value;
				}
			}

			if ($success)
			{
				if ($clearRemotePath)
				{
					$table->remote_path = null;
				}

				if (!$table->save($data))
				{
					throw new Exception($table->getError());
				}
			}

			// Delete tmp image
			if (JFile::exists((string) $file['tmp_name']))
			{
				JFile::delete((string) $file['tmp_name']);
			}

			$ret->success = $success;

			if ($success)
			{
				$ret->id = $table->get('id');
				Factory::getApplication()->input->set('id', $ret->id);
				$image           = RedshopbEntityMedia::load($ret->id);
				$image->viewName = $image->getViewName();
				$ret->updatedRow = RedshopbLayoutHelper::render('media.edit.imagerow', array(
						'item' => $image->getItem(),
					)
				);
				$ret->image      = RedshopbHelperThumbnail::originalToResize(
					$image->get('name'), 144, 144, 100, 0, 'products', false, $image->remote_path
				);
				$ret->alt        = $image->get('alt');
				$ret->editLayout = RedshopbLayoutHelper::render('media.edit.image', array(
						'form'        => $this->getForm(),
						'item'        => $this->getItem(),
						'formName'    => 'productImageForm',
						'showToolbar' => false,
					)
				);
			}
		}
		catch (Exception $e)
		{
			// Delete tmp image
			if (is_array($file) && isset($file['tmp_name']) && JFile::exists($file['tmp_name']))
			{
				JFile::delete($file['tmp_name']);
			}

			$ret->success = false;
			$ret->message = $e->getMessage();
		}

		// Sets the regular id set by the model when creating a new row
		if (!is_null($ret) && isset($ret->id))
		{
			$this->getState();
			$this->setState($this->getName() . '.id', $ret->id);

			// Saves the optional sync related id
			if (!$this->saveSyncRelatedId($data))
			{
				$ret->success = false;
			}
		}

		return $ret;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$data = parent::validate($form, $data, $group);

		if (!$data)
		{
			return false;
		}

		if (isset($data['view'])
			&& !($data['view'] == 1 || $data['view'] == 2 || $data['view'] == 0))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_MEDIA_IMAGE_VIEW_INVALID'), 'error');

			return false;
		}

		return $data;
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateWS($data)
	{
		// If uploading an image via base64, the 'image' field is ignored
		if (isset($data['image']) && !empty($data['image_upload']))
		{
			unset($data['image']);
		}

		// Creates an temp image so it can be saved by the model
		$data = RedshopbHelperWebservices::getTempImageURL($data, 'productImage', true, isset($data['image_upload']) ? $data['image_upload'] : '');

		if (isset($data['image']))
		{
			unset($data['image']);
		}

		if (!$data)
		{
			return false;
		}

		if (isset($data['product_attribute_value_id']))
		{
			$data['attribute_value_id'] = $data['product_attribute_value_id'];
			unset($data['product_attribute_value_id']);
		}

		// Optional enrichment
		$syncReference = RedshopbHelperSync::getEnrichmentBase($this);

		if ($syncReference != '')
		{
			if (isset($data['related_id']))
			{
				// Leaves related id all the same if it's not sent
				if (isset($data['id']) && $data['id'] > 0 && $data['related_id'] == '')
				{
					$sync                    = new RedshopbHelperSync;
					$data['sync_related_id'] = $sync->findSyncedLocalId($syncReference, $data['id']);
				}
				else
				{
					$data['sync_related_id'] = $data['related_id'];
					unset($data['related_id']);
				}

				if ($data['sync_related_id'] == 'null')
				{
					$data['sync_related_id'] = '';
				}
			}
			else
			{
				$sync                    = new RedshopbHelperSync;
				$data['sync_related_id'] = $sync->findSyncedLocalId($syncReference, $data['id']);
			}
		}

		return parent::validateWS($data);
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed               A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->context . '.' . $this->formName, $this->formName,
			array(
				'control'   => ArrayHelper::getValue($data, 'control', 'jform'),
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}
}
