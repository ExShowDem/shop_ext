<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
/**
 * My Offers Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.6
 */
class RedshopbControllerMyoffers extends RedshopbControllerAdmin
{
	/**
	 * Method for accept an offer
	 *
	 * @return  void
	 */
	public function accept()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$app  = Factory::getApplication();
		$cids = $app->input->get('cid', array(), 'array');

		if (!is_array($cids) || count($cids) < 1)
		{
			Log::add(Text::_('COM_REDSHOPB_MYOFFERS_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			$cid   = ArrayHelper::toInteger($cid);
			$model = $this->getModel();

			if ($model->acceptOffer($cids))
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('COM_REDSHOPB_MYOFFERS_STATUS_CHANGED_SUCESSFULY')
				);
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		// Set redirect
		$return = $this->input->getBase64('return');

		if ($return)
		{
			$this->setRedirect(base64_decode($return));
		}
		else
		{
			$this->setRedirect($this->getRedirectToListRoute());
		}
	}

	/**
	 * Method for accept an offer
	 *
	 * @return  void
	 */
	public function reject()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$app  = Factory::getApplication();
		$cids = $app->input->get('cid', array(), 'array');

		if (!is_array($cids) || count($cids) < 1)
		{
			Log::add(Text::_('COM_REDSHOPB_MYOFFERS_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			$cid   = ArrayHelper::toInteger($cid);
			$model = $this->getModel();

			if ($model->rejectOffer($cids))
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('COM_REDSHOPB_MYOFFERS_STATUS_CHANGED_SUCESSFULY')
				);
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
