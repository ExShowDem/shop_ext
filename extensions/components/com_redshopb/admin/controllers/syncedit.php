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
 * Sync edit Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerSyncEdit extends RControllerForm
{
	/**
	 * @var string
	 */
	protected $view_item = 'syncedit';

	/**
	 * @var string
	 */
	protected $view_list = 'sync';

	/**
	 * Method to change plugin name.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function changeplugin($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$data    = $this->input->post->get('jform', array(), 'array');
		$model   = $this->getModel();
		$table   = $model->getTable();
		$context = "$this->option.edit.$this->context";

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

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// Save the data in the session.
		$app->setUserState($context . '.data', $data);

		// Redirect back to the edit screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
		);

		return true;
	}
}
