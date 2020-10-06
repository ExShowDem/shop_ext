<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Menus_Restrictions
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

jimport('redcore.form.fields.rlist');
JLoader::import('redshopb.library');

/**
 * Group delivery time - groups field
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Menus_Restrictions
 * @since       1.0.0
 */
class JFormFieldVanirmenu extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $type = 'Vanirmenu';

	/**
	 * Current set of views / menus
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected static $views = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		$viewsFolder = JPATH_SITE . '/components/com_redshopb/views';

		$views = JFolder::folders($viewsFolder);

		if (!$views)
		{
			return $options;
		}

		foreach ($views as $view)
		{
			$viewName = $this->getRealViewName($view);

			if (!$viewName || isset(static::$views[$viewName]))
			{
				continue;
			}

			$viewNameRef = strtoupper($viewName);
			$viewText    = Text::_('COM_REDSHOPB_BREADCRUMB_' . $viewNameRef);

			static::$views[$viewName] = $viewText;
		}

		return array_merge($options, static::$views);
	}

	/**
	 * Find the real existing menu for a certain view folder
	 *
	 * @param   string  $viewName  Name of the view
	 *
	 * @return  string | false
	 *
	 * @since   1.0.0
	 */
	protected function getRealViewName($viewName)
	{
		$viewNameTest = RInflector::pluralize($viewName);

		if (!$this->languageStringExists('COM_REDSHOPB_BREADCRUMB_' . $viewNameTest))
		{
			return $viewNameTest;
		}

		$viewNameTest = RInflector::singularize($viewName);

		if (!$this->languageStringExists('COM_REDSHOPB_BREADCRUMB_' . $viewNameTest))
		{
			return $viewNameTest;
		}

		return false;
	}

	/**
	 * Find out if a certain language string exists or not
	 *
	 * @param   string  $languageString  Language string to find
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function languageStringExists($languageString)
	{
		return !(Text::_($languageString) != $languageString);
	}
}
