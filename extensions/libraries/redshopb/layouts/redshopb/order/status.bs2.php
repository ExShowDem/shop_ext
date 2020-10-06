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

/**
 * Layout variables
 * -------------------
 * @var   integer  $statusId    Order status identifier
 * @var   string   $statusName  Status name
 */

$classes = array('label');

switch ($statusId)
{
	case RedshopbEntityOrder::STATE_CONFIRMED:
	case RedshopbEntityOrder::STATE_SHIPPED:
	case RedshopbEntityOrder::STATE_READY_FOR_DELIVERY:
		$classes[] = 'label-success';
		break;

	case RedshopbEntityOrder::STATE_CANCELLED:
		$classes[] = 'label-important';
		break;

	case RedshopbEntityOrder::STATE_REFUNDED:
		$classes[] = 'label-warning';
		break;

	default:
		break;
}

?>
<label class="<?php echo implode(' ', $classes); ?>">
	<?php echo $this->escape($statusName); ?>
</label>
