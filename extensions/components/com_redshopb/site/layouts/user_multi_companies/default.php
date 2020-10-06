<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

$items      = $data['items'];
$listDirn   = $data['listDirn'];
$listOrder  = $data['listOrder'];
$pagination = $data['pagination'];
$formName   = $data['formName'];
?>

<?php if (empty($items)): ?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php
else :
?>
	<div class="redshopb-user_multi_companies-table">
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="user_multi_companyList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'umc.state', $listDirn, $listOrder) ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_LABEL', 'company_name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone, tablet">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ROLE_LABEL', 'role_name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_USER_MULTI_COMPANY_USER_NAME_LABEL', 'user_name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th width="1%" class="nowrap" data-hide="phone, tablet">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
			</tr>
			</thead>
			<?php
			if ($items)
					:
			?>
			<tbody>
			<?php
			foreach ($items as $i => $item)
				:
				?>
			<?php
			$canChange  = RedshopbHelperACL::getPermission('manage', 'company', Array('edit.state'), true);
			$canEdit    = RedshopbHelperACL::getPermission('manage', 'company', Array('edit','edit.own'), true);
			$canCheckin = $canEdit;
			?>
			<tr>
			<td>
				<?php if (!$item->main) : ?>
								<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php if (!$item->main) : ?>
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'carts.', $canChange, 'cb') ?>
				<?php endif; ?>
			</td>
			<td>
				<?php if ($canEdit && !$item->main) : ?>
								<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=user_multi_company.edit&id=' . $item->id); ?>">
				<?php endif; ?>
						<?php echo $item->company_name; ?>

						<?php if ($canEdit) : ?>
								</a>
						<?php endif; ?>
			</td>
			<td>
				<?php echo $this->escape($item->role_name); ?>
			</td>
			<td>
				<?php echo $this->escape($item->user_name); ?>
			</td>
			<td>
				<?php echo $item->id; ?>
			</td>
				</tr>
			<?php
			endforeach;
			?>
			</tbody>
			<?php
			endif;
			?>
		</table>
	</div>
	<div class="redshopb-user_multi_companies-pagination">
		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	</div>
<?php
endif;
