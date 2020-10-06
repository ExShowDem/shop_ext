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
use Joomla\CMS\Form\Form;
/**
 * Product Data Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Accessory extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		if (!parent::save($data))
		{
			return false;
		}

		$id = (int) $this->getState($this->getName() . '.id');

		return $id;
	}

	/**
	 * Method for set price of an product accessory
	 *
	 * @param   int    $id     ID of product accessory
	 * @param   float  $price  Price value
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function setPrice($id, $price)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$table = $this->getTable();

		// Check if this product accessory exist
		if (!$table->load($id))
		{
			return false;
		}

		return $table->save(array('price' => $price));
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

		// Validates that no duplicate records are inserted
		$productAccessoryTable = RedshopbTable::getAdminInstance('Product_Accessory');

		if ($productAccessoryTable->load(
			array(
					'product_id' => $data['product_id'],
					'accessory_product_id' => $data['accessory_product_id']
				)
		))
		{
			if ($data['id'] != $productAccessoryTable->id)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ACCESSORIES_DUPLICATE'), 'error');

				return false;
			}
		}

		return $data;
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

		// Removes invalid values for collection when not set
		if ($data['collection_id'] == '')
		{
			unset($data['collection_id']);
		}

		return $data;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateUpdateWS($data)
	{
		$data = parent::validateUpdateWS($data);

		if (!$data)
		{
			return false;
		}

		// Removes invalid values for collection when not set
		if ($data['collection_id'] == '')
		{
			unset($data['collection_id']);
		}

		return $data;
	}
}
