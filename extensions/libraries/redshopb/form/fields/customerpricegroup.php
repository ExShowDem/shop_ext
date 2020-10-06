<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Customer Price Group Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCustomerPriceGroup extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CustomerPriceGroup';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		if (is_object($this->value))
		{
			$this->value = (array) $this->value;
		}

		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->required ? ' required="required" aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			if (!empty($this->value))
			{
				$text = '';

				foreach ($this->value AS $priceGroupId)
				{
					$text  .= RedshopbHelperCompany::getPriceGroupName($priceGroupId) . "\n";
					$html[] = '<input type="hidden" name="' . $this->name . '[]" value="' . $priceGroupId . '" id="' . $this->id . '"/>';
				}

				$html[] = '<textarea ' . trim($attr) . ' >' . $text . '</textarea>';
			}
			else
			{
				$text   = current($options);
				$html[] = '<input type="text" value="' . $text->text . '" ' . trim($attr) . ' />';
				$html[] = '<input type="hidden" name="' . $this->name . '[]" value="" id="' . $this->id . '"/>';
			}
		}

		// Create a regular list.
		else
		{
			$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the Customer Price Groups.
		$items = $this->getCustomerPriceGroup($state);

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of Customer Price Group.
	 *
	 * @param   integer  $state  The product state
	 *
	 * @return  array  An array of product names.
	 */
	protected function getCustomerPriceGroup($state = null)
	{
		if (!empty($this->cache))
		{
			return $this->cache;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(array($db->qn('cpg.id', 'identifier'), $db->qn('cpg.name', 'data')))
			->from($db->qn('#__redshopb_customer_price_group', 'cpg'))
			->order('cpg.name');

		// Filter by state
		if (is_numeric($state))
		{
			$query->where('cpg.state = ' . $db->quote($state));
		}

		else
		{
			$query->where('cpg.state IN (0,1)');
		}

		// Check for available companies for this user
		$parentId  = $this->form->getData()->get('parent_id', 0);
		$companies = RedshopbEntityCompany::getInstance($parentId)->getTree(true, true);
		$parts     = array();

		if (!empty($companies))
		{
			$parts[] = $db->qn('cpg.company_id') . ' IN (' . implode(',', $companies) . ')';
		}

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse') && !RedshopbHelperACL::isSuperAdmin())
		{
			$parts[] = $db->qn('cpg.company_id') . ' IS NULL';
		}

		if (count($parts) > 0)
		{
			$query->where('(' . implode(' OR ', $parts) . ')');
		}

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (is_array($result))
		{
			$this->cache = $result;
		}

		return $this->cache;
	}
}
