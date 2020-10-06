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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

$action         = RedshopbRoute::_('index.php?option=com_redshopb&view=field&layout=edit&id=' . $this->item->id);
$isNew          = (int) $this->item->id <= 0;
$detailFieldset = $this->form->getFieldset('details');
RedshopbHtml::loadFooTable();
?>

<script type="text/javascript">
	var rsbftPhone = 0;
	var rsbftTablet = 0;
</script>

<?php

$saveOrderingUrl = 'index.php?option=com_redshopb&task=field_values.saveOrderAjax&tmpl=component';
HTMLHelper::_('rsortablelist.sortable', 'fieldValuesList', 'adminFormFieldValues', 'asc', $saveOrderingUrl);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
$input = Factory::getApplication()->input;
$tab   = $input->getString('tab');

if ($this->item->id) : ?>
	<script type="text/javascript">
		var loadedTabs = {};
		(function ($) {
			function ajaxFieldTabsetup(tabName) {
				$('a[href="#' + tabName + '"]').on('click', function (e) {

					// Tab already loaded
					if (loadedTabs[tabName] == true) {
						return true;
					}

					var tabNameFixed;

					switch(tabName)
					{
						case 'field_values':
							tabNameFixed = 'FieldValues';
							break;
						default:
							tabNameFixed = tabName;
					}

					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=field.ajax' + tabNameFixed + '&view=field&id=<?php echo $this->item->id; ?>',
						type: 'POST',
						data : {
							"<?php echo Session::getFormToken(); ?>": 1
						},
						beforeSend: function (xhr) {
							$('.' + tabName + '-content .spinner').show();
							$('#fieldTabs').addClass('opacity-40');
						}
					}).done(function (data) {
						$('.' + tabName + '-content .spinner').hide();
						$('#fieldTabs').removeClass('opacity-40');
						$('.' + tabName + '-content').html(data);
						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip();
						loadedTabs[tabName] = true;
						rsbftPhone = 480;
						rsbftTablet = 768;
						initFootableRedshopb();

						if (tabName == 'field_values'){
							new $.JSortableList('#fieldValuesList tbody','adminFormFieldValues','asc' , '<?php echo $saveOrderingUrl; ?>','','');
						}
					});
				})
			}

			$(document).ready(function () {
				ajaxFieldTabsetup('field_values');
	<?php if ($tab) : ?>
				$('#fieldTabs a[href="#<?php echo $tab ?>"]').trigger('click');
	<?php endif; ?>
			});
		})(jQuery);
	</script>
<?php endif; ?>
<div class="redshopb-field">
	<div class="row-fluid">
		<ul class="nav nav-tabs" id="fieldTabs">
			<li class="active">
				<a href="#field_details" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_FIELD_DETAILS'); ?></a>
			</li>
	<?php if ($this->item->id) : ?>
				<li>
					<a href="#field_values" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_FIELD_VALUES'); ?></a>
				</li>
	<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="field_details">
				<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
					  class="form-validate form-horizontal redshopb-field-form">
	<?php foreach ($detailFieldset AS $field) :
		$backWSValueButton = $this->form->getBackWSValueButton($field->fieldname, $field->group);
		echo $field->renderField(
			array(
			'backWSValueButton' => $backWSValueButton,
			'class' => $backWSValueButton ? 'controlGroupForOverrideField' : ''
			)
		);
	endforeach; ?>
					<!-- hidden fields -->
					<input type="hidden" name="option" value="com_redshopb">
					<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
					<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
	<?php if ($this->item->id) : ?>
				<div class="tab-pane" id="field_values">
	<?php if ($this->item->field_value_xref_id != 0) : ?>
						<div class="alert alert-warning">
							<h4><?php echo Text::_('COM_REDSHOPB_EDITING_XREF_HEADER'); ?></h4>
							<p><?php echo Text::_('COM_REDSHOPB_EDITING_XREF_BODY'); ?></p>
						</div>
	<?php endif; ?>
					<div class="container-fluid">
						<div class="row-fluid field_values-content">
							<div class="spinner pagination-centered">
	<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?>
							</div>
						</div>
					</div>
				</div>
	<?php endif; ?>
		</div>
	</div>
</div>
