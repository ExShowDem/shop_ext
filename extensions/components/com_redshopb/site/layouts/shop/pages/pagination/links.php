<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Pages.Pagination
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * ===========================
 * @var  array  $displayData    List available data
 * @var  int    $numberOfPages  Number of pages
 * @var  int    $currentPage    Current page
 * @var  int    $ajaxJS         Javascript function for ajax process.
 * @var  bool   $isCompanies    Use for companies pagination.
 * @var  bool   $isEmployees    Use for employees pagination.
 * @var  bool   $isDepartments  Use for departments pagination.
 * @var  bool   $enableDirectLinks  Use for enable hidden direct links
 */

extract($displayData);

// Calculate to display range of pages
/**
 * @var   int  $range  Number of pages to show around current page, from start and till end page
 */
$range = 1;

/**
 * @var   int  $paginationButtons  Number of buttons to display inside pagination list (including "..." fields)
 */
$paginationButtons = 8;

if ($numberOfPages > $paginationButtons)
{
	$start  = 1;
	$end    = $numberOfPages;
	$middle = ceil($numberOfPages / 2);

	$startZone    = range($start, $start + $range, 1);
	$middleZone   = range($middle - $range, $middle + $range, 1);
	$endZone      = range($end - $range, $end, 1);
	$pageZone     = range($currentPage - $range, $currentPage + $range, 1);
	$displayZones = array_merge($startZone, $middleZone, $endZone, $pageZone);
}

if ($numberOfPages > 1):
?>

<div class="pagination pagination-toolbar clearfix" style="text-align: center;">
	<ul class="pagination-list">
		<?php
		echo RedshopbLayoutHelper::render(
			'shop.pages.pagination.link',
			array(
				'text'          => Text::_('JSTART'),
				'active'        => ($currentPage > 1) ? true : false,
				'ajaxJS'        => $ajaxJS,
				'currentPage'   => $currentPage,
				'numberOfPages' => $numberOfPages
			)
		);
		echo RedshopbLayoutHelper::render(
			'shop.pages.pagination.link',
			array(
				'text'          => Text::_('JPREVIOUS'),
				'active'        => ($currentPage > 1) ? true : false,
				'ajaxJS'        => $ajaxJS,
				'currentPage'   => $currentPage,
				'numberOfPages' => $numberOfPages
			)
		);

		if (!empty($displayZones))
		{
			for ($i = 1; $i <= $numberOfPages; $i++)
			{
				if (in_array($i, $displayZones))
				{
					$output = RedshopbLayoutHelper::render(
						'shop.pages.pagination.link',
						array(
							'text'          => $i,
							'active'        => ($currentPage == $i) ? false : true,
							'ajaxJS'        => $ajaxJS,
							'currentPage'   => $currentPage,
							'numberOfPages' => $numberOfPages
						)
					);

					$showPoints = true;

					echo $output;
				}
				elseif ($showPoints)
				{
					echo '<li class="points"><span>...</span></li>';
					$showPoints = false;
				}
			}
		}
		else
		{
			for ($i = 1; $i <= $numberOfPages; $i++)
			{
				$output = RedshopbLayoutHelper::render(
					'shop.pages.pagination.link',
					array(
						'text'          => $i,
						'active'        => ($currentPage == $i) ? false : true,
						'ajaxJS'        => $ajaxJS,
						'currentPage'   => $currentPage,
						'numberOfPages' => $numberOfPages
					)
				);

				echo $output;
			}
		}

		echo RedshopbLayoutHelper::render(
			'shop.pages.pagination.link',
			array(
				'text'          => Text::_('JNEXT'),
				'active'        => ($currentPage < $numberOfPages) ? true : false,
				'ajaxJS'        => $ajaxJS,
				'currentPage'   => $currentPage,
				'numberOfPages' => $numberOfPages
			)
		);

		echo RedshopbLayoutHelper::render(
			'shop.pages.pagination.link',
			array(
				'text'          => Text::_('JEND'),
				'active'        => ($currentPage < $numberOfPages) ? true : false,
				'ajaxJS'        => $ajaxJS,
				'currentPage'   => $currentPage,
				'numberOfPages' => $numberOfPages
			)
		);
		?>
	</ul>
	<?php echo RedshopbLayoutHelper::render('shop.pages.pagination.direct_links', compact(array_keys(get_defined_vars()))); ?>
</div>
<?php endif;
