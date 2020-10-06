<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Layout variables
 * =========================
 * @var  array  $fields  List of field data
 */
extract($displayData);
?>

<?php if (!empty($fields)): ?>
<table class="table table-striped userFieldTable">
	<tbody>
		<?php foreach ($fields as $fieldData): ?>
			<tr>
				<td class="userFieldTitle"><?php echo $fieldData->title ? $fieldData->title : $fieldData->name ?></td>
				<td class="userFieldValue">
					<?php
					$params = new Registry($fieldData->field_data_params);
					$href   = $params->get('external_url', null);
					$title  = $params->get('title', $fieldData->description);

					if (!$href)
					{
						$scope = RInflector::pluralize($fieldData->scope);
						$href  = RedshopbHelperMedia::getFullMediaPath($params->get('internal_url', ''), $scope, 'documents');
					}
					?>
					<div class="redshopb-field redshopb-field-documents">
						<a title="<?php echo $fieldData->description ?>" href="<?php echo $href ?>" target="_blank">
							<i class="icon icon-book"></i> <?php echo $fieldData->value; ?>
						</a>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif;
