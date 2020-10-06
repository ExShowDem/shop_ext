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
 * Sync parent Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldSyncPlugin extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'SyncPlugin';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('element AS text, element AS value')
			->from($db->qn('#__extensions'))
			->where('type = ' . $db->q('plugin'))
			->where('folder = ' . $db->q('rb_sync'))
			->order('ordering ASC');

		$availablePlugins = $db->setQuery($query)
			->loadObjectList();

		$options = array_merge(parent::getOptions(), $availablePlugins);

		return $options;
	}
}
