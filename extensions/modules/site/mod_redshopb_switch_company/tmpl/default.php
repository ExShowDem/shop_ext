<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_switch_company
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;


?>
<div class="vanir-switch-company-container">
	<?php if ($userCompany) : ?>
		<h4 class="vanir-switch-company-company-name-headline"><?php echo Text::_('PLG_USER_REDSHOPB_SWITCH_COMPANY_SELECTED_COMPANY_LABEL') ?></h4>
		<span class="vanir-switch-company-company-name"><?php echo $userCompany->get('name') ?> (<?php echo $userRoleType->get('name') ?>)</span>
	<?php endif; ?>
	<div>
		<a class="btn vanir-switch-company-button" href="index.php?option=com_redshopb&view=user_select_company">
			<?php echo Text::_('PLG_USER_REDSHOPB_SWITCH_COMPANY_LABEL') ?>
		</a>
	</div>
</div>
