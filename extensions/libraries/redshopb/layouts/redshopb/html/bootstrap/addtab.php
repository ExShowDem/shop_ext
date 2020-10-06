<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$id     = empty($displayData['id']) ? '' : $displayData['id'];
$active = empty($displayData['active']) ? '' : $displayData['active'];
$title  = '';

// We need to add the title to the data attribute, so we can rebuild the tab navigation on ajax loads
if (strpos($id, 'collection_') !== false)
{
	$parts = explode('_', $id);
	$title = RedshopbHelperCollection::getName($parts[1], true);
}
?>

<div id="<?php echo $id; ?>" class="tab-pane<?php echo $active; ?>" data-title="<?php echo $title;?>">
