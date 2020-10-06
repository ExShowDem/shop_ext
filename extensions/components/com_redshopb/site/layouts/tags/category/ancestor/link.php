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

$category = RedshopbEntityCategory::load($extThis->category->id);

if ($category->isLoaded() && (int) $category->get('parent_id') > 1)
{
	$parent = RedshopbEntityCategory::load((int) $category->get('parent_id'));

	if ($parent->isLoaded() && (int) $parent->get('parent_id') > 1)
	{
		$ancestor = RedshopbEntityCategory::load((int) $parent->get('parent_id'));
		$itemId   = Factory::getApplication()->input->getInt('Itemid', 0);
		$link     = 'index.php?option=com_redshopb&view=shop&layout=category&id=' . (int) $ancestor->get('id') . '&Itemid=' . $itemId;
		?>
		<a class="category-parent-link" href="<?php echo RedshopbRoute::_($link, false) ?>">
			<?php echo $ancestor->get('name') ?>
		</a>
		<?php
	}
}
