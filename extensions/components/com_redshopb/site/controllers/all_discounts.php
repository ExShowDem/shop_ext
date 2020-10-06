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

/**
 * All Discounts Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerAll_Discounts extends RedshopbControllerAdmin
{
	/**
	 * Ajax call to get discounts tab content.
	 *
	 * @return  void
	 */
	public function ajaxDiscounts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app           = Factory::getApplication();
		$productId     = $this->input->getInt('product_id');
		$productItemId = $this->input->getInt('product_item_id', 0);
		$returnObject  = $this->getReturnObject();

		if (!$productId)
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_ALL_PRICES_ERROR_INVALID_PRODUCT_ID');
			$returnObject->messageType = 'alert-error';

			header('HTTP/1.1 400 Bad Request');

			echo json_encode($returnObject);

			$app->close();
		}

		$model = RModelAdmin::getInstance('All_discounts', 'RedshopbModel');
		$model->getState();

		$model->setState('filter.product', $productId);
		$model->setState('filter.product_item_id', $productItemId);

		$formName   = $this->input->getString('formName', 'discountsForm');
		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		$tab    = $this->input->get('tab', 'Discounts');
		$return = 'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=' . $tab;

		if (!empty($productItemId))
		{
			$secondaryReturn = base64_encode($return);
			$return          = 'index.php?option=com_redshopb&view=product_item&layout=edit&id=' . $productItemId;
			$return         .= '&tab=Discounts&return=' . $secondaryReturn;
		}

		$layoutOptions = array(
			'state' => $model->getState(),
			'items' => $model->getItems(),
			'filter_form' => $model->getForm(),
			'activeFilters' => $model->getActiveFilters(),
			'pagination' => $pagination,
			'formName' => $formName,
			'action' => 'index.php?option=com_redshopb&view=all_discounts',
			'return' => base64_encode($return),
			'productItemId' => $productItemId,
			'productId' => $productId
		);

		$returnObject->html = RedshopbLayoutHelper::render('all_discounts.list', $layoutOptions);

		echo json_encode($returnObject);

		$app->close();
	}
}
