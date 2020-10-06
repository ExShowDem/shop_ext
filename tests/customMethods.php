<?php

trait CustomMethods
{
	/**
	 * Alias for run:test-preparation
	 *
	 * @return   void
	 */
	public function runPrepare()
	{
		$this->runTestPreparation();
	}

	/**
	 * Runs the UninstallCest so Joomla can drop the tables without errors when re-running tests
	 *
	 * @param   integer   $debug   Turn debugging on
	 *
	 * @return  void
	 */
	public function runClean($debug = 0)
	{
		$selenium = $this->taskSeleniumStandaloneServer()
			->setURL("http://localhost:4444")
			->runSelenium();

		if (!$this->isWindows())
		{
			$selenium->waitForSelenium()
				->run();
		}

		$cmd = $this->taskCodecept()
			->arg('--fail-fast');

		if ($debug)
		{
			$cmd->arg('--debug')
				->arg('--steps');
		}

		$cmd->arg('./acceptance/uninstall/')
			->run()
			->stopOnFail();
	}

	/**
	 * Runs all tests excluding Install so use run:prepare first
	 *
	 * @param   integer   $debug   Turn debugging on
	 *
	 * @return  void
	 */
	public function runTestsAlt($debug = 0)
	{
		$selenium = $this->taskSeleniumStandaloneServer()
			->setURL("http://localhost:4444")
			->runSelenium();

		if (!$this->isWindows())
		{
			$selenium->waitForSelenium()
				->run();
		}

		$selenium->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		$tests = array(
			'./acceptance/administrator/',
			'./acceptance/frontend/',
			'api',
			'./acceptance/integration/',
			'./acceptance/uninstall/',
		);

		foreach ($tests as $test)
		{
			$cmd = $this->taskCodecept()
				->arg('--tap')
				->arg('--fail-fast');

			if ($debug)
			{
				$cmd->arg('--steps')
					->arg('--debug');
			}

			$cmd->arg($test)
				->run()
				->stopOnFail();
		}

		$this->killSelenium();
	}
}
