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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplication;

/**
 * Field Value Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       2.0
 */
class RedshopbControllerField_Value extends RedshopbControllerForm
{
	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		return parent::getRedirectToListAppend() . '&tab=field_values';
	}

	/**
	 * Overridden to normalize the input for WS
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->normalizeImageInput();

		return parent::save($key, $urlVar);
	}

	/**
	 * Method to store a value via AJAX
	 *
	 * @return void
	 */
	public function ajaxStore()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app              = Factory::getApplication();
		$input            = $app->input;
		$data             = $input->post->get('jform', array(), 'array');
		$data['field_id'] = $input->get('field_id', 0, 'INT');

		/** @var RedshopbModelField_value $model */
		$model = $this->getModel();
		$form  = $model->getForm(array(), false);

		$validData = $model->validate($form, $data);

		if (!$validData)
		{
			$this->ajaxBadRequest($app);
		}

		if (!$model->save($validData))
		{
			header('HTTP/1.1 500 Internal Server Error');
			echo Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
			$app->close();
		}

		$primaryKey = $model->getState($model->getName() . '.id');

		$item      = $model->getItem($primaryKey);
		$item->msg = Text::_('COM_REDSHOPB_FIELD_VALUE_SAVE_SUCCESSFUL');

		if (empty($validData['id']))
		{
			$item->isNew = true;
		}

		if ($validData['default'] == 1)
		{
			RedshopbHelperField::cleanDefaultFieldValue($data['field_id'], (int) $primaryKey);
			$item->defaultsText = Text::_('JNO');
		}

		$item->html = RedshopbLayoutHelper::render('field_value.table_row', $item);

		echo json_encode($item);

		$app->close();
	}

	/**
	 * Method to process a bad request via AJAX
	 *
	 * @param   CMSApplication  $app  the application
	 *
	 * @return void
	 */
	private function ajaxBadRequest($app)
	{
		header('HTTP/1.0 400 Bad Request');

		foreach ($app->getMessageQueue() AS $message)
		{
			echo '<p>' . $message['message'] . '</p>';
		}

		$app->close();
	}

	/**
	 * Method to delete a record via AJAX
	 *
	 * @return void
	 */
	public function ajaxDelete()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$model = $this->getModel();
		$data  = $input->get('jform', array(), 'ARRAY');

		$cid = array($data['id']);

		if (!$model->delete($cid))
		{
			header('HTTP/1.1 500 Internal Server Error');
			echo Text::_('COM_REDSHOPB_ERROR_DELETE_FAILED');
			$app->close();
		}

		echo Text::_('COM_REDSHOPB_FIELD_VALUE_DELETE_SUCCESSFUL');
		$app->close();
	}
}
