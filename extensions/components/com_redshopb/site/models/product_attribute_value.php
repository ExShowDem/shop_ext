<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Product Attribute Value Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Attribute_Value extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		$attributeId = $form->getValue('product_attribute_id', 0);

		$attribute = RedshopbEntityProduct_Attribute::getInstance($attributeId);

		if ($attribute->isConversionSet())
		{
			/*
			 * Why ? *
			 * $form->setFieldAttribute('value', 'required', false);
			 * $form->setFieldAttribute('value', 'type', 'hidden');
			 */
			$form->removeField('value');
			$form->setFieldAttribute('sku', 'required', false);
			$form->setFieldAttribute('sku', 'disabled', true);
		}

		return $form;
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
		$attributeId = (int) $data['product_attribute_id'];

		$attribute = RedshopbEntityProduct_Attribute::getInstance($attributeId);

		if ($attribute->isConversionSet())
		{
			$form->setFieldAttribute('value', 'required', false);
			$form->setFieldAttribute('sku', 'required', false);
		}

		return parent::validate($form, $data, null);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		/** @var RedshopbTableProduct_Attribute_Value $table */
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		if ($table->load($pk))
		{
			$isNew  = false;
			$oldRow = $table->getProperties();
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Include the content plugins for the on save events.
		PluginHelper::importPlugin('content');
		$dispatcher = RFactory::getDispatcher();

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));

		if (!$isNew
			&& !empty($oldRow)
			&& ($oldRow['state'] != $table->state || $oldRow['ordering'] != $table->ordering))
		{
			$table->reorder($this->getReorderConditions($table));
		}

		$this->setState($this->getName() . '.id', $table->id);
		$attribute = RedshopbEntityProduct_Attribute::getInstance($data['product_attribute_id']);

		if ($attribute->isConversionSet() && isset($data['product_attribute_value_conversion']))
		{
			$conversionValueTable = RTable::getAdminInstance('Product_Attribute_Value_Conversion_Xref', array('ignore_request' => true));
			$productValueId       = $this->getState($this->getName() . '.id');

			foreach ($data['product_attribute_value_conversion'] as $conversionId => $conversionValue)
			{
				$conversionData = array(
					'value_id'          => $productValueId,
					'conversion_set_id' => $conversionId,
					'value'             => $conversionValue
				);

				$conversionValueTable->save($conversionData);
			}
		}

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'product_attr_value'))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method for upload image from URL for products attribute
	 *
	 * @param   int     $id           ID of product
	 * @param   string  $imageURL     Image data encoded by Base64
	 * @param   string  $extension    Image extension
	 * @param   string  $imageUpload  Image upload flag
	 * @param   string  $folder       Image upload folder
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function uploadMediaImage($id, $imageURL, $extension, $imageUpload, $folder)
	{
		// Get temporary path of joomla
		$joomlaConfig = Factory::getConfig();

		$tmpPath = $joomlaConfig->get('tmp_path', '');

		$fileName = md5(time()) . '.' . $extension;
		$fullPath = $tmpPath . '/' . $fileName;

		if ($imageUpload == 0)
		{
			$imageURL = file_get_contents($imageURL);
		}

		file_put_contents($fullPath, $imageURL);

		// Save image to products folder
		$image = RedshopbHelperThumbnail::savingImage($fullPath, $fileName, $id, false, $folder);

		// Remove temporary image
		JFile::delete($fullPath);

		return $image;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 */
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'product_attribute_id = ' . (int) $table->product_attribute_id;
		$condition[] = 'state >= 0';

		return $condition;
	}

	/**
	 * Method to add conversion data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function conversionAdd($data)
	{
		$this->operationWS = true;

		$conversionXrefTable = RedshopbTable::getAdminInstance('Product_Attribute_Value_Conversion_Xref')
			->setOption('lockingMethod', 'Webservice');
		$table               = $this->getTable();
		$valueId             = $data['id'];

		if (!$table->load($valueId))
		{
			return false;
		}

		if ($data['image'] != '')
		{
			if ($conversionXrefTable->get('image') != '')
			{
				RedshopbHelperThumbnail::deleteImage($conversionXrefTable->get('image'), 1, 'conv_product_attr_value');
			}

			$extension = JFile::getExt($data['image']);
			$image     = $this->uploadMediaImage($valueId, $data['image'], $extension, 0, 'conv_product_attr_value');

			if ($image == '')
			{
				return false;
			}

			$data['image'] = $image;
		}

		$data['value_id'] = $valueId;

		if (!$conversionXrefTable->save($data))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to remove conversion data.
	 *
	 * @param   int  $attrId        The product attribute value id.
	 * @param   int  $conversionId  The conversion id
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function conversionRemove($attrId, $conversionId)
	{
		$this->operationWS = true;

		$conversionXrefTable = RedshopbTable::getAdminInstance('Product_Attribute_Value_Conversion_Xref')
			->setOption('lockingMethod', 'Webservice');

		if (!$conversionXrefTable->load(
			array(
							'value_id' => $attrId,
							'conversion_set_id' => $conversionId
						)
		))
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__redshopb_product_attribute_value_conv_xref'))
			->where(
				array(
					'value_id' => $attrId,
					'conversion_set_id' => $conversionId
				)
			);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to add image to conversion data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function conversionImageUpload($data)
	{
		$this->operationWS = true;

		$conversionXrefTable = RedshopbTable::getAdminInstance('Product_Attribute_Value_Conversion_Xref')
			->setOption('lockingMethod', 'Webservice');

		if (!$conversionXrefTable->load(
			array(
							'value_id' => $data['id'],
							'conversion_set_id' => $data['conversion_set_id']
						)
		))
		{
			return false;
		}

		if ($data['image'] == '')
		{
			return false;
		}

		if ($conversionXrefTable->get('image') != '')
		{
			RedshopbHelperThumbnail::deleteImage($conversionXrefTable->get('image'), 1, 'conv_product_attr_value');
		}

		$imageURL  = base64_decode($data['image']);
		$mimeType  = RedshopbHelperMedia::getMimeType($imageURL);
		$split     = explode('/', $mimeType);
		$extension = $split[1];

		$image = $this->uploadMediaImage($conversionXrefTable->value_id, $imageURL, $extension, 1, 'conv_product_attr_value');

		if ($image == '')
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update('#__redshopb_product_attribute_value_conv_xref')
			->set($db->quoteName('image') . ' = ' . $db->q($image))
			->where(
				array(
					'value_id' => $data['id'],
					'conversion_set_id' => $data['conversion_set_id']
				)
			);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to remove image from conversion data.
	 *
	 * @param   int  $attrId        The product attribute value id.
	 * @param   int  $conversionId  The conversion id
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function conversionImageRemove($attrId, $conversionId)
	{
		$this->operationWS = true;

		$conversionXrefTable = RedshopbTable::getAdminInstance('Product_Attribute_Value_Conversion_Xref')
			->setOption('lockingMethod', 'Webservice');

		if (!$conversionXrefTable->load(
			array(
							'value_id' => $attrId,
							'conversion_set_id' => $conversionId
						)
		))
		{
			return false;
		}

		// Delete old if exists
		$image = $conversionXrefTable->get('image');

		if (!empty($image))
		{
			RedshopbHelperThumbnail::deleteImage($image, 1, 'conv_product_attr_value');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update('#__redshopb_product_attribute_value_conv_xref')
			->set($db->quoteName('image') . ' = ' . $db->q(''))
			->where(
				array(
					'value_id' => $attrId,
					'conversion_set_id' => $conversionId
				)
			);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for remove an image from product attribute
	 *
	 * @param   int  $id  ID of category
	 *
	 * @return  boolean   True on success. False otherwise.
	 */
	public function imageRemove($id)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$id = (int) $id;

		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		// Delete old if exists
		$image = $table->get('image');

		if (!empty($image))
		{
			RedshopbHelperThumbnail::deleteImage($image, 1, 'product_attr_value');
		}

		$table->image = '';

		return $table->store();
	}

	/**
	 * Method for upload image for product attribute
	 *
	 * @param   int     $id         ID of product attribute value
	 * @param   string  $imageData  Image data (base64 encoded) encoded by Base64
	 *
	 * @return  boolean             True on success. False otherwise.
	 */
	public function imageUploadLegacy($id, $imageData)
	{
		$id = (int) $id;

		if (!$id || empty($imageData))
		{
			return false;
		}

		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		// Delete old if exists
		if ($table->get('image') != '')
		{
			RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, 'product_attr_value');
		}

		$imageData = base64_decode($imageData);
		$mimeType  = RedshopbHelperMedia::getMimeType($imageData);
		$split     = explode('/', $mimeType);
		$extension = $split[1];

		$image = $this->uploadMediaImage($id, $imageData, $extension, 1, 'product_attr_value');

		if ($image == '')
		{
			return false;
		}

		$table->image = $image;

		if (!$table->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * unpublish a product attribute value
	 *
	 * @param   integer  $id  The product attribute value id
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function unpublish($id)
	{
		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		$table->id    = $id;
		$table->state = 0;

		if (!$table->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * reorder a product attribute value
	 *
	 * @param   integer  $id        The product attribute value id
	 * @param   integer  $ordering  ordering
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function webserviceReorder($id, $ordering)
	{
		$this->operationWS = true;

		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		$table->id       = $id;
		$table->ordering = $ordering;

		if (!$table->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for translate product attribute
	 *
	 * @param   int     $id            ID of product attribute value
	 * @param   string  $languageCode  Code of language
	 * @param   string  $value         Translation name of product attribute value
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function translateLegacy($id, $languageCode, $value)
	{
		if (!$id || empty($languageCode) || empty($value))
		{
			return false;
		}

		$checkLang = RedshopbHelperTranslations::checkLanguageAvailable($languageCode);

		if (!$checkLang)
		{
			return false;
		}

		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		$translationTables = RTranslationHelper::getInstalledTranslationTables();

		// Check existing translate table
		if (!isset($translationTables['#__redshopb_product_attribute_value']))
		{
			return false;
		}

		$translationTable = $translationTables['#__redshopb_product_attribute_value'];

		$result = RedshopbHelperTranslations::storeTranslation(
			$translationTable,
			$table,
			$languageCode,
			array (
				'id'          => (int) $table->id,
				'string_value' => (string) $value
			)
		);

		if ($result !== true)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for remove translation of a product attribute value
	 *
	 * @param   int     $id            ID of product attribute value
	 * @param   string  $languageCode  Language code
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function translateRemoveLegacy($id, $languageCode)
	{
		$id = (int) $id;

		if (!$id || empty($languageCode))
		{
			return false;
		}

		$db = Factory::getDbo();

		$conditions = array(
			$db->qn('id') . ' = ' . $id,
			$db->qn('rctranslations_language') . ' = ' . $db->quote($languageCode)
		);

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__redshopb_product_attribute_value_rctranslations'))
			->where($conditions);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method for translate product attribute conversion
	 *
	 * @param   int     $id            ID of product attribute value
	 * @param   int     $conversionId  Conversion Id of product attribute value
	 * @param   string  $languageCode  Code of language
	 * @param   string  $value         Translation name of product attribute value
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function translateConversion($id, $conversionId, $languageCode, $value)
	{
		if (!$id || empty($languageCode) || empty($value))
		{
			return false;
		}

		$checkLang = RedshopbHelperTranslations::checkLanguageAvailable($languageCode);

		if (!$checkLang)
		{
			return false;
		}

		$table = RedshopbTable::getAdminInstance('Product_Attribute_Value_Conversion_Xref');

		if (!$table->load(
			array(
				'value_id' => $id,
				'conversion_set_id' => $conversionId
				)
		))
		{
			return false;
		}

		$translationTables = RTranslationHelper::getInstalledTranslationTables();

		// Check existing translate table
		if (!isset($translationTables['#__redshopb_product_attribute_value_conv_xref']))
		{
			return false;
		}

		$translationTable = $translationTables['#__redshopb_product_attribute_value_conv_xref'];

		$result = RedshopbHelperTranslations::storeTranslation(
			$translationTable,
			$table,
			$languageCode,
			array (
				'value_id'          => (int) $id,
				'conversion_set_id' => (int) $conversionId,
				'value' => (string) $value
			)
		);

		if ($result !== true)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for remove translation of a product attribute value conversion
	 *
	 * @param   int     $id            ID of product attribute value
	 * @param   int     $conversionId  Conversion Id of product attribute value
	 * @param   string  $languageCode  Language code
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function translateConversionRemove($id, $conversionId, $languageCode)
	{
		$id = (int) $id;

		if (!$id || empty($languageCode))
		{
			return false;
		}

		$db = Factory::getDbo();

		$conditions = array(
			$db->qn('value_id') . ' = ' . $id,
			$db->qn('conversion_set_id') . ' = ' . $conversionId,
			$db->qn('rctranslations_language') . ' = ' . $db->quote($languageCode)
		);

		$query = $db->getQuery(true)
			->delete(
				$db->quoteName('#__redshopb_product_attribute_value_conv_xref_rctranslations')
			)
			->where($conditions);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return	object
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// Gets the image URL
		if (empty($item->id) || empty($item->image))
		{
			return $item;
		}

		$item->imageurl = $this->getImageUrl($item->image, 'product_attr_value');

		return $item;
	}
}
