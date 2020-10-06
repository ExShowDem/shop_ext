<?php
/**
 * @package     Aesir.Commerce.Site
 * @subpackage  mod_redshopb_categories
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$classes   = array();
$classes[] = 'modRedshopbCategories nav nav-list';
$classes[] = 'menu' . $classSfx;

$id = 'id="modRedshopbCategories_' . $module->id . '"';

if (!is_null($params->get('tag_id')))
{
	$id = ' id="' . $params->get('tag_id') . '"';
}

// Note. It is important to remove spaces between elements.
// The menu class is deprecated. Use nav instead.
?>
<ul <?php echo $id ?> class="<?php echo implode(' ', $classes) ?>">
<?php
$hasCurrentCategory = ($list->current != 0);
$currentCategory    = RedshopbEntityCategory::load($list->current);

foreach ($list->redShopBCategories as $i => &$item)
{
	$isAncestorOrSelf = strpos($currentCategory->get('path'), $item->path) !== false;
	$isChild          = $item->parent_id === $currentCategory->get('id');
	$shouldShow       = $isAncestorOrSelf || $isChild;

	if ($shouldShow)
	{
		$isTopLvl = (empty($item->tree));

		if ($hasCurrentCategory && ($isTopLvl || !in_array($item->tree[0], $list->redshopbPath)))
		{
			continue;
		}

		$key    = $list->id . '-' . $item->id;
		$class  = array('item-' . $key, 'level-item-' . $item->relationLevel);
		$attr   = array('href' => $item->flink);
		$caret  = '';
		$active = false;

		if ($item->deeper)
		{
			$class[] = 'deeper';
			$class[] = 'dropdown';

			if ($params->get('showAllChildren', 0) == 1)
			{
				$attr['class']       = 'dropdown-toggle';
				$attr['data-toggle'] = 'collapse';
				$attr['href']        = '#b2b_anchor_' . $module->id . '_' . $item->id;
				$caret               = '<b class="caret"></b>';
			}
		}

		if ($item->parent)
		{
			$class[] = 'parent';
		}

		if (in_array($item->id, $list->redshopbPath))
		{
			$class[] = 'active';
			$active  = true;

			if ($item->id == $list->current)
			{
				$class[] = 'current';
			}
		}

		$attr = ModRedshopbCategoriesHelper::setBrowserNav($item, $attr);
		echo '<li class="' . implode(' ', $class) . '"><a ' . ModRedshopbCategoriesHelper::getLinkAttributes($attr)
			. '>' . $item->title . $caret . '</a>';
	}

	// The next item is deeper.
	if ($item->deeper)
	{
		echo '<ul id="b2b_anchor_' . $module->id . '_' . $item->id . '" class="submenu collapse' . ($active ? ' in' : '') . '">';
	}
	elseif ($item->shallower)
	{
		// The next item is shallower.
		echo '</li>';
		echo str_repeat('</ul></li>', $item->level_diff);
	}
	else
	{
		// The next item is on the same level.
		echo '</li>';
	}
}
?></ul><?php
