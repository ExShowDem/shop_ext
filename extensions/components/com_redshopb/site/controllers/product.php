<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
/**
 * Product Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_PRODUCT';

	/**
	 * Gets the URL arguments to append to an item redirect.
	 * overridden to add the tab value to the URL
	 *
	 * @param   integer|null  $recordId  The primary key id for the item.
	 * @param   string        $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$tab    = $this->input->getString('tab', $this->input->post->getString('tab', 'details'));

		if ($tab != 'details')
		{
			$append .= '&tab=' . $tab;
		}

		return $append;
	}

	/**
	 * Generate the items.
	 *
	 * @return  boolean
	 */
	public function generate()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$productId = $this->input->getInt('id');

		/** @var RedshopbModelProduct $model */
		$model = $this->getModel();

		if ($model->generateItems($productId))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_PRODUCT_GENERATE_ITEMS_SUCCESS'));
		}
		else
		{
			$error = $model->getError();

			if (empty($error))
			{
				$error = Text::_('COM_REDSHOPB_PRODUCT_GENERATE_ITEMS_ERROR');
			}

			$this->setMessage($error, 'error');
		}

		$this->setRedirect(
			RedshopbRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($productId, 'id'), false
			)
		);

		return true;
	}

	/**
	 * Ajax call to get attributes tab content.
	 *
	 * @return  void
	 */
	public function ajaxCompositions()
	{
		$this->getRelatedItem('Compositions', 'list.product', 'Product_Compositions');
	}

	/**
	 * Ajax call to get ordering tab content.
	 *
	 * @return  void
	 */
	public function ajaxOrdering()
	{
		// Validate ajax request
		RedshopbHelperAjax::validateAjaxRequest();

		$app       = Factory::getApplication();
		$input     = $app->input;
		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, 'Ordering');

		try
		{
			$model         = $this->getModel('Product');
			$layoutOptions = array(
				'product'   => $productId,
				'orderings' => $model->getOrderingValues($productId)
			);

			$this->renderTab('product.ordering', $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';

			$this->setErrorHtml($result, 'Ordering', 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Method to get a return object for ajax tab requests
	 *
	 * @param   int     $productId  The product id to associate with the request
	 * @param   string  $tabName    Name of the tab
	 *
	 * @return  mixed             Object on success, or sends 400 response with json payload
	 *
	 * @throws  Exception
	 */
	private function getReturnObject($productId, $tabName)
	{
		$returnObject              = new stdClass;
		$returnObject->message     = '';
		$returnObject->messageType = '';

		if (!empty($productId))
		{
			return $returnObject;
		}

		$returnObject->message     = Text::_('COM_REDSHOPB_PRODUCT_INVALID_PRODUCT_ID');
		$returnObject->messageType = 'alert-error';

		$this->setErrorHtml($returnObject, $tabName, $returnObject->message);

		header('HTTP/1.1 400 Bad Request');
		echo json_encode($returnObject);
		Factory::getApplication()->close();
	}

	/**
	 * Method to set a standardized error message to the return object for ajax tab requests
	 *
	 * @param   object  $returnObject  The object instance to render using json_encode
	 * @param   string  $tabName       Name of the tab
	 * @param   string  $errorMsg      The pre-translated error message
	 *
	 * @return  void
	 */
	private function setErrorHtml($returnObject, $tabName, $errorMsg)
	{
		$html   = array();
		$html[] = '<div class="alert alert-error">';
		$html[] = '<h3 class="alert-header">' . $errorMsg . '</h3>';
		$html[] = '<p >' . Text::_('COM_REDSHOPB_PRODUCT_ERROR_LOADING_TAB_DESCRIPTION') . '</p>';
		$html[] = '<p class="center reload-control">';
		$html[] = '<a href="javascript:void(0);" onclick="redSHOPB.products.loadTab(\'' . $tabName . '\');" class="btn">';
		$html[] = '<i class="icon-refresh"></i> ' . Text::_('COM_REDSHOPB_PRODUCT_ERROR_LOADING_TAB_RETRY');
		$html[] = '</a>';
		$html[] = '</div>';

		$returnObject->html = implode('', $html);
	}

	/**
	 * Method to render the tab layout to the buffer
	 *
	 * @param   string  $layoutId       Dot seperated layout id
	 * @param   array   $layoutOptions  Options to use for the layout
	 * @param   object  $returnObject   The object instance to render using json_encode
	 *
	 * echos json_encode($returnObject) after setting the layout to $returnObject->html
	 *
	 * @return void
	 */
	private function renderTab($layoutId, $layoutOptions, $returnObject)
	{
		$returnObject->html = RedshopbLayoutHelper::render($layoutId, $layoutOptions);

		$session                     = Factory::getSession();
		$registry                    = $session->get('registry');
		$returnObject->session       = $registry;
		$returnObject->layoutOptions = $layoutOptions;

		echo json_encode($returnObject);
	}

	/**
	 * Ajax call to get attributes tab content.
	 *
	 * @return  void
	 */
	public function ajaxAttributes()
	{
		$this->getRelatedItem('Attributes', 'filter.product_id', 'Product_Attributes', false);
	}

	/**
	 * Ajax call to get combinations tab content.
	 *
	 * @return  void
	 */
	public function ajaxCombinations()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, 'Combinations');

		try
		{
			/** @var RedshopbModelProduct $productModel */
			$productModel = RModelAdmin::getInstance('Product', 'RedshopbModel');

			$layoutOptions = array(
				'formName'             => 'combinationsForm',
				'attributes'           => $productModel->getAttributes($productId),
				'action'               => 'index.php?option=com_redshopb&view=product&layout=edit&model=product_items&product_id=' . $productId,
				'return'               => base64_encode(
					'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=Combinations'
				),
				'staticTypes'          => $productModel->getStaticTypes($productId),
				'dynamicTypes'         => $productModel->getDynamicTypes($productId),
				'issetItems'           => $productModel->getIssetItems($productId),
				'issetDynamicVariants' => $productModel->getIssetDynamicVariants($productId),
				'productId'            => $productId
			);

			$this->renderTab('product.combinations', $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, 'Compositions', 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Ajax call to get stock tab content.
	 *
	 * @return  void
	 */
	public function ajaxTablePrices()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, 'TablePrices');

		try
		{
			/** @var RedshopbModelProduct $productModel */
			$productModel = RModelAdmin::getInstance('Product', 'RedshopbModel');

			/** @var RedshopbModelAll_Prices $allPricesModel */
			$allPricesModel = RModelAdmin::getInstance('All_Prices', 'RedshopbModel');
			$allPricesModel->set('context', 'redshopb.product.all_prices');

			$state = $allPricesModel->getState();

			$allPricesModel->set('filterFormName', 'filter_prices');
			$form     = $allPricesModel->getForm();
			$formName = 'tablePricesForm';

			$layoutOptions = array(
				'state'                => $state,
				'filter_form'          => $form,
				'activeFilters'        => $allPricesModel->getActiveFilters(),
				'staticTypes'          => $productModel->getStaticTypes($productId),
				'dynamicTypes'         => $productModel->getDynamicTypes($productId),
				'issetItems'           => $productModel->getIssetItems($productId),
				'productInfo'          => $productModel->getItem($productId),
				'issetItemsPrices'     => $allPricesModel->getIssetItemsPrices($productId),
				'issetDynamicVariants' => $productModel->getIssetDynamicVariants($productId),
				'productId'            => $productId,
				'formName'             => $formName,
				'showToolbar'          => false,
				'action'               => 'index.php?option=com_redshopb&view=product&layout=edit&model=all_prices&product_id=' . $productId,
				'return'               => base64_encode(
					'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=TablePrices'
				)
			);

			$this->renderTab('product.prices', $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, 'Compositions', 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Ajax call to get prices tab content.
	 *
	 * @return  void
	 */
	public function ajaxPrices()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, 'Prices');

		try
		{
			// Initialized model and state (state initialization needed to fix product Id to the filter form).
			/** @var RedshopbModelAll_Prices $model */
			$model = RModelAdmin::getInstance('All_Prices', 'RedshopbModel');
			$model->set('filterFormName', 'filter_all_prices');

			// Check if product item in filter from other product - reset product item
			$productItemId = (int) $model->getState('filter.product_item_id', 0);

			if (!empty($productItemId) && !$model->isAvailableItemId($productId, $productItemId))
			{
				$model->setState('filter.product_item_id', '');
			}

			$model->setState('layout_filter.product_id', $productId);

			// Also initializes product_id in filter form
			$form = $model->getForm();
			$form->setValue('product_id', 'layout_filter', $productId);

			// Sets the other variables including pagination and items
			$formName   = 'pricesForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			$layoutOptions = array(
				'state'         => $model->getState(),
				'items'         => $model->getItems(),
				'filter_form'   => $form,
				'activeFilters' => $model->getActiveFilters(),
				'pagination'    => $pagination,
				'formName'      => $formName,
				'action'        => 'index.php?option=com_redshopb&view=product&layout=edit&model=all_prices&product_id=' . $productId,
				'return'        => base64_encode('index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=Prices'),
				'productId'     => $productId,
				'showToolbar'   => true
			);

			$this->renderTab('product.all_prices', $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, 'Compositions', 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Ajax call to get stock tab content.
	 *
	 * @return  void
	 */
	public function ajaxStock()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, 'Stock');

		try
		{
			$productEntity = RedshopbEntityProduct::getInstance($productId);
			$productEntity->loadItem();

			$layoutOptions = array(
				'productId'    => $productId,
				'stockrooms'   => $productEntity->getStockRooms($productId),
				'unitMeasure'  => $productEntity->getUnitMeasure()->getItem()
			);

			$this->renderTab('product.stock', $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, 'Stock', 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Ajax call to get collections tab content.
	 *
	 * @return  void
	 */
	public function ajaxCollections()
	{
		$this->getRelatedItem('Collections', 'filter.product', 'Collections', false);
	}

	/**
	 * Ajax call to get descriptions tab content.
	 *
	 * @return  void
	 */
	public function ajaxDescriptions()
	{
		$this->getRelatedItem('Descriptions', 'filter.product_id', 'Descriptions');
	}

	/**
	 * Ajax call to get discounts tab content.
	 *
	 * @return  void
	 */
	public function ajaxDiscounts()
	{
		$this->getRelatedItem('Discounts', 'filter.product', 'All_Discounts', false);
	}

	/**
	 * Ajax call to get descriptions tab content.
	 *
	 * @return  void
	 */
	public function ajaxWashCareSpecs()
	{
		$this->getRelatedItem('Wash_Care_Specs', 'list.product', 'Wash_Care_Specs');
	}

	/**
	 * Ajax call to get accessories tab content.
	 *
	 * @return  void
	 */
	public function ajaxAccessories()
	{
		$this->getRelatedItem2('Accessories', 'accessoriesForm');
	}

	/**
	 * Ajax call to get selected accessories tab content.
	 *
	 * @return  void
	 */
	public function ajaxSelectedAccessories()
	{
		$this->getRelatedItem3('SelectedAccessories', 'selectedaccessories', 'Product_Accessories');
	}

	/**
	 * Method for adding a  product as accessory.
	 *
	 * @return  void
	 */
	public function ajaxAddProductAccessory()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app                = Factory::getApplication();
		$accessoryProductId = $app->input->getInt('accessory_product_id', 0);
		$productAttrId      = $app->input->getInt('product_attr_id', 0);
		$productId          = $app->input->getInt('product_id', 0);

		if (!$accessoryProductId || !$productId)
		{
			echo '0';
			$app->close();
		}

		/** @var RedshopbModelProduct $model */
		$model = $this->getModel('Product');

		echo (int) $model->addProductAccessory($accessoryProductId, $productId, $productAttrId);

		$app->close();
	}

	/**
	 * Method for removing a  product as accessory.
	 *
	 * @return  void
	 */
	public function ajaxRemoveProductAccessory()
	{
		$this->getRelatedProduct('accessory_product_id', 'removeProductAccessory');
	}

	/**
	 * Ajax call to get Complimentary Products tab content.
	 *
	 * @return  void
	 */
	public function ajaxComplimentaryProducts()
	{
		$this->getRelatedItem2('Complimentary_Products', 'ComplimentaryProductsForm');
	}

	/**
	 * Ajax call to get selected Complimentary Products tab content.
	 *
	 * @return  void
	 */
	public function ajaxSelectedComplimentaryProducts()
	{
		$this->getRelatedItem3('SelectedComplimetaries', 'selectedcomplimentaryproducts', 'Product_Complimentary_Products');
	}

	/**
	 * Method for adding a  product as complimentary.
	 *
	 * @return  void
	 */
	public function ajaxAddProductComplimentary()
	{
		$this->getRelatedProduct('complimentary_product_id', 'addProductComplimentary');
	}

	/**
	 * Method for removing a  product as complimentary.
	 *
	 * @return  void
	 */
	public function ajaxRemoveProductComplimentary()
	{
		$this->getRelatedProduct('complimentary_product_id', 'removeProductComplimentary');
	}

	/**
	 * Ajax call to get main category from a certain company
	 *
	 * @return  void
	 */
	public function ajaxMainCategory()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$companyId = $this->input->getInt('company_id', 0);
		$category  = @json_decode($this->input->post->getString('category_id'));

		/** @var RedshopbModelProduct $model */
		$model = $this->getModel();
		$model->setState($model->getName() . '.id', $this->input->getInt('product_id', 0));
		$form = $model->getForm();
		$form->setFieldAttribute('category_id', 'emptystart', 'false');
		$form->setFieldAttribute('category_id', 'companyid', $companyId);
		$form->setValue('category_id', null, $category);

		echo $form->getInput('category_id');

		Factory::getApplication()->close();
	}

	/**
	 * Ajax call to get categories from a certain company
	 *
	 * @return  void
	 */
	public function ajaxCompanyCategories()
	{
		$this->getRelatedCompany('categories', 'categories');
	}

	/**
	 * Ajax call to get tags from a certain company
	 *
	 * @return  void
	 */
	public function ajaxCompanyTags()
	{
		$this->getRelatedCompany('tags', 'tag_id');
	}

	/**
	 * Ajax call to set product image State
	 *
	 * @return  void
	 */
	public function toggleImageState()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		$data = array(
			'id'         => $input->get('image_id', 0, 'int'),
			'state'      => $input->get('state', 0, 'int'),
			'product_id' => $input->get('product_id', 0, 'int')
		);

		echo json_encode($this->imageSave($data));

		$app->close();
	}

	/**
	 * Method to save an image to the media table
	 *
	 * @param   array  $data  Data to save
	 *
	 * @return  object
	 */
	private function imageSave($data)
	{
		/** @var RedshopbModelMedia $model */
		$model  = RModelAdmin::getInstance('Media', 'RedshopbModel');
		$return = $model->save($data);

		if ($return->success)
		{
			$return->message = Text::_('COM_REDSHOPB_FORM_SAVE_SUCESS');
		}

		return $return;
	}

	/**
	 * Ajax call to save product image
	 *
	 * @return  void
	 */
	public function ajaxImageSave()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		$data               = $input->get('jform', array(), 'array');
		$data['product_id'] = $input->get('product_id', 0);

		echo json_encode($this->imageSave($data));

		$app->close();
	}

	/**
	 * Ajax call to add or edit product image
	 *
	 * @return  void
	 */
	public function ajaxImageEdit()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$imageID   = $input->getInt('image_id', 0);
		$productId = $input->getInt('product_id', 0);

		if ($productId <= 0)
		{
			$app->close();
		}

		/** @var RedshopbModelMedia $model */
		$model = RModelAdmin::getInstance('Media', 'RedshopbModel');
		$item  = $model->getItem($imageID);

		/** @var Form $form */
		$form = $model->getForm(array(), false);
		$form->bind($item);

		if (!$imageID)
		{
			$form->removeField('ordering');
		}

		// @todo find out why we are loading the attributes model here. *bump*
		$attributeModel = RModelAdmin::getInstance('Product_Attributes', 'RedshopbModel');
		$attributeModel->setState('filter.product_id', $productId);

		echo RedshopbLayoutHelper::render('media.edit.image', array(
				'form'        => $form,
				'item'        => $item,
				'formName'    => 'productImageForm',
				'showToolbar' => false
			)
		);

		$app->close();
	}

	/**
	 * Ajax call to remove product image
	 *
	 * @return  void
	 */
	public function ajaxImageDelete()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();
		/** @var RedshopbModelMedia $model */
		$model   = RModelAdmin::getInstance('Media', 'RedshopbModel');
		$imageId = $app->input->get('image_id', 0, 'int');

		$return = $model->deleteImage($imageId);

		if ($return->success)
		{
			$return->message = Text::_('COM_REDSHOPB_FORM_DELETE_SUCESS');
		}

		echo json_encode($return);

		$app->close();
	}

	/**
	 * Product Image Sync
	 *
	 * @return  void
	 */
	public function ajaxImageSync()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();
		$this->addModelPath(JPATH_ADMINISTRATOR . '/components/com_redshopb/models');

		/** @var RedshopbModelSync $model */
		$model = $this->getModel('Sync');

		/** @var RedshopbTableSyncEdit $table */
		$table = $model->getTable('SyncEdit', 'RedshopbTable');

		if ($table->load(array('name' => 'GetProductPicture')))
		{
			ob_start();
			$return     = $model->syncSelectedItem($table->id, false, true, array('productId' => $app->input->getInt('product_id')));
			$syncOutput = ob_get_contents();
			ob_end_clean();

			if (empty($return['success']) || $return['success'] === false)
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_MEDIA_SYNC_FAILED'), 'error');
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_MEDIA_SYNC_FAILED'), 'error');
		}

		$messages           = $app->getMessageQueue();
		$return['messages'] = array();

		if (is_array($messages))
		{
			foreach ($messages as $msg)
			{
				switch ($msg['type'])
				{
					case 'message':
						$typeMessage = 'success';
						break;
					case 'notice':
						$typeMessage = 'info';
						break;
					case 'error':
						$typeMessage = 'important';
						break;
					case 'warning':
						$typeMessage = 'warning';
						break;
					default:
						$typeMessage = $msg['type'];
				}

				$return['messages'][] = array('message' => $msg['message'], 'type_message' => $typeMessage);
			}
		}

		echo json_encode($return);

		$app->close();
	}

	/**
	 * Method fot get vendor of an product
	 *
	 * @return  void
	 */
	public function ajaxloadvendor()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$productId = $this->input->getInt('productid', 0);

		if (!$productId)
		{
			echo Text::_('COM_REDSHOPB_DISCOUNT_ERROR_PRODUCT_DEBTOR_NOT_AVAILABLE');

			Factory::getApplication()->close();
		}

		/** @var RedshopbEntityCompany $productVendor */
		$productVendor = RedshopbEntityProduct::getInstance($productId)->getCompany();

		if (!$productVendor->hasId())
		{
			$productVendor = RedshopbHelperCompany::getMain();
		}

		/** @var RedshopbModelAll_Discount $model */
		$model = RedshopbModel::getAutoInstance('All_Discount');
		$form  = $model->getForm();

		$form->setFieldAttribute('sales_debtor_id', 'emptystart', 'false');
		$form->setFieldAttribute('sales_debtor_id', 'restriction', 'company');
		$form->setFieldAttribute('sales_debtor_id', 'companyid', $productVendor->id);

		Factory::getApplication()->close();
	}

	/**
	 * Print product details as PDF file.
	 *
	 * PDF document on success, null otherwise.
	 *
	 * @return  void
	 */
	public function printPDF()
	{
		$app          = Factory::getApplication();
		$id           = $app->input->get->get('id', 0, 'int');
		$model        = RModel::getAdminInstance('Shop');
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		// Set product filter
		$model->setState('filter.product_id', $id);
		$customerVendor = RedshopbHelperCompany::getVendorCompanyByCustomer($customerId, $customerType);

		// Set categories filter
		$availableCategories = (string) RedshopbHelperCategory::getCustomerCategories(
			1, false, $customerVendor->get('id'), 'comma', 0, 0, false, 99
		);
		$availableCategories = explode(',', $availableCategories);
		$model->setState('filter.product_category', $availableCategories);

		$extThis           = new stdClass;
		$data              = $model->getItems();
		$extThis->product  = $model->prepareItemsForShopView($data, $customerId, $customerType);
		$product           = $extThis->product->items[0];
		$config            = RedshopbApp::getConfig();
		$width             = $config->get('product_image_width', 256);
		$height            = $config->get('product_image_height', 256);
		$isShop            = RedshopbHelperPrices::displayPrices();
		$fieldsData        = RedshopbHelperProduct::loadProductFields($product->id, true);
		$categoryId        = RedshopbHelperCategory::getUrlCategoryId($product->categories);
		$extThis->category = RedshopbEntityCategory::load($categoryId);
		$category          = $extThis->category->getItem();
		$isOneProduct      = false;

		if (count($extThis->product->dropDownTypes) == 0 && count($extThis->product->staticTypes) == 0)
		{
			$isOneProduct = true;
		}

		$html = RedshopbHelperTemplate::renderTemplate('product-print', 'shop', $product->print_template_id, compact(array_keys(get_defined_vars())));

		// Start pdf code
		$mPDF       = RedshopbHelperMpdf::getInstance();
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/redcore/css/component.min.css');
		$mPDF->WriteHTML($stylesheet, 1);

		$mPDF->SetTitle(Text::_('COM_REDSHOPB_PDF_PRODUCT'));
		$mPDF->SetSubject(Text::_('COM_REDSHOPB_PDF_PRODUCT'));
		$mPDF->AddPage();

		$mPDF->WriteHTML($html, 2);
		$mPDF->Output($product->sku . '_' . $product->name . '.pdf', 'D');

		$app->close();
	}

	/**
	 * Get related function for ajaxCompositions, ajaxDescriptions, ajaxWashCareSpecs, ajaxAttributes, ajaxCollections, ajaxDiscounts
	 *
	 * @param   string   $tabName         Tab name with uppercase letters and underscore.
	 * @param   string   $modelSetState   Property of Set State.
	 * @param   string   $modelInstance   String value of type in getInstance.
	 * @param   boolean  $setProductId    Boolean value for setting product ID.
	 *
	 * @return  void
	 */
	private function getRelatedItem($tabName, $modelSetState, $modelInstance, $setProductId = true)
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, str_replace('_', '', $tabName));

		try
		{
			/** @var RModelList $model */
			$model = RModelAdmin::getInstance($modelInstance, 'RedshopbModel');
			$model->setState($modelSetState, $productId);
			$formName   = str_replace('_', '', lcfirst($tabName)) . 'Form';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			if ($setProductId)
			{
				$this->input->set('product_id', $productId);
				$form = $model->getForm();
				$form->setFieldAttribute('product_id', 'type', 'hidden', 'filter');
			}
			else
			{
				$form = $model->getForm();
			}

			$layoutOptions = array(
				'state'         => $model->getState(),
				'items'         => $model->getItems(),
				'pagination'    => $pagination,
				'productId'     => $productId,
				'activeFilters' => $model->getActiveFilters(),
				'filter_form'   => $form,
				'formName'      => $formName,
				'showToolbar'   => true,
				'action'        => RedshopbRoute::_(
					'index.php?option=com_redshopb&view=product&layout=edit&model=' . strtolower($modelInstance) . '&product_id=' . $productId
				),
				'return'        => base64_encode(
					'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=' . str_replace('_', '', $tabName)
				)
			);

			$this->renderTab('product.' . strtolower($tabName), $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, str_replace('_', '', $tabName), 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Get related function for ajaxComplimentaryProducts, ajaxAccessories
	 *
	 * @param   string   $tabName         Tab name with uppercase letters and underscore.
	 * @param   string   $tabFormName     String value of form name.
	 *
	 * @return  void
	 */
	private function getRelatedItem2($tabName, $tabFormName)
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, $tabName);

		try
		{
			/** @var RedshopbModelProducts $model */
			$model = RedshopbModel::getAutoInstance('Products');
			$model->setState('filter.notInProducts', array($productId));
			$model->setState('list.allow_parent_companies_products', true);

			$formName   = $tabFormName;
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);
			$model->setState('filter.include_categories', true);
			$model->setState('filter.include_tags', true);
			$model->setState('include_objects', true);
			$form = $model->getForm();

			$layoutOptions = array(
				'state'         => $model->getState(),
				'items'         => $model->getItems(),
				'pagination'    => $pagination,
				'productId'     => $productId,
				'activeFilters' => $model->getActiveFilters(),
				'filter_form'   => $form,
				'formName'      => $formName,
				'showToolbar'   => true,
				'action'        => RedshopbRoute::_('index.php?option=com_redshopb&view=product&layout=edit&product_id=' . $productId),
				'return'        => base64_encode(
					'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=' . str_replace('_', '', $tabName)
				),
				'button'        => 1
			);

			$this->renderTab('product.' . str_replace('_', '', strtolower($tabName)), $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, str_replace('_', '', $tabName), 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Get related function for ajaxSelectedAccessories, ajaxSelectedComplimentaryProducts
	 *
	 * @param   string   $tabName         Tab name with uppercase letters and underscore.
	 * @param   string   $tabFormName     String value of form name.
	 * @param   string   $modelInstance   String value of type in getInstance.
	 *
	 * @return  void
	 */
	private function getRelatedItem3($tabName, $tabFormName, $modelInstance)
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId = $input->getInt('product_id', 0);
		$result    = $this->getReturnObject($productId, $tabName);

		try
		{
			/** @var RedshopbModelList $model */
			$model      = RedshopbModel::getAutoInstance($modelInstance);
			$formName   = $tabFormName . 'Form';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);
			$form = $model->getForm();

			$model->setState('list.getSelected', true);
			$model->setState('filter.product_id', $productId);

			$layoutOptions = array(
				'state'         => $model->getState(),
				'items'         => $model->getItems(),
				'pagination'    => $pagination,
				'productId'     => $productId,
				'activeFilters' => $model->getActiveFilters(),
				'filter_form'   => $form,
				'formName'      => $formName,
				'showToolbar'   => true,
				'action'        => RedshopbRoute::_(
					'index.php?option=com_redshopb&view=product&layout=edit&model=' . $modelInstance . '&product_id=' . $productId
				),
				'return'        => base64_encode(
					'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=' . $tabName
				),
			);

			$this->renderTab('product.' . $tabFormName, $layoutOptions, $result);
		}
		catch (Exception $e)
		{
			$result->message     = $e->getMessage();
			$result->messageType = 'alert-error';
			$this->setErrorHtml($result, $tabName, 'Internal Server Error');

			header('HTTP/1.1 500 Internal Server Error');

			echo json_encode($result);
		}

		$app->close();
	}

	/**
	 * Get related function for ajaxRemoveProductAccessory, ajaxAddProductComplimentary, ajaxRemoveProductComplimentary
	 *
	 * @param   string   $productIdName  String value of product id name.
	 * @param   string   $operation      String name of function.
	 *
	 * @return  void
	 */
	private function getRelatedProduct($productIdName, $operation)
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app              = Factory::getApplication();
		$relatedProductId = $app->input->getInt($productIdName, 0);
		$productId        = $app->input->getInt('product_id', 0);

		if (!$relatedProductId || !$productId)
		{
			echo '0';
			$app->close();
		}

		$model = $this->getModel('Product');

		echo (int) $model->$operation($relatedProductId, $productId);

		$app->close();
	}

	/**
	 * Get related function for ajaxCompanyCategories, ajaxCompanyTags
	 *
	 * @param   string   $getId        String value of product id name.
	 * @param   string   $fieldAName   String name of field attribute.
	 *
	 * @return  void
	 */
	private function getRelatedCompany($getId, $fieldAName)
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$companyId = $this->input->getInt('company_id', 0);
		$value     = $this->input->post->get($getId, array(), 'array');
		$value     = ArrayHelper::toInteger($value);

		/** @var RedshopbModelProduct $model */
		$model = $this->getModel();

		$model->setState($model->getName() . '.id', $this->input->getInt('product_id', 0));

		/** @var Form $form */
		$form = $model->getForm();
		$form->setFieldAttribute($fieldAName, 'emptystart', 'false');
		$form->setFieldAttribute($fieldAName, 'companyid', $companyId);
		$form->setValue($fieldAName, null, $value);

		echo $form->getInput($fieldAName);

		Factory::getApplication()->close();
	}
}
