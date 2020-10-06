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
use Joomla\CMS\Factory;

$app   = Factory::getApplication();
$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');
?>
<div class="media btn-group">
	<a href="#" id="thumbs" onclick="MediaManager.setViewType('thumbs')" class="btn <?php echo ($style == "thumbs") ? 'active' : '';?>">
	<i class="icon-grid-view-2"></i> <?php echo Text::_('COM_RSBMEDIA_THUMBNAIL_VIEW'); ?></a>
	<a href="#" id="details" onclick="MediaManager.setViewType('details')" class="btn <?php echo ($style == "details") ? 'active' : '';?>">
	<i class="icon-list-view"></i> <?php echo Text::_('COM_RSBMEDIA_DETAIL_VIEW'); ?></a>
</div>
