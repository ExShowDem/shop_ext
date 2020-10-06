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
 * Type Category Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTypeCategory extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'TypeCategory';

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

		// Get the types.
		$items = $this->getTypes();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->type, $item->type);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of types.
	 *
	 * @return  array  An array of types names.
	 */
	protected function getTypes()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('type')
				->from($db->qn('#__redshopb_category'))
				->order('type')
				->group('type');

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
