<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * All Price Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerAll_Price extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_ALL_PRICE';

	/**
	 * Get the Route object for a redirect to item.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  Route   The Route object
	 */
	protected function getRedirectToItemRoute($append = null)
	{
		$redirectUrl = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $append;

		$productId = $this->input->get('product_id', 0, 'int');

		if ($productId)
		{
			$redirectUrl .= '&product_id=' . (int) $productId;
		}

		$productItemId = $this->input->get('product_item_id', 0, 'int');

		if ($productItemId)
		{
			$redirectUrl .= '&product_item_id=' . (int) $productItemId;
		}

		$type = $this->input->get('type', null, 'string');

		if ($type)
		{
			$redirectUrl .= '&type=' . $type;
		}

		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			$redirectUrl .= '&return=' . $return;
		}

		return RedshopbRoute::_($redirectUrl, false);
	}

	/**
	 * Get the Route object for a redirect to list.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  Route   The Route object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			return RedshopbRoute::_(base64_decode($return), false);
		}

		return parent::getRedirectToListRoute($append);
	}

	/**
	 * Get Product Items
	 *
	 * @return void
	 */
	public function ajaxGetProductItems()
	{
		RedshopbHelperAjax::validateAjaxRequest('get');

		$app       = Factory::getApplication();
		$productId = $app->input->get('id');
		$result    = array();
		$result[]  = (object) array('text' => Text::_('JSELECT'), 'value' => '');

		$columns = RedshopbHelperProduct::getSKUCollection($productId, 'objectList', false);

		if ($columns)
		{
			foreach ($columns as &$column)
			{
				$result[] = (object) array('text' => $column->sku, 'value' => $column->pi_id);
			}
		}

		echo json_encode($result);
		$app->close();
	}

	/**
	 * Change price for product item
	 *
	 * @return  void
	 */
	public function ajaxChangePrice()
	{
		// Check for request forgeries
		RedshopbHelperAjax::validateAjaxRequest();

		$app    = Factory::getApplication();
		$input  = $app->input;
		$result = new stdClass;

		$id = $input->post->getInt('id', 0);

		if (!$id)
		{
			$result->message     = Text::_('COM_REDSHOPB_INVALID_PRODUCT_PRICE_ID');
			$result->messageType = 'alert-error';
			header('HTTP/1.1 400 Bad Request');

			echo json_encode($result);
			$app->close();
		}

		$model = $this->getModel();

		if ($model->getIslockedByWebservice($id))
		{
			$result->message     = Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE');
			$result->messageType = 'alert-error';
			header('HTTP/1.1 409 Conflict');

			echo json_encode($result);
			$app->close();
		}

		$price = $input->getString('price', '');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update('#__redshopb_product_price')
			->set('price = ' . $db->q($price))
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$result->message     = Text::_('COM_REDSHOPB_PRODUCT_PRICE_FAILED');
			$result->messageType = 'alert-error';
			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
			$app->close();
		}

		$result->message     = Text::_('COM_REDSHOPB_PRODUCT_PRICE_SAVED');
		$result->messageType = 'alert-success';

		echo json_encode($result);
		$app->close();
	}

	/**
	 * Save price for product item
	 *
	 * @return void
	 */
	public function ajaxSaveNewPrice()
	{
		// Check for request forgeries
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$data = array(
			'id' => 0,
			'type_id' => $input->getInt('productItemId', 0),
			'type' => 'product_item',
			'sales_type' => 'all_customers',
			'sales_code' => '',
			'currency_id' => $input->getInt('currencyId', 38),
			'price' => $input->getFloat('price', 0.00),
			'starting_date' => '0000-00-00 00:00:00',
			'ending_date' => '0000-00-00 00:00:00',
			'allow_discount' => 1,
			'country_id' => null
		);

		$result = new stdClass;

		/** @var RedshopbModelAll_Price $model */
		$model = $this->getModel();

		if (!$model->save($data))
		{
			$result->message     = $model->getError();
			$result->messageType = 'alert-error';
			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
			$app->close();
		}

		$item = $model->getItem();

		if (!$item)
		{
			$result->message     = $model->getError();
			$result->messageType = 'alert-error';
			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
			$app->close();
		}

		$currencyAlpha3 = $input->getString('currencyAlpha3', 'DKK');
		$sku            = $input->getString('sku');
		$productId      = $input->get('product_id', 0);
		$itemUrl        = 'index.php?option=com_redshopb&product_id=' . $productId . '&tab=TablePrices';

		$return = $input->getBase64('return');

		if ($return)
		{
			$itemUrl .= '&return=' . $return;
		}

		$itemUrl = '&id=' . $item->id . '&task=all_price.edit';

		$htmlString   = array();
		$htmlString[] = '<div class="input-prepend input-append">';
		$htmlString[] = '<span class="add-on">' . $currencyAlpha3 . '</span>';
		$htmlString[] = '<input type="text" class="input-mini hasPopover" data-price-id="' . $item->id . '" value="' . $item->price . '"';
		$htmlString[] = ' name="jform[price][' . $item->id . ']" id="jform_price_' . $item->id . '" />';
		$htmlString[] = '<div class="btn-group js-record-controls">';
		$htmlString[] = '<button tabindex="-1" data-toggle="dropdown" class="btn dropdown-toggle"><span class="caret"></span></button>';
		$htmlString[] = '<ul class="dropdown-menu">';
		$htmlString[] = '<li><a href="javascript:void(0);" data-id="' . $item->id . '"';
		$htmlString[] = ' onclick="redSHOPB.products.updatePrice(event);" data-action="updatePrice">';
		$htmlString[] = '<i class="icon-save"></i> ';
		$htmlString[] = Text::_('JTOOLBAR_SAVE') . '</a></li>';
		$htmlString[] = '<li>';
		$htmlString[] = '<a href="' . RedshopbRoute::_($itemUrl) . '" data-action="leaveForm">';
		$htmlString[] = '<i class="icon-edit"></i> ' . Text::_('JTOOLBAR_EDIT') . '</a></li>';
		$htmlString[] = '<li><a href="javascript:void(0);" data-action="deletePrice"';
		$htmlString[] = 'data-id="' . $item->id . '" onclick="redSHOPB.products.deletePrice(event);">';
		$htmlString[] = '<i class="icon-trash"></i> ';
		$htmlString[] = Text::_('JTOOLBAR_DELETE') . '</a></li></ul></div></div>';
		$htmlString[] = '<div id="price_description_' . $item->id . '" class="hide">';
		$htmlString[] = '<table class="table table-condensed table-bordered">';

		if (!empty($sku))
		{
			$htmlString[] = '<tr><th>' . Text::_('COM_REDSHOPB_SKU') . '</th><td>' . htmlspecialchars($sku) . '</td></tr>';
		}

		$htmlString[] = '<tr><th>' . Text::_('COM_REDSHOPB_SALES_TYPE') . '</th><td>'
						. Text::_('COM_REDSHOPB_PRODUCT_PRICE_ALL_DEBTOR') . '</td></tr>';
		$htmlString[] = '<tr><th>' . Text::_('COM_REDSHOPB_START') . '</th><td> - </td></tr>';
		$htmlString[] = '<tr><th>' . Text::_('COM_REDSHOPB_END') . '</th><td> - </td></tr>';
		$htmlString[] = '</table></div></div>';

		$result->message     = Text::_('COM_REDSHOPB_PRODUCT_PRICE_SAVED');
		$result->messageType = 'alert-success';
		$result->html        = implode('', $htmlString);
		$result->item        = $item;

		echo json_encode($result);
		$app->close();
	}
}
