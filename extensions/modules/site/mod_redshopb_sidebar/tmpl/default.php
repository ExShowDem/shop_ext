<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_sidebar
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

$types = $helper->getTypes();

$form   = Factory::getApplication()->input->get('jform', array(), 'array');
$userId = null;

if (isset($form['client_user_id']))
{
	$userId = (int) $form['client_user_id'];
}
else
{
	$userId = Factory::getApplication()->getUserStateFromRequest('com_redshop.client_user_id', 'client_user_id', $userId);
}

$active = Factory::getApplication()->input->get('view');

$rendererSidebar = $helper->rendererSidebar($types, $active);
echo $rendererSidebar['html'];
