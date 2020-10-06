<?php
/**
 * @package     Aesir-e-commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2014 - 2018 Aesir-e-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Bootstrap3\SetupFrontendFrameworkSteps as SetupFrontendFrameworkSteps;

class setupFrontendFrameworkCest
{
	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameTemplateWright;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $position;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $module;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $menuAssignment;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $moduleName;

	 /**
	  * setupFrontendFrameworkCest    constructor.
	  */
	public function __construct()
	{
		$this->nameTemplateWright                   = 'redcomponent - Default';
		$this->position                             = 'Right [sidebar2]';
		$this->module                               = 'Module';
		$this->menuAssignment                       = 'Menu Assignment';
		$this->moduleName                           =
			[
				'Aesir E-Commerce Status' => "Aesir E-Commerce Status",
				'Login Form' => "Login Form"
			];
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 * @since 1.0.0
	 * @throws Exception
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * @param SetupFrontendFrameworkSteps $I
	 * @return void
	 * @since 2.1.0
	 * @throws Exception
	 */
	public function configForBootstrap3(SetupFrontendFrameworkSteps $I)
	{
		$I->wantToTest('Edit redSHOPB2B configuration');
		$I->configure();

		$I->wantToTest('make Wright style Default');
		$I->makeWrightTemplateDefault($this->nameTemplateWright);
		$I->disableTheFloatingTemplateToolbars();

		$I->wantToTest('Switch status module do be at the correct module position');
		foreach ($this->moduleName as $value)
		{
			 $I->switchModulePosition($value, $this->module, $this->position, $this->menuAssignment);
		}
	}
}