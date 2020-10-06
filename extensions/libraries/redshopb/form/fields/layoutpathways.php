<?php

/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');


/**
 * Field of layout pathways.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.13.0
 */
class JFormFieldLayoutPathways extends JFormFieldRlist
{
	/**
	 * @var string
	 */
	protected $type = 'LayoutPathways';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.13.0
	 */
	protected function getOptions()
	{
		$options         = array();
		$layoutPatchWays = RedshopbLayoutFile::getInstance('fake')
			->getDefaultIncludePaths();

		// Build the field options.
		if (!empty($layoutPatchWays))
		{
			foreach ($layoutPatchWays as $path)
			{
				$path         = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
				$relativePath = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $path);
				$options[]    = HTMLHelper::_('select.option', $relativePath, $relativePath);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
