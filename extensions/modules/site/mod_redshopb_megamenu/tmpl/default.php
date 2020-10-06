<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_megamenu
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('bootstrap.loadCss', true);

Factory::getDocument()->addScriptDeclaration('
(function($){
	$(document).ready(function () {
		jQuery(\'#redshopbMegaMenu_' . $module->id . '\').shopbMegaMenu({
			effect: \'' . $params->get('effect', 'fade') . '\', animation: \'' . $params->get('animation', 'none') . '\',
			indicatorFirstLevel: \'' . $params->get('indicatorFirstLevel', '+') . '\', indicatorSecondLevel: \''
	. $params->get('indicatorSecondLevel', '+') . '\',
			showSpeed: ' . (int) $params->get('showSpeed', 300) . ', hideSpeed: ' . (int) $params->get('hideSpeed', 300) . ',
			showOverlay: ' . ($params->get('showOverlay', 1) ? 'true' : 'false') . '
		});
	});
})(jQuery);
'
);

// Note. It is important to remove spaces between elements.
?><div id="redshopbMegaMenu_<?php
echo $module->id; ?>" class="navbar shopbMegaMenu"><ul class="nav shopbMegaMenu-menu menu<?php
	echo $classSfx; ?>"<?php
$tag = '';

if ($params->get('tag_id') != null)
{
	$tag = $params->get('tag_id') . '';
	echo ' id="' . $tag . '"';
}
?>><?php

foreach ($list as $i => &$item)
{
	$class = array('item-' . $item->id, 'level-item-' . $item->level);

	if (($item->id == $activeId) || ($item->type == 'alias' && $item->params->get('aliasoptions') == $activeId))
	{
		$class[] = 'current';
	}

	if (in_array($item->id, $path))
	{
		$class[] = 'active';
	}
	elseif ($item->type == 'alias')
	{
		$aliasToId = $item->params->get('aliasoptions');

		if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
		{
			$class[] = 'active';
		}
		elseif (in_array($aliasToId, $path))
		{
			$class[] = 'alias-parent-active';
		}
	}

	if ($item->type == 'separator')
	{
		$class[] = 'divider';
	}

	if ($item->deeper || isset($item->redShopBCategories))
	{
		$class[] = 'deeper';
	}

	if ($item->parent)
	{
		$class[] = 'parent';
	}

	if (isset($item->redShopBCategories))
	{
		$item->pluginParams = $params;
		$item->lastItem     = 0;
		ModRedshopbMegaMenuHelper::displayLevel($item->redShopBCategories, $item, $item->level);
	}
	else
	{
		if (($item->mega && !$item->replaceItem) || !$item->mega)
		{
			echo '<li class="' . implode(' ', $class) . '">';

			// Render the menu item.
			switch ($item->type)
			{
				case 'separator':
				case 'url':
				case 'component':
				case 'heading':
				case 'module':
					include RModuleHelper::getLayoutPath('mod_redshopb_megamenu', 'default_' . $item->type);
					break;

				default:
					include RModuleHelper::getLayoutPath('mod_redshopb_megamenu', 'default_url');
					break;
			}
		}

		if ($item->mega)
		{
			$item->pluginParams = $params;
			$item->lastItem     = 0;
			ModRedshopbMegaMenuHelper::displayJoomlaLevel($item->children, $item, $item->displayLevel, $item->id);
		}

		if (($item->mega && !$item->replaceItem) || !$item->mega)
		{
			// The next item is deeper.
			if ($item->deeper)
			{
				echo '<ul class="nav-child unstyled list-unstyled small dropdown">';

				continue;
			}

			if ($item->shallower)
			{
				// The next item is shallower.
				echo '</li>';
				echo str_repeat('</ul></li>', $item->level_diff);

				continue;
			}

			// The next item is on the same level.
			echo '</li>';
		}
	}
}
?></ul><div class="clearfix"></div></div><?php
