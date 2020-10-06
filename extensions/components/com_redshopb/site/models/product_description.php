<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
/**
 * Product Description Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Description extends RedshopbModelAdmin
{
	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$data = parent::validate($form, $data, $group);

		if (!$data)
		{
			return false;
		}

		if (isset($data['main_attribute_value_id']) && is_numeric($data['main_attribute_value_id']) && $data['main_attribute_value_id'] > 0)
		{
			// Checks that the main attribute value id given is part of the given product id
			$productAttributeValuesModel = RedshopbModelAdmin::getFrontInstance('Product_Attribute_Values');
			$productAttributeValuesModel->getState();

			$productAttributeValuesModel->setState('filter.id', $data['main_attribute_value_id']);
			$productAttributeValuesModel->setState('filter.product_id', $data['product_id']);

			if (!$productAttributeValuesModel->getItems())
			{
				Factory::getApplication()->enqueueMessage('COM_REDSHOPB_PRODUCT_DESCRIPTION_ERROR_ATTRIBUTE_VALUE', 'error');

				return false;
			}
		}

		// Checks for duplicate records (product_id - main_attribute_value_id)
		$productDescriptionsModel = RedshopbModelAdmin::getFrontInstance('Product_Descriptions');
		$productDescriptionsModel->getState();

		if (isset($data['id']) && is_numeric($data['id']))
		{
			$productDescriptionsModel->setState('filter.not_id', $data['id']);
		}

		$productDescriptionsModel->setState('filter.product_id', $data['product_id']);

		if (isset($data['main_attribute_value_id']) && is_numeric($data['main_attribute_value_id']) && $data['main_attribute_value_id'] > 0)
		{
			$productDescriptionsModel->setState('filter.main_attribute_value_id', $data['main_attribute_value_id']);
		}
		else
		{
			$productDescriptionsModel->setState('filter.main_attribute_value_id', 'null');
		}

		if ($productDescriptionsModel->getItems())
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_ERROR_DUPLICATE'), 'error');

			return false;
		}

		return $data;
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateWS($data)
	{
		$data = parent::validateWS($data);

		if (!$data)
		{
			return false;
		}

		if (strpos($data['description'], '<hr id="system-readmore" />') !== false)
		{
			$descriptions              = explode('<hr id="system-readmore" />', $data['description']);
			$data['description_intro'] = trim($descriptions[0]);
			$data['description']       = trim($descriptions[1]);
		}

		if (!empty($data['description']) && !preg_match('/^<(.*)>$/s', $data['description']))
		{
			$data['description'] = '<p>' . $data['description'] . '</p>';
		}

		// Adds automatic paragraphs to the HTML fields
		if (!empty($data['description_intro']) && !preg_match('/^<(.*)>$/s', $data['description_intro']))
		{
			$data['description_intro'] = '<p>' . $data['description_intro'] . '</p>';
		}

		// We actually don't use the description intro in the table
		// because in the form we use the content before the read more tag as the intro
		if (!empty($data['description_intro']))
		{
			$data['description'] = $data['description_intro'] . '<hr id="system-readmore" />' . $data['description'];
		}

		// Optional enrichment
		$syncReference = RedshopbHelperSync::getEnrichmentBase($this);

		if ($syncReference != '')
		{
			if (isset($data['related_id']))
			{
				// Leaves related id all the same if it's not sent
				if (isset($data['id']) && $data['id'] > 0 && $data['related_id'] == '')
				{
					$sync                    = new RedshopbHelperSync;
					$data['sync_related_id'] = $sync->findSyncedLocalId($syncReference, $data['id']);
				}
				else
				{
					$data['sync_related_id'] = $data['related_id'];
					unset($data['related_id']);
				}

				if ($data['sync_related_id'] == 'null')
				{
					$data['sync_related_id'] = '';
				}
			}
			else
			{
				$sync                    = new RedshopbHelperSync;
				$data['sync_related_id'] = $sync->findSyncedLocalId($syncReference, $data['id']);
			}
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$return = parent::save($data);

		if ($return)
		{
			// Saves the optional sync related id
			if (!$this->saveSyncRelatedId($data))
			{
				return false;
			}
		}

		return $return;
	}
}
