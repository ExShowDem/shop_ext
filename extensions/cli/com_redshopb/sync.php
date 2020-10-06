<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Application\CliApplication;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

$GLOBALS['scriptHasFinishedCorrect'] = false;

/**
 * Shutdown Handler
 *
 * @return  void
 *
 * @since 1.13.0
 */
function shutdownHandler()
{
	$error                    = error_get_last();
	$scriptHasFinishedCorrect = $GLOBALS['scriptHasFinishedCorrect'];

	if (!$scriptHasFinishedCorrect && !empty($error) && !in_array($error['type'], array(E_NOTICE, E_STRICT, E_DEPRECATED)))
	{
		fwrite(STDOUT, "The last notice was:\n");

		foreach ($error as $name => $value)
		{
			fwrite(STDOUT, $name . ': ' . $value . "\n");
		}

		$errorText = 'Type number: ' . $error['type'] . '; '
			. 'Message: ' . $error['message'] . '; '
			. 'File: ' . $error['file'] . '; '
			. 'Line: ' . $error['line'];

		if (class_exists('Log', false))
		{
			Log::add(
				$errorText,
				Log::ALL, 'webservice'
			);
		}

		if (class_exists('SyncApplicationCli', false))
		{
			SyncApplicationCli::getInstance()
				->sendErrorMail(
					array(
						array(
							'text' => $errorText,
							'nl' => true,
							'type' => 'error'
						)
					),
					$forceSendMail = true
				);
		}
	}
}

register_shutdown_function('shutdownHandler');

/**
 * Synchronize cli application.
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 * @since       1.0
 */
class SyncApplicationCli extends CliApplication
{
	/**
	 * Force execution, ignoring checkout
	 *
	 * @var    boolean
	 */
	private $isRootForced = false;

	/**
	 * Start time for the sync process
	 *
	 * @var    string
	 */
	private $time = null;

	/**
	 * List of output messages
	 *
	 * @var    array
	 */
	private $messages = array();

	/**
	 * Flag if we have error in processing CLI
	 *
	 * @var    boolean
	 */
	private $processError = false;

	/**
	 * Sync name that is currently running
	 *
	 * @var    string
	 */
	private $syncName = 'Sync';

	/**
	 * Sync timeout in hours that we allow to run. After this time it will force run it again.
	 *
	 * @var    string
	 */
	private $syncTimeoutHours = 6;

	/**
	 * Entry point for CLI script
	 *
	 * @throws Exception
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		// Arguments
		$longopts = array(
			"force"
		);

		$args         = (getopt(':::', $longopts));
		$runSyncAgain = false;

		// Forced via cli arguments
		if (isset($args['force']))
		{
			$this->isRootForced = true;
		}

		$this->time = microtime(true);
		$counter    = 0;

		// Host 'localhost/' uses especially for sh404sef for avoid redirect here
		$_SERVER['HTTP_HOST'] = 'localhost/';
		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
		$app = Factory::getApplication('site');
		$app->input->set('option', 'com_redshopb');

		// Import the system plugins.
		PluginHelper::importPlugin('system');
		$app->triggerEvent('onAfterInitialise');

		JLoader::import('redshopb.library');
		RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

		// Load common and local language files
		$lang              = Factory::getLanguage();
		$langCodes         = LanguageHelper::getLanguages('lang_code');
		$numberOfLanguages = count($langCodes);

		// Load language file
		$lang->load('com_redshopb', JPATH_ADMINISTRATOR, null, true, false)
		|| $lang->load('com_redshopb', JPATH_ADMINISTRATOR . "/components/com_redshopb", null, true, false)
		|| $lang->load('com_redshopb', JPATH_ADMINISTRATOR, RTranslationHelper::getSiteLanguage(), true, true)
		|| $lang->load('com_redshopb', JPATH_ADMINISTRATOR . "/components/com_redshopb", RTranslationHelper::getSiteLanguage(), true, true);

		// Print a blank line.
		$this->out();
		$this->out(Text::_('COM_REDSHOPB_CLI_SYNC_APP'));
		$this->out('============================');
		$this->out();

		$factory = new Lock\Factory(new FlockStore(Factory::getConfig()->get('tmp_path')));
		$lock    = $factory->createLock('rb-sync');

		if (!$lock->acquire())
		{
			if ($this->isRootForced)
			{
				$lock->release();
				$lock->acquire();

				$this->out('Forcing cron job and sending mail');

				// We send email as soon as this error happened
				$this->sendErrorMail(
					array(
						array(
							'text' => Text::sprintf('COM_REDSHOPB_CLI_PROCESS_IS_FORCED_TO_CONTINUE', $this->syncTimeoutHours),
							'nl' => true,
							'type' => 'error'
						)
					),
					$forceSendMail = true
				);
			}
			else
			{
				$this->out(Text::_('COM_REDSHOPB_CLI_ANOTHER_PROCESS'));
				Log::add(Text::_('COM_REDSHOPB_CLI_ANOTHER_PROCESS'), Log::WARNING, 'webservice');

				return;
			}
		}

		try
		{
			/** @var RedshopbTableSyncEdit $rootCron */
			$rootCron = RedshopbTable::getAdminInstance('SyncEdit');

