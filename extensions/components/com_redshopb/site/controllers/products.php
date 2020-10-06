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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
/**
 * Products Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProducts extends RedshopbControllerAdmin
{
	/**
	 * Discontinue a product.
	 *
	 * @return void
	 */
	public function discontinue()
	{
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// If we come from the product view, we have the product_id in the request
		if ('product' === $this->input->get('view'))
		{
			$cid = array($this->input->getInt('id'));
		}
		else
		{
			$cid = $this->input->get('cid', array(), 'array');
		}

		if (empty($cid))
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			/** @var RedshopbModelProduct $model */
			$model = $this->getModel('Product');

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Publish the items.
			if ($model->discontinueProducts($cid))
			{
				$ntext = $this->text_prefix . '_N_ITEMS_DISCONTINUED';
				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
