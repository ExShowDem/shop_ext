<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_categories
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="row-fluid">
	<div class="span10">
		<?php foreach ($list AS $item) : ?>
			<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=manufacturer&id=' . $item->id);?>">

				<?php if (isset($item->imagehtml)) : ?>
					<?php echo $item->imagehtml; ?>
				<?php else : ?>
					<?php echo $item->name ?>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</div>
	<div class="span2 mod_manufacture_navi">
		<a class="btn btn-red" href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=manufacturerlist');?>">
			<?php echo Text::_('MOD_REDSHOPB_MANUFACTURERS_SEE_ALL');?>
		</a>
	</div>
</div>
