<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Email helper.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 * @since       1.6.63
 */
class RedshopbHelperEmail
{
	/**
	 * Send Offer
	 *
	 * @param   integer  $offerId  Offer Id
	 *
	 * @return  boolean
	 */
	public static function sendOfferEmail($offerId)
	{
		$db  = Factory::getDbo();
		$app = Factory::getApplication();

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->where('pi.id = piavx.product_item_id')
			->order('pav.ordering');

		$query = $db->getQuery(true)
			->select('oix.*')
			->select('CONCAT_WS(' . $db->q('-') . ', p.sku, (' . $subQuery . ')) AS sku')
			->select($db->qn('p.name', 'product_name'))
			->from($db->qn('#__redshopb_offer_item_xref', 'oix'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = oix.product_id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = oix.product_item_id')
			->where('oix.offer_id = ' . (int) $offerId);

		$offerItems = $db->setQuery($query)
			->loadObjectList();

		if (!count($offerItems))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_OFFER_ADD_PRODUCTS_IN_CART_FIRST'), 'error');

			return false;
		}

		$offer = RedshopbTable::getAdminInstance('Offer');
		$offer->load($offerId);

		$query->clear()
			->select('ju.email, CONCAT_WS(' . $db->q(' ') . ', ru.name1, ru.name2) AS name, ru.use_company_email, ru.send_email')
			->from($db->qn('#__redshopb_user', 'ru'))
			->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id')
			->leftJoin(
				$db->qn('#__redshopb_offer', 'off') . ' ON CASE ' . $db->qn('off.customer_type')
				. ' WHEN ' . $db->q('company') . ' THEN ' . $db->qn('off.company_id') . ' = ' . $db->qn('umc.company_id')
				. ' WHEN ' . $db->q('department') . ' THEN ' . $db->qn('off.department_id') . ' = ' . $db->qn('ru.department_id')
				. ' WHEN ' . $db->q('employee') . ' THEN ' . $db->qn('off.user_id') . ' = ' . $db->qn('ru.id')
				. ' ELSE FALSE END'
			)
			->where('CASE off.customer_type WHEN ' . $db->q('company') . ' THEN rt.type = ' . $db->q('admin') . ' '
				. 'WHEN ' . $db->q('department') . ' THEN rt.type = ' . $db->q('hod') . ' '
				. 'WHEN ' . $db->q('employee') . ' THEN TRUE ELSE FALSE END'
			)
			->where('off.id = ' . (int) $offerId);

		$users = $db->setQuery($query)
			->loadObjectList();

		if ($users)
		{
			$templateData = RedshopbHelperTemplate::renderTemplate(
				'offer', 'email', $offer->get('template_id'), compact(array_keys(get_defined_vars())), '', null, true
			);

			if ($templateData)
			{
				$sender = self::getSenderInfo();
				$body   = self::fixImagesPaths($templateData->content);
				$isSent = false;

				foreach ($users as $user)
				{
					if ($user->use_company_email == 1 || $user->send_email == 0)
					{
						$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_OFFER_SENT_NOT_ALLOW_FOR_USER', $user->name), 'warning');
					}
					elseif (!RFactory::getMailer()->sendMail(
						$sender->sender, $sender->fromName, $user->email, $templateData->params->get('mail_subject'), $body, 1
					))
					{
						$app->enqueueMessage(Text::_('COM_REDSHOPB_OFFER_SENT_FAIL'), 'error');

						return false;
					}
					else
					{
						$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_OFFER_SENT_FOR_USER', $user->name));
						$isSent = true;
					}
				}

				if ($isSent)
				{
					$data = array(
						'status' => 'sent'
					);

					if (!$offer->save($data))
					{
						$app->enqueueMessage($offer->getError(), 'error');

						return false;
					}

					return true;
				}
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_OFFER_SENT_FAIL_NOT_FOUND_USERS'), 'error');

			return false;
		}

		return false;
	}

	/**
	 * send email for requested offer
	 *
	 * @param   integer  $offerId  offer id
	 *
	 * @return  boolean
	 */
	public static function sendRequestedOfferEmail($offerId)
	{
		if (!$offerId)
		{
			return false;
		}

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_offer'))
			->where('id = ' . (int) $offerId);
		$result = $db->setQuery($query)->loadObject();

		if (!empty($result->company_id))
		{
			$customerName = RedshopbEntityCompany::getInstance((int) $result->company_id)->get('name');

			// Get company admins
			$query  = $db->getQuery(true)
				->select(
					array(
						$db->qn('ju1.email', 'email'),
						$db->qn('rt.type', 'userType'),
						$db->qn('c.id', 'company')
					)
				)
				->from($db->qn('#__redshopb_user', 'ru1'))
				->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru1.id')
				->innerJoin($db->qn('#__users', 'ju1') . ' ON ' . $db->qn('ru1.joomla_user_id') . ' = ' . $db->qn('ju1.id'))
				->innerJoin($db->qn('#__user_usergroup_map', 'ug1') . ' ON ' . $db->qn('ju1.id') . ' = ' . $db->qn('ug1.user_id'))
				->innerJoin($db->qn('#__redshopb_role', 'r1') . ' ON ' . $db->qn('r1.joomla_group_id') . ' = ' . $db->qn('ug1.group_id'))
				->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r1.role_type_id') . ' = ' . $db->qn('rt.id'))
				->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('umc.company_id'))
				->where($db->qn('rt.type') . ' = ' . $db->q('admin'))
				->where($db->qn('c.id') . ' = ' . $result->vendor_id)
				->where('ru1.use_company_email = 0')
				->where('ru1.send_email = 1')
				->where($db->qn('c.send_mail_on_order') . ' = 1');
			$users  = $db->setQuery($query)->loadObjectList();
			$sender = self::getSenderInfo();

			if (!empty($users))
			{
				$mailer = RFactory::getMailer();
				$mailer->setSender($sender->sender);
				$recipients = array();

				foreach ($users as $user)
				{
					$recipients[] = $user->email;
				}

				$body = '<div>';

				$subject = Text::_('COM_REDSHOPB_OFFER_MAIL_REQUESTED_SUBJECT');

				$body .= sprintf(
					Text::_('COM_REDSHOPB_OFFER_MAIL_REQUESTED_BODY'),
					$customerName,
					$result->requested_date
				);

				$body .= '</div>';
				$body  = self::fixImagesPaths($body);

				$mailer->addRecipient($recipients);

				$mailer->isHtml(true);
				$mailer->Encoding = 'base64';
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$send = $mailer->Send();

				if ($send !== true)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method for send email to notify Admin about new company registration
	 *
	 * @param   int  $companyId  ID of company
	 *
	 * @return  boolean         True if success. False otherwise.
	 */
	public static function sendCompanyRegisterNotification($companyId = 0)
	{
		if (!$companyId)
		{
			return false;
		}

		$company = RedshopbEntityCompany::getInstance($companyId);

		if (!$company->loadItem())
		{
			return false;
		}

		$mailer    = RFactory::getMailer();
		$sender    = self::getSenderInfo();
		$recipient = Factory::getConfig()->get('mailfrom');

		$subject = Text::_('COM_REDSHOPB_MAIL_COMPANY_REGISTER_SUBJECT');

		$body = '<div>';

		$autoActivation = (bool) RedshopbEntityConfig::getInstance()->get('autoactivate_b2b_company', false);

		$body .= Text::sprintf(
			$autoActivation ? 'COM_REDSHOPB_MAIL_COMPANY_REGISTER_BODY_AUTO_ACTIVATED' : 'COM_REDSHOPB_MAIL_COMPANY_REGISTER_BODY',
			$company->get('name'),
			rtrim(Uri::root(), '/') . RedshopbRoute::_('index.php?option=com_redshopb&task=company.edit&id=' . $companyId, false)
		);

		$body .= '</div>';

		$mailer->setSender($sender->sender);
		$mailer->addRecipient(array($recipient));

		$mailer->isHtml(true);
		$mailer->Encoding = 'base64';
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$send = $mailer->Send();

		if ($send !== true)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get sender info
	 *
	 * @return stdClass
	 */
	public static function getSenderInfo()
	{
		$result           = new stdClass;
		$joomlaConfig     = Factory::getConfig();
		$componentParams  = RedshopbApp::getConfig();
		$result->sender   = $componentParams->get('mailfrom', $joomlaConfig->get('mailfrom', ''));
		$result->fromName = $joomlaConfig->get('fromname', '');

		if ($result->sender == '')
		{
			$uri            = Uri::getInstance();
			$host           = $uri->getHost();
			$result->sender = $joomlaConfig->get('mailfrom', 'no-reply@' . $host);
		}

		return $result;
	}

	/**
	 * Use absolute paths instead of relative ones when linking images
	 *
	 * @param   string  $message  Text message
	 *
	 * @return  string
	 */
	public static function fixImagesPaths($message)
	{
		$url         = Uri::getInstance()->root();
		$imagesArray = array();

		preg_match_all("/\< *[img][^\>]*[.]*\>/i", $message, $matches);

		foreach ($matches[0] as $match)
		{
			preg_match_all("/(src|height|width)*= *[\"\']{0,1}([^\"\'\ \>]*)/i", $match, $m);
			$imagescur     = array_combine($m[1], $m[2]);
			$imagesArray[] = $imagescur['src'];
		}

		$imagesArray = array_unique($imagesArray);

		if (count($imagesArray))
		{
			foreach ($imagesArray as $change)
			{
				if (strpos($change, 'http') === false)
				{
					$message = str_replace($change, $url . $change, $message);
				}
			}
		}

		return $message;
	}
}
