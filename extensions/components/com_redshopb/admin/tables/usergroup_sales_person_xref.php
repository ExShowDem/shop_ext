<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\User\UserHelper;

jimport('joomla.database.usergroup');

/**
 * User Company Sales Person Xref table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableUsergroup_Sales_Person_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_usergroup_sales_person_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_key = array('user_id', 'joomla_group_id');

	/**
	 * @var  integer
	 */
	public $user_id;

	/**
	 * @var  integer
	 */
	public $joomla_group_id;

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		if ($this->joomla_group_id === null)
		{
			/** @var RedshopbTableUsergroup $groupTable */
			$groupTable     = RedshopbTable::getAdminInstance('Usergroup');
			$groupTable->id = null;

			$user      = RedshopbHelperUser::getUser($this->user_id);
			$companyId = RedshopbHelperUser::getUserCompanyId($this->user_id);
			$groupId   = RedshopbHelperRole::getJoomlaGroupId($companyId, null, 'sales');

			if (!$user || !$companyId || !$groupId)
			{
				return false;
			}

			if (!$groupTable->save(
				array(
					'title' => 'Sales Person: ' . $user->name . ' ' . $user->name2 . '(' . $this->user_id . ')',
					'parent_id' => $groupId
				)
			))
			{
				throw new Exception($groupTable->getError());
			}

			$this->joomla_group_id = $groupTable->id;

			if (!UserHelper::addUserToGroup($user->joomla_user_id, $this->joomla_group_id))
			{
				return false;
			}
		}

		return parent::beforeStore($updateNulls);
	}
}
