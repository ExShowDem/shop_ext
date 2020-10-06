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

$view    = $displayData['view'];
$canView = $displayData['canView'];
$title   = $displayData['title'];
$reports = $displayData['reports'];
?>

<table class="table table-hover">
	<thead>
	<tr>
		<th class="nowrap center">
			<h3><?php echo $title; ?></h3>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($reports as $report) : ?>
		<tr>
			<td>
				<?php $item = $view->getReportItem($report); ?>

				<?php if ($canView) : ?>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=report_' . $report); ?>">
				<?php endif; ?>
					<?php echo Text::_('COM_REDSHOPB_REPORTS_' . $report . '_TITLE'); ?>

					<?php if ($canView) : ?>
				</a>
					<?php endif; ?>
				- <?php echo Text::_('COM_REDSHOPB_REPORTS_' . $report . '_DESC'); ?>
				<br />
				<span class="label label-<?php echo !empty($item->id) ? 'success' : 'warning'; ?>">
					<?php echo Text::sprintf(
						'COM_REDSHOPB_REPORTS_LAST_TIME_GENERATED',
						!empty($item->id) ? HTMLHelper::_('date', $item->modified_date, Text::_('DATE_FORMAT_LC4'), null) : Text::_('COM_REDSHOPB_REPORTS_NEVER')
					); ?>
				</span>
				<span class="label label-<?php echo !empty($item->id) ? 'success' : 'warning'; ?>">
					<?php echo Text::sprintf('COM_REDSHOPB_REPORTS_NUMBER_OF_ROWS', $item->rows); ?>
				</span>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
