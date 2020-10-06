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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<?php if (empty($this->items)):?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php else :?>
	<?php echo RedshopbLayoutHelper::render('campaign_products.list', array('products' => $this->items, 'productsImages' => $this->productsImages)); ?>
<?php endif;
