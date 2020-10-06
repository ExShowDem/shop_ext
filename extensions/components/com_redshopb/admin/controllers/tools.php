<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Tools Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerTools extends RedshopbControllerForm
{
	/**
	 * Resets all redshopb data to default values
	 *
	 * @return void
	 */
	public function redshopbDefaults()
	{
		/** @var RedshopbModelTools $model */
		$model = $this->getModel();
		$model->redshopbDefaults();
		$this->setRedirect('index.php?option=com_redshopb&view=tools');

		$this->redirect();
	}

	/**
	 * Execute Patch
	 *
	 * @return void
	 */
	public function executePatch()
	{
		$app = Factory::getApplication();
		ob_start();
		$version = $app->input->getString('version', '');

		if (!$version)
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_TOOLS_EXECUTE_PATCH_DONT_EXIST', $version), 'error');
			$this->setRedirect('index.php?option=com_redshopb&view=tools');

			return;
		}

		// Check if we have that version for execution
		$items = str_replace('.php', '', JFolder::files(JPATH_ADMINISTRATOR . '/components/com_redshopb/updates', '\.php$'));

		if (!in_array($version, $items))
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_TOOLS_EXECUTE_PATCH_DONT_EXIST', $version), 'error');
			$this->setRedirect('index.php?option=com_redshopb&view=tools');

			return;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_redshopb/updates/' . $version . '.php';
		$className = 'Com_RedshopbUpdateScript_' . str_replace('.', '_', $version);
		$class     = new $className;

		if (method_exists($class, 'execute'))
		{
			$class->{'execute'}();
		}

		if (method_exists($class, 'executeAfterUpdate'))
		{
			$class->{'executeAfterUpdate'}();
		}

		$syncOutput = ob_get_contents();
		ob_end_clean();

		$app->enqueueMessage($syncOutput, 'message');
		$this->setRedirect('index.php?option=com_redshopb&view=tools');
	}

	/**
	 * Execute CLI Patch
	 *
	 * @return void
	 */
	public function executeCliPatch()
	{
		$app = Factory::getApplication();
		ob_start();
		$file = $app->input->getString('cli', '');

		if (!$file)
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_TOOLS_EXECUTE_FILE_DONT_EXIST', $file), 'error');
			$this->setRedirect('index.php?option=com_redshopb&view=tools');

			return;
		}

		// Check if we have that version for execution
		$items = str_replace('.php', '', JFolder::files(JPATH_SITE . '/cli/com_redshopb', '\.php$'));

		if (!in_array($file, $items))
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_TOOLS_EXECUTE_FILE_DONT_EXIST', $file), 'error');
			$this->setRedirect('index.php?option=com_redshopb&view=tools');

			return;
		}

		echo nl2br(shell_exec('php ' . JPATH_SITE . '/cli/com_redshopb/' . $file . '.php'));

		$syncOutput = ob_get_contents();
		ob_end_clean();

		$app->enqueueMessage($syncOutput, 'message');
		$this->setRedirect('index.php?option=com_redshopb&view=tools');
	}
}
