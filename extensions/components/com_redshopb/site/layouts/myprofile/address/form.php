<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=address');
$header      = $displayData['header'];
$address     = $displayData['address'];
$addressType = $displayData['address_type'];
$formName    = $addressType . 'form';
$canEdit     = RedshopbHelperACL::getPermission('manage', 'address', array('edit','edit.own'), true);
$controlText = 'JACTION_CREATE';

if (!empty($address->country))
{
	$controlText = 'JTOOLBAR_EDIT';
}

?>
<div class="row">
	<div class="col-md-12">

		<?php if ($canEdit) :?>
		<form action="<?php echo $action; ?>"
			  name="<?php echo $formName;?>"
			  id="<?php echo $formName;?>"
			  class="adminForm"
			  method="post">
		<?php endif; ?>

			<h5><?php echo Text::_($header); ?></h5>

			<?php if (!empty($address)): ?>
				<?php echo RedshopbLayoutHelper::render('myprofile.address.display', array('address' => $address));?>

				<?php if ($canEdit) :?>
					<input type="hidden" value="<?php echo $address->delivery_address_id; ?>" name="id">
				<?php endif; ?>
			<?php endif; ?>

		<?php if ($canEdit) : ?>
			<div class="row">
				<div class="col-md-12">
					<input type="submit" name="edit<?php echo $addressType;?>" class="btn btn-small btn-primary" value="<?php echo Text::_($controlText); ?>">
				</div>
			</div>
			<input type="hidden" value="address.edit" name="task">
			<input type="hidden" value="1" name="from_user">
			<input type="hidden" value="<?php echo $addressType; ?>" name="create">
		</form>
		<?php endif; ?>
	</div>
</div>

