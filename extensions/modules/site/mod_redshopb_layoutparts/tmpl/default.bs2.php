<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_topnav
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

if ($valPart != '')
	:
	?>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<?php if ($wrapper != '') : ?>
					<?php echo '<' . $wrapper . '>' . $valPart . '</' . $wrapper . '>'; ?>
				<?php else : ?>
					<?php echo $valPart; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
endif;
