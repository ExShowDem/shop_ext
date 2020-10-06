<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * @var object $product
 */

$discountGroups = RedshopbEntityProduct::getInstance($product->id)->getDiscountGroups();

if ($discountGroups->count() > 0)
{
	?><ul class="discountGroupsClass"><?php
foreach ($discountGroups->toObjects() as $discountGroup):
	?>
	<li class="oneDiscountGroupName"><?php echo $discountGroup->name ?></li>
	<?php
endforeach;
	?></ul><?php
}
