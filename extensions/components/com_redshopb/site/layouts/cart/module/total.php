<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$customerType = $displayData['customerType'];
$customerId   = $displayData['customerId'];
$items        = $displayData['items'];
$totals       = $displayData['totals'];
$taxes        = $displayData['taxes'];

/** @var RedshopbEntityConfig $config */
$config   = $displayData['config'];
$currency = $config->get('default_currency', 38);

if ($customerType == 'company')
{
	$company = RedshopbHelperCompany::getCompanyById($customerId);

	if ($company->type == 'customer')
	{
		$currency = $company->currency_id;
	}
}

$displayTotals = '<div class="oneCurrencyTotal">' . RedshopbHelperProduct::getProductFormattedPrice(0, $currency) . '</div>';

if (count($totals) >= 0)
{
	$displayTotals = array();

	foreach ($totals as $currency => $total)
	{
		$displayTotals[] = '<div class="oneCurrencyTotal">' . RedshopbHelperProduct::getProductFormattedPrice($total, $currency) . '</div>';
	}

	$displayTotals = implode("\n", $displayTotals);
}

?>

<div class="col-md-12 pull-right">
	<?php if (count($items) > 0) : ?>
		<button class="btn btn-success" id="lc-shopping-cart-checkout" type="submit">
			<?php echo Text::_('COM_REDSHOPB_SHOP_CHECKOUT'); ?>
		</button>
	<?php endif; ?>
	<div class="cartLabelSubtotalText"><?php echo Text::_('COM_REDSHOPB_SHOP_TOTAL'); ?>:</div>
	<div class="cartLabelSubtotalValue">
		<?php echo $displayTotals;?>
	</div>
</div>
