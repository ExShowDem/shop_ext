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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');
JLoader::import('redshopb.entity.config');

/**
 * Tax Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTax extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Tax';

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

		// Get the taxes
		$items = $this->getTaxes($state);

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
	 * Method to get the list of taxes.
	 *
	 * @param   integer  $state  The taxes state
	 *
	 * @return  array  An array of taxes names.
	 */
	protected function getTaxes($state = null)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('id as identifier')
			->select('name as data')
			->from('#__redshopb_tax')
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

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user               = Factory::getUser();
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
			$or                 = array($db->qn('company_id') . ' IS NULL');

			if (!empty($availableCompanies))
			{
				$or[] = $db->qn('company_id') . ' IN (' . $availableCompanies . ')';
			}

			$query->where('(' . implode(' OR ', $or) . ')');
		}

		return $db->setQuery($query)
			->loadObjectList();
	}
}
