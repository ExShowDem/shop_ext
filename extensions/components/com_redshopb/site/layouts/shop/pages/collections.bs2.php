<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Pages
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die;

/**
 * Display variables
 * ===========================
 *
 * @var  array   $displayData    Array of available data.
 * @var  array   $collections    Array of collection ID.
 * @var  boolean $showPagination True if show pagination.
 */
extract($displayData);

$colsPerPage = RedshopbApp::getConfig()->get('categories_number_of_columns_per_page', 2);

switch ($colsPerPage)
{
	case "1":
		$span = "span12";

		break;
	case "3":
		$span = "span4";

		break;
	case "4":
		$span = "span3";

		break;
	default:
		$span = "span6";

		break;
}

?>
<div class="row-fluid categories">
	<div class="span12">
		<?php foreach ($collections as $collectionId): ?>
			<?php $collection = RedshopbEntityCollection::getInstance($collectionId); ?>
			<div class="<?php echo $span ?>">
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=collection&id=' . $collection->getId()); ?>">
					<div class="row-fluid pagination-centered">
						<div class="category-title">
							<span class="redshopb-shop-category-title"><?php echo $collection->get('name') ?></span>
						</div>
					</div>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</div>
