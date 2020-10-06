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
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
/**
 * Price Debtor Group Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelPrice_Debtor_Group extends RedshopbModelAdmin
{
	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'customer_price_group', $prefix = '', $config = array())
	{
		if ($name == '')
		{
			$name = 'customer_price_group';
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
		// the customers ids array to an object.
		if (isset($properties['customer_ids']) && !empty($properties['customer_ids']))
		{
			$item->customer_ids = $properties['customer_ids'];
		}
		else
		{
			$item->customer_ids = array();
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
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record.
	 */
	protected function canDelete($record)
	{
		return RedshopbHelperACL::isSuperAdmin();
	}

	/**
	 * Method to test whether a record can be change status state.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record.
	 */
	protected function canEditState($record)
	{
		return RedshopbHelperACL::isSuperAdmin();
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
		$form->setFieldAttribute('customer_ids', 'required', false);

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
	 *  Validate web service data for memberCompanyAdd function
	 *
	 * @param   int  $data  Data to be validated ('company_id')
	 *
	 * @return  array | false
	 */
	public function validateMemberCompanyAddWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'company');

		if (!$data)
		{
			return false;
		}

		$item = $this->getItem($data['id']);

		if ($item->company_id != '')
		{
			if (!RedshopbHelperCompany::isChildOf($data['company_id'], $item->company_id))
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_COMPANY_NOT_CHILDREN_OF', $data["company_id"], $item->id),
					'error'
				);

				return false;
			}
		}

		return $data;
	}

	/**
	 *  Add a member company to a group
	 *
	 * @param   int  $groupId    id of group
	 * @param   int  $companyId  id of company table
	 *
	 * @return  boolean Group ID on success. False otherwise.
	 */
	public function memberCompanyAdd($groupId, $companyId)
	{
		$groupTable = $this->getTable();

		if (!$groupTable->load($groupId))
		{
			return false;
		}

		$companyTable = RedshopbTable::getAdminInstance('Company');

		if (!$companyTable->load($companyId))
		{
			return false;
		}

		if (array_search($companyId, $groupTable->customer_ids) === false)
		{
			$groupTable->customer_ids[] = $companyId;
			$groupTable->setOption('customers.store', true);

			if (!$groupTable->save(array()))
			{
				return false;
			}
		}

		return $groupId;
	}

	/**
	 *  Validate web service data for memberCompanyRemove function
	 *
	 * @param   int  $data  Data to be validated ('company_id')
	 *
	 * @return  array | false
	 */
	public function validateMemberCompanyRemoveWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'company');
	}

	/**
	 *  Remove a member company from a group
	 *
	 * @param   int  $groupId    id of group
	 * @param   int  $companyId  id of company table
	 *
	 * @return  boolean Group ID on success. False otherwise.
	 */
	public function memberCompanyRemove($groupId, $companyId)
	{
		$groupTable = $this->getTable();

		if (!$groupTable->load($groupId))
		{
			return false;
		}

		$i = array_search($companyId, $groupTable->customer_ids);

		if ($i !== false)
		{
			unset($groupTable->customer_ids[$i]);
		}
		else
		{
			return $groupId;
		}

		$groupTable->setOption('customers.store', true);

		if (!$groupTable->save(array()))
		{
			return false;
		}

		return $groupId;
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

		if (isset($data['show_stock_as']))
		{
			if ($data['show_stock_as'] != 'actual_stock'
				&& $data['show_stock_as'] != 'color_codes'
				&& $data['show_stock_as'] != 'hide'
				&& $data['show_stock_as'] != 'not_set')
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_DEBTOR_ERROR_STOCK_INVALID', $data['show_stock_as']),
					'error'
				);

				return false;
			}
		}

		return $data;
	}
}
