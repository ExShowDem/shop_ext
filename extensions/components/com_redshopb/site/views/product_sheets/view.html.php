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
/**
 * Product sheets View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProduct_Sheets extends RedshopbView
{
	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_PRODUCT_SHEETS_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$print = RToolbarBuilder::createStandardButton(
			'product_sheets.printProductSheets',
			Text::_('COM_REDSHOPB_PRODUCT_SHEETS_PRINT_PRODUCTS'),
			'btn-success',
			'icon-print',
			false
		);

		$clear = RToolbarBuilder::createStandardButton(
			'product_sheets.clearProductList',
			Text::_('COM_REDSHOPB_PRODUCT_SHEETS_CLEAR_PRODUCT_LIST'),
			'btn-inverse',
			'icon-trash',
			false
		);

		$group->addButton($print)
			->addButton($clear);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
