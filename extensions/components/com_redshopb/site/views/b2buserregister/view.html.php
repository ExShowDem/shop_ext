<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;

/**
 * Registration view class for B2B Users.
 *
 * @since  1.6
 */
class RedshopbViewB2BUserRegister extends HtmlView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  CMSObject
	 */
	protected $item;

	/**
	 * @var  RedshopbEntityCompany
	 */
	public $company;

	/**
	 * @var string
	 */
	public $defaultOpen = 'login';

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  The template file to include
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$user      = Factory::getUser();
		$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

		if ($rsbUserId)
		{
			Factory::getApplication()->redirect(RedshopbRoute::_('index.php?option=com_redshopb', false));
		}

		$this->company = RedshopbEntityCompany::load($this->form->getValue('company_id'));
		$app           = Factory::getApplication();
		$menu          = $app->getMenu()->getActive();

		if (null !== $menu)
		{
			$this->defaultOpen = $menu->params->get('default_open', $this->defaultOpen);
		}

		$this->defaultOpen = $app->input->getCmd('active', $this->defaultOpen);

		return parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_B2B_USER_REGISTER_FORM_TITLE');
	}
}
