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

$data        = $displayData;
$breadcrumbs = $data['breadcrumbs'];
$separator   = $data['separator'];
$params      = $data['params'];
$classSfx    = $data['class_sfx'];
$count       = count($breadcrumbs);
?>
<div class="redshopb-breadcrumb">
	<ul itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb<?php echo $classSfx; ?>">
		<?php
		if ($params->get('showHere', 1))
		{
			echo '<li class="active">' . Text::_($params->get('breadcrumbs_here', 'COM_REDSHOPB_CONFIG_BREADCRUMBS_HERE')) . '&#160;</li>';
		}
		else
		{
			echo '<li class="active"><span class="divider icon-location"></span></li>';
		}

		// Get rid of duplicated entries on trail including home page when using multilanguage
		for ($i = 0; $i < $count; $i++)
		{
			if ($i == 1 && !empty($breadcrumbs[$i]->link) && !empty($breadcrumbs[$i - 1]->link) && $breadcrumbs[$i]->link == $breadcrumbs[$i - 1]->link)
			{
				unset($breadcrumbs[$i]);
			}
		}

		// Find last and penultimate items in breadcrumbs list
		end($breadcrumbs);
		$lastItemKey = key($breadcrumbs);
		prev($breadcrumbs);
		$penultItemKey = key($breadcrumbs);

		// Make a link if not the last item in the breadcrumbs
		$showLast = $params->get('showLast', 1);

		// Generate the trail
		foreach ($breadcrumbs as $key => $item) :
			if ($key != $lastItemKey)
			{
				// Render all but last item - along with separator
				echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

				if (!empty($item->link))
				{
					echo '<a itemprop="item" href="' . $item->link . '" class="pathway"><span itemprop="name">' . $item->name . '</span></a>';
				}
				else
				{
					echo '<span itemprop="name">' . $item->name . '</span>';
				}

				if (($key != $penultItemKey) || $showLast)
				{
					echo '<span class="divider">' . $separator . '</span>';
				}

				echo '<meta itemprop="position" content="' . ($key + 1) . '"></li>';
			}
			elseif ($showLast)
			{
				// Render last item if reqd.
				echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="active">';
				echo '<span itemprop="name">' . $item->name . '</span>';
				echo '<meta itemprop="position" content="' . ($key + 1) . '"></li>';
			}
		endforeach; ?>
	</ul>
</div>
