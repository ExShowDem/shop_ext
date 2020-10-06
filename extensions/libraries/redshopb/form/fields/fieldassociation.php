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
 * Filter Fieldset Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldFieldassociation extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Fieldassociation';

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

		// Get the options.
		$items = $this->getFieldsForAssociation();

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
	 * Method to get the list of Filter fieldsets.
	 *
	 * @return  array  An array of filter fieldset names.
	 */
	protected function getFieldsForAssociation()
	{
		if (empty($this->cache))
		{
			$scope = $this->form->getData()->get('scope', null);

			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select($db->qn('id', 'identifier'))
				->select('CONCAT(name, " - ", scope)' . $db->qn('data'))
				->from($db->qn('#__redshopb_field'))
				->where($db->qn('global') . ' = ' . $db->quote(0));

			if ($scope)
			{
				$query->where($db->qn('scope') . ' = ' . $db->quote($scope));
			}

			$query->order($db->qn('scope') . ' ASC')
				->order($db->qn('ordering') . ' ASC');

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
