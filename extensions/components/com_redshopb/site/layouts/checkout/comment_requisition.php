<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$showTitle = $displayData['showTitle'];

$isComment = (isset($displayData['comment']));
$text      = $isComment ? $displayData['comment'] : $displayData['requisition'];
$title     = $isComment ? 'COMMENT' : 'REQUISITION';

?>
<?php if ($showTitle):?>
<h4><?php echo Text::_('COM_REDSHOPB_ORDER_' . $title, true); ?></h4>
<?php endif;?>

<p><?php echo $text; ?></p>


