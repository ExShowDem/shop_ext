<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
	use _generated\AcceptanceTesterActions;

	/**
	 * Define custom actions here
	 */

	/**
	 * Tries to login n amount of times before failing
	 *
	 * @param   integer   $retry   Number of times to retry
	 *
	 * @return   void
	 */
	public function doFrontEndLoginRetry($retry = 1)
	{
		$this->retry('doFrontEndLogin', [], $retry);
	}

	/**
	 * Tries to logout n amount of times before failing
	 *
	 * @param   integer   $retry   Number of times to retry
	 *
	 * @return   void
	 */
	public function doFrontendLogoutRetry($retry = 1)
	{
		$this->retry('doFrontendLogout', [], $retry);
	}
	
	/**
	 * Runs the method passed n amount of times
	 *
	 * If the method fails it's run again until it either passes
	 * or the retry counter runs out
	 *
	 * @param   string    $method   The method to run
	 * @param   string    $params   Parameters for the method
	 * @param   integer   $retry    Number of time to retry
	 *
	 * @return   mixed
	 */
	public function retry($method, $params = [], $retry = 1)
	{
		if (0 === $retry)
		{
			return $this->{$method}(...$params);
		}

		try
		{
			$this->{$method}(...$params);
		}
		catch (Exception $e)
		{
			$this->comment("{$method} failed. Trying {$retry} more time(s)");
			$this->retry($method, $params, $retry - 1);
		}
	}
}
