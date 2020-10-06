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
use Joomla\CMS\Form\Form;

/**
 * Layout variables
 * ======================================
 *
 * @var  array  $displayData List of available data.
 * @var  Form   $form        Form object.
 * @var  object $item        Media data object.
 * @var  string $formName    Form custom name.
 */
extract($displayData);

/** @var RedshopbModelMedia $model */
$model = RModelAdmin::getInstance('Media', 'RedshopbModel');

// Gets sync reference
$syncReference = RedshopbHelperSync::getEnrichmentBase($model);
?>
<div class="image-edit-area">
	<div>
		<h3><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_OPTIONS'); ?></h3>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('alt'); ?>
				</div>
				<div class="controls">
					<?php echo $form->getInput('alt'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('view'); ?>
				</div>
				<div class="controls">
					<?php echo $form->getInput('view'); ?>
				</div>
			</div>

			<?php if (!empty($form->getInput('ordering'))) :?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('ordering'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('ordering'); ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('attribute_value_id'); ?>
				</div>
				<div class="controls">
					<?php echo $form->getInput('attribute_value_id'); ?>
				</div>
			</div>

			<?php if ($syncReference != '') :
			?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('sync_related_id'); ?>
				</div>
				<div class="controls">
					<?php echo $form->getInput('sync_related_id'); ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $form->getInput('state'); ?>
				</div>
			</div>
			<?php echo $form->getInput('id'); ?>
		</div>
		<div class="span6">
			<div class="control-group">
				<div class="control-label">
					<?php echo $form->getLabel('productImage'); ?>
					<?php echo $form->getBackWSValueButton('name'); ?>
				</div>
				<div class="controls product-media-manager-container">
					<?php if ($item->name): ?>
						<img class="bigThumb"
							 alt="<?php echo RedshopbHelperThumbnail::safeAlt($item->alt) ?>"
							 src="<?php echo RedshopbHelperThumbnail::originalToResize($item->name, 144, 144, 100, 0, 'products', false, $item->remote_path); ?>"/>
					<?php endif; ?>
					<br/><br/>
					<?php echo $form->getInput('productImage'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<button class="btn btn-large btn-success product-image-save-button" type="button">
			<?php echo Text::_('JAPPLY'); ?>
			<i class="icon-save"></i>
		</button>
	</div>
</div>
