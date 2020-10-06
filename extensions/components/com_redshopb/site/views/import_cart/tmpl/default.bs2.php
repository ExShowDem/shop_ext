<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$user = Factory::getUser();

$showImportCart = $user->get('id')
	&& (RedshopbHelperACL::isSuperAdmin() || RedshopbHelperACL::getPermission('import', 'order'));

$returnToCart = base64_encode('index.php?option=com_redshopb&view=shop&layout=cart');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

?>

<?php if ($showImportCart) :?>

<div class="row">
	<div class="col-md-12">
		<div class="import-title">
			<h3><?php echo Text::_('COM_REDSHOPB_IMPORT_CART_TITLE') ?></h3>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="import-desc">
			<p><?php echo Text::_('COM_REDSHOPB_IMPORT_CART_DESC') ?></p>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="import-link">
			<?php echo RedshopbLayoutHelper::render('import.form', array('model' => 'carts', 'return' => $returnToCart)); ?>
		</div>
	</div>
</div>

<?php endif;
