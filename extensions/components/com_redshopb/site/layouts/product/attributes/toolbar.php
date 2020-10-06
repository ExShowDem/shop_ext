<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;

$formName = $data['formName'];
?>
<div class="row">
	<div class="col-md-12">
		<h4 class="pull-left">
			<?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_LIST_TITLE'); ?>
		</h4>
		<div class="pull-right btn-toolbar">
			<a class="btn btn-success"
			   href="javascript:void(0);"
			   onclick="redSHOPB.form.submit(event)"
			   data-task="product_attribute.add">
				<i class="icon-plus-sign"></i>
				<?php echo Text::_('JTOOLBAR_NEW_TYPE') ?>
			</a>
		</div>
	</div>
</div>

