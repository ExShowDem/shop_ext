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

if (empty($children))
{
	return;
}

// Use automatic keys
$children = array_values($children);

$parent = $children[0]->getParent();

?>
<ul class="nav nav-list tree collapse in" id="submenu<?php echo $parent->getName(); ?>">

	<?php foreach ($children as $child) : ?>
		<?php echo RedshopbLayoutHelper::render('menu.collapse.node', array('node' => $child), null, array('debug' => false)); ?>
	<?php endforeach; ?>
</ul>
