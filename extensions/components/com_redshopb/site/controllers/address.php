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
 * Address Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerAddress extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_ADDRESS';

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = '';
		$create = $this->input->getCmd('create');

		if ($create)
		{
			$append .= '&create=' . $create;
		}

		return parent::getRedirectToItemAppend($recordId, $urlVar) . $append;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$lang    = Factory::getLanguage();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$data    = $this->input->post->get('jform', array(), 'array');
		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";
		$task    = $this->getTask();

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

		if (!$this->checkEditId($context, $recordId))
		{
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if ($checkin && $model->checkin($data[$key]) === false)
			{
				// Check-in failed. Go back to the item and display a notice.
				$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				// Redirect back to the edit screen.
				$this->setRedirect(
					$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
				);

				return false;
			}

			// Reset the ID, the multilingual associations and then treat the request as for Apply.
			$data[$key]           = 0;
			$data['associations'] = array();
			$task                 = 'apply';
		}

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

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

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
			);

			return false;
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		$create   = $this->input->get('create');
		$rsUserId = RedshopbHelperUser::getUserRSid();

		if ($create && $rsUserId && RedshopbInput::isFromUser())
		{
			switch ($create)
			{
				case 'billing':

					$userTable = $model->getTable('User', 'RedshopbTable');
					$userTable->setOption('noPasswordUpdate', true);

					if ($userTable->load($rsUserId))
					{
						$userAddress = array(
							'address_name' => $validData['name'],
							'address_name2' => $validData['name2'],
							'country_id' => $validData['country_id'],
							'address' => $validData['address'],
							'address2' => $validData['address2'],
							'zip' => $validData['zip'],
							'city' => $validData['city'],
							'email' => $validData['email'],
							'address_phone' => $validData['phone']
						);

						if (!$userTable->save($userAddress))
						{
							// Save the data in the session.
							$app->setUserState($context . '.data', $validData);

							// Redirect back to the edit screen.
							$this->setError($userTable->getError());
							$this->setMessage($this->getError(), 'error');

							// Redirect back to the edit screen.
							$this->setRedirect(
								$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
							);

							return false;
						}

						$model->setState($this->context . '.id', $userTable->get('address_id'));
					}
					else
					{
						return false;
					}

					break;
				case 'shipping':
				default:
					$validData['type']          = 3;
					$validData['customer_type'] = 'employee';
					$validData['customer_id']   = $rsUserId;
					break;
			}
		}

		if (!$create || ($create == 'shipping'  && $rsUserId && RedshopbInput::isFromUser()))
		{
			// Attempt to save the data.
			if (!$model->save($validData))
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				// Redirect back to the edit screen.
				$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				// Redirect back to the edit screen.
				$this->setRedirect(
					$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
				);

				return false;
			}
		}

		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($validData[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
			);

			return false;
		}

		$this->setMessage(
			Text::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$model->checkout($recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(
					$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
				);
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(
					$this->getRedirectToItemRoute($this->getRedirectToItemAppend(null, $urlVar))
				);
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Set redirect
				$this->setRedirect(
					$this->getRedirectToListRoute($this->getRedirectToListAppend())
				);
				break;
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append         = parent::getRedirectToListAppend();
		$fromCompany    = RedshopbInput::isFromCompany();
		$fromDepartment = RedshopbInput::isFromDepartment();

		// Append the tab name for the company or department view
		if ($fromCompany || $fromDepartment)
		{
			$append .= '&tab=addresses';
		}

		return $append;
	}

	/**
	 * Ajax function for getting address customer.
	 *
	 * @return void
	 */
	public function ajaxGetAddressCustomer()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app       = Factory::getApplication();
		$addressId = $app->input->getInt('addressId', 0);

		$address  = RedshopbEntityAddress::getInstance($addressId);
		$customer = $address->getCustomer();

		echo json_encode(
			array(
				'id' => $customer->getId(),
				'type' => $customer->getType()
			)
		);
		$app->close();
	}
}
