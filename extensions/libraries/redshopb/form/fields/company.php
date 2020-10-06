<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
jimport('redshopb.helper.acl');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

FormHelper::loadFieldClass('rlist');

/**
 * Company Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCompany extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Company';

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
		$options = array();

		// Sets an empty (default) option if it's not required
		if (($this->element['required'] != 'true')
			&& (!isset($this->element['hideDefault']) || $this->element['hideDefault'] == 'false'))
		{
			$nullOption = !empty($this->element['showNone']) && $this->element['showNone'] == 'yes' ?
				Text::_('JNONE') : (RedshopbHelperACL::getPermission('manage', 'mainwarehouse') ? Text::_('COM_REDSHOPB_MAIN_WAREHOUSE') : null);

			if ($nullOption !== null)
			{
				$nullValue = ((string) $this->element['nullValue'] != '' ? (string) $this->element['nullValue'] : '');
				$options[] = HTMLHelper::_('select.option', $nullValue, $nullOption);
			}
		}

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// ACL
		$acl = $this->element['acl'] ? (int) $this->element['acl'] : false;

		// Get the companies
		$items = ($acl == true) ? RedshopbHelperACL::listAvailableCompanies(null, 'objectList') : $this->getCompanies($state);

		// Build the field options
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if (!$item->state)
				{
					$item->data = $item->data . ' [' . Text::_('JUNPUBLISHED') . ']';
				}

				$customerNumber = '(' . $item->customer_number . ') ';
				$options[]      = HTMLHelper::_('select.option', $item->identifier, str_repeat('- ', $item->level - 1) . $customerNumber . $item->data);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of companies.
	 *
	 * @param   integer  $state  The companies state
	 *
	 * @return  array  An array of company names.
	 */
	protected function getCompanies($state = null)
	{
		if (empty($this->cache))
		{
			$permission        = $this->element['permission'];
			$companyPermission = 'redshopb.company.view';
			$level             = 0;
			$levelSlab         = 0;

			if ($permission == 'manage')
			{
				$companyPermission = 'redshopb.company.manage';
			}

			if (isset($this->element['levelslab']))
			{
				$levelSlab = (int) $this->element['levelslab'];
			}

			if ($levelSlab > 0)
			{
				$level = $levelSlab;
			}

			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(array($db->qn('a.level'), $db->qn('a.state'), $db->qn('a.customer_number')))
				->select('IF(a.name2 IS NULL OR a.name2 = ' . $db->q('') . ', a.name, CONCAT_WS(' . $db->q(' ') . ', a.name, a.name2)) AS data');

			if (isset($this->element['sync_reference']))
			{
				$query->select($db->qn('s.remote_key', 'identifier'))
					->from(
						$db->qn('#__redshopb_company', 'a') . ' INNER JOIN ' . $db->qn('#__redshopb_sync', 's')
						. ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('s.local_id')
						. ' AND ' . $db->qn('s.reference') . ' = ' . $db->q((string) $this->element['sync_reference'])
					);
			}
			else
			{
				$query->select($db->qn('a.id', 'identifier'))
					->from($db->qn('#__redshopb_company', 'a'));
			}

			$query->from($db->qn('#__redshopb_company', 'b'))
				->where('a.lft BETWEEN b.lft AND b.rgt')
				->where($db->qn('a.deleted') . ' = 0')
				->where($db->qn('b.deleted') . ' = 0')
				->where('a.level > ' . $level)
				->group('a.id')
				->order('a.lft ASC');

			// Selects ACL/logic restriction depending on where the field is placed
			switch ((string) $this->element['restriction'])
			{
				case 'company':
					// Shows only child companies for the selected company (according to ACL permissions of the selected company admin)
					$user      = Factory::getUser();
					$companyId = (int) $this->element['companyid'];
					$query->where('a.id IN (' . RedshopbHelperACL::listAvailableChildCompanies($user->id, $companyId, $companyPermission) . ')');
					break;

				default:
					// Check for available companies for this user if not a system admin of the app
					if (!RedshopbHelperACL::isSuperAdmin())
					{
						$user = Factory::getUser();
						$query->where('a.id IN (' . RedshopbHelperACL::listAvailableCompaniesbyPermission($user->id, $companyPermission) . ')');
					}
			}

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('a.state = ' . $db->quote($state));
			}
			else
			{
				$query->where('a.state IN (0,1)');
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multi select.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if ((string) $this->element['emptystart'] == 'true')
		{
			return '<div id="redshopb-companies"></div><div id="redshopb-companies-loading">' .
				HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') . '</div>';
		}

		if ((string) $this->element['restriction'] == 'company')
		{
			$options = $this->getOptions();

			if (!empty($options))
			{
				return parent::getInput();
			}
			else
			{
				return '<input type="hidden" name="' . $this->name . '" value="" /><span class="help-block">' .
					Text::_('COM_REDSHOPB_COMPANY_COMPANY_NEEDED') . '</span>';
			}
		}

		if (isset($this->element['department_fieldname']) && isset($this->element['department_fieldid']))
		{
			$url = Factory::getApplication()->isClient('administrator') ? 'index.php?option=com_redshopb&task=acl.ajaxGetDepartments'
				: Uri::root() . 'index.php?option=com_redshopb&task=user.ajaxGetDepartments';

			$jsScript = 'function jOnCompanySet(company)
				{
					var departmentWarper = jQuery("#' . $this->element['department_fieldid'] . '").parent();

					jQuery.ajax({
						type: "POST",
						url: "' . $url . '",
						data: {
							fieldName : "' . $this->element['department_fieldname'] . '",
							fieldId : "' . $this->element['department_fieldid'] . '",
							companyId : company.val(),
							userId : ' . Factory::getUser()->id . ',
							"' . Session::getFormToken() . '" : 1
						},
						beforeSend: function(xhr)
						{
							departmentWarper.empty();
						}
					}).done(function(data)
					{
						departmentWarper.html(data).ready(function () {
							jQuery("#' . $this->element['department_fieldid'] . '").chosen({
								disable_search_threshold : 10,
								allow_single_deselect : true
							});
						}).trigger("liszt:updated");
					});
				}';

			Factory::getDocument()->addScriptDeclaration($jsScript);
		}

		return parent::getInput();
	}
}
