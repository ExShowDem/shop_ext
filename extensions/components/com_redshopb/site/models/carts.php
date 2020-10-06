<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Saved Carts Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCarts extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_carts';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'carts_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'sc.id',
				'sc.name',
				'products_count',
				'sc.search_carts'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('sc.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'sc.*',
					$db->qn('u.name1', 'user_name')
				)
			)
			->from($db->qn('#__redshopb_cart', 'sc'))
			->join('left', $db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('sc.user_id'));

		// Count number of products
		$countProductQuery = $db->getQuery(true)
			->select('SUM(' . $db->qn('sci.quantity') . ')')
			->from($db->qn('#__redshopb_cart_item', 'sci'))
			->where($db->qn('sc.id') . ' = ' . $db->qn('sci.cart_id'));

		$query->select('(' . $countProductQuery . ') AS ' . $db->qn('products_count'));
		unset($countProductQuery);

		// Filter by user id
		$userId = (int) $this->getState('filter.user_id');

		if (!$userId)
		{
			$user = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

			if ($user)
			{
				$userId = $user->id;
			}
		}

		if ($userId)
		{
			$query->where($db->qn('sc.user_id') . ' = ' . $userId);
		}

		$search = $this->getState('filter.search_carts', '');

		if (!empty($search))
		{
			$query->where($db->qn('sc.name') . ' LIKE ' . $db->q('%' . $search . '%'));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'sc.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of raw items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   __DEVELOP_VERSION__
	 */
	public function getRawItems()
	{
		return parent::getItems();
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$carts = parent::getItems();

		if (empty($carts))
		{
			return $carts;
		}

		foreach ($carts as $cart)
		{
			$cartItems = RedshopbEntityCart::getInstance($cart->id)
				->applyCartItemsPrices();

			if (empty($cartItems))
			{
				continue;
			}

			$totals = array();

			foreach ($cartItems as $cartItem)
			{
				if ($cartItem->parent_cart_item_id)
				{
					$accessoryData = $cartItem->get('accessory');

					if (is_array($accessoryData))
					{
						$parentItem = $cartItems->get($cartItem->get('parent_cart_item_id'));
						$currency   = $parentItem->get('price')->currency;

						if (!array_key_exists($currency, $totals))
						{
							$totals[$currency] = 0;
						}

						if ($accessoryData['quantity'] <= 1)
						{
							$accessoryQuantity = $parentItem->get('quantity');
						}
						else
						{
							$accessoryQuantity = $accessoryData['quantity'];
						}

						$totals[$currency] += $accessoryQuantity * $accessoryData['price'];
					}
				}
				else
				{
					$prices = $cartItem->get('price');

					if (is_object($prices))
					{
						if (!array_key_exists($prices->currency, $totals))
						{
							$totals[$prices->currency] = 0;
						}

						$totals[$prices->currency] += $cartItem->get('quantity') * $prices->price;
					}
				}
			}

			$cart->totals = $totals;
		}

		return $carts;
	}

	/**
	 * Import cart
	 *
	 * @param   array  $importData  Data received from CSV file
	 *
	 * @return  mixed
	 */
	public function import($importData)
	{
		$result  = array();
		$keys    = array_keys($importData[0]);
		$columns = $this->getCsvColumns($keys);

		foreach ($importData as $rowNumber => $row)
		{
			if (!is_array($row))
			{
				$result['error'][] = Text::sprintf(
					'COM_REDSHOPB_CART_UNSUCCESSFULLY_IMPORTED', Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_COLUMNS_MISSING', $rowNumber + 2)
				);
				continue;
			}

			$data = array();

			// Prepare data with same columns
			foreach ($columns as $columnKey => $columnValue)
			{
				if (isset($row[strtolower($columnValue)]))
				{
					$data[$columnKey] = $row[strtolower($columnValue)];
				}
			}

			$productIdField = $keys[1];

			if ($productIdField === "sku")
			{
				$productInfo = RedshopbEntityProduct::getInstance()->loadItem('sku', $data['sku'])->getItem();

				if (empty($productInfo))
				{
					Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_SKU_DONT_EXIST', $data['sku']), 'error');
					continue;
				}
			}
			else
			{
				$productInfo = RedshopbEntityProduct::getInstance()->loadProductByCustomField($productIdField, $data[$productIdField]);

				if ($productInfo === false)
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_SKU_DONT_EXIST', $data[$productIdField]), 'error'
					);
					continue;
				}
			}

			if ((boolean) $productInfo->state === false)
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_SKU_DONT_EXIST', $data[$productIdField]), 'error'
				);
				continue;
			}

			$productId        = $productInfo->id;
			$productPriceInfo = RedshopbHelperPrices::getProductsPrice(array($productId));
			$productPrice     = $productPriceInfo[$productId]->price;

			// When importing carts, the CRUD operation is always CREATE
			if ($data['CRUD'] == 'CREATE')
			{
				$addResult = RedshopbHelperCart::addToCartById(
					intval($productId),
					0,
					null,
					$data[$keys[2]],
					floatval($productPrice),
					0,
					0,
					''
				);

				$result = $addResult['messageType'];
			}
		}

		return $result;
	}

	/**
	 * Get the columns for the csv file.
	 *
	 * @param   array  $keys  Headers.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public function getCsvColumns($keys)
	{
		return array(
			'CRUD' => Text::_('COM_REDSHOPB_CRUD'),
			$keys[1] => $keys[1],
			$keys[2] => $keys[2],
		);
	}
}
