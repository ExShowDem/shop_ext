<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
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

// Make sure currentPage is not zero number.
$currentPage = (!isset($currentPage) || !$currentPage) ? 1 : $currentPage;

$nextPage = $currentPage + 1;


if (true === RedshopbEntityConfig::getInstance()->getBool('no_pagination', false))
{
	$app = Factory::getApplication();

	if ('category' === $app->input->get('layout'))
	{
		$currentLimit = $app->getUserState('shop.productLimit');

		$app->setUserState('shop.pagination.limit.category_' . $app->input->get('id'), $currentLimit * $currentPage);
	}
}
?>

<?php if ($nextPage <= $numberOfPages): ?>
<div id="redshopbPaginationLoadMore">
	<a href="javascript:void(0);" class="btn btn-default" rel="nofollow"
	   data-page="<?php echo $nextPage ?>" data-page_total="<?php echo $numberOfPages ?>" onclick="<?php echo $ajaxJS ?>">
		<?php echo Text::_('COM_REDSHOPB_SHOP_LOAD_MORE') ?>
	</a>
</div>
<?php endif;
echo RedshopbLayoutHelper::render('shop.pages.pagination.direct_links', compact(array_keys(get_defined_vars())));
