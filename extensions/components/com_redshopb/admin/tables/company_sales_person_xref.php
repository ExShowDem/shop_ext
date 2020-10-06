<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * User Company Sales Person Xref table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCompany_Sales_Person_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_company_sales_person_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_key = array('user_id', 'company_id');

	/**
	 * @var  integer
	 */
	public $user_id;

	/**
	 * @var  integer
	 */
	public $company_id;

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		$xrefTable = RedshopbTable::getAdminInstance('Usergroup_Sales_Person_Xref');

		if (!$xrefTable->load(array('user_id' => $this->user_id)))
		{
			if (!$xrefTable->save(array('user_id' => $this->user_id)))
			{
				throw new Exception($xrefTable->getError());
			}
		}

		return parent::afterStore($updateNulls);
	}
}
