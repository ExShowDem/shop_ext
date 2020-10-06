<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_topnav
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

RHtml::_('rjquery.ui');

$userDisplay = $user->get('name');

if (!$user->guest && $rsbUserId)
{
	$userDisplay  = '<a href="' . Route::_('index.php?option=com_redshopb&task=user.editown&id=' . $rsbUserId) . '">';
	$userDisplay .= $user->get('name') . '</a>';
}

$displayProfile = ($params->get('user_profile', 1) && $user->get('id'));

$logoutUrl = Route::_('index.php?option=com_users&task=user.logout&' . Session::getFormToken() . '=1&return=' . $return);

$shouldDisplayCart = ($isShop && $params->get('cart', 1) && RedshopbHelperPrices::displayPrices());

$loggedInUserWithPermission = ($user->get('id') && RedshopbHelperACL::getPermission('place', 'order'));
$b2cUserWithPermission      = ($user->b2cMode && RedshopbHelperACL::getGlobalB2CPermission('place', 'order'));
$canDisplayCart             = ($loggedInUserWithPermission || $b2cUserWithPermission);

$company = RedshopbHelperCompany::getCompanyById($customerId);
$config  = RedshopbEntityConfig::getInstance();

$displayTotal = '<div class="oneCurrencyTotal" data-currency="' . $config->get('default_currency', 38) . '">0,00</div>';

$currency = null;

if (!empty($cartTotal))
{
	$displayTotal = array();

	foreach ($cartTotal as $currency => $total)
	{
		if ((float) $total >= 0.0)
		{
			$displayTotal[] = '<div class="oneCurrencyTotal" data-currency="' . $currency . '">'
				. RedshopbHelperProduct::getProductFormattedPrice($total, $currency) . '</div>';
		}
	}

	$displayTotal = implode(" ", $displayTotal);
}
elseif ($customerType == 'company' && $company->type == 'customer')
{
	$currency     = $company->currency_id;
	$displayTotal = '<div class="oneCurrencyTotal" data-currency="' . $currency . '">'
		. RedshopbHelperProduct::getProductFormattedPrice(0, $currency)
		. '</div>';
}

$showMyPage = ($user->get('id')
	&& (RedshopbHelperACL::isSuperAdmin() || RedshopbHelperPrices::displayPrices() || RedshopbHelperPrices::displayPrices() === false)
	&& RedshopbHelperACL::getPermission('place', 'order'));

$showImportCart = $user->get('id')
	&& (RedshopbHelperACL::isSuperAdmin() || RedshopbHelperACL::getPermission('import', 'order'))
	&& $displayImportCsvButton;

$returnToCart = base64_encode('index.php?option=com_redshopb&view=shop&layout=cart');

$option = $app->input->get('option');
$view   = $app->input->get('view');
$layout = $app->input->get('layout');

$isCheckout = ($option == 'com_redshopb' && $view == 'shop' && $layout == 'cart');

$cartFormAction = Route::_('index.php?option=com_redshopb&view=shop');
$cartFormId     = 'shopCartForm' . $module->id;

// Save cart modal
$modalId         = 'js-saveCartModal_' . $customerId . '_' . $customerType . '_' . $module->id;
$loadCartModalId = 'js-LoadCartModal_' . $customerId . '_' . $customerType . '_' . $module->id;

$savedCarts = null;

if ($user->get('id'))
{
	/** @var RedshopbModelCarts $savedCartsModel */
	$savedCartsModel = RedshopbModel::getFrontInstance('Carts', array('ignore_request' => true));
	$savedCarts      = $savedCartsModel->getRawItems();
}

$options = array();

$options[] = HTMLHelper::_('select.option', 'NEW', Text::_('JNEW'));

