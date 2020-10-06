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
/**
 * Stockroom Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroom extends RedshopbModelAdmin
{
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
		$form = parent::getForm(array(), true);
		$app  = Factory::getApplication();

		if (!$form->getValue('company_id'))
		{
			$form->setValue('company_id', null, $app->getUserState('stockroom.company_id', ''));
		}

		return $form;
	}

	/**
	 * Method for save amount of an product item for specific stockroom
	 *
	 * @param   int      $stockroomId    ID of stockroom
	 * @param   float    $amount         Amount of stock
	 * @param   int      $productItemId  Product item ID
	 * @param   boolean  $unlimited      1 is Unlimited. 0 is limit.
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function saveProductItemAmount($stockroomId, $amount, $productItemId, $unlimited = false)
	{
		$productItemStockroom                  = RedshopbTable::getAutoInstance('Stockroom_Product_Item_Xref');
		$productItemStockroom->stockroom_id    = (int) $stockroomId;
		$productItemStockroom->amount          = (float) $amount;
		$productItemStockroom->product_item_id = (int) $productItemId;
		$productItemStockroom->unlimited       = (boolean) $unlimited;

		if (!$productItemStockroom->check())
		{
			return false;
		}

		return $productItemStockroom->store();
	}

	/**
	 * Method for save amount of an product for specific stockroom
	 *
	 * @param   int      $stockroomId  ID of stockroom
	 * @param   float    $amount       Amount of stock
	 * @param   int      $productId    Product ID
	 * @param   boolean  $unlimited    1 is Unlimited. 0 is limit.
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function saveProductAmount($stockroomId, $amount, $productId, $unlimited = false)
	{
		$productStockroom               = RedshopbTable::getAutoInstance('Stockroom_Product_Xref');
		$productStockroom->stockroom_id = (int) $stockroomId;
		$productStockroom->amount       = (float) $amount;
		$productStockroom->product_id   = (int) $productId;
		$productStockroom->unlimited    = (boolean) $unlimited;

		if (!$productStockroom->check())
		{
			return false;
		}

		return $productStockroom->store();
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateWS($data)
	{
		// Sets the right address fields
		if (isset($data['address_line1']))
		{
			$data['address'] = $data['address_line1'];
		}

		if (isset($data['address_line2']))
		{
			$data['address2'] = $data['address_line2'];
		}

		if (isset($data['address_name1']))
		{
			$data['address_name'] = $data['address_name1'];
		}

		return parent::validateWS($data);
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateUpdateWS($data)
	{
		// If some of the manually updated fields is not sent, it brings it from the item itself to avoid validation errors
		$item = $this->getItemFromWSData($data['id']);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["id"]), 'error');

			return false;
		}

		if (!isset($data['address_line1']) || $data['address_line1'] == '')
		{
			$data['address_line1'] = $item->address;
		}

		if (!isset($data['address_line2']) || $data['address_line2'] == '')
		{
			$data['address_line2'] = $item->address2;
		}

		if (!isset($data['address_name1']) || $data['address_name1'] == '')
		{
			$data['address_name1'] = $item->address_name;
		}

		return parent::validateUpdateWS($data);
	}
}
