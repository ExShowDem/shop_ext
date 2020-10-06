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
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

RHtml::_('rjquery.ui');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$action        = $displayData['action'];
$config        = $displayData['config'];
$usingPayments = $displayData['usingPayments'];
$usingShipping = $displayData['usingShipping'];

$checkoutRegistration = $config->get('checkout_registration', 'registration_required');
$isGuest              = Factory::getUser()->guest;

$showLoginForm = ($checkoutRegistration != 'registration_none' && $isGuest);
$hideCheckout  = ($checkoutRegistration == 'registration_required' && $isGuest);

// Settings for checkout.payment_form
$paymentSettings              = $displayData;
$paymentSettings['showTitle'] = true;

// Settings for checkout.shipping_form
$shippingMethodSettings = $displayData['shippingMethodSettings'];

// Settings for checkout.delivery_info
$deliverySettings                  = $displayData;
$deliverySettings['showTitle']     = true;
$deliverySettings['showLoginForm'] = $showLoginForm;

// Settings for checkout.delivery_address_form
$deliveryFormSettings                     = array('showTitle' => true);
$deliveryFormSettings['address']          = (array) $displayData['deliveryAddress'];
$deliveryFormSettings['fields']           = $displayData['checkoutFields'];
$deliveryFormSettings['manageOwnAddress'] = $displayData['ownAddressManage'];
$deliveryFormSettings['showLoginForm']    = $showLoginForm;

if ($config->getInt('use_shipping_date', 0))
{
	$app                 = Factory::getApplication();
	$customerOrders      = $displayData['customerOrders'];
	$countCustomerOrders = count($customerOrders);
	$ids                 = array();
	$shippingDate        = (array) $app->getUserState('checkout.shipping_date', array());
	$shippingDateDelay   = (array) $app->getUserState('checkout.shipping_date_delay', array());

	// Settings for checkout.shipping_date
	$shippingDateSettings                      = array();
	$shippingDateSettings['orderCount']        = count($customerOrders);
	$shippingDateSettings['shippingDate']      = (array) $app->getUserState('checkout.shipping_date', array());
	$shippingDateSettings['shippingDateDelay'] = (array) $app->getUserState('checkout.shipping_date_delay', array());

	$datePickerSettings               = array();
	$datePickerSettings['buttonText'] = '<i class="icon-calendar icon-2x"></i>';
	$datePickerSettings['dateFormat'] = 'yy-mm-dd';
	$datePickerSettings['minDate']    = 1;
	$datePickerSettings['showOn']     = 'both';

	$shippingDateSettings['datePickerSettings'] = json_encode((object) $datePickerSettings);
}

$requisition = $displayData['checkoutFields']->requisition;
$comment     = $displayData['checkoutFields']->comment;

$showShippingDelayOrder = false;

$termsArticle       = $config->getString('terms_and_conditions', '');
$terms              = '';
$termsAndConditions = '';
$layout             = '';

if (!empty($termsArticle))
{
	$terms               = '<input type="hidden" id="terms-hidden" name="terms" value="0"/>';
	$termsAndConditions  = '<input type="checkbox" id="terms-and-conditions" class="terms-checkbox"/>';
	$termsAndConditions .= '<a href="#" onclick="jQuery(\'#acceptTerms\').modal(\'toggle\');">
								' . Text::_('COM_REDSHOPB_SHOP_TERMS_AND_CONDITIONS') . '
							 </a>';
	$tmp                 = explode('.', $termsArticle);

	if ($tmp[0] == 'content')
	{
		$contentModel = RModel::getAdminInstance('Article', array(), 'com_content');
		$article      = $contentModel->getItem($tmp[1]);
		$this->terms  = $article->introtext . $article->fulltext;
	}
	elseif ($tmp[0] == 'aesir' && ComponentHelper::isInstalled('com_reditem'))
	{
		jimport('libraries.reditem.entity.item');
		$itemEntity  = ReditemEntityItem::getInstance($tmp[1]);
		$this->terms = $itemEntity->renderTemplate();
	}

	$layout = RedshopbLayoutHelper::render('shop.terms', array('terms' => $this->terms));
}

?>
<?php if ($isGuest): ?>
	<div class="row-fluid">
		<div class="span12">
			<?php echo RedshopbLayoutHelper::render('checkout.login_register', $deliveryFormSettings); ?>
		</div>
	</div>
<?php endif; ?>
<?php
if ($hideCheckout): ?>
	<?php return; ?>
