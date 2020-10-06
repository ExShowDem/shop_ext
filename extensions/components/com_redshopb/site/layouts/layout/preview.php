<?php
/**
 * @package     Aesir.E-Commerce.Layouts
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Uri\Uri;

$data = (object) $displayData;
$id   = $data->id;
?>
<iframe src="<?php echo Uri::root() . 'index.php?option=com_redshopb&view=layout&layout=preview&layoutid=' . $id ?>" frameborder="0" width="100%" height="400px"></iframe>
