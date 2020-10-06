<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
/**
 * Base Entity.
 *
 * @since  1.0
 */
abstract class RedshopbEntityBase
{
	/**
	 * @const  integer
	 * @since  1.0
	 */
	const STATE_ENABLED = 1;

	/**
	 * @const  integer
	 * @since  1.0
	 */
	const STATE_DISABLED = 0;

	/**
	 * ACL prefix used to check permissions
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $aclPrefix = "core";

	/**
	 * Identifier of the loaded instance
	 *
	 * @var  mixed
	 */
	protected $id = null;

	/**
	 * Cached instances
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * Cached item
	 *
	 * @var  mixed
	 */
	protected $item = null;

	/**
	 * Cached table.
	 *
	 * @var  Table
	 */
	protected $table;

	/**
	 * Option of the component containing the tables. Example: com_content
	 *
	 * @var  string
	 */
	protected $component;

	/**
	 * Translations for items that support them
	 *
	 * @var  array
	 */
	protected $translations = array();

	/**
	 * Constructor
	 *
	 * @param   mixed  $id  Identifier of the active item
	 */
	public function __construct($id = null)
	{
		if ($id)
		{
			$this->id = $id;
		}
	}

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __get($property)
	{
		if (null != $this->item && property_exists($this->item, $property))
		{
			return $this->item->$property;
		}
	}

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 * @param   mixed   $value     Value to assign
	 *
	 * @return  self
	 *
	 * @since   1.0
	 */
	public function __set($property, $value)
	{
		if (null === $this->item)
		{
			$this->item = new stdClass;
		}

		$this->item->{$property} = $value;

		return $this;
	}

	/**
	 * Ensure that clones don't modify cached data
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function __clone()
	{
		$this->item = clone $this->item;
	}

	/**
	 * Magic method isset for entity property
	 *
	 * @param   string  $name  Property name
	 *
	 * @return  boolean
	 */
	public function __isset($name)
	{
		return isset($this->item->$name);
	}

	/**
	 * Bind an object/array to the entity
	 *
	 * @param   mixed  $item  Array/Object containing the item fields
	 *
	 * @return  $this
	 */
	public function bind($item)
	{
		// Accept basic array binding
		if (is_array($item))
		{
			$item = (object) $item;
		}

		$this->item = $item;

		if (property_exists($item, 'id'))
		{
			$this->id = $item->id;

			$class = get_called_class();

			// Ensure that we cache the item
			if (!isset(static::$instances[$class][$this->id]))
			{
				static::$instances[$class][$this->id] = $this;
			}
		}

		return $this;
	}

