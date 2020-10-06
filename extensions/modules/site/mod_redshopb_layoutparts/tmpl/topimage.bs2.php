<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_topnav
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

jimport('redshopb.router.route');

// Template and layout preview mode
$input       = Factory::getApplication()->input;
$previewMode = ($input->getInt('layoutid', 0) ? true : false);

if ($layoutParams)
	:
	if ($layoutParams->topImage != '')
		:
		?>
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span12 text-left">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=dashboard') ?>">
						<img src="<?php echo $layoutParams->topImage ?>" alt="<?php echo $layout->name ?>" />
					</a>
				</div>
			</div>
		</div>
		<?php
	endif;
endif;
