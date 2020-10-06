<?php
	/**
	 * @package     Aesir.E-Commerce.Frontend
	 * @subpackage  Models
	 *
	 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
	 * @license     GNU General Public License version 2 or later, see LICENSE.
	 */

defined('_JEXEC') or die;

/**
 * Newsletter Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelNewsletter extends RedshopbModelAdmin
{
	/**
	 * Unpublish a newsletter
	 *
	 * @param   integer  $newsletterId  The newsletter id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function unpublish($newsletterId)
	{
		$newsletterTable = $this->getTable();

		if (!$newsletterTable->load($newsletterId))
		{
			return false;
		}
		else
		{
			$newsletterTable->id    = $newsletterId;
			$newsletterTable->state = 0;

			if (!$newsletterTable->store())
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}
}
