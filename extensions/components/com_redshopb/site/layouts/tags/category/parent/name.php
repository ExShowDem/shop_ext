<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$category = RedshopbEntityCategory::load($extThis->category->id);

if ($category->isLoaded() && (int) $category->get('parent_id') > 1)
{
	$parent = RedshopbEntityCategory::load((int) $category->get('parent_id'));

	echo $parent->get('name');
}
