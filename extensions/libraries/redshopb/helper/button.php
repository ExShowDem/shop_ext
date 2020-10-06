<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Represents a standard button.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Toolbar
 * @since       1.0
 */
class RedshopbHelperButton extends RToolbarButton
{
	/**
	 * @var  string
	 */
	public $type = null;

	/**
	 * @var  string
	 */
	public $model = '';

	/**
	 * Constructor.
	 *
	 * @param   string  $type   Button layout form
	 * @param   string  $model  Model for import
	 */
	public function __construct($type = '', $model = 'users')
	{
		$this->type  = $type;
		$this->model = $model;
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
		return RedshopbLayoutHelper::render(
			$this->type,
			array(
				'model' => $this->model
			)
		);
	}
}
