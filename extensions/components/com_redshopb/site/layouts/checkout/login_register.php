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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

/** @var RedshopbModelB2BUserRegister $registerModel */
$registerModel = RedshopbModel::getInstance('B2BUserRegister', 'RedshopbModel');
$registerForm  = $registerModel->getForm();

$registerModel->set('formName', 'login');
$registerModel->set('context', 'com_redshopb.edit.b2buserregister.login');
$registerModel->set('control', null);
/** @var Form $loginForm */
$loginForm = $registerModel->getForm();

$input  = Factory::getApplication()->input;
$view   = $input->getCmd('view', 'shop');
$layout = $input->getCmd('layout', 'cart');
$active = $input->getCmd('active', 'guest');

$url                = 'index.php?option=com_redshopb&view=' . $view . '&layout=' . $layout;
$successReturn      = RedshopbHelperRoute::getRoute($url);
$failedReturnPrefix = $url . '&active=';

$config               = RedshopbEntityConfig::getInstance();
$checkoutRegistration = $config->get('checkout_registration', 'registration_required');

$isGuest       = Factory::getUser()->guest;
$showLoginForm = ($checkoutRegistration != 'registration_none' && $isGuest);

if ($checkoutRegistration == 'registration_required')
{
	$active = 'login';
}

// Settings for user.register
$registerSettings = array(
	'form'      => $registerForm,
	'action'    => RedshopbRoute::_('index.php?option=com_redshopb&view=b2buserregister&active=register'),
	'cancel'    => RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart'),
	'return'    => base64_encode($successReturn),
	'returnFail' => base64_encode(RedshopbHelperRoute::getRoute($failedReturnPrefix . 'register'))
);

// Settings for user.login
$loginSettings = array(
	'form'          => $loginForm,
	'formName'      => 'redshopLoginForm',
	'returnSuccess' => base64_encode($successReturn),
	'returnFail'    => base64_encode(RedshopbHelperRoute::getRoute($failedReturnPrefix . 'login'))
);
?>
<script type="text/javascript">
	jQuery(document).ready(function ()
	{
		jQuery('#redshopb-delivery-b2c-accordion div.accordion-body').on('hidden', function(event)
			{
				var collaps = jQuery('#redshopb-delivery-b2c-accordion div.accordion-body.in');

				if(collaps.length === 0)
				{
					var targ = redSHOPB.form.getEventTarget(event);
					targ.collapse('toggle');
				}
			}
		)
	});
</script>
<div class="row">
	<div class="col-md-12">
		<div class="accordion" id="redshopb-delivery-b2c-accordion">
			<?php if ($showLoginForm):?>
				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#redshopb-delivery-b2c-accordion" href="#collapseLogin">
							<h4><?php echo Text::_('COM_REDSHOPB_LOGIN_FORM_NAME') ?></h4>
						</a>
					</div>
					<div id="collapseLogin" class="accordion-body collapse <?php echo $active == 'login' ? 'in' : ''; ?>">
						<div class="accordion-inner">
							<?php echo RedshopbLayoutHelper::render('user.login', $loginSettings); ?>
						</div>
					</div>
				</div>

				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#redshopb-delivery-b2c-accordion" href="#collapseRegister">
							<h4><?php echo Text::_('COM_REDSHOPB_B2BUSER') ?></h4>
						</a>
					</div>
					<div id="collapseRegister" class="accordion-body collapse <?php echo $active == 'register' ? 'in' : ''; ?>">
						<div class="accordion-inner">
							<?php echo RedshopbLayoutHelper::render('user.register', $registerSettings); ?>
						</div>
					</div>
				</div>
			<?php endif;?>

			<?php if ($checkoutRegistration !== 'registration_required'):?>
				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#redshopb-delivery-b2c-accordion" href="#collapseGuestCheckout">
							<h4><?php echo Text::_('COM_REDSHOPB_GUEST_CHECKOUT') ?></h4>
						</a>
					</div>
					<div id="collapseGuestCheckout" class="accordion-body collapse <?php echo $active == 'guest' ? 'in' : ''; ?>">
						<div class="accordion-inner">
							<script type="text/javascript">
								jQuery(document).ready(function()
								{
									redSHOPB.shop.checkout.checkCountry();

									jQuery(document).on('change', '#country_id', function (event) {
										redSHOPB.shop.checkout.checkCountry();
									});

									var guestForm = jQuery('#guestCheckoutForm');
									guestForm.find('input').attr('form', 'adminForm');
									guestForm.find('select').attr('form', 'adminForm');

									if (redSHOPB.form.clientSupportsFormAttributes() == false)
									{
										redSHOPB.form.forgeFormAttributes('adminForm');

										var countryIdInput = guestForm.find('input[name="country_id"]');

										if (countryIdInput.attr('type') === 'hidden')
										{
											var adminFormCountryId = redSHOPB.form.getInput('country_id', jQuery('#adminForm'));
											adminFormCountryId.val(countryIdInput.val());
										}
									}

								});
							</script>
							<form id="guestCheckoutForm">
								<?php echo RedshopbLayoutHelper::render('checkout.guest_checkout_form', $displayData); ?>
							</form>
						</div>
					</div>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>
