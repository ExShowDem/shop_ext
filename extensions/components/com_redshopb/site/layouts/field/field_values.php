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

extract($displayData);

$showToolbar = isset($showToolbar) ? $showToolbar : false;
$return      = isset($return) ? $return : null;

if (!isset($action))
{
	$action = RedshopbRoute::_('index.php?option=com_redshopb&view=field_values');
}

$fieldId     = Factory::getApplication()->input->getInt('id');
$listOrder   = $state->get('list.ordering');
$listDirn    = $state->get('list.direction');
$saveOrder   = $listOrder == 'ordering';
$defaultText = array(Text::_('JNO'), Text::_('JYES'));

// Render the toolbar?
if ($showToolbar)
{
	echo RedshopbLayoutHelper::render('field_values.toolbar', $displayData);
}

$searchToolsOptions = array(
	"orderFieldSelector" => "#list_fullordering",
	"limitFieldSelector" => "#field_value_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($activeFilters)
);
	?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>"
	  method="post">
	<?php
	echo RedshopbLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => (object) array(
				'filterForm' => $filterForm,
				'activeFilters' => $activeFilters
			),
			'options' => $searchToolsOptions
		)
	);
	?>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="fieldValuesList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_SCOPE_ORDER', 'fv.ordering', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_VALUE_NAME_LABEL', 'fv.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_VALUE_VALUE_LABEL', 'fv.value', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_VALUE_DEFAULT_LABEL', 'fv.default', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
			</tr>
			</thead>
			<?php if ($items): ?>
				<tbody>
				<?php foreach ($items as $i => $item): ?>
					<tr>
						<td>
							<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
						</td>
						<td class="order nowrap center hidden-phone">
							<span class="sortable-handler">
								<span class="icon-move"></span>
							</span>
							<input type="text" style="display:none" name="order[]" value="<?php echo $item->ordering; ?>" />
						</td>
						<td>
							<?php
							$itemUrl = 'index.php?option=com_redshopb&task=field_value.edit&id=' . $item->id
								. '&jform[field_id]=' . $fieldId;

							if ($return)
							{
								$itemUrl .= '&return=' . $return;
							}
							?>
							<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
								<?php echo $item->name ?>
							</a>
						</td>
						<td>
							<?php echo $item->value; ?>
						</td>
						<td>
							<?php echo $defaultText[(int) $item->default];?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>

		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="field.saveModelState">
		<?php if ($return) : ?>
			<input type="hidden" name="return" value="<?php echo $return ?>">
		<?php endif; ?>
		<input type="hidden" name="jform[field_id]" value="<?php echo $fieldId ?>">
		<input type="hidden" name="from_field" value="1">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
