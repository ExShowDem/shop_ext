<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Field to select a user id from a modal list.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldRsbuser extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Rsbuser';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options       = array();
		$currentUserId = RedshopbHelperUser::getUserRSid();

		// Reviews if department filter is needed
		$departmentFilter   = $this->element['departmentfilter'] ? $this->element['departmentfilter'] : false;
		$fromChildCompanies = (isset($this->element['fromChildCompanies']) && $this->element['fromChildCompanies'] == 'true') ? true : false;

		// Get the companies
		$items = $this->getUsers(($departmentFilter ? $this->getDepartmentId() : 0), $fromChildCompanies);

		// Build the field options
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($item->identifier == $currentUserId)
				{
					$options[] = HTMLHelper::_('select.option', $item->identifier, Text::_('COM_REDSHOPB_ORDER_AS_MYSELF'));
				}
				else
				{
					$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of companies.
	 *
	 * @param   integer  $departmentId        (optional) filter by a department Id
	 * @param   bool     $fromChildCompanies  Exclude users in the same user company
	 *
	 * @return  array  An array of company names.
	 */
	protected function getUsers($departmentId = 0, $fromChildCompanies = false)
	{
		$key = (int) $departmentId . '_' . (int) $fromChildCompanies;

		if (!array_key_exists($key, $this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('ru.id as identifier')
				->select('ju.name as data')
				->from('#__redshopb_user AS ru')
				->leftJoin('#__users AS ju ON ju.id=ru.joomla_user_id')
				->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')

				// For proper query to get everything for acl filtering see users model
				->order('ju.name')
				->group('ru.id');

			// Filter by department Id if needed
			if ($departmentId)
			{
				$query->where('ru.department_id = ' . (int) $departmentId);
			}

			// If it's not a super admin, filters users by the available departments
			if (!RedshopbHelperACL::isSuperAdmin())
			{
				$permission           = $this->element['permission'];
				$companyPermission    = 'redshopb.company.view';
				$departmentPermission = 'redshopb.department.view';

				if ($permission == 'manage')
				{
					$companyPermission    = 'redshopb.company.manage';
					$departmentPermission = 'redshopb.department.manage';
				}

				$user = Factory::getUser();

				if (!$fromChildCompanies)
				{
					$companies   = RedshopbHelperACL::listAvailableCompaniesbyPermission($user->id, $companyPermission);
					$departments = RedshopbHelperACL::listAvailableDepartmentsbyPermission($user->id, $departmentPermission);
					$query->where(
						'(umc.company_id IN (' . $companies . ')'
						. ' OR ru.department_id IN (' . $departments . ')'
						. ' OR ju.id = ' . $user->id . ')'
					);
				}
				else
				{
					$userCompanyId = RedshopbHelperUser::getUserCompanyId();
					$companies     = RedshopbHelperACL::listAvailableCompanies(
						$user->id, 'comma', $userCompanyId, '', $companyPermission, '', false, false, true
					);
					$companies     = $companies ? $companies : 0;
					$query->where('umc.company_id IN (' . $companies . ')');
				}
			}

			$this->cache[$key] = $db->setQuery($query)
				->loadObjectList();
		}

		return $this->cache[$key];
	}

	/**
	 * Get the department id from the form
	 *
	 * @return  integer  The department id
	 */
	protected function getDepartmentId()
	{
		$input        = Factory::getApplication()->input;
		$form         = $input->get('jform', array(), 'array');
		$view         = $input->get('view');
		$departmentId = null;

		if (isset($form['department_id']))
		{
			$departmentId = (int) $form['department_id'];
		}

		return $departmentId;
	}
}
