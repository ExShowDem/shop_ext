<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
/**
 * Wash and care spec Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWash_Care_Spec extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'mainwarehouse';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = '', $prefix = 'RedshopbTable', $config = array())
	{
		$name = (empty($name)) ? 'Wash_Care_Spec' : $name;

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$files = Factory::getApplication()->input->files->get('jform', array(), 'array');
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

		if (count($files) > 0 && isset($files['imageFileUpload']) && $files['imageFileUpload']['name'] && $pk > 0)
		{
			$file                                  = $files['imageFileUpload'];
			RedshopbHelperThumbnail::$displayError = false;

			if (!RedshopbHelperThumbnail::checkFileError($file['name'], $file['error'])
				|| !RedshopbHelperMedia::checkExtension($file['name'])
				|| !RedshopbHelperMedia::checkIsImage($file['tmp_name']))
			{
				$this->setError(RedshopbHelperThumbnail::getError());

				return false;
			}

			// Delete old if exists
			if ($table->load($pk) && $table->get('image') != '')
			{
				RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, 'wash_care_spec');
			}

			// Saving image
			$data['image'] = RedshopbHelperThumbnail::savingImage(
				(string) $file['tmp_name'], (string) $file['name'], $table->get('id'), false, 'wash_care_spec'
			);

			if ($data['image'] === false)
			{
				$this->setError(RedshopbHelperThumbnail::getError());

				return false;
			}
		}
		elseif (isset($data['deleteImage']) && $data['deleteImage'] == 1)
		{
			// Delete old if exists
			if ($table->load($pk) && $table->get('image') != '')
			{
				RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, 'wash_care_spec');
				$data['image'] = '';
			}
		}

		return parent::save($data);
	}
}
