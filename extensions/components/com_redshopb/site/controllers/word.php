<?php
/**
 * @package     Aesir.E-Commerce.Backend
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
 * Word Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerWord extends RedshopbControllerForm
{
	/**
	 * Caching user data before reload page
	 *
	 * @return boolean
	 */
	public function reload()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$data    = $app->input->post->get('jform', array(), 'array');
		$context = "$this->option.edit.$this->context";

		// Save the data in the session.
		$app->setUserState($context . '.data', $data);
		$model = $this->getModel();
		$table = $model->getTable();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		// Redirect back to the edit screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
		);

		return true;
	}
}
