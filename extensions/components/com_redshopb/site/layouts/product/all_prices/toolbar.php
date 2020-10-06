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

$icons = array(
	'new' => 'icon-file-text-alt',
	'edit' => 'icon-edit',
	'delete' => 'icon-trash'
);

$buttons = array();

if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
{
	$buttons[] = '<a class="btn btn-success"'
		. ' href="javascript:void(0);" onclick="redSHOPB.form.submit(event);" data-task="all_price.add">'
		. '<i class="' . $icons['new'] . '"></i> ' . Text::_('JTOOLBAR_NEW') . '</a>';
}

if (RedshopbHelperACL::getPermission('manage', 'product', array('edit'), true))
{
	$buttons[] = '<a class="btn btn-default"'
		. ' href="javascript:void(0);" onclick="redSHOPB.form.submit(event);" data-task="all_price.edit" data-list="true">'
		. '<i class="' . $icons['edit'] . '"></i> ' . Text::_('JTOOLBAR_EDIT') . '</a>';
}

if (RedshopbHelperACL::getPermission('manage', 'product', array('delete'), true))
{
	$buttons[] = '<a class="btn btn-danger"'
		. ' href="javascript:void(0);" onclick="redSHOPB.products.tabSubmit(event);" data-task="all_prices.delete" data-list="true">'
		. '<i class="' . $icons['delete'] . '"></i> ' . Text::_('JTOOLBAR_DELETE') . '</a>';
}

?>
<div class="row">
	<div class="col-md-12">
		<div class="btn-toolbar">
			<div class="btn-group">
				<?php foreach ($buttons AS $button):?>
					<?php echo $button;?>
				<?php endforeach;?>
			</div>
		</div>
	</div>
</div>
