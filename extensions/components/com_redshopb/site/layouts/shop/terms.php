<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$terms  = $displayData['terms'];
$doc    = Factory::getDocument();
$script = array();

$script[] = '	function jAcceptTerms() {';
$script[] = '		jQuery("#terms-and-conditions").attr("checked", true).trigger("change");';
$script[] = '       jQuery("#acceptTerms").modal("hide");';
$script[] = '	}';

$script[] = '	function jDeclineTerms() {';
$script[] = '		jQuery("#terms-and-conditions").attr("checked", false).trigger("change");';
$script[] = '       jQuery("#acceptTerms").modal("hide");';
$script[] = '	}';

$doc->addScriptDeclaration(implode("\n", $script));
?>
<div id="acceptTerms" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="termModalLabel" aria-hidden="true">
	<div class="modal-header">
		<h3 id="termModalLabel"><?php echo Text::_('COM_REDSHOPB_SHOP_TERMS_AND_CONDITIONS'); ?></h3>
	</div>
	<div class="modal-body">
		<div class="row">
			<?php echo $terms; ?>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" onclick="jDeclineTerms()"><?php echo Text::_('COM_REDSHOPB_DECLINE'); ?></button>
		<button class="btn btn-primary" onclick="jAcceptTerms()"><?php echo Text::_('COM_REDSHOPB_ACCEPT'); ?></button>
	</div>
</div>