<?php endif ?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			jQuery(".ajax-quantity-change").on("change", function (event) {
				redSHOPB.shop.cart.updateItemQuantity(event);
			});

			<?php if (!empty($termsArticle)): ?>
			jQuery('#terms-and-conditions').on('change', function() {
				var term = jQuery("#terms-hidden");
				if (jQuery(this).is(":checked")) {
					term.val(1);
				} else {
					term.val(0);
				}
			});
			<?php endif; ?>
		});
	</script>
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<div class="row-fluid">
			<?php if ($usingShipping || !$isGuest):	?>
				<div class="span6">
					<?php if ($usingShipping): ?>
						<div class="row-fluid">
							<div class="span12 well">
								<div class="row-fluid">
									<div class="span12">
										<div id="shippingMethods">
											<?php echo RedshopbLayoutHelper::render('checkout.shipping_form', $shippingMethodSettings); ?>
										</div>
									</div>
								</div>
								<?php if ($config->getInt('use_shipping_date', 0)):	?>
									<div class="row-fluid">
										<div class="span12">
											<?php foreach ($displayData['customerOrders'] as $customerOrder):
												$shippingDateSettings['customerOrder'] = $customerOrder;
												$showShippingDelayOrder                = !empty($customerOrder->regular->hasDelayProduct) ? $customerOrder->regular->hasDelayProduct : false;
												echo RedshopbLayoutHelper::render('checkout.shipping_date', $shippingDateSettings); ?>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php if ($config->getAllowSplittingOrder()): ?>
						<div id="delayOrderShippingList" class="<?php echo $showShippingDelayOrder ? '' : 'hide' ?> isDelayOrderParameters">
							<div class="row-fluid">
								<div class="span12 well">
									<h3><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h3>
									<div class="row-fluid">
										<div class="span12">
											<?php
											$shippingMethodSettings['delayOrder'] = true;
											echo RedshopbLayoutHelper::render('checkout.shipping_form', $shippingMethodSettings); ?>
										</div>
									</div>
									<?php if ($config->getInt('use_shipping_date', 0)): ?>
										<div class="row-fluid">
											<div class="span12">
												<?php foreach ($displayData['customerOrders'] as $customerOrder):
													$shippingDateSettings['customerOrder'] = $customerOrder;
													$shippingDateSettings['delayOrder']    = true;
													echo RedshopbLayoutHelper::render('checkout.shipping_date', $shippingDateSettings); ?>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<?php endif; ?>

					<?php if (!$isGuest): ?>
						<div class="row-fluid">
							<div class="span12 well">
								<div class="row-fluid">
									<div class="span12" id="redshopb-delivery-info-address">
										<?php echo RedshopbLayoutHelper::render('checkout.delivery_address_form', $deliveryFormSettings); ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="span6">
				<?php if ($usingPayments): ?>
					<div class="row-fluid">
						<div class="span12 well">
							<?php echo RedshopbLayoutHelper::render('checkout.payment_form', $paymentSettings); ?>
						</div>
					</div>
				<?php if ($config->getAllowSplittingOrder()): ?>
				<div id="delayOrderPaymentList" class="<?php echo $showShippingDelayOrder ? '' : 'hide' ?> isDelayOrderParameters">
					<div class="row-fluid">
						<div class="span12 well">
							<h3><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h3>
							<?php echo RedshopbLayoutHelper::render('checkout.payment_delay_form', $paymentSettings); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php endif; ?>
				<div class="row-fluid">
					<div class="span12 well">
						<?php if (!$isGuest): ?>
							<div class="row-fluid">
								<div class="span12">
									<?php echo RedshopbLayoutHelper::render('checkout.delivery_info', $deliverySettings); ?>
								</div>
							</div>
						<?php endif; ?>
						<?php
						if ($config->getInt('use_shipping_date', 0)): ?>
							<div class="row-fluid">
								<div class="span12">
									<?php echo RedshopbLayoutHelper::render('checkout.vendor_info', $deliverySettings); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="row-fluid">
							<div class="span12" id="redshopb-delivery-info-comments">
								<?php echo RedshopbLayoutHelper::render('checkout.additional_info', $deliveryFormSettings); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo $terms; ?>
		<?php echo $displayData['checkoutFields']->type->input; ?>
		<input type="hidden" name="boxchecked" value="1">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<div class="row-fluid">
		<div class="span12">
			<div id="shopcart">
				<?php
					$customerBasket = RedshopbLayoutHelper::render('checkout.customer_basket', $displayData);
					Factory::getApplication()->triggerEvent('onRedshopbRenderCustomerBasket', array(&$customerBasket));
					echo $customerBasket;
				?>
			</div>
		</div>
	</div>
	<div id="redshopb-one-page-bottom-toolbar" class="row-fluid">
		<div class="span12">
			<?php echo $displayData['toolbar']->render(); ?>
		</div>
	</div>
<?php echo $termsAndConditions; ?>
<?php echo $layout; ?>

<?php echo RedshopbLayoutHelper::render('checkout.address.controls');
