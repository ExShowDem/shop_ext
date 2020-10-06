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
?>

<div class="row-fluid">
	<div class="span12">
		<div class="btn-toolbar">
			<div class="btn-group">
				<a href="javascript:void(0);" onclick="redSHOPB.form.submit(event);" data-task="myfavoritelist.add" class="btn btn-success">
					<i class="icon icon-plus"></i> <?php echo Text::_('COM_REDSHOPB_MYFAVOURITELIST_CREATE');?>
				</a>
			</div>
		</div>
	</div>
</div>
