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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Categories Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTags extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Tags';

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

		// Filter by state.
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the tags.
		$items = $this->getTags($state);

		// Build the field options.
		if (!empty($items))
		{
			if ($this->multiple)
			{
				$options[] = HTMLHelper::_('select.optgroup', Text::_('JOPTION_SELECT_TAG'));
			}

			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
			}
		}

		return $options;
	}

	/**
	 * Method to get the tags list.
	 *
	 * @param   integer  $state  The state of the tag.
	 *
	 * @return  array  An array of shop names.
	 */
	protected function getTags($state = null)
	{
		$key = $state;

		if (!isset($this->cache[$key]) || empty($this->cache[$key]))
		{
			$db = Factory::getDbo();

			if (!isset($this->cache[$key]))
			{
				$this->cache[$key] = array();
			}

			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('a.id', 'value'),
						$db->qn('a.name', 'text'),
						$db->qn('a.level'),
						$db->qn('a.state')
					)
				)
				->from($db->qn('#__redshopb_tag', 'a'));

			// Filter by state.
			if (is_numeric($state))
			{
				$query->where('a.state = ' . $db->quote($state));
			}
			else
			{
				$query->where('a.state IN (0,1)');
			}

			if (!empty($this->element['tag_type']))
			{
				$query->where('a.type = ' . $db->quote((string) $this->element['tag_type']));
			}

			if ((string) $this->element['filterproducts'] == 'true')
			{
				$filteredList = RedshopbHelperShop::getFilteredProductIds();

				if (empty($filteredList))
				{
					$filteredList = '0';
				}

				$query->innerJoin($db->qn('#__redshopb_product_tag_xref', 'tcx') . ' ON tcx.tag_id = a.id')
					->where('tcx.product_id IN (' . $filteredList . ')')
					->group('a.id');
			}

			// Selects ACL/logic restriction depending on where the field is placed
			switch ((string) $this->element['restriction'])
			{
				case 'parents':
					$user = RedshopbHelperUser::getUser();

					if (!is_null($user) && !RedshopbHelperACL::isSuperAdmin())
					{
						// Shows default ACL view and parent companies' tags
						$query->where(
							'(a.company_id IN (' .
							RedshopbHelperACL::listAvailableCompaniesAndParents($user->id) . ')  OR ' .
							$db->qn('a.company_id') . ' IS NULL)'
						);
					}
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
							'(a.company_id IN (' . RedshopbHelperACL::listAvailableCompanies($user->id) . ') ' .
							(RedshopbHelperACL::getPermission('manage', 'mainwarehouse') ? ' OR ' . $db->qn('a.company_id') . ' IS NULL' : '') . ')'
						);
					}
			}

			// Avoiding root and disabled tags
			$query->where($db->qn('a.parent_id') . ' IS NOT NULL')
				->order($db->qn('a.lft'));

			// Get the options.
			$options = $db->setQuery($query)
				->loadObjectList();

			if (!$options)
			{
				$options = array();
			}

			if (!empty($options))
			{
				foreach ($options as $option)
				{
					if (!$option->state)
					{
						$option->text = $option->text . ' [' . Text::_('JUNPUBLISHED') . ']';
					}

					if (!empty($option->level))
					{
						$option->text = str_repeat('- ', $option->level - 1) . $option->text;
					}
				}
			}

			if (!$this->multiple)
			{
				$options = array_merge(parent::getOptions(), $options);
			}

			// Merge any additional options in the XML definition.
			$this->cache[$key] = $options;
		}

		return $this->cache[$key];
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
			return '<div id="redshopb-tags"></div><div id="redshopb-tags-loading">' .
					HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') . '</div>';
		}

		$options     = $this->getOptions();
		$restriction = (string) $this->element['restriction'];

		if ($restriction == 'company' || $restriction == 'parents')
		{
			if ($options)
			{
				return parent::getInput();
			}
			else
			{
				$input = '<input type="hidden" name="' . $this->name . '" value="" />';

				if ($restriction == 'company')
				{
					$input .= '<span class="help-block">' . Text::_('COM_REDSHOPB_TAG_COMPANY_NEEDED') . '</span>';
				}

				return $input;
			}
		}

		return parent::getInput();
	}
}
