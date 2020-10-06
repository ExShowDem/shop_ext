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

$model  = isset($displayData['model']) ? $displayData['model'] : 'users';
$return = isset($displayData['return']) ? $displayData['return'] : null;
?>
<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=import.importCSV'); ?>" method="POST" enctype="multipart/form-data" id="csvForm_<?php echo $model; ?>" class="pull-right">
	<div class="input_upload_button">
		<input type="hidden" name="type" value="<?php echo $model; ?>"/>
		<a href="javascript:void(0);" class="btn" id="upload_button"><?php echo Text::_('COM_REDSHOPB_IMPORT_CSV'); ?></a>
		<input type="file" id="upload_input_invisible" name="rform[csv]" onchange="jQuery('#csvForm_<?php echo $model; ?>').submit();"/>
		<input type="hidden" name="model" value="<?php echo $model; ?>"/>

		<?php if ($return): ?>
		<input type="hidden" value="<?php echo $return; ?>" name="return" />
		<?php endif; ?>
	</div>
</form>
