<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data       = $displayData;
$formName   = $data['formName'];
$attributes = $data['attributes'];
$items      = $data['items'];
$return     = $data['return'];
$action     = $data['action'];
$productId  = $data['productId'];
?>

<?php if (empty($attributes) || empty($items)) : ?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>

<?php return;
endif; ?>

<?php
$productItems = $items;
$items        = array_values($items);

// Reorder the attribute according to the items attibute order
$randomAttributes       = array_keys($items[0]['attributes']);
$orderedAttributes      = array();
$hasAnyFlatDisplay      = false;
$flatDisplayAttributeId = null;

for ($i = 0; $i < count($attributes); $i++)
{
	$orderedAttributes[$i] = $attributes[$randomAttributes[$i]];

	if ((bool) $attributes[$randomAttributes[$i]]['main_attribute'])
	{
		$hasAnyFlatDisplay      = true;
		$flatDisplayAttributeId = $attributes[$randomAttributes[$i]]['id'];
	}
}

// Prepare the items for display (regroup the items by flat display attribute if any)
$preparedItems = array();
$itemsToVerify = $items;

foreach ($items as $k => &$item)
{
	if ($hasAnyFlatDisplay)
	{
		$itemAttributes = $item['attributes'];

		// Check if there are other items having the same attributes
		foreach ($itemsToVerify as $key => $itemToVerify)
		{
			if ($itemToVerify['id'] === $item['id'])
			{
				continue;
			}

			$itemToVerifyAttributes = $itemToVerify['attributes'];
			$diff                   = array_diff_assoc($itemToVerifyAttributes, $itemAttributes);

			if (1 === count($diff) && isset($diff[$flatDisplayAttributeId]))
			{
				// Append the attribute with the item id
				$currentAttributes = $item['attributes'][$flatDisplayAttributeId];
				$attributeToAdd    = $diff[$flatDisplayAttributeId];

				if (is_array($currentAttributes))
				{
					$newItemAttributes                      = $currentAttributes;
					$newItemAttributes[$itemToVerify['id']] = $attributeToAdd;
				}

				else
				{
					$newItemAttributes = array(
						$item['id'] => $currentAttributes,
						$itemToVerify['id'] => $attributeToAdd
					);
				}

				$item['attributes'][$flatDisplayAttributeId] = $newItemAttributes;
				unset($itemsToVerify[$key]);
				unset($items[$key]);
			}
		}
	}
}

$preparedItems = $items;
$product       = $data['model']->getItem($productId);
?>
<style>
	.table-combinations .unpublished {
		background-color: #DA4F49;
		color: black;
	}

	.table-combinations .published {
		background-color: #5BB75B;
		color: black;
	}

	.table-combinations .discontinued {
		background-color: black;
		color: white;
	}
</style>
<script>
	function editItem(id) {
		document.getElementById('item_id').value = id;
		var form = document.getElementById('<?php echo $formName ?>');
		Joomla.submitform('product_item.edit', form);
	}

	function deleteItem(id) {
		document.getElementById('item_id').value = id;
		var form = document.getElementById('<?php echo $formName ?>');
		Joomla.submitform('product_items.delete', form);
	}
