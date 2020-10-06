<?php
/**
 * @package     Aesir.E-Commerce.Plugin.Lookup_Integration
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::import('joomla.library');

/**
 * Vanir - VAT & EAN Lookup integration
 *
 * @since       1.0.0
 */
class PlgVanirLookup_Integration extends CMSPlugin
{
	/**
	 * Auto load language
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Looks up an EAN number through the nemhandel.dk API
	 *
	 * @return   void
	 */
	public function onAjaxLookup_Integration() //@codingStandardsIgnoreLine   Ignores method name not being camel case because of classname dependency
	{
		$app = Factory::getApplication();

		$ean = $app->input->post->getInt('ean');

		$url = 'https://registration.nemhandel.dk/NemHandelRegisterWeb/public/participant/info?key=' . $ean . '&keytype=GLN&asXML=true';

		$curl = curl_init($url);

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
			)
		);

		$xml = curl_exec($curl);

		$response = new RedshopbAjaxResponse;

		$response->setData(array('xml' => $xml));

		echo $response->toJson();

		$app->close();
	}

	/**
	 * Renders the lookup layout
	 *
	 * @param   string   $layout   This variable will be replaced with a HTML string
	 *
	 * @return   false|null
	 */
	public function onLookupIntegrationRenderLayout(&$layout)
	{
		$vatEnabled = (bool) $this->params->get('vat', 0);
		$eanEnabled = (bool) $this->params->get('ean', 0);

		if (!$vatEnabled && !$eanEnabled)
		{
			return false;
		}

		$this->injectJS();

		if ($vatEnabled)
		{
			$layout .= $this->renderVAT();
		}

		if ($eanEnabled)
		{
			$layout .= $this->renderEAN();
		}
	}

	/**
	 * Injects the JS files that are used in the layouts
	 *
	 * @return   void
	 */
	private function injectJS()
	{
		$doc = Factory::getDocument();

		$lookup   = file_get_contents(JPATH_PLUGINS . '/vanir/lookup_integration/js/lookup.js');
		$fillform = file_get_contents(JPATH_PLUGINS . '/vanir/lookup_integration/js/fillform.js');

		$doc->addScriptDeclaration($lookup);
		$doc->addScriptDeclaration($fillform);

		// These strings are used in the lookup JS object
		Text::script('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_NUMBER_FOUND', true);
		Text::script('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_NUMBER_NOT_FOUND', true);
		Text::script('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_CONNECTION_FAILED', true);
		Text::script('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_EAN_NUMBER_FOUND', true);
		Text::script('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_EAN_NUMBER_NOT_FOUND', true);
	}

	/**
	 * Renders the VAT lookup layout
	 *
	 * @return   string
	 */
	private function renderVAT()
	{
		$tmpl = new FileLayout('vanir.lookup_integration.layouts.vat', JPATH_PLUGINS);

		return $tmpl->render();
	}

	/**
	 * Renders the EAN lookup layout
	 *
	 * @return   string
	 */
	private function renderEAN()
	{
		$tmpl = new FileLayout('vanir.lookup_integration.layouts.ean', JPATH_PLUGINS);

		return $tmpl->render();
	}
}
