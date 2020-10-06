<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;
/**
 * HTML View class for the Rsbmedia component
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.0
 */
class RsbmediaViewMedia extends HtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The tmpl file to display
	 *
	 * @return mixed
	 */
	public function display($tpl = null)
	{
		$app	= Factory::getApplication();
		$config = ComponentHelper::getParams('com_rsbmedia');

		if (!$app->isClient('administrator'))
		{
			return $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
		}

		$lang = Factory::getLanguage();

		$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		$document = Factory::getDocument();

		HTMLHelper::_('behavior.framework', true);

		HTMLHelper::_('script', 'com_rsbmedia/mediamanager.js', true, true);

		/*
		HTMLHelper::_('stylesheet', 'media/mediamanager.css', array(), true);
		if ($lang->isRTL()) :
			HTMLHelper::_('stylesheet', 'media/mediamanager_rtl.css', array(), true);
		endif;
		*/

		HTMLHelper::_('behavior.modal');
		$document->addScriptDeclaration("
			window.addEvent('domready', function()
			{
				document.preview = SqueezeBox;
			});"
		);

		// HTMLHelper::_('script', 'system/mootree.js', true, true, false, false);
		HTMLHelper::_('stylesheet', 'system/mootree.css', array(), true);

		if ($lang->isRTL())
		{
			HTMLHelper::_('stylesheet', 'com_rsbmedia/mootree_rtl.css', array(), true);
		}

		if (DIRECTORY_SEPARATOR == '\\')
		{
			$base = str_replace(DIRECTORY_SEPARATOR, "\\\\", COM_RSBMEDIA_BASE);
		}
		else
		{
			$base = COM_RSBMEDIA_BASE;
		}

		$js = "
			var basepath = '" . $base . "';
			var viewstyle = '" . $style . "';
		";
		$document->addScriptDeclaration($js);

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !ClientHelper::hasCredentials('ftp');

		$session           = Factory::getSession();
		$state             = $this->get('state');
		$this->session     = $session;
		$this->config      = &$config;
		$this->state       = &$state;
		$this->require_ftp = $ftp;
		$this->folders_id  = ' id="media-tree"';
		$this->folders     = $this->get('folderTree');

		// Set the toolbar
		$this->addToolbar();

		parent::display($tpl);
		echo HTMLHelper::_('behavior.keepalive');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$bar  = Toolbar::getInstance('toolbar');
		$user = Factory::getUser();

		// The toolbar functions depend on Bootstrap JS
		HTMLHelper::_('bootstrap.framework');

		// Set the titlebar text
		JToolbarHelper::title(Text::_('COM_RSBMEDIA'), 'images mediamanager');

		// Add a upload button
		// Instantiate a new FileLayout instance and render the layout
		$layout = new FileLayout('toolbar.uploadmedia');

		$bar->appendButton('Custom', $layout->render(array()), 'upload');
		JToolbarHelper::divider();

		// Add a create folder button
		// Instantiate a new FileLayout instance and render the layout
		$layout = new FileLayout('toolbar.newfolder');

		$bar->appendButton('Custom', $layout->render(array()), 'upload');
		JToolbarHelper::divider();

		/*
		Add a delete button
		Disabling ACL for now
		Instantiate a new FileLayout instance and render the layout
		*/
		$layout = new FileLayout('toolbar.deletemedia');

		$bar->appendButton('Custom', $layout->render(array()), 'upload');
		JToolbarHelper::divider();

		// Add a preferences button
		JToolbarHelper::preferences('com_rsbmedia');
		JToolbarHelper::divider();

		JToolbarHelper::help('JHELP_CONTENT_RSBMEDIA_MANAGER');
	}

	/**
	 * Method for get folder level
	 *
	 * @param   string  $folder  Folder
	 *
	 * @return  string
	 */
	public function getFolderLevel($folder)
	{
		$this->folders_id = null;
		$txt              = null;

		if (isset($folder['children']) && count($folder['children']))
		{
			$tmp           = $this->folders;
			$this->folders = $folder;
			$txt           = $this->loadTemplate('folders');
			$this->folders = $tmp;
		}

		return $txt;
	}
}
