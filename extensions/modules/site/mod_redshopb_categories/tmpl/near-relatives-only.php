<?php
/**
 * @package     Redshopb.Site
 * @subpackage  mod_redshopb_categories
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$classes   = array();
$classes[] = 'modRedshopbCategories nav nav-list';
$classes[] = 'menu';

$id = 'id="modRedshopbCategories_' . $module->id . '"';

if (!is_null($params->get('tag_id')))
{
	$id = ' id="' . $params->get('tag_id') . '"';
}

// Note. It is important to remove spaces between elements.
// The menu class is deprecated. Use nav instead.
JLoader::register('ModBreadCrumbsHelper', JPATH_ROOT . '/modules/mod_breadcrumbs/helper.php');
$parentLink = array_reverse(ModBreadCrumbsHelper::getList($params));
$parentLink = array_slice($parentLink, 1, 1);

$deeper      = null;
$parentColor = null;
$parentId    = null;
$current     = $list->current;

foreach ($list->redShopBCategories as $category)
{
	if ($category->id == $current)
	{
		$parentId = $category->parent_id;
		$deeper   = $category->deeper;
		break;
	}
}
?>

<div class="control-group">

	<div class="controls no-margin">
		<ul <?php echo $id ?> class="<?php echo implode(' ', $classes) ?>">
			<?php
			if (empty($deeper)) :
				$parentColor = " dark";
			else :
				$parentColor = " red";
			endif;

			$hasCurrentCategory = ($list->current != 0);

			foreach ($parentLink as $item) :
				echo '<li class="parent-link' . $parentColor . '">';
				echo '<a href="' . $item->link . '"><span class="kompan-arrow left"></span>' . $item->name . '</a>';
				echo '</li>';
			endforeach;

			foreach ($list->redShopBCategories as $i => &$item)
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

				if (($item->parent_id == $list->current) || (empty($deeper) && ($item->parent_id) == $parentId))
				{
					echo '<li class="' . implode(' ', $class) . '"><a ' . ModRedshopbCategoriesHelper::getLinkAttributes($attr)
						. '>' . $item->title . $caret . '</a></li>';
				}
			}
			?>
		</ul>
	</div>
</div>
