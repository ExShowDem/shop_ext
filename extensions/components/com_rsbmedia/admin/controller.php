<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Client\ClientHelper;
/**
 * Rsbmedia Manager Component Controller
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.5
 */
class RsbmediaController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link FilterInput::clean()}.
	 *
	 * @return  JController This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		PluginHelper::importPlugin('content');
		$vName = $this->input->get('view', 'media');

		switch ($vName)
		{
			case 'images':
				$vLayout = $this->input->get('layout', 'default', 'string');
				$mName   = 'manager';

				break;

			case 'imagesList':
				$mName   = 'list';
				$vLayout = $this->input->get('layout', 'default', 'string');

				break;

			case 'mediaList':
				$app     = Factory::getApplication();
				$mName   = 'list';
				$vLayout = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

				break;

			case 'media':
			default:
				$vName   = 'media';
				$vLayout = $this->input->get('layout', 'default', 'string');
				$mName   = 'manager';
				break;
		}

		$document = Factory::getDocument();
		$vType    = $document->getType();

		// Get/Create the view
		$view = $this->getView($vName, $vType);
		$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/' . strtolower($vName) . '/tmpl');

		// Get/Create the model
		if ($model = $this->getModel($mName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($vLayout);

		// Display the view
		$view->display();

		return $this;
	}

	/**
	 * Validate ftp connection.
	 *
	 * @return void
	 */
	public function ftpValidate()
	{
		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');
	}
}
