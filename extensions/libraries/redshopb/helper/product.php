<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

// Be sure component layout folder included, usage for calls inside modules
RedshopbLayoutFile::addIncludePathStatic(JPATH_ROOT . '/components/com_redshopb/layouts');

/**
 * A Product helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperProduct
{
	/**
	 * @var array
	 */
	private static $currencies = array();

	/**
	 * Products cache
	 *
	 * @var  array
	 */
	private static $products = array();

	/**
	 * Generates all possibles attributes combinations.
	 *
	 * Input
	 *
	 * array(
	 *  'color' => array('blue', 'red'),
	 *  'size' => array(10, 20),
	 *  'other' => array('a', 'b')
	 * )
	 *
	 * Output
	 *
	 * array(
	 *  array('color' => 'blue', 'size' => 10, 'other' => 'a'),
	 *  array('color' => 'blue', 'size' => 10, 'other' => 'b'),
	 *  array('color' => 'blue', 'size' => 20, 'other' => 'a'),
	 *  array('color' => 'blue', 'size' => 20, 'other' => 'b'),
	 *  array('color' => 'red', 'size' => 10, 'other' => 'a'),
	 *  array('color' => 'red', 'size' => 10, 'other' => 'b'),
	 *  array('color' => 'red', 'size' => 20, 'other' => 'a'),
	 *  array('color' => 'red', 'size' => 20, 'other' => 'b'),
	 * )
	 *
	 * @param   array  $attributes  A multidimensionnal array of attribute names as key and an the possibles values as value.
	 *
	 * @return  array
	 */
	public static function generateCombinations(array $attributes)
	{
		$keys = array_keys($attributes);

		$results = array();
		self::doGenerateCombinations(array_values($attributes), array(), 0, $results);
		$finalResult = array();

		foreach ($results as $result)
		{
			$finalResult[] = array_combine($keys, $result);
		}

		return $finalResult;
	}

	/**
	 * Perform the generation of the combinations.
	 *
	 * @param   array    $attributes  The multidimensional array of possible attributes values
	 * @param   array    $current     The current set of attributes
	 * @param   integer  $cNum        The combination number
	 * @param   array    $results     The resulting set
	 *
	 * @return  void
	 */
	private static function doGenerateCombinations(array $attributes, array $current, $cNum, array &$results)
	{
		$count = count($attributes);

		if ($cNum === $count)
		{
			$results[] = $current;
		}

		else
		{
			for ($j = 0; $j < count($attributes[$cNum]); $j++)
			{
				$current[$cNum] = $attributes[$cNum][$j];
				self::doGenerateCombinations($attributes, $current, $cNum + 1, $results);
			}
		}
	}

	/**
	 * Get the attributes data for a given product.
	 *
	 * @param   integer  $productId  The product id
	 *
	 * @return  array  An array of attribute data
	 */
	public static function getAttributesAsArray($productId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__redshopb_product_attribute')
			->where('product_id = ' . $db->q($productId))
			->order('ordering ASC');

		$db->setQuery($query);

		$attributes = $db->loadAssocList();

		if (!is_array($attributes))
		{
			return array();
		}

		return $attributes;
	}

	/**
	 * Get Currency object by symbol or id
	 *
	 * @param   mixed  $currency  currency symbol or id
	 *
	 * @return null/object
	 */
	public static function getCurrency($currency = 'DKK')
	{
		if (!$currency)
		{
			$currency = (int) RedshopbApp::getConfig()->get('default_currency', 38);
		}

		if (!array_key_exists($currency, self::$currencies))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshopb_currency'));

			if (is_numeric($currency))
			{
				$query->where('id = ' . (int) ($currency));
			}
			else
			{
				$query->where('alpha3 = ' . $db->q($currency));
			}

			self::$currencies[$currency] = $db->setQuery($query)->loadObject();
		}

		return self::$currencies[$currency];
	}

	/**
	 * Formatted Product Price
	 *
	 * @param   float   $productPrice    Product price
	 * @param   string  $currencySymbol  Product currency symbol
	 * @param   bool    $appendSymbol    Append currency symbol to results?
	 *
	 * @return  string  Formatted Product Price
	 */
	public static function getProductFormattedPrice($productPrice, $currencySymbol = 'DKK', $appendSymbol = true)
	{
		/** @var  Object   $currency */
		$currency = self::getCurrency($currencySymbol);

		if (!$currency)
		{
			return $productPrice;
		}

		$productPrice = (double) $productPrice;

		/*
		 * $currency->decimals: Sets the number of decimal points.
		 * $currency->decimal_separator: Sets the separator for the decimal point.
		 * $currency->thousands_separator: Sets the thousands separator
		 */
		$price = number_format(
			(double) $productPrice,
			$currency->decimals,
			$currency->decimal_separator,
			$currency->thousands_separator
		);

		$currency->symbol = trim($currency->symbol);

		// Sets blank space between the currency symbol and the price
		$blankSpace = ($currency->blank_space == 1) ? '&nbsp;' : '';

		if ($currency->symbol_position == 0 && $appendSymbol)
		{
			$price = $currency->symbol . $blankSpace . $price;
		}
		elseif ($currency->symbol_position == 1 && $appendSymbol)
		{
			$price .= $blankSpace . $currency->symbol;
		}

		RFactory::getDispatcher()->trigger('onAfterRedshopbProductFormattedPrice', array(&$price, $productPrice, $appendSymbol, $currency));

		return $price;
	}

	/**
	 * Get color amount
	 *
	 * @param   object  $item  Data one product item
	 *
	 * @return string
	 */
	public static function getColorAmount($item)
	{
		$class = '';

		if (!isset($item->amount) || $item->amount <= 0)
		{
			$class = ' amountLessZero';
		}
		else
		{
			if (isset($item->stock_lower_level) && $item->amount < $item->stock_lower_level)
			{
				$class = ' amountMoreZeroLessLower';
			}
			elseif (isset($item->stock_lower_level) && isset($item->stock_upper_level)
				&& $item->amount >= $item->stock_lower_level && $item->amount < $item->stock_upper_level)
			{
				$class = ' amountMoreLowerLessUpper';
			}
			elseif (isset($item->stock_upper_level) && $item->amount >= $item->stock_upper_level)
			{
				$class = ' amountMoreUpper';
			}
		}

		return $class;
	}

	/**
	 * Render accessories dropdown
	 *
	 * @param   array   $accessories  An array of accessory objects
	 * @param   string  $id           Main product id
	 * @param   string  $cartPrefix   optional Cart prefix
	 * @param   string  $layout       Layout name for accessories
	 * @param   array   $displayData  Array variables for accessory layout
	 *
	 * @return  string
	 */
	public static function renderAccessoriesDropdown($accessories, $id, $cartPrefix = null, $layout = '', $displayData = array())
	{
		$options           = array();
		$selected          = array();
		$accessoryIdPrefix = null;
		$hidePrices        = false;
		$price             = 0;

		RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrices, 0, $id));

		if (empty($accessories))
		{
			return '';
		}

		foreach ($accessories as $accessory)
		{
			if ($accessory->collection_id && $accessory->hide_on_collection == 1)
			{
				continue;
			}

			$accessoryPrice = '';

			if (RedshopbHelperPrices::displayPrices() && isset($accessory->price) && $accessory->price > 0 && !$hidePrices)
			{
				$accessoryPrice = ' (' . self::getProductFormattedPrice($accessory->price, $accessory->currency) . ')';
			}

			$disabled = false;

			if ($accessory->selection !== 'optional')
			{
				$selected[] = $accessory->accessory_id;
			}

			if ($accessory->selection == 'require')
			{
				$disabled = true;
			}

			$options[] = HTMLHelper::_(
				'select.option',
				$accessoryIdPrefix . $accessory->accessory_id,
				ucfirst(str_replace('_', ' ', $accessory->name)) . $accessoryPrice,
				'value',
				'text',
				$disabled
			);
		}

		if (!$layout)
		{
			$layout = 'list';

			if (RedshopbHelperPrices::displayPrices())
			{
				$config      = RedshopbEntityConfig::getInstance();
				$displayMode = $config->get('show_product_accessories_as', 'checkbox');

				switch ($displayMode)
				{
					case 'select':
						$layout = 'dropdown';
						break;
					default:
						$layout = $displayMode;
						break;
				}
			}
		}

		return RedshopbLayoutHelper::render(
			'shop.accessory.' . $layout, array_merge($displayData, compact(array_keys(get_defined_vars())))
		);
	}

	/**
	 * Render Complimentary Products
	 *
	 * @param   array   $products     An array of complimentary products objects
	 * @param   string  $id           Main product id
	 * @param   string  $cartPrefix   optional Cart prefix
	 * @param   string  $layout       Layout name for complimentary products
	 * @param   array   $displayData  Array variables for complimentary products layout
	 *
	 * @return  string
	 */
	public static function renderComplimentaryProducts($products, $id, $cartPrefix = null, $layout = 'slider', $displayData = array())
	{
		$options  = array();
		$idPrefix = null;

		foreach ($products as $complimentary)
		{
			$options[] = HTMLHelper::_(
				'select.option',
				$idPrefix . $complimentary->complimentary_id,
				ucfirst(str_replace('_', ' ', $complimentary->name)),
				'value',
				'text'
			);
		}

		if (!$layout)
		{
			$layout = 'slider';
		}

		return RedshopbLayoutHelper::render(
			'shop.complimentary.' . $layout, array_merge($displayData, compact(array_keys(get_defined_vars())))
		);
	}

	/**
	 * Render accessories options.
	 *
	 * @param   array   $accessories        An array of accessory objects
	 * @param   string  $accessoryIdPrefix  Prefix for accessory id
	 *
	 * @return  mixed  Empty array on failure, option list on success.
	 */
	public static function renderAccessoriesOptions($accessories, $accessoryIdPrefix = null)
	{
		$options  = array();
		$optional = false;

		// Generate select
		foreach ($accessories as $accessory)
		{
			if (isset($accessory->price) && $accessory->price > 0)
			{
				$accessoryPrice = ' (' . self::getProductFormattedPrice($accessory->price, $accessory->currency) . ')';
			}

			else
			{
				$accessoryPrice = '';
			}

			$disabled = false;

			if ($accessory->selection == 'require' || $accessory->hide_on_collection == '1')
			{
				$disabled = true;
			}
			else
			{
				$optional = true;
			}

			if (!$disabled)
			{
				$options[] = HTMLHelper::_(
					'select.option',
					$accessoryIdPrefix . $accessory->accessory_id,
					ucfirst(str_replace('_', ' ', $accessory->description)) . $accessoryPrice,
					'value',
					'text',
					$disabled
				);
			}
		}

		return $optional ? $options : array();
	}

	/**
	 * Get Product image will get the first picture from the list of product pictures if only Product Id available
	 * If Product Item Id is also available then it will return path of that product Item Id attribute combination with the same color type
	 * If the color type for That Product Item Id is not available it will return first next available picture from product images
	 *
	 * @param   int  $productId      Product id
	 * @param   int  $productItemId  Product item id
	 * @param   int  $colorId        Color id
	 * @param   int  $mediaId        Id of the specific image to load
	 *
	 * @return  mixed  object | null
	 */
	public static function getProductImage($productId, $productItemId = 0, $colorId = 0, $mediaId = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('m.name', 'm.remote_path'))
			->from($db->qn('#__redshopb_media', 'm'))
			->where($db->qn('m.product_id') . ' = ' . (int) ($productId))
			->order($db->qn('m.ordering') . ' ASC', $db->qn('m.name') . ' ASC');

		if ($productItemId > 0)
		{
			$query->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = m.attribute_value_id')
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
				->leftJoin(
					$db->qn('#__redshopb_product_item_attribute_value_xref', 'piav') . ' ON piav.product_attribute_value_id = pav.id '
					. ' AND ' . $db->qn('piav.product_item_id') . ' = ' . (int) $productItemId
					. ' AND ' . $db->qn('pa.main_attribute') . ' = 1'
				)
				->where($db->qn('m.state') . ' = 1')
				->order('piav.product_attribute_value_id DESC');
		}
		elseif ($colorId > 0)
		{
			$query->where($db->qn('m.attribute_value_id') . ' = ' . (int) ($colorId));
		}

		if ($mediaId > 0)
		{
			$query->where($db->qn('m.id') . ' = ' . (int) $mediaId);
		}

		$query->order('IF(m.view = 0, 0, 1/m.view) DESC, m.id');

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get image thumb path only
	 *
	 * @param   int  $productId      Product id
	 * @param   int  $productItemId  Product item id
	 * @param   int  $colorId        Color id
	 *
	 * @return  string  Thumbnail path
	 */
	public static function getProductImageThumbPath($productId, $productItemId = 0, $colorId = 0)
	{
		$item  = self::getProductImage($productId, $productItemId, $colorId);
		$thumb = '';

		if ($item)
		{
			$thumb = RedshopbHelperThumbnail::originalToResize($item->name, 144, 144, 100, 0, 'products', false, $item->remote_path);
		}

		return $thumb;
	}

	/**
	 * Get image thumb html
	 *
	 * @param   int      $productId      Product id
	 * @param   int      $productItemId  Product item id
	 * @param   int      $colorId        Color id
	 * @param   boolean  $setDimensions  Set image dimensions?
	 * @param   int      $width          If setDimensions this will be used for width
	 * @param   int      $height         If setDimensions this will be used for height
	 *
	 * @return  string  Thumbnail html
	 */
	public static function getProductImageThumbHtml(
		$productId,
		$productItemId = 0,
		$colorId = 0,
		$setDimensions = false,
		$width = 144,
		$height = 144
	)
	{
		$item            = self::getProductImage($productId, $productItemId, $colorId);
		$thumb           = '';
		$imageAttributes = array();

		if (!$item)
		{
			return $thumb;
		}

		if ($setDimensions)
		{
			if (empty($width) || empty($height))
			{
				// Thumbnail preparation
				$config = RedshopbEntityConfig::getInstance();
				$width  = empty($width) ? $config->getThumbnailWidth() : $width;
				$height = empty($height) ? $config->getThumbnailHeight() : $height;
			}

			$imageAttributes = array('height' => $height, 'width' => $width);
		}

		$imageUrl = RedshopbHelperThumbnail::originalToResize(
			$item->name, $width, $height, 100, 0, 'products', false, (isset($item->remote_path) ? $item->remote_path : '')
		);

		if ($imageUrl)
		{
			$thumb = HTMLHelper::_('image', $imageUrl, $item->name, $imageAttributes);
		}

		return $thumb;
	}

	/**
	 * Get product price.
	 *
	 * @param   int     $productId     Product item id.
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 * @param   string  $currency      Currency.
	 *
	 * @return float Product price.
	 */
	public static function getProductPrice($productId, $customerId = null, $customerType = '', $currency = 'DKK')
	{
		if ($customerId == null)
		{
			$customerId = Factory::getApplication()->getUserStateFromRequest('list.customer_id', 'customer_id', 0, 'int');
		}

		$pPrice = RedshopbHelperPrices::getProductPrice($productId, $customerId, $customerType, $currency);

		if (!is_object($pPrice))
		{
			$pPrice = new stdClass;
		}

		if (!isset($pPrice->price) || $pPrice->price <= 0)
		{
			return 0.0;
		}

		return $pPrice->price;
	}

	/**
	 * Load product
	 *
	 * @param   integer  $productId  product id
	 *
	 * @return  mixed    Product object on success, false on failure.
	 */
	public static function loadProduct($productId)
	{
		if (!array_key_exists($productId, self::$products))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('p.*')
				->from($db->qn('#__redshopb_product', 'p'))
				->where($db->qn('p.id') . ' = ' . (int) $productId);

			self::$products[$productId] = $db->setQuery($query)->loadObject();

			if (self::$products[$productId])
			{
				self::setProductRelates(array($productId => self::$products[$productId]));
			}
		}

		return self::$products[$productId];
	}

	/**
	 * Set product array
	 *
	 * @param   array  $products  Array product/s values
	 *
	 * @return void
	 */
	public static function setProduct($products)
	{
		self::$products = $products + self::$products;
		self::setProductRelates($products);
	}

	/**
	 * Set product relates
	 *
	 * @param   array  $products  Products
	 *
	 * @return  void
	 */
	public static function setProductRelates($products)
	{
		$keys = array();

		foreach ((array) $products as $product)
		{
			if (isset($product->id) && !isset(self::$products[$product->id]->categories))
			{
				$keys[] = (int) $product->id;
			}
		}

		if (empty($keys))
		{
			return;
		}

		$db    = RFactory::getDbo();
		$query = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p'))
			->where('p.id IN (' . implode(',', $keys) . ')')

			// Check state main category
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pc3') . ' ON pc3.product_id = p.id AND p.category_id = pc3.category_id')
			->leftJoin($db->qn('#__redshopb_category', 'c3') . ' ON pc3.category_id = c3.id AND c3.state = 1');

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(DISTINCT c2.id ORDER BY c2.id ASC SEPARATOR ' . $db->q(',') . ')')
			->from($db->qn('#__redshopb_category', 'c2'))
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pc2') . ' ON c2.id = pc2.category_id')
			->where('p.id = pc2.product_id')
			->where('((p.category_id IS NOT NULL AND p.category_id != pc2.category_id) OR p.category_id IS NULL)')
			->where('c2.state = 1');

		// In first position set main category id
		$query->select('CONCAT_WS(' . $db->q(',') . ', c3.id, (' . $subQuery . ')) AS categories');

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(DISTINCT pwcsx.wash_care_spec_id SEPARATOR ' . $db->q(',') . ')')
			->from($db->qn('#__redshopb_product_wash_care_spec_xref', 'pwcsx'))
			->leftJoin($db->qn('#__redshopb_wash_care_spec', 'wcs') . ' ON wcs.id = pwcsx.wash_care_spec_id')
			->where('product_id = p.id')
			->where('wcs.state = 1');
		$query->select('(' . $subQuery . ') AS wash_care_spec_id');

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(DISTINCT ptx.tag_id SEPARATOR ' . $db->q(',') . ')')
			->from($db->qn('#__redshopb_product_tag_xref', 'ptx'))
			->leftJoin($db->qn('#__redshopb_tag', 'pt') . ' ON pt.id = ptx.tag_id')
			->where('ptx.product_id = p.id')
			->where('pt.state = 1');
		$query->select('(' . $subQuery . ') AS tag_id');

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(DISTINCT pcompx.company_id SEPARATOR ' . $db->q(',') . ')')
			->from($db->qn('#__redshopb_product_company_xref', 'pcompx'))
			->leftJoin($db->qn('#__redshopb_company', 'comp') . ' ON comp.id = pcompx.company_id')
			->where('pcompx.product_id = p.id')
			->where('comp.state = 1');
		$query->select('(' . $subQuery . ') AS customer_ids');
		$oldTranslate  = $db->translate;
		$db->translate = false;
		$relates       = $db->setQuery($query)->loadObjectList('id');

		if ($relates)
		{
			foreach ($relates as $productId => $relate)
			{
				self::$products[$productId]->categories = explode(',', $relate->categories);

				if (count(self::$products[$productId]->categories) == 0)
				{
					self::$products[$productId]->categories[] = 0;
				}

				self::$products[$productId]->wash_care_spec_id = explode(',', $relate->wash_care_spec_id);
				self::$products[$productId]->tag_id            = explode(',', $relate->tag_id);
				self::$products[$productId]->customer_ids      = explode(',', $relate->customer_ids);
				RedshopbEntityProduct::getInstance($productId)
					->bind(self::$products[$productId]);
			}
		}

		$db->translate = $oldTranslate;
		RFactory::getDispatcher()->trigger('onRedshopbSetProductRelates', array(&self::$products, $keys));
	}

	/**
	 * Get all the valid SKUs / Product Items for a product
	 *
	 * @param   integer  $productId      The product Id
	 * @param   string   $arrayType      (optional) output format: objectList, array
	 * @param   bool     $onlyPublished  Flag from selected only published items
	 *
	 * @return  array    Array of valid SKUs with their product item: objectList (pi_id, sku), array: simple array with product item id as key
	 */
	public static function getSKUCollection($productId, $arrayType = 'objectList', $onlyPublished = true)
	{
		$result = self::getSKUCollections(array($productId), $arrayType, $onlyPublished);

		if ($result && !empty($result) && $arrayType == 'array')
		{
			$result = $result[$productId];
		}

		return $result;
	}

	/**
	 * Get all the valid SKUs / Product Items for a product
	 *
	 * @param   array   $productIds     The product Id
	 * @param   string  $arrayType      (optional) output format: objectList, array
	 * @param   bool    $onlyPublished  Flag from selected only published items
	 * @param   int     $collectionId   Collection id
	 *
	 * @return  array    Array of valid SKUs with their product item: objectList (pi_id, sku), array: simple array with product item id as key
	 */
	public static function getSKUCollections($productIds, $arrayType = 'objectList', $onlyPublished = true, $collectionId = 0)
	{
		if (!count($productIds))
		{
			return array();
		}

		$db         = Factory::getDbo();
		$productIds = ArrayHelper::toInteger($productIds);
		$query      = $db->getQuery(true)
			->select(
				array(
					$db->qn('pi.id', 'pi_id'),
					$db->qn('pi.product_id'),
					$db->qn('pi.sku')
				)
			)
			->from($db->qn('#__redshopb_product_item', 'pi'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON pi.product_id = p.id')
			->where('pi.product_id IN (' . implode(',', $productIds) . ')')
			->group('pi.id');

		if ($onlyPublished)
		{
			$query->where('pi.state = 1');
		}

		if ($collectionId)
		{
			$query->leftJoin($db->qn('#__redshopb_collection_product_item_xref', 'cpix') . ' ON pi.id = cpix.product_item_id')
				->where('cpix.collection_id = ' . (int) $collectionId);

			if ($onlyPublished)
			{
				$query->where('cpix.state = 1');
			}
		}

		$refProductItems = $db->setQuery($query)->loadObjectList();

		switch ($arrayType)
		{
			case 'array':
				$productItems = array();

				foreach ($refProductItems as $productItem)
				{
					if (!isset($productItems[$productItem->product_id]))
					{
						$productItems[$productItem->product_id] = array();
					}

					$productItems[$productItem->product_id][$productItem->pi_id] = $productItem->sku;
				}

				return $productItems;
			default:
				return $refProductItems;
		}
	}

	/**
	 * Get product SKU.
	 *
	 * @param   int  $productId  Product id.
	 *
	 * @return string Product SKU.
	 */
	public static function getSKU($productId)
	{
		$product = self::loadProduct($productId);

		return $product->sku;
	}

	/**
	 * Get product name.
	 *
	 * @param   int  $productId  Product id.
	 *
	 * @return string Product name.
	 */
	public static function getName($productId)
	{
		$product = self::loadProduct($productId);

		return $product->name;
	}

	/**
	 * Method for get list of Wash & Care Information of an product
	 *
	 * @param   int  $productId  ID of product
	 *
	 * @return  array/boolean    Array of Wash & Care data. False other wise.
	 */
	public static function getWashCareSpecs($productId)
	{
		$productId = (int) $productId;

		if (!$productId)
		{
			return false;
		}

		$washCareModels = RModelAdmin::getInstance('Wash_Care_Specs', 'RedshopbModel');
		$washCareModels->setState('filter.product_id', $productId);
		$washCareModels->setState('filter.wash_care_spec_state', 1);
		$washCareModels->setState('list.ordering', 'wcs.ordering');
		$washCareItems = $washCareModels->getItems();

		if (empty($washCareItems))
		{
			return false;
		}

		return $washCareItems;
	}

	/**
	 * Get Products Data
	 *
	 * @param   array  $products  Products array
	 * @param   array  $ids       Product list id
	 *
	 * @return  array
	 */
	public static function getProductsData($products = array(), $ids = array())
	{
		if (empty($ids))
		{
			$ids = array();

			foreach ($products as $item)
			{
				$itemId = is_numeric($item) ? $item : (int) $item->id;
				$ids[]  = $itemId;
			}
		}
		else
		{
			$ids = ArrayHelper::toInteger($ids);
		}

		$db = Factory::getDbo();

		// Get general description for each of item.
		$query       = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_product_descriptions'))
			->where($db->qn('product_id') . ' IN (' . implode(',', $ids) . ')')
			->group('product_id');
		$descResults = $db->setQuery($query)->loadObjectList('product_id');

		// Check if item is in favourite list of current user.
		$rsbUser   = RedshopbEntityUser::loadActive(true);
		$rsbUserId = $rsbUser->get('id');

		if ($rsbUserId)
		{
			$query->clear()
				->select(array('COUNT(' . $db->qn('flpx.id') . ') AS ' . $db->qn('count'), 'flpx.product_id'))
				->from($db->qn('#__redshopb_favoritelist_product_xref', 'flpx'))
				->leftJoin($db->qn('#__redshopb_favoritelist', 'fl') . ' ON ' . $db->qn('fl.id') . ' = ' . $db->qn('flpx.favoritelist_id'))
				->where($db->qn('flpx.product_id') . '  IN (' . implode(',', $ids) . ')')
				->where($db->qn('fl.user_id') . ' = ' . (int) $rsbUserId)
				->group('flpx.product_id');
			$oldTranslate    = $db->translate;
			$db->translate   = false;
			$favoriteResults = $db->setQuery($query)->loadObjectList('product_id');
			$db->translate   = $oldTranslate;
		}
		else
		{
			$favoriteResults = null;
		}

		// Get Wash & Care spec for each of item.
		$query->clear()
			->select('wcs.*, pwcsx.product_id')
			->from($db->qn('#__redshopb_wash_care_spec', 'wcs'))
			->innerJoin($db->qn('#__redshopb_product_wash_care_spec_xref', 'pwcsx') . ' ON pwcsx.wash_care_spec_id = wcs.id')
			->where('pwcsx.product_id IN (' . implode(',', $ids) . ')')
			->where('wcs.state = 1')
			->order('wcs.id ASC');
		$washResults = $db->setQuery($query)->loadObjectList();
		$washArray   = array();

		// Get unit of measures
		$unitOfMeasureIds = array();

		foreach ($products as $id => $product)
		{
			if (!empty($product->unit_measure_id)
				&& !array_key_exists($product->unit_measure_id, $unitOfMeasureIds))
			{
				$unitOfMeasureEntity = RedshopbEntityUnit_Measure::getInstance($product->unit_measure_id);

				if (!$unitOfMeasureEntity->isLoaded())
				{
					$unitOfMeasureIds[$product->unit_measure_id] = $product->unit_measure_id;
				}
			}
		}

		if (!empty($unitOfMeasureIds))
		{
			$query->clear()
				->select('um.*')
				->from($db->qn('#__redshopb_unit_measure', 'um'))
				->where('um.id IN (' . implode(',', $unitOfMeasureIds) . ')');
			$umArray = $db->setQuery($query)->loadObjectList('id');

			foreach ($umArray as $umId => $um)
			{
				RedshopbEntityUnit_Measure::getInstance($umId)->bind($um);
			}
		}

		if ($washResults)
		{
			foreach ($washResults as $washResult)
			{
				if (!isset($washArray[$washResult->product_id]))
				{
					$washArray[$washResult->product_id] = array();
				}

				$washArray[$washResult->product_id] = $washResult;
			}
		}

		foreach ($products as $id => $product)
		{
			$products[$id]->inFavouriteList = false;

			if (isset($favoriteResults[$product->id]))
			{
				$products[$id]->inFavouriteList = true;
			}

			$products[$id]->description = false;

			if (isset($descResults[$product->id]))
			{
				$products[$id]->description = $descResults[$product->id];
			}

			$products[$id]->wash_care_specs = false;

			if (isset($washArray[$product->id]))
			{
				$products[$id]->wash_care_specs = $washArray[$product->id];
			}

			if (!empty($product->unit_measure_id))
			{
				$products[$id]->unit_measure_text = RedshopbEntityUnit_Measure::getInstance($product->unit_measure_id)
					->get('name', Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS'));
			}
			else
			{
				$products[$id]->unit_measure_text = Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS');
			}
		}

		return $products;
	}

	/**
	 * Method to get recently purchased products
	 *
	 * @param   int  $limit  Limit of products
	 *
	 * @return  array    Array of recent products. False otherwise.
	 */
	public static function getRecentProducts($limit = 0)
	{
		$db     = Factory::getDbo();
		$user   = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');
		$userId = $user ? $user->id : '0';

		$query = $db->getQuery(true)
			->select('p.*')
			->select('IFNULL(c.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->select('c.asset_id AS company_asset_id')
			->select($db->qn('pp.price', 'price'))
			->select($db->qn('pp.retail_price', 'retail_price'))
			->select($db->qn('oi.currency_id', 'currency'))
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin('#__redshopb_company AS c ON c.id = p.company_id AND ' . $db->qn('c.deleted') . ' = 0')
			->leftJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON oi.product_id = p.id')
			->leftJoin($db->qn('#__redshopb_order', 'o') . ' ON o.id = oi.order_id')
			->leftJoin(
				$db->qn('#__redshopb_product_price', 'pp')
				. ' ON ' . $db->qn('pp.type_id') . ' = ' . $db->qn('p.id')
				. ' AND ' . $db->qn('pp.type') . ' = ' . $db->quote('product')
				. ' AND ' . $db->qn('pp.sales_type') . ' = ' . $db->quote('all_customers')
				. ' AND ' . $db->qn('pp.quantity_min') . ' IS NULL'
				. ' AND ' . $db->qn('quantity_max') . ' IS NULL'
			)
			->where('o.customer_type = ' . $db->q('employee'))
			->where('o.customer_id = ' . (int) $userId)
			->order('o.created_date')
			->group('p.id');

		if ($userId != 0 && $userId != '')
		{
			$query->select('COUNT(' . $db->qn('flpx.id') . ') AS ' . $db->qn('favoritelists'))
				->leftJoin($db->qn('#__redshopb_favoritelist_product_xref', 'flpx') . ' ON ' . $db->qn('flpx.product_id') . ' = ' . $db->qn('p.id'))
				->leftJoin($db->qn('#__redshopb_favoritelist', 'fl') . ' ON ' . $db->qn('fl.id') . ' = ' . $db->qn('flpx.favoritelist_id'))
				->where('(fl.user_id IS NULL OR fl.user_id = ' . (int) $userId . ')');
		}

		$db->setQuery($query, 0, $limit);
		$recentProducts = $db->loadObjectList();

		foreach ($recentProducts as $i => $product)
		{
			// Fetch thumbnail obeying dimensions from config
			$recentProducts[$i]->thumb = self::getProductImageThumbHtml($product->id, 0, 0, true);
		}

		return $recentProducts;
	}

	/**
	 * Method to get the most purchased products
	 *
	 * @param   int  $limit  Limit of products
	 *
	 * @return  array    Array of recent products. False otherwise.
	 */
	public static function getMostPurchased($limit = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('p.*')
			->select('IFNULL(c.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->select('c.asset_id AS company_asset_id')
			->select($db->qn('pp.price', 'price'))
			->select($db->qn('pp.retail_price', 'retail_price'))
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin('#__redshopb_company AS c ON c.id = p.company_id AND ' . $db->qn('c.deleted') . ' = 0')
			->leftJoin(
				$db->qn('#__redshopb_product_price', 'pp')
				. ' ON ' . $db->qn('pp.type_id') . ' = ' . $db->qn('p.id')
				. ' AND ' . $db->qn('pp.type') . ' = ' . $db->quote('product')
				. ' AND ' . $db->qn('pp.sales_type') . ' = ' . $db->quote('all_customers')
				. ' AND ' . $db->qn('pp.quantity_min') . ' IS NULL'
				. ' AND ' . $db->qn('quantity_max') . ' IS NULL'
			)
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.discontinued') . ' = 0')
			->where($db->qn('p.service') . ' = 0')
			->group('p.id');

		// Exclude products set up as fee or freight
		$query2 = $db->getQuery(true)
			->select($db->qn('freight_product_id'))
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('deleted') . ' = 0')
			->where($db->qn('freight_product_id') . ' IS NOT NULL');
		$query3 = $db->getQuery(true)
			->select($db->qn('product_id'))
			->from($db->qn('#__redshopb_fee'))
			->where($db->qn('product_id') . ' IS NOT NULL');

		$query->where($db->qn('p.id') . ' NOT IN (' . $query2->__toString() . ')');
		$query->where($db->qn('p.id') . ' NOT IN (' . $query3->__toString() . ')');

		// Volume pricing
		$volumePriceQuery = $db->getQuery(true);
		$volumePriceQuery->select('vp.id')
			->from($db->qn('#__redshopb_product_price', 'vp'))
			->where($db->qn('vp.type') . ' = ' . $db->q('product'))
			->where($db->qn('vp.type_id') . ' = ' . $db->qn('p.id'))
			->where('(' . $db->qn('vp.quantity_min') . ' IS NOT NULL OR ' . $db->qn('vp.quantity_max') . ' IS NOT NULL)');

		$query->select('(' . $volumePriceQuery . ' LIMIT 0, 1) AS hasVolumePricing');

		$orderQuery = $db->getQuery(true)
			->select('COUNT(o.id) as frequency, oi.product_id')
			->from($db->qn('#__redshopb_order', 'o'))
			->leftJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
			->group('oi.product_id');

		$user      = RedshopbHelperCommon::getUser();
		$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

		// Limit companies based on allowed permissions (main warehouse or allowed companies' categories)
		if ($user->b2cMode)
		{
			$availableCompanies = RedshopbEntityCompany::getInstance($user->b2cCompany)->getTree(true, true);
			$query->where(
				'(' . $db->qn('p.company_id') . ' IN(' . implode(',', $availableCompanies) . ') OR ' . $db->qn('p.company_id') . ' IS NULL)'
			);
		}
		else
		{
			// This list not available for main company users and fro super admins
			if (RedshopbHelperUser::isFromMainCompany($rsbUserId, 'employee'))
			{
				$query->where('0 = 1');
			}
			else
			{
				$companies = RedshopbHelperACL::listAvailableCompanies($user->id, 'comma', 0, '', 'redshopb.company.view', '', true);
				$companies = explode(',', $companies);

				if (!empty($companies))
				{
					// Exclude current company of user
					$userCompany = RedshopbHelperUser::getUserCompany();

					if ($userCompany)
					{
						$orderQuery->where($db->qn('o.customer_company') . ' = ' . (int) $userCompany->id);
						$key = array_search($userCompany->id, $companies);
					}

					if ($userCompany && $key !== false)
					{
						unset($companies[$key]);
					}
				}

				if (empty($companies))
				{
					$companies[] = 0;
				}

				$query->where(
					'(' . $db->qn('p.company_id') . ' IN ('
					. implode(',', $companies) . ') OR '
					. $db->qn('p.company_id') . ' IS NULL)'
				);
			}
		}

		$query->select('orderCount.frequency')
			->innerJoin('(' . $orderQuery . ') AS orderCount ON orderCount.product_id = p.id')
			->order('orderCount.frequency DESC');

		$mostPurchased = $db->setQuery($query, 0, $limit)
			->loadObjectList('id');

		if ($mostPurchased)
		{
			$ids = implode(',', array_keys($mostPurchased));
			$query->clear()
				->select('SUM(' . $db->quote('oi.quantity') . ') AS psum, oi.product_id, oi.currency_id')
				->from($db->qn('#__redshopb_order_item', 'oi'))
				->where('oi.product_id IN (' . $ids . ')')
				->group('oi.product_id');

			$quantitySum = $db->setQuery($query)
				->loadObjectList('product_id');

			$user      = Factory::getUser();
			$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

			if ($rsbUserId != 0 && $rsbUserId != '')
			{
				$query->clear()
					->select('COUNT(' . $db->qn('flpx.id') . ') AS ' . $db->qn('favoritelists'))
					->select('flpx.product_id')
					->from($db->qn('#__redshopb_favoritelist_product_xref', 'flpx'))
					->leftJoin($db->qn('#__redshopb_favoritelist', 'fl') . ' ON ' . $db->qn('fl.id') . ' = ' . $db->qn('flpx.favoritelist_id'))
					->where('fl.user_id = ' . (int) $rsbUserId)
					->where('flpx.product_id IN (' . $ids . ')')
					->group('flpx.product_id');

				$favouriteSum = $db->setQuery($query)
					->loadObjectList('product_id');
			}

			self::setProduct($mostPurchased);

			foreach ($mostPurchased as $i => $product)
			{
				// Fetch thumbnail obeying dimensions from config
				$product->thumb = self::getProductImageThumbHtml($product->id, 0, 0, true);

				if (isset($quantitySum[$i]))
				{
					$product->psum     = $quantitySum[$i]->psum;
					$product->currency = $quantitySum[$i]->currency_id;
				}
				else
				{
					$product->psum     = 0;
					$product->currency = null;
				}

				if (isset($favouriteSum[$i]))
				{
					$product->favoritelists = $favouriteSum[$i]->favoritelists;
				}
				else
				{
					$product->favoritelists = 0;
				}
			}
		}

		return $mostPurchased;
	}

	/**
	 * Method for get company Id of an product
	 *
	 * @param   integer  $productId  ID of product
	 *
	 * @return  integer              ID of company if success. False otherwise.
	 *
	 * @deprecated  2.0  Use RedshopbEntityProduct::getInstance($productId)->getCompany()->id
	 */
	public static function getCompanyId($productId = 0)
	{
		$company = RedshopbEntityProduct::getInstance($productId)->getCompany();

		return $company->isLoaded() ? $company->id : false;
	}

	/**
	 * Load the fields related to this product
	 *
	 * @param   int   $productId    ID of product
	 * @param   bool  $getFullInfo  Getting full field info
	 *
	 * @return  mixed   Object list on success, false otherwise
	 */
	public static function loadProductFields($productId, $getFullInfo = false)
	{
		return RedshopbHelperField::loadScopeFieldData('product', $productId, 0, $getFullInfo);
	}

	/**
	 * Method for check if product is in favourite list of specific user
	 *
	 * @param   int  $productId  ID of product
	 * @param   int  $rsbUserId  Redshopb User ID.
	 *
	 * @return  boolean          True if in favourite list. False otherwise.
	 */
	public static function isInFavouriteList($productId = 0, $rsbUserId = 0)
	{
		if (!$productId)
		{
			return false;
		}

		$rsbUser = RedshopbHelperUser::getUser();

		if (!$rsbUserId && $rsbUser)
		{
			$rsbUserId = $rsbUser->id;
		}
		else
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(' . $db->qn('flpx.id') . ') AS ' . $db->qn('count'))
			->from($db->qn('#__redshopb_product', 'p'))
			->join('left', $db->qn('#__redshopb_favoritelist_product_xref', 'flpx') . ' ON ' . $db->qn('flpx.product_id') . ' = ' . $db->qn('p.id'))
			->join(
				'left', $db->qn('#__redshopb_favoritelist', 'fl') . ' ON ' . $db->qn('fl.id') . ' = ' . $db->qn('flpx.favoritelist_id') .
				' AND ' . $db->qn('fl.user_id') . ' = ' . (int) $rsbUserId
			)
			->where($db->qn('p.id') . ' = ' . (int) $productId);
		$db->setQuery($query);

		return (boolean) $db->loadObject()->count;
	}

	/**
	 * Method for get categories of an product
	 *
	 * @param   int  $productId  ID of product.
	 *
	 * @return  array/boolean    List ID of categories. False otherwise.
	 */
	public static function getCategories($productId = 0)
	{
		if (!$productId)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('category_id'))
			->from($db->qn('#__redshopb_product_category_xref'))
			->where($db->qn('product_id') . ' = ' . (int) $productId);

		return $db->setQuery($query)->loadColumn();
	}

	/**
	 * Method for format number follow decimal position of product.
	 *
	 * @param   integer|float  $number     Number for format.
	 * @param   integer        $productId  ID of specific product.
	 *
	 * @return  float|integer              Formatted number.
	 */
	public static function decimalFormat($number, $productId)
	{
		$productId = (int) $productId;

		if (!$productId)
		{
			return $number;
		}

		$decimal = RedshopbEntityProduct::load($productId)->getDecimalPosition();

		if (!$decimal)
		{
			return (float) $number;
		}

		return (float) number_format((float) $number, $decimal, '.', '');
	}

	/**
	 * Returns product description
	 *
	 * @param   object  $productDescription  Product description
	 *
	 * @return  string
	 */
	public static function getProductDescription($productDescription)
	{
		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos  = preg_match($pattern, $productDescription);

		if ($tagPos > 0)
		{
			list (, $productDescription) = preg_split($pattern, $productDescription, 2);
		}

		if (RedshopbApp::getConfig()->get('product_desc_process', 0))
		{
			// Run dispatcher for product's description
			PluginHelper::importPlugin('content');
			$productDescription = HTMLHelper::_('content.prepare', $productDescription);
		}

		return $productDescription;
	}

	/**
	 * Function for getting product items.
	 *
	 * @param   int  $productId  Product id.
	 *
	 * @return  array  Array of product items.
	 */
	public static function getProductItems($productId)
	{
		$model = RedshopbModel::getFrontInstance('Product');

		if ($productId)
		{
			return $model->getProductItems($productId);
		}

		return array();
	}

	/**
	 * Method for render volume prices of specific product.
	 *
	 * @param   int      $productId     Product ID
	 * @param   int      $customerId    Customer ID
	 * @param   string   $customerType  Customer Type
	 * @param   int      $currencyId    Currency ID
	 * @param   boolean  $includeTax    Include tax or not.
	 *
	 * @return  string
	 */
	public static function renderVolumePrices($productId = 0, $customerId = 0, $customerType = '', $currencyId = 0, $includeTax = false)
	{
		if (!$productId)
		{
			return null;
		}

		if (!$customerId)
		{
			$customerId = Factory::getApplication()->getUserState('shop.customer_id', 0);
		}

		if (empty($customerType))
		{
			$customerType = Factory::getApplication()->getUserState('shop.customer_type', '');
		}

		if (!$currencyId)
		{
			$currencyId = RedshopbApp::getConfig()->get('default_currency', 38);
		}

		$prices = RedshopbHelperPrices::getProductsPrice(
			array($productId),
			$customerId,
			$customerType,
			$currencyId,
			array(0),
			'',
			RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType),
			null,
			false,
			false,
			false
		);

		if (empty($prices))
		{
			return '';
		}

		// Remove price which is not volume price
		foreach ($prices as $index => $price)
		{
			if (!$price->quantity_min && !$price->quantity_max)
			{
				unset($prices[$index]);
			}
		}

		if ($includeTax)
		{
			return RedshopbLayoutHelper::render(
				'price.volumewithtax',
				array('productId' => $productId, 'prices' => $prices)
			);
		}

		return RedshopbLayoutHelper::render(
			'price.volume',
			array('productId' => $productId, 'prices' => $prices)
		);
	}
}
