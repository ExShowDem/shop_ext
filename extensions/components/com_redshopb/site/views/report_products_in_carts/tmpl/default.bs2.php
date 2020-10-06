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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();
RHelperAsset::load('reports.css', 'com_redshopb');

$chartFilters = $this->filterForm->getGroup('chart');
$action       = RedshopbRoute::_('index.php?option=com_redshopb&view=report_products_in_carts');
$listOrder    = $this->state->get('list.ordering');
$listDirn     = $this->state->get('list.direction');
$saveOrder    = $listOrder == 'ordering';
$totalSums    = array();
$print        = Factory::getApplication()->input->get('print', 0);
?>
<?php if ($print) : ?>
	<script type="text/javascript">
		(function ($) {
			$(document).ready(function () {
				window.print();
			});
		})(jQuery);
	</script>
	<h1><?php echo $this->getTitle(); ?></h1>
<?php else:
	echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
endif; ?>
<div class="redshopb-report_products_in_carts">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm form-horizontal" id="adminForm" method="post">

		<?php if (!$print) : ?>
		<div class="row">
			<div class="span6">
				<?php
				echo RedshopbLayoutHelper::render(
					'reports.searchtools.default',
					array(
						'view' => $this,
						'options' => array(
							'searchField' => 'search_report_products_in_carts',
							'searchFieldSelector' => '#filter_search_report_products_in_carts',
							'limitFieldSelector' => '#list_report_products_in_carts_limit',
							'activeOrder' => $listOrder,
							'activeDirection' => $listDirn
						)
					)
				);
				?>
			</div>
			<div class="span6">
				<div class="js-stools clearfix">
					<div class="hidden-phone clearfix">
						<?php if ($chartFilters) :
							foreach ($chartFilters as $fieldName => $field) : ?>
								<div class="js-stools-field-filter">
									<?php echo $field->input; ?>
								</div>
							<?php endforeach;
						endif; ?>
					</div>
				</div>
				<?php echo RedshopbLayoutHelper::render(
					'chart.chart',
					array(
						'view' => $this,
						'options' => array(
							'chartOptions' => array(
								'legendTemplate' => ''
							),
							'chartType' => $this->chartType,
							'chartData' => $this->chartData,
							'chartId' => 'reportProductsInCartsChart',
						)
					)
				); ?>
			</div>
		</div>
		<?php echo RedshopbLayoutHelper::render('reports.toolbar', array('view' => $this, 'action' => $action)); ?>
		<hr/>
		<?php endif; ?>
		<div class="redshopb-report_products_in_carts-table">
			<table class="table table-hover js-redshopb-footable table-condensed" id="reportList">
				<thead>
				<tr>
					<?php foreach ($this->tableColumns as $cell) : ?>
						<th class="nowrap">
							<?php echo Text::_($cell['title']); ?>
						</th>
					<?php endforeach; ?>
				</tr>
				</thead>
				<?php if ($this->items): ?>
					<tbody>
					<?php foreach ($this->items as $i => $item): ?>
						<tr>
							<?php foreach ($this->tableColumns as $cellKey => $cell) :
								if (empty($cell['noSum'])) :
									$currency = (isset($item->currency) ? $item->currency : '');

									if (!isset($totalSums[$currency][$cellKey])):
										$totalSums[$currency][$cellKey] = 0;
									endif;

									$totalSums[$currency][$cellKey] += $item->{$cellKey};
								endif; ?>
								<td class="nowrap">
									<?php echo $this->getFormattedValue($cellKey, $item->{$cellKey}); ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
					<?php foreach ($totalSums as $currency => $totalSum) : ?>
						<tr>
							<th>
								<?php echo Text::_('COM_REDSHOPB_REPORT_TOTAL'); ?>
							</th>
							<?php foreach ($this->tableColumns as $cellKey => $cell) :
								if ($cell == reset($this->tableColumns)) :
									continue;
								endif;
								?>
								<th class="nowrap">
									<?php if (empty($cell['noSum'])) :
										echo $this->getFormattedValue($cellKey, $totalSum[$cellKey]);
									endif; ?>
								</th>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tfoot>
				<?php endif; ?>
			</table>
		</div>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
