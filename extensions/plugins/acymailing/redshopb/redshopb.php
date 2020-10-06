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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;

Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
JLoader::import('redshopb.library');
JLoader::import('helpers.helper', JPATH_ADMINISTRATOR . '/components/com_acymailing');

/**
 * PlgAcyMailing_Redshopb class.
 *
 * @package  Redshopb.Plugin
 * @since    1.6.8
 */
class PlgAcyMailingRedshopb extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * When the user is created
	 *
	 * @param   mixed  $subscriber  Subscriber
	 *
	 * @return  void
	 */
	public function onAcyUserCreate($subscriber)
	{
	}

	/**
	 * When the user is modified
	 *
	 * @param   mixed  $subscriber  Subscriber
	 *
	 * @return  void
	 */
	public function onAcyUserModify($subscriber)
	{
	}

	/**
	 * When the user subscribes to a list
	 *
	 * @param   int    $subid    Subscriber ID
	 * @param   mixed  $listids  List of IDs
	 *
	 * @return  void
	 */
	public function onAcySubscribe($subid, $listids)
	{
	}

	/**
	 * When the user unsubscribes from a list
	 *
	 * @param   int    $subid    Subscriber ID
	 * @param   mixed  $listids  List of IDs
	 *
	 * @return  void
	 */
	public function onAcyUnsubscribe($subid, $listids)
	{
	}

	/**
	 * When a Newsletter is added to the queue
	 *
	 * @param   int  $mailid  Mail ID
	 *
	 * @return  void
	 */
	public function onAcySendNewsletter($mailid)
	{
	}

	/**
	 * When a list is created
	 *
	 * @param   mixed  $list  List
	 *
	 * @return  void
	 */
	public function onAcyBeforeListCreate($list)
	{
	}

	/**
	 * When a list is modified
	 *
	 * @param   mixed  $list  List
	 *
	 * @return  void
	 */
	public function onAcyBeforeListModify($list)
	{
	}

	/**
	 * When a list is deleted
	 *
	 * @param   mixed  $elements  Elements
	 *
	 * @return  void
	 */
	public function onAcyBeforeListDelete($elements)
	{
	}

	/**
	 * When the user clicks on a link
	 *
	 * @param   int  $subid   Subscriber ID
	 * @param   int  $mailid  Mail ID
	 * @param   int  $urlid   URL ID
	 *
	 * @return  void
	 */
	public function onAcyClickLink($subid, $mailid, $urlid)
	{
	}

	/**
	 * Cron trigger, execute once per day
	 *
	 * @return  void
	 */
	public function onAcyCronTrigger()
	{
		$config = acymailing_config();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select(
				array(
					$db->qn('rbnl.id', 'newsletter_id'),
					$db->qn('rbu.id', 'user_id'),
					$db->qn('rbnus.newsletter_id', 'exists'),
					$db->qn('acus.senddate', 'send_date'),
					$db->qn('acus.opendate', 'open_date'),
					'acus.html, acus.sent, acus.open, acus.bounce, acus.fail, acus.ip'
				)
			)
			->from($db->qn('#__acymailing_userstats', 'acus'))
			->innerJoin($db->qn('#__redshopb_newsletter', 'rbnl') . ' ON rbnl.plugin = ' . $db->q('acymailing') . ' AND rbnl.plugin_id = acus.mailid')
			->innerJoin($db->qn('#__acymailing_subscriber', 'ams') . ' ON acus.subid = ams.subid')
			->innerJoin($db->qn('#__redshopb_user', 'rbu') . ' ON rbu.joomla_user_id = ams.userid')
			->leftJoin($db->qn('#__redshopb_newsletter_user_stats', 'rbnus') . ' ON rbnus.newsletter_id = rbnl.id AND rbnus.user_id = rbu.id');

		$cronPluginsNext = (int) $config->get('cron_plugins_next', 0);

		if ($cronPluginsNext)
		{
			$query->where('acus.senddate >= ' . (int) ($cronPluginsNext - 86400));
		}

		$results = $db->setQuery($query)->loadObjectList();

		if ($results)
		{
			foreach ($results as $result)
			{
				if ($result->exists)
				{
					unset($result->exists);
					$db->updateObject('#__redshopb_newsletter_user_stats', $result, array('newletter_id', 'user_id'));
				}
				else
				{
					$db->insertObject('#__redshopb_newsletter_user_stats', $result);
				}
			}
		}
	}
}
