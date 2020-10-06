<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Companies Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCompanies extends RedshopbControllerAdmin
{
	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  boolean  False on failure or error, true on success.
	 *
	 * @since   1.9.14
	 */
	public function rebuild()
	{
		$this->setRedirect($this->getRedirectToListRoute());

		if (!RedshopbHelperUser::isRoot())
		{
			return false;
		}

		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(Text::_('COM_REDSHOPB_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(Text::sprintf('COM_REDSHOPB_REBUILD_FAILED'), 'error');

			return false;
		}
	}
}
