<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  redshopb_newsletter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
JLoader::import('redshopb.library');
JLoader::import('helpers.helper', JPATH_ADMINISTRATOR . '/components/com_acymailing');

/**
 * PlgRedshopb_NewsletterAcyMailing function.
 *
 * @package  Redshopb.Plugin
 * @since    1.6.8
 */
class PlgRedshopb_NewsletterAcymailing extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var boolean
	 */
	protected $acyInstalled = false;

	/**
	 * Constructor
	 *
	 * @param   object  $subject   The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'))
		{
			include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
			$this->acyInstalled = true;
		}
	}

	/**
	 * Method is called right after the content is saved.
	 *
	 * @param   string  $context  The context of the content passed to the plugin.
	 * @param   object  $table    A Content object.
	 * @param   bool    $isNew    If the content is just about to be created.
	 *
	 * @return  void|boolean
	 */
	public function onRedshopbAfterSave($context, $table, $isNew)
	{
		$app = Factory::getApplication();

		if (!$this->acyInstalled && array_search($context, array('com_redshopb.newsletter', 'com_redshopb.newsletter_list')))
		{
			$app->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_ACYMAILING_NOT_FOUND'), 'warning');

			return false;
		}

		$db = Factory::getDbo();

		switch ($context)
		{
			case 'com_redshopb.newsletter':
				$item            = new stdClass;
				$item->published = $table->get('state');
				$item->type      = 'news';
				$item->visible   = 1;
				$item->userid    = Factory::getUser()->id;
				$item->alias     = $table->get('alias');
				$item->html      = 1;

				// Subject of your Newsletter
				$item->subject = $table->get('subject');

				// Body of your Newsletter... the text version will be automatically generated from this html version.
				$item->body = $table->get('body');

				// ID of the template attached to this Newsletter so the CSS defined in that AcyMailing template will be applied to your Newsletter.
				// $item->tempid = $table->get('template_id');

				if (!$isNew || $table->get('plugin') == $this->_name)
				{
					$item->mailid = $table->get('plugin_id');
				}

				$itemClass = acymailing_get('class.mail');
				$itemId    = $itemClass->save($item);

				// The save function returns you the ID of the inserted Newsletter so you can then use it to insert e-mails in the queue.
				if ($itemId)
				{
					$table->set('plugin_id', $itemId);
					$table->set('plugin', $this->_name);

					// Store the data.
					if (!$table->store())
					{
						$app->enqueueMessage($table->getError(), 'error');

						return false;
					}

					$listMailClass = acymailing_get('class.listmail');
					$query         = $db->getQuery(true)
						->delete($db->qn('#__acymailing_listmail'))
						->where('mailid = ' . (int) $itemId);

					if (!$db->setQuery($query)->execute())
					{
						$app->enqueueMessage($table->getError(), 'error');

						return false;
					}

					$query->clear()
						->select('plugin_id')
						->from($db->qn('#__redshopb_newsletter_list'))
						->where('plugin = ' . $db->q($this->_name))
						->where('id = ' . (int) $table->get('newsletter_list_id'));
					$result = $db->setQuery($query)->loadResult();

					if ($result)
					{
						if (!$listMailClass->save($itemId, array($result)))
						{
							return false;
						}
					}
				}
				break;
			case 'com_redshopb.newsletter_list':
				$list                = new stdClass;
				$list->name          = $table->get('name');
				$list->type          = 'list';
				$list->published     = $table->get('state');
				$list->visible       = 1;
				$list->userid        = Factory::getUser()->id;
				$list->alias         = $table->get('alias');
				$list->access_sub    = 'all';
				$list->access_manage = 'none';
				$list->languages     = 'all';

				if (!$isNew || $table->get('plugin') == $this->_name)
				{
					$list->listid = $table->get('plugin_id');
				}

				$listClass = acymailing_get('class.list');

				// This function will create the list and return the ID of the created list
				// The save function returns you the ID of the inserted Newsletter so you can then use it to insert e-mails in the queue.
				$listId = $listClass->save($list);

				if ($listId)
				{
					$table->set('plugin_id', $listId);
					$table->set('plugin', $this->_name);

					// Store the data.
					if (!$table->store())
					{
						$app->enqueueMessage($table->getError(), 'error');

						return false;
					}

					// Check user exists in Acymailing table, if no - create then
					$subQuery = $db->getQuery(true)
						->select('as.userid')
						->from($db->qn('#__acymailing_subscriber', 'as'));

					$query = $db->getQuery(true)
						->select('ju.*')
						->from($db->qn('#__users', 'ju'))
						->leftJoin($db->qn('#__redshopb_user', 'ru') . ' ON ru.joomla_user_id = ju.id')
						->leftJoin($db->qn('#__redshopb_newsletter_user_xref', 'rnux') . ' ON ru.id = rnux.user_id')
						->where('rnux.newsletter_list_id = ' . (int) $table->get('id'))
						->where('ru.joomla_user_id NOT IN (' . $subQuery . ')')
						->group('ju.id');
					$users = $db->setQuery($query)->loadObjectList();

					if ($users)
					{
						$userHelper = acymailing_get('helper.user');

						foreach ($users as $user)
						{
							$joomUser            = new stdClass;
							$joomUser->email     = trim(strip_tags($user->email));
							$joomUser->name      = trim(strip_tags($user->name));
							$joomUser->confirmed = 1;
							$joomUser->enabled   = 1 - $user->block;
							$joomUser->userid    = $user->id;

							if (!$userHelper->validEmail($joomUser->email))
							{
								continue;
							}

							$userClass               = acymailing_get('class.subscriber');
							$userClass->checkVisitor = false;
							$userClass->sendConf     = false;
							$userClass->save($joomUser);
						}
					}

					$time = time();

					// Un subscribe users who not set in current saves, but who subscribe in previous saves
					$subQuery = $db->getQuery(true)
						->select('acs.subid')
						->from($db->qn('#__acymailing_subscriber', 'acs'))
						->leftJoin($db->qn('#__redshopb_user', 'ru') . ' ON ru.joomla_user_id = acs.userid')
						->leftJoin($db->qn('#__redshopb_newsletter_user_xref', 'rnux') . ' ON ru.id = rnux.user_id')
						->where('rnux.newsletter_list_id = ' . (int) $table->get('id'));

					$query = $db->getQuery(true)
						->delete($db->qn('#__acymailing_listsub'))
						->where('subid NOT IN (' . $subQuery . ')')
						->where('listid = ' . (int) $listId)
						->where('status = 1');

					if (!$db->setQuery($query)->execute())
					{
						$app->enqueueMessage($table->getError(), 'error');

						return false;
					}

					$subQuery = $db->getQuery(true)
						->select('als.subid')
						->from($db->qn('#__acymailing_listsub', 'als'))
						->where('als.listid = ' . (int) $listId);

					$query = $db->getQuery(true)
						->select('acs.subid')
						->from($db->qn('#__acymailing_subscriber', 'acs'))
						->leftJoin($db->qn('#__redshopb_user', 'ru') . ' ON ru.joomla_user_id = acs.userid')
						->leftJoin($db->qn('#__redshopb_newsletter_user_xref', 'rnux') . ' ON ru.id = rnux.user_id')
						->where('acs.subid NOT IN (' . $subQuery . ')')
						->where('rnux.newsletter_list_id = ' . (int) $table->get('id'));

					$userIds = $db->setQuery($query)->loadColumn();

					if ($userIds)
					{
						foreach ($userIds as $userId)
						{
							$column          = new stdClass;
							$column->listid  = $listId;
							$column->subid   = $userId;
							$column->subdate = $time;
							$column->status  = 1;

							if (!$db->insertObject('#__acymailing_listsub', $column))
							{
								$app->enqueueMessage($table->getError(), 'error');

								return false;
							}
						}
					}
				}
				break;
		}

		return true;
	}

	/**
	 * Content is passed by reference, but after the deletion.
	 *
	 * @param   string  $context  The context of the content passed to the plugin.
	 * @param   object  $table    A Content object.
	 *
	 * @return  void|boolean
	 */
	public function onRedshopbAfterDelete($context, $table)
	{
		if ($table->get('plugin') == $this->_name)
		{
			if (!$this->acyInstalled && array_search($context, array('com_redshopb.newsletter', 'com_redshopb.newsletter_list')))
			{
				Factory::getApplication()->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_ACYMAILING_NOT_FOUND'), 'warning');

				return false;
			}

			switch ($context)
			{
				case 'com_redshopb.newsletter':
					$mailClass = acymailing_get('class.mail');
					$mailClass->delete($table->get('plugin_id'));
					break;
				case 'com_redshopb.newsletter_list':
					$listClass = acymailing_get('class.list');
					$listClass->delete($table->get('plugin_id'));
					break;
			}
		}

		return true;
	}

	/**
	 * onRedshopbSendNewsletter
	 *
	 * @param   int  $id  Newletter id in redshopb side
	 *
	 * @return  null
	 */
	public function onRedshopbSendNewsletter($id)
	{
		if (!$this->acyInstalled)
		{
			Factory::getApplication()->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_ACYMAILING_NOT_FOUND'), 'warning');

			return null;
		}

		$app     = Factory::getApplication();
		$input   = $app->input;
		$db      = Factory::getDBO();
		$mailId  = $this->getMailId($id);
		$subTask = $input->getCmd('subtask', '');

		if (!$mailId)
		{
			$app->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_NO_RECEIVER'), 'warning');

			return null;
		}

		switch ($subTask)
		{
			case 'schedule':
				$senddate         = $input->getString('senddate', '');
				$sendhours        = $input->getString('sendhours', '');
				$sendminutes      = $input->getString('sendminutes', '');
				$senddateComplete = $senddate . ' ' . $sendhours . ':' . $sendminutes;
				$user             = Factory::getUser();

				if (empty($senddate))
				{
					$app->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_SPECIFY_DATE'), 'warning');
					$app->redirect(
						'index.php?option=com_redshopb&view=newsletter&layout=scheduleconfirm&tmpl=component&subtask=schedule&id=' . $id
					);

					return null;
				}

				$realSendDate = acymailing_getTime($senddateComplete);

				if ($realSendDate < time())
				{
					$app->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_DATE_FUTURE'), 'warning');
					$app->redirect(
						'index.php?option=com_redshopb&view=newsletter&layout=scheduleconfirm&tmpl=component&subtask=schedule&id=' . $id
					);

					return null;
				}

				$mail            = new stdClass;
				$mail->mailid    = $mailId;
				$mail->senddate  = $realSendDate;
				$mail->sentby    = $user->id;
				$mail->published = 2;

				$mailClass = acymailing_get('class.mail');
				$mailClass->save($mail);

				$myNewsletter = $mailClass->get($mailId);
				$app->enqueueMessage(
					Text::sprintf(
						'PLG_REDSHOPB_NEWSLETTER_AUTOSEND_DATE', '<b><i>' . $myNewsletter->subject . '</i></b>', acymailing_getDate($realSendDate)
					),
					'success'
				);
				break;
			case 'continuesend':
				$config      = acymailing_config();
				$newcrontime = time() + 120;

				if ($config->get('cron_next') < $newcrontime)
				{
					$newValue            = new stdClass;
					$newValue->cron_next = $newcrontime;
					$config->save($newValue);
				}

				require_once 'helpers/queue.php';

				$totalSend   = $input->getInt('totalsend', 0);
				$alreadySent = $input->getInt('alreadysent', 0);

				$helperQueue         = new RedshopbAcyQueue;
				$helperQueue->mailid = $mailId;
				$helperQueue->report = true;
				$helperQueue->total  = $totalSend;
				$helperQueue->start  = $alreadySent;
				$helperQueue->pause  = $config->get('queue_pause');
				$helperQueue->process($id);
				$app->close();
				break;
			default:
				$user                = Factory::getUser();
				$time                = time();
				$queueClass          = acymailing_get('class.queue');
				$queueClass->onlynew = $input->getInt('onlynew', 0);
				$totalSub            = $queueClass->queue($mailId, $time);

				if (empty($totalSub))
				{
					$app->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_NO_PROCESS'), 'warning');

					return null;
				}

				$mailObject            = new stdClass;
				$mailObject->senddate  = $time;
				$mailObject->published = 1;
				$mailObject->mailid    = $mailId;
				$mailObject->sentby    = $user->id;
				$db->updateObject('#__acymailing_mail', $mailObject, 'mailid');

				$config    = acymailing_config();
				$queueType = $config->get('queue_type');

				if ($queueType == 'onlyauto')
				{
					$app->enqueueMessage(Text::sprintf('PLG_REDSHOPB_NEWSLETTER_ADDED_QUEUE', $totalSub));
					$app->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_AUTOSEND_CONFIRMATION'));
				}
				else
				{
					$app->redirect(
						'index.php?option=com_redshopb&view=newsletter&layout=sendready&tmpl=component&subtask=continuesend&task=newsletter.send&id='
						. $id . '&totalsend=' . $totalSub . '&' . Session::getFormToken() . '=1'
					);
				}
				break;
		}

		return null;
	}

	/**
	 * Get mail id in acy mailing
	 *
	 * @param   int  $id  Id newsletter in redshopb
	 *
	 * @return mixed
	 */
	protected function getMailId($id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true)
			->select('plugin_id')
			->from($db->qn('#__redshopb_newsletter'))
			->where('plugin = ' . $db->q('acymailing'))
			->where('id = ' . (int) $id);

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * on Redshopb Set Newsletter Info
	 *
	 * @param   object  $view  Values newsletter view
	 *
	 * @return boolean
	 */
	public function onRedshopbSetNewsletterInfo(&$view)
	{
		if (!$this->acyInstalled)
		{
			Factory::getApplication()->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_ACYMAILING_NOT_FOUND'), 'warning');

			return false;
		}

		$queueClass    = acymailing_get('class.queue');
		$mailId        = $this->getMailId($view->get('item')->id);
		$view->nbqueue = $queueClass->nbQueue($mailId);

		return true;
	}
}
