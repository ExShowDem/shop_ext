<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

if (empty($menu))
{
	return;
}
?>
<div style="position: relative; margin-bottom: 30px">
	<ul class="nav nav-list tree">
		<?php $trees = $menu->getTrees(); ?>

		<?php if ($trees) : ?>
			<?php foreach ($trees as $tree) : ?>
				<?php
				$node = $tree->getRootNode();
				?>
				<?php echo RedshopbLayoutHelper::render('menu.collapse.node', array('node' => $node), null, array('debug' => false)); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
	<?php if (isset($menu->showMore) && $menu->showMore > 0): ?>
	<span class="pull-right" style="margin-top: 10px">
		<?php echo Text::sprintf('COM_REDSHOPB_SHOP_COMPANIES_MENU_MORE', $menu->showMore);?>
	</span>
	<?php endif;?>
</div>
