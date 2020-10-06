<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('text');

/**
 * RelatedSKU Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldRelatedSKU extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'RelatedSKU';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
	{
		$relatedSKUName = RedshopbApp::getConfig()->get('related_sku_name',
			Text::_('COM_REDSHOPB_RELATED_DEFAULT_SKU')
		) . ' ' . Text::_('COM_REDSHOPB_SKU');

		$this->element['label'] = $relatedSKUName;
		$this->description      = Text::sprintf('COM_REDSHOPB_RELATED_SKU_DESC', $relatedSKUName);

		return parent::getLabel();
	}
}
