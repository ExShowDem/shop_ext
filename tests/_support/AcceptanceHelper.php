<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Helper Class
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
/* Here you can define custom actions
 all public methods declared in helper class will be available in $I */

/**
 * Class AcceptanceHelper
 *
 * @package  Codeception\Module
 *
 * @since    1.4
 */
class AcceptanceHelper extends \Codeception\Module
{
	/**
	 * @var   RemoteWebDriver
	 * @since 2.0.3
	 */
	private $webDriver = null;
	
	
	/**
	 * @var   JoomlaBrowser
	 * @since 2.0.3
	 */
	private $joomlaBrowserModule = null;
	
	/**
	 * Function to Return value of desired Configuration
	 *
	 * @param  String   $configurationName  Name of the Configuration Parameter Needed
	 *
	 * @return mixed
	 */
	public function getConfig($configurationName)
	{
		$configuration = $this->config[$configurationName];

		return $configuration;
	}

	/**
	 * Function to Verify State of an Object
	 *
	 * @param   String  $expected  Expected State
	 * @param   String  $actual    Actual State
	 *
	 * @return void
	 */
	public function verifyState($expected, $actual)
	{
		$this->assertEquals($expected, $actual, "Assert that the Actual State is equal to the state we Expect");
	}

	/**
	 * Function to VerifyNotices
	 *
	 * @param   string  $expected  Expected Value
	 * @param   string  $actual    Actual Value
	 * @param   string  $page      Page for which we are Verifying
	 *
	 * @return void
	 */
	public function verifyNotices($expected, $actual, $page)
	{
		$this->assertEquals($expected, $actual, "Page " . $page . " Contains PHP Notices and Warnings");
	}
	/**
	 * Check if no javascript errors are present
	 *
	 * @return  void
	 * @since   2.0.3
	 */
	public function dontSeeJsError()
	{
		$logs = $this->webDriver->manage()->getLog('browser');
		foreach ($logs as $log)
		{
			if ($log['level'] == 'SEVERE')
			{
				throw new ModuleException($this, 'Some error in JavaScript: ' . json_encode($log));
			}
		}
	}
	
	/**
	 * Wait for ajax load
	 *
	 * @param   integer  $timeout  Time out
	 *
	 * @return  void
	 * @since   2.0.3
	 */
	public function waitAjaxLoad($timeout = 120)
	{
		$this->joomlaBrowserModule->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', $timeout);
		$this->joomlaBrowserModule->wait(1);
		$this->dontSeeJsError();
	}
	
	/**
	 * Wait for page to load
	 *
	 * @param   integer  $timeout  Time out
	 *
	 * @return  void
	 * @since   5.6.0
	 */
	public function waitPageLoad($timeout = 120)
	{
		$this->joomlaBrowserModule->waitForJs('return document.readyState == "complete"', $timeout);
		$this->waitAjaxLoad($timeout);
		$this->dontSeeJsError();
	}
}
