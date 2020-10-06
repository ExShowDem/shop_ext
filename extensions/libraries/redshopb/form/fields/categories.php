<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

jimport('redshopb.helper.acl');
jimport('redshopb.helper.user');

/**
 * Categories Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCategories extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Categories';

	/**
	 * A static cache.
	 *
	 * @var array
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

		// Get the categories.
		$items = $this->getCategories();

		// Build the field options.
		if (!empty($items))
		{
			if ($this->multiple)
			{
				$options[] = HTMLHelper::_('select.optgroup', Text::_('JOPTION_SELECT_CATEGORY'));
			}

			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
			}
		}

		return $options;
	}

	/**
	 * Method to get the categories list.
	 *
	 * @return  array  An array of categories
	 */
	protected function getCategories()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('a.id', 'value'),
					$db->qn('a.name', 'text'),
					$db->qn('a.level', 'level'),
					$db->qn('a.state', 'state')
				)
			)
			->from($db->qn('#__redshopb_category', 'a'));

		if ((string) $this->element['filterproducts'] == 'true')
		{
			$filteredList = RedshopbHelperShop::getFilteredProductIds();

			if (empty($filteredList))
			{
				$filteredList = '0';
			}

			$query->leftJoin('#__redshopb_product_category_xref AS pc ON pc.category_id = a.id')
				->where('pc.product_id IN (' . $filteredList . ')')
				->group('a.id');
		}

		// Selects ACL/logic restriction depending on where the field is placed
		switch ((string) $this->element['restriction'])
		{
			case 'parents':
				$condition = $db->qn('a.company_id') . ' IS NULL';
				$companies = array();
				$user      = RedshopbHelperCommon::getUser();

				if (!$user->guest)
				{
					if (!RedshopbHelperACL::isSuperAdmin()
						|| RedshopbHelperACL::getPermissionInto('impersonate', 'order', 0, 'redshopb', $user->id))
					{
						$app          = Factory::getApplication();
						$customerType = $app->getUserState('shop.customer_type', 'employee');
						$customerId   = $app->getUserState('shop.customer_id', 0);
						$userCompany  = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);

						if ($userCompany)
						{
							$companies = RedshopbEntityCompany::getInstance($userCompany->id)->getTree(true, false);
							$companies = implode(',', $companies);
						}
					}
					else
					{
						$companies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
					}
				}
				elseif ($user->b2cMode)
				{
					$companies = $user->b2cCompany;
				}

				// Shows default ACL view and parent companies' tags
				if (!empty($companies))
				{
					$condition = '(a.company_id IN (' . $companies . ')  OR ' . $condition . ')';
				}

				$query->where($condition);

				break;

			case 'impersonate':
				$app          = Factory::getApplication();
				$customerType = $app->getUserState('shop.customer_type', 'employee');
				$companyId    = $app->getUserState('shop.company_id', 0);

				if ($customerType == 'company')
				{
					$companyId = $app->getUserState('shop.customer_id');
				}

				$allChildrenWithProducts = RedshopbHelperACL::listAvailableCategories(
					Factory::getUser()->id,
					false,
					100,
					$companyId,
					false,
					'comma',
					'',
					null,
					0,
					0,
					false,
					true
				);

				$query->where('a.id IN (' . $allChildrenProducts . ')');

				break;

			case 'company':
				// Shows only companyid (property) tags
				$companyId = (string) $this->element['companyid'];

				if ($companyId == '')
				{
					// If no company is selected, no options are given
					$query->where('0 = 1');
				}
				else
				{
					$query->where('a.company_id ' . ($companyId ? ' = ' . $companyId : ' IS NULL'));
				}
				break;

			default:
				$user = RedshopbHelperUser::getUser();

				if (!is_null($user) && !RedshopbHelperACL::isSuperAdmin())
				{
					// Shows default ACL viewable tags
					$query->where(
						'(a.company_id IN (' . RedshopbHelperACL::listAvailableCompanies($user->joomla_user_id) . ') ' .
							(RedshopbHelperACL::getPermission('manage', 'mainwarehouse') ? ' OR ' . $db->qn('a.company_id') . ' IS NULL' : '') . ')'
					);
				}
		}

		// Levels of categories to include
		$levels = (int) $this->element['levels'];

		if ($levels)
		{
			$query->where($db->qn('a.level') . ' <= ' . $levels);
		}

		// Avoiding root and disabled categories
		$query->where($db->qn('a.parent_id') . ' IS NOT NULL')
			->where($db->qn('a.state') . ' IN (0, 1)')
			->order($db->qn('a.lft'));

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Pad the option text with spaces using depth level as a multiplier.
		$count = count($options);

		for ($i = 0; $i < $count; $i++)
		{
			if (!$options[$i]->state)
			{
				$options[$i]->text = $options[$i]->text . ' [' . Text::_('JUNPUBLISHED') . ']';
			}

			if ($options[$i]->level)
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level - 1) . $options[$i]->text;
			}
		}

		if (!$this->multiple)
		{
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if ((string) $this->element['emptystart'] == 'true')
		{
			return '<div id="redshopb-categories-' . (string) $this->element['name'] . '"></div>' .
				'<div id="redshopb-categories-' . (string) $this->element['name'] . '-loading">' .
				HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') . '</div>';
		}

		return parent::getInput();
	}
}
