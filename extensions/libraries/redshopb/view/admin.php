<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Base view.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  View
 * @since       1.0
 */
abstract class RedshopbViewAdmin extends RViewAdmin
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
	protected $displayBackToJoomla = true;

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
		parent::__construct($config);

		$this->sidebarData = array(
			'active' => strtolower($this->_name),
			'view' => $this
		);
	}

	/**
	 * Display method
	 *
	 * @param   string|null  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		RHtmlMedia::setFramework('bootstrap3');

		parent::display($tpl);
	}
}
