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
use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

// Options
$searchField  = 'filter_' . $data['options']->get('searchField', 'search');
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');
?>
<?php if (!empty($filters[$searchField])) : ?>
	<?php if ($searchButton) : ?>
		<?php if ($filterButton) : ?>
			<div class="btn-wrapper hidden-phone">
				<button type="button" class="btn hasTooltip js-stools-btn-filter" title="<?php echo RHtml::tooltipText('JSEARCH_TOOLS_DESC'); ?>">
					<?php echo Text::_('JSEARCH_TOOLS');?> <i class="caret"></i>
				</button>
			</div>
			<div class="btn-wrapper">
				<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo RHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
					<?php echo Text::_('JSEARCH_FILTER_CLEAR');?>
				</button>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php endif;
