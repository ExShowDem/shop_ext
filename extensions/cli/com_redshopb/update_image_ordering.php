<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CliApplication;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
JLoader::import('redshopb.library');

/**
 * Updates product images ordering for each product.
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Images
 * @since       1.0
 */
class Update_Image_OrderingApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		// Print a blank line.
		$this->out();
		$this->out('Updating product images ordering');
		$this->out('============================');
		$this->out();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$this->out('Gathering all products...');
		$query->select($db->qn('id'))
			->from($db->qn('#__redshopb_product'));
		$pIds = $db->setQuery($query)->loadColumn();
		$this->out(count($pIds) . ' products found!');
		$this->out();

		foreach ($pIds as $pid)
		{
			$product = RedshopbEntityProduct::getInstance($pid);
			$this->out('Updating images order for product: ' . $product->get('name', 'ID ' . $product->getId()));
			$this->orderImages($product->searchImages(array('list.ordering' => 'm.name', 'list.direction' => 'ASC')));
		}

		$this->out();
		$this->out('============================');
		$this->out('Images ordering completed!');
		$this->out();
	}

	/**
	 * Function for updating product images ordering.
	 *
	 * @param   RedshopbEntitiesCollection  $images  Images collection.
	 *
	 * @return  void
	 *
	 * @since   1.12.51
	 */
	private function orderImages($images)
	{
		$i     = 0;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		foreach ($images as $image)
		{
			$id = $image->getId();

			$query->update($db->qn('#__redshopb_media'))
				->set($db->qn('ordering') . ' = ' . $i)
				->where($db->qn('id') . ' = ' . (int) $id);
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->out('Error updating order value for image ' . $image->get('name', 'ID ' . $image->getId()) . '!');
			}

			$i++;
		}

		$this->out(count($images) . ' images updated.');
	}
}
