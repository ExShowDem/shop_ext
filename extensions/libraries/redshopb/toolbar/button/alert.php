<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

/**
 * Represents an alert button.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Toolbar
 * @since       1.0
 */
class RedshopbToolbarButtonAlert extends RToolbarButton
{
	/**
	 * @var string
	 */
	protected $alert;

	/**
	 * Constructor.
	 *
	 * @param   string  $text       The button text.
	 * @param   string  $alert      The alert text.
	 * @param   string  $iconClass  The icon class.
	 * @param   string  $class      The button class.
	 */
	public function __construct($text, $alert, $iconClass = '', $class = '')
	{
		parent::__construct($text, $iconClass, $class);

		$this->alert = $alert;
	}

	/**
	 * Get the button alert.
	 *
	 * @return  string  The alert.
	 */
	public function getAlert()
	{
		return $this->alert;
	}

	/**
	 * Render the button.
	 *
	 * @param   boolean  $isOption  Is menu option?
	 *
	 * @return  string  The rendered button.
	 */
	public function render($isOption = false)
	{
		return RedshopbLayoutHelper::render('toolbar.button.alert', array('button' => $this));
	}
}
