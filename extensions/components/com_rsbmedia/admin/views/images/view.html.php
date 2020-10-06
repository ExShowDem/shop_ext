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
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Client\ClientHelper;
/**
 * HTML View class for the Rsbmedia component
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.0
 */
class RsbmediaViewImages extends HTMLView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @see     HtmlView::loadTemplate()
	 * @since   12.2
	 */
	public function display($tpl = null)
	{
		$config = ComponentHelper::getParams('com_rsbmedia');
		$lang	= Factory::getLanguage();

		HTMLHelper::_('behavior.framework', true);
		HTMLHelper::_('script', 'com_rsbmedia/popup-imagemanager.js', true, true);
		HTMLHelper::_('stylesheet', 'com_rsbmedia/popup-imagemanager.css', array(), true);

		if ($lang->isRTL())
		{
			HTMLHelper::_('stylesheet', 'com_rsbmedia/popup-imagemanager_rtl.css', array(), true);
		}

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !ClientHelper::hasCredentials('ftp');

		$this->session     = Factory::getSession();
		$this->config      = $config;
		$this->state       = $this->get('state');
		$this->basePath    = $this->state->get('basepath');
		$this->folderList  = $this->get('folderList');
		$this->require_ftp = $ftp;

		parent::display($tpl);
	}
}
