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
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock;

/**
 * Sync Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerSync extends RControllerAdmin
{
	/**
	 * Synchronise the customers
	 *
	 * @return  void
	 *
	 * @since  1.13.0
	 */
	public function selectedItem()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('sync');

		$cronId    = $app->input->getInt('id', 0);
		$fullSync  = $app->input->getInt('fullSync', 0);
		$startSync = $app->input->getBool('startSync', false);
		$return    = ['success' => false];
		$factory   = new Lock\Factory(new FlockStore(Factory::getConfig()->get('tmp_path')));
		$lock      = $factory->createLock('rb-sync');

		if ($lock->acquire())
		{
			try
			{
				ob_start();
				$return     = $model->syncSelectedItem($cronId, $fullSync, $startSync);
				$syncOutput = ob_get_contents();
				ob_end_clean();

				if (empty($return['success']) || $return['success'] === false)
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_SYNC_FAILED'), 'error');
				}

				if (!empty($syncOutput))
				{
					$app->enqueueMessage($syncOutput, 'message');
				}
			}
			catch (Throwable $exception)
			{
				$app->enqueueMessage($exception->getMessage(), 'message');
			}

			finally
			{
				$lock->release();
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_CLI_ANOTHER_PROCESS'), 'error');
		}

		$messages           = $app->getMessageQueue();
		$return['messages'] = array();

		if (is_array($messages))
		{
			foreach ($messages as $msg)
			{
				switch ($msg['type'])
				{
					case 'message':
						$typeMessage = 'success';
						Log::add($msg['message'], Log::INFO, 'webservice');
						break;
					case 'notice':
						$typeMessage = 'info';
						Log::add($msg['message'], Log::NOTICE, 'webservice');
						break;
					case 'error':
						$typeMessage = 'important';
						Log::add($msg['message'], Log::ERROR, 'webservice');
						break;
					case 'warning':
						$typeMessage = 'warning';
						Log::add($msg['message'], Log::WARNING, 'webservice');
						break;
					default:
						$typeMessage = $msg['type'];
						Log::add($msg['message'], Log::DEBUG, 'webservice');
				}

				$return['messages'][] = array('message' => $msg['message'], 'type_message' => $typeMessage);
			}
		}

		echo json_encode($return);

		$app->close();
	}

	/**
	 * Clear Hashed keys
	 *
	 * @return  void
	 */
	public function clearHashedKeys()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		$app = Factory::getApplication();

		// Get items to publish from the request.
		$cid = $app->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('sync');

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				if ($model->clearHashedKeys($cid))
				{
					$this->setMessage(Text::plural('COM_REDSHOPB_SYNC_HASH_KEYS_CLEARED', count($cid)));
				}
				else
				{
					$this->setMessage($model->getError(), 'error');
				}
			}
			catch (Exception $e)
			{
				$this->setMessage(Text::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}
		}

		// Set redirect
		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=sync', false));
	}

	/**
	 * Clear Hashed keys
	 *
	 * @return  void
	 */
	public function clearAllHashedKeys()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Get the model.
		$model = $this->getModel('sync');

		// Publish the items.
		try
		{
			if ($model->clearAllHashedKeys())
			{
				$this->setMessage(Text::_('COM_REDSHOPB_SYNC_ALL_HASH_KEYS_CLEARED'));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		catch (Exception $e)
		{
			$this->setMessage(Text::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
		}

		// Set redirect
		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=sync', false));
	}

	/**
	 * Switch to edit sync list
	 *
	 * @return void
	 */
	public function editSync()
	{
		$app = Factory::getApplication();
		$app->setUserState('list.change_sync', 1);
		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=sync', false));
	}

	/**
	 * Switch to synchronize webservices
	 *
	 * @return void
	 */
	public function syncWebservices()
	{
		$app = Factory::getApplication();
		$app->setUserState('list.change_sync', 0);
		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=sync', false));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		if (empty($name))
		{
			$name = 'syncedit';
		}

		return parent::getModel($name, $prefix, $config);
	}
}
