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

/**
 * Conversion Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.26
 */
class RedshopbModelConversion extends RedshopbModelAdmin
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$isNew      = true;
		$table      = $this->getTable();
		$primaryKey = $table->getKeyName();
		$primaryKey = (!empty($data[$primaryKey])) ? $data[$primaryKey] : (int) $this->getState($this->getName() . '.id');
		$files      = Factory::getApplication()->input->files->get('jform', array(), 'array');

		// Allow an exception to be thrown.
		try
		{
			// Force to set first conversion of an Conversion Sets will be default.
			if (!$primaryKey && !$this->isHasDefaultConversion($data['product_attribute_id']))
			{
				$data['default'] = 1;
			}

			// Load the row if saving an existing record.
			if ($primaryKey > 0)
			{
				$table->load($primaryKey);
				$isNew = false;
			}

			// Image upload for view
			if (count($files) > 0 && isset($files['imageFileUpload']) && $files['imageFileUpload']['name'] && $primaryKey > 0)
			{
				$file                                  = $files['imageFileUpload'];
				RedshopbHelperThumbnail::$displayError = false;

				if (!RedshopbHelperThumbnail::checkFileError($file['name'], $file['error'])
					|| !RedshopbHelperMedia::checkExtension($file['name'])
					|| !RedshopbHelperMedia::checkIsImage($file['tmp_name']))
				{
					$this->setError(RedshopbHelperThumbnail::getError());
					echo RedshopbHelperThumbnail::getError();

					return false;
				}

				// Delete old if exists
				if (!$isNew && !empty($table->get('image')))
				{
					RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, 'prod_attr_conv');
					$isNew = false;
				}

				// Saving image
				$data['image'] = RedshopbHelperThumbnail::savingImage(
					(string) $file['tmp_name'],
					(string) $file['name'], $table->get('id'), false, 'prod_attr_conv'
				);

				if ($data['image'] === false)
				{
					$this->setError(RedshopbHelperThumbnail::getError());
					echo RedshopbHelperThumbnail::getError();

					return false;
				}
			}

			// Default selected for conversion set.
			if ($data['default'] == 1)
			{
				$this->clearDefaultForConversionSet($data['product_attribute_id']);
			}

			return parent::save($data);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Method to clear default selected of conversion sets for specific Product Attribute Type
	 *
	 * @param   integer  $productAttributeId  ID of Product Attribute Type
	 *
	 * @return  boolean                       True on success. False otherwise.
	 */
	public function clearDefaultForConversionSet($productAttributeId = 0)
	{
		if (!$productAttributeId)
		{
			return false;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_conversion'))
			->set($db->qn('default') . ' = ' . $db->quote(0))
			->where($db->qn('product_attribute_id') . ' = ' . $db->quote($productAttributeId));
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method for check if product Attribute has default conversion or not.
	 *
	 * @param   integer  $productAttributeId  Product Attribute ID
	 *
	 * @return  boolean                      True if has. False otherwise.
	 */
	public function isHasDefaultConversion($productAttributeId = 0)
	{
		$productAttributeId = (int) $productAttributeId;

		if (!$productAttributeId)
		{
			return false;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(' . $db->qn('id') . ')')
			->from($db->qn('#__redshopb_conversion'))
			->where($db->qn('product_attribute_id') . ' = ' . $productAttributeId)
			->where($db->qn('default') . ' = 1');

		return $db->setQuery($query)->loadResult();
	}
}
