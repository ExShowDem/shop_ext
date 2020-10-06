<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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
	<div class="redshopb-addresses-table">
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="addressList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">

				</th>
				<th class="nowrap center" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS_FOR_LABEL', 'entity', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS_LABEL', 'address', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone, tablet">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS2_LABEL', 'address2', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_LABEL', 'country', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" style="width: 8%" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ZIP_LABEL', 'zip', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_CITY_LABEL', 'city', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone, tablet">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TYPE', 'type', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
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
			$canChange  = RedshopbHelperACL::getPermission('manage', 'address', Array('edit.state'), true);
			$canEdit    = RedshopbHelperACL::getPermission('manage', 'address', Array('edit','edit.own'), true);
			$canCheckin = $canEdit;
			?>
			<tr>
			<td>
				<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
			</td>
			<td>
				<?php
				if ($item->type == 1)
						:
				?>
				<i class="icon-truck text-info" title="<?php echo Text::_('JOPTION_SELECT_ADDRESS_SHIPPING') ?>"></i>
					<?php
				elseif ($item->type == 3)
						:
							?>
						<i class="icon-truck" title="<?php echo Text::_('JOPTION_SELECT_ADDRESS_DEFAULT_SHIPPING') ?>"></i>
							<?php
				endif;
					?>
				</td>
				<td>
					<?php
					if ($item->checked_out)
						:
				?>
				<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'addresses.', $canCheckin, 'cb', $formName); ?>
						<?php
					endif;

					if ($canEdit)
						:
				?>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=address.edit&id=' . $item->id); ?>">
				<?php
					endif;
					?>
					<?php
					if ($item->name == '')
						{
						echo $this->escape($item->entity);
					}
					else
						{
						echo $this->escape($item->name);
					}
						?>
						<?php
						if ($canEdit)
							:
					?>
				</a>
					<?php
						endif;
						?>
						</td>
						<td>
					<?php echo $this->escape($item->entity); ?>
						</td>
						<td>
						<?php echo $this->escape($item->address); ?>
						</td>
						<td>
						<?php echo $this->escape($item->address2); ?>
						</td>
						<td>
						<?php echo $this->escape(Text::_($item->country)); ?>
						</td>
						<td>
						<?php echo $this->escape($item->zip); ?>
						</td>
						<td>
						<?php echo $this->escape($item->city); ?>
						</td>
						<td>
						<?php echo RedshopbEntityAddress::getInstance()->getTypeName($item->type); ?>
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
	<div class="redshopb-addresses-pagination">
		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	</div>
<?php
endif;
