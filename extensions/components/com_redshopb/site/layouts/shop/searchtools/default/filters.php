<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\Registry\Registry;

defined('JPATH_REDCORE') or die;

/**
 * Layout variables
 * =====================
 * @var  array $displayData Layout data.
 */

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

$searchField = 'filter_' . $data['options']->get('searchField', 'search');

// Load the form filters
$filters = is_null($data['view']->filterForm) ? false : $data['view']->filterForm->getGroup('filter');
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ($fieldName == 'filter_product_collection' && !RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance(RedshopbHelperCompany::getCompanyIdByCustomer($data['view']->customerId, $data['view']->customerType)))) : ?>
			<?php continue; ?>
		<?php endif; ?>

		<?php if ($fieldName != $searchField) : ?>
			<div class="js-stools-field-filter">
				<?php echo $field->input; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif;
