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
 * Company Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCategoryType extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CategoryType';

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

		$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_CATEGORY_TYPE'));

		// Get the companies
		$items = $this->getCategoryTypes();

		// Build the field options
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
	 * Method to get the list of companies.
	 *
	 * @return  array  An array of category types
	 */
	protected function getCategoryTypes()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('DISTINCT c.type')
				->from($db->qn('#__redshopb_category', 'c'))
				->where('c.type IS NOT NULL')
				->order('c.type');

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
