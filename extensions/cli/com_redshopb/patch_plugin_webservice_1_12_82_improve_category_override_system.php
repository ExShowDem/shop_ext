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
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Application\CliApplication;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

/**
 * This script is only for DJe member sites. It will make sync records that came from ERP WS calls a main one
 * and records from webservice sync an enrichment
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class ImproveCategoryOverrideSystemApplicationCli extends CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		$this->out('Started');

		$this->out('getting WS plugin options');
		$db     = Factory::getDbo();
		$plugin = PluginHelper::getPlugin('rb_sync', 'webservice');
		$params = new Registry($plugin->params);

		// Getting all category records from DJe portal since we dont have pim.id stored
		$this->out('Get DJe Portal token');

		$curl        = curl_init($params->get('remote_url') . '/index.php?option=token&api=oauth2');
		$curlOptions = array(
			'client_id'     => $params->get('client_id'),
			'client_secret' => $params->get('client_secret'),
			'grant_type'    => 'client_credentials'
		);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlOptions);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($curl);
		$result = json_decode($result);
		curl_close($curl);

		if (isset($result->error))
		{
			$this->out($result->error_description);

			return;
		}

		$accessToken = $result->access_token;
		$this->out('Token:' . $accessToken);
		$this->out('Get all categories from DJe Portal ' . $params->get('remote_url'));

		$readListUrl = '/index.php?webserviceClient=site&webserviceVersion=1.5.0&option=redshopb'
			. '&view=category&api=hal&list[ordering]=id&list[direction]=ASC'
			. '&filter[include_images]=true&filter[include_local_fields]=true&list[limit]=0'
			. '&access_token=' . $accessToken;

		$curl = curl_init($params->get('remote_url') . $readListUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($curl);
		$this->out('Returned code: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE));

		$categories = json_decode($result);
		curl_close($curl);

		$this->out('Number of categories fetched: ' . count($categories->_embedded->item));

		$this->out('Getting categories created through WS');
		$query = $db->getQuery(true)
			->select('s.*')
			->from($db->qn('#__redshopb_sync', 's'))
			->where($db->qn('s.reference') . ' = ' . $db->q('erp.pim.category'));

		$categoryReferences = $db->setQuery($query)->loadObjectList('remote_key');
		$this->out('Number of category from WS: ' . count($categoryReferences));

		foreach ($categories->_embedded->item as $category)
		{
			$erpId = '';

			foreach ($category->id_others as $id)
			{
				if (strpos($id, 'pim.') === 0)
				{
					$erpId = substr($id, strlen('pim.'));
					break;
				}
			}

			if ($erpId && !empty($categoryReferences[$erpId]))
			{
				$updateQuery = $db->getQuery(true)
					->update('#__redshopb_sync')
					->set($db->qn('main_reference') . ' = 0')
					->where($db->qn('reference') . ' = ' . $db->q('erp.webservice.categories'))
					->where($db->qn('remote_key') . ' = ' . $db->q((int) $category->id));

				$db->setQuery($updateQuery)->execute();
				$this->out('Setting category reference to not main: ' . $category->name);
			}
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}
}

$instance = CliApplication::getInstance('ImproveCategoryOverrideSystemApplicationCli');
$instance->execute();
