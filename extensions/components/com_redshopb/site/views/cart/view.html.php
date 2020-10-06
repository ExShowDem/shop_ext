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

/**
 * Saved Cart View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.6.71
 */
class RedshopbViewCart extends RedshopbView
{
	/**
	 * @var  array
	 */
	public $item;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('Cart');
		$app   = Factory::getApplication();

		$this->item = $model->getItem();

		if (!$this->item->id)
		{
			$app->enqueueMessage('Could not found specific cart', 'error');
			$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=carts', false));
		}

		$cartEntity = RedshopbEntityCart::getInstance($this->item->id);

		if ($cartEntity->removeNotAvailableProducts())
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_SAVED_CART_WARNING_SOME_PRODUCTS_ARE_NOT_AVAILABLE'), 'warning');
		}

		$this->item->cartItems = $cartEntity->applyCartItemsPrices();

		RedshopbHelperCommon::initCartScript();

		parent::display($tpl);
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group  = new RToolbarButtonGroup;
		$group2 = new RToolbarButtonGroup;

		$placeOrderButton = RToolbarBuilder::createLinkButton(
			'#',
			Text::_('COM_REDSHOPB_SAVED_CARTS_CHECK_OUT'),
			'',
			'btn btn-default btn-checkout-saved-cart',
			'data-id="' . $this->item->id . '" data-form="adminForm"'
		);

		$closeButton = RToolbarBuilder::createLinkButton(
			RedshopbRoute::_('index.php?option=com_redshopb&view=carts', false), 'JTOOLBAR_CLOSE', 'icon-remove', 'btn-danger'
		);

		$group->addButton($placeOrderButton);
		$group2->addButton($closeButton);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);
		$toolbar->addGroup($group2);

		return $toolbar;
	}
}
