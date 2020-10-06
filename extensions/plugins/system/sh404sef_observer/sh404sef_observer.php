<?php
/**
 * @package     RedCOMPONENT
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Observer\AbstractObserver;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Sh404sefObserver\Helper\UrlHelper;
use Sh404sefObserver\Observer\Sh404sefObserver;
use Sh404sefObserver\Helper\PreprocessHelper;

require_once __DIR__ . '/vendor/autoload.php';

PluginHelper::importPlugin('sh404sef_observer');

JObserverMapper::addObserverClassToClass(Sh404sefObserver::class, Table::class);

/**
 * sh404sef_observer System Plugin
 *
 * @package     Sh404sef_Observer
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemSh404sef_Observer extends CMSPlugin
{
	/**
	 * @var boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var integer
	 * @since 2.6.0
	 */
	protected $maxId;

	/**
	 * @since   2.6.0
	 * @throws Exception
	 * @return  void
	 */
	public function onBeforeRender()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if (!$app->isClient('administrator')
			|| $input->getCmd('option') != 'com_menus'
			|| $input->get('view') != 'items'
			|| $input->get('tmpl') == 'component'
			|| $this->params->get('show_duplicate_menu_item_message', 0) != 1
			|| !PluginHelper::isEnabled('system', 'sh404sef')
			|| !Sh404sefFactory::getConfig()->Enabled)
		{
			return;
		}

		$enabledPlugins = PluginHelper::getPlugin('sh404sef_observer');

		if (empty($enabledPlugins))
		{
			return;
		}

		$db = Factory::getDbo();

		$like = [];

		foreach ($enabledPlugins as $enabledPlugin)
		{
			$like[] = 'm.link LIKE ' . $db->q('%option=com_' . $enabledPlugin->name . '%');
		}

		$query = $db->getQuery(true)
			->select('m.link, count(*) as countLinks')
			->from($db->qn('#__menu', 'm'))
			->where('m.published = 1')
			->where('m.parent_id > 0')
			->where('m.client_id = 0')
			->where('m.type = ' . $db->q('component'))
			->where('(' . implode(' OR ', $like) . ')')
			->group('m.link')
			->having('countLinks > 1');

		$query = $db->getQuery(true)
			->select('b.link')
			->from('(' . $query . ') AS b');

		$query = $db->getQuery(true)
			->select('c.*, t.title as menuTitle')
			->from($db->qn('#__menu', 'c'))
			->leftJoin($db->qn('#__menu_types', 't') . ' ON t.menutype = c.menutype')
			->where('c.link IN (' . $query . ')')
			->order('c.link');

		$links = $db->setQuery($query)->loadObjectList();

		if (empty($links))
		{
			return;
		}

		$table = ['<table class="table">'];

		$table[] = Text::_('PLG_SYSTEM_SH404SEF_OBSERVER_FOUND_DUPLICATES');

		$table[]     = '<tr><td>';
		$currentLink = '';

		foreach ($links as $link)
		{
			if ($currentLink != $link->link)
			{
				if (!empty($currentLink))
				{
					$table[] = '</td></tr><tr><td>';
				}

				$currentLink = $link->link;
			}

			$url = Route::_("index.php?option=com_menus&task=item.edit&id={$link->id}");

			$table[] = Text::sprintf('PLG_SYSTEM_SH404SEF_OBSERVER_FOUND_DUPLICATES_ONE_LINK', $url, $link->title, $link->alias, $link->menuTitle);
		}

		$table[] = '</td></tr>';
		$table[] = '</table>';

		$app->enqueueMessage(implode($table), 'warning');
	}

	/**
	 * Catch state changes from list views
	 *
	 * @param   string         $context  Context
	 * @param   array|integer  $pks      Ids
	 * @param   integer        $value    Value
	 *
	 * @return boolean
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if (!PluginHelper::isEnabled('system', 'sh404sef')
			|| !\Sh404sefFactory::getConfig()->Enabled)
		{
			return true;
		}

		switch ($context)
		{
			case 'com_menus.item':
				$preprocess = PreprocessHelper::getInstance();

				// Un-publish children as well
				if ($value != 1)
				{
					$pks = array_keys(UrlHelper::getChildrenItems($pks, '#__menu', true));
				}

				if (!empty($pks))
				{
					$dispatcher = \RFactory::getDispatcher();

					foreach ((array) $pks as $pk)
					{
						if ($value == 1)
						{
							$table = Table::getInstance('Menu');

							if ($table->load($pk))
							{
								$observerClass = $table->getObserverOfClass(Sh404sefObserver::class);

								if ($observerClass instanceof AbstractObserver)
								{
									$dispatcher->trigger('onSh404sefObserverChangesOnMenuItem', array($observerClass, $table->get('link')));
								}
							}
						}

						$preprocess->addCondition(['Itemid' => $pk]);
					}
				}

				$preprocess->processSefUrl();
				break;
		}

		return true;
	}

	/**
	 * @return void
	 * @since   2.6.0
	 */
	public function onAfterRoute()
	{
		if (!PluginHelper::isEnabled('system', 'sh404sef')
			|| !\Sh404sefFactory::getConfig()->Enabled)
		{
			return;
		}

		$this->maxId = $this->getMaxSh404SefId();
	}

	/**
	 * @return integer
	 * @since   2.6.0
	 */
	protected function getMaxSh404SefId()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('MAX(id)')
			->from($db->qn('#__sh404sef_urls'));

		return (int) $db->setQuery($query, 0, 1)
			->loadResult();
	}

	/**
	 * @return void
	 * @since   2.6.0
	 */
	public function onAfterRespond()
	{
		if (empty($this->maxId))
		{
			return;
		}

		$newMaxId = $this->getMaxSh404SefId();

		if ($this->maxId >= $newMaxId)
		{
			return;
		}

		PreprocessHelper::getInstance()
			->addCondition(['from' => $this->maxId, 'to' => $newMaxId], 'removeHomeAlias')
			->processSefUrl();
	}
}
