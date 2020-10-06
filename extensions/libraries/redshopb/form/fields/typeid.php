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

/**
 * Type Id Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       2.0
 */
class JFormFieldTypeid extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Typeid';

	/**
	 * A static cache.
	 *
	 * @var array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('id', 'value') . ',' . $db->qn('name', 'text'));
		$query->from($db->qn('#__redshopb_type'))->order('name');
		$db->setQuery($query);

		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
		}

		return $options;
	}
}

