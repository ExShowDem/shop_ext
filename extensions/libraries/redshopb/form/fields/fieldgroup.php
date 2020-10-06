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

FormHelper::loadFieldClass('rlist');

/**
 * Tag Parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldFieldgroup extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Fieldgroup';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$scope = $this->form->getData()->get('scope', null);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('id', 'value'),
				'CONCAT(' . $db->qn('name') . ',\' - \', ' . $db->qn('scope') . ') AS text'
			)
		)
			->from($db->qn('#__redshopb_field_group'));

		if ($this->form->getField('scope'))
		{
			$query->where($db->qn('scope') . ' = ' . $db->q($scope));
		}

		$query->order('ordering ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}
}
