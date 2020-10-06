<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
/**
 * Product Item Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Item extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Discontinue one or more products.
	 *
	 * @param   array  $pks  A list of the primary keys to change.
	 *
	 * @return  boolean  True on success.
	 */
	public function discontinue(array $pks)
	{
		/** @var RedshopbTableProduct_Item $table */
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if (!$table->load($pk))
			{
				continue;
			}

			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');

				return false;
			}
		}

		// Attempt to discontinue the product.
		if (!$table->discontinue($pks))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	// @ToDo: Remove this function
	/**
	 * Get roles users companies
	 *
	 * @return mixed
	 */
	public function getRoles()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_role_type'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	// @ToDo: Remove this function
	/**
	 * Get currencies values
	 *
	 * @return mixed
	 */
	public function getCurrencies()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('id', 'symbol'))
			->from($db->qn('#__redshopb_currency'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get the SKU of a product item
	 *
	 * @param   int  $id  Product Item Id
	 *
	 * @return mixed
	 */
	public function getSKU($id)
	{
		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->where('piavx.product_item_id = pi.id')
			->order('pav.ordering');

		$query = $db->getQuery(true)
			->select(array('CONCAT_WS(' . $db->q('-') . ', p.sku, (' . $subQuery . ')) AS sku'))
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.product_id = p.id')
			->where('pi.id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return	array
	 */
	public function getItem($pk = null)
	{
		$productItem = parent::getItem($pk);

		if (!$productItem->id)
		{
			return $productItem;
		}

		// Get price for this product item
		$productItem->price        = 0.0;
		$productItem->retail_price = 0.0;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('pp.price', 'pp.retail_price')))
			->from($db->qn('#__redshopb_product_price', 'pp'))
			->leftJoin(
				$db->qn('#__redshopb_product_item', 'pi') . ' ON ((' . $db->qn('pp.type_id') . ' =
				' . $db->qn('pi.id') . ') AND (' . $db->qn('pp.type') . ' = ' . $db->quote('product_item') . '))'
			)
			->where($db->qn('pi.id') . ' = ' . $productItem->id);
		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			$productItem->price        = (float) $result->price;
			$productItem->retail_price = (float) $result->retail_price;
		}

		// Get Product Attribute value ids array
		$query->clear()
			->select($db->qn('product_attribute_value_id'))
			->from($db->qn('#__redshopb_product_item_attribute_value_xref'))
			->where($db->qn('product_item_id') . ' = ' . $productItem->id);
		$db->setQuery($query);
		$productItem->product_attribute_value_ids = $db->loadColumn();

		return $productItem;
	}

	/**
	 * save a product item
	 *
	 * @param   integer  $dataArray  array of form data
	 *
	 * @return  integer       Product item id
	 */
	public function saveProductItem($dataArray = array())
	{
		$productItemTable = $this->getTable();

		$productItemId = parent::createWS($dataArray);

		$produtAttrValXreftable = RTable::getAdminInstance('Product_Item_Attribute_Value_Xref');

		foreach ($dataArray['product_attribute_value_ids'] as $attributeId)
		{
			$produtAttrValXreftable->product_item_id            = $productItemId;
			$produtAttrValXreftable->product_attribute_value_id = $attributeId;
			$produtAttrValXreftable->store();
		}

		if ($dataArray['price'] != '' && $dataArray['price'] > 0)
		{
			if (!$this->setPrice($productItemId, $dataArray['price']))
			{
				return false;
			}
		}

		if ($dataArray['retail_price'] != '' && $dataArray['retail_price'] > 0)
		{
			if (!$this->setRetailPrice($productItemId, $dataArray['retail_price']))
			{
				return false;
			}
		}

		return $productItemId;
	}

	/**
	 * unpublish a product item
	 *
	 * @param   integer  $id  The product item id
	 *
	 * @return  integer       Product item id
	 */
	public function unpublish($id)
	{
		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		$table->id    = $id;
		$table->state = 0;

		if (!$table->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for set price for an product item.
	 *
	 * @param   int    $productItemId  Id of product item
	 * @param   float  $price          Price value
	 *
	 * @return  boolean               True on success. False otherwise.
	 */
	public function setPrice($productItemId, $price)
	{
		$productItemTable = RedshopbTable::getAdminInstance('Product_Item');

		// Check if product item exist
		if (!$productItemTable->load($productItemId))
		{
			return false;
		}

		$productPriceTable = RedshopbTable::getAdminInstance('Product_Price');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('p.id'))
			->from($db->qn('#__redshopb_product_price', 'p'))
			->where($db->qn('p.type') . ' = ' . $db->quote('product_item'))
			->where($db->qn('p.sales_type') . ' = ' . $db->quote('all_customers'))
			->where($db->qn('p.type_id') . ' = ' . $productItemTable->id);
		$db->setQuery($query);
		$result = $db->loadObject();

		// If this product item has price, load this price. If not, insert new price for this product item.
		if ($result)
		{
			$productPriceTable->load($result->id);
		}
		else
		{
			$productPriceTable->id         = null;
			$productPriceTable->type_id    = $productItemId;
			$productPriceTable->type       = 'product_item';
			$productPriceTable->sales_type = 'all_customers';
		}

		$productPriceTable->price = $price;

		return $productPriceTable->store();
	}

	/**
	 * Method for set retail price for an product item.
	 *
	 * @param   int    $productItemId  Id of product item
	 * @param   float  $retailPrice    Price value
	 *
	 * @return  boolean               True on success. False otherwise.
	 */
	public function setRetailPrice($productItemId, $retailPrice)
	{
		$productItemTable = RedshopbTable::getAdminInstance('Product_Item');

		// Check if product item exist
		if (!$productItemTable->load($productItemId))
		{
			return false;
		}

		$productPriceTable = RedshopbTable::getAdminInstance('Product_Price');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('p.id'))
			->from($db->qn('#__redshopb_product_price', 'p'))
			->where($db->qn('p.type') . ' = ' . $db->quote('product_item'))
			->where($db->qn('p.sales_type') . ' = ' . $db->quote('all_customers'))
			->where($db->qn('p.type_id') . ' = ' . $productItemTable->id);
		$db->setQuery($query);
		$result = $db->loadObject();

		// If this product item has price, load this price. If not, insert new price for this product item.
		if ($result)
		{
			$productPriceTable->load($result->id);
		}
		else
		{
			$productPriceTable->id         = null;
			$productPriceTable->type_id    = $productItemId;
			$productPriceTable->type       = 'product_item';
			$productPriceTable->sales_type = 'all_customers';
		}

		$productPriceTable->retail_price = $retailPrice;

		return $productPriceTable->store();
	}
}
