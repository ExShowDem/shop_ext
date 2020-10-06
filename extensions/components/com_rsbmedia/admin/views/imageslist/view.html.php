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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;

/**
 * HTML View class for the Rsbmedia component
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.0
 */
class RsbmediaViewImagesList extends HtmlView
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
		// Do not allow cache
		Factory::getApplication()->allowCache(false);

		$lang = Factory::getLanguage();

		HTMLHelper::_('stylesheet', 'com_rsbmedia/popup-imagelist.css', array(), true);

		if ($lang->isRTL())
		{
			HTMLHelper::_('stylesheet', 'com_rsbmedia/popup-imagelist_rtl.css', array(), true);
		}

		$document = Factory::getDocument();
		$document->addScriptDeclaration("var ImageManager = window.parent.ImageManager;");

		$images  = $this->get('images');
		$folders = $this->get('folders');
		$state   = $this->get('state');

		$this->baseURL = COM_RSBMEDIA_BASEURL;
		$this->images  = &$images;
		$this->folders = &$folders;
		$this->state   = &$state;

		parent::display($tpl);
	}

	/**
	 * Set folder item in gallery.
	 *
	 * @param   int  $index  Folder index.
	 *
	 * @return void
	 */
	public function setFolder($index = 0)
	{
		if (isset($this->folders[$index]))
		{
			$this->_tmp_folder = &$this->folders[$index];
		}
		else
		{
			$this->_tmp_folder = new CMSObject;
		}
	}

	/**
	 * Set image item in gallery.
	 *
	 * @param   int  $index  Image index
	 *
	 * @return void
	 */
	public function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = $this->images[$index];
		}
		else
		{
			$this->_tmp_img = new CMSObject;
		}
	}
}
