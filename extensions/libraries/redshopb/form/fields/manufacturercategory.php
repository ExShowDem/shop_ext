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
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Tag type Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.12.43
 */
class JFormFieldManufacturerCategory extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'ManufacturerCategory';

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
		$options = array();

		if (empty($this->cache))
		{
			$this->cache = $this->getManufacturerCategories();
		}

		// Build the field options.
		if (!empty($this->cache))
		{
			foreach ($this->cache as $manufacturerCategory)
			{
				$options[] = HTMLHelper::_('select.option', $manufacturerCategory, $manufacturerCategory);
			}
		}

		$parentOptions = parent::getOptions();

		if ($parentOptions)
		{
			$options = array_merge($parentOptions, $options);
		}

		return $options;
	}

	/**
	 * Get active user's collections
	 *
	 * @return  array  object with name and identifier of each collection
	 */
	protected function getManufacturerCategories()
	{
		return RedshopbHelperManufacturer::getManufacturerCategories();
	}
}
