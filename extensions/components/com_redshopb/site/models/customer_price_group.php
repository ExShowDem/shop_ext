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

/**
 * Customer Price Group Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCustomer_Price_Group extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * unpublish a customer price group
	 *
	 * @param   integer  $id  The product id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function unpublish($id)
	{
		$customerPriceTable = $this->getTable();

		if (!$customerPriceTable->load($id))
		{
			return false;
		}

		$db = Factory::getDbo();

		$fields = array(
			$db->quoteName('state') . ' = 0'
		);

		$conditions = array(
			$db->quoteName('id') . ' = ' . (int) $id

		);

		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__redshopb_customer_price_group'))->set($fields)->where($conditions);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	/**
	 * add a company to customer group
	 *
	 * @param   integer  $priceGroupId  The price group id
	 * @param   integer  $companyId     The company id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function memberCompanyAdd($priceGroupId, $companyId)
	{
		$xrefTable = RedshopbTable::getAdminInstance('Customer_Price_Group_Xref', 'RedshopbTable');

		if (!$xrefTable->load(
			array(
				'customer_id' => $companyId,
				'price_group_id' => $priceGroupId
			)
		))
		{
			if (!$xrefTable->save(
				array(
					'customer_id' => $companyId,
					'price_group_id' => $priceGroupId

				)
			))
			{
				return false;
			}

			return true;
		}
	}

	/**
	 * remove a company of a  customer group
	 *
	 * @param   integer  $priceGroupId  The price group id
	 * @param   integer  $companyId     The company id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function memberCompanyRemove($priceGroupId, $companyId)
	{
		$xrefTable = RedshopbTable::getAdminInstance('Customer_Price_Group_Xref', 'RedshopbTable');

		if (!$xrefTable->load(
			array(
				'customer_id' => $companyId,
				'price_group_id' => $priceGroupId
			)
		))
		{
			return false;
		}

		$db = Factory::getDbo();

		$conditions = array(
			$db->quoteName('customer_id') . ' = ' . (int) $companyId,
			$db->quoteName('price_group_id') . ' = ' . (int) $priceGroupId

		);

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__redshopb_customer_price_group_xref'))->where($conditions);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return false;
	}
}