</script>
<div class="redshopb-collection-edit-productcombinations">
	<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>" method="post">
		<div class="row">
			<h3><?php echo $product->name;?></h3>
			<div class="redshopb-collection-edit-productcombinations-table">
				<table class="table table-bordered table-condensed table-combinations">
					<thead>
					<tr>
						<?php foreach ($orderedAttributes as $attribute) : ?>
							<?php if ($attribute['main_attribute']) : ?>
								<?php foreach ($attribute['values'] as $value) : ?>
									<th><?php echo $attribute['name'] ?> (<?php echo $value ?>)</th>
								<?php endforeach; ?>
							<?php else : ?>
								<th><?php echo $attribute['name'] ?></th>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php if (!$hasAnyFlatDisplay) : ?>
							<th width="5%">
								<?php echo Text::_('JTOOLBAR_EDIT') ?>
							</th>
						<?php endif; ?>
					</tr>
					</thead>
					<tbody>
					<?php if ($hasAnyFlatDisplay) : ?>
						<?php foreach ($preparedItems as $preparedItem) : ?>
							<tr>
								<?php foreach ($orderedAttributes as $attribute) : ?>
									<?php if ($attribute['main_attribute']) : ?>
										<?php foreach ($attribute['values'] AS $value) : ?>
											<td>
												<?php if (is_array($preparedItem['attributes'][$attribute['id']])) : ?>
													<?php
													$itemId = array_search($value, $preparedItem['attributes'][$attribute['id']], true);

													if ($itemId) :
														$productItem = $productItems[$itemId];

														if (1 == $productItem['discontinued'])
														{
															$class = 'discontinued';
														}

														elseif (1 == $productItem['state'])
														{
															$class = 'published';
														}

														elseif (0 == $productItem['state'])
														{
															$class = 'unpublished';
														}
														?>
														<div class="<?php echo $class ?> pagination-centered">
															<div class="btn-group">
																<button class="btn btn-xs"
																		onclick="editItem('<?php echo $itemId ?>')">
																	<i class="icon-edit"></i>
																</button>
																<button class="btn btn-danger btn-xs"
																		onclick="deleteItem('<?php echo $itemId ?>')">
																	<i class="icon-trash"></i>
																</button>
															</div>
														</div>
													<?php endif; ?>
												<?php
												elseif ($value === $preparedItem['attributes'][$attribute['id']]) :
													$productItem = $productItems[$preparedItem['id']];

													if (1 == $productItem['discontinued'])
													{
														$class = 'discontinued';
													}

													elseif (1 == $productItem['state'])
													{
														$class = 'published';
													}

													elseif (0 == $productItem['state'])
													{
														$class = 'unpublished';
													}
													?>
													<div class="<?php echo $class ?> pagination-centered">
														<div class="btn-group">
															<button class="btn btn-xs"
																	onclick="editItem('<?php echo $preparedItem['id'] ?>')">
																<i class="icon-edit"></i>
															</button>
															<button class="btn btn-danger btn-xs"
																	onclick="deleteItem('<?php echo $preparedItem['id'] ?>')">
																<i class="icon-trash"></i>
															</button>
														</div>
													</div>
												<?php endif; ?>
											</td>
										<?php endforeach; ?>
									<?php else : ?>
										<td>
											<?php echo $preparedItem['attributes'][$attribute['id']] ?>
										</td>
									<?php endif; ?>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>

					<?php else : ?>
						<?php
						foreach ($preparedItems as $preparedItem) :
							$productItem = $productItems[$preparedItem['id']];

							if (1 == $productItem['discontinued'])
							{
								$class = 'discontinued';
							}

							elseif (1 == $productItem['state'])
							{
								$class = 'published';
							}

							elseif (0 == $productItem['state'])
							{
								$class = 'unpublished';
							}
							?>
							<tr>
								<?php foreach ($orderedAttributes as $attribute) : ?>
									<td class="<?php echo $class ?>">
										<?php echo $preparedItem['attributes'][$attribute['id']] ?>
									</td>
								<?php endforeach; ?>
								<td>
									<div class="pagination-centered">
										<div class="btn-group">
											<button class="btn btn-xs"
													onclick="editItem('<?php echo $preparedItem['id'] ?>')">
												<i class="icon-edit"></i>
											</button>
											<button class="btn btn-danger btn-xs"
													onclick="deleteItem('<?php echo $preparedItem['id'] ?>')">
												<i class="icon-trash"></i>
											</button>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div>
			<input id="item_id" type="hidden" name="cid[]" value="">
			<input type="hidden" name="task" value="">
			<input type="hidden" name="return" value="<?php echo $return ?>">
			<input type="hidden" name="jform[product_id]" value="<?php echo $productId ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
