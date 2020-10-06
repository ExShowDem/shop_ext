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
 * Product Item table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Price extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_price';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $product_item_id;

	/**
	 * @var  integer
	 */
	public $type_id;

	/**
	 * @var  string
	 */
	public $type;

	/**
	 * @var  string
	 */
	public $sales_type;

	/**
	 * @var  integer
	 */
	public $sales_code;

	/**
	 * @var  integer
	 */
	public $currency_id;

	/**
	 * @var  float
	 */
	public $price;

	/**
	 * @var  string
	 */
	public $starting_date = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $ending_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $is_multiple = null;

	/**
	 * @var  integer
	 */
	public $quantity_min = null;

	/**
	 * @var  integer
	 */
	public $quantity_max = null;

	/**
	 * @var  integer
	 */
	public $allow_discount;

	/**
	 * @var integer
	 */
	public $country_id = null;

	/**
	 * @var integer
	 */
	protected $company_id = null;

	/**
	 * @var integer
	 */
	protected $customer_price_group_id = null;

	/**
	 * @var string
	 */
	protected $campaign_code = null;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.product_price'
		),
		'pim' => array(
			'erp.pim.product_price'
		),
		'fengel' => array(
			'fengel.product_price_item'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'product_id' => array(
			'model' => 'Products'
		),
		'product_item_id' => array(
			'model' => 'Product_Items'
		),
		'customer_price_group_id' => array(
			'model' => 'Price_Debtor_Groups'
		),
		'company_id' => array(
			'model' => 'Companies'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'currency_code' => 'Currencies',
		'country_code' => 'Countries'
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'allow_discount'
	);

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if ($this->quantity_min || $this->quantity_max)
		{
			$decimalPosition = 0;
			$productId       = ($this->type == 'product')
				? $this->type_id
				: RedshopbEntityProduct_Item::load($this->type_id)->getProduct()->get('id');

			$this->quantity_min = (!is_null($this->quantity_min)) ? RedshopbHelperProduct::decimalFormat($this->quantity_min, $productId) : null;
			$this->quantity_max = (!is_null($this->quantity_max)) ? RedshopbHelperProduct::decimalFormat($this->quantity_max, $productId) : null;

			if ((float) $this->quantity_min && (float) $this->quantity_max && $this->quantity_min > $this->quantity_max)
			{
				$this->setError(Text::_('COM_REDSHOPB_PRICE_SAVE_ERROR_VOLUME_MIN_MAX'));

				return false;
			}
		}

		$this->prepareTypeForSave($this->type, $this->type_id);

		$cloneTable = clone $this;

		if ($cloneTable->load($this->get('id')))
		{
			$previousPrice = $cloneTable->get('price');
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if (!isset($previousPrice) || $previousPrice != $this->get('price'))
		{
			$price          = new stdClass;
			$price->type    = $this->type;
			$price->type_id = $this->type_id;

			$this->updateOffers(array($price));
		}

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
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (empty($src['is_multiple']))
		{
			$this->is_multiple = 0;
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Method to insure the product_id is always set when saving a product_price
	 *
	 * @param   string  $type    'product' or 'product_item' used to determin the type of price being saved
	 * @param   int     $typeId  Either the product table ID or the product item table ID
	 *
	 * @return  void
	 */
	protected function prepareTypeForSave($type, $typeId)
	{
		// If we don't have a type id, then don't do anything

		if (empty($typeId))
		{
			return;
		}

		switch ($type)
		{
			case 'product':

				if ($this->product_id != $typeId)
				{
					$this->product_id = $this->type_id;
				}

				break;

			case 'product_item':

				if ($this->product_item_id != $typeId)
				{
					$this->product_item_id = $this->type_id;
				}

				if (empty($this->product_id))
				{
					$productItem = RedshopbTable::getAdminInstance('Product_Item');

					if ($productItem->load($this->product_item_id))
					{
						$this->product_id = $productItem->product_id;
					}
				}

				break;
		}
	}

	/**
	 * Method for update offers
	 *
	 * @param   array  $prices  List of prices
	 *
	 * @return  boolean        True if success. False otherwise.
	 */
	protected function updateOffers($prices = array())
	{
		if (!count($prices))
		{
			return true;
		}

		$productIds     = array();
		$productItemIds = array();
		$db             = Factory::getDbo();
		$query          = $db->getQuery(true)
			->select('oix.offer_id')
			->from($db->qn('#__redshopb_offer_item_xref', 'oix'))
			->leftJoin($db->qn('#__redshopb_offer', 'o') . ' ON o.id = oix.offer_id')
			->where('o.state = 1')
			->where($db->qn('o.status') . '!=' . $db->q('ordered'))
			->group('oix.offer_id');
		$or             = array();

		foreach ($prices as $price)
		{
			if ($price->type == 'product')
			{
				$productIds[] = $price->type_id;
			}
			else
			{
				$productItemIds[] = $price->type_id;
			}
		}

		if (count($productIds) > 0)
		{
			$or[] = 'oix.product_id IN (' . implode(',', $productIds) . ') AND product_item_id IS NULL';
		}

		if (count($productItemIds) > 0)
		{
			$or[] = 'oix.product_item_id IN (' . implode(',', $productItemIds) . ')';
		}

		$query->where('(' . implode(' OR ', $or) . ')');
		$offers = $db->setQuery($query)
			->loadColumn();

		if (!empty($offers))
		{
			$query->clear()
				->update($db->qn('#__redshopb_offer'))
				->set('state = 0')
				->where('id IN (' . implode(',', $offers) . ')');
			$db->setQuery($query)
				->execute();
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_OFFER_UPUBLISH_BECAUSE_PRODUCT_CHANGES'), 'warning');
		}

		return true;
	}

	/**
	 * Method to delete all record matching type_id and type
	 *
	 * @param   string  $typeIds  either comma separated string of product ids or product item ids
	 * @param   string  $type     either 'product' or 'product_item'
	 *
	 * @return boolean
	 */
	public function deleteByTypeId($typeIds, $type)
	{
		return $this->deleteByTypes('type_id', 'type', $typeIds, $type);
	}

	/**
	 * Method to delete all record matching type_id and type
	 *
	 * @param   string  $salesIds   either comma separated string of company ids, customer_price_group_id
	 * @param   string  $salesType  either 'customer_price_group' or 'customer_price'
	 *
	 * @return boolean
	 */
	public function deleteBySalesId($salesIds, $salesType)
	{
		return $this->deleteByTypes('sales_code', 'sales_type', $salesIds, $salesType);
	}

	/**
	 * Method to delete all record either sales_id/sales_code or type_id/type values
	 *
	 * @param   string  $idField    Name of the id field 'type_id' or 'sales_id'
	 * @param   string  $typeField  Name of the field 'type' or 'sales_code'
	 * @param   string  $ids        comma separated string of company ids, customer_price_group_id, product ids or product item ids
	 * @param   string  $type       either 'customer_price_group','customer_price','product' or 'product_item'
	 *
	 * @return boolean
	 */
	private function deleteByTypes($idField, $typeField, $ids, $type)
	{
		if (empty($ids))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($this->_tbl)
			->where($db->qn($idField) . ' IN (' . $ids . ')')
			->where($db->qn($typeField) . ' = ' . $db->q($type));

		$realKeys = $db->setQuery($query)->loadColumn();

		if (empty($realKeys))
		{
			return true;
		}

		$db->transactionStart(true);

		if (!$this->delete($realKeys))
		{
			$db->transactionRollback(true);

			return false;
		}

		$db->transactionCommit(true);

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $realPk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($realPk = null)
	{
		$pk = $realPk;
		$db = Factory::getDbo();

		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}
		// Try the instance property value
		elseif (empty($pk) && $this->{$k})
		{
			$pk = $db->q($this->{$k});
		}

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$query  = $db->getQuery(true)
			->select('type, type_id')
			->from($db->qn($this->_tbl))
			->where('id IN (' . $pk . ')');
		$prices = $db->setQuery($query)
			->loadObjectList();

		if (!parent::delete($realPk))
		{
			return false;
		}

		$this->updateOffers($prices);

		return true;
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		if (empty($this->country_id))
		{
			$this->country_id = null;
		}

		if (empty($this->quantity_min) && empty($this->quantity_max))
		{
			$this->quantity_min = null;
			$this->quantity_max = null;
		}

		if ($this->price > 0)
		{
			if (!$this->checkDuplicates())
			{
				$this->setError(Text::_('COM_REDSHOPB_PRODUCT_PRICE_ERROR_NO_DUPLICATES_ALLOWED'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to insure there are no duplicate price records allowed
	 *
	 * @return boolean
	 */
	protected function checkDuplicates()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true);

		$query->select('id')
			->from($this->_tbl)
			->where('type_id = ' . $db->q($this->type_id))
			->where('type = ' . $db->q($this->type))
			->where('sales_type = ' . $db->q($this->sales_type))
			->where('sales_code = ' . $db->q($this->sales_code))
			->where('currency_id = ' . $db->q($this->currency_id))
			->where('price > 0');

		if ($this->id)
		{
			$query->where('id <> ' . (int) $this->id);
		}

		$quantityMin = 'quantity_min IS NULL';
		$quantityMax = 'quantity_max IS NULL';
		$countryId   = 'country_id IS NULL';

		if (!empty($this->quantity_min))
		{
			$quantityMin = 'quantity_min = ' . $db->q($this->quantity_min);
		}

		$query->where($quantityMin);

		if (!empty($this->quantity_max))
		{
			$quantityMax = 'quantity_max = ' . $db->q($this->quantity_max);
		}

		$query->where($quantityMax);

		if (!empty($this->country_id))
		{
			$countryId = 'country_id = ' . $db->q($this->country_id);
		}

		$query->where($countryId);

		$startingDate = $db->getNullDate();
		$endingDate   = $db->getNullDate();

		if (!empty($this->starting_date))
		{
			$startingDate = $this->starting_date;
		}

		if (!empty($this->ending_date))
		{
			$endingDate = $this->ending_date;
		}

		$query->where('starting_date = ' . $db->q($startingDate))
			->where('ending_date = ' . $db->q($endingDate));

		// Trigger the event
		$results = RFactory::getDispatcher()
			->trigger('onRedshopbCheckPriceDuplicates', array($this, &$query));

		if (count($results) && in_array(false, $results, true))
		{
			return false;
		}

		return (int) $db->setQuery($query, 0, 1)->loadResult() ? false : true;
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

		/** @todo remove this in next version as it we are storing both the product_id and product_type_id in the DB */

		switch ($this->type)
		{
			case 'product':
				$this->product_id = $this->type_id;
				break;

			case 'product_item':
				$this->product_item_id = $this->type_id;
				$productItem           = RedshopbTable::getAdminInstance('Product_Item');

				if ($productItem->load($this->type_id))
				{
					$this->product_id = $productItem->product_id;
				}
				break;
		}

		switch ($this->sales_type)
		{
			case 'customer_price_group':
				$this->customer_price_group_id = (int) $this->sales_code;
				break;

			case 'customer_price':
				$this->company_id = (int) $this->sales_code;
				break;

			case 'campaign':
				$this->campaign_code = $this->sales_code;
				break;
		}

		return true;
	}
}
