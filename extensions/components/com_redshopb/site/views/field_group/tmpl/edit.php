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
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

$detailFieldset = $this->form->getFieldset('details');
$action         = RedshopbRoute::_('index.php?option=com_redshopb&view=field_group&layout=edit&id=' . $this->item->id);
$isNew          = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="row">
	<div class="col-md-12">
		<ul class="nav nav-tabs" id="productTabs">
			<li class="active">
				<a href="#details" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
				</a>
			</li>
			<?php if (!$isNew): ?>
				<li>
					<a href="#fieldAssociations" data-toggle="tab" data-ajax-tab-load="true">
						<?php echo Text::_('COM_REDSHOPB_FIELD_ASSOCIATION_TITLE'); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</div>

<div class="tab-content">
		<div class="tab-pane active" id="details">
		<div class="redshopb-field">
			<div class="row">
				<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
					  class="form-validate form-horizontal redshopb-field-form">
					<?php foreach ($detailFieldset AS $field):
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
		</div>
	</div>

<?php if ($this->item->id): ?>
	<div class="tab-pane " id="fieldAssociations">
	<?php
		echo RedshopbLayoutHelper::render('fields.associatefields',
			array(
				'item_id' => $this->item->id,
				'fields' => $this->fields,
				'unassociatedFields' => $this->unassociatedFields,
				'form' => $this->form,
				'controller' => 'field_group'
			)
		);
	?>
	</div>
<?php endif; ?>
</div>
