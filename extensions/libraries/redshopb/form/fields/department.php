<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Department Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldDepartment extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Department';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $usersDepartment = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		if (isset($this->element['disableCompanyFilter']) && $this->element['disableCompanyFilter'] == 'true')
		{
			$companyId = null;
		}
		else
		{
			$companyId = $this->getCompanyId();
		}

		$userId = $this->getClientUserId();

		$source = $this->element['source'] ? $this->element['source'] : 'company';

		if ($source == 'user')
		{
			$items = $this->getDepartmentsByUser($userId, $companyId);
		}
		elseif ($source == 'fromChildCompanies')
		{
			$items = $this->getDepartments($companyId, true);
		}
		else
		{
			$items = $this->getDepartments($companyId);
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if (!$item->state)
				{
					$item->data = $item->data . ' [' . Text::_('JUNPUBLISHED') . ']';
				}

				$options[] = HTMLHelper::_('select.option', $item->identifier, str_repeat('- ', $item->level - 1) . $item->data);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of departments.
	 *
	 * @param   integer  $companyId           The related company id
	 * @param   bool     $fromChildCompanies  Exclude departments in the same user company
	 *
	 * @return  array  An array of department names.
	 */
	protected function getDepartments($companyId = null, $fromChildCompanies = false)
	{
		$key = (int) $companyId . '_' . (int) $fromChildCompanies;

		if (!array_key_exists($key, $this->cache))
		{
			$permission              = $this->element['permission'];
			$departmentPermission    = 'redshopb.department.view';
			$companyPermission       = 'redshopb.company.view';
			$onlyFromSelectedCompany = isset($this->element['onlyFromSelectedCompany']) && $this->element['onlyFromSelectedCompany'] == 'true';

			if ($permission == 'manage')
			{
				$departmentPermission = 'redshopb.department.manage';
				$companyPermission    = 'redshopb.company.manage';
			}

			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array (
						$db->qn('d.id', 'identifier'),
						'CONCAT(' . $db->qn('d.name') . ', ' . $db->quote(' (') . ',' . $db->qn('c.name') . ',' . $db->quote(')') . ') as data',
						$db->qn('d.level', 'level'),
						$db->qn('d.state', 'state')
					)
				)
				->from($db->qn('#__redshopb_department', 'd'))
				->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = d.company_id AND ' . $db->qn('c.deleted') . ' = 0')
				->where($db->qn('d.id') . ' > 1')
				->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' IN (0,1)')
				->order($db->qn('d.lft'));

			// Check for available departments for this user if not a system admin of the app
			if (!RedshopbHelperACL::isSuperAdmin())
			{
				$user                 = Factory::getUser();
				$availableDepartments = RedshopbHelperACL::listAvailableDepartmentsbyPermission($user->id, $departmentPermission);

				$query->where($db->qn('d.id') . ' IN (' . $availableDepartments . ')');
			}

			if ($fromChildCompanies)
			{
				$user          = Factory::getUser();
				$userCompanyId = RedshopbHelperUser::getUserCompanyId();
				$companies     = RedshopbHelperACL::listAvailableCompanies(
					$user->id, 'comma', $userCompanyId, '', $companyPermission, '', false, false, true
				);
				$companies     = $companies ? $companies : 0;
				$query->where('d.company_id IN (' . $companies . ')');
			}
			else
			{
				if (empty($companyId))
				{
					$companyId = (int) $this->form->getValue('company_id');
				}

				if ($companyId)
				{
					if ($onlyFromSelectedCompany)
					{
						$query->where('d.company_id = ' . (int) $companyId);
					}
					else
					{
						$companies = RedshopbEntityCompany::getInstance($companyId)->getTree();
						$query->where('d.company_id IN (' . (implode(',', $companies)) . ')');
					}
				}
			}

			$this->cache[$key] = $db->setQuery($query)
				->loadObjectList();
		}

		return $this->cache[$key];
	}

	/**
	 * Method to get the list of departments.
	 *
	 * @param   integer  $userId     The user id
	 * @param   integer  $companyId  Company Id to filter from
	 *
	 * @return  array  An array of department names.
	 */
	protected function getDepartmentsByUser($userId = null, $companyId = null)
	{
		if (!isset($this->usersDepartment[$userId]))
		{
			$this->usersDepartment[$userId] = array();
			$departments                    = RedshopbHelperDepartment::getDepartmentsByUser($userId, $companyId);

			if (is_array($departments))
			{
				$userDepartments = array();

				foreach ($departments as $department)
				{
					$userDepartment             = new stdClass;
					$userDepartment->identifier = $department->id;
					$userDepartment->data       = $department->name;
					$userDepartment->level      = $department->level;
					$userDepartment->state      = $department->state;

					$userDepartments[] = $userDepartment;
				}

				$this->usersDepartment[$userId] = $userDepartments;
			}
		}

		return $this->usersDepartment[$userId];
	}

	/**
	 * Get the company id.
	 *
	 * @return  integer  The company id
	 */
	protected function getCompanyId()
	{
		$input     = Factory::getApplication()->input;
		$form      = $input->get('jform', array(), 'array');
		$view      = $input->get('view');
		$companyId = null;

		switch ($view)
		{
			case 'users':
				$usersModel = RModel::getFrontInstance('users');
				$company    = $usersModel->getState('filter.company');

				if (isset($company))
				{
					$companyId = (int) $company;
				}

				break;
			case 'user':
				$userModel = RModel::getFrontInstance('user');
				$user      = $userModel->getItem();

				if (isset($user->company_id))
				{
					$companyId = (int) $user->company_id;
				}
				break;
			case 'addresses':
				$addressesModel = RModel::getFrontInstance('addresses');
				$company        = $addressesModel->getState('filter.company_id');

				if (isset($company))
				{
					$companyId = (int) $company;
				}

				break;
			case 'company':
				$company = $input->get('id');

				if (isset($company))
				{
					$companyId = (int) $company;
				}

				break;
			default :
				if (isset($form['company_id']))
				{
					$companyId = (int) $form['company_id'];
				}
		}

		return $companyId;
	}

	/**
	 * Get the company id.
	 *
	 * @return  integer  The company id
	 */
	protected function getClientUserId()
	{
		$input  = Factory::getApplication()->input;
		$form   = $input->get('jform', array(), 'array');
		$userId = null;

		if (isset($form['customer_id']))
		{
			$userId = (int) $form['customer_id'];
		}
		elseif ($input->getString('view') == 'order' && $input->get('id', null, 'int') > 0)
		{
			$id = $input->get('id', null, 'INT');
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('ro.customer_id')
				->from('#__redshopb_order AS ro')
				->where('ro.id = ' . (int) $id);

			$db->setQuery($query);
			$result = $db->loadObject();

			$userId = $result->customer_id;
		}
		else
		{
			$userId = Factory::getApplication()->getUserStateFromRequest("com_redshopb.customer_id", 'customer_id', $userId);
		}

		return $userId;
	}
}
