<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

/**
 * Newsletter Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.6
 */
class RedshopbControllerNewsletter extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_NEWSLETTER';

	/**
	 * Preview task
	 *
	 * @return boolean
	 */
	public function preview()
	{
		if (!$this->save())
		{
			return false;
		}

		$this->setMessage(null, 'message');
		$urlVar   = 'id';
		$app      = Factory::getApplication();
		$recordId = $app->input->getInt($urlVar);
		$app->setUserState('newsletter.layout.' . $recordId, 'preview');

		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
		);

		return true;
	}

	/**
	 * Cancel send task
	 *
	 * @return   boolean
	 */
	public function cancelSend()
	{
		$urlVar   = 'id';
		$app      = Factory::getApplication();
		$recordId = $app->input->getInt($urlVar);
		$app->setUserState('newsletter.layout.' . $recordId, 'edit');

		$this->setRedirect(
			'index.php?option=com_redshopb&task=newsletter.edit&id=' . $recordId
		);

		return true;
	}

	/**
	 * Method for get Subscribers table
	 *
	 * @return  void
	 */
	public function ajaxLoadSubscribers()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$newsletterListId = $this->input->getInt('newsletter_list_id', 0);
		$fixedMode        = $this->input->getInt('fixed', null);
		$id               = $this->input->getInt('id', 0);

		if (!$newsletterListId)
		{
			$app->close();
		}

		$subscribers   = RedshopbHelperNewsletter_List::getSubscribers($newsletterListId, $fixedMode);
		$subscriberIds = array(0);

		if (!empty($subscribers))
		{
			foreach ($subscribers as $subscriber)
			{
				$subscriberIds[] = $subscriber;
			}
		}

		/** @var RedshopbModelUsers $usersModel */
		$model = RModelAdmin::getInstance('Users', 'RedshopbModel');
		$model->set('filterFormName', 'filter_users_newsletter_list');
		$state = $model->getState();
		$model->setState('filter.ids', $subscriberIds);

		$formName   = 'usersForm';
		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		// Get Fixed status for subscribers
		$subscribers = $model->getItems();

		if (!empty($subscribers))
		{
			foreach ($subscribers as &$subscriber)
			{
				$subscriber->fixed = RedshopbHelperNewsletter_List::getSubscriberFixedStatus($newsletterListId, $subscriber->id);
			}
		}

		echo RedshopbLayoutHelper::render('newsletter_list.users', array(
				'newsletterListId' => $newsletterListId,
				'parentId' => $id,
				'view' => 'newsletter',
				'state' => $state,
				'items' => $subscribers,
				'pagination' => $pagination,
				'filterForm' => $model->getForm(),
				'activeFilters' => $model->getActiveFilters(),
				'formName' => $formName,
				'showToolbar' => false,
				'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter&model=users'),
				'return' => base64_encode('index.php?option=com_redshopb&view=newsletter&layout=edit&id='
					. $id . '&tab=recipients&from_newsletter=1'
				)
			)
		);

		$app->close();
	}

	/**
	 * Save newsletters. Check mailing plugin
	 *
	 * @return mixed
	 */
	public function save()
	{
		// Check acyba.com/acymailing installed or not
		if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php')
			// Check Aesir E-Commerce Acymailing integration plugin Type redshopb_newsletter Element acymailing enabled or not
			|| !PluginHelper::isEnabled('redshopb_newsletter', 'acymailing')
		)
		{
			Factory::getApplication()->enqueueMessage(Text::_('PLG_REDSHOPB_NEWSLETTER_ACYMAILING_NOT_FOUND'), 'warning');

			$this->setRedirect($this->getRedirectToListRoute());

			return false;
		}
		else
		{
			return parent::save();
		}
	}

	/**
	 * Send newsletters
	 *
	 * @return boolean
	 */
	public function send()
	{
		Session::checkToken('request') or jexit(Text::_('JINVALID_TOKEN'));

		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('redshopb_newsletter');
		$recordId = $this->input->getInt('id');

		try
		{
			// Trigger the onRedshopbSendNewsletter event.
			$dispatcher->trigger('onRedshopbSendNewsletter', array($recordId));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$recordId = $this->input->getInt('id');
		$this->setRedirect(
			'index.php?option=com_redshopb&view=newsletter&layout=sendready&subtask=finish&tmpl=component&id=' . $recordId
		);

		return true;
	}
}
