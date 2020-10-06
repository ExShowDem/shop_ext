<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>{email.email-header}
<div class="all">
	<?php echo RedshopbHelperTemplate::renderFromString(
		Text::_('COM_REDSHOPB_ORDER_MAIL_PAYMENT_STATUS_WAS_CHANGED_BODY'), 'payment-status-changed-body', $displayData, 'email'
	); ?>
</div>
{email.email-footer}
