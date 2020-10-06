<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// HTML helpers
HTMLHelper::_('vnrbootstrap.tooltip');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

?>
<div class="redshopb-contact_list">
	<h1><?php echo Text::_('COM_REDSHOPB_CONTACT_LIST_TITLE'); ?></h1>
	<div class="row-fluid">
		<div class="span6">
			<div class="contact-image">
			<?php if (!empty($this->vendorImageLink)): ?>
				<img src="<?php echo $this->vendorImageLink ?>" width="<?php echo $this->vendorImageWidth ?>" height="<?php echo $this->vendorImageHeight ?>" />
			<?php else: ?>
				<?php echo RedshopbHelperMedia::drawDefaultImg($this->vendorImageWidth, $this->vendorImageHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'); ?>
			<?php endif; ?>
			</div>
		</div>
		<div class="span6">
			<div class="contact-information-wrapper">
				<h3><?php echo $this->vendor->get('name') ?></h3>
				<div class="contact-address">
					<p><?php echo $this->vendor->getAddress()->get('address') ?>
					<?php if ($this->vendor->getAddress()->get('address2')): ?>
						<br /><i><?php echo $this->vendor->getAddress()->get('address2') ?></i>
					<?php endif; ?>
					</p>
					<p><?php echo $this->vendor->getAddress()->get('zip') ?>, <?php echo $this->vendor->getAddress()->get('city') ?></p>
					<?php if ($this->vendor->getAddress()->getState()->getId()): ?>
						<br /><i><?php echo $this->vendor->getAddress()->getState()->get('name') ?></i>
					<?php endif; ?>
					<p><?php echo $this->vendor->getAddress()->getCountry()->get('name') ?></p>
				</div>
				<div class="contact-list-company">
					<?php echo $this->vendor->get('contact_info'); ?>
				</div>
			</div>
		</div>
	</div>
</div>
