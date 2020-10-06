<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Newsletter View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewNewsletter extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var array|false
	 */
	public $subscribers;

	/**
	 * @var mixed
	 */
	public $alreadySent;

	/**
	 * @var mixed
	 */
	public $nbqueue;

	/**
	 * @var mixed
	 */
	public $hours;

	/**
	 * @var mixed
	 */
	public $minutes;

	/**
	 * @var mixed
	 */
	public $calendar;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app      = Factory::getApplication();
		$recordId = $app->input->getInt('id');

		if ($app->input->getCmd('layout', 'edit') == 'edit')
		{
			$this->setLayout($app->getUserState('newsletter.layout.' . $recordId, 'edit'));
		}

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		if (!PluginHelper::isEnabled('redshopb_newsletter'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_NEWSLETTER_MAIL_ENGINE_NOT_FOUND'), 'warning');
		}

		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('redshopb_newsletter');
		$dispatcher->trigger('onRedshopbSetNewsletterInfo', array(&$this));

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_NEWSLETTER_FORM');
		$state = $isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$layout    = $this->getLayout();
		$toolbar   = new RToolbar;
		$group     = new RToolbarButtonGroup;
		$mailGroup = new RToolbarButtonGroup;

		switch ($layout)
		{
			case 'preview':
				$scheduleSend = RToolbarBuilder::createModalButton(
					'newletterScheduleModal', 'COM_REDSHOPB_NEWSLETTER_SCHEDULE_BUTTON', '', 'icon-time', false,
					array(
						'url' => 'index.php?option=com_redshopb&view=newsletter&layout=scheduleconfirm&tmpl=component&id=' . $this->item->id
					)
				);
				$send         = RToolbarBuilder::createModalButton(
					'newletterSendModal', 'COM_REDSHOPB_NEWSLETTER_SEND_BUTTON', '', 'icon-envelope', false,
					array(
						'url' => 'index.php?option=com_redshopb&view=newsletter&layout=sendready&tmpl=component&id=' . $this->item->id
					)
				);
				$edit         = RToolbarBuilder::createStandardButton('newsletter.cancelSend', 'JEDIT', 'btn-info', 'icon-backward', false);
				$cancel       = RToolbarBuilder::createCloseButton('newsletter.cancel');
				$group->addButton($scheduleSend)
					->addButton($send)
					->addButton($cancel);
				$mailGroup->addButton($edit);
				$toolbar->addGroup($group)
					->addGroup($mailGroup);
				break;
			case 'scheduleconfirm':
				$listHours    = array();
				$listMinutess = array();

				for ($i = 0; $i < 24; $i++)
				{
					$listHours[] = HTMLHelper::_('select.option', $i, ($i < 10 ? '0' . $i : $i));
				}

				$time         = time();
				$defaultHours = floor(HTMLHelper::_('date', $time, 'm', false) / 5) * 5;
				$this->hours  = HTMLHelper::_('select.genericlist',   $listHours, 'sendhours', 'style="width:50px;"', 'value', 'text', $defaultHours);

				for ($i = 0; $i < 60; $i += 5)
				{
					$listMinutess[] = HTMLHelper::_('select.option', $i, ($i < 10 ? '0' . $i : $i));
				}

				$defaultMinutes    = HTMLHelper::_('date', $time, 'H', false);
				$this->minutes     = HTMLHelper::_(
					'select.genericlist',   $listMinutess, 'sendminutes', 'style="width:50px;"', 'value', 'text', $defaultMinutes
				);
				$this->subscribers = RedshopbHelperNewsletter_List::getSubscribers($this->item->newsletter_list_id);
				$this->calendar    = HTMLHelper::_(
					'calendar',
					HTMLHelper::_('date', $time, 'Y-m-d', false),
					'senddate',
					'senddate',
					'%Y-%m-%d',
					array('style' => 'width:80px'
					)
				);
				break;
			case 'sendready':
				$this->subscribers = RedshopbHelperNewsletter_List::getSubscribers($this->item->newsletter_list_id);

				if (empty($this->nbqueue))
				{
					$this->alreadySent = RedshopbHelperNewsletter_List::getNewsletterStatsCount($this->item->id);
				}
				break;
			default:
				$save         = RToolbarBuilder::createSaveButton('newsletter.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('newsletter.save');
				$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('newsletter.save2new');

				$group->addButton($save)
					->addButton($saveAndClose)
					->addButton($saveAndNew);

				if (empty($this->item->id))
				{
					$cancel = RToolbarBuilder::createCancelButton('newsletter.cancel');
				}
				else
				{
					$cancel = RToolbarBuilder::createCloseButton('newsletter.cancel');
				}

				$group->addButton($cancel);

				$previewSend = RToolbarBuilder::createStandardButton(
					'newsletter.preview',
					'COM_REDSHOPB_NEWSLETTER_PREVIEW_SEND_BUTTON',
					'btn-info',
					'icon-search',
					false
				);
				$mailGroup->addButton($previewSend);

				$toolbar->addGroup($group)
					->addGroup($mailGroup);
		}

		return $toolbar;
	}
}
