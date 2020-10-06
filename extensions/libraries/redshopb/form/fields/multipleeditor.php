<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\EditorField;
use Joomla\CMS\Editor\Editor;

FormHelper::loadFieldClass('editor');

/**
 * Form Field class for the Joomla CMS.
 * A textarea field for content creation
 *
 * @see    Editor
 * @since  1.6
 */
class JFormFieldMultipleeditor extends EditorField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Multipleeditor';

	/**
	 * The Editor object.
	 *
	 * @var    Editor
	 * @since  1.6
	 */
	protected $editor;

	/**
	 * The height of the editor.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $height;

	/**
	 * The width of the editor.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $width;

	/**
	 * The assetField of the editor.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $assetField;

	/**
	 * The authorField of the editor.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $authorField;

	/**
	 * The asset of the editor.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $asset;

	/**
	 * The buttons of the editor.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $buttons;

	/**
	 * The hide of the editor.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $hide;

	/**
	 * The editorType of the editor.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $editorType;

	/**
	 * @var string
	 */
	protected $disabledLayout = 'redshopb.field.disabled_editor';

	/**
	 * Method to get the field input markup for the editor area
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$readonly = $this->readonly || $this->disabled;

		if ($this->element['multiple'] == 1)
		{
			if (!is_array($this->value))
			{
				$this->value = array($this->value);
			}

			$return = array();

			foreach ($this->value AS $index => $value)
			{
				if ($readonly)
				{
					$layoutData = array_merge($this->getLayoutData(), array('id' => $this->id . '_' . $index, 'value' => $value));

					// Trim the trailing line in the layout file
					$return[] = rtrim(RedshopbLayoutHelper::render($this->disabledLayout, $layoutData), PHP_EOL);
				}
				else
				{
					$editor   = $this->getEditor();
					$return[] = $editor->display(
						$this->name,
						htmlspecialchars($value, ENT_COMPAT, 'UTF-8'),
						$this->width,
						$this->height,
						$this->columns,
						$this->rows,
						$this->buttons ? (is_array($this->buttons) ? array_merge($this->buttons, $this->hide) : $this->hide) : false,
						$this->id . '_' . $index,
						$this->asset,
						$this->form->getValue($this->authorField),
						array('syntax' => (string) $this->element['syntax'])
					);
				}
			}

			return implode(' ', $return);
		}

		if ($readonly)
		{
			// Trim the trailing line in the layout file
			return rtrim(RedshopbLayoutHelper::render($this->disabledLayout, $this->getLayoutData()), PHP_EOL);
		}
		else
		{
			// Get an editor object.
			$editor = $this->getEditor();

			return $editor->display(
				$this->name, htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'), $this->width, $this->height, $this->columns, $this->rows,
				$this->buttons ? (is_array($this->buttons) ? array_merge($this->buttons, $this->hide) : $this->hide) : false, $this->id, $this->asset,
				$this->form->getValue($this->authorField), array('syntax' => (string) $this->element['syntax'])
			);
		}
	}

	/**
	 * Method to get a Editor object based on the form field.
	 *
	 * @return  Editor  The Editor object.
	 *
	 * @since   1.6
	 */
	protected function getEditor()
	{
		// Only create the editor if it is not already created.
		if (empty($this->editor))
		{
			$editor = null;

			if ($this->editorType)
			{
				// Get the list of editor types.
				$types = $this->editorType;

				// Get the database object.
				$db = Factory::getDbo();

				// Iterate over teh types looking for an existing editor.
				foreach ($types as $element)
				{
					// Build the query.
					$query = $db->getQuery(true)
						->select('element')
						->from('#__extensions')
						->where('element = ' . $db->quote($element))
						->where('folder = ' . $db->quote('editors'))
						->where('enabled = 1');

					// Check of the editor exists.
					$db->setQuery($query, 0, 1);
					$editor = $db->loadResult();

					// If an editor was found stop looking.
					if ($editor)
					{
						break;
					}
				}
			}

			// Create the Editor instance based on the given editor.
			if (is_null($editor))
			{
				$conf   = Factory::getConfig();
				$editor = $conf->get('editor');
			}

			$this->editor = Editor::getInstance($editor);
		}

		return $this->editor;
	}

	/**
	 * Method to get the Editor output for an onSave event.
	 *
	 * @return  string  The Editor object output.
	 *
	 * @since   1.6
	 */
	public function save()
	{
		return $this->getEditor()->save($this->id);
	}
}
