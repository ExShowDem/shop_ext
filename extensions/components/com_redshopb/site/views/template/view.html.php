<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
/**
 * Template View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.6.36
 */
class RedshopbViewTemplate extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var  object
	 */
	protected $state;

	/**
	 * @var  array
	 */
	protected $usedTags;

	/**
	 * @var array
	 */
	protected $wholeTags = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		RedshopbHelperTemplate::createDefaultTemplate($this->item);

		$this->usedTags = RedshopbHelperTemplate::getUsedTagsInTemplate($this->item->content);

		$templateGroup = $this->form->getValue('template_group');
		$exclude       = array();

		if ($templateGroup)
		{
			if ($templateGroup == 'shop')
			{
				$this->wholeTags['field'] = $this->getFieldTags();
				$exclude[]                = 'types';
			}

			$this->wholeTags = array_merge(
				$this->wholeTags, $this->getLayoutsTags(
					JPATH_ROOT . '/components/com_redshopb/layouts/' . RedshopbHelperTemplate::getTagFolderForGroup($templateGroup),
					$exclude
				)
			);
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_TEMPLATE_FORM_TITLE');
		$state = $isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Returns all field tags available for template
	 *
	 * @return  array  array of template tags
	 */
	public function getFieldTags()
	{
		$tags = array();

		$fields = RedshopbHelperField::getFields();

		foreach ($fields as $scope => $fieldsInScope)
		{
			if (!isset($tags[$scope]))
			{
				$tags[$scope] = array();
			}

			foreach ($fieldsInScope as $field)
			{
				$tags[$scope]['fields.title.' . $field->alias] = $field->description;
				$tags[$scope]['fields.' . $field->alias]       = $field->description;
			}
		}

		return $tags;
	}

	/**
	 * Returns all tags available for templates
	 *
	 * @param   string  $folder   Folder for search
	 * @param   array   $exclude  Array with names of files or folders which should not be shown in the result.
	 * @param   int     $level    Current deep level
	 *
	 * @return  array  array of templates tags
	 */
	public function getLayoutsTags($folder, $exclude = array(), $level = 0)
	{
		$tags = array();

		if (JFolder::exists($folder))
		{
			$handle = opendir($folder);

			if ($handle)
			{
				$level++;

				while (false !== ($entry = readdir($handle))) // @codingStandardsIgnoreLine
				{
					if (in_array($entry, $exclude) || in_array($entry, array('.', '..')))
					{
						continue;
					}

					$file = $folder . '/' . $entry;

					if (is_dir($file))
					{
						$results = $this->getLayoutsTags($file, $exclude, $level);

						if (!empty($results))
						{
							if ($level > 1)
							{
								foreach ($results as $result)
								{
									$tags[$entry . '.' . $result] = $entry . '.' . $result;
								}
							}
							else
							{
								foreach ($results as $key => $result)
								{
									$tags[$entry][$entry . '.' . $key] = $entry . '.' . $result;
								}
							}
						}
					}
					elseif (is_file($file) && preg_match("/.php/", $file))
					{
						$cropExt        = JFile::stripExt($entry);
						$tags[$cropExt] = $cropExt;
					}
				}

				closedir($handle);
			}
		}

		return $tags;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		// Just allow edit/save on new template, disable for default template (1,2,3,4)
		if (RedshopbHelperACL::getPermission('manage', 'template', array('edit', 'edit.own'), true)
			&& (($this->state->get('templateName', '') && strpos($this->state->get('templateName', ''), '.') === false) || $this->item->editable))
		{
			$save         = RToolbarBuilder::createSaveButton('template.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('template.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			if (RedshopbHelperACL::getPermission('manage', 'template', array('create'), true))
			{
				$saveAndNew = RToolbarBuilder::createSaveAndNewButton('template.save2new');

				$group->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('template.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('template.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
