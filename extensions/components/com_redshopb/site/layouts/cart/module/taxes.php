<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$customerType = $displayData['customerType'];
$customerId   = $displayData['customerId'];
$totals       = $displayData['totals'];
$taxes        = $displayData['taxes'];
?>
<div class="cartModuleTaxes">
	<?php foreach ($totals as $currency => $total):?>
		<?php if (!array_key_exists($currency, $taxes) || count($taxes[$currency]) <= 0):?>
			<?php continue;?>
		<?php endif;?>

		<table class="table table-condensed table-striped table-bordered">
			<?php foreach ($taxes[$currency] as $tax):?>
				<?php $tax = (object) $tax;?>
				<tr>
					<td>
						<?php echo $tax->name;?>

						<?php if (!empty($tax->product)): ?>
							<span class="highlight-1 hasTooltipTax cartAccessories">
								<i class="icon-info"></i>
							</span>
							<div class="hidden classWithTaxInfo">
								<?php foreach ($tax->product AS $product):?>
								<p><?php echo $product?></p>
								<?php endforeach;?>
							</div>
						<?php endif; ?>
					</td>
					<td class="text-right"><?php echo RedshopbHelperProduct::getProductFormattedPrice($tax->tax, $currency) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endforeach; ?>
</div>
