<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Browser
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/**
 * Custom Browser history.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Browser
 * @since       1.0
 */
class RedshopbBrowserHistory extends RBrowserHistory
{
	/**
	 * Enqueue an element.
	 *
	 * @param   mixed    $uri            The uri to enqueue.
	 * @param   boolean  $duplicateLast  True to duplicate the last element if it's the same.
	 *
	 * @return  RBrowserHistory
	 */
	public function enqueue($uri, $duplicateLast)
	{
		$currentUri = $this->getCurrent();

		// We make sure that the uri is not appended if we have the same view and id
		if (!empty($currentUri))
		{
			$currentUri  = Uri::getInstance($currentUri);
			$currentView = $currentUri->getVar('view');
			$currentId   = $currentUri->getVar('id');

			$newUri  = Uri::getInstance($uri);
			$newView = $newUri->getVar('view');
			$newId   = $newUri->getVar('id');

			if ($currentView === $newView && $currentId === $newId)
			{
				return $this;
			}
		}

		return parent::enqueue($uri, $duplicateLast);
	}
}
