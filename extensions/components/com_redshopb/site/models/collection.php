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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
/**
 * Collection Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCollection extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'collection';

	/**
	 * Override to get handle default department id on creation
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$fromDepartment = RedshopbInput::isFromDepartment();
		$fromCompany    = RedshopbInput::isFromCompany();
		$fromCollection = RedshopbInput::isFromCollection();
		$fromProduct    = RedshopbInput::isFromProduct();

		$this->setState($this->getName() . '.fromDepartment', $fromDepartment);
		$this->setState($this->getName() . '.fromCompany',    $fromCompany);
		$this->setState($this->getName() . '.fromProduct',    $fromProduct);

		if ($fromDepartment)
		{
			$departmentId = RedshopbInput::getDepartmentIdForm();
			$this->setState($this->getName() . '.departmentId', $departmentId);

			// Get corresponding company id
			$table = $this->getTable('Department');
			$table->load($departmentId);

			$this->setState($this->getName() . '.fromCompany', $fromDepartment);
			$this->setState($this->getName() . '.companyId', $table->company_id);
		}
		elseif ($fromCompany)
		{
			$companyId = RedshopbInput::getCompanyIdForm();
			$this->setState($this->getName() . '.companyId', $companyId);
		}
		elseif ($fromCollection)
		{
			$collectionId = RedshopbInput::getCollectionIdForm();
			$this->setState($this->getName() . '.collectionId', $collectionId);
		}
		elseif ($fromProduct)
		{
			$productId = RedshopbInput::getProductIdForm();
			$this->setState($this->getName() . '.productId', $productId);
		}
	}

	/**
	 * Get product items relates to colors
	 *
	 * @param   array  $colors  Ids colors
	 *
	 * @return mixed
	 */
	public function getColorItems($colors = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('piav.*')
			->from($db->qn('#__redshopb_product_item_attribute_value_xref', 'piav'))
			->innerJoin($db->qn('#__redshopb_collection_product_item_xref', 'wpi') . ' ON wpi.product_item_id = piav.product_item_id')
			->where('piav.product_attribute_value_id IN (' . implode(',', $colors) . ')')
			->where('wpi.state = 1');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		$table->fromDepartment = $this->getState($this->getName() . '.fromDepartment');
		$table->fromCompany    = $this->getState($this->getName() . '.fromCompany');
		$table->fromCollection = $this->getState($this->getName() . '.fromCollection');
		$table->fromProduct    = $this->getState($this->getName() . '.fromProduct');

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
		else
		{
			$departmentId = $this->getState($this->getName() . '.departmentId');

			if ($departmentId)
			{
				$table->set('department_ids', array($departmentId));
			}

			$companyId = $this->getState($this->getName() . '.companyId');

			if ($companyId)
			{
				$table->set('company_id', $companyId);
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);
		$item       = ArrayHelper::toObject($properties, CMSObject::class);

		// This is needed because toObject will transform
		// the department ids array to an object.
		if (isset($properties['department_ids']) && !empty($properties['department_ids']))
		{
			$departmentIds        = $properties['department_ids'];
			$item->department_ids = $departmentIds;
		}
		else
		{
			$item->department_ids = array();
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
	 * Method to get a list of collection product items.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getCollectionProductItems()
	{
		$pk     = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$result = array();

		if ($pk > 0)
		{
			$db	= $this->getDbo();

			$query = $db->getQuery(true)
				->select('wpix.product_item_id')
				->from('#__redshopb_collection_product_item_xref AS wpix')

				->where('wpix.collection_id = ' . (int) $pk)
				->where('wpix.state = 1');

			$db->setQuery($query);

			$result = $db->loadObjectList();
		}

		return $result;
	}

	/**
	 * Gets all departments from searched Ids.
	 *
	 * @param   array  $departmentIds  The primary key id for the item.
	 *
	 * @return  object  List of object
	 */
	public function getDepartments($departmentIds)
	{
		$db            = $this->getDbo();
		$departmentIds = ArrayHelper::toInteger($departmentIds);

		if (count($departmentIds) > 0)
		{
			$query = $db->getQuery(true)
				->select('d.*')
				->from('#__redshopb_department AS d')
				->where('d.id IN (' . (implode(',', $departmentIds)) . ')')
				->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');

			// Check for available departments for this user if not a system admin of the app
			if (!RedshopbHelperACL::isSuperAdmin())
			{
				$user = Factory::getUser();
				$query->where('d.id IN (' . RedshopbHelperACL::listAvailableDepartments($user->id) . ')');
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			return $result;
		}

		return null;
	}

	/**
	 * Gets company details
	 *
	 * @param   int  $companyId  The primary key id for the item.
	 *
	 * @return  object  Company details
	 */
	public function getCompany($companyId)
	{
		$table = $this->getTable('Company');
		$table->load($companyId);

		$properties = $table->getProperties(1);
		$item       = ArrayHelper::toObject($properties, CMSObject::class);

		return $item;
	}

	/**
	 * Get Departments form field with selected collection values
	 *
	 * @return  boolean  False if failed
	 */
	public function getDepartmentsFormField()
	{
		$this->getState();
		$form      = $this->getForm();
		$companyId = Factory::getApplication()->input->get('company_id', null);
		$item      = $this->getItem(Factory::getApplication()->input->get('id', null));

		if (!empty($companyId) && RedshopbHelperCompany::getDepartmentsCount($companyId) <= 0)
		{
			return Text::_('COM_REDSHOPB_DEPARTMENT_MISSING');
		}
		else
		{
			$form->setValue('department_ids', null, $item->department_ids);

			return $form->getInput('department_ids');
		}
	}

	/**
	 * Get collection currency object.
	 *
	 * @param   int  $currencyId  Currency id.
	 *
	 * @return object Currency details.
	 */
	public function getCurrency($currencyId)
	{
		$table = $this->getTable('Currency');
		$table->load($currencyId);

		$properties = $table->getProperties(1);
		$item       = ArrayHelper::toObject($properties, CMSObject::class);

		return $item;
	}

	/**
	 * Remove product and variations from collection
	 *
	 * @param   int  $productId     The ID of the product to remove
	 * @param   int  $collectionId  Id collection
	 *
	 * @return  array     $msg with true if successful
	 */
	public function removeProduct($productId = null, $collectionId = null)
	{
		if (is_int($productId) && is_int($collectionId))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__redshopb_collection_product_xref'))
				->where($db->quoteName('product_id') . ' = ' . $productId)
				->where($db->quoteName('collection_id') . ' = ' . $collectionId);

			$db->setQuery($query);
			$db->execute();

			return array('msg' => true);
		}

		return array('msg' => false);
	}

	/**
	 * Un publish a collection
	 *
	 * @param   integer  $collectionId  The collection id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function unpublish($collectionId)
	{
		$collectionTable = $this->getTable();

		if (!$collectionTable->load($collectionId))
		{
			return false;
		}

		$db = Factory::getDbo();

		$fields = array(
			$db->quoteName('state') . ' = 0'
		);

		$conditions = array(
			$db->quoteName('id') . ' = ' . (int) $collectionId
		);

		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__redshopb_collection'))
			->set($fields)->where($conditions);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Add a department to collection
	 *
	 * @param   integer  $collectionId  The collection id
	 * @param   integer  $departmentId  The department id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function departmentAdd($collectionId, $departmentId)
	{
		$xrefTable = $this->getTable(
			'Collection_Department_Xref',
			'RedshopbTable'
		);

		if (!$xrefTable->load(array('collection_id' => $collectionId, 'department_id' => $departmentId)))
		{
			if (!$xrefTable->save(array('collection_id' => $collectionId, 'department_id' => $departmentId)))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	/**
	 * Remove a department from collection
	 *
	 * @param   integer  $collectionId  The collection id
	 * @param   integer  $departmentId  The department id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function departmentRemove($collectionId, $departmentId)
	{
		$xrefTable = $this->getTable(
			'Collection_Department_Xref',
			'RedshopbTable'
		);

		if (!$xrefTable->load(array('collection_id' => $collectionId, 'department_id' => $departmentId)))
		{
			return false;
		}

		$db = Factory::getDbo();

		$conditions = array(
			$db->quoteName('collection_id') . ' = ' . (int) $collectionId,
			$db->quoteName('department_id') . ' = ' . (int) $departmentId
		);

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__redshopb_collection_department_xref'))
			->where($conditions);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
