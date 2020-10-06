<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

if (!isset($availableStyles))
{
	$availableStyles = array('list', 'grid');
}

if (!isset($showAsURL))
{
	$uri = Uri::getInstance();
	$uri->delVar('show_as');
	$uri->delVar('Itemid');
	$showAsURL = $uri->toString(array('path', 'query'));
}

foreach ($availableStyles as $availableStyle):
?>
	<span class="shop-category-icon shop-category-icon-<?php echo $availableStyle; ?>">
	<a rel="nofollow" <?php
	if ($showAs == $availableStyle) :
		echo 'class="category-' . $availableStyle . '-active show-as-active"';
	endif;
	?> href="<?php
	echo RedshopbRoute::_($showAsURL . '&show_as=' . $availableStyle);?>">
		&nbsp;
	</a>
</span>
<?php endforeach;
