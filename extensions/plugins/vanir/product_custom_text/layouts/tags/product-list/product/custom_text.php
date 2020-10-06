<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  product_custom_text
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

RHelperAsset::load('script.js', 'plg_vanir_product_custom_text');

$plugin = PluginHelper::getPlugin('vanir', 'product_custom_text');
$params = new Registry($plugin->params);
$label  = (string) $params->get('textLabel', Text::_('PLG_VANIR_PRODUCT_CUSTOM_TEXT_COLUMN_NAME'));
?>
<span><?php echo $label; ?></span>
<input type="text" name="product_custom_text" class="form-control product-custom-text" id="product_custom_text_<?php echo $productId ?>" />
