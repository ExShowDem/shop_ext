<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * @var array $displayData
 * @var string $title
 * @var string $value
 * @var int $firstSpan
 */

extract($displayData);

if (!isset($firstSpan))
{
	$firstSpan = 4;
}

?><div class="row-fluid">
	<div class="span<?php echo $firstSpan ?>">
		<?php echo $title; ?>
	</div>
	<div class="span<?php echo 12 - $firstSpan ?>">
		<?php echo $value; ?>
	</div>
</div>