	/**
	 * Check if current user can create an item
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function canCreate()
	{
		if ($this->canDo($this->getAclPrefix() . '.create'))
		{
			return true;
		}

		if ($this->canDo($this->getAclPrefix() . '.create.own'))
		{
			return $this->isOwner();
		}

		return false;
	}

	/**
	 * Check if current user can save an item
	 *
	 * @return boolean
	 */
	public function canSave()
	{
		if ($this->canReadOnly())
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if current user can delete an item
	 *
	 * @return  boolean
	 */
	public function canDelete()
	{
		if (!$this->hasId())
		{
			return false;
		}

		if ($this->canReadOnly())
		{
			return false;
		}

		if ($this->canDo($this->getAclPrefix() . '.delete'))
		{
			return true;
		}

		if ($this->canDo($this->getAclPrefix() . '.delete.own'))
		{
			return $this->isOwner();
		}

		return false;
	}

	/**
	 * Check if current user can edit this item
	 *
	 * @return  boolean
	 */
	public function canEdit()
	{
		if (!$this->hasId())
		{
			return false;
		}

		// User has global edit permissions
		if ($this->canDo($this->getAclPrefix() . '.edit'))
		{
			return true;
		}

		// User has global edit permissions
		if ($this->canDo($this->getAclPrefix() . '.edit.own'))
		{
			return $this->isOwner();
		}

		return false;
	}

	/**
	 * Check if current user has permission to perform an action
	 *
	 * @param   string  $action  The action. Example: core.create
	 *
	 * @return  boolean
	 */
	public function canDo($action)
	{
		$user = Factory::getUser();

		return $user->authorise($action, $this->getAssetName());
	}

	/**
	 * Check if user can view this item.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function canView()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return ((int) $item->state === static::STATE_ENABLED || $this->isOwner());
	}

	/**
	 * Check if user can view this item just in readonly mode.
	 *
	 * @return boolean
	 */
	public function canReadOnly()
	{
		if (!$this->hasId())
		{
			return false;
		}

		return $this->getTable()
			->isLockedByWebservice($this->getId());
	}

	/**
	 * Remove an instance from cache
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  void
	 */
	public static function clearInstance($id = null)
	{
		$class = get_called_class();

		unset(static::$instances[$class][$id]);
	}

	/**
	 * Format a link
	 *
	 * @param   string   $url     Url to format
	 * @param   boolean  $routed  Process Url through Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	protected function formatUrl($url, $routed = true, $xhtml = true)
	{
		if (!$url)
		{
			return null;
		}

		if (!$routed)
		{
			return $url;
		}

		return RedshopbRoute::_($url, $xhtml);
	}

	/**
	 * Get an item property
	 *
	 * @param   string  $property  Property to get
	 * @param   mixed   $default   Default value to assign if property === null | property === ''
	 *
	 * @return  mixed
	 */
	public function get($property, $default = null)
	{
		$item = $this->getItem();

		if (!empty($item) && property_exists($item, $property))
		{
			return ($item->$property !== null && $item->$property !== '') ? $item->$property : $default;
		}

		return $default;
	}

	/**
	 * Get the ACL prefix applied to this class
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getAclPrefix()
	{
		return $this->aclPrefix;
	}

	/**
	 * Get the item add link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getAddLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		$url = $this->getBaseUrl() . '&task=' . $this->getInstanceName() . '.add' . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item add link with a return link to the current page.
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getAddLinkWithReturn($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		$url = $this->getAddLink($itemId, false, false) . '&return=' . base64_encode(Uri::getInstance()->toString());

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the identifier of the project asset
	 *
	 * @return  string
	 */
	protected function getAssetName()
	{
		if ($this->hasId())
		{
			return $this->getComponent() . '.' . $this->getInstanceName() . '.' . $this->id;
		}

		// Use the global permissions
		return $this->getComponent();
	}

	/**
	 * Gets the base URL for tasks/views.
	 *
	 * Example: index.php?option=com_redshopb&view=shop
	 *
	 * @return  string
	 */
	protected function getBaseUrl()
	{
		return 'index.php?option=' . $this->getComponent() . '&view=' . $this->getInstanceName();
	}

	/**
	 * Get the component that contains the tables
	 *
	 * @return  string
	 */
	protected function getComponent()
	{
		if (null === $this->component)
		{
			$this->component = $this->getComponentFromPrefix();
		}

		return $this->component;
	}

	/**
	 * Get the component from the prefix. Ex.: ContentEntityArticle will return com_content
	 *
	 * @return  string
	 */
	protected function getComponentFromPrefix()
	{
		return 'com_' . $this->getPrefix();
	}

	/**
	 * Get an entity date field formatted
	 *
	 * @param   string   $itemProperty     Item property containing the date
	 * @param   string   $format           Desired date format
	 * @param   boolean  $translateFormat  Translate the format for multilanguage purposes
	 *
	 * @return  string
	 */
	public function getDate($itemProperty, $format = 'DATE_FORMAT_LC1', $translateFormat = true)
	{
		$item = $this->getItem();

		if (!$item || !property_exists($item, $itemProperty))
		{
			return null;
		}

		if ($format && $translateFormat)
		{
			$format = Text::_($format);
		}

		return HTMLHelper::_('date', $item->{$itemProperty}, $format);
	}

	/**
	 * Local proxy for Factory::getDbo()
	 *
	 * @return  JDatabaseDriver
	 */
	protected function getDbo()
	{
		return Factory::getDbo();
	}

	/**
	 * Get the item delete link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getDeleteLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$urlToken = '&' . Session::getFormToken() . '=1';

		$url = $this->getBaseUrl() . '&task=' . $this->getInstanceName()
			. '.delete&id=' . $this->getSlug() . $urlToken . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item delete link with a return link to the current page.
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getDeleteLinkWithReturn($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getDeleteLink($itemId, false, false) . '&return=' . base64_encode(Uri::getInstance()->toString());

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item edit link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getEditLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getBaseUrl() . '&task=' . $this->getInstanceName()
			. '.edit&id=' . $this->getSlug() . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item edit link with a return link to the current page.
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getEditLinkWithReturn($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getEditLink($itemId, false, false) . '&return=' . base64_encode(Uri::getInstance()->toString());

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Create and return a cached instance
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function getInstance($id = null)
	{
		if (null === $id)
		{
			return new static;
		}

		$class = get_called_class();

		if (empty(static::$instances[$class][$id]))
		{
			static::$instances[$class][$id] = new static($id);
		}

		return static::$instances[$class][$id];
	}

	/**
	 * Create and return a cached instance by a different field of the table (UID)
	 *
	 * @param   string|array  $fieldName   Field(s) to use
	 * @param   string|array  $fieldValue  Key value(s)
	 *
	 * @return  $this
	 */
	public static function getInstanceByField($fieldName, $fieldValue)
	{
		$instance = static::getInstance();

		return $instance->loadItem($fieldName, $fieldValue);
	}

	/**
	 * Get the name of the current entity type
	 *
	 * @return  string
	 */
	public function getInstanceName()
	{
		$class = get_class($this);

		$name = strstr($class, 'Entity');
		$name = str_replace('Entity', '', $name);

		return strtolower($name);
	}

	/**
	 * Get the id
	 *
	 * @return  integer|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get item from the database
	 *
	 * @return  mixed  Object / null
	 */
	public function getItem()
	{
		if (empty($this->item))
		{
			$this->loadItem();
		}

		return $this->item;
	}

	/**
	 * Get the item link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with Route?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getBaseUrl() . '&id=' . $this->getSlug() . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Generate the Itemid string part for URLs
	 *
	 * @param   mixed  $itemId  inherit or desired itemId. Use 0 to not inherit active itemId
	 *
	 * @return  string
	 */
	protected function getLinkItemIdString($itemId = 'inherit')
	{
		return ($itemId !== 'inherit') ? '&Itemid=' . (int) $itemId : null;
	}

	/**
	 * Get the prefix of this entity.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	protected function getPrefix()
	{
		$class = get_class($this);

		return strtolower(strstr($class, 'Entity', true));
	}

	/**
	 * Generate the item slug for URLs
	 *
	 * @return  string
	 */
	protected function getSlug()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return $this->hasId() ? $this->id : null;
		}

