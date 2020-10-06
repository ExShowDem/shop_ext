<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * Type of attribute entity.
 *
 * @since  1.7
 */
class RedshopbEntityTemplate extends RedshopbEntity
{
	/**
	 * Decoded params in Registry format.
	 *
	 * @var  Registry;
	 */
	protected $decodedParams;

	/**
	 * Method for get correct params base on template name.
	 *
	 * @param   boolean  $checkOverrideTemplate  True for get params from current template..
	 *
	 * @return  Registry
	 */
	public function getParams($checkOverrideTemplate = false)
	{
		if (!$this->getId())
		{
			return new Registry;
		}

		if (null === $this->decodedParams)
		{
			$this->loadParams();
		}

		// If not check current template. Return result.
		if (false === $checkOverrideTemplate || $this->get('template_group') != 'email')
		{
			return $this->decodedParams;
		}

		// Try to get params base on current template
		$return = $this->decodedParams->get(Factory::getApplication()->getTemplate());

		if ($return)
		{
			return new Registry($return);
		}

		// Try to get param which is default.
		if (array_key_exists(0, $this->decodedParams->toArray()))
		{
			return new Registry($this->decodedParams->toArray()[0]);
		}

		return new Registry;
	}

	/**
	 * Method for load param with Registry encoded
	 *
	 * @return  void
	 */
	public function loadParams()
	{
		if (!$this->isLoaded())
		{
			$this->loadItem();
		}

		$this->decodedParams = new Registry($this->params);
	}
}
