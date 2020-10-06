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
 * Layouts Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.13.0
 */
class RedshopbControllerLayout_List extends RedshopbControllerAdmin
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.13.0
	 */
	public function getModel($name = 'Layout_Item', $prefix = '', $config = null)
	{
		$config = empty($config) || !is_array($config) ? array('ignore_request' => false) : $config;

		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$id = Factory::getApplication()->input->getString('id');

		if (!$id)
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');

			// Remove the items.
			if ($model->delete($id))
			{
				$this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', 1));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}

			// Invoke the postDelete method to allow for the child class to access the model.
			$this->postDeleteHook($model, $id);
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
