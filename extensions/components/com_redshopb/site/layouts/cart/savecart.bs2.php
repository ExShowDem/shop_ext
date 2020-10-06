<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$action = Route::_($displayData['formAction']);

$customerId   = $displayData['customerId'];
$customerType = $displayData['customerType'];
$orderId      = $displayData['orderId'];
$modalId      = 'js-saveCartModal_' . $orderId . '_' . $customerId . '_' . $customerType;

$return = $displayData['return'];

/** @var RedshopbModelCarts $savedCartsModel */
$savedCartsModel = RedshopbModel::getFrontInstance('Carts', array('ignore_request' => true));
$savedCarts      = $savedCartsModel->getItems();
$options         = array();

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
<form action="<?php echo $action;?>" style="margin:0 0;">
	<div id="<?php echo $modalId;?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="saveCartModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h3 id="saveCartModalLabel"><?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_MODAL_TITLE'); ?></h3>
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
								   id="<?php echo $modalId . '_name';?>"
								   class="input required"
								   name="name"
								   required="true"
								   aria-required="true"
								   placeholder="<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART_MODAL_CART_INPUT_NAME_DESC'); ?>" />
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="javascript:void(0);" class="btn btn-danger" data-dismiss="modal" aria-hidden="true">
						<i class="icon-remove"></i>&nbsp
						<?php echo Text::_('JTOOLBAR_CLOSE');?>
					</a>
					<button id="<?php echo $modalId . '_save';?>" class="btn btn-primary"><i class="icon-save">
						</i>&nbsp;<?php echo Text::_('JTOOLBAR_SAVE');?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="task" value="cart.saveCart">
	<input type="hidden" name="orderId" value="<?php echo $orderId ?>">
	<input type="hidden" name="return" value="<?php echo $return;?>">
	<input type="hidden" name="customer" value="<?php echo base64_encode($customerId . '_' . $customerType);?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<button class="btn btn-default btn-small" id="save-cart-btn" type="button" data-toggle="modal" data-target="#<?php echo $modalId;?>">
	<i class="icon-save"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_CART') ?>
</button>
<script>
	jQuery(document).ready(function()
	{
		jQuery('#<?php echo $modalId;?> select[name="savedCartId"]').on('change', function(event)
		{
			var targ = redSHOPB.form.getEventTarget(event);
			var selected = targ.find(':selected');

			var form = targ.closest('form');
			var name = form.find('input[name="name"]');

			if(selected.val() == 'NEW')
			{
				name.val('');
				name.focus();
				return;
			}

			name.val(selected.text());
			name.focus();
		});
	})
</script>
