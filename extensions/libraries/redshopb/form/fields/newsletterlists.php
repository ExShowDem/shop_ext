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
 * Newsletter lists Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldNewsletterlists extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Newsletterlists';

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

		// Get the currencies
		$items = $this->getNewsletterLists($state);

		// Build the field options
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->id, $item->name);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of Newsletter Lists.
	 *
	 * @param   integer  $state  The currencies state
	 *
	 * @return  array  An array of Newsletter Lists names.
	 */
	protected function getNewsletterLists($state = null)
	{
		if (empty($this->cache))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('id, name')
				->from($db->qn('#__redshopb_newsletter_list'))
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

			$this->cache = $db->setQuery($query)->loadObjectList();
		}

		return $this->cache;
	}
}
