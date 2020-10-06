<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);

?><!-- Google Code for Order Confirmation Conversion Page -->
<script type="text/javascript">
	/* <![CDATA[ */
	var google_conversion_id = <?php echo (int) $params->get('google_conversion_id'); ?>;
	var google_conversion_language = "en";
	var google_conversion_format = "3";
	var google_conversion_color = "ffffff";
	var google_conversion_label = "<?php echo $params->get('google_conversion_label'); ?>";
	var google_conversion_value = <?php echo (float) $order->total_price; ?>;
	var google_conversion_currency = "<?php echo $order->currency; ?>";
	var google_remarketing_only = false;
	/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
<noscript>
	<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/<?php echo (int) $params->get('google_conversion_id'); ?>/?value=<?php echo (float) $order->total_price ?>&amp;currency_code=<?php echo $order->currency; ?>&amp;label=<?php echo $params->get('google_conversion_label'); ?>&amp;guid=ON&amp;script=0"/>
	</div>
</noscript>
