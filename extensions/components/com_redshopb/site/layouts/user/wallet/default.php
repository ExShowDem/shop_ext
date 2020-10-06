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

$wallet         = $data['wallet'];
$successMessage = '';
$errorMessage   = '';

if (isset($data['successMessage']))
{
	$successMessage = $data['successMessage'];
}

if (isset($data['errorMessage']))
{
	$errorMessage = $data['errorMessage'];
}
?>

<?php if ($successMessage != '') : ?>
<div class="alert alert-success">
<?php echo $successMessage ?>
</div>
<?php endif; ?>

<?php if ($errorMessage != '') : ?>
<div class="alert alert-warning">
<?php echo $errorMessage ?>
</div>
<?php endif; ?>

<?php if (empty($wallet) || empty($wallet->credit)) : ?>
<div class="alert alert-info">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<div class="pagination-centered">
	<h3>
		<?php echo Text::_('COM_REDSHOPB_USER_WALLET_NO_CREDIT_TO_DISPLAY') ?>
	</h3>
</div>
</div>
<?php else : ?>
	<table class="table table-striped">
		<tr>
			<th><?php echo Text::_('COM_REDSHOPB_CURRENCY'); ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_AMOUNT'); ?></th>
		</tr>
		<?php
		foreach ($wallet->credit as $credit) : ?>
		<tr>
		<td><?php echo $credit['currency']; ?></td>
		<td><?php echo $credit['amount']; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
<?php endif;
