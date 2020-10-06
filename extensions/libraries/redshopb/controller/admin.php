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
/**
 * Controller admin.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Controller
 * @since       1.0
 */
abstract class RedshopbControllerAdmin extends RControllerAdmin
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
			&& in_array($this->view_list, array('offers', 'myoffers')))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_OFFER_DISABLED'), 'error');
			Factory::getApplication()->redirect(
				Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getDefault()->id, false)
			);
		}
	}

	/**
	 * Get the Route object for a redirect to list.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  string
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return RedshopbRoute::_($returnUrl . $append, false);
		}

		return RedshopbRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $append, false);
	}

	/**
	 * Get return Object - in use for ajax calls to controller using the redshopB Ajax framework
	 *
	 * @param   string  $msg      Default return message
	 * @param   string  $msgType  Default return message type
	 *
	 * @return stdClass
	 */
	protected function getReturnObject($msg = '', $msgType = 'alert-success')
	{
		$returnObject              = new stdClass;
		$returnObject->message     = $msg;
		$returnObject->messageType = $msgType;
		$returnObject->html        = '';

		return $returnObject;
	}

	/**
	 * Generates a CSV file ready for downloading in the Joomla tmp folder
	 *
	 * @throws  Exception
	 *
	 * @return   void
	 */
	public function ajaxGenerateCsvFile()
	{
		$app  = Factory::getApplication();
		$type = $app->input->get('view', '');

		/** @var RedshopbViewCsv $view */
		$view     = $this->getView($type, 'csv');
		$viewData = $view->getViewData();

		$csvLines = $this->getCsvData($app, $type, $view, $viewData);

		// Get the file name
		$filename = $viewData->get('filename');

		setlocale(LC_ALL, $view->localeEncoding);

		$file = fopen(JPATH_ROOT . "/tmp/{$filename}", 'w');

		foreach ($csvLines as $line)
		{
			fputcsv($file, $line, $view->delimiter, $view->enclosure);
		}

		fclose($file);

		ob_clean();

		echo base64_encode(serialize($filename));

		$app->close();
	}

	/**
	 * Generates data for creating CSV files
	 *
	 * @param   CMSApplication    $app       The Joomla application
	 * @param   string            $type      Name of the currect view, used to get the associating model
	 * @param   RedshopbViewCsv   $view      CSV View
	 * @param   array             $viewData  Protected view data
	 *
	 * @return  array
	 */
	protected function getCsvData($app, $type, $view, $viewData)
	{
		$csvLines = array();

		// Get the columns
		$columns = $viewData->get('columns');

		/** @var RModelList $model */
		$model = $this->getModel($type);

		$data = json_decode($app->input->post->getString('result', '[]'));

		// Prepare the items
		if (method_exists($model, 'getItemsCsv') && !empty($data))
		{
			$items = $model->getItemsCsv(substr($type, 0, 1), $data);
		}
		else
		{
			// Fallback to old method of getting data

			// For additional filtering and formating if needed
			$model->setState('streamOutput', 'csv');

			$items = $model->getItems();
		}

		$csvLines[0] = $columns;
		$inc         = 1;

		// Check if the preprocessing method exists
		$preprocessExists = method_exists($view, 'preprocess');

		foreach ($items as $item)
		{
			$csvLines[$inc] = array();

			foreach ($columns as $name => $title)
			{
				if (property_exists($item, $name))
				{
					$csvLines[$inc][$name] = $preprocessExists ? $view->preprocess($name, $item->$name) : $item->$name;
				}
			}

			$inc++;
		}

		return $csvLines;
	}
}
