<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Table Locks Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerTable_Locks extends RedshopbControllerAdmin
{
	/**
	 * Bulk delete all locks from filter selection
	 *
	 * @return  void
	 */
	public function bulkDelete()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		/** @var RedshopbModelTable_Locks $model */
		$model = $this->getModel('Table_Locks', 'RedshopbModel', array('ignore_request' => false));
		$model->getState();

		$model->setState('list.limit', 0);

		$items   = $model->getItems();
		$lockIds = array();

		foreach ($items as $item)
		{
			$lockIds[] = $item->id;
		}

		if ($model->delete($lockIds))
		{
			$this->setMessage(Text::sprintf('COM_REDSHOPB_TABLE_LOCK_BULK_DELETE_MESSAGE', count($items)));
		}
		else
		{
			$this->setMessage($model->getError(), 'error');
		}

		$this->setRedirect($this->getRedirectToListRoute());
	}
}
