<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Filter Fieldset Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       2.0
 */
class RedshopbControllerFilter_Fieldset extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_FILTER_FIELDSET';

	/**
	 * Ajax function for searching fields.
	 *
	 * @return  void
	 *
	 * @since   1.12.50
	 */
	public function ajaxSearchFields()
	{
		$app      = Factory::getApplication();
		$response = new RedshopbAjaxResponse;
		$search   = $app->input->getString('search', '');
		$id       = $app->input->getInt('id', 0);
		$fields   = $this->getModel()->getUnselectedFields($id, $search);

		// Set response body
		$response->setBody(RedshopbLayoutHelper::render('filter_fieldset.modal.fields', array('fields' => $fields)));

		echo $response->toJson();
		$app->close();
	}
}
