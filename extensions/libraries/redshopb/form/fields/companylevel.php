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
 * Company Level Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCompanyLevel extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CompanyLevel';

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

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the companies level
		$maxLevel = $this->getCompaniesLevel($state);

		// Build the field options
		if ($maxLevel)
		{
			for ($i = 1; $i <= $maxLevel; $i++)
			{
				$options[] = HTMLHelper::_('select.option', $i, Text::sprintf('COM_REDSHOPB_COMPANY_SELECT_COMPANY_LEVEL', $i));
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
	protected function getCompaniesLevel($state)
	{
		if (empty($this->cache))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('MAX(level)')
				->from($db->qn('#__redshopb_company'))
				->where($db->qn('deleted') . ' = 0');

			// Check for available companies for this user if not a system admin of the app
			if (!RedshopbHelperACL::isSuperAdmin())
			{
				$user = Factory::getUser();
				$query->where('id IN (' . RedshopbHelperACL::listAvailableCompanies($user->id) . ')');
			}

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('state = ' . $db->quote($state));
			}
			else
			{
				$query->where('state IN (0,1)');
			}

			$result = $db->setQuery($query)->loadResult();

			if ($result)
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
