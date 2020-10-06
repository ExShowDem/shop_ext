<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * Department Entity.
 *
 * @since  1.7
 *
 * @property string  $name
 */
class RedshopbEntityDepartment extends RedshopbEntity
{
	use RedshopbEntityTraitAddress, RedshopbEntityTraitAddressesShipping, RedshopbEntityTraitAddressShippingDefault;
	use RedshopbEntityTraitCompany, RedshopbEntityTraitParent, RedshopbEntityTraitAddressDelivery;
	use RedshopbEntityTraitUsesRedshopb_Acl, RedshopbEntityTraitFields;

	/**
	 * ACL prefix used to check permissions
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $aclPrefix = "redshopb.department";

	/**
	 * Child Departments
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $children;

	/**
	 * Departments that are descendants of this department
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $descendants;

	/**
	 * Check if current user can create an item
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function canCreate()
	{
		if (!$this->canDo('core.create'))
		{
			return false;
		}

		if ($this->canDo($this->getAclPrefix() . '.manage'))
		{
			return true;
		}

		if ($this->canDo($this->getAclPrefix() . '.manage.own'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if current user can edit this item
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function canEdit()
	{
		if (!$this->canDo('core.edit') && !$this->canDo('core.edit.own'))
		{
			return false;
		}

		if ($this->canDo($this->getAclPrefix() . '.manage'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the child companies
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildren()
	{
		if (null === $this->children)
		{
			$this->children = $this->searchChildren();
		}

		return $this->children;
	}

	/**
	 * Load all the descendants departments
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getDescendants()
	{
		if (null === $this->descendants)
		{
			$this->descendants = $this->searchDescendants();
		}

		return $this->descendants;
	}

	/**
	 * Load delivery address from database
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadDefaultShippingAddress()
	{
		$this->defaultShippingAddress = RedshopbEntityAddress::getInstance();

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('a') . '.*')
			->from($db->qn('#__redshopb_address', 'a'))
			->innerJoin(
				$db->qn('#__redshopb_department', 'd')
				. ' ON ' . $db->qn('d.id') . ' = ' . $db->qn('a.customer_id')
				. ' AND ' . $db->qn('a.customer_type') . ' = ' . $db->q('department')
			)
			->where($db->qn('d.deleted') . ' = 0')
			->where($db->qn('a.type') . ' = ' . (int) RedshopbEntityAddress::TYPE_DEFAULT_SHIPPING)
			->where($db->qn('d.id') . ' = ' . (int) $this->id);

		$db->setQuery($query, 0, 1);

		$addressData = $db->loadObject();

		if ($addressData)
		{
			$this->defaultShippingAddress = RedshopbEntityAddress::getInstance($addressData->id)->bind($addressData);
		}

		return $this;
	}

	/**
	 * Get the available shipping addresses
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadShippingAddresses()
	{
		$this->shippingAddresses = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('a') . '.*')
			->from($db->qn('#__redshopb_address', 'a'))
			->innerJoin(
				$db->qn('#__redshopb_department', 'd')
				. ' ON ' . $db->qn('d.id') . ' = ' . $db->qn('a.customer_id')
				. ' AND ' . $db->qn('a.customer_type') . ' = ' . $db->q('department')
			)
			->where($db->qn('d.deleted') . ' = 0')
			->where($db->qn('a.type') . ' = ' . (int) RedshopbEntityAddress::TYPE_SHIPPING)
			->where($db->qn('d.id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$addresses = $db->loadObjectList();

		foreach ($addresses as $address)
		{
			$entity = RedshopbEntityAddress::getInstance($address->id)->bind($address);

			$this->shippingAddresses->add($entity);
		}

		return $this;
	}

	/**
	 * Search in child departments.
	 * Note: this only searches in first level child departments.
	 *
	 * @param   array  $modelState  State for the Departments model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchChildren($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'd.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force this entity as parent department
		$state['filter.parent_id'] = $this->id;

		$children = RedshopbModel::getFrontInstance('departments')->search($state);

		// In an ideal world the model already returned a collection
		if ($children instanceof RedshopbEntitiesCollection)
		{
			return $children;
		}

		foreach ($children as $child)
		{
			$entity = static::getInstance($child->id)->bind($child);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search descendant departments
	 *
	 * @param   array  $modelState  State for the Departments model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchDescendants($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'd.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force this entity as ancestor
		$state['filter.ancestor'] = $this->id;

		$descendants = RedshopbModel::getFrontInstance('departments')->search($state);

		if ($descendants instanceof RedshopbEntitiesCollection)
		{
			return $descendants;
		}

		foreach ($descendants as $department)
		{
			$entity = static::getInstance($department->id)->bind($department);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Get the billing address
	 *
	 * @return  RedshopbEntityAddress
	 */
	public function getBillingAddress()
	{
		$this->determineBillingAddress();

		return $this->address;
	}

	/**
	 * Determine the billing address
	 *
	 * @return  void
	 */
	protected function determineBillingAddress()
	{
		$app           = Factory::getApplication();
		$departmentId  = (int) $app->getUserState('shop.customer_id');
		$instance      = RedshopbEntityAddress::getInstance();
		$this->address = $instance->loadItem(
			array('customer_id', 'customer_type', 'type'),
			array($departmentId, RedshopbEntityCustomer::TYPE_DEPARTMENT, 2)
		);

		if (is_null($this->address->get('id')))
		{
			$companyId     = RedshopbHelperDepartment::getCompanyId($departmentId);
			$deptsCompany  = RedshopbEntityCompany::load($companyId);
			$this->address = $deptsCompany->getAddress();
		}
	}
}
