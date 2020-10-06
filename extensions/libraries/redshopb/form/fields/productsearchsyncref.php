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
 * Product Search Sync Ref Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldProductSearchSyncRef extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'ProductSearchSyncRef';

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
		/** @var RedshopbTableProduct $productTable */
		$productTable    = RTable::getAdminInstance('Product', array(), 'com_redshopb');
		$wsSyncMapFields = $productTable->getAllWsSyncMapPk();

		// Build the field options.
		if (!empty($wsSyncMapFields))
		{
			foreach ($wsSyncMapFields as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item, $item);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
