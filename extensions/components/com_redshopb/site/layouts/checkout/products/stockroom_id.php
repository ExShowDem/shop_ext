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

$item     = $displayData['item'];
$field    = $displayData['field'];
$delivery = $displayData['delivery'];

if (!$field->value)
{
	echo '<td class="field_' . $field->fieldname . '"></td>';

	return;
}

$stockroom = RedshopbEntityStockroom::load($field->value);

$displayHtml = '';

if (!empty($stockroom))
{
	$displayHtml  = $stockroom->min_delivery_time . ' ~ ' . $stockroom->max_delivery_time;
	$displayHtml .= ' ' . Text::_('COM_REDSHOPB_STOCKROOM_DELIVERY_' . strtoupper($delivery));
}
?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $displayHtml;?>
</td>

