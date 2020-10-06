<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Department Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldWashCareSpec extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'WashCareSpec';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$items   = $this->getWashCareSpecs();
		$groups  = array();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if (isset($groups[$item->type_code]))
				{
					$groups[$item->type_code][] = $item;
				}
				else
				{
					$groups[$item->type_code] = array($item);
				}
			}

			foreach ($groups as $group => $washcarespecs)
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					$group
				);

				foreach ($washcarespecs as $wcs)
				{
					if (isset($wcs->description))
					{
						if (count($wcs->description) > 200)
						{
							$value = substr($wcs->description, 0, 200) . '...';
						}
						else
						{
							$value = $wcs->description;
						}
					}
					else
					{
						$value = $wcs->code;
					}

					$options[] = HTMLHelper::_(
						'select.option',
						$wcs->id,
						$value
					);
				}

				$options[] = HTMLHelper::_(
					'select.optgroup',
					$group
				);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Get wash and care specs.
	 *
	 * @return array
	 */
	private function getWashCareSpecs()
	{
		if (empty($this->cache))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->qn('#__redshopb_wash_care_spec'))
				->order($db->qn('type_code'));

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
