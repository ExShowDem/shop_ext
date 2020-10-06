<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($data['options']['filtersHidden']) ? $data['options']['filtersHidden'] : empty($data['view']->activeFilters),
	'defaultLimit'        =>
		isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : Factory::getApplication()->getCfg('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
RHtml::_('rsearchtools.form', $formSelector, $data['options']);

?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar">
			<?php echo RedshopbLayoutHelper::render('reports.searchtools.default.bar', $data); ?>

			<div class="clearfix">
				<?php echo RedshopbLayoutHelper::render('reports.searchtools.default.filters', $data); ?>
			</div>
		</div>

		<div class="clear btn-group pull-right">
		</div>
		<div class="btn-group pull-right">
			<button type="submit" class="btn hasTooltip" title="<?php echo RHtml::tooltipText('COM_REDSHOPB_REPORTS_SHOW_REPORT'); ?>">
				<i class="icon-table"></i>
				<?php echo Text::_('COM_REDSHOPB_REPORTS_SHOW_REPORT');?>
			</button>
		</div>
		<div class="btn-group pull-right">
			<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo RHtml::tooltipText('COM_REDSHOPB_REPORTS_CLEAR_FILTERS'); ?>">
				<i class="icon-eraser"></i>
				<?php echo Text::_('COM_REDSHOPB_REPORTS_CLEAR_FILTERS');?>
			</button>
		</div>
		<div class="js-stools-container-list hidden-phone hidden-tablet">
			<?php echo RedshopbLayoutHelper::render('reports.searchtools.default.list', $data); ?>
		</div>
	</div>
</div>
