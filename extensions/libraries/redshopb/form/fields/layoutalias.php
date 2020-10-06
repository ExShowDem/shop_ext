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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.form.formfield');
FormHelper::loadFieldClass('text');


/**
 * Field of URL company alias.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldLayoutAlias extends JFormFieldRText
{
	/**
	 * @var string
	 */
	protected $type = 'LayoutAlias';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		$jinput   = Factory::getApplication()->input;
		$layoutId = $jinput->getInt('id', 0);

		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('company_id')
			->from($db->quoteName('#__redshopb_layout'))
			->where($db->quoteName('id') . ' = ' . $db->quote($layoutId));
		$db->setQuery($query);
		$companyId = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('alias')
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('published') . ' <> ' . $db->quote('-2'))
			->where($db->quoteName('params') . ' LIKE ' . $db->quote('%company_id\":\"' . $companyId . '\"%') . ' AND ' . $db->qn('link') . ' LIKE ' . $db->q('%view=b2buserregister%'));

		$db->setQuery($query);
		$alias = $db->loadResult();

		return '<input id="jform_alias" name="jform[alias]" type="text" value="' . $alias . '" label="COM_REDSHOPB_ALIAS" />';
	}
}