if (!empty($savedCarts))
{
	foreach ($savedCarts as $savedCart)
	{
		if ($savedCart->user_cart == '0')
		{
			$options[] = HTMLHelper::_('select.option', $savedCart->id, $savedCart->name);
		}
	}
}
?>
<div class="modRedshopbStatus row" id="modRedshopbStatus_<?php echo $module->id; ?>">
	<div class="col-md-12">
		<?php if ($displayProfile):?>
			<div class="modRedshopbStatusUserProfile row">
				<div class="col-md-12">
					<span class="username"><?php echo $userDisplay; ?></span> -
					<form action="<?php echo Route::_('index.php?option=com_users');?>" method="post" class="form-inline">
						<a href="javascript:void(0);" onclick="jQuery(this).closest('form').submit();">
							<?php echo Text::_('LIB_REDCORE_ACCOUNT_LOGOUT'); ?>
						</a>
						<input type="hidden" name="option" value="com_users"/>
						<input type="hidden" name="return" value="<?php echo $return;?>">
						<input type="hidden" name="task" value="user.logout"/>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				</div>
			</div>
		<?php endif; ?>

			<?php if ($shouldDisplayCart && $canDisplayCart): ?>
				<div class="modRedshopbStatusCart">
					<a href="javascript:void(0);" id="redshopb-cart-link" class="btn btn-success"
						onclick="redSHOPB.cart.toggleCart(event);">
						<div class="pull-left">
						<span class="redshopb-cart-items">
							<?php echo $numberOfItems ?>
						</span>
							<?php echo Text::_('COM_REDSHOPB_SHOP_ITEMS'); ?>
						</div>
						<?php if ($params->get('total_in_head', 0)): ?>
							<div class="pull-right total-value">
								<?php echo $displayTotal; ?>
							</div>
						<?php endif; ?>
					</a>
					<form action="<?php echo $cartFormAction; ?>"
						name="<?php echo $cartFormId; ?>"
						id="<?php echo $cartFormId; ?>"
						class="adminForm js-shopping-cart-form"
						method="post">
						<div id="redshopb-cart">
							<div class="redshopb-cart-content shopping-cart-form">
								<div class="spinner pagination-centered">
									<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
								</div>
							</div>
						</div>
						<input type="hidden" name="refModId" value="<?php echo $modalId; ?>" />
						<input type="hidden" name="refLoadModId" value="<?php echo $loadCartModalId; ?>" />
						<input type="hidden" name="showDiscColumn" value="<?php echo $showDiscColumn; ?>" />
						<input type="hidden" name="isCheckout" value="<?php echo (int) $isCheckout; ?>" />
						<input type="hidden" name="task" value="shop.checkout" />
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				</div>

				<?php
				/*
				Temporarily removed because of failing tests

					<form action="<?php echo Route::_('index.php?option=com_redshopb&view=carts'); ?>"
						  style="margin:0 0;">
						<div id="<?php echo $modalId; ?>"
							 class="modal fade"
							 tabindex="-1" role="dialog"
							 aria-labelledby="saveCartModalLabel"
							 aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h3 id="saveCartModalLabel"><?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_MODAL_TITLE'); ?>
										</h3>
									</div>
									<div class="modal-body">
										<div class="row-fluid">
											<div class="span2">
												<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_SELECT_CART'); ?>
											</div>
											<div class="span10">
												<?php echo HTMLHelper::_('select.genericlist', $options, 'savedCartId') ?>
											</div>
										</div>
										<div class="row-fluid">
											<div class="span2">
												<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_MODAL_CART_INPUT_NAME'); ?>
											</div>
											<div class="span10">
												<input type="text"
													id="<?php echo $modalId . '_name'; ?>"
													class="input required"
													name="name"
													required="true"
													aria-required="true"
													placeholder="<?php
														echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_MODAL_CART_INPUT_NAME_DESC'); ?>"/>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<a href="javascript:void(0);" class="btn" data-dismiss="modal"
										   aria-hidden="true">
											<i class="icon-remove"></i>&nbsp
											<?php echo Text::_('JTOOLBAR_CLOSE'); ?>
										</a>
										<button id="<?php echo $modalId . '_save'; ?>" class="btn btn-success"><i
													class="icon-save">
											</i>&nbsp;<?php echo Text::_('JTOOLBAR_SAVE'); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" name="option" value="com_redshopb">
						<input type="hidden" name="task" value="cart.saveCart">
						<input type="hidden" name="return"
							   value="<?php echo base64_encode(Route::_('index.php?option=com_redshopb&view=carts')); ?>">
						<input type="hidden" name="customer"
							   value="<?php echo base64_encode($customerId . '_' . $customerType); ?>"/>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>

					<form action="<?php echo Route::_('index.php?option=com_redshopb&view=cart'); ?>" method="post"
						  style="margin:0 0;">
						<div id="<?php echo $loadCartModalId; ?>"
							 class="modal fade"
							 tabindex="-1"
							 role="dialog"
							 aria-labelledby="saveCartModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h3 id="saveCartModalLabel"><?php echo Text::_('COM_REDSHOPB_SHOP_LOAD_CART'); ?></h3>
									</div>
									<div class="modal-body">
										<div class="row-fluid">
											<div class="span2">
												<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_SELECT_CART'); ?>
											</div>
											<div class="span10">
												<?php array_shift($options); ?>
												<?php echo HTMLHelper::_('select.genericlist', $options, 'cartId') ?>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<a href="javascript:void(0);" class="btn" data-dismiss="modal"
										   aria-hidden="true">
											<i class="icon-remove"></i>&nbsp
											<?php echo Text::_('JTOOLBAR_CLOSE'); ?>
										</a>
										<button id="<?php echo $loadCartModalId . '_load'; ?>" type="submit"
												class="btn btn-success">
											</i>&nbsp;<?php echo Text::_('COM_REDSHOPB_SHOP_CHECKOUT'); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" name="task" value="cart.checkoutCart"/>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
					*/
					?>
				<script>
					jQuery(document).ready(function () {
						redSHOPB.cart.getShoppingCart('<?php echo $cartFormId; ?>');

						<?php
						/*
						Commented out
						jQuery('#<?php echo $modalId;?> select[name="savedCartId"]').on('change', function (event) {
							var targ = redSHOPB.form.getEventTarget(event);
							var selected = targ.find(':selected');

							var form = targ.closest('form');
							var name = form.find('input[name="name"]');

							if (selected.val() == 'NEW') {
								name.val('');
								name.focus();
								return;
							}

							name.val(selected.text());
							name.focus();
						});
						*/
						?>
					});

					var cartLoaded = true;
				</script>
			<?php endif; ?>
		</div>
	</div>

	<?php if ($showImportCart) :?>
		<div class="modRedshopbStatus row">
			<div class="col-md-12">
				<div class="import-link-status pull-right">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=import_cart'); ?>" class="btn">Import Cart</a>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php if ($showMyPage || $displayImpersonationButtons):?>
	<div class="row">
		<div class="col-md-12">
			<?php if ($showMyPage) :?>
				<div class="profile-link-status pull-right">
					<a class="btn btn-success" href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=mypage') ?>">
						<?php echo Text::_('COM_REDSHOPB_MYPAGE'); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ($displayImpersonationButtons) :?>
				<div class="impersonation-buttons-status pull-right">
					<form method="post"
						id="modRedshopbStatusForm"
						name="modRedshopbStatusForm"
						action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop'); ?>">

						<?php if ($companiesVendor == 'parent'):?>
							<a href="javascript:void(0);"
							   onclick="Joomla.submitform('shop.changevendor', document.getElementById('modRedshopbStatusForm'))"
							   class="btn btn-danger shop-changevendor">
								<i class="icon-signout"></i>
								<?php echo Text::_('COM_REDSHOPB_SHOP_CHANGE_VENDOR'); ?>
							</a>
						<?php endif;?>
						<a href="javascript:void(0);"
						   onclick="Joomla.submitform('shop.changecustomer', document.getElementById('modRedshopbStatusForm'))"
						   class="btn btn-danger shop-changecustomer">
							<i class="icon-signout"></i>
							<?php echo Text::_('COM_REDSHOPB_SHOP_CHANGE_CUSTOMER'); ?>
						</a>
						<input type="hidden" name="task" value=""/>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif;
