<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<?php if (count($this->images) > 0 || count($this->folders) > 0) { ?>
<div class="row-fluid">
	<ul class="manager thumbnails">
		<div class="row-fluid">
		<?php for ($i = 0, $n = count($this->folders); $i < $n; $i++) :
			if ($i % 6 == 0 && $i != 0) : ?>
		</div><div class="row-fluid">
			<?php endif;
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		endfor;
		$j = $n % 6;
		?>

		<?php for ($i = $j, $n = count($this->images) + $j; $i < $n; $i++) :
			if ($i % 6 == 0 && $i != 0) : ?>
		</div><div class="row-fluid">
			<?php endif;
			$this->setImage($i - $j);
			echo $this->loadTemplate('image');
		endfor; ?>
		</div>
	</ul>
</div>
<?php } else { ?>
	<div id="media-noimages">
		<div class="alert alert-info"><?php echo Text::_('COM_RSBMEDIA_NO_IMAGES_FOUND'); ?></div>
	</div>
<?php } ?>
