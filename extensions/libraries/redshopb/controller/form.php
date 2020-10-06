<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
/**
 * Controller form.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Controller
 * @since       1.0
 */
abstract class RedshopbControllerForm extends RControllerForm
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (!RedshopbEntityConfig::getInstance()->getInt('enable_offer', 1)
			&& in_array($this->view_item, array('offer', 'myoffer')))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_OFFER_DISABLED'), 'error');
			Factory::getApplication()->redirect(
				Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getDefault()->id, false)
			);
		}
	}

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
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit');
		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$append .= RedshopbInput::getRequestVariables();

		return $append;
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
		$id      = $this->input->getInt('id');
		$isNew   = (int) $id <= 0;
		$browser = RedshopbBrowser::getInstance(RedshopbBrowser::REDSHOPB_HISTORY);

		// No browser
		if (!isset($browser))
		{
			return RedshopbRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $append, false);
		}

		// We don't go back if there is not new
		if (!$isNew)
		{
			$browser->back();
		}

		// Get the current Uri
		$currentUri = $browser->getCurrentUri();

		if (!empty($currentUri))
		{
			// Append the vars one by one to override the existing ones
			parse_str($append, $vars);
			$uri = Uri::getInstance($currentUri);

			foreach ($vars as $name => $val)
			{
				$uri->setVar($name, $val);
			}

			return RedshopbRoute::_($uri->toString(), false);
		}

		return parent::getRedirectToListRoute($append);
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return string The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append  = parent::getRedirectToListAppend();
		$append .= RedshopbInput::getRequestVariables(false);

		return $append;
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

		$returnUrl = $input->get('return', 'index.php');
		$model     = $input->get('model', null);

		if ($model)
		{
			$returnUrl = $input->get('return', 'index.php');
			$returnUrl = base64_decode($returnUrl);
			$context   = $input->getCmd('context', '');
			$option    = 'auto';

			if (stripos($model, '.') !== false)
			{
				list($option, $model) = explode('.', $model);
			}

			$model = RModel::getAdminInstance(ucfirst($model), array('context' => $context), $option);

			$model->getState();
		}

		$app->redirect(RedshopbRoute::_($returnUrl, false));
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		$model = $this->getModel();

		if (method_exists($model, 'canSave') && !$model->canSave($data))
		{
			return false;
		}

		return parent::allowAdd($data);
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$model = $this->getModel();

		if (method_exists($model, 'canSave') && !$model->canSave($data))
		{
			return false;
		}

		return parent::allowEdit($data, $key);
	}

	/**
	 * Get the Route object for a redirect to item.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  Route  The Route object
	 */
	protected function getRedirectToItemRoute($append = null)
	{
		return RedshopbRoute::_(
			'index.php?option=' . $this->option . '&view=' . $this->view_item
			. $append, false
		);
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   BaseDatabaseModel  $model      The data model object.
	 * @param   array              $validData  The validated data.
	 *
	 * @return  void
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		// If we go from another item, then need delete previous item in breadcrumb history
		if (in_array($this->getTask(), array('save2copy', 'save2new')))
		{
			RedshopbBrowser::getInstance(RedshopbBrowser::REDSHOPB_HISTORY)->back();
		}
	}

	/**
	 * Method to normalize the multipart form image input for WS
	 *
	 * @return  boolean.
	 */
	protected function normalizeImageInput()
	{
		$data               = $this->input->post->get('jform', array(), 'array');
		$data['image_file'] = $this->input->files->get('jform', array(), 'array');
		$this->input->post->set('jform', $data);

		return true;
	}
}
