<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
/**
 * Company Entity.
 *
 * @since  1.7
 *
 * @property string  $name
 * @property string  $vat_number
 * @property string  $invoice_email
 */
class RedshopbEntityCompany extends RedshopbEntity
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
	protected $aclPrefix = "redshopb.company";

	/**
	 * Ancestor companies
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $ancestors;

	/**
	 * Child Companies
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $children;

	/**
	 * Vendor company (parent)
	 *
	 * @var    RedshopbEntitiesCollection|null
	 * @since  1.7
	 */
	protected $vendor;

	/**
	 * Child departments
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $departments;

	/**
	 * Departments that in this company or in its children companies
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $descendantDepartments;

	/**
	 * Companies that are descendants of this company
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $descendants;

	/**
	 * Company images folder.
	 * Example: array('relative' => '', 'full' =>)
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $imageFolder = array();

	/**
	 * Company roles
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $roles;

	/**
	 * Company tree
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $tree;

	/**
	 * Product wallet
	 *
	 * @var    object
	 * @since  1.7
	 */
	protected $walletProduct;

	/**
	 * Categories list
	 *
	 * @var    object
	 * @since  1.7
	 */
	protected $categories;

	/**
	 * Price groups list
	 *
	 * @var    object
	 * @since  1.13.0
	 */
	protected $priceGroups;

	/**
	 * Currency
	 *
	 * @var    integer
	 * @since  1.7
	 */
	protected $currency;

	/**
	 * @var string
	 */
	protected $stock_visibility;

	/**
	 * @var  RedshopbEntitiesCollection
	 */
	protected $collections;

	/**
	 * @var   RedshopbEntitiesCollection
	 * @since 1.13.2
	 */
	protected $defaultPriceGroups = array();

	/**
	 * Method to get a company instance by customer
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return  RedshopbEntityCompany|false  The company on success, false on failure.
	 */
	public static function getInstanceByCustomer($customerId, $customerType)
	{
		$company = false;

		switch ($customerType)
		{
			case 'employee':
				$company = RedshopbEntityUser::getInstance($customerId)->getCompany();
				break;
			case 'department':
				$company = RedshopbEntityDepartment::getInstance($customerId)->getCompany();
				break;
			case  'company':
				$company = self::getInstance($customerId);
				break;
		}

		return $company;
	}

	/**
	 * Get the ancestor companies
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getAncestors()
	{
		if (null === $this->ancestors)
		{
			$this->ancestors = $this->searchAncestors();
		}

		return $this->ancestors;
	}

	/**
	 * Get the child companies.
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
	 * Get the vendor (parent) company
	 *
	 * @return  $this|null
	 *
	 * @since   1.7
	 */
	public function getVendor()
	{
		if (null === $this->vendor)
		{
			$this->vendor = $this->searchVendor();
		}

		return $this->vendor;
	}

	/**
	 * Get the children companies ids
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildrenIds()
	{
		return $this->getChildren()->ids();
	}

	/**
	 * Get departments in this company or its descendants
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   1.7
	 */
	public function getDescendantDepartments()
	{
		if (null === $this->descendantDepartments)
		{
			$this->descendantDepartments = $this->searchDescendantDepartments();
		}

		return $this->descendantDepartments;
	}

	/**
	 * Load all the descendants companies
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
	 * Get the child departments.
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getDepartments()
	{
		if (null === $this->departments)
		{
			$this->departments = $this->searchDepartments();
		}

		return $this->departments;
	}

	/**
	 * Get the folder where the images for this company are stored
	 *
	 * @param   bool  $relative  Return relative path?
	 *
	 * @return  string
	 *
	 * @since   1.7
	 */
	public function getImageFolder($relative = false)
	{
		$key = $relative ? 'relative' : 'absolute';

		if (!isset($this->imageFolder[$key]))
		{
			try
			{
				$this->loadImageFolder();
			}
			catch (Exception $e)
			{
				return '';
			}
		}

		return $this->imageFolder[$key];
	}

	/**
	 * Get roles available for this company
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function getRoles()
	{
		if (null === $this->roles)
		{
			$this->roles = $this->searchRoles();
		}

		return $this->roles;
	}

	/**
	 * Get the category tree
	 *
	 * @param   boolean  $includeRoot  Include root category
	 * @param   boolean  $includeSelf  Include instance category in the tree
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getTree($includeRoot = true, $includeSelf = true)
	{
		if (null === $this->tree)
		{
			$this->loadTree();
		}

		$tree = $this->tree;

		if (!$includeRoot && count($tree > 1))
		{
			array_shift($tree);
		}

		if (!$includeSelf)
		{
			array_pop($tree);
		}

		return $tree;
	}

	/**
	 * Get this company currency - or b2b default
	 *
	 * @return  integer
	 *
	 * @since   2.0
	 */
	public function getCurrency()
	{
		if (null === $this->currency)
		{
			$componentParams = RedshopbApp::getConfig();
			$this->currency  = $componentParams->get('default_currency', 38);

			if (!$this->hasId())
			{
				return $this->currency;
			}

			if (is_null($this->item))
			{
				$companyModel = RedshopbModel::getFrontInstance('Company');
				$this->bind($companyModel->getItem($this->id));
			}

			if (!is_null($this->item->currency_id))
			{
				$this->currency = $this->item->currency_id;

				return $this->currency;
			}

			// Default currency (from config or simply DKK as fallback)
		}

		return $this->currency;
	}

	/**
	 * Get this company currency - or vendor currency
	 *
	 * @return  integer
	 *
	 * @since   2.0
	 */
	public function getCustomerCurrency()
	{
		if (is_null($this->item))
		{
			$companyModel = RedshopbModel::getFrontInstance('Company');
			$this->bind($companyModel->getItem($this->id));
		}

		if (!is_null($this->item->currency_id))
		{
			$currency = $this->item->currency_id;
		}
		else
		{
			$vendor = $this->getVendor();

			if ($vendor)
			{
				$currency = $vendor->getCurrency();
			}
			// If vendor is not set we will take shop default currency
			else
			{
				$currency = RedshopbEntityConfig::getInstance()->get('default_currency', 38);
			}
		}

		return $currency;
	}

	/**
	 * Get the name sanitised
	 * Note: Here only to keep B/C with old helpers
	 *
	 * @return  string
	 *
	 * @since   1.7
	 */
	public function getWebSafeName()
	{
		return $this->getWebSafeProperty('name');
	}

	/**
	 * Get all collections
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   1.7
	 */
	public function getCollections()
	{
		if (null === $this->collections)
		{
			$this->collections = $this->searchCollections();
		}

		return $this->collections;
	}

	/**
	 * Get default groups
	 *
	 * @param   boolean  $aclFiltered  Filter by ACL checks (default true)
	 *
	 * @return  RedshopbEntitiesCollection
	 * @since   1.13.2
	 */
	public function getDefaultPriceGroups($aclFiltered = true)
	{
		if (!isset($this->defaultPriceGroups[(string) $aclFiltered]))
		{
			$this->defaultPriceGroups[(string) $aclFiltered] = $this->searchDefaultPriceGroups($aclFiltered);
		}

		return $this->defaultPriceGroups[(string) $aclFiltered];
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
				$db->qn('#__redshopb_company', 'c')
				. ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.customer_id')
				. ' AND ' . $db->qn('a.customer_type') . ' = ' . $db->q('company')
			)
			->where($db->qn('c.deleted') . ' = 0')
			->where($db->qn('a.type') . ' = ' . (int) RedshopbEntityAddress::TYPE_DEFAULT_SHIPPING)
			->where($db->qn('c.id') . ' = ' . (int) $this->id);

		$db->setQuery($query, 0, 1);

		$addressData = $db->loadObject();

		if ($addressData)
		{
			$this->defaultShippingAddress = RedshopbEntityAddress::getInstance($addressData->id)->bind($addressData);
		}

		return $this;
	}

	/**
	 * Load the image folder
	 *
	 * @param   bool  $relative  Return relative path?
	 *
	 * @throws  RuntimeException
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	protected function loadImageFolder($relative = false)
	{
		$this->imageFolder = array(
			'relative' => '',
			'absolute' => ''
		);

		$item = $this->getItem();

		if (!$item || !$item->customer_number)
		{
			return $this;
		}

		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.filesystem.folder');

		$ancestors = $this->getAncestors();

		$relativePath = array('companies');

		foreach ($ancestors as $ancestor)
		{
			$name           = urlencode(strtolower(str_replace(" ", '_', $ancestor->customer_number)));
			$folder         = JFile::makeSafe($name);
			$relativePath[] = $folder;
		}

		// Add current company data
		$name           = urlencode(strtolower(str_replace(" ", '_', $item->customer_number)));
		$relativePath[] = JFile::makeSafe($name);

		$relativePath = implode('/', $relativePath);
		$absolutePath = JPATH_SITE . '/images/' . $relativePath;

		// Make sure it exists
		if (!JFolder::create($absolutePath))
		{
			throw new RuntimeException("Couldn't create image folder for company " . $this->id);
		}

		$this->imageFolder = array(
			'relative' => $relativePath,
			'absolute' => $absolutePath
		);

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
				$db->qn('#__redshopb_company', 'c')
				. ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.customer_id')
				. ' AND ' . $db->qn('a.customer_type') . ' = ' . $db->q('company')
			)
			->where($db->qn('c.deleted') . ' = 0')
			->where($db->qn('a.type') . ' = ' . (int) RedshopbEntityAddress::TYPE_SHIPPING)
			->where($db->qn('c.id') . ' = ' . (int) $this->id);

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
	 * Load the category tree
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	protected function loadTree()
	{
		$this->tree = array();

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_company', 'c'))
			->join('INNER', $db->qn('#__redshopb_company', 'p') . ' ON p.lft < c.lft AND p.rgt > c.rgt AND ' . $db->qn('p.deleted') . ' = 0')
			->where('c.id = ' . (int) $this->id)
			->where($db->qn('c.deleted') . ' = 0')
			->order('p.lft ASC');

		$tree = $db->setQuery($query)->loadColumn();

		if ($tree)
		{
			$this->tree = $tree;
		}

		$this->tree[] = $this->id;

		return $this;
	}

	/**
	 * Search ancestor companies
	 *
	 * @param   array  $modelState  State for the Companies model
	 *
	 * @return  self
	 *
	 * @since   1.7
	 */
	public function searchAncestors($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'c.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		$state['filter.descendant'] = $this->id;

		$ancestors = RedshopbModel::getFrontInstance('companies')->search($state);

		foreach ($ancestors as $ancestor)
		{
			$entity = static::getInstance($ancestor->id)->bind($ancestor);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search in child companies.
	 * Note: this only searches in first level child companies.
	 *
	 * @param   array  $modelState  State for the Companies model
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
			'list.ordering'  => 'c.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force search in this company
		$state['filter.parent_id'] = $this->id;

		$children = RedshopbModel::getFrontInstance('companies')->search($state);

		foreach ($children as $child)
		{
			$entity = static::getInstance($child->id)->bind($child);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search for vendor (parent) company
	 *
	 * @return  RedshopbEntityCompany
	 *
	 * @since   2.0
	 */
	public function searchVendor()
	{
		if (!$this->hasId())
		{
			return null;
		}

		switch (RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent'))
		{
			case 'main':
				return static::getInstance(RedshopbHelperCompany::getMain()->id);
			case 'parent':
			default:
				if ($this->get('parent_id'))
				{
					return static::getInstance($this->get('parent_id'));
				}
		}

		return null;
	}

	/**
	 * Search in this company child departments
	 *
	 * @param   array  $modelState  State for the Departments model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchDepartments($modelState = array())
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

		$state['filter.company_id'] = $this->id;

		$departments = RedshopbModel::getFrontInstance('departments')->search($state);

		foreach ($departments as $department)
		{
			$entity = RedshopbEntityDepartment::getInstance($department->id)->bind($department);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search in descendant departments
	 *
	 * @param   array  $modelState  State for the Departments model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchDescendantDepartments($modelState = array())
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

		// Force this company ancestor
		$state['filter.company_ancestor'] = $this->id;

		$departments = RedshopbModel::getFrontInstance('departments')->search($state);

		if ($departments instanceof RedshopbEntitiesCollection)
		{
			return $departments;
		}

		foreach ($departments as $department)
		{
			$entity = RedshopbEntityDepartment::getInstance($department->id)->bind($department);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search descendant companies
	 *
	 * @param   array  $modelState  State for the Companies model
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
			'list.ordering'  => 'c.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force ancestor to this company
		$state['filter.ancestor'] = $this->id;

		$companies = RedshopbModel::getFrontInstance('companies')->search($state);

		if ($companies instanceof RedshopbEntitiesCollection)
		{
			return $companies;
		}

		foreach ($companies as $company)
		{
			$entity = static::getInstance($company->id)->bind($company);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search roles in this company
	 *
	 * @param   array  $modelState  State for the model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchRoles($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		$state = array(
			'list.ordering'  => 'rt.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force search in this company
		$state['filter.company'] = (int) $this->id;

		$roles = RedshopbModel::getFrontInstance('roles')->search($state);

		foreach ($roles as $role)
		{
			// We will populate role type to save queries
			$type = $this->genTypeFromRolesModelResult($role);

			$entity = RedshopbEntityRole::getInstance($role->id)->bind($role)->setType($type);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Generate a Role_Type entity from roles list model result
	 *
	 * @param   stdClass  $role  Object from the roles list model
	 *
	 * @return  RedshopbEntityRole_Type
	 *
	 * @since   2.0
	 */
	private function genTypeFromRolesModelResult($role)
	{
		$typeData       = (array) $role;
		$typeData['id'] = $role->role_type_id;

		$propertiesRemoved = array('checked_out', 'checked_out_time', 'created_by', 'created_date', 'modified_by', 'modified_date');

		$typeData = array_diff_key($typeData, array_flip($propertiesRemoved));

		$type = RedshopbEntityRole_Type::getInstance($role->role_type_id);

		$typeTable = $type->getTable();

		$typeData = array_intersect_key($typeData, $typeTable->getProperties(1));

		$type->bind($typeData);

		return $type;
	}

	/**
	 * Load wallet product from DB
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	protected function loadWalletProduct()
	{
		$this->walletProduct = RedshopbEntityProduct::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->wallet_product_id)
		{
			return $this;
		}

		$this->walletProduct = RedshopbEntityProduct::load($item->wallet_product_id);

		return $this;
	}

	/**
	 * Get the ancestor companies
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getCategories()
	{
		if (null === $this->categories)
		{
			$this->categories = $this->searchCategories();
		}

		return $this->categories;
	}

	/**
	 * Get the price groups
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   1.13.0
	 */
	public function getPriceGroups()
	{
		if (null === $this->priceGroups)
		{
			$this->priceGroups = $this->searchPriceGroups();
		}

		return $this->priceGroups;
	}

	/**
	 * Search in this company child categories
	 *
	 * @param   array  $modelState  State for the Departments model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchCategories($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'c.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		$state['filter.company_id'] = $this->id;

		$categories = RedshopbModel::getFrontInstance('Categories')->search($state);

		foreach ($categories as $category)
		{
			$entity = RedshopbEntityCategory::getInstance($category->id)->bind($category);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Get price groups of this company
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   1.13.0
	 */
	public function searchPriceGroups()
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->select('pg.*')
			->from($db->qn('#__redshopb_customer_price_group_xref', 'pgx'))
			->join('inner', $db->qn('#__redshopb_customer_price_group', 'pg') . ' ON ' . $db->qn('pgx.price_group_id') . ' = ' . $db->qn('pg.id'))
			->where('pg.state = 1')
			->where($db->qn('pgx.customer_id') . ' = ' . (int) $this->getId());
		$results = $db->setQuery($query)->loadObjectList();

		if (empty($results))
		{
			return $collection;
		}

		foreach ($results as $priceGroup)
		{
			$entity = RedshopbEntityPrice_Group::getInstance($priceGroup->id)->bind($priceGroup);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search all collections belong to this company
	 *
	 * @param   array  $modelState  State for the Collections model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchCollections($modelState = array())
	{
		$result = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $result;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'w.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		$state['filter.company'] = $this->id;

		/** @var RedshopbModelCollections $model */
		$model       = RedshopbModel::getFrontInstance('collections');
		$collections = $model->search($state);

		foreach ($collections as $collection)
		{
			$entity = RedshopbEntityCollection::getInstance($collection->id)->bind($collection);

			$result->add($entity);
		}

		return $result;
	}

	/**
	 * Load default price groups for this company
	 *
	 * @param   boolean  $aclFiltered  Filter by ACL checks (default true)
	 *
	 * @return  RedshopbEntitiesCollection
	 * @since   1.13.2
	 */
	protected function searchDefaultPriceGroups($aclFiltered = true)
	{
		$result = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $result;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'cpg.id',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		$state['filter.default']       = '1';
		$state['filter.group_company'] = $this->id;

		if (!$aclFiltered)
		{
			$state['filter.ignoreacl'] = true;
		}

		/** @var RedshopbModelPrice_Debtor_Groups $model */
		$model  = RedshopbModel::getFrontInstance('Price_Debtor_Groups');
		$groups = $model->search($state);

		// Special case for main company, to attach the groups set to main warehouse
		if ($this->get('type') == 'main')
		{
			$state['filter.group_company'] = 'null';
			$mainGroups                    = $model->search($state);

			$groups = array_merge($groups, $mainGroups);
		}

		foreach ($groups as $group)
		{
			$entity = RedshopbEntityPrice_Group::getInstance($group->id)->bind($group);

			$result->add($entity);
		}

		return $result;
	}

	/**
	 * Get Stock Visibility
	 *
	 * @return  string
	 */
	public function getStockVisibility()
	{
		if ($this->stock_visibility)
		{
			return $this->stock_visibility;
		}

		$this->stock_visibility = 'hide';

		if (!$this->hasId())
		{
			return $this->stock_visibility;
		}

		if ($this->show_stock_as)
		{
			$this->stock_visibility = $this->show_stock_as;
		}

		if ($this->stock_visibility == 'not_set')
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('DISTINCT(cpg.show_stock_as)')
				->from($db->qn('#__redshopb_customer_price_group', 'cpg'))
				->leftJoin($db->qn('#__redshopb_customer_price_group_xref', 'cpgx') . ' ON cpgx.price_group_id = cpg.id')
				->where('cpgx.customer_id = ' . (int) $this->id)
				->where('cpg.state = 1')
				->order(
					'FIELD(cpg.show_stock_as, ' . $db->q('actual_stock') . ', '
					. $db->q('color_codes') . ', ' . $db->q('hide') . ', ' . $db->q('not_set') . ')'
				);

			$result = $db->setQuery($query, 0, 1)
				->loadResult();

			if ($result)
			{
				$this->stock_visibility = $result;
			}
		}

		if ($this->stock_visibility == 'not_set')
		{
			$this->stock_visibility = 'hide';
		}

		return $this->stock_visibility;
	}

	/**
	 * Method to get the guest user ID for b2c guest checkout
	 *
	 * @return mixed
	 */
	public function getGuestEmployeeId()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$id    = $this->getId();

		$query->select('user.id')
			->from('#__redshopb_user AS user')
			->leftJoin('#__user_usergroup_map AS juser_group ON  juser_group.user_id = user.joomla_user_id')
			->leftJoin('#__redshopb_role AS user_role  ON user_role.joomla_group_id = juser_group.group_id')
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = user.id')
			->where('umc.company_id = ' . (int) $id)
			->where('user.employee_number = ' . $db->q($id . '-guest'))
			->where('user_role.role_type_id = 7');

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method to create a guest employee for guest checkout
	 *
	 * @return boolean|integer
	 */
	public function createGuestEmployee()
	{
		$config = RedshopbEntityConfig::getInstance();
		$id     = $this->getId();

		if (empty($id) || !$this->isB2C())
		{
			return false;
		}

		/** @var RedshopbTableUser $userTable */
		$userTable = RedshopbTable::getAdminInstance('User');
		$userTable->reset();

		$guestUser = array(
			'company_id' => $id,
			'role_type_id' => 7,
			'name1' => $config->get('checkout_guest_user_name', 'Guest'),
			'employee_number' => $id . '-guest',
			'use_company_email' => 1,
			'send_email' => 0
		);

		$userTable->bind($guestUser);

		if (!$userTable->check() || !$userTable->store())
		{
			return false;
		}

		return $userTable->id;
	}

	/**
	 * Is the company a b2c company
	 *
	 * @return boolean
	 */
	public function isB2C()
	{
		$b2c = $this->get('b2c', 0);

		return ($b2c == 1);
	}

	/**
	 * Get Unique UserGroup Name
	 *
	 * @param   string  $title      Current title
	 * @param   int     $parentId   Parent id
	 * @param   int     $currentId  Current id
	 *
	 * @return  string
	 */
	public function getUniqueUserGroupName($title = '', $parentId = 0, $currentId = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('title')
			->from($db->qn('#__usergroups'))
			->where('title = ' . $db->q(trim($title)))
			->where('parent_id = ' . (int) $parentId)
			->where('id <> ' . (int) $currentId);

		while ($result = $db->setQuery($query, 0, 1)->loadResult())
		{
			$title = StringHelper::increment($result, 'dash');
			$query->clear('where')
				->where('title = ' . $db->q(trim($title)))
				->where('parent_id = ' . (int) $parentId)
				->where('id <> ' . (int) $currentId);
		}

		return $title;
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
		$company = $this->getCompany();

		if (is_null($company->getId()))
		{
			$company = self::load($this->getId());
		}

		$this->address = $company->getAddress();
	}
}
