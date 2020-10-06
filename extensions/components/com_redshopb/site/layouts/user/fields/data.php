<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * =========================
 * @var  array  $fields  List of field data
 */
extract($displayData);

$defaultText = array(Text::_('JNO'), Text::_('JYES'));
?>

<?php if (!empty($fields)): ?>
<table class="table table-striped userFieldTable">
	<tbody>
	<?php foreach ($fields as $fieldData): ?>
		<?php if ($fieldData->type_alias == 'radioyes' && $fieldData->value == 0): ?>
			<?php continue; ?>
		<?php endif; ?>

		<?php if (isset($defaultText[$fieldData->value]) || !in_array($fieldData->type_alias, array('radioboolean', 'radioyes'))) : ?>
			<tr>
				<td class="userFieldTitle"><?php echo $fieldData->title ? $fieldData->title : $fieldData->name ?></td>
				<td class="userFieldValue"><?php echo $fieldData->value ?></td>
			</tr>
		<?php endif; ?>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endif;