		return !empty($item->alias) ? $this->id . '-' . $item->alias : $this->id;
	}

	/**
	 * Get the associated table
	 *
	 * @param   string  $name  Main name of the Table. Example: Article for ContentTableArticle
	 *
	 * @return  RedshopbTable
	 */
	public function getTable($name = null)
	{
		if (null === $name)
		{
			$class = get_class($this);
			$name  = strstr($class, 'Entity');
		}

		$name = str_replace('Entity', '', $name);

		return RTable::getAdminInstance($name, array(), $this->getComponent());
	}

	/**
	 * Check if we have an identifier loaded
	 *
	 * @return  boolean
	 */
	public function hasId()
	{
		$id = (int) $this->id;

		return !empty($id);
	}

	/**
	 * Check if item has been loaded
	 *
	 * @return  boolean
	 */
	public function isLoaded()
	{
		return ($this->hasId() && $this->item !== null);
	}

	/**
	 * Check if current member is owner
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function isOwner()
	{
		if (!$this->hasId())
		{
			return false;
		}

		$user = Factory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return ($item->created_by == $user->get('id'));
	}

	/**
	 * Basic instance check: has id + loadable item
	 *
	 * @return  boolean
	 */
	public function isValid()
	{
		if (!$this->hasId())
		{
			return false;
		}

		$item = $this->getItem();

		return !empty($item);
	}

	/**
	 * Load a cached instance and ensure that the item is loaded
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function load($id = null)
	{
		$instance = static::getInstance($id);

		if (!$instance->isLoaded())
		{
			$instance->loadItem();
		}

		return $instance;
	}

	/**
	 * Load the item already loaded in a table
	 *
	 * @param   RTable  $table  Table with the item loaded
	 *
	 * @return  self
	 */
	public function loadFromTable($table)
	{
		$key = $table->getKeyName();

		if (!empty($table->{$key}))
		{
			// Get the data from the table
			if (method_exists($table, 'getTableProperties'))
			{
				// Redshopb method to get only public properties
				$data = $table->getTableProperties();
			}
			else
			{
				// Fallback for every other Table (not redshopb tables)
				$data = $table->getProperties(true);
			}

			// Item is always an object
			$this->item = ArrayHelper::toObject($data);

			$this->id    = $table->id;
			$this->table = clone $table;

			$class = get_called_class();

			// Ensure that we cache the item
			if (!isset(static::$instances[$class][$this->id]) || !static::$instances[$class][$this->id]->isLoaded())
			{
				static::$instances[$class][$this->id] = $this;
			}
		}

		return $this;
	}

	/**
	 * Default loading is trying to use the associated table.  Optionally using two arrays.
	 *
	 * @param   string|array  $key       Field name(s) used as key
	 * @param   string|array  $keyValue  Value(s) used if it's not the $this->id property of the instance
	 *
	 * @return  self
	 */
	public function loadItem($key = 'id', $keyValue = null)
	{
		// If trying to get the item using id but no key was provided (or already in place) nothing is get
		if (is_string($key) && $key == 'id' && is_null($keyValue) && !$this->hasId())
		{
			return $this;
		}

		// Data array for getting the table data.  Considering key is a string
		$data = array((is_string($key) ? $key : 'id') => (is_string($key) && $key == 'id') ? $this->id : $keyValue);

			// If array, checks that both parameters match in number of elements
		if (is_array($key))
		{
			if (!is_array($keyValue) || count($key) != count($keyValue))
			{
				return $this;
			}

			$data = array_combine($key, $keyValue);
		}

		$table = $this->getTable();

		if ($table && $table->load($data))
		{
			$this->loadFromTable($table);
		}

		return $this;
	}

	/**
	 * Try to directly save the entity using the associated table
	 *
	 * @param   mixed  $item  Object / Array to save. Null = try to store current item
	 *
	 * @return  integer  The item id
	 *
	 * @throws  RuntimeException  When save failed
	 *
	 * @since   1.0
	 */
	public function save($item = null)
	{
		if (null === $item)
		{
			$item = $this->getItem();
		}

		if (!$item)
		{
			throw new RuntimeException("Nothing to save", 422);
		}

		$table = $this->getTable();

		if (!$table instanceof Table)
		{
			throw new RuntimeException("Table for instance " . $this->getInstanceName() . " could not be loaded", 500);
		}

		if (!$table->save((array) $item))
		{
			throw new RuntimeException("Item could not be saved: " . $table->getError(), 500);
		}

		// Force entity reload / save to cache
		static::clearInstance($this->id);

		static::loadFromTable($table);

		return $table->id;
	}
}
