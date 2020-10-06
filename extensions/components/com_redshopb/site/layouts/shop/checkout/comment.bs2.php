<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = (object) $displayData;

$output = isset($data->output) ? $data->output : '';

?>

<div class="well">
	<h4><?php echo Text::_('COM_REDSHOPB_ORDER_COMMENT', true); ?></h4>
	<div class="row-fluid">
		<p><?php echo $data->comment; ?></p>
	</div>
</div>

