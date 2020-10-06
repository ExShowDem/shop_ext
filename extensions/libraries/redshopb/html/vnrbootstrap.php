<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_REDCORE') or die;
jimport('redcore.html.rbootstrap');

use Joomla\CMS\Factory;
/**
 * Vnrbootstrap HTML class.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Html
 * @since       1.12.39
 */
abstract class JHtmlVnrbootstrap extends JHtmlRbootstrap
{
	/**
	 * Begins the display of a new tab content panel.
	 *
	 * @param   string  $selector  Identifier of the panel.
	 * @param   string  $id        The ID of the div element
	 * @param   string  $title     The title text for the new UL tab
	 *
	 * @return  string  HTML to start a new panel
	 */
	public static function addTab($selector, $id, $title)
	{
		static $tabScriptLayout = null;
		static $tabLayout       = null;

		$tabScriptLayout = is_null($tabScriptLayout) ? new RedshopbLayoutFile('redshopb.html.bootstrap.addtabscript') : $tabScriptLayout;
		$tabLayout       = is_null($tabLayout) ? new RedshopbLayoutFile('redshopb.html.bootstrap.addtab') : $tabLayout;

		$active = (static::$loaded['JHtmlRbootstrap::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		// Inject tab into UL
		Factory::getDocument()->addScriptDeclaration(
			$tabScriptLayout->render(
				array(
					'selector' => $selector,
					'id'       => $id,
					'active'   => $active,
					'title'    => $title
				)
			)
		);

		$html = $tabLayout->render(array('id' => $id, 'active' => $active));

		return $html;
	}

	/**
	 * [renderModal description]
	 *
	 * @param   string   $selector   [description]
	 * @param   array    $params     [description]
	 * @param   string   $body       [description]
	 *
	 * @return  string
	 */
	public static function renderModal($selector = 'modal', $params = array(), $body = '')
	{
		if (RHtmlMedia::getFramework() == 'bootstrap3')
		{
			$layoutData = array(
				'selector' => $selector,
				'params'   => $params,
				'body'     => $body,
			);

			return RedshopbLayoutHelper::render('modal.dialog', $layoutData);
		}
		else
		{
			return parent::renderModal($selector, $params, $body);
		}
	}
}
