<?php
/**
 * @package     Kvasir.Plugin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);
?>

<div class="row-fluid">
	<div class="span12>">
		<p>
			<a href="javascript:void(0);" class="btn btn-info" id="syncRecordsButton" onclick="redSHOPB.solr.syncRecords(event)">
				<?php echo Text::_('PLG_VANIR_SEARCH_SOLR_SYNC_BUTTON');?>
			</a>
		</p>
	</div>
</div>
<div class="row-fluid">
	<div class="span12" id="syncRecordsStatus">
	</div>
</div>
<input type="hidden" name="start" value="0"/>
