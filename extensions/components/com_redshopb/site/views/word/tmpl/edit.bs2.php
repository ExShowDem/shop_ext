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

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=word');
$isNew  = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	jQuery(document).ready(function () {
		var radioMainWord = jQuery('.radioMainWord input:checked').val();
		mainWordInit(radioMainWord);

		jQuery('#redshopb-word').on('click', '.radioMainWord input', function () {
			mainWordInit(jQuery(this).val());
		});

		function mainWordInit(status) {
			if (status == 1) {
				jQuery('.mainWordDiv').removeClass('hide');
				jQuery('.notMainWordDiv').addClass('hide');
			} else {
				jQuery('.mainWordDiv').addClass('hide');
				jQuery('.notMainWordDiv').removeClass('hide');
			}
		}
	});
</script>
<div id="redshopb-word">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		  class="form-validate form-horizontal redshopb-word-form" enctype="multipart/form-data">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('word'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('word'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('shared'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('shared'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('main_word'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('main_word'); ?>
			</div>
		</div>
		<div class="mainWordDiv">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('synonyms'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('synonyms'); ?>
				</div>
			</div>
		</div>
		<div class="notMainWordDiv">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('meanings'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('meanings'); ?>
				</div>
			</div>
			<div class="wholeMeaningsDiv">
				<?php
				$meanings = $this->form->getValue('meanings');

				if (!empty($meanings) && !is_array(reset($meanings))):?>
					<h3><?php echo Text::_('COM_REDSHOPB_WORD_MEANING_INFO'); ?></h3>

					<?php foreach ($meanings as $meaning): ?>
						<div class="oneMeaningDiv alert alert-info">
							<h4><?php echo Text::_('COM_REDSHOPB_WORD_MEANING') . RedshopbEntityWord::getInstance($meaning)->get('word'); ?></h4>
							<?php

							$synonyms = RedshopbEntityWord::getInstance($meaning)->getSynonymsIds();

							if (count($synonyms)):
								echo Text::_('COM_REDSHOPB_WORD_RELATED_SYNONYMS');

								foreach ($synonyms as $synonym):?>
									<span class="badge badge-info">
											<?php echo RedshopbEntityWord::getInstance($synonym)->get('word'); ?>
										</span>
									<?php
								endforeach;
							else:
								echo Text::_('COM_REDSHOPB_WORD_RELATED_SYNONYMS_NOT_FOUND');
							endif; ?>
						</div>
						<?php
					endforeach;
				endif; ?>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" id="wordId">
		<input type="hidden" name="task" value="" id="formTask">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
