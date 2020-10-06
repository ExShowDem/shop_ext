<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Menus_Restrictions
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

JLoader::import('redshopb.library');

/**
 * Vanir - Menus Restrictions Plugin
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Menus_Restrictions
 * @since       1.0.0
 */
class PlgVanirMenus_Restrictions extends CMSPlugin
{

	/**
	 * Add additional views for permission checking
	 *
	 * @param   array  $views  Views
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterComRedshopbGetViewsACL(&$views)
	{
		$restrictedMenus = $this->params->get('restrictedmenus');

		if (!is_array($restrictedMenus) || empty($restrictedMenus))
		{
			return;
		}

		$matchedRestrictions = array();

		// Checks the current restriction in the provided views
		if (is_array($views) && !empty($views))
		{
			foreach ($views as $view => $permission)
			{
				if (in_array($view, $restrictedMenus))
				{
					$views[$view]          = '';
					$matchedRestrictions[] = $view;
				}
			}
		}

		// Any missing restriction is added as a forced ones, to prevent access to any other non-considered view that was added to the plugin
		$missingRestrictions = array_diff($restrictedMenus, $matchedRestrictions);

		if (!empty($missingRestrictions))
		{
			foreach ($missingRestrictions as $missingRestriction)
			{
				$views[$missingRestriction] = '';
			}
		}
	}
}
