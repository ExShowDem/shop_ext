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
use Joomla\CMS\Factory;

JLoader::import('helpers.layout', JPATH_COMPONENT_ADMINISTRATOR);

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action       = RedshopbRoute::_('index.php?option=com_redshopb&view=layouts');
$listOrder    = $this->state->get('list.ordering');
$listDirn     = $this->state->get('list.direction');
$layoutHelper = new RedshopbHelperLayout;
$user         = Factory::getUser();

// Global ACL permissions since there is no company property over layouts
$canChange  = RedshopbHelperACL::getPermission('manage', 'layout', Array('edit.state'), false);
$canEdit    = RedshopbHelperACL::getPermission('manage', 'layout', Array('edit', 'edit.own'), false);
$canCheckin = $canEdit;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-layouts">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<div class="row-fluid">
			<?php
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => $this,
					'options' => array(
						'filterButton' => false,
						'searchField' => 'search_layouts',
						'searchFieldSelector' => '#filter_search_layouts',
						'limitFieldSelector' => '#list_layout_limit',
						'activeOrder' => $listOrder,
						'activeDirection' => $listDirn
					)
				)
			);
			echo $this->form->getInput('layouts_order');
			?>
		</div>
		<?php if (empty($this->items)): ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else: ?>
			<div class="row-fluid">
				<div class="redshopb-layouts-data">
					<?php foreach ($this->items as $i => $row): ?>
						<?php if ($i % 3 == 0): ?>
							<?php $end = $i + 2; ?>
							<div class="row-fluid">
						<?php endif; ?>
						<div class="span4">
							<div class="layout-item">
								<?php
								echo RedshopbLayoutHelper::render(
									'layout.item',
									array(
										'layout' => $row,
										'no'     => $i
									)
								);
								?>
							</div>
						</div>
						<?php if ($i == $end): ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="redshopb-layouts-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
