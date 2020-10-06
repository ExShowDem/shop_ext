<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

extract($displayData);

if (empty($node))
{
	return;
}

$classes = array();

$children = $node->getChildren();

if ($children)
{
	$classes[] = 'parent';
}

if ($node->isActive())
{
	$classes[] = 'active';
}

$target = $node->getTarget();

if ($target == '#')
{
	$classes[] = 'nolink';
}

?>
<li <?php if ($classes) : ?>class="<?php echo implode(' ', $classes); ?>"<?php
	endif; ?>>

	<a href="<?php echo $target; ?>" <?php if ($classes) : ?>class="<?php echo implode(' ', $classes); ?>"<?php
			 endif; ?>>

		<?php if ($children) : ?>
			<span class="tree-toggle" data-toggle="collapse" data-target="#submenu<?php echo $node->getName(); ?>"><i class="icon-chevron-up"></i></span>
		<?php endif; ?>
		<i class="indenter icon-angle-right"></i> <?php echo $node->getContent(); ?>
	</a>
	<?php if ($children) : ?>
		<?php echo RedshopbLayoutHelper::render('menu.collapse.children', array('children' => $children), null, array('debug' => false)); ?>
	<?php endif; ?>
</li>
