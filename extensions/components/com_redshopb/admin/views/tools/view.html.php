<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Tools View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewTools extends RedshopbViewAdmin
{
	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items    = str_replace('.php', '', JFolder::files(JPATH_ADMINISTRATOR . '/components/com_redshopb/updates', '\.php$'));
		$this->cliItems = str_replace('.php', '', JFolder::files(JPATH_SITE . '/cli/com_redshopb', '\.php$', false, false, array('joomla_framework.php')));

		usort($this->items, 'version_compare');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_TOOLS_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$rebuildTools = RToolbarBuilder::createStandardButton(
			'tools.redshopbDefaults', 'COM_REDSHOPB_TOOLS_RESET', 'redshopb-defaults', 'icon-refresh', false
		);
		$group->addButton($rebuildTools);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
