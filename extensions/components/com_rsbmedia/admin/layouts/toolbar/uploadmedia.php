<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$title = Text::_('JTOOLBAR_UPLOAD');
?>
<button data-toggle="collapse" data-target="#collapseUpload" class="btn btn-small btn-success">
	<i class="icon-plus icon-white" title="<?php echo $title; ?>"></i> <?php echo $title; ?>
</button>
