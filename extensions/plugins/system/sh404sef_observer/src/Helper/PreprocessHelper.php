<?php
/**
 * @package     Sh-404sef_Observer
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2016 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Sh404sefObserver\Helper;

use Joomla\CMS\Factory;
use Symfony\Component\Process;

defined('_JEXEC') or die;

/**
 * @since  2.6.0
 */
class PreprocessHelper
{
	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $conditions = [];

	/**
	 * @var $this
	 */
	protected static $instance;

	/**
	 * @return $this
	 */
	static public function getInstance()
	{
		if (empty(static::$instance))
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * @param   array           $condition  A condition to add
	 * @param   boolean|string  $do         What to do
	 *
	 * @return  $this
	 * @since   2.6.0
	 */
	public function addCondition($condition, $do = 'toUpdate')
	{
		ksort($condition);

		$key = serialize($condition);

		if (is_bool($do))
		{
			if ($do)
			{
				$do = 'toDelete';
			}
			else
			{
				$do = 'toUpdate';
			}
		}

		if (empty($this->conditions[$do]))
		{
			$this->conditions[$do] = [];
		}

		$this->conditions[$do][$key] = $condition;

		return $this;
	}

	/**
	 * Process SEF URLs
	 *
	 * @return  void
	 *
	 * @since  2.6.0
	 */
	public function processSefUrl()
	{
		$db = Factory::getDbo();

		$conditions = [];

		foreach ($this->conditions as $key => $value)
		{
			if (!empty($value))
			{
				$conditions[$key] = array_values($value);
			}
		}

		if (empty($conditions))
		{
			return;
		}

		$this->conditions = [];

		$obj = (object) [
			'data' => json_encode($conditions)
		];

		$db->insertObject('#__sh404sef_observer', $obj);

		$command = [
			(new Process\PhpExecutableFinder)->find() ?? 'php',
			JPATH_ROOT . '/cli/sh404observer/process.php'
		];

		// Support an old approach if some old version of Process was loaded
		if (!method_exists(Process\Process::class, 'fromShellCommandline'))
		{
			$command = implode(' ', $command);
		}

		(new Process\Process($command))
			->setTimeout(0)
			->start();
	}
}
