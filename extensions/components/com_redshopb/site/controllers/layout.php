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

JLoader::import('layout', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/');

/**
 * Layout Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerLayout extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_LAYOUT';

	/**
	 * Preview layout
	 *
	 * @return void
	 */
	public function preview()
	{
		$app    = Factory::getApplication();
		$id     = $app->input->get('id', 0, 'int');
		$model  = $this->getModel();
		$layout = $model->getItem($id);

		echo RedshopbLayoutHelper::render(
			'layout.preview',
			array(
				'name'    => $layout->name,
				'id'      => $id,
				'params'  => (object) $layout->params
			)
		);

		$app->close();
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = null)
	{
		$urlVar = empty($urlVar) ? 'id' : $urlVar;

		return parent::getRedirectToItemAppend($recordId, $urlVar);
	}
}
