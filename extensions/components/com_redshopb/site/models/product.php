<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
jimport('models.trait.customfields', JPATH_ROOT . '/components/com_redshopb/');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
/**
 * Product Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct extends RedshopbModelAdmin
{
	use RedshopbModelsTraitCustomFields;

	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Store array minimal ordering from current product
	 *
	 * @var array
	 */
	public static $minimalOrdering = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array
	 *
	 * @throws  RuntimeException
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->setScope('product');
	}

	/**
	 * Overridden to use "Product_discount" table by default
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = 'Product', $prefix = 'RedshopbTable', $options = array())
	{
		$table = parent::getTable($name, $prefix, $options);

		// Disables xref store operations when it's a ws-related operation (because the fields won't be set so it won't try to delete xref data)
		if (property_exists($this, 'operationWS') && $this->operationWS)
		{
			$table->setOption('category_filter_fieldset_relate.store', false);
			$table->setOption('tag_relate.store', false);
			$table->setOption('company_relate.store', false);
			$table->setOption('fields_relate.store', false);
			$table->setOption('webservice_permission.store', false);
		}

		return $table;
	}

	/**
	 * Discontinue one or more products.
	 *
	 * @param   array  $pks  A list of the primary keys to change.
	 *
	 * @return  boolean  True on success.
	 */
	public function discontinueProducts(array $pks)
	{
		/** @var RedshopbTableProduct $table */
		$table = $this->getTable();
		$db    = $this->getDbo();
		$pks   = (array) $pks;

		$canEditMessageSet = false;
		$wsMessageSet      = false;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					if (!$canEditMessageSet)
					{
						Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'warning');
						$canEditMessageSet = true;
					}
				}

				if ($this->getIslockedByWebservice($pk))
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					if (!$wsMessageSet)
					{
						Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'), 'error');
						$wsMessageSet = true;
					}
				}
			}
		}

		try
		{
			// Sanitize input.
			$pks    = ArrayHelper::toInteger($pks);
			$userId = Factory::getUser()->id;

			// Update the publishing state for rows with the given primary keys.
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_product'))
				->set($db->qn('discontinued') . ' = 1');

			$checkin = false;

			// Determine if there is checkin support for the table.
			if (property_exists($table, 'checked_out') || property_exists($table, 'checked_out_time'))
			{
				$query->where('(' . $db->qn('checked_out') . ' IS NULL OR ' . $db->qn('checked_out') . ' = ' . (int) $userId . ')');
				$checkin = true;
			}

			// Build the WHERE clause for the primary keys.
			$query->where($db->qn('id') . ' IN (' . implode(',', $pks) . ')');

			$db->setQuery($query);
			$db->execute();

			// If checkin is supported and all rows were adjusted, check them in.
			if ($checkin && (count($pks) == $db->getAffectedRows()))
			{
				// Checkin the rows.
				$query->clear()
					->update($db->qn('#__redshopb_product'))
					->set($db->qn('checked_out') . ' = NULL')
					->set($db->qn('checked_out_time') . ' = ' . $db->q($db->getNullDate()))
					->where($db->qn('id') . ' IN (' . implode(',', $pks) . ')');
				$db->setQuery($query);

				// Check for a database error.
				$db->execute();
			}

			// Discontinue product items
			$query->clear()
				->update($db->qn('#__redshopb_product_item'))
				->set($db->qn('discontinued') . ' = 1')
				->where($db->qn('product_id') . ' IN (' . implode(',', $pks) . ')');

			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Overridden to convert stock_upper and stock_lower levels to decimals, if unit of measure requires it
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  false|object             Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$item = parent::getItemWS($pk, $addRelatedData);

		if (!$item)
		{
			return false;
		}

		$item->stock_upper_level = RedshopbHelperProduct::decimalFormat($item->stock_upper_level, $item->id);
		$item->stock_lower_level = RedshopbHelperProduct::decimalFormat($item->stock_lower_level, $item->id);

		return $item;
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

		$item = ArrayHelper::toObject($properties, CMSObject::class);

		// Set the defaults
		$item->categories   = array();
		$item->tag_id       = array();
		$item->customer_ids = array();

		// This is needed because toObject will transform
		// the categories ids array to an object.
		if (!empty($properties['categories']))
		{
			$item->categories = $properties['categories'];
		}

		if (!empty($properties['tag_id']))
		{
			$item->tag_id = $properties['tag_id'];
		}

		if (!empty($properties['customer_ids']))
		{
			$item->customer_ids = $properties['customer_ids'];
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		// Fallback price
		$fallbackPrice      = RedshopbEntityProduct::getInstance($item->id)->bind($item)->getFallbackPrice();
		$item->price        = 0;
		$item->retail_price = 0;

		if (!is_null($fallbackPrice))
		{
			$item->price        = $fallbackPrice->price;
			$item->retail_price = $fallbackPrice->retail_price;
		}

		$this->attachExtraFields($item);

		return $item;
	}

	/**
	 * Method to attach the extra fields data to the item
	 *
	 * @param   object  $item  the record item
	 *
	 * @return void
	 */
	private function attachExtraFields($item)
	{
		if (empty($item->id))
		{
			return;
		}

		/** @var RedshopbModelScope_Fields $model */
		$model = RModelAdmin::getInstance('Scope_Fields', 'RedshopbModel');
		$model->getState();
		$model->setState('filter.scope', 'product');
		$model->setState('filter.item_id', $item->id);

		$template             = RedshopbHelperTemplate::findTemplate('product', 'shop', $item->template_id);
		$fieldsUsedInTemplate = RedshopbHelperTemplate::getUsedTagsInTemplate($template);
		$model->setState('filter.fieldsUsedInTemplate', $fieldsUsedInTemplate);
		$this->setState('fieldsUsedInTemplate', $fieldsUsedInTemplate);

		$extraFields = $model->getItem();

		$item->extrafields = $extraFields['extrafields'];
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		/** @var Form $form */
		$form = parent::getForm(array(), true);

		if (!$form)
		{
			return false;
		}

		$this->addExtraFields($form);
		$fallbackPrice = RedshopbEntityProduct::getInstance($form->getValue('id'))->getFallbackPrice();

		if (is_null($fallbackPrice))
		{
			return $form;
		}

		$form->setValue('price', null, $fallbackPrice->price);
		$form->setValue('retail_price', null, $fallbackPrice->retail_price);

		return $form;
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
		if (!$this->operationWS)
		{
			$this->addCustomFieldsValidation($form);
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean True on success, False on error.
	 * @throws Exception
	 */
	public function save($data)
	{
		if (!$this->operationWS)
		{
			if (empty($data['categories'][0]))
			{
				$data['categories'] = array();
			}

			if (empty($data['tag_id'][0]))
			{
				$data['tag_id'] = array();
			}

			if (empty($data['customer_ids']))
			{
				$data['customer_ids'] = array();
			}
		}

		$productId = 0;

		if (isset($data['id']))
		{
			$productId = (int) $data['id'];
		}

		if (!parent::save($data))
		{
			return false;
		}

		if (!$productId)
		{
			$productId = $this->getState($this->getName() . '.id');
		}

		// Store extra fields data if available.
		if (!is_null($data['extrafields']) && is_array($data['extrafields']))
		{
			$table = $this->getTable();

			if (!RedshopbHelperField::storeScopeFieldData(
				'product', $productId, 0, $data['extrafields'], true, $table->getOption('lockingMethod', 'User')
			))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_FIELDS_SAVE_FAILURE'), 'error');
			}
		}

		// Save related price data if available
		if ($data['price'] != '')
		{
			$this->saveFallbackPrice($productId, $data['price'], 'price');
		}

		if ($data['retail_price'] != '')
		{
			$this->saveFallbackPrice($productId, $data['retail_price'], 'retail_price');
		}

		return true;
	}

	/**
	 * Set price to a product
	 *
	 * @param   int     $id      product id
	 * @param   int     $price   product price
	 * @param   string  $erpId   ERP id of the product price record
	 *
	 * @return  integer Product id | false
	 */
	public function setPrice($id, $price, $erpId = '')
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		return $this->saveFallbackPrice($id, $price, 'price', $erpId);
	}

	/**
	 * Method to save price for a product
	 *
	 * @param   int     $id      product id
	 * @param   int     $price   product price
	 * @param   string  $type    either 'price' or 'retail_price'
	 * @param   string  $erpId   ERP id of the product price record
	 *
	 * @return  integer Product id | false
	 */
	private function saveFallbackPrice($id, $price, $type, $erpId = '')
	{
		$fallbackPrice = RedshopbEntityProduct::getInstance($id)->getFallbackPrice();
		$priceTable    = RedshopbTable::getAdminInstance('Product_Price');

		if (!is_null($fallbackPrice))
		{
			if (!$priceTable->load($fallbackPrice->id))
			{
				$priceTable->id = null;
				$priceTable->reset();
			}
		}

		$priceValues = array(
			'type_id' => $id,
			'type' => 'product',
			'sales_type' => 'all_customers',
			'sales_code' => null,
			'currency_id' => RedshopbEntityCompany::getInstance(
				RedshopbEntityProduct::getInstance($id)->getItem()->company_id
			)->getCustomerCurrency(),
			'starting_date' => '0000-00-00 00:00:00',
			'ending_date' => '0000-00-00 00:00:00',
			'country_id' => null,
			'quantity_min' => null,
			'quantity_max' => null,
			$type => $price
		);

		if (!$priceTable->save($priceValues))
		{
			return false;
		}

		if ($erpId != '')
		{
			$priceModel = RedshopbModel::getFrontInstance('All_Price');

			if (!$priceModel->updateERPId($erpId, $priceTable->id))
			{
				return false;
			}
		}

		return $id;
	}

	/**
	 * Set retail price to a product
	 *
	 * @param   int     $id            product id
	 * @param   int     $retailPrice   product retail price
	 * @param   string  $erpId         ERP id of the product price record
	 *
	 * @return  integer Product id | false
	 */
	public function setRetailPrice($id, $retailPrice, $erpId = '')
	{
		return $this->saveFallbackPrice($id, $retailPrice, 'retail_price', $erpId);
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * This method overridden to check the template_code scope = product
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array|boolean
	 */
	public function validateWS($data)
	{
		$data = parent::validateWS($data);

		if (!is_array($data) || empty($data))
		{
			return false;
		}

		if (!$this->isValidTemplateCode($data))
		{
			Factory::getApplication()->enqueueMessage(Text::_($this->getError()), 'error');

			return false;
		}

		return $data;
	}

	/**
	 * Method to check if $data['template_code'] is a valid product template
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return boolean
	 *
	 * @throws ErrorException
	 */
	private function isValidTemplateCode($data)
	{
		if (empty($data['template_code']))
		{
			return true;
		}

		$templateEntity = RedshopbEntityTemplate::getInstance()->loadItem('alias', $data['template_code']);

		if ($templateEntity->get('template_group') != 'shop' || $templateEntity->get('scope') != 'product')
		{
			$this->setError(Text::_('COM_REDSHOPB_PRODUCT_WS_ERROR_INVALID_TEMPLATE_CODE'));

			return false;
		}

		return true;
	}

	/**
	 * Generates the items for a product.
	 *
	 * @param   integer  $productId  The product id
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	public function generateItems($productId)
	{
		$attributes = RedshopbHelperProduct::getAttributesAsArray($productId);

		if (empty($attributes))
		{
			$this->setError(Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_REQUIRE_SET_FROM_PRODUCT_MORE_ONE_TYPE'));

			return false;
		}

		// An array of formatted attributes for the generator
		$formattedAttributes = array();

		// Get the possible values for each attribute
		foreach ($attributes as &$attribute)
		{
			$values = RedshopbHelperProduct_Attribute::getValues($attribute['id']);

			if (!$values)
			{
				$this->setError(Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_EACH_TYPE_MUST_HAVE_AT_LEAST_ONE_ATTRIBUTE'));

				return false;
			}

			$attribute['values']                   = $values;
			$formattedAttributes[$attribute['id']] = array_keys($values);
		}

		// Generate the attribute combinations
		$combinations = RedshopbHelperProduct::generateCombinations($formattedAttributes);

		if (empty($combinations))
		{
			$this->setError(Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_NOT_HAVE_COMBINATIONS_FROM_CURRENT_PRODUCT'));

			return false;
		}

		$db             = Factory::getDbo();
		$query          = $db->getQuery(true);
		$itemTable      = RedshopbTable::getAdminInstance('Product_Item');
		$availableItems = array(0);

		foreach ($combinations as $combination)
		{
			$query->clear()
				->select('pi.id')
				->from($db->qn('#__redshopb_product_item', 'pi'))
				->where($db->qn('pi.product_id') . ' = ' . (int) $productId);

			foreach ($combination as $key => $attributeValueId)
			{
				$query->leftJoin(
					$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx' . $key) . ' ON piavx' . $key . '.product_item_id = pi.id'
				)->where('piavx' . $key . '.product_attribute_value_id = ' . $attributeValueId);
			}

			$result = $db->setQuery($query)->loadResult();

			if (!$result)
			{
				// Create the item
				$itemTable->reset();
				$itemTable->id = null;

				// Create the item
				if (!$itemTable->save(array('product_id' => $productId)))
				{
					$this->setError($itemTable->getError());

					return false;
				}

				$availableItems[]        = $itemTable->id;
				$itemAttributeValueTable = RedshopbTable::getAdminInstance('Product_Item_Attribute_Value_Xref');

				foreach ($combination as $attributeValueId)
				{
					$itemAttributeValueTable->reset();

					// Create the attribute value
					if (!$itemAttributeValueTable->save(
						array(
							'product_item_id' => $itemTable->id,
							'product_attribute_value_id' => $attributeValueId,
						)
					))
					{
						$this->setError($itemAttributeValueTable->getError());

						return false;
					}
				}

				// Set proper SKU combination
				$productItemSku = RedshopbHelperProduct_Item::getSKU($itemTable->id, true);
				$itemTable->save(
					array(
						'product_id' => $productId,
						'sku' => $productItemSku,
					)
				);
			}
			else
			{
				$availableItems[] = $result;
			}
		}

		$query->clear()
			->delete($db->qn('#__redshopb_product_item'))
			->where($db->qn('product_id') . ' = ' . (int) $productId)
			->where('id NOT IN (' . implode(',', $availableItems) . ')');
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

		return true;
	}

	/**
	 * Get the attributes associated to this product.
	 *
	 * @param   integer  $productId   The product id
	 * @param   array    $attrValues  Selected attribute value ids.
	 * @param   bool     $valsAssoc   Return attr values in assoc way (attr_id => attr_val_id)
	 * @param   bool     $showSku     Show/Hide SKU in attribute value.
	 *
	 * @return  array  An array of attributes to display
	 */
	public function getAttributes($productId, $attrValues = array(), $valsAssoc = false, $showSku = true)
	{
		$db = $this->_db;

		$query = $this->_db->getQuery(true)
			->select('*')
			->from('#__redshopb_product_attribute')
			->where($db->qn('product_id') . ' = ' . (int) $productId)
			->order('ordering ASC');

		// Filter by state
		$stateFilter = $this->getState('filter.attribute_state', '');

		if (!empty($stateFilter))
		{
			$query->where($db->qn('state') . ' = ' . (int) $stateFilter);
		}

		$attributes = $db->setQuery($query)->loadAssocList('id');

		if (!is_array($attributes))
		{
			return array();
		}

		foreach ($attributes as &$attribute)
		{
			$values = RedshopbHelperProduct_Attribute::getValues($attribute['id'], $showSku, $this->getState('filter.collectionPrices'), $attrValues, true);

			if ($attribute['main_attribute'] == 1)
			{
				$attribute['flat_values'] = $values;
			}

			if ($valsAssoc)
			{
				$attribute['values'] = $values;
			}
			else
			{
				$attribute['values'] = array_values($values);
			}
		}

		return $attributes;
	}

	/**
	 * Get the items associated to this product.
	 *
	 * @param   integer  $productId  The product id
	 *
	 * @return  array  An array of product items
	 */
	public function getProductItems($productId)
	{
		$db = $this->_db;

		$query = $this->_db->getQuery(true)
			->select('pi.*')
			->from('#__redshopb_product_item AS pi')
			->where($db->qn('pi.product_id') . ' = ' . (int) $productId);

		// Filter by collection
		$collectionPrices = $this->getState('filter.collectionPrices');

		if ($collectionPrices)
		{
			$query->leftJoin($db->qn('#__redshopb_collection_product_item_xref', 'wpi') . ' ON wpi.product_item_id = pi.id')
				->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON pi.id = piavx.product_item_id')
				->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = piavx.product_attribute_value_id')
				->innerJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id AND pa.main_attribute = 1')
				->select(
					array($db->qn('wpi.price', 'collectionPrice'), 'piavx.product_attribute_value_id', $db->qn('wpi.state', 'collection_item_state'))
				)->where('wpi.collection_id = ' . (int) $collectionPrices)

				// Get right sort for attributes and values
				->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx2') . ' ON pi.id = piavx2.product_item_id')
				->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav2') . ' ON pav2.id = piavx2.product_attribute_value_id')
				->innerJoin($db->qn('#__redshopb_product_attribute', 'pa2') . ' ON pa2.id = pav2.product_attribute_id AND pa2.main_attribute != 1')
				->group('pi.id')
				->order('pa2.ordering, pav2.ordering');
		}

		// Filter by state
		$stateFilter = $this->getState('filter.productItem_state', '');

		if (!empty($stateFilter))
		{
			$query->where($db->qn('pi.state') . ' = ' . (int) $stateFilter);
		}

		$db->setQuery($query);

		$items = $db->loadAssocList('id');

		if (!is_array($items))
		{
			return array();
		}

		foreach ($items as &$item)
		{
			$item['attributes'] = RedshopbHelperProduct_Item::getAttributeValues($item['id']);
		}

		return $items;
	}

	/**
	 * Function to get product accessories. Result is an array
	 * with accessories. If product id is not provided, current
	 * model state id is used as a product id.
	 *
	 * @param   int     $productId         Product id.
	 * @param   string  $selected          Selected accessory.
	 * @param   bool    $required          Include required accessories.
	 * @param   int     $departmentId      Department id.
	 * @param   array   $collectionIds     Include collection ids
	 * @param   int     $dropDownSelected  Id seleceted attribute value
	 * @param   int     $customerId        Customer id.
	 * @param   string  $customerType      Customer type.
	 * @param   string  $currency          Currency.
	 * @param   int     $endCustomer       End customer company id.
	 *
	 * @return  array  Array with product accessories(ids). Empty array on failure.
	 */
	public function getAccessoriesIds(
		$productId = 0, $selected = null, $required = true, $departmentId = 0, $collectionIds = array(), $dropDownSelected = 0,
		$customerId = 0, $customerType = '', $currency = 'DKK', $endCustomer = 0
	)
	{
		if ($productId == 0)
		{
			$productId = $this->getState($this->getName() . '.id');
		}

		$funcArgs                = get_defined_vars();
		$key                     = serialize($funcArgs);
		static $accessoriesArray = array();

		if (array_key_exists($key, $accessoriesArray))
		{
			return $accessoriesArray[$key];
		}

		$db          = $this->getDbo();
		$accessories = array();

		if ($productId <= 0)
		{
			$accessoriesArray[$key] = array_values($accessories);

			return $accessoriesArray[$key];
		}

		// Adding required accessories
		if ($required)
		{
			$query = $this->getAccessoriesQuery(array($productId), $departmentId, $collectionIds, array($dropDownSelected));
			$query->where($db->qn('selection') . ' = ' . $db->q('require'));

			$accessoryObjects = $db->setQuery($query)->loadObjectList();

			foreach ($accessoryObjects as $accessoryObject)
			{
				if (!RedshopbHelperShop::inCollectionMode(
					RedshopbEntityCompany::getInstance(
						RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
					)
				)
				)
				{
					$accessoryObject->price       = 0;
					$accessoryObject->currency    = $currency;
					$accessoryObject->currency_id = 0;

					$priceObject = RedshopbHelperPrices::getProductPrice(
						$accessoryObject->accessory_product_id, $customerId, $customerType, $currency, '', $endCustomer
					);

					if ($priceObject)
					{
						$accessoryObject->price       = $priceObject->price;
						$accessoryObject->currency    = $priceObject->currency;
						$accessoryObject->currency_id = $priceObject->currency_id;
					}
				}

				$accessories[$accessoryObject->accessory_id] = array(
					'accessory_id'     => $accessoryObject->accessory_id,
					'currency_id'      => $accessoryObject->currency_id,
					'currency'         => $accessoryObject->currency,
					'productId'        => $accessoryObject->accessory_product_id,
					'product_name'     => $accessoryObject->name,
					'price'            => $accessoryObject->price,
					'sku'              => $accessoryObject->sku,
					'product_sku'      => $accessoryObject->sku,
					'required'         => $accessoryObject->selection == 'require' ? 1 : 0,
					'hide_on_collection' => $accessoryObject->hide_on_collection
				);
			}
		}

		// Adding selected accessory
		if (!empty($selected))
		{
			$query    = $this->getAccessoriesQuery(array($productId), $departmentId, $collectionIds, array($dropDownSelected));
			$quantity = array();

			if (!is_array($selected))
			{
				$selected = array($selected);
			}
			elseif (is_array($selected))
			{
				$firstValue = reset($selected);

				if (is_object($firstValue))
				{
					$accessoriesIds = array();

					foreach ($selected as $oneAccessory)
					{
						$quantity[$oneAccessory->id] = $oneAccessory->quantity;
						$accessoriesIds[]            = $oneAccessory->id;
					}

					$selected = $accessoriesIds;
				}
			}

			$selected = ArrayHelper::toInteger($selected);
			$query->where('id IN (' . implode(',', $selected) . ')');

			$accessoryObjects = $db->setQuery($query)->loadObjectList();

			foreach ($accessoryObjects as $accessoryObject)
			{
				$accessories[$accessoryObject->accessory_id] = array(
					'accessory_id'     => $accessoryObject->accessory_id,
					'currency_id'      => $accessoryObject->currency_id,
					'currency'         => $accessoryObject->currency,
					'productId'        => $accessoryObject->accessory_product_id,
					'product_name'     => $accessoryObject->name,
					'price'            => $accessoryObject->price,
					'sku'              => $accessoryObject->sku,
					'product_sku'      => $accessoryObject->sku,
					'required'         => $accessoryObject->selection == 'require' ? 1 : 0,
					'hide_on_collection' => $accessoryObject->hide_on_collection,
					'quantity'         => empty($quantity) ? 1 : $quantity[$accessoryObject->accessory_id]
				);
			}
		}

		$accessoriesArray[$key] = array_values($accessories);

		return $accessoriesArray[$key];
	}

	/**
	 * Get query for getting accessories from DB.
	 *
	 * @param   array  $ids               Product ids.
	 * @param   int    $departmentId      Department id.
	 * @param   array  $collectionIds     Collection id.
	 * @param   array  $dropDownSelected  Main attributes selected.
	 *
	 * @return  JDatabaseQuery Accessory query.
	 */
	public function getAccessoriesQuery($ids, $departmentId = 0, $collectionIds = array(), $dropDownSelected = array())
	{
		$db         = $this->getDbo();
		$issetItems = array();

		if (!empty($ids))
		{
			// Product ids is passed as an array, taking the first element
			$issetItems = $this->getIssetItems(reset($ids));
		}

		$pQuery = $db->getQuery(true)
			->select(
				array(
					$db->qn('pia.accessory_product_id'),
					$db->qn('pia.description'),
					$db->qn('pia.collection_id'),
					$db->qn('pia.hide_on_collection'),
					$db->qn('pia.price'),
					$db->qn('pia.selection'),
					$db->qn('pia.state'),
					$db->qn('pia.id'),
					$db->qn('p2.sku'),
					$db->qn('p2.name'),
					$db->qn('p2.service'),
					$db->qn('p2.unit_measure_id'),
					$db->qn('c.id', 'currency_id'),
					$db->qn('c.alpha3', 'currency'),
					$db->qn('pia.id', 'accessory_id')
				)
			);

		$itemsQuery = clone $pQuery;

		$pQuery->select('pia.product_id')
			->from($db->qn('#__redshopb_product_accessory', 'pia'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = pia.product_id')
			->leftJoin($db->qn('#__redshopb_product', 'p2') . ' ON p2.id = pia.accessory_product_id')
			->leftJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON wpx.product_id = p.id')
			->leftJoin($db->qn('#__redshopb_collection', 'w') . ' ON w.id = pia.collection_id')
			->leftJoin($db->qn('#__redshopb_collection_department_xref', 'wdx') . ' ON pia.collection_id = wdx.collection_id')
			->leftJoin($db->qn('#__redshopb_currency', 'c') . ' ON c.id = w.currency_id')
			->where('p.id IN (' . implode(',', $ids) . ')')
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			);

		if (!empty($issetItems) && !empty($dropDownSelected))
		{
			$itemsQuery->select('pa.product_id')
				->from($db->qn('#__redshopb_product_item_accessory', 'pia'))
				->leftJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON ' . $db->qn('pia.attribute_value_id') . ' = ' . $db->qn('pav.id')
				)
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
				->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = pa.product_id')
				->leftJoin($db->qn('#__redshopb_product', 'p2') . ' ON p2.id = pia.accessory_product_id')
				->leftJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON wpx.product_id = p.id')
				->leftJoin($db->qn('#__redshopb_collection', 'w') . ' ON w.id = pia.collection_id')
				->leftJoin($db->qn('#__redshopb_collection_department_xref', 'wdx') . ' ON pia.collection_id = wdx.collection_id')
				->leftJoin($db->qn('#__redshopb_currency', 'c') . ' ON c.id = w.currency_id')
				->where('p.id IN (' . implode(',', $ids) . ')')
				->where($db->qn('p.state') . ' = 1')
				->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where($db->qn('pav.state') . ' = 1')
				->where($db->qn('pa.state') . ' = 1')
				->where($db->qn('pa.main_attribute') . ' = 1')
				->where($db->qn('pia.attribute_value_id') . ' IN (' . implode(',', $dropDownSelected) . ')')
				->order('pia.collection_id')
				->order('pav.ordering')
				->group('pia.id');
		}

		// Filter collection
		if (is_array($collectionIds)
			&& !empty($collectionIds)
			&& !(count($collectionIds) == 1 && $collectionIds[0] == 0))
		{
			$collectionIdsString = implode(',', $collectionIds);

			if ($collectionIdsString != '')
			{
				$pQuery->where('pia.collection_id IN (' . $collectionIdsString . ')')
					->where($db->qn('w.state') . ' = 1');
			}

			if (!empty($issetItems) && !empty($dropDownSelected))
			{
				$itemsQuery->where('pia.collection_id IN (' . $collectionIdsString . ')')
					->where($db->qn('w.state') . ' = 1');
			}
		}

		if ($departmentId != 0)
		{
			$departments = array($departmentId);
			$departments = array_merge($departments, RedshopbHelperDepartment::getChildDepartments($departmentId));
			$pQuery->where('wdx.department_id IN (' . implode(',', $departments) . ')');

			if (!empty($issetItems) && !empty($dropDownSelected))
			{
				$itemsQuery->where('wdx.department_id IN (' . implode(',', $departments) . ')');
			}
		}

		if (!empty($issetItems) && !empty($dropDownSelected))
		{
			$query = $db->getQuery(true)
				->select('*')
				->from($pQuery->unionDistinct($itemsQuery), 'res')
				->order('collection_id, name')
				->group('id');
		}
		else
		{
			$query = $db->getQuery(true)
				->select('*')
				->from(
					$pQuery->order('pia.collection_id, p2.name')
						->group('pia.id'), 'res'
				);
		}

		return $query;
	}

	/**
	 * Get minimal ordering from current product
	 *
	 * @param   int  $productId  Id current product
	 *
	 * @return JDatabaseQuery
	 */
	public function getMinimalOrdering($productId)
	{
		if (isset(self::$minimalOrdering[$productId]))
		{
			return self::$minimalOrdering[$productId];
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('MIN(pa2.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa2'))
			->where($db->qn('pa2.product_id') . ' = ' . (int) $productId);
		$db->setQuery($query);

		self::$minimalOrdering[$productId] = $db->loadResult();

		return self::$minimalOrdering[$productId];
	}

	/**
	 * Get types from X axis
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return mixed
	 */
	public function getStaticTypes($productId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'pav.*',
					$db->qn('pivx.product_item_id'),
					$db->qn('pa.ordering'),
					$db->qn('pa.name'),
					$db->qn('pa.type_id')
				)
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where($db->qn('pa.product_id') . ' = ' . (int) $productId)
			->where($db->qn('pa.ordering') . ' = ' . (int) $this->getMinimalOrdering($productId))
			->group('pav.id')
			->order('pav.ordering, pav.id ASC');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(
				RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.'))
			)
		);
		$db->setQuery($query);

		$result = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		return $result;
	}

	/**
	 * Get dynamic types
	 *
	 * @param   int  $productId  Object product values
	 *
	 * @return mixed
	 */
	public function getDynamicTypes($productId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'pav.*',
					$db->qn('pa.name'),
					$db->qn('pa.type_id')
				)
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where($db->qn('pa.product_id') . ' = ' . (int) $productId)
			->where($db->qn('pa.ordering') . ' != ' . (int) $this->getMinimalOrdering($productId))
			->group('pav.id')
			->order('pav.id, pav.ordering ASC');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(
				RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.'))
			)
		);
		$db->setQuery($query);

		$results = $db->loadObjectList();

		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		if (!$results)
		{
			return array();
		}

		$dynamicTypes = array();

		foreach ($results as $result)
		{
			$dynamicTypes[$result->id] = $result;
		}

		return $dynamicTypes;
	}

	/**
	 * Get isset item and relating items with attribute values
	 *
	 * @param   int  $productId  Object product values
	 *
	 * @return array
	 */
	public function getIssetItems($productId)
	{
		$db = Factory::getDbo();

		// Select attribute values ids from items
		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.id ORDER BY pa.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->where($db->qn('pa.product_id') . ' = ' . $db->qn('pi.product_id'))
			->where($db->qn('pi.id') . ' = ' . $db->qn('pivx.product_item_id'))
			->order('pa.ordering ASC')
			->order('pav.ordering, pav.id ASC');

		$query = $db->getQuery(true);
		$query->select(
			array(
					'(' . $subQuery . ') AS values_ids',
					$db->qn('pi.id'),
					$db->qn('pi.state')
				)
		)
			->from($db->qn('#__redshopb_product_item', 'pi'))
			->where($db->qn('pi.product_id') . ' = ' . (int) $productId);

		$results = $db->setQuery($query)->loadObjectList();

		if (!$results)
		{
			return array();
		}

		$issetItems = array();

		foreach ($results as $result)
		{
			if (empty($result->values_ids))
			{
				continue;
			}

			$issetItems[$result->values_ids] = $result;
		}

		return $issetItems;
	}

	/**
	 * Get isset dynamic variants
	 *
	 * @param   int  $productId  Object product values
	 *
	 * @return mixed
	 */
	public function getIssetDynamicVariants($productId)
	{
		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav2.id ORDER BY pa2.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav2'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx2') . ' ON pivx2.product_attribute_value_id = pav2.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa2') . ' ON pa2.id = pav2.product_attribute_id')
			->where('pi.id = pivx2.product_item_id')
			->where('pa2.product_id = pa.product_id')
			->where('pa2.ordering != ' . (int) $this->getMinimalOrdering($productId))
			->order('pav2.id, pav2.ordering ASC');

		$query = $db->getQuery(true)
			->select(
				array('(' . $subQuery . ') AS concat_dynamics')
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pa.product_id = ' . (int) $productId)
			->where('pa.ordering = ' . (int) $this->getMinimalOrdering($productId))
			->group('concat_dynamics')
			->order('concat_dynamics');

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if (!$results)
		{
			return array();
		}

		$issetDynamicVariants = array();

		foreach ($results as $result)
		{
			if ($result->concat_dynamics)
			{
				$issetDynamicVariants[] = $result->concat_dynamics;
			}
		}

		return $issetDynamicVariants;
	}

	/**
	 * Store a product item stock
	 *
	 * @param   array  $data  Array values
	 *
	 * @return void
	 */
	public function storeStock($data)
	{
		//  @ToDo New stockroom-based function
	}

	/**
	 * Check In Image Sync
	 *
	 * @return void
	 */
	public function checkInImageSync()
	{
		$table = $this->getTable('SyncEdit', 'RedshopbTable');

		if ($table->load(array('name' => 'GetProductPicture')))
		{
			$table->checkIn();
		}
	}

	/**
	 *  Feature/unfeature a product
	 *
	 * @param   integer  $id     The product id
	 * @param   integer  $value  value
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function updateFeatured($id, $value)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$productTable = $this->getTable();

		if (!$productTable->load($id))
		{
			return false;
		}

		$data = array('featured' => $value);

		if (!$productTable->save($data))
		{
			return false;
		}

		return $id;
	}

	/**
	 * Discontinue a product
	 *
	 * @param   integer  $id  The product id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function discontinueWS($id)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$productTable = $this->getTable();

		if (!$productTable->load($id))
		{
			return false;
		}

		$data = array('discontinued' => 1);

		if (!$productTable->save($data))
		{
			return false;
		}

		return $id;
	}

	/**
	 *  Validate web service data for categoryAdd function
	 *
	 * @param   int  $data  Data to be validated ('category_id')
	 *
	 * @return  array | false
	 */
	public function validateCategoryAddWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'category');
	}

	/**
	 *  Add a category to product
	 *
	 * @param   int $productId  id of product table
	 * @param   int $categoryId id of category
	 *
	 * @return  boolean Product ID on success. False otherwise.
	 * @throws Exception
	 */
	public function categoryAdd($productId, $categoryId)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct $productTable */
		$productTable  = $this->getTable();
		$categoryTable = RedshopbTable::getAdminInstance('Category');

		if (!$productTable->load($productId) || !$categoryTable->load($categoryId))
		{
			return false;
		}

		$categories = $productTable->categories;

		if (array_search($categoryId, $categories) !== false)
		{
			return $productId;
		}

		$categories[] = $categoryId;
		$productTable->setOption('category_relate.store', true);

		if (!$productTable->save(array('categories' => $categories)))
		{
			return false;
		}

		return $productId;
	}

	/**
	 *  Validate web service data for categoryRemove function
	 *
	 * @param   int  $data  Data to be validated ('category_id')
	 *
	 * @return  array | false
	 */
	public function validateCategoryRemoveWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'category');
	}

	/**
	 *  Remove a category from a product
	 *
	 * @param   int  $productId   id of product table
	 * @param   int  $categoryId  id of category
	 *
	 * @return  boolean Product ID on success. False otherwise.
	 */
	public function categoryRemove($productId, $categoryId)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct $productTable */
		$productTable = $this->getTable();

		if (!$productTable->load($productId))
		{
			return false;
		}

		$categories = $productTable->categories;
		$i          = array_search($categoryId, $categories);

		if ($i === false)
		{
			return $productId;
		}

		unset($productTable->categories[$i]);

		$productTable->setOption('category_relate.store', true);
		$savedData = array();

		if ($productTable->category_id == $categoryId)
		{
			$savedData['category_id'] = '';
		}

		if (!$productTable->save($savedData))
		{
			return false;
		}

		return $productId;
	}

	/**
	 *  Validate web service data for tagAdd function
	 *
	 * @param   int  $data  Data to be validated ('tag_id')
	 *
	 * @return  array | false
	 */
	public function validateTagAddWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'tag');
	}

	/**
	 *  Add a tag to product
	 *
	 * @param   int  $productId  id of product table
	 * @param   int  $tagId      id of tag table
	 *
	 * @return  boolean Product ID on success. False otherwise.
	 */
	public function tagAdd($productId, $tagId)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct $productTable */
		$productTable = $this->getTable();
		$tagTable     = RedshopbTable::getAdminInstance('Tag');

		if (!$productTable->load($productId) || !$tagTable->load($tagId))
		{
			return false;
		}

		if (array_search($tagId, $productTable->tag_id) !== false)
		{
			return $productId;
		}

		$productTable->tag_id[] = $tagId;
		$productTable->setOption('tag_relate.store', true);

		if (!$productTable->save(array()))
		{
			return false;
		}

		return $productId;
	}

	/**
	 *  Validate web service data for tagRemove function
	 *
	 * @param   int  $data  Data to be validated ('tag_id')
	 *
	 * @return  array | false
	 */
	public function validateTagRemoveWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'tag');
	}

	/**
	 *  Remove a tag from a product
	 *
	 * @param   int  $productId  id of product table
	 * @param   int  $tagId      id of tag
	 *
	 * @return  boolean Product ID on success. False otherwise.
	 */
	public function tagRemove($productId, $tagId)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct $productTable */
		$productTable = $this->getTable();

		if (!$productTable->load($productId))
		{
			return false;
		}

		$i = array_search($tagId, $productTable->tag_id);

		if ($i === false)
		{
			return $productId;
		}

		unset($productTable->tag_id[$i]);

		$productTable->setOption('tag_relate.store', true);

		if (!$productTable->save(array()))
		{
			return false;
		}

		return $productId;
	}

	/**
	 *  Validate web service data for companyLimitationAdd function
	 *
	 * @param   int  $data  Data to be validated ('company_id')
	 *
	 * @return  array | false
	 */
	public function validateCompanyLimitationAddWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'company');
	}

	/**
	 *  Add a company limitation to product
	 *
	 * @param   int  $productId  id of product table
	 * @param   int  $companyId  id of company table
	 *
	 * @return  boolean Product ID on success. False otherwise.
	 */
	public function companyLimitationAdd($productId, $companyId)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct $productTable */
		$productTable = $this->getTable();
		$companyTable = RedshopbTable::getAdminInstance('Company');

		if (!$productTable->load($productId) || !$companyTable->load($companyId))
		{
			return false;
		}

		$customerIds = $productTable->get('customer_ids');

		if (array_search($companyId, $customerIds) !== false)
		{
			return $productId;
		}

		$customerIds[] = $companyId;
		$productTable->set('customer_ids', $customerIds);
		$productTable->setOption('company_relate.store', true);

		if (!$productTable->save(array()))
		{
			return false;
		}

		return $productId;
	}

	/**
	 *  Validate web service data for companyLimitationRemove function
	 *
	 * @param   int  $data  Data to be validated ('company_id')
	 *
	 * @return  array | false
	 */
	public function validateCompanyLimitationRemoveWS($data)
	{
		return RedshopbHelperWebservices::validateExternalId($data, 'company');
	}

	/**
	 *  Remove a company limitation from a product
	 *
	 * @param   int  $productId  id of product table
	 * @param   int  $companyId  id of company
	 *
	 * @return  boolean Product ID on success. False otherwise.
	 */
	public function companyLimitationRemove($productId, $companyId)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct $productTable */
		$productTable = $this->getTable();

		if (!$productTable->load($productId))
		{
			return false;
		}

		$customerIds = $productTable->get('customer_ids');
		$i           = array_search($companyId, $customerIds);

		if ($i === false)
		{
			return $productId;
		}

		unset($customerIds[$i]);
		$productTable->set('customer_ids', $customerIds);

		$productTable->setOption('company_relate.store', true);

		if (!$productTable->save(array()))
		{
			return false;
		}

		return $productId;
	}

	/**
	 * Add accessory to a product with variants
	 *
	 * @param   integer  $accessoryProductId  Accessory Product id
	 * @param   integer  $productId           Product id
	 * @param   integer  $productAttrId       Product attribute id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function addProductAccessory($accessoryProductId, $productId, $productAttrId)
	{
		$row = array();

		if ($productAttrId && count($this->getIssetItems($productId)) > 0)
		{
			/** @var RedshopbTableProduct_Item_Accessory $itemTable */
			$itemTable = RedshopbTable::getAdminInstance('Product_Item_Accessory');

			if ($itemTable->load(
				array(
					'attribute_value_id' => $productAttrId,
					'accessory_product_id' => $accessoryProductId
				)
			))
			{
				return false;
			}

			$row['attribute_value_id']   = $productAttrId;
			$row['accessory_product_id'] = $accessoryProductId;

			return (bool) $itemTable->save($row);
		}

		/** @var RedshopbTableProduct_Accessory $itemTable */
		$table = RedshopbTable::getAdminInstance('Product_Accessory');

		if ($table->load(
			array(
				'product_id' => $productId,
				'accessory_product_id' => $accessoryProductId
			)
		))
		{
			return false;
		}

		$row['selection']            = 'require';
		$row['product_id']           = $productId;
		$row['accessory_product_id'] = $accessoryProductId;

		return (bool) $table->save($row);
	}

	/**
	 * Add accessory to a product with variants
	 *
	 * @param   integer  $accessoryProductId  Accessory Product id
	 * @param   integer  $productId           Product id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function removeProductAccessory($accessoryProductId, $productId)
	{
		$productId = (int) $productId;

		if (!$productId || !$accessoryProductId)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$items = $this->getIssetItems($productId);

		if (!empty($items))
		{
			$attributesIds = array();
			$attributes    = $this->getAttributes($productId);

			foreach ($attributes as $attribute)
			{
				if ($attribute['main_attribute'])
				{
					$attributesIds[] = $attribute['id'];
				}
			}

			$query->delete($db->qn('#__redshopb_product_item_accessory'))
				->where($db->qn('accessory_product_id') . ' = ' . (int) $accessoryProductId)
				->where('attribute_value_id IN (' . implode(',', $attributesIds) . ')');

			$db->setQuery($query)->execute();
		}

		$query->clear()
			->delete($db->qn('#__redshopb_product_accessory'))
			->where($db->qn('accessory_product_id') . ' = ' . (int) $accessoryProductId)
			->where($db->qn('product_id') . ' = ' . (int) $productId);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Add complimentary to a product
	 *
	 * @param   integer  $complimentaryProductId  Complimentary Product id
	 * @param   integer  $productId               Product id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function addProductComplimentary($complimentaryProductId, $productId)
	{
		$table = RedshopbTable::getAdminInstance('Product_Complimentary');
		$row   = array();

		if ($table->load(
			array(
				'product_id' => $productId,
				'complimentary_product_id' => $complimentaryProductId
			)
		))
		{
			return false;
		}

		$row['product_id']               = $productId;
		$row['complimentary_product_id'] = $complimentaryProductId;

		return (bool) $table->save($row);
	}

	/**
	 * Add complimentary to a product with variants
	 *
	 * @param   integer  $complimentaryProductId  Complimantary Product id
	 * @param   integer  $productId               Product id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function removeProductComplimentary($complimentaryProductId, $productId)
	{
		$productId = (int) $productId;

		if (!$productId || !$complimentaryProductId)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_product_complimentary'))
			->where($db->qn('complimentary_product_id') . ' = ' . (int) $complimentaryProductId)
			->where($db->qn('product_id') . ' = ' . (int) $productId);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Getter function for product ordering per category.
	 *
	 * @param   int  $productId  Product id.
	 *
	 * @return  array  Object array of products ordering per category.
	 */
	public function getOrderingValues($productId)
	{
		if (empty($productId) || !is_numeric($productId))
		{
			return array();
		}

		$db       = $this->getDbo();
		$query    = $db->getQuery(true);
		$subQuery = clone $query;

		$subQuery->select($db->qn('pcx2.category_id'))
			->from($db->qn('#__redshopb_product_category_xref', 'pcx2'))
			->where($db->qn('pcx2.product_id') . ' = ' . (int) $productId);

		$query->select(
			array(
				$db->qn('p.id', 'pid'),
				$db->qn('p.name', 'pname'),
				$db->qn('c.id', 'cid'),
				$db->qn('c.name', 'cname'),
				$db->qn('pcx.ordering', 'ordering')
			)
		)
			->from($db->qn('#__redshopb_product_category_xref', 'pcx'))
			->innerJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pcx.product_id'))
			->innerJoin($db->qn('#__redshopb_category', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('pcx.category_id'))
			->where($db->qn('pcx.category_id') . ' IN (' . $subQuery . ')')
			->order($db->qn('pcx.category_id') . ' ASC, ' . $db->qn('pcx.ordering') . ' ASC');

		$rows     = $db->setQuery($query)->loadObjectList();
		$ordering = array();

		if (!empty($rows))
		{
			foreach ($rows as $row)
			{
				if (isset($ordering[$row->cid]))
				{
					$ordering[$row->cid][] = $row;
				}
				else
				{
					$ordering[$row->cid] = array($row);
				}
			}
		}

		return $ordering;
	}
}
