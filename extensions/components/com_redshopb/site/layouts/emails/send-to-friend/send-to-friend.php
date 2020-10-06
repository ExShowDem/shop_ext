<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<p><?php echo Text::sprintf(Text::_('COM_REDSHOPB_SENDTOFRIEND_GREETING'), $data['friends_name']);?></p>
<p><?php echo Text::sprintf(Text::_('COM_REDSHOPB_SENDTOFRIEND_I_RECOMMEND_PRODUCT_FROM_SITE'), $siteName, $product->name);?></p>

<?php if (isset($product->description->description)): ?>
	<p><?php echo $product->description->description; ?></p>
<?php endif; ?>
<p><?php echo Text::sprintf(Text::_('COM_REDSHOPB_SENDTOFRIEND_LINK_FOOTER'), $link);?></p>
