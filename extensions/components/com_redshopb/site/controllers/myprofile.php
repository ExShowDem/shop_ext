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

/**
 * My Profile Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerMyprofile extends RedshopbControllerAdmin
{
	/**
	 * Method to change password
	 *
	 * @return boolean
	 */
	public function changePassword()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app    = Factory::getApplication();
		$model  = $this->getModel();
		$user   = Factory::getUser();
		$userId = (int) $user->get('id');
		Factory::getLanguage()->load('com_users');

		// Get the user data.
		$data = $app->input->post->get('jform', array(), 'array');

		// Force the ID to this user.
		$data['id'] = $userId;

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
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

			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myprofile', false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->changeUserPassword($validData);

		// Check for errors.
		if ($return === false)
		{
			// Redirect back to the edit screen.
			$this->setMessage($model->getError(), 'warning');
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myprofile', false));

			return false;
		}

		$this->setMessage(Text::_('COM_REDSHOPB_MYPROFILE_PASSWORD_SAVE_SUCCESS'));
		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myprofile', false));

		return true;
	}
}
