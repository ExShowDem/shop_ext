<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();
$moduleSearch    = Factory::getApplication()->getUserState('shop.search', '');

if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

// Options
$searchField  = 'filter_' . $data['options']->get('searchField', 'search');
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = is_null($data['view']->filterForm) ? array() : $data['view']->filterForm->getGroup('filter');
?>
<?php if (isset($filters[$searchField]) && !empty($filters[$searchField])) : ?>
	<?php if ($searchButton) : ?>
		<label for="filter_search" class="element-invisible">
			<?php echo Text::_('LIB_REDCORE_FILTER_SEARCH_DESC'); ?>
		</label>
		<?php if (!(empty($filters[$searchField]->value) && !empty($moduleSearch))):?>
		<div class="btn-wrapper input-append">
			<?php echo $filters[$searchField]->input; ?>
			<button type="submit" class="btn hasTooltip" title="<?php echo RHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="icon-search"></i>
			</button>
		</div>
		<?php endif;?>

		<?php if ($filterButton) : ?>
			<div class="btn-wrapper">
				<button type="button" class="btn hasTooltip js-stools-btn-filter" title="<?php echo RHtml::tooltipText('JSEARCH_TOOLS_DESC'); ?>">
					<?php echo Text::_('JSEARCH_TOOLS');?> <i class="caret"></i>
				</button>
			</div>
		<?php endif; ?>
		<div class="btn-wrapper">
			<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo RHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
				<?php echo Text::_('JSEARCH_FILTER_CLEAR');?>
			</button>
		</div>
	<?php endif; ?>
<?php endif;
