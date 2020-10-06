<?php
/**
 * @package     Aesir.E-Commerce.Layouts
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = (object) $displayData;

$canEdit = RedshopbHelperACL::getPermission('manage', 'layout', Array('edit', 'edit.own'), true);

$layout         = $data->layout;
$params         = (object) $layout->params;
$id             = $layout->id;
$name           = $layout->name;
$company        = $layout->company_name . ($layout->company_name2 != '' ? ' ' . $layout->company_name2 : '');
$companyl       = $layout->companyl_name . ($layout->companyl_name2 != '' ? ' ' . $layout->companyl_name2 : '');
$no             = $data->no;
$home           = $layout->home;
$defaultCompany = ($layout->company_id != '');

?>
<div class="thumbnail layout-thumb">
	<?php
		echo RedshopbLayoutHelper::render(
			'layout.preview',
			array(
							'id' => $layout->id
						)
		);
	?>
</div>

<div class="layout-actions">
	<div class="row-fluid">
		<span class="span4">
			<?php echo HTMLHelper::_('rgrid.id', $no, $id); ?>
			<?php
				echo HTMLHelper::_('rgrid.isdefault', ($home != '0' || $defaultCompany), $no, 'layouts.', ($home != '1' && !$defaultCompany));
			?>
		</span>
		<?php
		if ($company != '')
		:
		?>
		<span class="span8 pull-right text-right">
		<span class="badge badge-info">
		<?php echo sprintf(Text::_('COM_REDSHOPB_LAYOUT_DEFAULT_FOR'), (strlen($company) > 25 ? substr($company, 0, 25) . '...' : $company)) ?>
		</span>
		</span>
		<?php
		endif;
		?>
	</div>
	<div class="row-fluid">
		<span class="span12 pull-right text-right">
			<?php
			if ($canEdit)
			:
			?>
			<?php echo HTMLHelper::_(
				'link',
				RedshopbRoute::_('index.php?option=com_redshopb&task=layout.edit&id=' . $id),
				Text::_('COM_REDSHOPB_DETAILS'),
				array('title' => Text::_('COM_REDSHOPB_DETAILS'))
			); ?>
			<?php
			endif;
			?>
		</span>
	</div>
</div>

<div class="layout-info">
	<div class="row-fluid">
		<div class="span12">
			<h3><?php echo $name; ?></h3>
		</div>
	</div>
	<div class="row-fluid">
		<?php
		if ($companyl != '')
		:
		?>
		<div class="span8">
		<span><?php echo Text::_('COM_REDSHOPB_COMPANY_LABEL') . ': ' . $companyl ?></span>
		</div>
		<?php
		endif;
		?>
		<div class="span4 pull-right text-right">
			<span>#ID <?php echo $id; ?></span>
		</div>		
	</div>
</div>
