<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Form\Form;

/**
 * Form class.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Form
 * @since       1.0
 */
class RedshopbForm extends RForm
{
	/**
	 * @var array
	 */
	protected $oldWSProperties = array('WSFlags' => array(), 'WSProperties' => array());

	const LOCKED        = -1;
	const MATCH         = 0;
	const USER_OVERRIDE = 1;

	/**
	 * @var boolean
	 */
	public $disableAllFormFields = false;

	/**
	 * @var RedshopbTable
	 */
	public $table = false;

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   string  $data     The name of an XML file or string to load as the form definition.
	 * @param   array   $options  An array of form options.
	 * @param   mixed   $replace  Flag to toggle whether form fields should be replaced if a field
	 *                            already exists with the same group/name.
	 * @param   mixed   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  Form
	 *
	 * @throws  InvalidArgumentException if no data provided.
	 * @throws  RuntimeException if the form could not be loaded.
	 */
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
	{
		// Reference to array with form instances
		/** @var  RForm[] $forms */
		$forms = &self::$forms;

		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name]))
		{
			$data = trim($data);

			if (empty($data))
			{
				throw new InvalidArgumentException(sprintf('RForm::getInstance(name, *%s*)', gettype($data)));
			}

			// Instantiate the form.
			$forms[$name] = new RedshopbForm($name, $options);

			// Load the data.
			if (substr(trim($data), 0, 1) == '<')
			{
				if ($forms[$name]->load($data, $replace, $xpath) == false)
				{
					throw new RuntimeException('RForm::getInstance could not load form');
				}
			}
			else
			{
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
				{
					throw new RuntimeException('RForm::getInstance could not load file');
				}
			}
		}

		return $forms[$name];
	}

	/**
	 * Method to load, setup and return a JFormField object based on field data.
	 *
	 * @param   \SimpleXMLElement  $element  The XML element object representation of the form field.
	 * @param   string             $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed              $value    The optional value to use as the default for the field.
	 *
	 * @return  \JFormField|boolean  The JFormField object for the field or boolean false on error.
	 *
	 * @since   1.14.0
	 */
	protected function loadField($element, $group = null, $value = null)
	{
		// Make sure there is a valid SimpleXMLElement.
		if (!($element instanceof \SimpleXMLElement))
		{
			return false;
		}

		// Get the field type.
		$type = $element['type'] ? (string) $element['type'] : 'text';

		// Load the JFormField object for the field.
		$field = $this->loadFieldType($type);

		// If the object could not be loaded, get a text field object.
		if ($field === false)
		{
			$field = $this->loadFieldType('text');
		}

		/*
		 * Get the value for the form field if not set.
		 * Default to the translated version of the 'default' attribute
		 * if 'translate_default' attribute if set to 'true' or '1'
		 * else the value of the 'default' attribute for the field.
		 */
		if ($value === null)
		{
			$default   = (string) ($element['default'] ? $element['default'] : $element->default);
			$translate = $element['translate_default'];

			if ($translate && ((string) $translate == 'true' || (string) $translate == '1'))
			{
				$lang = Factory::getLanguage();

				if ($lang->hasKey($default))
				{
					$debug   = $lang->setDebug(false);
					$default = Text::_($default);
					$lang->setDebug($debug);
				}
				else
				{
					$default = Text::_($default);
				}
			}

			$value = $this->getValue((string) $element['name'], $group, $default);
		}

		// Setup the JFormField object.
		$field->setForm($this);

		if ($this->disableAllFormFields
			&& (string) $element['disabled'] != 'true'
			&& (string) $element['readonly'] != 'true'
			&& (string) $element['type'] != 'hidden')
		{
			$element['disabled'] = 'true';
		}

		if ($field->setup($element, $value, $group))
		{
			return $field;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Return button for init back ws changes
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  string
	 */
	public function getBackWSValueButton($name, $group = null)
	{
		$field = $this->getField($name, $group);

		if (!$field
			|| $field->getAttribute('disabled') == 'true'
			|| $field->getAttribute('readonly') == 'true'
			|| $field->getAttribute('type') == 'hidden')
		{
			return '';
		}

		static $formInit = false;

		if (!$formInit)
		{
			$formInit = true;
			$document = Factory::getDocument();
			$document->addScriptDeclaration('
			(function($){
				$(document).ready(function () {
					$(\'body\').on(\'change\', \'.backWSValueButton input\', function(e){
						var input = $(this);
						var parent = input.parent();

						if (input.is(\':checked\')){
							parent.find(\'i\').removeClass(\'icon-undo\').addClass(\'icon-save\');
							parent.removeClass(\'btn-inverse\').addClass(\'btn-warning\');
						}else{
							parent.find(\'i\').removeClass(\'icon-save\').addClass(\'icon-undo\');
							parent.removeClass(\'btn-warning\').addClass(\'btn-inverse\');
						}
					});
				});
			})(jQuery);'
			);
		}

		$wsOverrideFound = $this->wsOverrideFound($name, $group);

		if ($wsOverrideFound == self::MATCH)
		{
			return '';
		}

		static $displayedButtons = array();
		$button                  = array();

		if (!array_key_exists($name, $displayedButtons))
		{
			$displayedButtons[$name] = true;
			$class                   = $this->getFieldAttribute($name, 'class', null, $group);

			if ($wsOverrideFound > 0)
			{
				$this->setFieldAttribute($name, 'class', $class . ' wsOverrideFound lockedColumnFound', $group);
				$button[] = ' <label class="hasTooltip btn btn-mini btn-inverse backWSValueButton" data-original-title="'
					. Text::_('COM_REDSHOPB_TABLE_LOCK_CURRENT_COLUMN_LOCKED_CAN_UNLOCK') . '">';
				$button[] = '<i class="icon-unlock"></i>';
				$button[] = '<input type="checkbox" class="hide" value="' . $wsOverrideFound . '" name="' . $field->formControl;

				if ($group)
				{
					$button[] = '[' . $group . ']';
				}

				$button[] = '[aECUnLockedColumns][' . $field->fieldname . ']" />';
				$button[] = '</label>';
			}
			elseif ($wsOverrideFound == self::LOCKED)
			{
				$this->setFieldAttribute($name, 'class', $class . ' wsOverrideFound', $group);
				$button[] = ' <label class="hasTooltip btn btn-mini btn-inverse backWSValueButton" data-original-title="'
					. Text::_('COM_REDSHOPB_TABLE_LOCK_CURRENT_COLUMN_LOCKED') . '">';
				$button[] = '<i class="icon-lock"></i>';
				$button[] = '</label>';
			}
		}

		return implode('', $button);
	}

	/**
	 * Method for check is WS override found.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  integer
	 */
	protected function wsOverrideFound($name, $group = null)
	{
		$userId = Factory::getUser()->id;

		if (!in_array(RedshopbHelperUser::getUserRole($userId, 'joomla'), array('superadmin', 'admin')))
		{
			return false;
		}

		static $overrides = array();
		$key              = $name . '.' . $group;

		if (array_key_exists($key, $overrides))
		{
			return $overrides[$key];
		}

		// Check to see if the assigned table is actually loaded
		$this->loadAssignedTable();
		$overrides[$key] = self::MATCH;
		$isLocked        = $this->table && $this->table->isTableColumnLocked($name);

		if ($isLocked)
		{
			$overrides[$key] = self::LOCKED;
		}

		// If this method (user) is the one that locked this column, he can unlock it
		if (!$isLocked
			&& $this->table instanceof Table
			&& isset($this->table->lockedColumns[$name]))
		{
			$overrides[$key] = $this->table->lockedColumns[$name]->id;
		}

		return $overrides[$key];
	}

	/**
	 * Check to see if the assigned table is actually loaded
	 *
	 * @return  void
	 */
	protected function loadAssignedTable()
	{
		if ($this->table instanceof Table)
		{
			if (!$this->table->{$this->table->get('_tbl_key')} && $this->data->get($this->table->get('_tbl_key')))
			{
				$this->table->load($this->data->get($this->table->get('_tbl_key')));
			}
		}
	}

	/**
	 * Check values are match or not
	 *
	 * @param   mixed  $value1  First value
	 * @param   mixed  $value2  Second value
	 *
	 * @return boolean
	 */
	protected function matchValues($value1, $value2)
	{
		if (is_array($value1)
			&& is_array($value2))
		{
			$value1 = !empty($value1) ? json_decode(json_encode($value1), true) : array();
			$value2 = !empty($value2) ? json_decode(json_encode($value2), true) : array();

			if (count($value1) > 0 && count($value2) > 0 && is_array($value1[0]) && is_array($value2[0]))
			{
				foreach ($value1 as $key => $val)
				{
					if (!isset($value2[$key]))
					{
						return false;
					}

					if (count(array_diff($value1[$key], $value2[$key])) > 0
						|| count(array_diff($value2[$key], $value1[$key])) > 0)
					{
						return false;
					}
				}

				foreach ($value2 as $key => $val)
				{
					if (!isset($value1[$key]))
					{
						return false;
					}

					if (count(array_diff($value1[$key], $value2[$key])) > 0
						|| count(array_diff($value2[$key], $value1[$key])) > 0)
					{
						return false;
					}
				}
			}
			else
			{
				if (count(array_diff($value1, $value2)) > 0
					|| count(array_diff($value2, $value1)) > 0)
				{
					return false;
				}
			}
		}
		else
		{
			if ($value1 != $value2)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get the label for a field input.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  string  The form field label.
	 *
	 * @since   11.1
	 */
	public function getLabel($name, $group = null)
	{
		// Attempt to get the form field.
		$field = $this->getField($name, $group);

		if ($field)
		{
			$label = $field->label;
			Factory::getApplication()->triggerEvent('onBeforeGetLabelRedshopb', array($this, $field, &$label));

			return $label . $this->getBackWSValueButton($name, $group);
		}

		return '';
	}

	/**
	 * Method to get a form field markup for the field input.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value  The optional value to use as the default for the field.
	 *
	 * @return  string  The form field markup.
	 *
	 * @since   11.1
	 */
	public function getInput($name, $group = null, $value = null)
	{
		// Attempt to get the form field.
		$field = $this->getField($name, $group, $value);

		if ($field)
		{
			return $this->getBackWSValueButton($name, $group) . $field->input;
		}

		return '';
	}

	/**
	 * Method to bind data to the form.
	 *
	 * @param   mixed  $data  An array or object of data to bind to the form.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function bind($data)
	{
		// Make sure there is a valid Joomla\CMS\Form\Form XML document.
		if (!($this->xml instanceof SimpleXMLElement))
		{
			return false;
		}

		// The data must be an object or array.
		if (!is_object($data) && !is_array($data))
		{
			return false;
		}

		// Convert the input to an array.
		if (is_object($data))
		{
			if ($data instanceof Registry)
			{
				// Handle a Registry.
				$data = $data->toArray();
			}
			elseif ($data instanceof CMSObject)
			{
				// Handle a CMSObject.
				$data = $data->getProperties();
			}
			else
			{
				// Handle other types of objects.
				$data = (array) $data;
			}
		}

		// Process the input data.
		foreach ($data as $k => $v)
		{
			if ($this->findField($k))
			{
				// If the field exists set the value.
				$this->data->set($k, $v);
			}
			elseif (is_object($v) || ArrayHelper::isAssociative($v))
			{
				// If the value is an object or an associative array hand it off to the recursive bind level method.
				$this->bindLevel($k, $v);
			}
		}

		return true;
	}
}
