<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Base view.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  View
 * @since       1.0
 */
abstract class RedshopbView extends RViewSite
{
	/**
	 * The component title to display in the topbar layout (if using it).
	 * It can be html.
	 *
	 * @var string
	 */
	protected $componentTitle = '<strong>Aesir E-Commerce</strong>';

	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = true;

	/**
	 * The sidebar layout name to display.
	 *
	 * @var  boolean
	 */
	protected $sidebarLayout = 'sidebar';

	/**
	 * Do we have to display a topbar ?
	 *
	 * @var  boolean
	 */
	protected $displayTopBar = true;

	/**
	 * The topbar layout name to display.
	 *
	 * @var  boolean
	 */
	protected $topBarLayout = 'topbar';

	/**
	 * Do we have to display a topbar inner layout ?
	 *
	 * @var  boolean
	 */
	protected $displayTopBarInnerLayout = true;

	/**
	 * The topbar inner layout name to display.
	 *
	 * @var  boolean
	 */
	protected $topBarInnerLayout = 'topnav';

	/**
	 * True to display "Back to Joomla" link (only if displayJoomlaMenu = false)
	 *
	 * @var  boolean
	 */
	protected $displayBackToJoomla = false;

	/**
	 * True to display "Version 1.0.x"
	 *
	 * @var  boolean
	 */
	protected $displayComponentVersion = true;

	/**
	 * Redirect to another location
	 *
	 * @var  string
	 */
	protected $logoutReturnUri = 'index.php';

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.<br/>
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).<br/>
	 *                          charset: the character set to use for display<br/>
	 *                          escape: the name (optional) of the function to use for escaping strings<br/>
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)<br/>
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name<br/>
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)<br/>
	 *                          layout: the layout (optional) to use to display the view<br/>
	 */
	public function __construct($config = array())
	{
		// If user is Super Admin (or has permission to manage the core component, enables Back2Joomla link)
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$this->displayBackToJoomla = true;
		}

		parent::__construct($config);

		$this->sidebarData = array(
			'active' => strtolower($this->_name),
			'view' => $this
		);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$this->prepareDocument();

		$render = RedshopbLayoutHelper::render(
			$this->componentLayout,
			array(
				'view' => $this,
				'tpl' => $tpl,
				'component_title' => $this->componentTitle,
			)
		);

		if ($render instanceof Exception)
		{
			return $render;
		}

		echo $render;

		return true;
	}

	/**
	 * Method to prepares the document
	 *
	 * @return  void
	 */
	protected function prepareDocument()
	{
		$app    = Factory::getApplication();
		$params = $app->getParams();
		RedshopbBrowserBreadcrumbs::generateBreadcrumbs();
		$document = Factory::getDocument();

		if ($params->get('robots'))
		{
			$document->setMetaData('robots', $params->get('robots'));
		}

		$title         = null;
		$pathway       = $app->getPathway()->getPathway();
		$createLayouts = array('edit', 'create');
		$currentLayout = $app->input->getCmd('layout');
		$currentView   = $app->input->getCmd('view');
		$currentId     = $app->input->getInt('id');

		if (!empty($pathway))
		{
			$last = array_pop($pathway);

			if (is_object($last))
			{
				$title = $last->name;
			}
		}

		if ($currentView != 'shop' && $title)
		{
			if (in_array($currentLayout, $createLayouts))
			{
				if ($currentId)
				{
					$title = Text::_('JEDIT') . ' ' . $title;
				}
				elseif (!empty($pathway))
				{
					$title = null;
					$last  = array_pop($pathway);

					if (is_object($last))
					{
						$title = Text::_('JNEW') . ': ' . $last->name;
					}
				}
			}

			if (empty($title))
			{
				$title = $app->get('sitename');
			}
			elseif ($app->get('sitename_pagetitles', 0) == 1)
			{
				$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
			}
			elseif ($app->get('sitename_pagetitles', 0) == 2)
			{
				$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
			}
		}

		if (!empty($title) && !in_array($currentLayout, array('category', 'product', 'manufacturer')))
		{
			$document->setTitle($title);
		}
	}
}
