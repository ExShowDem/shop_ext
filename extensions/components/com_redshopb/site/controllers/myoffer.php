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
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

/**
 * My offer Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerMyoffer extends RedshopbControllerForm
{
	/**
	 * Method to add comment when rejecting offer
	 *
	 * @return  boolean
	 */
	public function addComment()
	{
		$app        = Factory::getApplication();
		$formData   = new Registry($app->input->get('jform', '', 'array'));
		$comments   = $formData->get('comments', '');
		$offId      = $app->input->getint('offid', 0);
		$addComment = $app->input->getint('addcomment', 0);

		if (!$offId)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('COM_REDSHOPB_MYOFFER_NO_OFFER_SELECT_ERROR'),
				'error'
			);
		}

		// Access check.
		if (!$this->allowEdit(array('id' => $offId)))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		$model = $this->getModel('Myoffer');

		if ($addComment == 0)
		{
			$comments = '';
		}

		if ($model->addComment($offId, $comments))
		{
			if (!empty($comments))
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('COM_REDSHOPB_MYOFFER_COMMENTS_ADDED_SUCESSFULY')
				);
			}
			else
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('COM_REDSHOPB_MYOFFERS_REJECTED_SUCESSFULY')
				);
			}
		}
		else
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('COM_REDSHOPB_MYOFFER_COMMENTS_ADDED_ERROR'),
				'error'
			);
		}

		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=edit&id=' . $offId, false));

		return true;
	}

	/**
	 * Method to request an offer
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function requestOffer()
	{
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$data    = $app->input->post->get('jform', array(), 'array');
		$context = 'com_redshopb.edit.send_offer_form';

		$model = $this->getModel();
		$model->set('context', $context);
		$model->set('formName', 'myoffer_request');
		$prefixUrl = '';
		$tmpl      = $app->input->getCmd('tmpl', '');
		$source    = $app->input->getString('source', '');

		if ($tmpl)
		{
			$prefixUrl .= '&tmpl=' . $tmpl;
		}

		if ($source)
		{
			$prefixUrl .= '&source=' . $source;
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		$validate       = $model->validate($form, $data);
		$data['source'] = $app->input->get('source', '');

		if ($validate === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			$len = count($errors);

			for ($i = 0; $i < $len && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$app->setUserState($context . '.data', $data);
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=requestoffer' . $prefixUrl, false));

			return false;
		}

		$model = RedshopbModel::getAutoInstance('Offer');

		if ($model->saveOfferFromCart($data))
		{
			// Flush the data from the session
			$app->setUserState($context . '.data', null);
			$app->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_OFFER_SUCCESS'));

			if ($tmpl)
			{
				$prefixUrl .= '&send=1';
				$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=requestoffer' . $prefixUrl, false));
			}
			else
			{
				$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myoffers', false));
			}
		}
		else
		{
			$app->setUserState($context . '.data', $data);
			$app->enqueueMessage($model->getError(), 'warning');
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=requestoffer' . $prefixUrl, false));
		}

		return true;
	}

	/**
	 * Method for checkout from offer view
	 *
	 * @return  boolean   True on success. False otherwise.
	 */
	public function checkoutCart()
	{
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		$id = $this->input->getInt('id', 0);

		if (!$id)
		{
			$cids = $this->input->get('cid', array(), 'array');

			if (count($cids) > 0)
			{
				$id = current($cids);
			}
		}

		if (!$this->allowEdit(array('id' => $id)))
		{
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($id))
			);

			return false;
		}

		$model = $this->getModel('Myoffer');

		if (!$model->loadCart($id))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($id))
			);

			return false;
		}

		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		// If not found current customer, then set from offer info for get access to cart
		if (!$customerType || !$customerId)
		{
			$offer = RedshopbHelperCart::loadOffer($id);
			$app->setUserState('shop.customer_type', $offer['customer_type']);
			$app->setUserState('shop.customer_id', $offer['customer_id']);
		}

		$this->setMessage(Text::_('COM_REDSHOPB_ITEMS_ADDED_TO_CART'));
		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false));

		return true;
	}

	/**
	 * Method to check if you can save a new or existing record.
	 * Task Save disabled here, because not allow for myOffer view
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 */
	protected function allowSave($data, $key = 'id')
	{
		return false;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		if (!parent::allowEdit($data, $key))
		{
			return false;
		}

		$model = $this->getModel('MyOffer');
		$table = $model->getTable('offer');

		if ($table->load((int) $data[$key]))
		{
			return RedshopbHelperOffers::canUse($table);
		}

		return false;
	}

	/**
	 * Method to print an offer
	 *
	 * @return void
	 */
	public function printPDF()
	{
		$app = Factory::getApplication();
		$id  = $app->input->get('id', 0, 'int');

		$model       = $this->getModel();
		$item        = (object) $model->getItem($id)->getProperties();
		$offerItems  = $model->getProducts($item->id);
		$endCustomer = RedshopbHelperOrder::getEntityFromCustomer($item->customer_id, $item->customer_type);
		$customer    = RedshopbHelperCompany::getCustomerCompanyByCustomer($item->customer_id, $item->customer_type);

		$vendor        = RedshopbEntityCompany::getInstance($item->company_id)->getParent();
		$vendorAddress = RedshopbEntityAddress::getInstance($vendor->address_id)->getExtendedData();

		if (!isset($vendorAddress->country))
		{
			$vendorAddress->country = null;
		}

		$offerEmployee   = null;
		$offerDepartment = null;

		switch ($item->customer_type)
		{
			case 'employee':
				$offerEmployee   = $endCustomer;
				$offerDepartment = RedshopbHelperUser::getUserDepartment($offerEmployee->id);
				break;

			case 'department':
				$offerDepartment = $endCustomer;
				break;
		}

		$html = RedshopbLayoutHelper::render(
			'myoffer.pdf.header',
			array(
				'company'        => $customer,
				'comment'         => $item->comments,
				'employee'   => $offerEmployee,
				'department' => $offerDepartment,
				'vendor'     => $vendor,
				'vendorAddress' => $vendorAddress
			)
		);

		$html .= RedshopbLayoutHelper::render(
			'myoffer.pdf.print',
			array(
				'item' => $item,
				'offerItems' => $offerItems
			)
		);

		$mPDF       = RedshopbHelperMpdf::getInstance();
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/redcore/css/component.min.css');
		$mPDF->WriteHTML($stylesheet, 1);
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/com_redshopb/css/pdf_order.css');
		$mPDF->WriteHTML($stylesheet, 1);

		$mPDF->SetTitle(Text::_('COM_REDSHOPB_PDF_MYOFFER'));
		$mPDF->SetSubject(Text::_('COM_REDSHOPB_PDF_MYOFFER'));
		$mPDF->AddPage();

		$mPDF->WriteHTML($html, 2);

		$now = new Date;
		$mPDF->Output('Offer-' . $customer->name . '-' . $endCustomer->name . '.pdf', 'D');

		$app->close();
	}
}
