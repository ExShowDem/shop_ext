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

$config    = RedshopbEntityConfig::getInstance();
$showTitle = $displayData['showTitle'];
$fields    = $displayData['fields'];
?>
<?php if ($showTitle) : ?>
	<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_ADDITIONAL_INFO', true); ?></h4>
<?php endif;?>
<div class="row-fluid">
	<div class="span12">
		<div class="control-group">
			<div class="control-label">
				<?php echo $fields->requisition->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->requisition->input; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $fields->comment->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->comment->input; ?>
			</div>
		</div>
		<?php if ($config->get('show_invoice_email_field', 0)) : ?>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox">
						<?php echo $fields->invoice_email_toggle->input; ?>&nbsp;
						<?php echo Text::_('COM_REDSHOPB_COMPANY_INVOICE_EMAIL_TOGGLE'); ?>
					</label>
				</div>
			</div>

			<div class="control-group invoice_email_group" style="display: none;">
				<div class="control-label">
					<?php echo $fields->invoice_email->label; ?>
				</div>
				<div class="controls">
					<?php echo $fields->invoice_email->input; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
