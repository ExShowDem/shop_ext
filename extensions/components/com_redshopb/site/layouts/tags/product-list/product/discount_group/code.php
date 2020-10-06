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
 * @var int $productId
 */

$discountGroups = RedshopbEntityProduct::getInstance($productId)->getDiscountGroups();

if ($discountGroups->count() > 0)
{
	?><ul class="discountGroupsClass"><?php
foreach ($discountGroups->toObjects() as $discountGroup):
	?>
	<li class="oneDiscountGroupCode"><?php echo $discountGroup->code ?></li>
	<?php
endforeach;
	?></ul><?php
}
