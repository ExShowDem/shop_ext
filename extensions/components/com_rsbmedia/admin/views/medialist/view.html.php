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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
/**
 * HTML View class for the Rsbmedia component
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 * @since       1.0
 */
class RsbmediaViewMediaList extends HtmlView
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
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			return $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
		}

		// Do not allow cache
		$app->allowCache(false);

		HTMLHelper::_('behavior.framework', true);

		Factory::getDocument()->addScriptDeclaration("
		window.addEvent('domready', function()
		{
			window.parent.document.updateUploader();
			$$('a.img-preview').each(function(el)
			{
				el.addEvent('click', function(e)
				{
					window.top.document.preview.fromElement(el);
					return false;
				});
			});
		});"
		);

		$images    = $this->get('images');
		$documents = $this->get('documents');
		$folders   = $this->get('folders');
		$state     = $this->get('state');

		// Check for invalid folder name
		if (empty($state->folder))
		{
			$dirname = $app->input->getString('folder', '');

			if (!empty($dirname))
			{
				$dirname = htmlspecialchars($dirname, ENT_COMPAT, 'UTF-8');
				$app->enqueueMessage(Text::sprintf('COM_RSBMEDIA_ERROR_UNABLE_TO_BROWSE_FOLDER_WARNDIRNAME', $dirname), 'warning');
			}
		}

		$this->baseURL   = Uri::root();
		$this->images    = &$images;
		$this->documents = &$documents;
		$this->folders   = &$folders;
		$this->state     = &$state;

		parent::display($tpl);
	}

	/**
	 * Method for set folder
	 *
	 * @param   integer  $index  Index
	 *
	 * @return  void
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
	 * Method for set image
	 *
	 * @param   integer  $index  Index
	 *
	 * @return  void
	 */
	public function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = &$this->images[$index];
		}
		else
		{
			$this->_tmp_img = new CMSObject;
		}
	}

	/**
	 * Method for set document
	 *
	 * @param   integer  $index  Index
	 *
	 * @return  void
	 */
	public function setDoc($index = 0)
	{
		if (isset($this->documents[$index]))
		{
			$this->_tmp_doc = &$this->documents[$index];
		}
		else
		{
			$this->_tmp_doc = new CMSObject;
		}
	}
}
