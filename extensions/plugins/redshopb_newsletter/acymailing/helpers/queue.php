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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;


if (!class_exists('AcyqueueHelper'))
{
	include ACYMAILING_HELPER . 'queue.php';
}

/**
 * Class RedshopbAcyQueue
 *
 * @since  1.6.8
 */
class RedshopbAcyQueue extends AcyqueueHelper
{
	/**
	 * @var integer
	 */
	public $total;

	/**
	 * @var boolean
	 */
	public $finish;

	/**
	 * @var boolean
	 */
	public $mod_security2;

	/**
	 * @var integer
	 */
	public $nbprocess;

	/**
	 * @var integer
	 */
	public $consecutiveError;

	/**
	 * @var string
	 */
	public $messages;

	/**
	 * @var boolean
	 */
	public $report;

	/**
	 * process in redshopb
	 *
	 * @param   int  $id  Id newsletter in redshopb
	 *
	 * @return  boolean
	 */
	public function process($id = 0)
	{
		$queueClass    = acymailing_get('class.queue');
		$queueElements = $queueClass->getReady($this->send_limit, $this->mailid);

		if (empty($queueElements))
		{
			$this->finish = true;

			if ($this->report)
			{
				Factory::getApplication()->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_NO_PROCESS'), 'warning');
			}

			return true;
		}

		if ($this->report)
		{
			if (function_exists('apache_get_modules'))
			{
				$modules             = apache_get_modules();
				$this->mod_security2 = in_array('mod_security2', $modules);
			}

			@ini_set('output_buffering', 'off');
			@ini_set('zlib.output_compression', 0);

			if (!headers_sent())
			{
				while (ob_get_level() > 0 && $this->obend++ < 3)
				{
					@ob_end_flush();
				}
			}

			$disp  = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />';
			$disp .= '<title>' . Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_PROCESS') . '</title>';
			$disp .= '<style>body{font-size:12px;font-family: Arial,Helvetica,sans-serif;}</style></head><body>';
			$disp .= "<div style='position:fixed; top:3px;left:3px;background-color : white;border : 1px solid grey; padding : 3px;font-size:14px'>";
			$disp .= "<span id='divpauseinfo' ";
			$disp .= "style='padding:10px;margin:5px;font-size:16px;font-weight:bold;display:none;background-color:black;color:white;'> </span>";
			$disp .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_PROCESS') . ': <span id="counter" >' . $this->start . '</span> / ' . $this->total;
			$disp .= '</div>';
			$disp .= "<div id='divinfo' ";
			$disp .= "style='display:none;position:fixed;bottom:3px;left:3px;background-color:white;border:1px solid grey;padding:3px;'> </div>";
			$disp .= '<br /><br />';
			$url   = URI::base()
				. 'index.php?option=com_redshopb&view=newsletter&layout=sendready&tmpl=component&task=newsletter.send&subtask=continuesend&id='
				. $id . '&totalsend=' . $this->total . '&' . Session::getFormToken() . '=1&alreadysent=';
			$disp .= '<script type="text/javascript" language="javascript">';
			$disp .= 'var mycounter = document.getElementById("counter");';
			$disp .= 'var divinfo = document.getElementById("divinfo");
					var divpauseinfo = document.getElementById("divpauseinfo");
					function setInfo(message){ divinfo.style.display = \'block\';divinfo.innerHTML=message; }
					function setPauseInfo(nbpause){ divpauseinfo.style.display = \'\';divpauseinfo.innerHTML=nbpause;}
					function setCounter(val){ mycounter.innerHTML=val;}
					var scriptpause = ' . intval($this->pause) . ';
					function handlePause(){
						setPauseInfo(scriptpause);
						if(scriptpause > 0){
							scriptpause = scriptpause - 1;
							setTimeout(\'handlePause()\',1000);
						}else{
							document.location.href=\'' . $url . '\'+mycounter.innerHTML;
						}
					}
					</script>';
			echo $disp;

			if (function_exists('ob_flush'))
			{
				@ob_flush();
			}

			if (!$this->mod_security2)
			{
				@flush();
			}
		}

		$mailHelper         = acymailing_get('helper.mailer');
		$mailHelper->report = false;

		if ($this->config->get('smtp_keepalive', 1) || in_array($this->config->get('mailer_method'), array('elasticemail', 'smtp_com')))
		{
			$mailHelper->SMTPKeepAlive = true;
		}

		$queueDelete = array();
		$queueUpdate = array();
		$statsAdd    = array();

		$maxTry = (int) $this->config->get('queue_try', 0);

		$currentMail     = $this->start;
		$this->nbprocess = 0;

		if (count($queueElements) < $this->send_limit)
		{
			$this->finish = true;
		}

		foreach ($queueElements as $oneQueue)
		{
			$currentMail++;
			$this->nbprocess++;

			if ($this->report)
			{
				echo '<script type="text/javascript" language="javascript">setCounter(' . $currentMail . ')</script>';

				if (function_exists('ob_flush'))
				{
					@ob_flush();
				}

				if (!$this->mod_security2)
				{
					@flush();
				}
			}

			$result = $mailHelper->sendOne($oneQueue->mailid, $oneQueue);

			$queueDeleteOk = true;
			$otherMessage  = '';

			if ($result)
			{
				$this->successSend++;
				$this->consecutiveError                                        = 0;
				$queueDelete[$oneQueue->mailid][]                              = $oneQueue->subid;
				$statsAdd[$oneQueue->mailid][1][(int) $mailHelper->sendHTML][] = $oneQueue->subid;
				$queueDeleteOk                                                 = $this->_deleteQueue($queueDelete);
				$queueDelete                                                   = array();

				if ($this->nbprocess % 10 == 0)
				{
					$this->statsAdd($statsAdd);
					$this->_queueUpdate($queueUpdate);
					$statsAdd    = array();
					$queueUpdate = array();
				}
			}
			else
			{
				$this->errorSend++;
				$newtry = false;

				if (in_array($mailHelper->errorNumber, $mailHelper->errorNewTry))
				{
					if (empty($maxTry) || $oneQueue->try < $maxTry - 1)
					{
						$newtry       = true;
						$otherMessage = Text::sprintf('PLG_REDSHOPB_NEWSLETTER_QUEUE_NEXT_TRY', 60);
					}

					if ($mailHelper->errorNumber == 1)
					{
						$this->consecutiveError++;
					}

					if ($this->consecutiveError == 2)
					{
						sleep(1);
					}
				}

				if (!$newtry)
				{
					$queueDelete[$oneQueue->mailid][]                               = $oneQueue->subid;
					$statsAdd[$oneQueue->mailid][0][(int) @$mailHelper->sendHTML][] = $oneQueue->subid;

					if ($mailHelper->errorNumber == 1 && $this->config->get('bounce_action_maxtry'))
					{
						$queueDeleteOk = $this->_deleteQueue($queueDelete);
						$queueDelete   = array();
						$otherMessage .= $this->_subscriberAction($oneQueue->subid);
					}
				}
				else
				{
					$queueUpdate[$oneQueue->mailid][] = $oneQueue->subid;
				}
			}

			$messageOnScreen = $mailHelper->reportMessage;

			if (!empty($otherMessage))
			{
				$messageOnScreen .= ' => ' . $otherMessage;
			}

			$this->_display($messageOnScreen, $result, $currentMail);

			if (!$queueDeleteOk)
			{
				$this->finish = true;
				break;
			}

			if (!empty($this->stoptime) && $this->stoptime < time())
			{
				$this->_display(Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_REFRESH_TIMEOUT'));

				if ($this->nbprocess < count($queueElements))
				{
					$this->finish = false;
				}

				break;
			}

			if ($this->consecutiveError > 3 && $this->successSend > 3)
			{
				$this->_display(Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_REFRESH_CONNECTION'));
				break;
			}

			if ($this->consecutiveError > 5 || connection_aborted())
			{
				$this->finish = true;
				break;
			}
		}

		$this->_deleteQueue($queueDelete);
		$this->statsAdd($statsAdd);
		$this->_queueUpdate($queueUpdate);

		if ($mailHelper->SMTPKeepAlive)
		{
			$mailHelper->SmtpClose();
		}

		if (!empty($this->total) && $currentMail >= $this->total)
		{
			$this->finish = true;
		}

		if ($this->consecutiveError > 5)
		{
			$this->_handleError();

			return false;
		}

		if ($this->report && !$this->finish)
		{
			echo '<script type="text/javascript" language="javascript">handlePause();</script>';
		}

		if ($this->report)
		{
			echo "</body></html>";

			while ($this->obend-- > 0)
			{
				ob_start();
			}

			exit;
		}

		return true;
	}

	/**
	 * Stats Add
	 *
	 * @param   array  $statsAdd  Array stats info
	 *
	 * @return boolean
	 */
	public function statsAdd($statsAdd)
	{
		if (empty($statsAdd))
		{
			return true;
		}

		$time   = time();
		$subids = array();
		$db     = Factory::getDbo();

		foreach ($statsAdd as $mailid => $infos)
		{
			$mailid = intval($mailid);

			foreach ($infos as $status => $infosSub)
			{
				foreach ($infosSub as $html => $subscribers)
				{
					$query       = $db->getQuery(true)
						->select('subid')
						->from(acymailing_table('userstats'))
						->where('mailid = ' . $db->q($mailid))
						->where('subid IN (' . implode(',', $subscribers) . ')');
					$existSubIds = $db->setQuery($query)->loadColumn();

					foreach ($subscribers as $subscriber)
					{
						if (in_array($subscriber, $existSubIds))
						{
							continue;
						}

						$insertObject         = new stdClass;
						$insertObject->mailid = $mailid;
						$insertObject->subid  = $subscriber;
						$insertObject->html   = $html;
						$insertObject->sent   = 0;
						$db->insertObject(acymailing_table('userstats'), $insertObject);
					}

					$query = $db->getQuery(true)
						->update(acymailing_table('userstats'))
						->set('html = ' . $db->q($html))
						->set('senddate = ' . $db->q($time))
						->where('mailid = ' . $db->q($mailid))
						->where('subid IN (' . implode(',', $subscribers) . ')');

					if ($status)
					{
						$subids = array_merge($subids, $subscribers);
						$query->set('sent = sent + 1')
							->set('fail = 0');
					}
					else
					{
						$query->set('fail = fail +1');
					}

					$db->setQuery($query)->execute();

					$query                = $db->getQuery(true)
						->select($db->qn('rnl.id', 'newsletter_id'))
						->from($db->qn('#__redshopb_newsletter', 'rnl'))
						->where('rnl.plugin = ' . $db->q('acymailing'))
						->where('rnl.plugin_id = ' . $db->q($mailid));
					$redshopbNewsletterId = $db->setQuery($query)->loadResult();

					$query               = $db->getQuery(true)
						->select('rbu.id')
						->from($db->qn('#__redshopb_user', 'rbu'))
						->leftJoin($db->qn('#__acymailing_subscriber', 'acs') . ' ON rbu.joomla_user_id = acs.userid')
						->where('acs.subid IN (' . implode(',', $subscribers) . ')');
					$redshopbSubscribers = $db->setQuery($query)->loadColumn();

					$query            = $db->getQuery(true)
						->select('user_id')
						->from($db->qn('#__redshopb_newsletter_user_stats'))
						->where('newsletter_id = ' . $db->q($redshopbNewsletterId))
						->where('user_id IN (' . implode(',', $redshopbSubscribers) . ')');
					$existUserInStats = $db->setQuery($query)->loadColumn();

					foreach ($redshopbSubscribers as $redshopbSubscriber)
					{
						if (in_array($redshopbSubscriber, $existUserInStats))
						{
							continue;
						}

						$insertObject                = new stdClass;
						$insertObject->newsletter_id = $redshopbNewsletterId;
						$insertObject->user_id       = $redshopbSubscriber;
						$insertObject->html          = $html;
						$insertObject->sent          = 0;
						$db->insertObject('#__redshopb_newsletter_user_stats', $insertObject);
					}

					$query = $db->getQuery(true)
						->update($db->qn('#__redshopb_newsletter_user_stats'))
						->set('html = ' . $db->q($html))
						->set('send_date = ' . $db->q($time))
						->where('newsletter_id = ' . $db->q($redshopbNewsletterId))
						->where('user_id IN (' . implode(',', $redshopbSubscribers) . ')');

					if ($status)
					{
						$query->set('sent = sent + 1')
							->set('fail = 0');
					}
					else
					{
						$query->set('fail = fail +1');
					}

					$db->setQuery($query)->execute();
				}
			}

			$nbhtml = empty($infos[1][1]) ? 0 : count($infos[1][1]);
			$nbtext = empty($infos[1][0]) ? 0 : count($infos[1][0]);
			$nbfail = 0;

			if (!empty($infos[0][0]))
			{
				$nbfail += count($infos[0][0]);
			}

			if (!empty($infos[0][1]))
			{
				$nbfail += count($infos[0][1]);
			}

			$query = $db->getQuery(true)
				->update(acymailing_table('stats'))
				->set('senthtml = senthtml + ' . $db->q($nbhtml))
				->set('senttext = senttext + ' . $db->q($nbtext))
				->set('fail = fail + ' . $db->q($nbfail))
				->set('senddate = ' . $db->q($time))
				->where('mailid = ' . $db->q($mailid));
			$db->setQuery($query)->execute();

			if (!$db->getAffectedRows())
			{
				$db->setQuery($query)->execute();
				$insertObject           = new stdClass;
				$insertObject->mailid   = $mailid;
				$insertObject->senthtml = $nbhtml;
				$insertObject->senttext = $nbtext;
				$insertObject->fail     = $nbfail;
				$insertObject->senddate = $time;
				$db->insertObject(acymailing_table('stats'), $insertObject);
			}
		}

		if (!empty($subids))
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__acymailing_subscriber'))
				->set('lastsent_date = ' . $db->q($time))
				->where('subid IN (' . implode(',', $subids) . ')');
			$db->setQuery($query)->execute();
		}

		return true;
	}

	/**
	 * subscriber Action
	 *
	 * @param   int  $subid  Subscriber id
	 *
	 * @return string
	 */
	private function _subscriberAction($subid)
	{
		if ($this->config->get('bounce_action_maxtry') == 'delete')
		{
			$this->subClass->delete($subid);

			return Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_DELETED', $subid);
		}

		$listId = 0;

		if (in_array($this->config->get('bounce_action_maxtry'), array('sub', 'remove', 'unsub')))
		{
			$status = $this->subClass->getSubscriptionStatus($subid);
		}

		$message = '';

		switch ($this->config->get('bounce_action_maxtry'))
		{
			case 'sub' :
				$listId = $this->config->get('bounce_action_lists_maxtry');

				if (!empty($listId))
				{
					$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_SUBSCRIBED_TO', $subid, $listId);

					if (empty($status[$listId]))
					{
						$this->listsubClass->addSubscription($subid, array('1' => array($listId)));
					}
					elseif ($status[$listId]->status != 1)
					{
						$this->listsubClass->updateSubscription($subid, array('1' => array($listId)));
					}
				}
				break;
			case 'remove' :
				$unsubLists = array_diff(array_keys($status), array($listId));

				if (!empty($unsubLists))
				{
					$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_REMOVED_FROM_LISTS', $subid, implode(',', $unsubLists));
					$this->listsubClass->removeSubscription($subid, $unsubLists);
				}
				else
				{
					$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_NOT_SUBSCRIBED', $subid);
				}
				break;
			case 'unsub' :
				$unsubLists = array_diff(array_keys($status), array($listId));

				if (!empty($unsubLists))
				{
					$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_UNSUBSCRIBED_FROM_LISTS', $subid, implode(',', $unsubLists));
					$this->listsubClass->updateSubscription($subid, array('-1' => $unsubLists));
				}
				else
				{
					$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_NOT_SUBSCRIBED', $subid);
				}
				break;
			case 'delete' :
				$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_DELETED', $subid);
				$this->subClass->delete($subid);
				break;
			case 'block' :
				$message .= Text::sprintf('PLG_REDSHOPB_NEWSLETTER_USER_BLOCKED', $subid);
				$query    = $this->db->getQuery(true)
					->update('#__acymailing_subscriber')
					->set('enabled = 0')
					->where('subid = ' . (int) $subid);
				$this->db->setQuery($query)->execute();
				$query = $this->db->getQuery(true)
					->delete('#__acymailing_queue')
					->where('subid = ' . (int) $subid);
				$this->db->setQuery($query)->execute();
				break;
		}

		return $message;
	}

	/**
	 * queue Update
	 *
	 * @param   array  $queueUpdate  Array queue Update
	 *
	 * @return boolean
	 */
	private function _queueUpdate($queueUpdate)
	{
		if (empty($queueUpdate))
		{
			return true;
		}

		$delay = 3600;

		foreach ($queueUpdate as $mailid => $subscribers)
		{
			$query = $this->db->getQuery(true)
				->update(acymailing_table('queue'))
				->set('senddate = senddate + ' . (int) $delay)
				->set('try = try +1')
				->where('mailid = ' . (int) $mailid)
				->where('subid IN (' . implode(',', $subscribers) . ')');
			$this->db->setQuery($query);
			$this->db->execute();
		}

		return true;
	}

	/**
	 * handle Error
	 *
	 * @return  void
	 */
	private function _handleError()
	{
		$this->finish = true;
		$message      = Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_STOPED');
		$message     .= '<br/>';
		$message     .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_KEPT_ALL');
		$message     .= '<br/>';

		if ($this->report)
		{
			if (empty($this->successSend) && empty($this->start))
			{
				$message .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_CHECKONE');
				$message .= '<br/>';
				$message .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_ADVISE_LIMITATION');
			}
			else
			{
				$message .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_REFUSE');
				$message .= '<br/>';

				if (!acymailing_level(1))
				{
					$message .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_CONTINUE_COMMERCIAL');
				}
				else
				{
					$message .= Text::_('PLG_REDSHOPB_NEWSLETTER_SEND_CONTINUE_AUTO');
				}
			}
		}

		$this->_display($message);
	}

	/**
	 * delete Queue
	 *
	 * @param   array  $queueDelete  Array delete Queue
	 *
	 * @return boolean
	 */
	private function _deleteQueue($queueDelete)
	{
		if (empty($queueDelete))
		{
			return true;
		}

		$status = true;

		foreach ($queueDelete as $mailid => $subscribers)
		{
			$nbsub = count($subscribers);
			$query = $this->db->getQuery(true)
				->delete(acymailing_table('queue'))
				->where('mailid = ' . (int) $mailid)
				->where('subid IN (' . implode(',', $subscribers) . ')');
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				$status = false;
				$this->_display($this->db->getErrorNum . ' : ' . $this->db->getErrorMsg());
			}
			else
			{
				$nbdeleted = $this->db->getAffectedRows();

				if ($nbdeleted != $nbsub)
				{
					$status = false;
					$this->_display(Text::_('PLG_REDSHOPB_NEWSLETTER_QUEUE_DOUBLE'));
				}
			}
		}

		return $status;
	}

	/**
	 * display notices
	 *
	 * @param   string  $message  Message
	 * @param   string  $status   Status
	 * @param   string  $num      Style
	 *
	 * @return  void
	 */
	private function _display($message, $status = '', $num = '')
	{
		$this->messages[] = strip_tags($message);

		if (!$this->report)
		{
			return;
		}

		if (!empty($num))
		{
			$color = $status ? 'green' : 'red';
			echo '<br/>' . $num . ' : <font color="' . $color . '">' . $message . '</font>';
		}
		else
		{
			echo '<script type="text/javascript" language="javascript">setInfo(\'' . addslashes($message) . '\')</script>';
		}

		if (function_exists('ob_flush'))
		{
			@ob_flush();
		}

		if (!$this->mod_security2)
		{
			@flush();
		}
	}
}
