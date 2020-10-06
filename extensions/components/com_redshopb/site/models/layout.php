<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
JLoader::import('layout', JPATH_COMPONENT_ADMINISTRATOR . 'helpers');
JLoader::import('joomla.filesystem.file');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Menu;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Layout Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelLayout extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'layout';

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data = array())
	{
		$table = $this->getTable();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Define menu item vars
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');

		/** @var Menu $menuTable */
		$menuTable = Table::getInstance('Menu');

		$component = ComponentHelper::getComponent('com_redshopb');

		$menuRow = array();

		$db->transactionStart();

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Save data.
			$table->save($data);

			// Clean the cache.
			$this->cleanCache();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			$db->transactionRollback();

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
			$pk = (int) $this->getState($this->getName() . '.id');
		}

		$this->setState($this->getName() . '.new', $isNew);

		// Saving CSS file
		$layout     = (object) $data;
		$layout->id = $pk;
		$path       = JPATH_ROOT . '/media/com_redshopb/css/';

		if (JFile::exists($path . 'layout_' . $pk . '.css'))
		{
			try
			{
				JFile::delete($path . 'layout_' . $pk . '.css');
			}
			catch (Exception $e)
			{
				$this->setError(Text::_($e->getMessage()));
				$db->transactionRollback();

				return false;
			}
		}

		$css = $layout->params['customCSS'];

		try
		{
			JFile::write($path . 'layout_' . $pk . '.css', $css);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			$db->transactionRollback();

			return false;
		}

		$db->transactionCommit();

		$db        = Factory::getDbo();
		$menuQuery = $db->getQuery(true);
		$menuQuery->select('*')
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('menutype') . ' = ' . $db->quote($data['menu_type']))
			->where($db->quoteName('published') . ' <> ' . $db->quote('-2'));

		$db->setQuery($menuQuery);
		$menuItems = $db->loadObjectList();

		foreach ($menuItems as $menuItem)
		{
			if (json_decode($menuItem->params)->companyid == $data['company_id'])
			{
				foreach (get_object_vars($menuItem) as $propertyName => $propertyValue)
				{
					$menuRow[$propertyName] = $propertyValue;
				}

				$menuRow['alias'] = $data['alias'];
			}
		}

		// Generate new menu item
		if (empty($menuRow))
		{
			$params = new Registry(
				array(
					'company_id'    => $data['company_id'],
					'department_id' => $data['department_id'],
					'is_default'    => 0
				)
			);

			$menuRow['id']           = 0;
			$menuRow['title']        = $data['name'];
			$menuRow['published']    = 1;
			$menuRow['menutype']     = $data['menu_type'];
			$menuRow['type']         = 'component';
			$menuRow['component_id'] = $component->id;
			$menuRow['parent_id']    = 1;
			$menuRow['language']     = '*';
			$menuRow['alias']        = $data['alias'];
			$menuRow['path']         = $data['alias'];
			$menuRow['link']         = 'index.php?option=com_redshopb&view=b2buserregister';
			$menuRow['params']       = $params->toString();

			$menuTable->setLocation($menuRow['parent_id'], 'last-child');
		}

		if (!$menuTable->save($menuRow))
		{
			$this->setError(Text::_($menuTable->getError()));

			return false;
		}

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item->id > 0)
		{
			$company           = RedshopbHelperCompany::getCompaniesByLayoutsIds(array($item->id));
			$item->customer_id = is_array($company) ? (count($company) ? $company[0] : 0) : $company;
		}

		return $item;
	}

	/**
	 * Set default layout.
	 *
	 * @param   int  $pk  Layout id
	 *
	 * @return boolean True on success
	 */
	public function setDefault($pk = 0)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('company_id'))
			->from($db->qn('#__redshopb_layout'))
			->where($db->qn('id') . ' = ' . $pk);
		$db->setQuery($query);

		$companyId = $db->loadResult();

		if ($companyId)
		{
			$query->clear()
				->update($db->qn('#__redshopb_company'))
				->set('layout_id = ' . $pk)
				->where('id = ' . $companyId);
			$db->setQuery($query);
			$db->execute();

			$mainCompany = RedshopbApp::getMainCompany();

			if ($companyId == $mainCompany->id)
			{
				$query->clear()
					->update($db->qn('#__redshopb_layout'))
					->set('home = 0');
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__redshopb_layout'))
					->set('home = 1')
					->where('id = ' . $pk);
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Get default layout.
	 *
	 * @return object Layout object
	 */
	public function getDefault()
	{
		$db    = $this->getDbo();
		$query = RedshopbHelperLayout::getDefaultLayoutQuery();
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   Form    $form   A Form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     FormField
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content')
	{
		parent::preprocessForm($form, $data, $group);

		$user      = Factory::getUser();
		$companyId = RedshopbHelperUser::getUserCompanyId($user->id, 'joomla');

		if ($companyId)
		{
			$imagePath = RedshopbEntityCompany::getInstance($companyId)->getImageFolder();
			$form->setFieldAttribute('topImage', 'directory', $imagePath, 'params');
			$form->setFieldAttribute('backgroundImage', 'directory', $imagePath, 'params');
		}

		if (!ComponentHelper::isEnabled('com_rsbmedia'))
		{
			$form->setFieldAttribute('topImage', 'type', 'rmedia', 'params');
			$form->setFieldAttribute('backgroundImage', 'type', 'rmedia', 'params');
		}
	}
}
