<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
/**
 * Product sheets Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Sheets extends RedshopbModelList
{
	/**
	 * Gets the first product item main attribute id for a selected product
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return  integer
	 */
	protected function getFirstProductItemMainAttrId($productId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('pamv.id'))
			->from($db->qn('#__redshopb_product_item', 'pi'))
			->join('inner', $db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('pi.product_id') . ' = ' . $db->qn('p.id'))
			->join('inner', $db->qn('#__redshopb_product_attribute', 'pa') . ' ON ' . $db->qn('pa.product_id') . ' = ' . $db->qn('p.id'))
			->join(
				'inner', $db->qn('#__redshopb_product_attribute_value', 'pav')
				. ' ON ' . $db->qn('pav.product_attribute_id') . ' = ' . $db->qn('pa.id')
			)
			->join(
				'inner',
				$db->qn(
					'#__redshopb_product_item_attribute_value_xref', 'piavx'
				) . ' ON ' . $db->qn('piavx.product_item_id') . ' = ' . $db->qn('pi.id')
				. ' AND ' . $db->qn('piavx.product_attribute_value_id') . ' = ' . $db->qn('pav.id')
			)
			->join('inner', $db->qn('#__redshopb_product_attribute', 'pam')
				. ' ON ' . $db->qn('pam.product_id') . ' = ' . $db->qn('p.id')
				. ' AND ' . $db->qn('pam.main_attribute') . ' = 1'
			)
			->join('inner', $db->qn('#__redshopb_product_attribute_value', 'pamv')
				. ' ON ' . $db->qn('pamv.product_attribute_id') . ' = ' . $db->qn('pam.id')
				. ' AND ' . $db->qn('pamv.id') . ' = ' . $db->qn('piavx.product_attribute_value_id')
			)
			->where($db->qn('p.id') . ' = ' . (int) $productId)
			->where($db->qn('pi.state') . ' = 1')
			->where($db->qn('pa.state') . ' = 1')
			->where($db->qn('pav.state') . ' = 1')
			->order(
				array(
					$db->qn('pa.ordering'),
					$db->qn('pav.ordering'),
					)
			);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Print pdf document for given order id
	 *
	 * @param   int  $collectionId  (optional) Generate the product sheets from
	 *                              an specific collectioninstead of using the session list
	 *
	 * @return void
	 */
	public function printPDF($collectionId = 0)
	{
		$session          = Factory::getSession();
		$sesitems         = $session->get('productSheets.products', null, 'redshopb');
		$selectedProducts = array_keys($sesitems);

		if (empty($selectedProducts))
		{
			$selectedProducts = array(0);
		}

		/** @var RedshopbModelProducts $model */
		$model = RModelAdmin::getInstance('Products', 'RedshopbModel');

		// Getting model state variables to ensure variables are created
		$model->getState();

		// Setting state variables
		$model->setState('list.product_state', '1');
		$model->setState('list.product_discontinued', '0');
		$model->setState('list.allow_parent_companies_products', true);
		$model->setState('list.allow_mainwarehouse_products', true);
		$model->setState('list.disallow_freight_fee_products', true);
		$model->setState('filter.include_categories', true);
		$model->setState('filter.include_tags', true);
		$model->setState('include_objects', true);

		$model->set('context', 'com_redshopb.product_sheets_selected.products');

		if ($collectionId)
		{
			$model->setState('filter.product_collection', $collectionId);
		}
		else
		{
			$model->setState('filter.product_id', $selectedProducts);
		}

		$items = $model->getItems();

		// Start pdf code
		$mPDF = RedshopbHelperMpdf::getInstance();

		$stylesheet = file_get_contents(JPATH_ROOT . '/media/redcore/css/component.min.css');
		$mPDF->WriteHTML($stylesheet, 1);
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/com_redshopb/css/product_sheets.css');
		$mPDF->WriteHTML($stylesheet, 1);

		$mPDF->SetTitle(Text::_('COM_REDSHOPB_PDF_PRODUCT_SHEETS'));
		$mPDF->SetSubject(Text::_('COM_REDSHOPB_PDF_PRODUCT_SHEETS'));

		$selectedItems = array();

		foreach ($items as $finalItem)
		{
			if ($collectionId)
			{
				$dropDownItem                                                          = $this->getFirstProductItemMainAttrId($finalItem->id);
				$selectedItems[$finalItem->id . '_' . $dropDownItem]                   = clone $finalItem;
				$selectedItems[$finalItem->id . '_' . $dropDownItem]->dropDownSelected = $dropDownItem;
			}
			else
			{
				if (isset($sesitems[$finalItem->id]))
				{
					$dropDownItems = array_keys($sesitems[$finalItem->id]);

					foreach ($dropDownItems as $dropDownItem)
					{
						$selectedItems[$finalItem->id . '_' . $dropDownItem]                   = clone $finalItem;
						$selectedItems[$finalItem->id . '_' . $dropDownItem]->dropDownSelected = $dropDownItem;
					}
				}
			}
		}

		foreach ($selectedItems as $item)
		{
			$mPDF->AddPage();
			$item->description = $model->getDescriptions($item->id);

			$item->colorId = (isset($item->dropDownSelected)) ? $item->dropDownSelected : 0;

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->qn('string_value'))
				->from($db->qn('#__redshopb_product_attribute_value'))
				->where($db->qn('id') . ' = ' . (int) $item->colorId);
			$db->setQuery($query);

			$item->color = $db->loadResult();

			$query->clear()
				->select('pc.*')
				->from($db->qn('#__redshopb_product_composition', 'pc'))
				->where('pc.product_id = ' . (int) $item->id)
				->where('(pc.flat_attribute_value_id = ' . (int) $item->colorId . ' OR pc.flat_attribute_value_id IS NULL)')
				->order('pc.flat_attribute_value_id DESC');
			$db->setQuery($query);

			$item->compositions = $db->loadObjectList();

			$query->clear()
				->select(
					array(
						'pav.string_value'
					)
				)
				->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id AND pa.state = 1')
				->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
				->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
				->where('pa.name = ' . $db->quote('Str.'))
				->where('pa.product_id = ' . (int) $item->id)
				->where('pav.state = 1')
				->where('pa.state = 1')
				->where('pi.state = 1')
				->group('pav.id')
				->order('pav.ordering, pav.id ASC');

			RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
				array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
				)
			);
			$db->setQuery($query);

			$sizes = $db->loadColumn();
			RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

			if ($sizes)
			{
				$item->sizes = $sizes[0] . ((count($sizes) > 1) ? ' - ' . $sizes[count($sizes) - 1] : '');
			}

			$query->clear()
				->select(array('pav.*', 'pa.product_id', 'pa.type_id'))
				->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
				->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
				->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
				->where('pa.product_id = ' . (int) $item->id)
				->where('pa.state = 1')
				->where('pav.state = 1')
				->where('pi.state = 1')
				->where('pa.main_attribute = 1')
				->order('pav.ordering')
				->group('pav.id');

			$db->setQuery($query);

			$colors       = $db->loadObjectList();
			$item->colors = array();

			foreach ($colors as $color)
			{
				$item->colors[] = RedshopbHelperCollection::getProductItemValueFromType($color->type, $color, true);
			}

			RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

			$item->wash = RModel::getInstance('Shop', 'RedshopbModel')->getWash($item->id);

			$logos = RModelAdmin::getInstance('Logos', 'RedshopbModel');
			$logos->setState('type', array('Ecotex', 'EUFlower'));

			$item->logos = $logos->getItems();

			$query->clear()
				->select('l.image')
				->from($db->qn('#__redshopb_logos', 'l'))
				->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . 'on pcx.category_id = l.brand_id')
				->where('type = ' . $db->q('ProductPDF'))
				->where('pcx.product_id = ' . (int) $item->id);
			$db->setQuery($query);

			$brandImage = $db->loadResult();

			if ($brandImage)
			{
				$item->background = RedshopbHelperThumbnail::originalToResize($brandImage, 680, 141, 100, 1, 'logos');
			}

			$html = RedshopbLayoutHelper::render(
				'product_sheets.pdf',
				array(
					'item' => $item,
				)
			);

			$mPDF->WriteHTML($html, 2);
		}

		$mPDF->Output(Text::_('COM_REDSHOPB_PRODUCT_SHEETS_TITLE') . '_' . Date::getInstance()->format('Y_m_d_H_i', true) . '.pdf', 'D');
	}
}
