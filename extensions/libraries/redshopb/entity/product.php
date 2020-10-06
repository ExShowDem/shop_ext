<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * Product Entity.
 *
 * @since  2.0
 */
class RedshopbEntityProduct extends RedshopbEntity
{
	use RedshopbEntityTraitCompany;
	use RedshopbEntityTraitFields;

	/**
	 * Collection ID
	 *
	 * @var integer
	 */
	public $collectionId;

	/**
	 * Attributes of this product
	 *
	 * @var  RedshopbEntitiesCollection
	 */
	protected $attributes;

	/**
	 * Product images
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $images;

	/**
	 * Product unit measure
	 *
	 * @var  RedshopbEntityUnit_Measure
	 */
	protected $unitMeasure;

	/**
	 * Collection of stockrooms
	 *
	 * @var array
	 */
	protected $stockrooms = array();

	/**
	 * Fallback price
	 *
	 * @var  stdClass (id, price, retail_price)
	 */
	protected $fallbackPrice = null;

	/**
	 * Product descriptions
	 *
	 * @var   array
	 */
	protected $descriptions = array();

	/**
	 * Product discount groups
	 *
	 * @var array
	 */
	protected $discountGroups;

	/**
	 * Product manufacturer
	 *
	 * @var RedshopbEntityManufacturer
	 */
	protected $manufacturer;

	/**
	 * Get product attributes
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getAttributes()
	{
		if (null === $this->attributes)
		{
			$this->attributes = $this->searchAttributes();
		}

		return $this->attributes;
	}

	/**
	 * Get product descriptions
	 *
	 * @return  array
	 */
	public function getDescriptions()
	{
		if (empty($this->descriptions))
		{
			$this->descriptions = $this->searchDescriptions();
		}

		return $this->descriptions;
	}

	/**
	 * Get a sequential indexed array of descriptions
	 *
	 * @return array
	 */
	public function getIndexedDescriptions()
	{
		return array_values($this->getDescriptions()->toObjects());
	}

	/**
	 * Get the product manufacturer
	 *
	 * @return RedshopbEntityManufacturer
	 */
	public function getManufacturer()
	{
		$item = $this->getItem();

		if (empty($item->manufacturer_id))
		{
			return null;
		}

		$this->manufacturer = RedshopbEntityManufacturer::getInstance($item->manufacturer_id);

		return $this->manufacturer;
	}

	/**
	 * searchCategories() proxy to get this product categories
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function getCategories()
	{
		return $this->searchCategories();
	}

	/**
	 * Get the product images
	 *
	 * @param   bool      $includeAllImages         Include attribute images
	 * @param   null|int  $productAttributeValueId  Product attribute value it. If set null and $includeAllImages = true,
	 *                                              then select images just from a product.
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function getImages($includeAllImages = true, $productAttributeValueId = null)
	{
		$modelState = array(
			'filter.product_attribute_value_id' => $productAttributeValueId,
			'filter.include_all_images' => $includeAllImages
		);

		return $this->searchImages($modelState);
	}

	/**
	 * searchTags() proxy to get this product tags
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function getTags()
	{
		return $this->searchTags();
	}

	/**
	 * Get product discount groups
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getDiscountGroups()
	{
		if (null === $this->discountGroups)
		{
			$this->discountGroups = $this->searchDiscountGroups();
		}

		return $this->discountGroups;
	}

	/**
	 * Search on this product discount groups
	 *
	 * @param   array  $modelState  State for the Product_Attributes model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchDiscountGroups($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'pdg.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0,
			'select.product_list' => false
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force product filter
		$state['filter.product_id'] = $this->id;

		$model                 = RedshopbModel::getFrontInstance('Product_Discount_Groups');
		$productDiscountGroups = $model->search($state);

		foreach ($productDiscountGroups as $productDiscountGroup)
		{
			$entity = RedshopbEntityProduct_Discount_Group::getInstance($productDiscountGroup->id)->bind($productDiscountGroup);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Check if this product is in a category
	 *
	 * @param   integer  $categoryId  Category identifier
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function hasCategory($categoryId)
	{
		$categoryId = (int) $categoryId;

		if (!$categoryId)
		{
			return false;
		}

		return in_array($categoryId, $this->getCategories()->ids());
	}

	/**
	 * Check if this product has a tag
	 *
	 * @param   integer  $tagId  Tag identifier
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function hasTag($tagId)
	{
		$tagId = (int) $tagId;

		if (!$tagId)
		{
			return false;
		}

		return in_array($tagId, $this->getTags()->ids());
	}

	/**
	 * Returns the product fallback price object
	 *
	 * @return  stdClass
	 *
	 * @since   2.0
	 */
	public function getFallbackPrice()
	{
		if (is_null($this->fallbackPrice))
		{
			$this->loadFallbackPrices();
		}

		return $this->fallbackPrice;
	}

