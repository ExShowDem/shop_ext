<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$items  = $displayData['children'];
$parent = $displayData['parent'];
?>

<?php
foreach ($items as $i => $item)
:
?>
<?php
$order      = ($item instanceof RedshopbEntityOrder) ? $item : RedshopbEntityOrder::getInstance($item->id)->bind($item);
$canChange  = RedshopbHelperACL::getPermission('manage', 'order', array('edit.state'), true);
$canEdit    = RedshopbHelperACL::getPermission('manage', 'order', array('edit', 'edit.own'), true) && ($item->status == 0);
$canCheckin = $canEdit;
$sentOrder  = null;

if (!is_null($item->log_type) && !empty($item->log_type))
{
	$logType = strtoupper($item->log_type);

	if ($logType == 'EXPEDITE')
	{
		$sentOrder       = RedshopbHelperOrder::getExpeditedOrder($item->id);
		$sentOrderString = str_pad($sentOrder, 6, '0', STR_PAD_LEFT);
	}
}
else
{
	$logType = 'NONE';
}

$isParent = '';

if (in_array($logType, array('EXPEDITE', 'COLLECT')))
{
	$isParent = 'js-redshopb-parent';
}

switch ($item->status)
{
	case 0:
		$trClass = 'warning';
		break;
	case 1:
	case 4:
	case 5:
		$trClass = 'success';
		break;
	case 2:
	case 3:
		$trClass = 'error';
		break;
	case 6:
	case 7:
		$trClass = 'info';
		break;
	default:
		$trClass = '';
}
?>
<tr class="<?php echo $trClass . ' js-redshopb-child ' . $isParent; ?>" data-id="<?php echo $item->id; ?>" data-parent="<?php echo $parent; ?>">
<td style="background: red">
	<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
</td>
<td>
	<?php if ($item->checked_out) : ?>
				<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
					$item->checked_out_time, 'orders.', $canCheckin
				); ?>
	<?php endif; ?>

			<?php if ($canEdit) : ?>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . $item->id); ?>">
			<?php endif; ?>
					<?php echo str_pad($item->id, 6, '0', STR_PAD_LEFT); ?>

					<?php if ($canEdit) : ?>
				</a>
					<?php endif; ?>

			<?php if ($isParent) : ?>
				<button class="btn btn-mini js-redshop-children"><i class="icon-chevron-down"></i></button>
			<?php endif; ?>
		</td>
		<td>
			<?php echo Text::_('COM_REDSHOPB_' . $this->escape($item->customer_type)); ?>
		</td>
		<td>
			<?php echo Text::_('COM_REDSHOPB_COMPANY') . ': ' . $this->escape($item->company_name); ?>

			<?php if (isset($item->department_name)) : ?>
				<br /><?php echo Text::_('COM_REDSHOPB_DEPARTMENT') . ': ' . $this->escape($item->department_name);  ?>
			<?php endif; ?>

			<?php if (isset($item->employee_name)) : ?>
				<br /><?php echo Text::_('COM_REDSHOPB_EMPLOYEE') . ': ' . $this->escape($item->employee_name);  ?>
			<?php endif; ?>
		</td>
		<td>
			<?php echo $this->escape($item->vendor_name); ?>
		</td>
		<td>
			<?php echo $order->renderStatusLabel(); ?>
		</td>
		<td>
			<?php echo HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')) ?>
		</td>
		<td><?php echo $this->escape($item->author); ?></td>
		<td>
			<?php echo Text::_('COM_REDSHOPB_ORDER_LOG_TYPE_' . $logType); ?>

			<?php if (!is_null($sentOrder)) : ?>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . $sentOrder); ?>">
					<?php echo $sentOrderString;?>
				</a>
			<?php endif;?>
		</td>
		<td>
			<a class="btn btn-small btn-primary" href="index.php?option=com_redshopb&task=order.printPDF&id=<?php echo $item->id;?>" target="_blank" title="<?php echo Text::_('COM_REDSHOPB_PRINT'); ?>">
				<i class="icon-print"></i>
			</a>
		</td>
	</tr>
<?php
endforeach;
