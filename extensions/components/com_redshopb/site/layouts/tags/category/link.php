<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$itemId       = Factory::getApplication()->input->getInt('Itemid', 0);
$collectionId = Factory::getApplication()->input->getInt('collection_id', 0);

$link = 'index.php?option=com_redshopb&view=shop&layout=category&id=' . (int) $extThis->category->get('id') . '&Itemid=' . $itemId;

$link = !$collectionId ? $link : $link . '&collection_id=' . $collectionId;
?>
	<a class="category-link" href="<?php echo RedshopbRoute::_($link, false) ?>">
		<?php echo $extThis->category->get('name') ?>
	</a>
<?php
