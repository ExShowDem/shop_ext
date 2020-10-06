<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

?>
<div class="row-fluid oneSearchGroupEntity">
	<div class="span3">
		<button type="button" class="btn btn-danger btn-mini deleteCriteria">
			<span class="icon-remove"></span>
		</button>
		<button type="button" class="btn btn-mini reorderCriteriaButton">
			<i class="icon-reorder"></i>
		</button>
		<strong><?php echo $entity['title'] ?></strong>
		<input type="hidden"
			   data-template-name="<?php echo $name . '[{replaceNumberGroup}][' . $entity['name'] . '][name]' ?>"
			   name="<?php echo $name . '[' . $groupNumber . '][' . $entity['name'] . '][name]' ?>"
			   value="<?php echo $entity['name'] ?>" class="inputForReplace searchEntityName">
	</div>
	<div class="span3"><?php

		$method = array(
			HTMLHelper::_('select.option', '-1', Text::_('COM_REDSHOPB_USE_GLOBAL')),
			HTMLHelper::_('select.option', 'exact', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_EXACT')),
			HTMLHelper::_('select.option', 'exact_and_partial', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_EXACT_AND_PARTIAL'))
		);

		echo HTMLHelper::_(
			'select.genericlist', $method, $name . '[' . $groupNumber . '][' . $entity['name'] . '][method]',
			'class="inputForReplace" data-template-name="' . $name . '[{replaceNumberGroup}][' . $entity['name'] . '][method]"'
			. ' data-template-id="' . $id . '_method_dropdown_{replaceNumberGroup}_' . $entity['name'] . '"',
			'value', 'text',
			isset($entity['method']) ? $entity['method'] : '-1',
			$id . '_method_dropdown_' . $groupNumber . '_' . $entity['name']
		);
		?></div>
	<div class="span3"><?php

		$synonym = array(
			HTMLHelper::_('select.option', '0', Text::_('JNO')),
			HTMLHelper::_('select.option', '-1', Text::_('COM_REDSHOPB_USE_GLOBAL'))
		);

		echo HTMLHelper::_(
			'select.genericlist', $synonym, $name . '[' . $groupNumber . '][' . $entity['name'] . '][synonym]',
			'class="inputForReplace" data-template-name="' . $name . '[{replaceNumberGroup}][' . $entity['name'] . '][synonym]"'
			. ' data-template-id="' . $id . '_synonym_dropdown_{replaceNumberGroup}_' . $entity['name'] . '"',
			'value', 'text',
			isset($entity['synonym']) ? $entity['synonym'] : '-1',
			$id . '_synonym_dropdown_' . $groupNumber . '_' . $entity['name']
		);
		?></div>
	<div class="span3"><?php

		$stem = array(
			HTMLHelper::_('select.option', '0', Text::_('JNO')),
			HTMLHelper::_('select.option', '-1', Text::_('COM_REDSHOPB_USE_GLOBAL'))
		);

		echo HTMLHelper::_(
			'select.genericlist', $stem, $name . '[' . $groupNumber . '][' . $entity['name'] . '][stem]',
			'class="inputForReplace" data-template-name="' . $name . '[{replaceNumberGroup}][' . $entity['name'] . '][stem]"'
			. ' data-template-id="' . $id . '_stem_dropdown_{replaceNumberGroup}_' . $entity['name'] . '"',
			'value', 'text',
			isset($entity['stem']) ? $entity['stem'] : '-1',
			$id . '_stem_dropdown_' . $groupNumber . '_' . $entity['name']
		);
		?></div>
</div>
