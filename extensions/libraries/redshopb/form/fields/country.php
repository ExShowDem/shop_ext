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
 * Country Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCountry extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Country';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$items   = $this->getCountries();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$optKey    = array(
					'attr' => 'data-eu="' . $item->eu_zone . '" data-has_state="' . $item->has_state . '"',
					'option.attr' => 'attr'
				);
				$options[] = HTMLHelper::_('select.option', $item->identifier, Text::_($item->data), $optKey);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of countries.
	 *
	 * @return  array  An array of country names.
	 */
	protected function getCountries()
	{
		static $cache     = array();
		$configAssignment = true;

		if (isset($this->element['configurationAssignment'])
			&& (string) $this->element['configurationAssignment'] == 'false')
		{
			$configAssignment = false;
		}

		$key = (int) $configAssignment;

		if (!array_key_exists($key, $cache))
		{
			$db       = Factory::getDbo();
			$subQuery = $db->getQuery(true)
				->select('s.id')
				->from($db->qn('#__redshopb_state', 's'))
				->where('s.country_id = c.id');

			$query = $db->getQuery(true)
				->select('c.id as identifier')
				->select('c.name as data')
				->select('c.eu_zone')
				->select('IF((' . $subQuery . ' LIMIT 0, 1) IS NOT NULL, 1, 0) AS has_state')
				->from($db->qn('#__redshopb_country', 'c'))
				->order('c.name');

			if (!RedshopbHelperACL::isSuperAdmin())
			{
				$user               = Factory::getUser();
				$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
				$or                 = array($db->qn('c.company_id') . ' IS NULL');

				if (!empty($availableCompanies))
				{
					$or[] = $db->qn('c.company_id') . ' IN (' . $availableCompanies . ')';
				}

				$query->where('(' . implode(' OR ', $or) . ')');
			}

			if ($configAssignment)
			{
				$config            = RedshopbApp::getConfig();
				$countryAssignment = $config->getString('country_assignment', '0');
				$shopCountries     = $config->get('shop_countries', array());
				$shopCountries     = Joomla\Utilities\ArrayHelper::toInteger($shopCountries);

				switch ($countryAssignment)
				{
					case '-':
						$ids = array((int) $config->getInt('default_country_id', 59));

						if ($this->value && (int) $this->value != (int) $config->getInt('default_country_id', 59))
						{
							$ids[] = (int) $this->value;
						}

						$query->where('c.id IN(' . implode(',', $ids) . ')');
						break;
					case '-1':
						$valuePosition = array_search($this->value, $shopCountries);

						if ($this->value && $valuePosition !== false)
						{
							unset($shopCountries[$valuePosition]);
						}

						if (!empty($shopCountries))
						{
							$query->where($db->qn('id') . ' NOT IN (' . implode(',', $shopCountries) . ')');
						}
						break;
					case '1':
						if ($this->value && !is_array($this->value) && !in_array($this->value, $shopCountries))
						{
							$shopCountries[] = $this->value;
						}
						elseif ($this->value && is_array($this->value))
						{
							$shopCountries = array_unique(array_merge($shopCountries, $this->value));
						}

						if (!empty($shopCountries))
						{
							$query->where($db->qn('id') . ' IN (' . implode(',', $shopCountries) . ')');
						}

						// If list empty - restrict any countries
						else
						{
							$query->where('0 = 1');
						}
						break;

					// Do nothing, all countries are available
					case '0':
					default:
						break;
				}
			}

			$cache[$key] = $db->setQuery($query)
				->loadObjectList();
		}

		if ($configAssignment
			&& count($cache[$key]) == 1
			&& $this->required)
		{
			$currentOption = reset($cache[$key]);

			if (!$this->value || $currentOption->identifier == $this->value)
			{
				$this->value    = $currentOption->identifier;
				$this->readonly = true;
			}
		}

		if ($this->readonly && isset($this->element['hideReadOnly'])
			&& (string) $this->element['hideReadOnly'] == 'true')
		{
			$this->hidden = true;
		}

		return $cache[$key];
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 */
	public function getLabel()
	{
		$this->getCountries();

		return parent::getLabel();
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1'
			|| (string) $this->readonly == 'true'
			|| (string) $this->disabled == '1'
			|| (string) $this->disabled == 'true'
		)
		{
			$attr .= ' disabled="disabled"';
		}

		if ($this->hidden)
		{
			return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		}

		// Create a read-only list (no name) with hidden input(s) to store the value(s).
		elseif ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = HTMLHelper::_(
				'select.genericlist', $options, '',
				array(
					'list.attr' => trim($attr),
					'option.attr' => 'attr',
					'option.key' => 'value',
					'option.text' => 'text',
					'list.select' => $this->value,
					'id' => $this->id
				)
			);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
				}
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
			}
		}
		else
			// Create a regular list.
		{
			$html[] = HTMLHelper::_(
				'select.genericlist', $options, $this->name,
				array(
					'list.attr' => trim($attr),
					'option.attr' => 'attr',
					'option.key' => 'value',
					'option.text' => 'text',
					'list.select' => $this->value,
					'id' => $this->id
				)
			);
		}

		return implode($html);
	}
}
