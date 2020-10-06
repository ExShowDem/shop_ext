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
 * Department Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCollection extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Collection';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $userdep = array();

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
			$this->cache = $this->getCollections();
		}

		// Build the field options.
		if (!empty($this->cache))
		{
			foreach ($this->cache as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
			}

			if ((string) $this->element['force'] == 'true')
			{
				if ($this->value == '')
				{
					$this->value = $this->cache[0]->identifier;
				}
			}
		}

		$parentOptions = parent::getOptions();

		if ($parentOptions)
		{
			foreach ($parentOptions as $i => $option)
			{
				$parentOptions[$i]->text = $option->text;
			}
		}

		return array_merge($parentOptions, $options);
	}

	/**
	 * Get active user's collections
	 *
	 * @return  array  object with name and identifier of each collection
	 */
	protected function getCollections()
	{
		$filterShopDeps = false;

		if (isset($this->element['filter_shop_departments'])
			&& (string) $this->element['filter_shop_departments'] == 'true')
		{
			$filterShopDeps = true;
		}

		return RedshopbHelperCollection::getUserCollections($filterShopDeps);
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$view  = Factory::getApplication()->input->getString('view', 'shop');
		$showW = (int) RedshopbApp::getConfig()->get('show_shop_collection_filter', 1);

		if ($view == 'shop' && $showW == 0)
		{
			return '';
		}

		return parent::getInput();
	}
}
