<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

jimport('joomla.database.usergroup');

/**
 * Company table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCompany extends RedshopbTableNestedAsset
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_company';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $parent_id;

	/**
	 * @var  integer
	 */
	public $address_id;

	/**
	 * @var  integer
	 */
	public $asset_id;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  string
	 */
	public $path = '';

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $name2;

	/**
	 * @var  string
	 */
	public $requisition;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  string
	 */
	public $customer_number = '';

	/**
	 * @var  string
	 */
	public $image;

	/**
	 * Layout id.
	 *
	 * @var  integer
	 */
	public $layout_id;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  integer
	 */
	protected $country_id;

	/**
	 * @var int|null
	 */
	protected $state_id;

	/**
	 * @var  string
	 */
	protected $address_name;

	/**
	 * @var  string
	 */
	protected $address_name2;

	/**
	 * @var  string
	 */
	protected $address;

	/**
	 * @var  string
	 */
	protected $address2;

	/**
	 * @var  string
	 */
	protected $zip;

	/**
	 * @var  string
	 */
	protected $city;

	/**
	 * @var  string
	 */
	protected $address_phone;

	/**
	 * @var  string
	 */
	public $contact_info;

	/**
	 * @var  integer
	 */
	public $order_approval = 0;

	/**
	 * @var  integer
	 */
	public $use_wallets = 1;

	/**
	 * @var   integer
	 */
	public $hide_company = 0;

	/**
	 * @var  integer|null
	 */
	public $currency_id = null;

	/**
	 * @var  string
	 */
	public $site_language;

	/**
	 * @var  string
	 */
	public $phone;

	/**
	 * @var   string
	 */
	public $invoice_email;

	/**
	 * @var string
	 */
	public $show_stock_as = 'not_set';

	/**
	 * @var integer
	 */
	public $show_price = -1;

	/**
	 * @var  integer
	 */
	public $employee_mandatory;

	/**
	 * @var array
	 */
	protected $price_group_ids = array();

	/**
	 * @var array
	 */
	protected $customer_discount_ids = array();

	/**
	 * @var array
	 */
	protected $delivery_addresses = array();

	/**
	 * @var array
	 */
	protected $sales_persons = array();

	/**
	 * @var  string
	 */
	public $type;

	/**
	 * @var string
	 */
	protected $site_language_text;

	/**
	 * @var boolean
	 */
	protected $deleteIfEmptyDefaultAddress = false;

	/**
	 * @var string
	 */
	protected $oldType;

	/**
	 * @var  integer
	 */
	public $use_collections;

	/**
	 * @var  integer
	 */
	public $send_mail_on_order = 1;

	/**
	 * @var  integer
	 */
	public $show_retail_price = -1;

	/**
	 * @var  integer
	 */
	public $b2c;

	/**
	 * @var null
	 */
	public $url = null;

	/**
	 * @var  integer
	 */
	public $stockroom_verification = 1;

	/**
	 * @var integer
	 */
	public $deleted = 0;

	/**
	 * @var integer
	 */
	public $tax_group_id;

	/**
	 * @var [type]
	 */
	public $tax_based_on;

	/**
	 * @var [type]
	 */
	public $calculate_vat_on;

	/**
	 * @var [type]
	 */
	public $vat_number;

	/**
	 * @var [type]
	 */
	public $customer_tax_exempt;

	/**
	 * @var [type]
	 */
	public $tax_exempt;

	/**
	 * Holds the email when saving an address
	 *
	 * @var   string
	 */
	protected $email;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Category' => 'company_id',
		'Tag' => 'company_id',
		'Product' => 'company_id',
		'Product_Discount_Group' => 'company_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.company'
		),
		'pim' => array(
			'erp.pim.company'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'parent_id' => array (
			'model' => 'Companies'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'country_code' => 'Countries',
		'currency_code' => 'Currencies'
	);

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'delivery_addresses' => 'Addresses',
		'sales_persons' => 'Users',
		'price_group_ids' => 'Price_Debtor_Groups',
		'customer_discount_ids' => 'Discount_Debtor_Groups'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'state',
		'order_approval',
		'use_wallets',
		'hide_company',
		'use_collections',
		'send_mail_on_order',
		'show_retail_price',
		'employee_mandatory',
		'b2c',
		'stockroom_verification'
	);

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		$isNew = (int) $this->id <= 0;

		// Settings for new companies
		if ($isNew)
		{
			// Sets the default price groups for this vendor (only if no groups were set during creation)
			$this->price_group_ids = empty($this->price_group_ids)
				? RedshopbEntityCompany::getInstance($this->parent_id)->getDefaultPriceGroups(false)->ids()
				: $this->price_group_ids;
		}

		if (!$this->deleted)
		{
			// Get the id of the company which is set to main
			$mainCompanyId = $this->checkCompanyMainType();

			// Only one company can be of main level
			if ($this->type == "main")
			{
				if (count($mainCompanyId) > 0 && $this->id != $mainCompanyId)
				{
					$this->setError(Text::_('COM_REDSHOPB_COMPANY_MAIN_LEVEL_SAME_ERROR'));

					return false;
				}
			}

			// Avoid duplicate customer numbers
			$companyTable     = clone $this;
			$companyTable->id = null;
			$companyTable->reset();

			if ($companyTable->load(array('customer_number' => $this->customer_number, 'deleted' => 0)))
			{
				if ($isNew || ((int) $this->id != (int) $companyTable->id))
				{
					$this->setError(Text::_('COM_REDSHOPB_COMPANY_DUPLICATE_CUSTOMER_NUMBER'));

					return false;
				}
			}
		}

		if ($this->currency_id == 0 || $this->currency_id == '')
		{
			$this->currency_id = null;
		}

		$addressTable = RedshopbTable::getAdminInstance('Address')
			->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
			->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'))
			->setOption('notSetAddressSeparate', true);

		if ($this->country_id == '' && $this->address == '' && $this->zip == '' && $this->city == '')
		{
			$this->deleteIfEmptyDefaultAddress = true;
		}

		if ($this->deleteIfEmptyDefaultAddress && $this->type != 'customer')
		{
			if ($this->address_id)
			{
				if (!$addressTable->delete($this->address_id, true))
				{
					$this->setError($addressTable->getError());

					return false;
				}
			}

			$this->address_id = null;
		}
		elseif (!$isNew && !$this->storeAddress())
		{
			return false;
		}

		// If this is b2c company. Need to check B2C requirement.
		if ($this->b2c == 1 && !$this->deleted && !$this->checkB2C())
		{
			return false;
		}

		if ($isNew
			&& !($this->deleteIfEmptyDefaultAddress && $this->type != 'customer'))
		{
			if (!$this->storeAddress())
			{
				return false;
			}
		}

		if (!$this->customer_number)
		{
			$this->customer_number = 'C' . $this->address_id;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if ($this->deleted)
		{
			return true;
		}

		// Create the core roles.
		if (!$this->createRoles($this->id))
		{
			return false;
		}

		// Rebuilds ACL if it's a new record and buildACL option hasn't been shut off, or if rebuildACL is been turned on an existing record
		if (($isNew && $this->getOption('buildACL', true)) || $this->getOption('rebuildACL', false))
		{
			// Rebuilds ACL for the company
			if (!RedshopbHelperACL::rebuildCompanyACL($this->id))
			{
				return false;
			}
		}

		if (!$this->storePriceGroupXref())
		{
			return false;
		}

		if (!$this->storeDiscountGroupXref())
		{
			return false;
		}

		// Stores the web service reference if the table is not called from a web service - otherwise the ws function will do it
		if (!$this->getOption('store.ws'))
		{
			$this->storeWSReference();
		}

		return true;
	}

	/**
	 * Store company address
	 *
	 * @return boolean
	 */
	protected function storeAddress()
	{
		if ($this->type == 'end_customer')
		{
			$addressOrder = 9;
		}
		elseif ($this->type == 'customer')
		{
			$addressOrder = 12;
		}
		else
		{
			$addressOrder = 0;
		}

		$address = array(
			'name' => $this->address_name,
			'name2' => $this->address_name2,
			'id' => $this->address_id,
			'country_id' => $this->country_id,
			'state_id' => $this->state_id,
			'address' => $this->address,
			'address2' => $this->address2,
			'zip' => $this->zip,
			'city' => $this->city,
			'phone' => $this->address_phone,
			'type' => 2,
			'order' => $addressOrder,
			'customer_type' => 'company',
			'customer_id' => (int) $this->id,
			'email' => $this->email,
		);

		/** @var RedshopbTableAddress $addressTable */
		$addressTable = RedshopbTable::getAdminInstance('Address')
			->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
			->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'))
			->setOption('notSetAddressSeparate', true);

		if ($address['id'])
		{
			if (!$addressTable->load($address['id']))
			{
				$address['id'] = 0;
			}
		}

		if (!$addressTable->save($address))
		{
			$this->setError($addressTable->getError());

			return false;
		}

		$this->address_id = $addressTable->get('id');

		return true;
	}

	/**
	 * Store sync table ws reference
	 *
	 * @return  void
	 */
	protected function storeWSReference()
	{
		if (!isset($this->wsSyncMapPK) || !isset($this->wsSyncMapPK['erp']))
		{
			return;
		}

		$wsRef      = $this->wsSyncMapPK['erp'][0];
		$syncHelper = new RedshopbHelperSync;

		$currentCustNumber = $syncHelper->findSyncedLocalId($wsRef, $this->id);

		if ($currentCustNumber && $currentCustNumber == $this->customer_number)
		{
			return;
		}

		$syncHelper->deleteSyncedLocalId($wsRef, $this->id);
		$syncHelper->recordSyncedId($wsRef, $this->customer_number, $this->id);
	}

	/**
	 * Store the price group x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	protected function storePriceGroupXref()
	{
		return RedshopbHelperDatabase::refreshXrefAssociation(
			'Company',
			'customer_id',
			(int) $this->id,
			'price_group_id',
			'Customer_Price_Group_Xref',
			$this->price_group_ids
		);
	}

	/**
	 * Store the discount group x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	protected function storeDiscountGroupXref()
	{
		if (!isset($this->customer_discount_ids))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_customer_discount_group_xref'))
			->where('customer_id = ' . (int) $this->id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		$xrefTable = RedshopbTable::getAdminInstance('Customer_Discount_Group_Xref');

		if (!is_array($this->customer_discount_ids) || count($this->customer_discount_ids) == 0)
		{
			return true;
		}

		foreach ($this->customer_discount_ids as $id)
		{
			if ($id == '')
			{
				continue;
			}

			$xrefTable->discount_group_id = null;
			$xrefTable->customer_id       = null;
			$xrefTable->reset();

			$data = array('discount_group_id' => $id, 'customer_id' => $this->id);

			if (!$xrefTable->save($data))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Called after check().
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function afterCheck()
	{
		if (empty($this->tax_group_id))
		{
			$this->tax_group_id = null;
		}

		$company = clone $this;

		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_NAME_CANNOT_BE_EMPTY'));

			return false;
		}

		$this->oldType = $this->type;

		if ($this->parent_id)
		{
			if ($company->load($this->parent_id))
			{
				if ($company->level == 0)
				{
					$this->type = 'main';
				}
				elseif ($company->level == 1)
				{
					$this->type = 'customer';
				}
				elseif ($company->level > 1)
				{
					$this->type = 'end_customer';
				}
			}
		}

		if (!$this->site_language)
		{
			$this->site_language = $this->site_language_text;
		}

		// Make sure Main Company can not marked as B2C
		if ($this->type == 'main' && $this->level == 1 && $this->b2c == 1)
		{
			$this->setError(Text::_('COM_REDSHOPB_COMPANY_ERROR_MAIN_COMPANY_MARK_B2C'));

			return false;
		}

		if ($this->b2c != 1 && !empty($this->url))
		{
			$this->setError(Text::_('COM_REDSHOPB_COMPANY_ERROR_ATTEMPTING_TO_STORE_URL_FOR_B2B_COMPANY'));

			return false;
		}

		if ($this->b2c == 1 && !$this->checkB2C())
		{
			return false;
		}

		return parent::afterCheck();
	}

	/**
	 * Check B2C company requirement.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	protected function checkB2C()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('b2c') . ' = 1')
			->where($db->qn('deleted') . ' = 0');

		if ($this->id)
		{
			$query->where($db->qn('id') . ' != ' . $db->quote($this->id));
		}

		// If this company is B2C company base on url.
		if (!empty($this->url))
		{
			$query->where($db->qn('url') . ' = ' . $db->quote($this->url));

			$msg = 'COM_REDSHOPB_COMPANY_ERROR_URL_UNIQUE';
		}
		// If this is main B2C Company
		else
		{
			$query->where('(' . $db->qn('url') . ' IS NULL OR ' . $db->qn('url') . ' = "")');

			$msg = 'COM_REDSHOPB_COMPANY_ERROR_ONLY_ONE_EMPTY_URL_ALLOWED';
		}

		$result = $db->setQuery($query)->loadResult();

		if ($result)
		{
			$this->setError(Text::_($msg));

			return false;
		}

		return true;
	}

	/**
	 * Check if any other company is in level type main
	 *
	 * @return  array $result count of rows
	 */
	protected function checkCompanyMainType()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('type') . ' = ' . $db->q('main'))
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset))
		{
			return false;
		}

		if ($this->id && $this->address_id)
		{
			/** @var RedshopbEntityAddress $address */
			$address = RedshopbEntityAddress::load($this->address_id);

			if (!$address->isLoaded())
			{
				return false;
			}

			$this->address_id    = $address->id;
			$this->country_id    = $address->country_id;
			$this->state_id      = $address->state_id;
			$this->address_name  = $address->name;
			$this->address_name2 = $address->name2;
			$this->address       = $address->address;
			$this->address2      = $address->address2;
			$this->zip           = $address->zip;
			$this->city          = $address->city;
			$this->address_phone = $address->phone;
		}

		if ($this->id)
		{
			$this->price_group_ids       = RedshopbEntityCompany::getInstance((int) $this->id)->getPriceGroups()->ids();
			$this->customer_discount_ids = RedshopbHelperCompany::getDiscountGroupIds((int) $this->id);

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			// Delivery addresses
			$query->clear()
				->select('id')
				->from($db->qn('#__redshopb_address'))
				->where($db->qn('customer_type') . ' = ' . $db->q('company'))
				->where($db->qn('customer_id') . ' = ' . (int) $this->id)
				->where($db->qn('type') . ' IN (1,3)');
			$db->setQuery($query);

			$result = $db->loadColumn();

			if ($result)
			{
				$this->delivery_addresses = $result;
			}

			// Delivery addresses
			$query->clear()
				->select('user_id')
				->from($db->qn('#__redshopb_company_sales_person_xref', 'sp'))
				->where($db->qn('company_id') . ' = ' . (int) $this->id);
			$db->setQuery($query);

			$result = $db->loadColumn();

			if ($result)
			{
				$this->sales_persons = $result;
			}
		}

		return true;
	}

	/**
	 * Get Children Ids
	 *
	 * @param   integer  $pk  The primary key of the node to delete.
	 *
	 * @return  integer|array
	 */
	public function getChildrenIds($pk)
	{
		$k     = $this->_tbl_key;
		$id    = (is_null($pk)) ? $this->$k : $pk;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('node.id')
			->from($db->qn('#__redshopb_company', 'node'))
			->leftJoin(
				$db->qn('#__redshopb_company', 'parent') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('parent.deleted') . ' = 0'
			)
			->where('parent.id = ' . (int) $id)
			->where($db->qn('node.deleted') . ' = 0')
			->order('node.lft DESC');
		$db->setQuery($query);

		$results = $db->loadColumn();

		if ($results)
		{
			return $results;
		}

		return $pk;
	}

	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk         The primary key of the node to delete.
	 * @param   boolean  $children   True to delete child nodes, false to move them up a level.
	 * @param   boolean  $checkMain  Determines if main company needs to be checked to prevent it from being deleted
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null, $children = false, $checkMain = true)
	{
		// Load the specified record (if necessary)
		if (!empty($pk))
		{
			$this->load($pk);
		}

		$companies = array($this->id);

		if ($checkMain)
		{
			$mainCompanyId = $this->checkCompanyMainType();

			if (($mainCompanyId == $this->id))
			{
				$this->setError(Text::_('COM_REDSHOPB_COMPANY_MAIN_DELETE_ERROR'));

				return false;
			}
		}

		if ($children)
		{
			$companies = array_merge(array($this->id), $this->getChildrenIds($this->id));
		}

		$collectionTable = RedshopbTable::getAdminInstance('Collection');

		if (!$collectionTable->deleteCollections($companies, 'company'))
		{
			$this->setError('Collection error: ' . $collectionTable->getError());

			return false;
		}

		$addressTable = RedshopbTable::getAdminInstance('Address');

		if (!$addressTable->deleteShippingAddresses($companies, 'company'))
		{
			$this->setError('Address error: ' . $addressTable->getError());

			return false;
		}

		$userTable = RedshopbTable::getAdminInstance('User');

		if (!$userTable->deleteUsers($companies, 'company'))
		{
			$this->setError('User error: ' . $userTable->getError());

			return false;
		}

		$departmentTable = RedshopbTable::getAdminInstance('Department');

		if (!$departmentTable->deleteDepartments($companies))
		{
			$this->setError('Department error: ' . $departmentTable->getError());

			return false;
		}

		$roleTable = RedshopbTable::getAdminInstance('Role');

		if (!$roleTable->deleteRoles($companies))
		{
			$this->setError('Role error: ' . $roleTable->getError());

			return false;
		}

		$tagTable = RedshopbTable::getAdminInstance('Tag');

		if (!$tagTable->deleteTags($companies))
		{
			$this->setError('Tag error: ' . $tagTable->getError());

			return false;
		}

		$categoryTable = RedshopbTable::getAdminInstance('Category');

		if (!$categoryTable->deleteCategories($companies))
		{
			$this->setError('Category error: ' . $categoryTable->getError());

			return false;
		}

		$offerTable = RedshopbTable::getAdminInstance('Offer');

		if (!$offerTable->deleteOffers($companies))
		{
			$this->setError('Offer error: ' . $offerTable->getError());

			return false;
		}

		$productTable = RedshopbTable::getAdminInstance('Product');

		if (!$productTable->deleteProducts($companies))
		{
			$this->setError('Product error: ' . $productTable->getError());

			return false;
		}

		/** @var RedshopbTableProduct_Price $priceTable */
		$priceTable = RedshopbTable::getAdminInstance('Product_Price');

		if (!$priceTable->deleteBySalesId(implode(',', $companies), 'customer_price'))
		{
			$this->setError('Product price error: ' . $priceTable->getError());

			return false;
		}

		/** @var RedshopbTableProduct_Discount $discountTable */
		$discountTable = RedshopbTable::getAdminInstance('Product_Discount');

		if (!$discountTable->deleteBySalesId(implode(',', $companies), 'debtor'))
		{
			$this->setError('Product discount error: ' . $discountTable->getError());

			return false;
		}

		if (parent::delete($this->id, $children))
		{
			if ($this->address_id)
			{
				/** @var RedshopbTableAddress $addressTable */
				$addressTable = RedshopbTable::getAdminInstance('Address')
					->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

				if (!$addressTable->delete($this->address_id, true))
				{
					$this->setError('Address error: ' . $addressTable->getError());

					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Overriden asset name
	 *
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_redshopb.company.' . (int) $this->$k;
	}

	/**
	 * Overriden asset title
	 *
	 * @return  string
	 */
	protected function _getAssetTitle()
	{
		return $this->name;
	}

	/**
	 * Overriden to set the right parent ID in asset table
	 *
	 * @param   Table    $table  A Table object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 */
	public function getRedshopbAssetParentId($table = null, $id = null)
	{
		// This is a company under another company
		if (!$this->parent_id)
		{
			return parent::_getAssetParentId($table, $id);
		}

		// Build the query to get the asset id of the parent company.
		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName('asset_id'));
		$query->from($this->_db->quoteName($this->_tbl));
		$query->where($this->_db->quoteName('id') . ' = ' . (int) $this->parent_id);

		// Get the asset id from the database.
		$this->_db->setQuery($query);

		$result = $this->_db->loadResult();

		if (!$result)
		{
			return null;
		}

		return (int) $result;
	}

	/**
	 * Create the joomla groups and roles for this company.
	 *
	 * @param   int  $companyId  Company ID
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function createRoles($companyId)
	{
		$db = $this->_db;

		// Initializes or tries to initialize ACL roles
		RedshopbHelperACL::initializeACL();

		// Get the role type ids
		$types = RedshopbHelperRole::getTypeIds();

		// Get the existing roles and their type id (excluding the company role)
		$query = $db->getQuery(true)
			->select('r.role_type_id')
			->from($db->qn('#__redshopb_role', 'r'))
			->leftJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON rt.id = r.role_type_id')
			->leftJoin($db->qn('#__usergroups', 'ug') . ' ON ug.id = r.joomla_group_id')
			->where('ug.id IS NOT NULL')
			->where('r.company_id = ' . $db->q($companyId))
			->where('rt.company_role = 0');
		$db->setQuery($query);
		$existingTypeIds = $db->loadColumn();

		if (!is_array($existingTypeIds))
		{
			$existingTypeIds = array();
		}

		$existingTypeIds = Joomla\Utilities\ArrayHelper::toInteger($existingTypeIds);

		// Check the role that needs to be created.
		$typeIdsToCreate = Array();

		foreach ($types as $type)
		{
			if (!in_array($type->id, $existingTypeIds))
			{
				$typeIdsToCreate[] = $type;
			}
		}

		// If we have some roles to create.
		if (!empty($typeIdsToCreate))
		{
			foreach ($typeIdsToCreate as $createdType)
			{
				/** @var RedshopbTableRole $roleTable */
				$roleTable = RedshopbTable::getAdminInstance('Role');
				$roleId    = 0;

				if ($roleTable->load(array('role_type_id' => $createdType->id, 'company_id' => $companyId)))
				{
					$roleId = $roleTable->get('id');
				}

				// Create the role.
				if (!$roleTable->save(
					array(
						'id'              => $roleId,
						'role_type_id'    => $createdType->id,
						'company_id'      => $companyId,
						'joomla_group_id' => null,
					)
				))
				{
					return null;
				}
			}
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 *                            If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$mainCompanyId = $this->checkCompanyMainType();

		if (in_array($mainCompanyId, $pks))
		{
			$this->setError(Text::_('COM_REDSHOPB_COMPANY_MAIN_STATUS_ERROR'));

			return false;
		}

		// Update children categories state follow this state
		if (!is_array($pks))
		{
			$pks = array($pks);
		}

		$pks = ArrayHelper::toInteger($pks);

		foreach ($pks as $companyId)
		{
			$childrens = RedshopbEntityCompany::load($companyId)->getChildrenIds();
			$pks       = array_merge($pks, $childrens);
		}

		$pks = array_unique($pks);

		if (!parent::publish($pks, $state, $userId))
		{
			return false;
		}

		if ($state == 0)
		{
			$db  = Factory::getDbo();
			$pks = ArrayHelper::toInteger($pks);

			// If there are no primary keys set check to see if the instance key is set.
			if (empty($pks))
			{
				$key = $this->getKeyName();

				if ($this->{$key})
				{
					$pks = array($this->{$key});
				}

				// Nothing to set publishing state on, return false.
				else
				{
					$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

					return false;
				}
			}

			$subQuery = $db->getQuery(true)
				->select('node.id')
				->from($db->qn('#__redshopb_company', 'node'))
				->leftJoin(
					$db->qn('#__redshopb_company', 'parent') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND '
					. $db->qn('parent.deleted') . ' = 0'
				)
				->where('parent.id IN (' . implode(',', $pks) . ')')
				->where($db->qn('node.deleted') . ' = 0');

			$query = $db->getQuery(true)
				->select('ru.joomla_user_id')
				->from($db->qn('#__redshopb_user', 'ru'))
				->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = ru.id')
				->where('umc.company_id IN (' . $subQuery . ')');
			$db->setQuery($query);

			$results = $db->loadColumn();

			if ($results)
			{
				$app = Factory::getApplication();

				foreach ($results as $result)
				{
					$app->logout($result, array('clientid' => 0));
				}
			}
		}

		return true;
	}
}
