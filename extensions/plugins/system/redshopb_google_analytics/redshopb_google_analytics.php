<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Redshopb System Customer Plugin
 *
 * @package     Aesir.E-Commerce
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemRedshopb_Google_Analytics extends CMSPlugin
{
	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $head = '';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $body = '';

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @since   1.0.0
	 * @throws Exception
	 */
	public function __construct($subject, $config = array())
	{
		parent::__construct($subject, $config);

		if (!Factory::getApplication()->isClient('site'))
		{
			return;
		}

		JLoader::import('redshopb.library');
		RedshopbLayoutFile::addIncludePathStatic(__DIR__ . '/layouts');

		if ($this->params->get('google_analytics', 0) == 1
			&& $this->params->get('ga_tracking_id', '') != '')
		{
			$this->head .= RedshopbLayoutFile::getInstance('script.google.google_analytics')->render(array('params' => $this->params));
		}
	}

	/**
	 * Function called on receipt and pay layouts
	 *
	 * @param   array  $orderDetails  array('multipleOrderIds' => string, 'multipleOrders' => array)
	 * @param   RView  $view          View object
	 *
	 * @return void
	 */
	public function onAfterRedshopbShopPrepareOrder(&$orderDetails, &$view)
	{
		if ($view->getLayout() == 'receipt'
			&& !empty($view->customerOrder))
		{
			foreach ($view->customerOrder as $order)
			{
				if ($this->params->get('google_analytics_ecommerce', 1) == 1)
				{
					$this->body .= RedshopbLayoutFile::getInstance('script.google.google_analytics_ecommerce')
						->render(array('params' => $this->params, 'order' => $order));
				}

				if ($this->params->get('tracking_adwords', 0) == 1)
				{
					$this->body .= RedshopbLayoutFile::getInstance('script.google.tracking_adwords')
						->render(array('params' => $this->params, 'order' => $order));
				}
			}
		}
	}

	/**
	 * onAfterRender
	 *
	 * @since  1.0.0
	 *
	 * @return  void
	 * @throws Exception
	 */
	public function onAfterRender()
	{
		$app = Factory::getApplication();

		if (!$app->isClient('site')
			|| (empty($this->body) && empty($this->head)))
		{
			return;
		}

		$buffer = $app->getBody();

		$this->injectScript($buffer, 'head');
		$this->injectScript($buffer, 'body');

		$app->setBody($buffer);
	}

	/**
	 * Inject script
	 *
	 * @param   string  $buffer   Buffer
	 * @param   string  $section  Body or head
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function injectScript(&$buffer, $section)
	{
		if (!empty($this->{$section}))
		{
			$pos = strpos($buffer, '</' . $section . '>');

			if ($pos !== false)
			{
				$buffer = substr($buffer, 0, $pos) . $this->{$section} . substr($buffer, $pos);
			}
		}
	}
}
