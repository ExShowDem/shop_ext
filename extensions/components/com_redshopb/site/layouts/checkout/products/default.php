<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$item    = $displayData['item'];
$field   = $displayData['field'];
$isEmail = $displayData['isEmail'];

$displayHtml = $field->value;

if (!$isEmail)
{
	$displayHtml = $field->input;
}

?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $displayHtml;?>
</td>


