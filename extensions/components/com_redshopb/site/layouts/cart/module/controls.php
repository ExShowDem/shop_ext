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
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$enableOffer = $displayData['enableOffer'];
$hasOffers   = $displayData['hasOffers'];
$hasItems    = $displayData['hasItems'];

$app             = Factory::getApplication();
$user            = Factory::getUser();
$modalId         = $app->getUserStateFromRequest('cart.refModId', 'refModId', null, 'string');
$loadCartModalId = $app->getUserStateFromRequest('cart.refLoadModId', 'refLoadModId', null, 'string')
?>

<?php if ($enableOffer && $hasItems && !$hasOffers): ?>
	<a class="btn btn-link btn-small requestOfferCartButton"
	   href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=requestoffer');?>">
		<i class="icon-envelope"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_REQUEST_OFFER');?>
	</a>
<?php endif; ?>

<?php if ($user->id != 0 && true == false):?>
	<?php if ($hasItems):?>
		<a href="javascript:void(0)"
		   class="btn btn-link btn-small shopping-cart-save"
		   data-toggle="modal"
		   data-target="#<?php echo $modalId;?>">
			<?php echo Text::_('COM_REDSHOPB_SHOP_SAVE_CART'); ?>
		</a>
	<?php endif;?>
	<a href="javascript:void(0)"
	   class="btn btn-link btn-small shopping-cart-load"
	   data-toggle="modal"
	   data-target="#<?php echo $loadCartModalId;?>">
		<?php echo Text::_('COM_REDSHOPB_SHOP_LOAD_CART'); ?>
	</a>
<?php endif;?>

<?php if ($hasItems || $hasOffers):?>
<a href="javascript:void(0)"
   class="btn btn-link btn-small shopping-cart-clear"
   onclick="redSHOPB.cart.clearShoppingCart(event)">
	<?php echo Text::_('COM_REDSHOPB_SHOP_CLEAR_CART'); ?>
</a>
<?php endif;
