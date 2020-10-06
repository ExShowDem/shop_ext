<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Free_Shipping Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       2.0
 */
class RedshopbControllerFree_Shipping extends RedshopbControllerForm
{
	/**
	 * Method to delete an entry in the 'free_shipping_threshold_purchases' database table.
	 *
	 * @return  void
	 */
	public function delete()
	{
		$app   = Factory::getApplication();
		$cids  = $app->input->getInt('cid');
		$model = $this->getModel();

		if ($model->delete($cids))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_THRESHOLD_EXPENDITURE_DELETE_SUCCESS'));
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_THRESHOLD_EXPENDITURE_DELETE_ERROR'), 'error');
		}

		$this->setRedirect($this->getRedirectToListRoute());
	}
}
