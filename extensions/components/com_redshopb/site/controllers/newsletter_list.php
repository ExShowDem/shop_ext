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

/**
 * Newsletter list Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.6.17
 */
class RedshopbControllerNewsletter_List extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_NEWSLETTER_LIST';

	/**
	 * Method for load "Recipients" tab data
	 *
	 * @return  void
	 */
	public function ajaxLoadSegmentation()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$newsletterListId = $this->input->getInt('id', 0);
		$builderValue     = '';
		$table            = $this->getModel()->getTable();

		if ($newsletterListId && $table->load($newsletterListId))
		{
			$builderValue = $table->segmentation_json;
		}

		// Load subscribers list
		$subscribers = RedshopbHelperNewsletter_List::getSubscribers($newsletterListId);

		$layoutData = array(
			'newsletterListId' => $newsletterListId,
			'subscribers'      => $subscribers,
			'builderValue'     => $builderValue
		);

		echo RedshopbLayoutHelper::render('newsletter_list.recipients', $layoutData);

		Factory::getApplication()->close();
	}

	/**
	 * Method for update segmentation query for newsletter list
	 *
	 * @return  void
	 */
	public function ajaxUpdateSegmentationQuery()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$criteria         = $this->input->getString('criteria', '');
		$criteriaJSON     = $this->input->getString('criteria_json', '');
		$newsletterListId = $this->input->getInt('id', 0);
		$model            = $this->getModel();

		echo $model->updateSegmentationQuery($newsletterListId, $criteria, $criteriaJSON);

		$app->close();
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

		$newsletterListId = $this->input->getInt('id', 0);
		$fixedMode        = $this->input->getInt('fixed', null);

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
		$model = RModelAdmin::getInstance('Users', 'RedshopbModel', array('context' => 'newsletter_list.recipients'));
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
			'parentId'         => $newsletterListId,
			'view'             => 'newsletter_list',
			'state'            => $state,
			'items'            => $subscribers,
			'pagination'       => $pagination,
			'filterForm'       => $model->getForm(),
			'activeFilters'    => $model->getActiveFilters(),
			'formName'         => $formName,
			'showToolbar'      => false,
			'action'           => RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter_list&model=users'),
			'return'           => base64_encode('index.php?option=com_redshopb&view=newsletter_list&layout=edit&id='
				. $newsletterListId . '&tab=recipients&from_newsletter_list=1'
			)
			)
		);

		$app->close();
	}

	/**
	 * Method for update an subscriber.
	 *
	 * @return   void
	 */
	public function ajaxUpdateFixed()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$newsletterListId = $this->input->getInt('id', 0);
		$fixedStatus      = $this->input->getInt('fixed', null);
		$userId           = $this->input->getInt('userId', 0);

		$model = $this->getModel();

		if ($model->storeSubscribers($newsletterListId, $userId, $fixedStatus))
		{
			$newStatus = (boolean) $fixedStatus;
			$newStatus = !$newStatus;
			echo (int) $newStatus;
		}

		Factory::getApplication()->close();
	}
}
