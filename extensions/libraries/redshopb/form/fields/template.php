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

FormHelper::loadFieldClass('rlist');

/**
 * Tag Parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTemplate extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Template';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->select(
				array(
					$db->qn('id', 'value'),
					$db->qn('name', 'text')
				)
			)
			->from($db->qn('#__redshopb_template'))
			->where($db->qn('state') . ' = 1')
			->where($db->qn('scope') . ' = ' . $db->quote((string) $this->element['scope']))
			->order('id ASC');

		if (isset($this->element['templateGroup']))
		{
			$templateGroup = (string) $this->element['templateGroup'];
		}
		else
		{
			$templateGroup = 'shop';
		}

		$query->where('template_group = ' . $db->q($templateGroup));

		if (isset($this->element['scope']))
		{
			$query->where('scope = ' . $db->q((string) $this->element['scope']));
		}

		// Get the options.
		$result = $db->setQuery($query)->loadObjectList();

		if ($result)
		{
			$options = $result;
		}

		return array_merge(parent::getOptions(), $options);
	}
}
