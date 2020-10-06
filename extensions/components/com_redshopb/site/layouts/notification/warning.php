<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;

$message     = !empty($data['message']) ? $data['message'] : '';
$closeButton = !empty($data['closeButton']) ? true : false;

?>
<div class="alert alert-info">
	<?php if ($closeButton): ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php endif; ?>
	<div class="pagination-centered">
		<h3><?php echo $message ?></h3>
	</div>
</div>
