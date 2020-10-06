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
 * Product Items Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct_Items extends RedshopbControllerAdmin
{
	/**
	 * Discontinue a product item.
	 *
	 * @return void
	 */
	public function discontinue()
	{
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// If we come from the product item view, we have the product_item_id in the request
		if ('product_item' === $this->input->get('view'))
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
			/** @var RedshopbModelProduct_Item $model */
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			try
			{
				$model->discontinue($cid);
				$ntext = $this->text_prefix . '_N_ITEMS_DISCONTINUED';
				$this->setMessage(Text::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage(Text::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
