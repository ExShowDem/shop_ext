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
JLoader::import('redshopb.entity.config');

/**
 * Currency Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCurrency extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Currency';

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

		if (!$this->value && $this->element['not_use_default'] != 'true')
		{
			$config      = RedshopbEntityConfig::getInstance();
			$this->value = $config->getInt('default_currency');
		}

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the currencies
		$items = $this->getCurrencies($state);

		if (!$this->required)
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_CURRENCY'));
		}

		// Build the field options
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
	 * Method to get the list of currencies.
	 *
	 * @param   integer  $state  The currencies state
	 *
	 * @return  array  An array of currency names.
	 */
	protected function getCurrencies($state = null)
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('id as identifier')
				->select('name as data')
				->from('#__redshopb_currency')
				->order('name');

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('state = ' . $db->quote($state));
			}

			else
			{
				$query->where('state IN (0,1)');
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
