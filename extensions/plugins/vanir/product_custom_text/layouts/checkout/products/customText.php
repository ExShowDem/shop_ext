<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ==============================
 *
 * @var  array   $displayData  Layout data.
 * @var  object  $item         Item data.
 * @var  object  $field        Field data.
 */

extract($displayData);

?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $field->value ?>
</td>
