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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 * My offer View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewMyoffer extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * Array of items
	 *
	 * @var  array
	 */
	protected $offerItems = array();

	/**
	 * @var  string
	 */
	protected $customerType;

	/**
	 * @var  integer
	 */
	protected $customerId;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$layout = $this->getLayout();
		$app    = Factory::getApplication();

		/** @var RedshopbModelMyoffer $model */
		$model = $this->getModel();

		switch ($layout)
		{
			case 'print':
			case 'edit':
				$this->item       = $this->get('Item');
				$this->offerItems = $model->getProducts($this->item->id);

				$offerTable = RedshopbTable::getAdminInstance('Offer');
				$offerTable->load($this->item->id);
				$this->customerType = $offerTable->get('customer_type', '');
				$this->customerId   = $offerTable->get('customer_id', 0);

				if (!RedshopbHelperOffers::canUse($this->item))
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_MYOFFERS_ERROR_NOT_HAVE_PERMISSION_FOR_EDIT'), 'error');
					$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myoffers', false));
				}

			break;
			case 'addcomment':
				$this->offId = $app->input->get('id', '', 'int');
			break;
			case 'requestoffer':

				$this->isModal = ($app->input->get('tmpl') == 'component');
				$this->title   = Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_REQUEST_OFFER');
				$model->set('context', 'com_redshopb.edit.send_offer_form');
				$model->set('formName', 'myoffer_request');
				$this->form = $model->getForm();
				break;
			default:

				break;
		}

		if (empty($this->form))
		{
			$this->form	= $this->get('Form');
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_MYOFFER_FORM');
		$state = $isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}
}
