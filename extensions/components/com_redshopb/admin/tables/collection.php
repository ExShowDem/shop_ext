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
 * Collection table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCollection extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'departments.load' => true,
		'departments.store' => false,
		'products.store' => false,
		'product_items.store' => false,
		'product_items.update_only_state' => true,
		'product_items.update_only_price' => false,
		'products.load' => false,
		'product_items.load' => false,
		'ownProducts.update' => true,
		'ownProductItems.update' => true,
		'product.update_only_price' => false
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_collection';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * @var  integer
	 */
	public $company_id;

	/**
	 * @var  integer
	 */
	public $currency_id;

	/**
	 * This is an array of department id from
	 * the collection_department_xref table.
	 *
	 * @var  array
	 */
	protected $department_ids;

	/**
	 * This is an array of product ids from
	 * the collection_product_xref table.
	 *
	 * @var  array
	 */
	protected $product_ids;

	/**
	 * This is an array of product item ids from
	 * the collection_product_item_xref table.
	 *
	 * @var  array
	 */
	protected $product_item_ids;

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
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->department_ids   = null;
		$this->product_ids      = null;
		$this->product_item_ids = null;

		parent::reset();
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// Sanitize department ids
		if ($this->department_ids)
		{
			$this->department_ids = array_unique($this->department_ids, SORT_STRING);
		}

		// Default value for product_ids
		if (empty($this->product_ids))
		{
			$this->product_ids = array();
		}

		// Default value for product_item_ids
		if (empty($this->product_item_ids))
		{
			$this->product_item_ids = array();
		}

		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_NAME_CANNOT_BE_EMPTY'));

			return false;
		}

		return true;
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
	 */
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset))
		{
			return false;
		}

		if ($this->getOption('departments.load'))
		{
			if (!$this->loadDepartmentXref())
			{
				return false;
			}
		}

		if ($this->getOption('products.load'))
		{
			if (!$this->loadProductXref())
			{
				return false;
			}
		}

		if ($this->getOption('product_items.load'))
		{
			if (!$this->loadProductItemXref())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Load the product items related to this collection
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	public function loadProductItemXref()
	{
		$db = $this->_db;

		if (!is_array($this->product_item_ids))
		{
			$this->product_item_ids = array();
		}

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('product_item_id', 'id'),
					$db->qn('wpix.price'),
					$db->qn('wpix.state')
				)
			)
			->from($db->qn('#__redshopb_collection_product_item_xref', 'wpix'))
			->where('wpix.collection_id = ' . $db->q($this->id));

		$db->setQuery($query);

		$this->product_item_ids = $db->loadAssocList('id');

		return true;
	}

	/**
	 * Load the products related to this collection
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	public function loadProductXref()
	{
		$db = $this->_db;

		if (!is_array($this->product_ids))
		{
			$this->product_ids = array();
		}

		$query = $db->getQuery(true)
			->select('wpx.product_id')
			->from($db->qn('#__redshopb_collection_product_xref', 'wpx'))
			->where('wpx.collection_id = ' . $db->q($this->id));

		$db->setQuery($query);

		$productsId = $db->loadColumn();

		if ($productsId)
		{
			foreach ($productsId as $productId)
			{
				$this->product_ids[] = array('id' => $productId);
			}
		}

		return true;
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
		$layout = Factory::getApplication()->input->getCmd('layout');

		if ($layout == 'create' || $layout == 'edit' || !$layout)
		{
			$this->setOption('departments.store', true);
		}
		elseif ($layout == 'create_products')
		{
			$this->setOption('products.store', true);
		}
		elseif ($layout == 'create_product_items')
		{
			$this->setOption('product_items.store', true);
		}

		if (parent::store($updateNulls))
		{
			// Store the departments
			if ($this->getOption('departments.store'))
			{
				if (!$this->storeDepartmentXref())
				{
					return false;
				}
			}

			// Store the products
			if ($this->getOption('products.store'))
			{
				if (!$this->storeProductXref())
				{
					return false;
				}
			}

			// Store the product items
			if ($this->getOption('product_items.store'))
			{
				if (!$this->storeProductItemXref())
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Load the departments related to this products
	 *
	 * @return  boolean  True on sucess, false otherwise
	 */
	private function loadDepartmentXref()
	{
		$db = $this->_db;

		$query = $db->getQuery(true)
			->select('wd.department_id')
			->from($db->qn('#__redshopb_collection_department_xref', 'wd'))
			->where('wd.collection_id = ' . $db->q($this->id));

		$db->setQuery($query);

		$departmentsId = $db->loadColumn();

		if (!is_array($departmentsId))
		{
			$departmentsId = array();
		}

		$this->department_ids = $departmentsId;

		return true;
	}

	/**
	 * Delete Collections
	 *
	 * @param   string/array  $pk            Array of ids or ids comma separated
	 * @param   string        $customerType  Customer type
	 *
	 * @return boolean
	 */
	public function deleteCollections($pk, $customerType)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		$db = Factory::getDbo();

		switch ($customerType)
		{
			case 'department':
				$subQuery = $db->getQuery(true)
					->select('w.id')
					->from($db->qn('#__redshopb_collection', 'w'))
					->leftJoin($db->qn('#__redshopb_collection_department_xref', 'wdx') . ' ON wdx.collection_id = w.id')
					->where('wdx.department_id IN (' . implode(',', $pk) . ')')
					->group('w.id');
				$query    = $db->getQuery(true)
					->select('wdx.*')
					->from($db->qn('#__redshopb_collection_department_xref', 'wdx'))
					->where('wdx.collection_id IN (' . $subQuery . ')')
					->where('wdx.department_id NOT IN (' . implode(',', $pk) . ')')
					->order('wdx.collection_id ASC');
				$db->setQuery($query);
				$collectionsForUpdate = array();

				$results = $db->loadObjectList();

				if ($results)
				{
					$collectionsDepartments = array();

					foreach ($results as $result)
					{
						if (!isset($collectionsDepartments[$result->collection_id]))
						{
							$collectionsDepartments[$result->collection_id] = array();
						}

						$collectionsDepartments[$result->collection_id][] = $result->department_id;
					}

					$this->setOption('departments.store', true);

					foreach ($collectionsDepartments as $id => $collectionDepartments)
					{
						if ($this->load($id, true))
						{
							$collectionsForUpdate[] = $id;
							$row                    = array('department_ids' => $collectionDepartments);

							if (!$this->save($row))
							{
								return false;
							}
						}
					}
				}

				$query->clear()
					->select('w2.id')
					->from($db->qn('#__redshopb_collection', 'w2'))
					->where('w2.id IN (' . $subQuery . ')');

				if (count($collectionsForUpdate) > 0)
				{
					$query->where('w2.id NOT IN(' . implode(',', $collectionsForUpdate) . ')');
				}

				$db->setQuery($query);

				$results = $db->loadColumn();

				if ($results)
				{
					foreach ($results as $result)
					{
						if ($this->load($result, true))
						{
							if (!$this->delete($result))
							{
								return false;
							}
						}
					}
				}

				break;
			case 'company':
				$query = $db->getQuery(true)
					->select('w.id')
					->from($db->qn('#__redshopb_collection', 'w'))
					->where('w.company_id IN (' . implode(',', $pk) . ')');
				$db->setQuery($query);

				$collections = $db->loadColumn();

				if ($collections)
				{
					foreach ($collections as $collectionId)
					{
						if ($this->load($collectionId, true))
						{
							if (!$this->delete($collectionId))
							{
								return false;
							}
						}
					}
				}
				break;
		}

		return true;
	}

	/**
	 * Store the department x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeDepartmentXref()
	{
		if (!isset($this->department_ids))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_collection_department_xref')
			->where('collection_id = ' . $db->q($this->id));

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		/** @var RedshopbTableCollection_Department_Xref $xrefTable */
		$xrefTable = RedshopbTable::getAdminInstance('Collection_Department_Xref');

		// Store the new items
		foreach ($this->department_ids as $departmentId)
		{
			if (!$xrefTable->load(
				array(
					'collection_id' => $this->id,
					'department_id' => $departmentId
				)
			))
			{
				if (!$xrefTable->save(
					array(
						'collection_id' => $this->id,
						'department_id' => $departmentId
					)
				))
				{
					$this->setError($xrefTable->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Store the product x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeProductXref()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('wpx.product_id, wpx.price')
			->from($db->qn('#__redshopb_collection_product_xref', 'wpx'))
			->where('wpx.collection_id = ' . (int) $this->id);

		if ($this->getOption('ownProducts.update', true) == false)
		{
			$query->innerJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = wpx.product_id AND s.reference = ' . $db->q('fengel.product'));
		}

		$products = $db->setQuery($query)->loadAssocList('product_id', 'price');

		if ($this->getOption('product.update_only_price', false) == true)
		{
			foreach ($this->product_ids as $product)
			{
				if (isset($products[$product['id']]) && $product['price'] != $products[$product['id']])
				{
					$query->clear()
						->update($db->qn('#__redshopb_collection_product_xref'))
						->set('price = ' . $db->q($product['price']))
						->where('collection_id = ' . (int) $this->id)
						->where('product_id = ' . (int) $product['id']);
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
				}
			}
		}
		else
		{
			$xrefTable      = RedshopbTable::getAdminInstance('Collection_Product_Xref');
			$xrefItemsTable = RedshopbTable::getAdminInstance('Collection_Product_Item_Xref');

			foreach ($this->product_ids as $product)
			{
				if (!isset($products[$product['id']]))
				{
					if (!$xrefTable->load(
						array(
							'collection_id' => $this->id,
							'product_id' => $product['id']
						)
					))
					{
						if (!$xrefTable->save(
							array(
								'collection_id' => $this->id,
								'product_id' => $product['id']
							)
						))
						{
							$this->setError($xrefTable->getError());

							return false;
						}
					}

					// Set all product items as selected if product relate store first
					$query->clear()
						->select('pi.id')
						->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_item_id = pi.id')
						->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = piavx.product_attribute_value_id')
						->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
						->from($db->qn('#__redshopb_product_item', 'pi'))
						->where('pi.product_id = ' . (int) $product['id'])
						->where('pi.state = 1')
						->where('pav.state = 1')
						->where('pa.state = 1')
						->where('pi.discontinued = 0');
					$db->setQuery($query);

					$productItems = $db->loadColumn();

					if ($productItems)
					{
						foreach ($productItems as $productItem)
						{
							if (!$xrefItemsTable->load(
								array(
									'collection_id' => $this->id,
									'product_item_id' => $productItem
								), true
							))
							{
								if (!$xrefItemsTable->save(
									array(
										'collection_id' => $this->id,
										'product_item_id' => $productItem,
										'price' => 0,
										'state' => 1
									)
								))
								{
									$this->setError($xrefTable->getError());

									return false;
								}
							}
						}
					}
				}
				else
				{
					unset($products[$product['id']]);
				}
			}

			if (!empty($products) && count($products) > 0)
			{
				$productIds = implode(',', array_keys($products));

				$query->clear()
					->select('id')
					->from($db->qn('#__redshopb_product_item'))
					->where('product_id IN (' . $productIds . ')');
				$db->setQuery($query);

				$productItemIds = $db->loadColumn();

				if ($productItemIds)
				{
					$query->clear()
						->delete($db->qn('#__redshopb_collection_product_item_xref'))
						->where('collection_id = ' . (int) $this->id)
						->where('product_item_id IN (' . implode(',', $productItemIds) . ')');
					$db->setQuery($query);

					if (!$db->execute())
					{
						return false;
					}
				}

				$query->clear()
					->delete($db->qn('#__redshopb_collection_product_xref'))
					->where('collection_id = ' . (int) $this->id)
					->where('product_id IN (' . $productIds . ')');
				$db->setQuery($query);

				if (!$db->execute())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Store the product item x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	public function storeProductItemXref()
	{
		$db        = Factory::getDbo();
		$xrefTable = RedshopbTable::getAdminInstance('Collection_Product_Item_Xref');

		if ($this->getOption('product_items.update_only_state', false) == true)
		{
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshopb_collection_product_item_xref'))
				->where('collection_id = ' . (int) $this->id);
			$db->setQuery($query);

			$productItems = $db->loadAssocList('product_item_id');

			foreach ($this->product_item_ids as $productItem)
			{
				if (isset($productItems[$productItem['id']]) && $productItem['state'] == 1)
				{
					$query->clear()
						->update($db->qn('#__redshopb_collection_product_item_xref'))
						->set('state = ' . (int) $productItem['state'])
						->where('collection_id = ' . (int) $this->id)
						->where('product_item_id = ' . (int) $productItem['id']);
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

					unset($productItems[$productItem['id']]);
				}
				elseif (!isset($productItems[$productItem['id']]))
				{
					$query->clear()
						->insert($db->qn('#__redshopb_collection_product_item_xref'))
						->columns('collection_id, product_item_id, state')
						->values((int) $this->id . ',' . (int) $productItem['id'] . ',' . (int) $productItem['state']);
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
				}
			}

			if ($productItems && count($productItems) > 0)
			{
				$ids = array_keys($productItems);
				$query->clear()
					->update($db->qn('#__redshopb_collection_product_item_xref'))
					->set('state = 0')
					->where('collection_id = ' . (int) $this->id)
					->where('product_item_id IN (' . implode(',', $ids) . ')');
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
			}
		}
		elseif ($this->getOption('product_items.update_only_price', false) == true)
		{
			$query = $db->getQuery(true)
				->select('product_item_id, price')
				->from($db->qn('#__redshopb_collection_product_item_xref'))
				->where('collection_id = ' . (int) $this->id);
			$db->setQuery($query);

			$productItems = $db->loadAssocList('product_item_id', 'price');

			foreach ($this->product_item_ids as $productItem)
			{
				if (isset($productItems[$productItem['id']]) && $productItem['price'] != $productItems[$productItem['id']])
				{
					$query->clear()
						->update($db->qn('#__redshopb_collection_product_item_xref'))
						->set('price = ' . $db->q($productItem['price']))
						->where('collection_id = ' . (int) $this->id)
						->where('product_item_id = ' . (int) $productItem['id']);
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
				}
			}
		}
		else
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__redshopb_collection_product_item_xref'))
				->where('collection_id = ' . (int) $this->id);

			if ($this->getOption('ownProductItems.update', true) == false)
			{
				$subQuery = $db->getQuery(true)
					->select('wpix.product_item_id')
					->from($db->qn('#__redshopb_collection_product_item_xref', 'wpix'))
					->innerJoin(
						$db->qn('#__redshopb_sync', 's') . ' ON s.local_id = wpix.product_item_id AND s.reference = ' . $db->q('fengel.item_related')
					)
					->where('wpix.collection_id = ' . (int) $this->id);
				$db->setQuery($subQuery);

				$ownProductItems = $db->loadColumn();

				if ($ownProductItems)
				{
					$query->where('product_item_id IN (' . implode(',', $ownProductItems) . ')');
				}
				else
				{
					$query->where('1 = 0');
				}
			}

			$db->setQuery($query);

			if (!$db->execute())
			{
				return false;
			}

			foreach ($this->product_item_ids as $productItem)
			{
				if (!$xrefTable->load(
					array(
						'collection_id' => $this->id,
						'product_item_id' => $productItem['id']
					)
				))
				{
					if (!$xrefTable->save(
						array(
							'collection_id' => $this->id,
							'product_item_id' => $productItem['id'],
							'price' => $productItem['points'],
							'state' => $productItem['state']
						)
					))
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
	 * Save product and product items from existing collections
	 *
	 * @param   Integer  $collectionId  Collection Id
	 *
	 * @throws Exception
	 *
	 * @return  boolean  False if failed
	 */
	public function createProductsFromCollections($collectionId)
	{
		$collections = Factory::getApplication()->input->post->get('collections', '', 'string');

		if ($collections == '')
		{
			return true;
		}

		$collections = explode(',', $collections);
		$collections = ArrayHelper::toInteger($collections);

		// No collections selected, this is new custom collection
		if (count($collections) == 0)
		{
			return true;
		}

		$db = $this->_db;

		try
		{
			$db->transactionStart();

			// Collection Products

			// Delete all items
			$query = $db->getQuery(true)
				->delete('#__redshopb_collection_product_xref')
				->where('collection_id = ' . $db->q($collectionId));

			$db->setQuery($query);

			if (!$db->execute())
			{
				throw new Exception(Text::_('COM_REDSHOPB_COLLECTION_ERROR_CREATING_FROM_EXISTING'));
			}

			$query = $db->getQuery(true)
				->select('product_id')
				->from('#__redshopb_collection_product_xref')
				->where('collection_id IN (' . (implode(',', $collections)) . ')');

			$db->setQuery($query);

			$productIds = $db->loadColumn();

			/** @var RedshopbTableCollection_Product_Xref $xrefTable */
			$xrefTable = RedshopbTable::getAdminInstance('Collection_Product_Xref');

			foreach ($productIds as $productId)
			{
				if (!$xrefTable->load(
					array(
						'collection_id' => $collectionId,
						'product_id' => $productId
					)
				))
				{
					if (!$xrefTable->save(
						array(
							'collection_id' => $collectionId,
							'product_id' => $productId
						)
					))
					{
						throw new Exception($xrefTable->getError());
					}
				}
			}

			// Collection Product Items

			// Delete all items
			$query = $db->getQuery(true)
				->delete('#__redshopb_collection_product_item_xref')
				->where('collection_id = ' . $db->q($collectionId));

			$db->setQuery($query);

			if (!$db->execute())
			{
				throw new Exception(Text::_('COM_REDSHOPB_COLLECTION_ERROR_CREATING_FROM_EXISTING'));
			}

			$query = $db->getQuery(true)
				->select(array('product_item_id', 'state', 'price'))
				->from($db->qn('#__redshopb_collection_product_item_xref'))
				->where('collection_id IN (' . (implode(',', $collections)) . ')');

			$db->setQuery($query);

			$productItemIds = $db->loadObjectList();

			/** @var RedshopbTableCollection_Product_Xref $xrefTable */
			$xrefTable = RedshopbTable::getAdminInstance('Collection_Product_Item_Xref');

			foreach ($productItemIds as $productItem)
			{
				if (!$xrefTable->load(
					array(
						'collection_id' => $collectionId,
						'product_item_id' => $productItem->product_item_id
					)
				))
				{
					if (!$xrefTable->save(
						array(
							'collection_id' => $collectionId,
							'product_item_id' => $productItem->product_item_id,
							'state' => $productItem->state,
							'price' => $productItem->price
						)
					))
					{
						throw new Exception($xrefTable->getError());
					}
				}
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
