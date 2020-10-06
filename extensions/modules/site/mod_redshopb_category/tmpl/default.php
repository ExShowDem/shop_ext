<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_category
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ===================================
 * @var  RedshopbEntityCategory  $category     Category data object
 * @var  string                  $classSuffix  Module class suffix
 */

if (!$category->isLoaded())
{
	return;
}
?>

<div class="mod_redshopb_category<?php echo $classSuffix ?>">
	<h3><a href="<?php echo $category->link ?>"><?php echo $category->get('name') ?></a></h3>
	<div class="category-description">
		<?php echo $category->get('description') ?>
	</div>
</div>
