<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$uri    = Uri::getInstance();
$scheme = $uri->getScheme();
$host   = $uri->getHost();

echo $scheme . '://' . $host . RedshopbRoute::_('index.php?option=com_redshopb&view=orders');
