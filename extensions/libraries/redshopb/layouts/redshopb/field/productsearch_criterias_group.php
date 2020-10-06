<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);

?>
<div class="row-fluid oneSearchGroup <?php echo isset($groupClass) ? $groupClass : '' ?>">
	<div class="span2 oneSearchGroupTitle">
		<button type="button" class="btn btn-danger btn-mini deleteSearchGroup">
			<span class="icon-remove"></span>
		</button>
		<button type="button" class="btn btn-mini reorderSearchGroupButton">
			<i class="icon-reorder"></i>
		</button>
		<span class="label label-info order-number"><?php echo $groupNumber ?></span>
	</div>
	<div class="span10 oneSearchGroupEntities">
		<?php if (!empty($entities)):
			foreach ($entities as $entity):
				echo RedshopbLayoutHelper::render(
					'redshopb.field.productsearch_criterias_entity',
					array(
					'id'     => $id,
					'entity' => $entity,
					'name'   => $name,
					'groupNumber' => $groupNumber
					)
				);
			endforeach;
		endif;
		?>
	</div>
</div>
