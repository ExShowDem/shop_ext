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
?>
<div class="row-fluid">
	<div class="btn-toolbar">
		<div class="btn-group">
			<?php if (RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true)): ?>
			<button class="btn"
					onclick="Joomla.submitform('all_prices.saveAllPrices', document.getElementById('<?php echo $formName; ?>'))"
					href="#">
				<i class="icon-save"></i>
				<?php echo Text::_('JTOOLBAR_APPLY') ?>
			</button>
			<?php endif; ?>
		</div>
	</div>
</div>