			$dateInstance = Date::getInstance();
			$date         = $dateInstance->toSql();

			// Variables mute_from and mute_to stored with user UTC format
			$dateInstance->setTimeZone(new DateTimeZone(Factory::getConfig()->get('offset')));
			$time = $dateInstance->format('H:i:s', true);

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('node.id, node.name, node.mask_time, node.offset_time, node.params, node.checked_out, node.checked_out_time')
				->select('node.plugin')
				->from($db->qn('#__redshopb_cron', 'node'))

				// Avoid sync with unpublished parents
				->leftJoin($db->qn('#__redshopb_cron', 'parent') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND parent.state = 0')
				->leftJoin($db->qn('#__redshopb_cron', 'c') . ' ON c.id = node.parent_id')
				->where('node.state = 1')
				->where('node.level > 0')
				->where('node.next_start <= STR_TO_DATE(' . $db->q($date) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
				->where(
					'if (node.mute_from = STR_TO_DATE(' . $db->q('00:00:00') . ', ' . $db->q('%H:%i:%s')
					. ') AND node.mute_to = STR_TO_DATE(' . $db->q('00:00:00') . ', ' . $db->q('%H:%i:%s') . ')'
					. ', 1 = 1, STR_TO_DATE(' . $db->q($time) . ', ' . $db->q('%H:%i:%s') . ') NOT BETWEEN node.mute_from AND node.mute_to)'
				)
				->where('parent.id IS NULL')
				->group('node.id')
				->order('node.next_start ASC, node.lft ASC');

			$table = $db->setQuery($query, 0, 1)->loadObject();

			if ($table)
			{
				$runSyncAgain = true;

				$rootCron->checkIn($table->id);

				$continue       = true;
				$startSync      = true;
				$this->syncName = $table->name;

				$this->out(Text::sprintf('COM_REDSHOPB_CLI_EXECUTE', $table->plugin . ': ' . $table->name));
				Log::add(Text::sprintf('COM_REDSHOPB_CLI_EXECUTE', $table->plugin . ': ' . $table->name), Log::INFO, 'webservice');

				/** @var RedshopbModelSync $model */
				$model = RedshopbModel::getAdminInstance('Sync', array(), 'com_redshopb');

				while ($continue)
				{
					$counter++;
					ob_start();
					$return     = $model->syncSelectedItem($table->id, false, $startSync);
					$startSync  = false;
					$syncOutput = ob_get_contents();
					ob_end_clean();

					if (isset($return['success']) && is_bool($return['success']) && $return['success'])
					{
						/** @var RedshopbTableSyncEdit $currentSync */
						$currentSync = RedshopbTable::getAdminInstance('SyncEdit');
						$currentSync->load($table->id);
						$return['continue'] = false;
						$nextExecute        = Date::getInstance(
							strtotime(
								$currentSync->offset_time, Date::getInstance()->toUnix()
							)
						)->format($currentSync->mask_time);
						$currentSync->set('next_start', $nextExecute);

						if (!$currentSync->store())
						{
							throw new Exception(Text::_('COM_REDSHOPB_CLI_ERROR_UPDATE_TABLE'));
						}
					}
					elseif (isset($return['success']) && is_array($return['success']))
					{
						// If we have parameter is continuous set and is set to True it will continue in the same process session
						$return['continue'] = !empty($return['success']['isContinuous']);

						// We will check for number of iterations allowed per process so we do not end up in a loop
						if ($return['continue'])
						{
							/** @var RedshopbTableSyncEdit $currentSync */
							$currentSync = RedshopbTable::getAdminInstance('SyncEdit');
							$currentSync->load($table->id);
							$items = $currentSync->items_process_step > 0
								? $currentSync->items_total / $currentSync->items_process_step
								: $currentSync->items_total;

							// Something happened with the counters and we need to restart the sync cron
							if ($currentSync->items_total <= 0)
							{
								$return['continue'] = false;
								$this->errorOut(Text::_('COM_REDSHOPB_CLI_ERROR_ITEMS_TOTAL_ZERO'));
							}

							// If items process step is 0 then we are out of time and need to restart the sync
							// Or if the maximum number of iterations (+10) is exceeded we need to restart the sync
							if (($currentSync->items_process_step == 0 && $counter > ($numberOfLanguages + 10))
								|| (($items * $numberOfLanguages) + 10) < $counter)
							{
								$return['continue'] = false;
								$this->errorOut(
									Text::sprintf(
										'COM_REDSHOPB_CLI_ERROR_ITEMS_ITERATIONS_EXCEEDED',
										$counter,
										($currentSync->items_process_step == 0 ? $numberOfLanguages : ($items * $numberOfLanguages)) + 10
									)
								);
							}

							if ($return['continue'])
							{
								$this->out(
									Text::sprintf(
										'COM_REDSHOPB_CLI_ITEMS_ITERATIONS',
										$counter,
										(($items * $numberOfLanguages) + 10)
									)
								);
							}
						}
					}
					else
					{
						$runSyncAgain       = false;
						$return['continue'] = false;
						$app->enqueueMessage(Text::_('COM_REDSHOPB_SYNC_FAILED') . ' ' . $this->syncName . ' ' . $syncOutput, 'error');
					}

					if (!empty($syncOutput))
					{
						$app->enqueueMessage($syncOutput, 'message');
					}

					$messages           = $app->getMessageQueue();
					$return['messages'] = array();

					if (is_array($messages))
					{
						foreach ($messages as $msg)
						{
							switch ($msg['type'])
							{
								case 'message':
									$typeMessage = 'success';
									Log::add($msg['message'], Log::INFO, 'webservice');
									break;
								case 'notice':
									$typeMessage = 'info';
									Log::add($msg['message'], Log::NOTICE, 'webservice');
									break;
								case 'error':
									$typeMessage = 'important';
									Log::add($msg['message'], Log::ERROR, 'webservice');
									$this->errorOut($msg['type'] . ': ' . $msg['message']);
									break;
								case 'warning':
									$typeMessage = 'warning';
									Log::add($msg['message'], Log::WARNING, 'webservice');
									break;
								default:
									$typeMessage = $msg['type'];
									Log::add($msg['message'], Log::DEBUG, 'webservice');
							}

							$return['messages'][] = array('message' => $msg['message'], 'type_message' => $typeMessage);
						}
					}

					if (count($return['messages']))
					{
						foreach ($return['messages'] as $message)
						{
							$this->out($message['type_message'] . ': ' . $message['message']);
						}
					}

					if (isset($return['continue']) && $return['continue'])
					{
						$continue = true;
					}
					else
					{
						$continue = false;
					}

					// Clear message queue to avoid record events from previous sync part to log files
					$app->getMessageQueue(true);
				}
			}
			else
			{
				$runSyncAgain = false;
				$this->out(Text::_('COM_REDSHOPB_CLI_NOTHING_EXECUTE'));
				Log::add(Text::_('COM_REDSHOPB_CLI_NOTHING_EXECUTE'), Log::INFO, 'webservice');
			}
		}
		catch (Exception $e)
		{
			// Display the error
			$this->errorOut($e->getMessage());
			$runSyncAgain = false;

			Log::add($e->getMessage(), Log::ERROR, 'webservice');
		}

		finally
		{
			$lock->release();
		}

		$this->out();

		// Total reporting.
		$time = round(microtime(true) - $this->time, 3);
		$this->out(Text::sprintf('COM_REDSHOPB_CLI_PROCESS_COMPLETE', $time), true);
		Log::add(Text::sprintf('COM_REDSHOPB_CLI_PROCESS_COMPLETE', $time), Log::INFO, 'webservice');

		// Print a blank line at the end.
		$this->out();

		// We send email if there was some error reported
		$this->sendErrorMail($this->messages);

		$GLOBALS['scriptHasFinishedCorrect'] = true;

		// We do this automatically if the sync is not completed yet
		if ($runSyncAgain)
		{
			$this->doExecuteSyncFunctionAgain();
		}
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  CliApplication  Instance of $this to allow chaining.
	 *
	 * @codeCoverageIgnore
	 * @since   11.1
	 */
	public function out($text = '', $nl = true)
	{
		$this->messages[] = array('text' => $text, 'nl' => $nl, 'type' => 'message');

		return parent::out($text, $nl);
	}

	/**
	 * Write a error string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  CliApplication  Instance of $this to allow chaining.
	 */
	public function errorOut($text = '', $nl = true)
	{
		$this->messages[]   = array('text' => $text, 'nl' => $nl, 'type' => 'error');
		$this->processError = true;

		return parent::out($text, $nl);
	}

	/**
	 * We send email if there was some error reported
	 *
	 * @param   array  $messages       Text messages to display in the mail.
	 * @param   bool   $forceSendMail  Send mail even if not process Error flag is set
	 *
	 * @return  void
	 */
	public function sendErrorMail($messages, $forceSendMail = false)
	{
		if ($this->processError || $forceSendMail)
		{
			$recipients = RedshopbEntityConfig::getInstance()->get('sync_notification_recipients', '', 'raw');

			if (!empty($recipients))
			{
				$app        = Factory::getApplication('site');
				$mailer     = RFactory::getMailer();
				$time       = Date::getInstance()->format('Y.m.d H:i:s', true);
				$recipients = explode(',', $recipients);
				$body       = '';

				foreach ($messages as $message)
				{
					$body .= $message['type'] == 'error' ? '<span style="color:red; font-weight: bold;">' : '';
					$body .= $message['text'];
					$body .= $message['type'] == 'error' ? '</span>' : '';
					$body .= $message['nl'] ? '<br />' : '';
				}

				$mailer->setSender(
					array(
						$app->get('mailfrom'),
						$app->get('fromname')
					)
				);
				$mailer->isHtml(true);
				$mailer->Encoding = 'base64';
				$mailer->setSubject(Text::sprintf('COM_REDSHOPB_CLI_PROCESS_ERROR_MAIL', $this->syncName, $time));
				$mailer->setBody($body);

				foreach ($recipients as $i => $recipient)
				{
					$i == 0 ? $mailer->addRecipient(array(trim($recipient))) : $mailer->addCc(array(trim($recipient)));
				}

				$result = $mailer->Send();

				if (!is_bool($result))
				{
					$this->out($result);
				}

				$this->out('Error Mail sent!');
			}
		}
	}

	/**
	 * Execute sync process again
	 *
	 * @return  void
	 */
	public function doExecuteSyncFunctionAgain()
	{
		// Execute with proper php version, proper php ini file and memory limit as it was used in the current process
		$cmd = '"' . (!empty($_SERVER['_']) ? $_SERVER['_'] : PHP_BINDIR . '/php') . '"'
			. ' -c "' . php_ini_loaded_file() . '"'
			. ' -d memory_limit=' . ini_get('memory_limit')
			. ' "' . __FILE__ . '"';

		// Windows or Linux
		if (substr(php_uname(), 0, 7) == "Windows")
		{
			pclose(popen('start "' . microtime() . '" /B ' . $cmd, 'r'));
		}
		else
		{
			shell_exec($cmd . " > /dev/null &");
		}
	}
}

CliApplication::getInstance('SyncApplicationCli')->execute();
