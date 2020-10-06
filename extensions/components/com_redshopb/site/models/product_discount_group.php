<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
/**
 * Discount Product Group Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Discount_Group extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'product_discount_group', $prefix = '', $config = array())
	{
		if ($name == '')
		{
			$name = 'product_discount_group';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
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

		// This is needed because toObject will transform
		// the products ids array to an object.
		if (isset($properties['product_ids']) && !empty($properties['product_ids']))
		{
			$item->product_ids = $properties['product_ids'];
		}
		else
		{
			$item->product_ids = array();
		}

		// This is needed because toObject will transform
		// the products ids array to an object.
		if (isset($properties['product_item_ids']) && !empty($properties['product_item_ids']))
		{
			$item->product_item_ids = $properties['product_item_ids'];
		}
		else
		{
			$item->product_item_ids = array();
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
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
		$form = $this->getForm();
		$form->setFieldAttribute('product_ids', 'required', false);

		return parent::validateWS($data);
	}

	/**
	 * Validate incoming data from the web service for creation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateCreateWS($data)
	{
		// Sets code to the same value as the ERP id to keep it as a visible reference in the views
		if (isset($data['id']))
		{
			$data['code'] = $data['id'];
		}

		return parent::validateCreateWS($data);
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
		// If some of the manually updated fields is not sent, it brings it from the item itself to avoid validation errors
		$item = $this->getItemFromWSData($data['id']);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["id"]), 'error');

			return false;
		}

		if (!isset($data['code']) || $data['code'] == '')
		{
			$data['code'] = $item->code;
		}

		// Sets the new customer number if the erp_id is changed
		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['code'] = $data['erp_id'];
		}

		return parent::validateUpdateWS($data);
	}

	/**
	 *  Validate web service data for productAdd function
	 *
	 * @param   int  $data  Data to be validated ('product_id')
	 *
	 * @return  array | false
	 */
	public function validateProductAddWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'product');

		if (!$data)
		{
			return false;
		}

		$item = $this->getItem($data['id']);

		$productModel = RedshopbModelAdmin::getFrontInstance('Product');
		$product      = $productModel->getItem($data['product_id']);

		if ($item->company_id != $product->company_id)
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_REDSHOPB_CUSTOMER_DISCOUNT_GROUP_PRODUCT_NOT_COMPANY', $data["product_id"], $item->id), 'error'
			);

			return false;
		}

		return $data;
	}

	/**
	 *  Add a member company to a group
	 *
	 * @param   int  $groupId    id of group
	 * @param   int  $productId  id of product table
	 *
	 * @return  boolean Group ID on success. False otherwise.
	 */
	public function productAdd($groupId, $productId)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$groupTable = $this->getTable();

		if (!$groupTable->load($groupId))
		{
			return false;
		}

		$productTable = RedshopbTable::getAdminInstance('Product');

		if (!$productTable->load($productId))
		{
			return false;
		}

		if (array_search($productId, $groupTable->product_ids) === false)
		{
			$groupTable->product_ids[] = $productId;
			$groupTable->setOption('products.store', true);

			if (!$groupTable->save(array()))
			{
				return false;
			}
		}

		return $groupId;
	}

	/**
	 *  Validate web service data for productRemove function
	 *
	 * @param   int  $data  Data to be validated ('product_id')
	 *
	 * @return  array | false
	 */
	public function validateProductRemoveWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'product');
	}

	/**
	 *  Remove a member company from a group
	 *
	 * @param   int  $groupId    id of group
	 * @param   int  $productId  id of product table
	 *
	 * @return  boolean Group ID on success. False otherwise.
	 */
	public function productRemove($groupId, $productId)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$groupTable = $this->getTable();

		if (!$groupTable->load($groupId))
		{
			return false;
		}

		$i = array_search($productId, $groupTable->product_ids);

		if ($i !== false)
		{
			unset($groupTable->product_ids[$i]);
		}
		else
		{
			return $groupId;
		}

		$groupTable->setOption('products.store', true);

		if (!$groupTable->save(array()))
		{
			return false;
		}

		return $groupId;
	}
}
