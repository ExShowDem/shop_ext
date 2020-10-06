<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Usergroup;
use Joomla\CMS\Language\Text;

/**
 * User Multi Company Reference table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6.65
 */
class RedshopbTableUser_Multi_Company extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_user_multi_company';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $user_id;

	/**
	 * @var  integer
	 */
	public $company_id;

	/**
	 * @var  integer
	 */
	public $role_id;

	/**
	 * @var  integer
	 */
	public $main;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.user_multi_company'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'user_id' => array(
			'model' => 'Users'
		),
		'company_id' => array(
			'model' => 'Companies'
		),
		'role_id' => array(
			'model' => 'Roles'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array(
		'state'
	);

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$this->user_id    = (int) $this->user_id;
		$this->company_id = (int) $this->company_id;
		$this->role_id    = (int) $this->role_id;

		if (!$this->user_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_USER_MULTI_COMPANY_ERROR_MISSING_USER_ID'));

			return false;
		}

		if (!$this->company_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_USER_MULTI_COMPANY_ERROR_MISSING_COMPANY_ID'));

			return false;
		}

		if (!$this->role_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_USER_MULTI_COMPANY_ERROR_MISSING_ROLE_ID'));

			return false;
		}

		// We are assuming there can be only one record with the same user/company combo
		$xrefId = $this->getXrefId($this->user_id, $this->company_id);

		if (empty($this->id) && $xrefId)
		{
			$this->id = $xrefId;
		}

		return true;
	}

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		if (!parent::afterStore($updateNulls))
		{
			return false;
		}

		$user  = RedshopbEntityUser::getInstance($this->user_id)->loadItem();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Get user Group Id if it is not already set for the user
		$query->select(
			array (
				$db->qn('r.joomla_group_id')
			)
		)
			->from($db->qn('#__redshopb_role', 'r'))
			->leftJoin(
				$db->qn('#__user_usergroup_map', 'ugm') . ' ON ' . $db->qn('ugm.group_id') . ' = ' . $db->qn('r.joomla_group_id')
				. ' AND ' . $db->qn('ugm.user_id') . ' = ' . (int) $user->get('joomla_user_id')
			)
			->where($db->qn('ugm.group_id') . ' IS NULL')
			->where($db->qn('r.company_id') . ' = ' . (int) $this->company_id)
			->where($db->qn('r.role_type_id') . ' = ' . (int) $this->role_id);

		$b2bUsergroupId = $db->setQuery($query)->loadObject();

		if ($b2bUsergroupId)
		{
			$query = $db->getQuery(true);
			$query->insert($db->qn('#__user_usergroup_map'))
				->set($db->qn('user_id') . ' = ' . (int) $user->get('joomla_user_id'))
				->set($db->qn('group_id') . ' = ' . (int) $b2bUsergroupId->joomla_group_id);
			$result = $db->setQuery($query)->execute();

			return (bool) $result;
		}

		$this->deleteUnusedCompanyRoleUserGroup($this->company_id);

		return true;
	}

	/**
	 * Called before delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeDelete($pk = null)
	{
		if (!parent::beforeDelete($pk))
		{
			return false;
		}

		// We must load item so we can use it later on after load function
		$this->load($pk);

		return true;
	}

	/**
	 * Called after delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterDelete($pk = null)
	{
		if (!parent::afterDelete($pk))
		{
			return false;
		}

		$user  = RedshopbEntityUser::getInstance($this->user_id)->loadItem();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Get user Group Id if it is not already set for the user
		$query->select(
			array (
				$db->qn('r.joomla_group_id')
			)
		)
			->from($db->qn('#__redshopb_role', 'r'))
			->where($db->qn('r.company_id') . ' = ' . (int) $this->company_id)
			->where($db->qn('r.role_type_id') . ' = ' . (int) $this->role_id);

		$b2bUsergroupId = $db->setQuery($query)->loadObject();

		if ($b2bUsergroupId)
		{
			$query = $db->getQuery(true);
			$query->delete($db->qn('#__user_usergroup_map'))
				->where($db->qn('user_id') . ' = ' . (int) $user->get('joomla_user_id'))
				->where($db->qn('group_id') . ' = ' . (int) $b2bUsergroupId->joomla_group_id);
			$result = $db->setQuery($query)->execute();

			return (bool) $result;
		}

		$this->deleteUnusedCompanyRoleUserGroup($this->company_id);

		return true;
	}

	/**
	 * Method to return the xref ID from the user_id and company_id
	 *
	 * @param   int  $userId     Primary key of the user
	 * @param   int  $companyId  Primary key of the company
	 *
	 * @return mixed  id or null if one does not exist
	 */
	private function getXrefId($userId, $companyId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->qn($this->_tbl))
			->where($db->qn('user_id') . ' = ' . (int) $userId)
			->where($db->qn('company_id') . ' = ' . (int) $companyId);
		$result = $db->setQuery($query)->loadResult();

		return $result;
	}

	/**
	 * Deletes unused company roles and usergroups
	 *
	 * @param   int  $companyId  Company Id
	 *
	 * @return  void
	 */
	protected function deleteUnusedCompanyRoleUserGroup($companyId)
	{
		// Delete role user group without any user in it
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('r.*')
			->from($db->qn('#__redshopb_role', 'r'))
			->leftJoin(
				$db->qn('#__redshopb_user_multi_company', 'umc')
				. ' ON umc.company_id = r.company_id AND umc.role_id = r.role_type_id'
			)
			->where($db->qn('umc.user_id') . ' IS NULL')
			->where($db->qn('r.company_id') . ' = ' . (int) $companyId);

		$rolesWithoutUsers = $db->setQuery($query)->loadObjectList();

		if ($rolesWithoutUsers)
		{
			foreach ($rolesWithoutUsers as $role)
			{
				/** @var Usergroup $groupTable */
				$groupTable = Table::getInstance('Usergroup');

				if ($role->joomla_group_id)
				{
					if ($groupTable->load($role->joomla_group_id, true))
					{
						$groupTable->delete($role->joomla_group_id);
					}
				}
			}

			// Rebuilds ACL for the company
			RedshopbEntityCompany::clearInstance($companyId);
			RedshopbHelperACL::rebuildCompanyACL($companyId);
		}
	}
}
