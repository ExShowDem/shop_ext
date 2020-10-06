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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Collections Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCollections extends RedshopbControllerAdmin
{
	/**
	 * Generate product sheets for a collection
	 *
	 * @return void
	 */
	public function generateProductSheets()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		$app = Factory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');

			// Set redirect
			$this->setRedirect($this->getRedirectToListRoute());
		}

		$cid = (int) $cid[0];

		/** @var RedshopbModelProduct_sheets $model */
		$model = RModelAdmin::getInstance('Product_sheets', 'RedshopbModel');

		$model->printPDF($cid);

		$app->close();
	}
}
