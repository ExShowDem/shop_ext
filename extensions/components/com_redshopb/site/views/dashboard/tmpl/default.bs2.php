<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Templates
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

RHelperAsset::load('dashboard.css', 'com_redshopb');
$itemRows = array_chunk($this->items, 4);

?>
<?php foreach ($itemRows as $row):?>
	<?php $itemCount = count($row);?>
	<div class="row-fluid">
		<?php foreach ($row as $item):?>
			<?php $query = (!empty($item['query'])) ? $item['query'] : '';?>
			<div class="span3">
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=' . $item['view'] . $query); ?>">
					<div class="row-fluid">
						<div class="span12 pagination-centered">
							<span class="dashboard-icon-link-icon">
							<i class="<?php echo $item['icon'] ?> icon-4x"></i>
						</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12 pagination-centered">
						<span class="dashboard-icon-link-text">
							<strong><?php echo $item['text'];?></strong>
						</span>
						</div>
					</div>
				</a>
			</div>
		<?php endforeach;?>
	</div>
<?php endforeach;
