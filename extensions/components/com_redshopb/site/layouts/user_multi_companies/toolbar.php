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
<h2>
	<?php echo Text::_('COM_REDSHOPB_USER_MULTI_COMPANY_LIST_TITLE'); ?>
</h2>
<div class="row">
	<div class="btn-toolbar toolbar">
		<div class="btn-group">
			<button class="btn btn-success" onclick="Joomla.submitform('user_multi_company.add',
				document.getElementById('<?php echo $formName; ?>'))" href="#">
				<i class="icon-file-text-alt"></i>
				<?php echo Text::_('JTOOLBAR_NEW') ?>
			</button>

			<button class="btn"
				onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
					else{ Joomla.submitform('user_multi_company.edit', document.getElementById('<?php echo $formName; ?>'))}"
				href="#">
				<i class="icon-edit"></i>
				<?php echo Text::_('JTOOLBAR_EDIT') ?>
			</button>
			<button class="btn btn-danger"
				onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
					else{ Joomla.submitform('user_multi_companies.delete', document.getElementById('<?php echo $formName; ?>'))}"
				href="#">
				<i class="icon-trash"></i>
				<?php echo Text::_('JTOOLBAR_DELETE') ?>
			</button>
		</div>
	</div>
</div>
