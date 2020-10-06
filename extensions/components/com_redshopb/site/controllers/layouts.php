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
 * Layouts Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerLayouts extends RedshopbControllerAdmin
{
	/**
	 * Method to set the default layout for a client.
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function setDefault()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				Log::add(Text::_('COM_REDSHOPB_NO_LAYOUT_SELECTED'), Log::WARNING, 'jerror');
			}

			$pks = ArrayHelper::toInteger($pks);

			// Pop off the first element.
			$id    = array_shift($pks);
			$model = $this->getModel('Layout');
			$model->setDefault($id);
			$this->setMessage(Text::_('COM_REDSHOPB_SUCCESS_DEFAULT_SET'));
		}
		catch (Exception $e)
		{
			$this->setMessage(Text::_($e->getMessage()), 'error');
		}

		$this->setRedirect('index.php?option=com_redshopb&view=layouts');
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Get layouts to remove
		$layouts       = Factory::getApplication()->input->get('cid', array(), 'array');
		$defaultLayout = RedshopbHelperLayout::getDefaultLayout();
		$path          = JPATH_ROOT . '/media/com_redshopb/css/';

		// Get the model.
		$model = $this->getModel('Layouts');

		if (!is_array($layouts) || count($layouts) < 1)
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			JLoader::import('joomla.filesystem.file');

			// Make sure the item ids are integers
			$layouts = ArrayHelper::toInteger($layouts);

			// Remove the items.
			if ($model->delete($layouts, $defaultLayout))
			{
				foreach ($layouts as $layout)
				{
					if (JFile::exists($path . 'layout_' . $layout . '.css'))
					{
						try
						{
							JFile::delete($path . 'layout_' . $layout . '.css');
						}
						catch (Exception $e)
						{
							Log::add(Text::_($e->getMessage(), Log::WARNING, 'jerror'));
						}
					}
				}

				$this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($layouts)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $layouts);

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
