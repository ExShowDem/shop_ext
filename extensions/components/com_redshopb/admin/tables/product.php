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
use Joomla\CMS\Language\Text;

/**
 * Product table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'wash_care_relate.store' => false,
		'category_relate.store' => true,
		'category_filter_fieldset_relate.store' => true,
		'tag_relate.store' => true,
		'company_relate.store' => true,
		'fields_relate.store' => true,
		'webservice_permission.store' => true,
		'deleteMissingFields' => false,
		'forceCategoryOrdering' => false
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * Main category
	 *
	 * @var  integer
	 */
	public $category_id;

	/**
	 * This is an array of category_id from
	 * the product_category_xref table.
	 *
	 * @var  array
	 */
	public $categories;

	/**
	 * This is an array of wash_care_spec_id from
	 * the product_wash_care_spec_xref table.
	 *
	 * @var  array
	 */
	protected $wash_care_spec_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  string
	 */
	public $sku;

	/**
	 * @var  string
	 */
	public $manufacturer_sku;

	/**
	 * @var  string
	 */
	public $related_sku;

	/**
	 * @var  integer
	 */
	public $company_id;

	/**
	 * @var  string
	 */
	public $stock_upper_level = 0;

	/**
	 * @var  string
	 */
	public $stock_lower_level = 0;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  integer
	 */
	public $discontinued = 0;

	/**
	 * @var  integer
	 */
	public $featured = 0;

	/**
	 * @var integer
	 */
	public $manufacturer_id = null;

	/**
	 * @var integer
	 */
	public $decimal_position = null;

	/**
	 * @var float
	 */
	public $min_sale = 1;

	/**
	 * @var float
	 */
	public $max_sale = null;

	/**
	 * @var float
	 */
	public $pkg_size = 1;

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
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var string
	 */
	public $date_new = '0000-00-00';

	/**
	 * @var integer
	 */
	public $unit_measure_id = null;

	/**
	 * @var integer
	 */
	public $template_id = null;

	/**
	 * @var integer
	 */
	public $print_template_id = null;

	/**
	 * @var integer
	 */
	public $filter_fieldset_id = null;

	/**
	 * @var integer
	 */
	public $hits = '';

	/**
	 * @var array
	 */
	public $tag_id;

	/**
	 * This is an array of category_id from
	 * the product_company_xref table.
	 *
	 * @var  array
	 */
	protected $customer_ids;

	/**
	 * @var  array
	 */
	protected $ordering;

	/**
	 * @var integer
	 */
	public $service = 0;

	/**
	 * @var int|null
	 */
	public $tax_group_id;

	/**
	 * Layout params in JSON format.
	 *
	 * @var  integer
	 */
	public $params;

	/**
	 * @var  float
	 */
	public $weight;

	/**
	 * @var  float
	 */
	public $volume;

	/**
	 * @var string
	 */
	public $publish_date = '0000-00-00';

	/**
	 * @var string
	 */
	public $unpublish_date = '0000-00-00';

	/**
	 * @var  integer
	 */
	public $calc_type;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Media' => 'product_id',
		'Stockroom_Product_Xref' => 'product_id',
		'Product_Accessory' => array(
			'product_id',
			'accessory_product_id'
		),
		'Product_Description' => 'product_id',
		'Field_Data' => array(
			'_key' => array(
				'item_id',
				'subitem_id'
			),
			'_extrajoins' => array(
				'field' => 'field.id = _table.field_id'
			),
			'_conditions' => array(
				'scope' => 'product'
			)
		),
		'Product_Price' => 'product_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.product'
		),
		'erp' => array(
			'ws.product'
		),
		'b2b' => array(
			'erp.webservice.products'
		),
		'fengel' => array(
			'fengel.product'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'company_id' => array(
			'model' => 'Companies'
		),
		'category_id' => array(
			'model' => 'Categories'
		),
		'manufacturer_id' => array(
			'model' => 'Manufacturers'
		),
		'filter_fieldset_id' => array(
			'model' => 'Filter_Fieldsets'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'template_code' => 'Templates',
		'unit_measure_code' => 'Unit_Measures'
	);

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'tag_id' => 'Tags',
		'categories' => 'Categories',
		'customer_ids' => 'Companies'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state', 'service', 'discontinued', 'featured');

	/**
	 * WS sync map of date fields to prevent invalid dates
	 *
	 * @var  array
	 */
	protected $wsSyncMapDate = array('date_new');

	/**
	 * Fields with data needed for saving
	 *
	 * @var  array
	 */
	protected $fields;

	/**
	 * Definition of a key to act as a parent to the entity for alias creation purposes
	 *
	 * @var  string
	 */
	protected $entityParentKey = 'category_id';

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->categories        = null;
		$this->wash_care_spec_id = null;
		$this->tag_id            = null;
		$this->customer_ids      = null;

		parent::reset();
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (!empty($this->categories))
		{
			// Sanitize category id
			$this->categories = array_values(array_unique($this->categories, SORT_STRING));
		}

		// Sanitize the company_id
		if (empty($this->company_id))
		{
			$this->company_id = null;
		}

		// Sanitize the category_id
		if (empty($this->category_id))
		{
			$this->category_id = null;
		}

		if (empty($this->unit_measure_id))
		{
			$this->unit_measure_id = null;
		}

		// Sanitize the customer_ids
		if (empty($this->customer_ids))
		{
			$this->customer_ids = null;
		}

		if (empty($this->tax_group_id))
		{
			$this->tax_group_id = null;
		}

		// Make sure there is no other product with the same SKU
		$product = clone $this;
		$product->setOption('itemLoadDependencies', $this->getOption('itemLoadDependencies', false));

		if ($product->load(array('sku' => $this->sku)) && $product->id != $this->id && $product->company_id == $this->company_id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_PRODUCT_SKU_ALREADY_TAKEN', $this->sku));

			return false;
		}

		if (!empty($this->category_id) && !in_array($this->category_id, $this->categories))
		{
			$this->categories[] = $this->category_id;

			// We add this because we do not want to lock it because of category_id change
			$this->propertiesAfterLoad['categories'][] = $this->category_id;
		}

		// Checking for categories, removing old category id from the list unless it is explicitly set
		if ($this->id && $product->load($this->id))
		{
			// If the main category ID is changed
			if ($product->category_id != $this->category_id)
			{
				foreach ($this->categories as $key => $categoryId)
				{
					if ($categoryId == $product->category_id)
					{
						unset($this->categories[$key]);

						// We unset old value so we dont lock this field only because of the category_id change
						foreach ($this->propertiesAfterLoad['categories'] as $keyNum => $value)
						{
							if ($value == $categoryId)
							{
								unset($this->propertiesAfterLoad['categories'][$keyNum]);
								break;
							}
						}

						break;
					}
				}
			}
		}

		// Format stock lower and upper due to decimal position
		$this->stock_lower_level = RedshopbHelperProduct::decimalFormat($this->stock_lower_level, $this->id);
		$this->stock_upper_level = RedshopbHelperProduct::decimalFormat($this->stock_upper_level, $this->id);

		return true;
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		if ($this->getOption('itemLoadDependencies', true))
		{
			if (!$this->loadCategoryXref()
				|| !$this->loadWashCareSpecXref()
				|| !$this->loadTagXref()
				|| !$this->loadCompanyXref())
			{
				return false;
			}
		}

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if ($this->id)
		{
			$isNew = false;
		}
		else
		{
			$isNew = true;
		}

		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if ($this->getOption('category_relate.store') && !$this->storeCategoryXref($isNew))
		{
			return false;
		}

		if ($this->getOption('category_filter_fieldset_relate.store')
			&& !$this->storeCategoryFilterFieldsetRelate())
		{
			return false;
		}

		if ($this->getOption('fields_relate.store') && !$this->storeProductFields())
		{
			return false;
		}

		if ($this->getOption('wash_care_relate.store') && !$this->storeWashCareSpecXref())
		{
			return false;
		}

		if ($this->getOption('tag_relate.store') && !$this->storeTagXref())
		{
			return false;
		}

		if ($this->getOption('company_relate.store') && !$this->storeCompanyXref())
		{
			return false;
		}

		if ($this->getOption('webservice_permission.store')
			&& !RedshopbHelperWebservice_Permission::savePermissionsForProduct($this))
		{
			return false;
		}

		return true;
	}

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		if (!$this->category_id)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_PRODUCT_NO_CATEGORY_NO_SHOP', $this->name), 'warning');
		}

		return parent::afterStore($updateNulls);
	}

	/**
	 * Store the wash and care x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeWashCareSpecXref()
	{
		if (!isset($this->wash_care_spec_id))
		{
			return true;
		}

		// Delete all items
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_product_wash_care_spec_xref'))
			->where($db->qn('product_id') . ' = ' . (int) $this->id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (!is_array($this->wash_care_spec_id) || count($this->wash_care_spec_id) <= 0)
		{
			return true;
		}

		$xrefTable = RedshopbTable::getAdminInstance('Product_Wash_Care_Spec_Xref');

		// Store the new items
		foreach ($this->wash_care_spec_id as $washCareSpec)
		{
			$keys = array('product_id' => $this->id,'wash_care_spec_id' => $washCareSpec['id']);

			if ($xrefTable->load($keys))
			{
				// Already a record so we don't need to create one.
				continue;
			}

			$keys['ordering'] = $washCareSpec['ordering'];

			if (!$xrefTable->save($keys))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Store the category x references
	 *
	 * @param   boolean  $isNew  Is product new.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeCategoryXref($isNew)
	{
		if (!isset($this->categories))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_product_category_xref')
			->where($db->qn('product_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (!is_array($this->categories) || count($this->categories) <= 0)
		{
			return true;
		}

		/** @var RedshopbTableProduct_Category_Xref $xrefTable */
		$xrefTable = RedshopbTable::getAdminInstance('Product_Category_Xref');

		if ($this->getOption('forceCategoryOrdering'))
		{
			$xrefTable->setOption('forceOrderingValues', true);
		}

		// Store the new items
		foreach ($this->categories as $categoryId)
		{
			$keys = array('product_id' => $this->id, 'category_id' => $categoryId);
			$vals = $keys;

			if ($isNew && empty($this->ordering[$categoryId]))
			{
				$query->clear()
					->select(
						'MAX(' . $db->qn('ordering') . ')+1 AS ' . $db->qn('ordering')
					)
					->from($db->qn('#__redshopb_product_category_xref'))
					->where($db->qn('category_id') . ' = ' . (int) $this->category_id);
				$ordering = $db->setQuery($query)->loadResult();

				if (!empty($ordering))
				{
					$vals['ordering'] = $ordering;
				}
			}
			elseif (!empty($this->ordering[$categoryId]))
			{
				$vals['ordering'] = $this->ordering[$categoryId];
			}

			if ($xrefTable->load($keys))
			{
				continue;
			}

			if (!$xrefTable->save($vals))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Store the category filter fieldset x references relation
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeCategoryFilterFieldsetRelate()
	{
		if (empty($this->filter_fieldset_id))
		{
			return true;
		}

		if (is_array($this->categories) && count($this->categories) > 0)
		{
			// Store the new items
			foreach ($this->categories as $categoryId)
			{
				/** @var RedshopbTableCategory $xrefTable */
				$xrefTable = RedshopbTable::getAdminInstance('Category');

				if (!$xrefTable->load(array('id' => $categoryId)))
				{
					$data = array(
						'id' => $categoryId,
						'filter_fieldset_id' => $this->filter_fieldset_id
					);

					if (!$xrefTable->save($data))
					{
						$this->setError($xrefTable->getError());

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Store product fields
	 *
	 * @return  boolean  True on success, false otherwise
	 * @throws Exception
	 */
	private function storeProductFields()
	{
		if (isset($this->fields))
		{
			return RedshopbHelperField::storeScopeFieldData(
				'product', $this->id, 0, $this->fields,
				$this->getOption('deleteMissingFields', false), $this->getOption('lockingMethod', 'User')
			);
		}

		return true;
	}

	/**
	 * Store the company x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeCompanyXref()
	{
		if (!isset($this->customer_ids))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_product_company_xref')
			->where($db->qn('product_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (!is_array($this->customer_ids) || count($this->customer_ids) <= 0)
		{
			return true;
		}

		$xrefTable = RedshopbTable::getAdminInstance('Product_Company_Xref');

		// Store the new items
		foreach ($this->customer_ids as $customerId)
		{
			$keys = array('product_id' => $this->id, 'company_id' => $customerId);

			if ($xrefTable->load($keys))
			{
				continue;
			}

			if (!$xrefTable->save($keys))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Store the tag x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeTagXref()
	{
		if (!isset($this->tag_id))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_product_tag_xref')
			->where($db->qn('product_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		if (!is_array($this->tag_id) || count($this->tag_id) <= 0)
		{
			return true;
		}

		$xrefTable = RedshopbTable::getAdminInstance('Product_Tag_Xref');

		// Store the new items
		foreach ($this->tag_id as $tagId)
		{
			$keys = array('product_id' => $this->id, 'tag_id' => $tagId);

			if ($xrefTable->load($keys))
			{
				continue;
			}

			if (!$xrefTable->save($keys))
			{
				$this->setError($xrefTable->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Load the WashCareSpec related to this products
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadWashCareSpecXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('wash_care_spec_id AS id')
			->select('ordering')
			->from($db->qn('#__redshopb_product_wash_care_spec_xref'))
			->where($db->qn('product_id') . ' = ' . (int) $this->id);
		$db->setQuery($query);
		$washCareSpecId = $db->loadAssocList();

		if (!is_array($washCareSpecId))
		{
			$washCareSpecId = array();
		}

		$this->wash_care_spec_id = $washCareSpecId;

		return true;
	}

	/**
	 * Load the categories related to this products
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadCategoryXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redshopb_product_category_xref')
			->where($db->qn('product_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);
		$catXrefs   = $db->loadObjectList();
		$categories = array();
		$orderings  = array();

		foreach ($catXrefs as $catXref)
		{
			$catId             = $catXref->category_id;
			$categories[]      = $catId;
			$orderings[$catId] = $catXref->ordering;
		}

		$this->categories = $categories;
		$this->ordering   = $orderings;

		return true;
	}

	/**
	 * Load the companies related to this product
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadCompanyXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('company_id')
			->from('#__redshopb_product_company_xref')
			->where($db->qn('product_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$companyId = $db->loadColumn();

		if (!is_array($companyId))
		{
			$companyId = array();
		}

		$this->customer_ids = $companyId;

		return true;
	}

	/**
	 * Load the tags related to this product
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadTagXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('tag_id')
			->from('#__redshopb_product_tag_xref')
			->where($db->qn('product_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$tagId = $db->loadColumn();

		if (!is_array($tagId))
		{
			$tagId = array();
		}

		$this->tag_id = $tagId;

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		$db  = $this->_db;
		$ids = $pk;

		// Initialise variables.
		$key = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($ids))
		{
			// Sanitize input.
			$ids = ArrayHelper::toInteger($ids);
			$ids = RHelperArray::quote($ids);
			$ids = implode(',', $ids);
		}

		$ids = (is_null($ids)) ? $this->$key : $ids;

		// If no primary key is given, return false.
		if ($ids === null)
		{
			return false;
		}

		$db->transactionStart();

		try
		{
			$query = $db->getQuery(true);

			// ERP records deleted before actual deletes are executed, otherwise without the parent records, sync records won't be deleted
			$this->deleteErpRecords(explode(',', $ids));

			$query->delete('#__redshopb_product_price')
				->where('product_id IN(' . $ids . ')');
			$db->setQuery($query)->execute();

			$query->clear()
				->select('oix.offer_id')
				->from($db->qn('#__redshopb_offer_item_xref', 'oix'))
				->where('oix.product_id IN (' . $ids . ')')
				->group('oix.offer_id');
			$offers = $db->setQuery($query)
				->loadColumn();

			if (!empty($offers))
			{
				$query->clear()
					->delete($db->qn('#__redshopb_offer_item_xref'))
					->where('product_id IN (' . $ids . ')');
				$db->setQuery($query)
					->execute();

				$query->clear()
					->update($db->qn('#__redshopb_offer'))
					->set('state = 0')
					->where('id IN (' . implode(',', $offers) . ')');
				$db->setQuery($query)
					->execute();
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_OFFER_UPUBLISH_BECAUSE_PRODUCT_CHANGES'), 'warning');
			}

			// Delete product media
			$query = $db->getQuery(true)
				->select('id')
				->from('#__redshopb_media')
				->where('product_id IN (' . $ids . ')');
			$db->setQuery($query);

			$mediaIds = $db->loadColumn();

			if ($mediaIds)
			{
				$xrefTable = RedshopbTable::getAdminInstance('Media')
					->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

				if (!$xrefTable->delete($mediaIds))
				{
					throw new Exception($xrefTable->getError());
				}
			}

			// Delete product field data
			$query = $db->getQuery(true)
				->select('fd.id')
				->from($db->qn('#__redshopb_field_data', 'fd'))
				->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON f.id = fd.field_id')
				->where('fd.item_id IN (' . $ids . ')')
				->where('f.scope = ' . $db->q('product'));
			$db->setQuery($query);

			$fieldDataIds = $db->loadColumn();

			if ($fieldDataIds)
			{
				$xrefTable = RedshopbTable::getAdminInstance('Field_Data')
					->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

				if (!$xrefTable->delete($fieldDataIds))
				{
					throw new Exception($xrefTable->getError());
				}
			}

			// Delete product items
			$query = $db->getQuery(true)
				->select('id')
				->from('#__redshopb_product_item')
				->where('product_id IN (' . $ids . ')');

			$productItemIds = $db->setQuery($query)->loadColumn();

			if ($productItemIds)
			{
				$xrefTable = RedshopbTable::getAdminInstance('product_Item')
					->setOption('forceWebserviceUpdate', $this->getOption('forceWebserviceUpdate', false))
					->setOption('lockingMethod', $this->getOption('lockingMethod', 'User'));

				if (!$xrefTable->delete($productItemIds))
				{
					throw new Exception($xrefTable->getError());
				}
			}

			if (!parent::delete($pk))
			{
				throw new Exception($this->getError());
			}
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$this->setError($e->getMessage());
			}

			$db->transactionRollback();

			return false;
		}

		$db->transactionCommit();

		return true;
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!parent::bind($src, $ignore))
		{
			return false;
		}

		if ($this->id
			&& isset($src['company_id'])
			&& $src['company_id'] == '')
		{
			$this->company_id = null;
			$this->setOption('storeNulls', true);
		}

		if (isset($src['template_id']) && $src['template_id'] == '')
		{
			$this->template_id = null;
			$this->setOption('storeNulls', true);
		}

		if (isset($src['print_template_id']) && $src['print_template_id'] == '')
		{
			$this->print_template_id = null;
			$this->setOption('storeNulls', true);
		}

		if (isset($src['filter_fieldset_id']) && $src['filter_fieldset_id'] == '')
		{
			$this->filter_fieldset_id = null;
			$this->setOption('storeNulls', true);
		}

		if (isset($src['manufacturer_id']) && $src['manufacturer_id'] == '')
		{
			$this->manufacturer_id = null;
			$this->setOption('storeNulls', true);
		}

		if (isset($src['decimal_position']) && $src['decimal_position'] == '')
		{
			$this->decimal_position = null;
			$this->setOption('storeNulls', true);
		}

		return true;
	}

	/**
	 * Delete Products
	 *
	 * @param   string/array  $pk  Array of company ids or ids comma separated
	 *
	 * @return boolean
	 */
	public function deleteProducts($pk)
	{
		// Initialise variables.
		$key = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->{$key} : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('id'))
			->from($db->qn('#__redshopb_product'))
			->where('company_id IN (' . $pk . ')');
		$db->setQuery($query);

		$products = $db->loadColumn();

		if (!$products)
		{
			return true;
		}

		foreach ($products as $productId)
		{
			if ($this->load($productId, true) && !$this->delete($productId))
			{
				return false;
			}
		}

		return true;
	}
}