	/**
	 * Search on this product images
	 *
	 * @param   array  $modelState  State for the Medias model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchImages($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'm.id',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force product filter
		$state['filter.product_id'] = $this->id;

		$model  = RedshopbModel::getFrontInstance('medias');
		$medias = $model->search($state);

		foreach ($medias as $media)
		{
			$entity           = RedshopbEntityMedia::getInstance($media->id)->bind($media);
			$entity->viewName = $entity->getViewName();
			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search on this product attributes
	 *
	 * @param   array  $modelState  State for the Product_Attributes model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchAttributes($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'a.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force product filter
		$state['filter.product_id'] = $this->id;

		$model      = RedshopbModel::getFrontInstance('product_attributes');
		$attributes = $model->search($state);

		foreach ($attributes as $attribute)
		{
			$entity = RedshopbEntityProduct_Attribute::getInstance($attribute->id)->bind($attribute);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search on this product descriptions
	 *
	 * @param   array  $modelState  State for the Product_Descriptions model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchDescriptions($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'pd.id',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force product filter
		$state['filter.product_id'] = $this->id;

		/** @var \RedshopbModelProduct_Descriptions $model */
		$model        = RedshopbModel::getFrontInstance('product_descriptions');
		$descriptions = $model->search($state);

		foreach ($descriptions as $description)
		{
			$entity = RedshopbEntityProduct_Description::getInstance($description->id)->bind($description);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search on this product categories
	 *
	 * @param   array  $modelState  State for the Categories model
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

		// Force product filter
		$state['filter.product'] = $this->id;

		$model      = RedshopbModel::getFrontInstance('categories');
		$categories = $model->search($state);

		foreach ($categories as $category)
		{
			$entity = RedshopbEntityCategory::getInstance($category->id)->bind($category);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Search inside this product tags
	 *
	 * @param   array  $modelState  State for the Tags model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchTags($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		$state = array(
			'list.ordering'  => 't.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force product filter
		$state['filter.product'] = $this->id;

		$tags = RedshopbModel::getFrontInstance('tags')->search($state);

		foreach ($tags as $tag)
		{
			$entity = RedshopbEntityTag::getInstance($tag->id)->bind($tag);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Get product unit measure
	 *
	 * @return  RedshopbEntityUnit_Measure
	 */
	public function getUnitMeasure()
	{
		if ($this->unitMeasure === null)
		{
			$this->unitMeasure = RedshopbEntityUnit_Measure::getInstance($this->get('unit_measure_id'));
		}

		return $this->unitMeasure;
	}

	/**
	 * Method to get a list of stockrooms based on product ID
	 *
	 * @param   int  $productId  product id
	 *
	 * @return array of data items.
	 */
	public function getStockRooms($productId = null)
	{
		if (empty($productId))
		{
			$productId = $this->id;
		}

		if (!isset($this->stockrooms[$productId]) || !is_array($this->stockrooms[$productId]))
		{
			/** @var RedshopbModelStockrooms $stockroomsModel */
			$stockroomsModel = RedshopbModel::getFrontInstance('Stockrooms');
			$stockroomsModel->setState('filter.company_id', self::getInstance($productId)->getCompany()->id);
			$stockRooms = $stockroomsModel->getItems();

			if (!$stockRooms)
			{
				$stockRooms = array();
			}

			foreach ($stockRooms as $stockRoom)
			{
				$availableStock = RedshopbHelperStockroom::getProductStockroomData($productId, $stockRoom->id);

				$stockRoom->productStockData = $availableStock;

				$stockRoom->deliveryClass = 'productNoStockFlag';
				$stockRoom->iconClass     = 'text-error';
				$stockRoom->deliveryText  = Text::_('COM_REDSHOP_DEVLIVERY_STATUS_WHEN_NO_STOCK');

				if (empty($availableStock))
				{
					continue;
				}

				if ($availableStock->amount >= 1 || $availableStock->unlimited == 1)
				{
					$stockRoom->deliveryClass = 'productInStockFlagProduct';
					$stockRoom->iconClass     = 'text-success';
					$stockRoom->deliveryText  = Text::sprintf(
						'COM_REDSHOP_STOCK_DELIVERY_NUMBER_DAYS',
						$stockRoom->min_delivery_time,
						$stockRoom->max_delivery_time
					);
				}
			}

			$this->stockrooms[$productId] = $stockRooms;
		}

		return $this->stockrooms[$productId];
	}

	/**
	 * Method to get the id of stockroom with the shortest delivery time.
	 *
	 * @return object
	 */
	public function getMinDeliveryStockroom()
	{
		$productId = $this->id;

		/** @var RedshopbModelStockrooms $stockroomsModel */
		$stockroomsModel = RedshopbModel::getFrontInstance('Stockrooms');
		$results         = $stockroomsModel->getMinDeliveryStocks(array($productId));

		if (!array_key_exists($productId, $results))
		{
			return null;
		}

		$stockId      = $results[$productId];
		$minStockRoom = null;

		foreach ($this->getStockRooms($productId) AS $stock)
		{
			if ($stock->id == $stockId)
			{
				$minStockRoom = $stock;
				break;
			}
		}

		return $minStockRoom;
	}

	/**
	 * Get decimal postion for product.
	 *
	 * @return  integer|null  Number of position or null if value not found
	 */
	public function getDecimalPosition()
	{
		// Try to get from individual product config
		$decimalPosition = $this->get('decimal_position', null);

		// Try to get from product unit measure
		if (is_null($decimalPosition))
		{
			$decimalPosition = $this->getUnitMeasure()->get('decimal_position', null);
		}

		return $decimalPosition;
	}

	/**
	 * Loads the fallback prices of the product (normal and retail)
	 *
	 * @return  void
	 */
	public function loadFallbackPrices()
	{
		if (!$this->hasId())
		{
			return;
		}

		if (!$this->isLoaded())
		{
			$this->loadItem();
		}

		if ($this->item->company_id)
		{
			$currencyId = RedshopbEntityCompany::getInstance($this->item->company_id)->getCustomerCurrency();
		}
		else
		{
			$currencyId = RedshopbApp::getConfig()->get('default_currency', 38);
		}

		// @toDo: change this ugly query and move it to the prices model list
		$db            = $this->getDbo();
		$query         = $db->getQuery(true)
			->select('pp.*')
			->from($db->qn('#__redshopb_product_price', 'pp'))
			->where('type_id = ' . (int) $this->id)
			->where('type = ' . $db->q('product'))
			->where('sales_type = ' . $db->q('all_customers'))
			->where('sales_code = ' . $db->q(''))
			->where('country_id IS NULL')
			->where('quantity_min IS NULL')
			->where('quantity_max IS NULL')
			->where('currency_id = ' . (int) $currencyId)
			->where('price >= 0');
		$fallbackPrice = $db->setQuery($query)->loadObject();

		if (is_null($fallbackPrice))
		{
			return;
		}

		$this->fallbackPrice               = new stdClass;
		$this->fallbackPrice->id           = $fallbackPrice->id;
		$this->fallbackPrice->price        = $fallbackPrice->price;
		$this->fallbackPrice->retail_price = $fallbackPrice->retail_price;
	}

	/**
	 * Check if when adding an item the quantities are OK.
	 *
	 * @param   integer|float  $quantity  Cart Quantity.
	 *
	 * @return  array
	 */
	public function checkQuantities($quantity)
	{
		if (!$this->isLoaded())
		{
			$this->loadItem();
		}

		if ($this->item->max_sale > 0 && $quantity > $this->item->max_sale)
		{
			return array('isOK' => false, 'msg' => Text::sprintf('COM_REDSHOPB_ADD_TO_CART_ERROR_MAX_SALE', $this->item->max_sale));
		}

		if ($quantity < $this->item->min_sale)
		{
			return array('isOK' => false, 'msg' => Text::sprintf('COM_REDSHOPB_ADD_TO_CART_ERROR_MIN_SALE', $this->item->min_sale));
		}

		/*
		Lambda function to calculate the modulo of two floats.

		This is needed because fmod() produces rounding errors and the % operator only takes integers.
		 */
		$modulo = function ($dividend, $divisor)
		{
			return $dividend - round($dividend / $divisor) * $divisor;
		};

		if ($this->item->pkg_size > 1 && $modulo((float) $quantity, (float) $this->item->pkg_size) !== (float) 0)
		{
			return array(
				'isOK' => false,
				'msg' => Text::sprintf(
					'COM_REDSHOPB_ADD_TO_CART_ERROR_PKG_SIZE',
					$this->item->name,
					$this->item->pkg_size
				)
			);
		}

		return array('isOK' => true);
	}

	/**
	 * Gets All data from the first image.
	 * @param   null|integer $width  the image width you wish
	 * @param   null|integer $height the image height you wish
	 *
	 * @return false|object
	 *
	 * @since 1.13.0
	 */
	public function getMainImageData($width = null, $height = null)
	{
		$image = $this->getImages()->current();

		if (!$image)
		{
			return false;
		}

		$image = $image->getItem();

		if ($width === null || $height === null)
		{
			$path       = RedshopbHelperMedia::getFullMediaPath($image->name, 'products', 'images', $image->remote_path);
			$image->url = URI::base() . $path;

			return $image;
		}

		$path       = RedshopbHelperThumbnail::originalToResize($image->name, $width, $height, 100, 0, 'products', true, $image->remote_path);
		$image->url = Uri::base() . $path;

		return $image;
	}

	/**
	 * Searches and returns a product (and its info) by its custom field
	 *
	 * @param   string  $customField         Custom field name of product
	 * @param   string  $customFieldValue    Custom field value of product
	 *
	 * @return  RedshopbEntityProduct|false
	 */
	public function loadProductByCustomField($customField, $customFieldValue)
	{
		$state				   = array();
		$state['filter.field'] = $customField;
		$state['filter.value'] = $customFieldValue;

		/** @var RedshopbModelField_Datas $fieldDatasModel */
		$fieldDatasModel = RedshopbModel::getFrontInstance('Field_Datas');

		$fieldDatas = $fieldDatasModel->search($state);

		if (empty($fieldDatas)
			|| intval($fieldDatas[0]->importable) !== 1
			|| strcmp($fieldDatas[0]->scope, 'product') !== 0)
		{
			return false;
		}

		$data = array(
			'id' => $fieldDatas[0]->item_id
		);

		$table = $this->getTable();

		if ($table && $table->load($data))
		{
			$this->loadFromTable($table);
		}
		else
		{
			return false;
		}

		return $this;
	}
}
