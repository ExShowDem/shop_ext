<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('rbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$orderings      = $displayData['orderings'];
$currentProduct = $displayData['product'];
$disabled       = RedshopbEntityProduct::getInstance($currentProduct)->canReadOnly();
?>
<div class="row-fluid">
	<?php if (empty($orderings)): ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<h3><?php echo Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY') ?></h3>
			</div>
		</div>
	<?php else: ?>
		<div class="span12 adapt-inputs">
			<?php foreach ($orderings as $catId => $products) :?>
				<div class="control-group">
					<div class="control-label">
						<?php
						$product  = array_shift($products);
						$options  = array();
						$catName  = $product->cname;
						$counter  = 1;
						$selected = null;

						if ($product->pid == $currentProduct)
						{
							$options[] = HTMLHelper::_('select.option', $product->ordering, ($counter++) . '. ' . $product->pname, array('disable' => true));
							$selected  = $product->ordering;
						}
						else
						{
							$options[] = HTMLHelper::_('select.option', $product->ordering, ($counter++) . '. ' . $product->pname);
						}
						?>
						<label id="jform_ordering_<?php echo $catId; ?>-lbl"
							   class="hasPopover" title=""
							   data-content="<?php echo Text::_('COM_REDSHOPB_PRODUCT_ORDERING_DESC'); ?>"
							   data-original-title="<?php echo $catName; ?>"
							   for="jform_ordering_<?php echo $catId; ?>">
							<?php echo $catName; ?>
						</label>
					</div>
					<div class="controls">
						<?php
						foreach ($products as $product)
						{
							if ($product->pid == $currentProduct)
							{
								$options[] = HTMLHelper::_('select.option', $product->ordering, ($counter++) . '. ' . $product->pname, array('disable' => true));
								$selected  = $product->ordering;
							}
							else
							{
								$options[] = HTMLHelper::_('select.option', $product->ordering, ($counter++) . '. ' . $product->pname);
							}
						}
						?>
						<?php echo HTMLHelper::_(
							'select.genericlist', $options, 'jform[ordering][' . $catId . ']',
							'class="select"' . ($disabled ? ' disabled' : ''), 'value', 'text', $selected, 'jform_ordering_' . $catId
						); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
