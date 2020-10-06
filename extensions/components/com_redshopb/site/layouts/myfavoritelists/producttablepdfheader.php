<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$favouriteList = $displayData['favourite_list'];

?>

<table width="100%">
	<tbody>
		<tr>
			<td></td>
			<td align="right" valign="bottom">
				<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST'); ?>: <strong><?php echo $favouriteList['favourite_list_name']; ?></strong>
			</td>
		</tr>
	</tbody>
</table>
