<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;

$formName = $data['formName'];
$model    = RedshopbModelAdmin::getInstance('Product', 'RedshopbModel');

$disabled = '';
$icons    = array(
	'new' => 'icon-file-text-alt',
	'edit' => 'icon-edit',
	'delete' => 'icon-trash'
);

if ($model->getIslockedByWebservice($data['productId']))
{
	$disabled        = ' disabled="disabled"';
	$icons['new']    = 'icon-lock';
	$icons['edit']   = 'icon-lock';
	$icons['delete'] = 'icon-lock';
}

$buttons = array();

if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
{
	$buttons[] = '<a class="btn btn-success"' . $disabled
		. ' href="javascript:void(0);" onclick="redSHOPB.form.submit(event);" data-task="description.add">'
		. '<i class="' . $icons['new'] . '"></i> ' . Text::_('JTOOLBAR_NEW') . '</a>';
}

if (RedshopbHelperACL::getPermission('manage', 'product', array('edit'), true))
{
	$buttons[] = '<a class="btn"' . $disabled
		. ' href="javascript:void(0);" onclick="redSHOPB.form.submit(event);" data-task="description.edit" data-list="true">'
		. '<i class="' . $icons['edit'] . '"></i> ' . Text::_('JTOOLBAR_EDIT') . '</a>';
}

if (RedshopbHelperACL::getPermission('manage', 'product', array('delete'), true))
{
	$buttons[] = '<a class="btn btn-danger"' . $disabled
		. ' href="javascript:void(0);" onclick="redSHOPB.products.tabSubmit(event);" data-task="descriptions.delete" data-list="true">'
		. '<i class="' . $icons['delete'] . '"></i> ' . Text::_('JTOOLBAR_DELETE') . '</a>';
}
?>
<div class="row-fluid">
	<div class="span12">
		<div class="btn-toolbar">
			<div class="btn-group">
				<?php foreach ($buttons AS $button):?>
					<?php echo $button;?>
				<?php endforeach;?>
			</div>
		</div>
	</div>
</div>
