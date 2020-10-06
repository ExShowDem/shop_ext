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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=product_accessory&layout=edit');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$input          = Factory::getApplication()->input;
$productId      = RedshopbInput::getField('product_id');
$attributeValue = $input->get('attribute_value');
$isNew          = (int) $this->item->id <= 0;

echo $attributeValue;
?>
<div class="redshopb-product_accessory">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-product_attribute-form" enctype="multipart/form-data">
		<div class="row-fluid">
			<hr />
			<div class="tab-content">
				<div class="tab-pane active" id="productAccessoryDetails">
					<?php if ($productId): ?>
						<input type="hidden" name="jform[product_id]" value="<?php echo $productId; ?>">
					<?php else: ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('product_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('product_id'); ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('hide_on_collection'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('hide_on_collection'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('selection'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('selection'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('price'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('price'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('description'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('description'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('state'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('state'); ?>
						</div>
					</div>
				</div>
			</div>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="jform[accessory_product_id]" value="<?php echo $this->item->accessory_product_id; ?>">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="return" value="<?php echo $input->get('return') ?>">
		<input type="hidden" name="from_product" value="<?php echo $input->getInt('from_product', 0) ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
