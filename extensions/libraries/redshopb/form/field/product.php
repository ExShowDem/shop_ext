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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::import('redshopb.library');

FormHelper::loadFieldClass('rlist');

/**
 * Product Field
 *
 * @since  2.0
 */
class RedshopbFormFieldProduct extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Product';

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
		if ((string) $this->element['emptystart'] == 'true')
		{
			return '<div id="redshopb-products"></div><div id="redshopb-products-loading">'
				. HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') . '</div>';
		}

		// Get the field options.
		$options = (array) $this->getOptions();

		if ((string) $this->element['restriction'] == 'company')
		{
			if (!$options)
			{
				return '<input type="hidden" name="' . $this->name . '" value="" /><span class="help-block">'
					. Text::_('COM_REDSHOPB_PRODUCT_COMPANY_NEEDED') . '</span>';
			}
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

		// Create a read-only list (no name) with a hidden input to store the value.
		if (((string) $this->element['readonly'] == 'true'
			|| (string) $this->element['disabled'] == 'true')
			&& (string) $this->element['force_select'] != 'true'
		)
		{
			$text = current($options)->text;

			foreach ($options as $option)
			{
				if ($option->value == $this->value)
				{
					$text = $option->text;
					break;
				}
			}

			$html[] = '<span>' . $text . '</span>';
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" id="' . $this->id . '"/>';
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

		// Filter by discontinued
		$discontinued = $this->element['discontinued'] ? (int) $this->element['discontinued'] : null;

		// Get the products.
		$items = $this->getProducts($state, $discontinued);

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
	 * Method to get the list of products.
	 *
	 * @param   integer  $state         The product state
	 * @param   integer  $discontinued  The discontinued status of a product
	 *
	 * @return  array  An array of product names.
	 */
	protected function getProducts($state = null, $discontinued = null)
	{
		if (empty($this->cache))
		{
			$db    = Factory::getDbo();
			$model = RedshopbModel::getFrontInstance('Products', array(), 'com_redshopb');

			$query = $db->getQuery(true)
				->from('#__redshopb_product AS p')
				->order('p.name');

			if (isset($this->element['showSku']) && $this->element['showSku'] == 1)
			{
				$query->select('p.id AS identifier, CONCAT(p.sku, " - ", p.name) AS data');
			}
			else
			{
				$query->select('p.id AS identifier, p.name AS data');
			}

			$user = Factory::getUser();

			// Selects ACL/logic restriction depending on where the field is placed
			switch ((string) $this->element['restriction'])
			{
				case 'parents':
					// Shows default ACL view and parent companies' tags
					$query->where(
						'(p.company_id IN (' .
						RedshopbHelperACL::listAvailableCompaniesAndParents($user->id) .
						')  OR ' . $db->qn('p.company_id') . ' IS NULL)'
					);
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
						$company = (int) $companyId;
						$query->where('p.company_id ' . ($companyId ? ' = ' . $company : ' IS NULL'));
					}
					break;

				case 'current':
					$companyId = Factory::getApplication()->input->getInt('id', 0);

					if ($companyId)
					{
						$query->where($db->qn('p.company_id') . ' = ' . $companyId);
					}

				case 'none':
					break;

				default:
					// Limit companies based on usual ACL (allowed companies' products)
					$query = $model->filterProductCompany($query);
			}

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('p.state = ' . $db->quote($state));
			}
			else
			{
				$query->where('p.state IN (0,1)');
			}

			// Filter by discontinued
			if (is_numeric($discontinued))
			{
				$query->where('p.discontinued = ' . $db->quote($discontinued));
			}
			else
			{
				$query->where('p.discontinued IN (0,1)');
			}

			if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
			{
				if ($this->value)
				{
					$query->where('p.id IN (' . implode(',', (array) $this->value) . ')');
				}
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
}
