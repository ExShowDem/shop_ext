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

$data       = $displayData;
$formName   = $data['formName'];
$attributes = $data['attributes'];
$items      = $data['items'];
$return     = $data['return'];
$action     = $data['action'];
$productId  = $data['productId'];

if (empty($attributes) || empty($items))
{
	echo RedshopbLayoutHelper::render('common.nodata');

	return;
}

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

$preparedItems     = $items;
$product           = $data['model']->getItem($productId);
$nameFlatAttribute = '';
?>
<div class="redshopb-collection-edit-prices">
	<div class="row">
		<h3><?php echo $product->name;?></h3>
		<table class="table table-bordered table-condensed table-combinations">
			<thead>
			<tr>
				<td></td>
				<?php foreach ($orderedAttributes as $attribute) : ?>
					<?php if ($attribute['main_attribute']) :
						$nameFlatAttribute = $attribute['name'];
						?>
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
			<?php if ($hasAnyFlatDisplay) :
				$isHaveItems = array();
				?>
				<?php foreach ($preparedItems as $preparedItem) : ?>
					<tr>
						<td></td>
						<?php foreach ($orderedAttributes as $attribute) : ?>
							<?php if ($attribute['main_attribute']) : ?>
								<?php foreach ($attribute['values'] AS $value) : ?>
									<td class="text-center">
										<?php if (is_array($preparedItem['attributes'][$attribute['id']])) : ?>
											<?php
											$itemId = array_search($value, $preparedItem['attributes'][$attribute['id']], true);

											if ($itemId) :
												$productItem = $productItems[$itemId];
												$class       = 'unpublish';

												if (1 == $productItem['collection_item_state'])
												{
													$class = 'published';
												}

												$isHaveItems[$productItem['product_attribute_value_id']] = true;

												if (1 == $productItem['discontinued'])
												{
													$class = 'discontinued';
												}
												?>
												<div class="<?php echo $class ?>">
													<input type="text" name="jform[price][<?php echo $itemId ?>]" id="jform_price_<?php echo $itemId ?>" value="<?php echo $productItem['collectionPrice']; ?>" class="input-mini" />
												</div>
											<?php endif; ?>
										<?php
										elseif ($value === $preparedItem['attributes'][$attribute['id']]) :
											$productItem = $productItems[$preparedItem['id']];
											$class       = 'unpublish';

											if (1 == $productItem['collection_item_state'])
											{
												$class = 'published';
											}

											$isHaveItems[$productItem['product_attribute_value_id']] = true;

											if (1 == $productItem['discontinued'])
											{
												$class = 'discontinued';
											}
											?>
											<div class="<?php echo $class ?>">
												<input type="text" name="jform[price][<?php echo $preparedItem['id'] ?>]" id="jform_price_<?php echo $preparedItem['id'] ?>" value="<?php echo $productItem['collectionPrice']; ?>" class="input-mini" />
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
				<tr>
					<th><?php echo Text::sprintf('COM_REDSHOPB_COLLECTION_SET_ALL_FROM_COLOR', $nameFlatAttribute); ?></th>

					<?php foreach ($orderedAttributes as $attribute) : ?>
					<?php if ($attribute['main_attribute']) : ?>
					<?php foreach ($attribute['flat_values'] AS $flatKey => $value) : ?>
								<?php if (isset($isHaveItems[$flatKey])): ?>
					<td class="text-center">
						<input type="text" name="jform[price_color][<?php echo $flatKey; ?>]" id="jform_price_<?php echo $flatKey; ?>" value="" class="input-mini" />
					</td>
								<?php else: ?>
					<td></td>
								<?php endif; ?>
					<?php endforeach; ?>
					<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			<?php else : ?>
				<?php
				foreach ($preparedItems as $preparedItem) :
					$productItem = $productItems[$preparedItem['id']];
					?>
					<tr>
						<?php foreach ($orderedAttributes as $attribute) : ?>
							<td class="<?php echo $class ?>">
								<?php echo $preparedItem['attributes'][$attribute['id']] ?>
							</td>
						<?php endforeach; ?>
						<td class="text-center">
							<?php
							if (1 == $productItem['state']):
								$class = 'published';

								if (1 == $productItem['discontinued'])
								{
									$class = 'discontinued';
								}
								?>
								<div>
									<input type="text" name="jform[price][<?php echo $preparedItem['id'] ?>]" id="jform_price_<?php echo $preparedItem['id'] ?>" value="<?php echo $productItem['collectionPrice']; ?>" class="input-mini" />
								</div>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
