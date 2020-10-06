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
use Joomla\CMS\Router\Route;

/**
 * Collection Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCollection extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_COLLECTION';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('createFromExisting', 'new');
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
		$app    = Factory::getApplication();
		$result = parent::save($key, $urlVar);

		$task = $this->getTask();

		if ($task == 'save2new')
		{
			RedshopbBrowser::getInstance(RedshopbBrowser::REDSHOPB_HISTORY)->clearHistory();
			$app->input->set('id', 0);
			$this->create();
		}

		return $result;
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 */
	public function create()
	{
		$app     = Factory::getApplication();
		$context = $this->option . '.edit.' . $this->context;

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context . '.data', null);
		$this->input->set('layout', 'create');

		// Redirect back to the create screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend() . '&layout=create&id=' . $app->input->getInt('id'))
		);

		return true;
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 */
	public function createFromExisting()
	{
		$app     = Factory::getApplication();
		$context = $this->option . '.edit.' . $this->context;

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context . '.data', null);
		$this->input->set('layout', 'create');
		$append      = '';
		$collections = $this->input->post->get('cid', array(), 'array');
		$collections = ArrayHelper::toInteger($collections);

		if (count($collections) == 0)
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		if (count($collections) > 0)
		{
			$append .= '&collections=' . (implode(',', $collections));
		}

		// Redirect back to the create screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend() . $append . '&layout=create')
		);

		return true;
	}

	/**
	 * Method to add a new product in Collection.
	 *
	 * @return  boolean  Redirect to correct view
	 */
	public function addNewProduct()
	{
		$app     = Factory::getApplication();
		$context = $this->option . '.edit.' . $this->context;

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context . '.data', null);
		$this->input->set('layout', 'create_products');
		$collectionId = $this->input->post->get('id', 0, 'int');

		if ($collectionId == 0)
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('COM_REDSHOPB_COLLECTION_ERROR_PLEASE_SELECT_PRODUCT'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collections', false
				)
			);

			return false;
		}

		// Redirect to the step two
		$this->setRedirect(
			RedshopbRoute::_(
				'index.php?option=com_redshopb&view=collection&layout=create_products'
				. $this->getRedirectToItemAppend($collectionId, 'id'), false
			)
		);

		return true;
	}

	/**
	 * Move to the next creation step.
	 *
	 * @return boolean
	 */
	public function createNext()
	{
		$step = $this->getCreateUrlStep();

		// Step one
		if (1 === $step)
		{
			return $this->createSave();
		}

		// Step two
		if (2 === $step)
		{
			return $this->createSaveProducts();
		}

		// Step three
		if (3 === $step)
		{
			return $this->createSaveProductItems();
		}

		return false;
	}

	/**
	 * Cancel the current creation step.
	 *
	 * @return void
	 */
	public function createCancel()
	{
		$step = $this->getCreateUrlStep();
		$id   = $this->input->getInt('id');

		if (1 === $step)
		{
			// Redirect to the list
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return;
		}

		if (2 === $step)
		{
			// Redirect to step one
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create&id=' . $id
					. $this->getRedirectToItemAppend(), false
				)
			);

			return;
		}

		if (3 === $step)
		{
			// Redirect to step two
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create_products&id=' . $id
					. $this->getRedirectToItemAppend(), false
				)
			);

			return;
		}
	}

	/**
	 * Save the first creation step.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function createSave()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$lang    = Factory::getLanguage();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$data    = $this->input->post->get('jform', array(), 'array');
		$checkin = property_exists($table, 'checked_out');
		$context = $this->option . '.edit.' . $this->context;

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
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

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
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
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
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
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

		// Set the record data in the session.
		$recordId = $model->getState($this->context . '.id');
		$this->holdEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		if ($this->input->post->get('collections', '', 'string') != '')
		{
			// Save product and product items from existing collections
			if (!$table->createProductsFromCollections($recordId))
			{
				// Failed to add products from existing collections, so go back to the record and display a notice.
				$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				// Redirect back to the edit screen.
				$this->setRedirect(
					RedshopbRoute::_(
						'index.php?option=com_redshopb&view=collection&layout=create'
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);
			}
		}

		$model->checkout($recordId);

		// Redirect to the step two
		$this->setRedirect(
			RedshopbRoute::_(
				'index.php?option=com_redshopb&view=collection&layout=create_products'
				. $this->getRedirectToItemAppend($recordId, $urlVar), false
			)
		);

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}

	/**
	 * Save the second creation step.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function createSaveProducts()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$cids = $this->input->get('cid', array(), 'array');

		$app        = Factory::getApplication();
		$lang       = Factory::getLanguage();
		$model      = $this->getModel();
		$table      = $model->getTable();
		$productIds = array();

		foreach ($cids as $cid)
		{
			$productIds[] = array('id' => $cid);
		}

		$data    = array('product_ids' => $productIds);
		$checkin = property_exists($table, 'checked_out');
		$context = $this->option . '.edit.' . $this->context;

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

		if (empty($cids))
		{
			// No products selected
			$this->setError(Text::_('COM_REDSHOPB_COLLECTION_ERROR_PRODUCTS_NOT_SELECTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create_products'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		// Populate the row id from the session.
		$data[$key] = $recordId;

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

		$model->getTable()->setOption('departments.store', false);

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create_products'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($data[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create_products'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
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

		// Set the record data in the session.
		$recordId = $model->getState($this->context . '.id');
		$this->holdEditId($context, $recordId);
		$app->setUserState($context . '.data', null);
		$model->checkout($recordId);

		// Redirect to the step two
		$this->setRedirect(
			RedshopbRoute::_(
				'index.php?option=com_redshopb&view=collection&layout=create_product_items'
				. $this->getRedirectToItemAppend($recordId, $urlVar), false
			)
		);

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model);

		return true;
	}

	/**
	 * Save the thrid creation step.
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function createSaveProductItems()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', array(), 'array');

		$app             = Factory::getApplication();
		$lang            = Factory::getLanguage();
		$model           = $this->getModel();
		$table           = $model->getTable();
		$productItemsIds = array();

		if (count($cid) > 0)
		{
			foreach ($cid as $id)
			{
				$productItemsIds[] = array('id' => $id, 'state' => 1);
			}
		}

		$data    = array('product_item_ids' => $productItemsIds);
		$checkin = property_exists($table, 'checked_out');
		$context = $this->option . '.edit.' . $this->context;

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

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create_product_items'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($data[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				RedshopbRoute::_(
					'index.php?option=com_redshopb&view=collection&layout=create_product_items'
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
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

		// Set the record data in the session.
		$recordId = $model->getState($this->context . '.id');
		$this->holdEditId($context, $recordId);
		$app->setUserState($context . '.data', null);
		$model->checkout($recordId);

		// Finished
		$this->setRedirect(
			RedshopbRoute::_(
				'index.php?option=com_redshopb&task=collection.edit&id=' . $recordId, false
			)
		);

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model);

		return true;
	}

	/**
	 * Get the create url step from the url.
	 *
	 * @return  integer  The step number
	 */
	private function getCreateUrlStep()
	{
		$layout = $this->input->get('layout');

		if ('create' === $layout)
		{
			return 1;
		}

		if ('create_products' === $layout)
		{
			return 2;
		}

		if ('create_product_items' === $layout)
		{
			return 3;
		}

		return 0;
	}

	/**
	 * Get the Route object for a redirect to item.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToItemRoute($append = null)
	{
		$redirectUrl = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $append;

		$productId = $this->input->get('product_id', 0, 'int');

		if ($productId)
		{
			$redirectUrl .= '&product_id=' . (int) $productId;
		}

		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			$redirectUrl .= '&return=' . $return;
		}

		return RedshopbRoute::_($redirectUrl, false);
	}

	/**
	 * Get the Route object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			return RedshopbRoute::_(base64_decode($return), false);
		}

		return parent::getRedirectToListRoute($append);
	}

	/**
	 * We don't want the layout to be appended when we are in a create_* layout.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit');
		$append = '';

		if ('create' !== $layout && 'create_products' !== $layout && 'create_product_items' !== $layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$companyId      = RedshopbInput::getCompanyIdForm();
		$departmentId   = RedshopbInput::getDepartmentIdForm();
		$collectionId   = RedshopbInput::getCollectionIdForm();
		$fromCompany    = RedshopbInput::isFromCompany();
		$fromDepartment = RedshopbInput::isFromDepartment();
		$fromCollection = RedshopbInput::isFromCollection();

		if ($fromCompany && $companyId)
		{
			$append .= '&jform[company_id]=' . $companyId;
		}

		if ($fromDepartment && $departmentId)
		{
			$append .= '&jform[department_id]=' . $departmentId;
		}

		if ($fromCollection && $collectionId)
		{
			$append .= '&jform[collection_id]=' . $collectionId;
		}

		if ($fromCompany)
		{
			$append .= '&from_company=1';
		}

		if ($fromDepartment)
		{
			$append .= '&from_department=1';
		}

		if ($fromCollection)
		{
			$append .= '&from_collection=1';
		}

		$return = $this->input->getBase64('return', false);

		if ($return)
		{
			$append .= '&return=' . $return;
		}

		return $append;
	}

	/**
	 * Ajax call to get products content for the create steps.
	 *
	 * @return  void
	 */
	public function ajaxcreateproducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId = $input->getInt('id', 0);

		if ($collectionId > 0)
		{
			/** @var RedshopbModelProducts $model */
			$model = RModelAdmin::getInstance('Products', 'RedshopbModel');

			// Getting model state variables to ensure variables are created
			$model->getState();

			// Setting state variables
			$model->setState('filter.notInProducts',  RedshopbHelperCollection::getCollectionProducts($collectionId));
			$model->setState('list.product_state', '1');
			$model->setState('list.ordering', $input->get('data-order', ''));
			$model->setState('list.direction', $input->get('data-direction', ''));
			$model->setState('list.allow_parent_companies_products', true);
			$model->setState('list.allow_mainwarehouse_products', true);
			$model->setState('list.disallow_freight_fee_products', true);
			$model->setState('filter.include_categories', true);
			$model->setState('filter.include_tags', true);
			$model->setState('include_objects', true);

			$collection = RedshopbEntityCollection::getInstance($collectionId);
			$companyIds = RedshopbEntityCompany::getInstance($collection->get('company_id'))->getTree(true, true);
			$model->setState('filter.company_ids', $companyIds);

			$formName   = 'productsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			$filterForm = $model->getForm();
			$filterForm->setFieldAttribute('product_state', 'default', '1', 'filter');
			$filterForm->setFieldAttribute('product_state', 'disabled', 'true', 'filter');

			echo RedshopbLayoutHelper::render('collection.create.products', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $filterForm,
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => false,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=collection&model=products'),
					'return' => base64_encode(
						'index.php?option=com_redshopb&view=collection&layout=create_products&tab=products&id='
						. $collectionId
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get products already added in collection
	 *
	 * @return  void
	 */
	public function ajaxcreatecollectionproducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId       = $input->getInt('id', 0);
		$layout             = $input->getCmd('layout', 'create_products');
		$collectionProducts = RedshopbHelperCollection::getCollectionProducts($collectionId);

		// Transitional products in collection (not yet saved)
		$cids = $input->getCmd('cids', '');

		if ($cids != '')
		{
			$collectionProducts = array_merge($collectionProducts, explode('_', $cids));
		}

		// We do not show any product if we do not have them in xref table
		if (count($collectionProducts) == 0)
		{
			$collectionProducts = Array(0);
		}

		if ($collectionId > 0)
		{
			$layoutFolder = 'create';

			if ($layout == 'edit')
			{
				$layoutFolder = 'edit';
			}

			/** @var RedshopbModelProducts $model */
			$model = RModelAdmin::getInstance('Products', 'RedshopbModel', array('context' => 'tab_products'));

			// Getting model state variables to ensure variables are created
			$model->getState();

			// Setting state variables
			$model->setState('list.force_collection', true);
			$model->setState('filter.product_collection', $collectionId);
			$model->setState('filter.product_id', $collectionProducts);
			$model->setState('list.ordering', $input->get('data-order', 'cpx.ordering'));
			$model->setState('list.direction', $input->get('data-direction', 'asc'));
			$model->setState('list.allow_parent_companies_products', true);
			$model->setState('list.allow_mainwarehouse_products', true);
			$model->setState('filter.include_categories', true);
			$model->setState('filter.include_tags', true);
			$model->setState('include_objects', true);

			$formName             = 'collectionProductsForm';
			$pagination           = $model->getPagination();
			$pagination->formName = $formName;
			$filterForm           = $model->getForm();

			echo RedshopbLayoutHelper::render('collection.' . $layoutFolder . '.collectionproducts', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $filterForm,
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => false,
					'showPagination' => true,
					'context' => $model->get('context'),
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=collection&model=products'),
					'return' => base64_encode(
						'index.php?option=com_redshopb&view=collection&tab=createcollectionproducts&from_collection=1&layout=' . $layout . '&id='
						. $collectionId
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get products already added in collection
	 *
	 * @return  void
	 */
	public function ajaxcreatecollectionproductItems()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId = $input->getInt('id', 0);
		$productId    = $input->getInt('product_id', 0);
		$layout       = $input->getCmd('layout', 'create_product_items');

		if ($collectionId > 0)
		{
			/** @var RedshopbModelProducts $model */
			$model = RModelAdmin::getInstance('Product_Attributes', 'RedshopbModel');

			$formName   = 'collectionProductItemsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('collection.create.collectionproductitems', array(
					'state' => $model->getState(),
					'staticTypes' => RedshopbHelperCollection::getStaticTypes($productId),
					'dynamicTypes' => RedshopbHelperCollection::getDynamicTypes($productId),
					'issetDynamicVariants' => RedshopbHelperCollection::getIssetDynamicVariants($productId),
					'issetItems' => RedshopbHelperCollection::getIssetItems($productId, $collectionId),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => false,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=collection&model=products'),
					'return' => base64_encode(
						'index.php?option=com_redshopb&view=collection&tab=products&from_collection=1&layout=' . $layout . '&id='
						. $collectionId
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get combinations tab content.
	 *
	 * @return  void
	 */
	public function ajaxcombinations()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId = $input->getInt('id', 0);
		$productId    = $input->getInt('product_id', 0);

		if ($productId == 0)
		{
			$collectionProducts = RedshopbHelperCollection::getCollectionProducts($collectionId);
		}
		else
		{
			$collectionProducts = array($productId);
		}

		if (!empty($collectionProducts))
		{
			// @var RedshopbModelProduct $model
			$model = RModelAdmin::getInstance('Product', 'RedshopbModel');
			$model->setState('filter.collectionId', $collectionId);
			$formName = 'combinationsForm';

			foreach ($collectionProducts as $productId)
			{
				echo RedshopbLayoutHelper::render(
					'collection.edit.productcombinations',
					array(
						'formName' => $formName,
						'productId' => $productId,
						'model' => $model,
						'attributes' => $model->getAttributes($productId),
						'items' => $model->getProductItems($productId),
						'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId),
						'return' => base64_encode('index.php?option=com_redshopb&view=product&layout=edit&tab=combinations&id=' . $productId)
					)
				);
			}
		}

		$app->close();
	}

	/**
	 * Ajax call to get departments based on selected Company.
	 *
	 * @return  void
	 */
	public function ajaxGetFieldDepartments()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$model = $this->getModel('Collection', 'RedshopbModel', array('ignore_request' => false));
		$input = $app->input;
		$input->set('view', 'collection');

		$companyId           = $input->getInt('company_id', 0);
		$jform               = $input->get('jform', array(), 'array');
		$jform['company_id'] = $companyId;
		$input->set('jform', $jform);

		$departments = $model->getDepartmentsFormField();
		echo $departments;

		$app->close();
	}

	/**
	 * Ajax call to remove product from collection
	 *
	 * @return  void
	 */
	public function ajaxRemoveProduct()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app          = Factory::getApplication();
		$productId    = $app->input->getInt('product_id', 0);
		$collectionId = $app->input->getInt('collection_id', 0);
		$result       = $this->getModel()->removeProduct($productId, $collectionId);
		echo json_encode($result);

		$app->close();
	}

	/**
	 * Ajax call to get prices tab content.
	 *
	 * @return  void
	 */
	public function ajaxprices()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId       = $input->getInt('id', 0);
		$collectionProducts = RedshopbHelperCollection::getCollectionProducts($collectionId);

		// We do not show any product if we do not have them in xref table
		if (count($collectionProducts) == 0)
		{
			$collectionProducts = Array(0);
		}

		/** @var RedshopbModelProducts $model */
		$model = RModelAdmin::getInstance('Products', 'RedshopbModel', array('context' => 'tab_prices'));

		// Getting model state variables to ensure variables are created
		$model->getState();

		// Setting state variables
		$model->setState('filter.product_id', $collectionProducts);
		$model->setState('list.ordering', $input->get('data-order', ''));
		$model->setState('list.direction', $input->get('data-direction', ''));
		$model->setState('list.allow_parent_companies_products', true);
		$model->setState('list.allow_mainwarehouse_products', true);
		$model->setState('filter.include_categories', true);
		$model->setState('filter.include_tags', true);
		$model->setState('include_objects', true);
		$model->setState('include.collection_price', $collectionId);

		$formName   = 'collectionProductsPricesForm';
		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		echo RedshopbLayoutHelper::render('collection.edit.priceproducts', array(
				'state' => $model->getState(),
				'items' => $model->getItems(),
				'pagination' => $pagination,
				'filter_form' => $model->getForm(),
				'activeFilters' => $model->getActiveFilters(),
				'formName' => $formName,
				'showToolbar' => true,
				'context' => $model->get('context'),
				'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=collection&layout=edit&model=products&tab=prices'),
				'return' => base64_encode(
					'index.php?option=com_redshopb&view=collection&tab=products&from_collection=1&layout=edit&tab=prices&id='
					. $collectionId
				)
			)
		);

		$app->close();
	}

	/**
	 * Method to save a record.
	 *
	 * @return  void
	 */
	public function saveAllPrices()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$data         = $this->input->post->get('jform', array(), 'array');
		$collectionId = $this->input->post->get('id', 0, 'int');
		$model        = $this->getModel('Collection');
		$table        = $model->getTable();
		$table->setOption('product_items.store', true)
			->setOption('products.store', true)
			->setOption('product.update_only_price', true)
			->setOption('product_items.update_only_state', false)
			->setOption('product_items.update_only_price', true)
			->setOption('products.load', true)
			->setOption('product_items.load', true);
		$error          = false;
		$countStoring   = 0;
		$productItemIds = array();
		$productIds     = array();

		if (isset($data['product_price']) && count($data['product_price']) > 0)
		{
			foreach ($data['product_price'] as $id => $price)
			{
				$countStoring++;
				$productIds[$id] = array(
					'id' => $id,
					'price' => $price
				);
			}
		}

		if (isset($data['price']) && count($data['price']) > 0)
		{
			foreach ($data['price'] as $id => $price)
			{
				$countStoring++;
				$productItemIds[$id] = array(
					'id' => $id,
					'price' => $price
				);
			}
		}

		if (isset($data['price_color']) && count($data['price_color']) > 0)
		{
			$colorsIds = array();

			foreach ($data['price_color'] as $id => $priceColor)
			{
				if ($priceColor != '')
				{
					$colorsIds[] = $id;
				}
			}

			if (count($colorsIds) > 0)
			{
				$colorItems = $model->getColorItems($colorsIds);

				if ($colorItems)
				{
					foreach ($colorItems as $colorItem)
					{
						if (isset($data['price_color'][$colorItem->product_attribute_value_id]))
						{
							$productItemIds[$colorItem->product_item_id]['price']
								= (float) $data['price_color'][$colorItem->product_attribute_value_id];
							$productItemIds[$colorItem->product_item_id]['id']    = $colorItem->product_item_id;
						}
					}
				}
			}
		}

		if (count($productItemIds) > 0 || count($productIds) > 0)
		{
			if (!$table->load($collectionId))
			{
				$this->setMessage($table->getError(), 'error');
				$error = true;
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('wpx.product_id, wpx.price')
				->from($db->qn('#__redshopb_collection_product_xref', 'wpx'))
				->where('wpx.collection_id = ' . $db->q($collectionId));

			$productPrices = $db->setQuery($query)
				->loadAssocList('product_id', 'price');

			if (is_array($table->get('product_item_ids')) && count($table->get('product_item_ids')) > 0)
			{
				foreach ($table->get('product_item_ids') as $key => $productItemId)
				{
					if (!isset($productItemIds[$key]))
					{
						$productItemIds[$key] = $productItemId;
					}
				}
			}

			if (!empty($productPrices))
			{
				foreach ($productPrices as $productId => $productPrice)
				{
					if (!isset($productIds[$productId]))
					{
						$productIds[$productId] = array(
							'id' => $productId,
							'price' => $productPrice
						);
					}
				}
			}

			$row = array(
				'id' => $collectionId,
				'product_item_ids' => $productItemIds,
				'product_ids' => $productIds
			);

			if (!$table->save($row))
			{
				$this->setMessage($table->getError(), 'error');
				$error = true;
			}
		}

		if (!$error)
		{
			$this->setMessage(Text::plural($this->text_prefix . '_N_PRICES_UPDATED', $countStoring));
		}

		$append = $this->getRedirectToItemAppend($collectionId, 'id');

		$append .= '&tab=prices';

		// Set redirect
		$this->setRedirect($this->getRedirectToItemRoute($append));
	}

	/**
	 * Ajax call to get product item prices.
	 *
	 * @return  void
	 */
	public function ajaxcollectionprices()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId = $input->getInt('id', 0);
		$productId    = $input->getInt('product_id', 0);

		$collectionProducts = array($productId);

		if (!empty($collectionProducts))
		{
			/** @var RedshopbModelProduct $model */
			$model = RModelAdmin::getInstance('Product', 'RedshopbModel', array('ignore_request' => true));
			$model->setState('filter.collectionId', $collectionId);
			$model->setState('filter.collectionPrices', $collectionId);
			$model->setState('filter.attribute_state', 1);
			$model->setState('filter.productItem_state', '');
			$formName = 'productItemPricesForm' . $productId;

			foreach ($collectionProducts as $productId)
			{
				echo RedshopbLayoutHelper::render(
					'collection.edit.prices',
					array(
						'formName' => $formName,
						'productId' => $productId,
						'collectionId' => $collectionId,
						'model' => $model,
						'attributes' => $model->getAttributes($productId),
						'items' => $model->getProductItems($productId),
						'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=collection&layout=edit&id=' . $productId),
						'return' => base64_encode('index.php?option=com_redshopb&view=collection&layout=edit&tab=prices&id=' . $productId)
					)
				);
			}
		}

		$app->close();
	}

	/**
	 * Saves price for collection product item
	 *
	 * @return  void
	 */
	public function ajaxsaveprice()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$collectionId  = $input->getInt('collectionId', 0);
		$productItemId = $input->getInt('productItemId', 0);
		$price         = $input->getString('price', '');

		if (!empty($collectionId) && !empty($productItemId))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('product_item_id')
				->from('#__redshopb_collection_product_item_xref AS wpi')
				->where('wpi.collection_id = ' . $db->q($collectionId))
				->where('wpi.product_item_id = ' . $db->q($productItemId));

			$db->setQuery($query);
			$item  = $db->loadResult();
			$query = $db->getQuery(true);

			if ($item)
			{
				$query->update('#__redshopb_collection_product_item_xref')
					->where('collection_id = ' . $db->q($collectionId))
					->where('product_item_id = ' . $db->q($productItemId));
			}
			else
			{
				$query->insert('#__redshopb_collection_product_item_xref');
			}

			$query->set('collection_id = ' . $db->q($collectionId)
				. ', product_item_id = ' . $db->q($productItemId)
				. ', price = ' . $db->q($price)
				. ', state = ' . $db->q(1)
			);
			$db->setQuery($query);

			$result = $db->execute();

			if ($result)
			{
				echo '1';
			}
			else
			{
				echo '0';
			}
		}
		else
		{
			echo '0';
		}

		$app->close();
	}

	/**
	 * Method called to save a model state
	 *
	 * @return  void
	 */
	public function saveModelState()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$layout = $input->get('layout', '');

		if ($layout == 'create_products')
		{
			// Overriden to save transitional new products (of the collection) into the GET variables of the redirection
			$cid = $this->input->post->get('cid', array(), 'array');

			if ($cid && !empty($cid))
			{
				$cids = implode('_', $cid);

				$returnUrl = $input->get('return', 'index.php');
				$returnUrl = base64_decode($returnUrl) . '&cids=' . $cids;
				$input->set('return', base64_encode($returnUrl));
			}
		}

		parent::saveModelState();
	}
}
