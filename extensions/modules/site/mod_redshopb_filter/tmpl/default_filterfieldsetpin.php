<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();
?>

<?php if (!empty($pinnedFilterFieldsets)): ?>
<!-- Filter Fieldsets filter -->
<?php
echo RedshopbLayoutHelper::render(
	'filters.fieldset',
	array(
		'data' => $pinnedFilterFieldsets,
		'vertical' => true,
		'showSubmit' => false,
		'jsCallback' => 'redSHOPB.shop.filters.filterProductList(event);'
	),
	null,
	array('component' => 'com_redshopb')
);
?>
<!-- Filter Fieldsets filter - End -->
<?php endif;
