<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Product Attribute table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Attribute extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_attribute';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  integer
	 */
	public $ordering = 0;

	/**
	 * 0: string, 1: float, 2: int
	 *
	 * @var  integer
	 */
	public $type_id = 0;

	/**
	 * @var  boolean
	 */
	public $main_attribute = 0;

	/**
	 * @var  boolean
	 */
	public $conversion_sets = 0;

	/**
	 * @var  string
	 */
	public $image = '';

	/**
	 * @var  boolean
	 */
	public $enable_sku_value_display = 0;

	/**
	 * @var integer
	 */
	public $state = 1;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * Columns used to generate alias from
	 *
	 * @var  string
	 */
	protected $_aliasColumns = array('product_id', 'name');

	/**
	 * Keys to consider exclusive from the alias (appart from the alias field itself)
	 *
	 * @var  string
	 */
	protected $_aliasKeys = array('product_id');

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'fengel' => array(
			'fengel.type'
		)
	);

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		$isFromWebservice = $this->getOption('forceWebserviceUpdate', false);

		if ($isFromWebservice == false
			&& $this->isFromWebservice('#__redshopb_product', null, $this->product_id))
		{
			$this->setError(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'));
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_ITEM_RELATED_TO_WEBSERVICE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');

			return false;
		}

		if ($this->getOption('disableFlatValidations', false) == false)
		{
			// If this attribute is using flat display and there is another one, disable it for the oteher
			if ($this->main_attribute)
			{
				$attribute = clone $this;
				$attribute->setOption('disableFlatValidations', true);

				if ($attribute->load(array('main_attribute' => 1, 'product_id' => $this->product_id))
					&& $attribute->id != $this->id)
				{
					$attribute->save(array('main_attribute' => 0));
				}
			}
			else
			{
				$attribute = clone $this;
				$attribute->setOption('disableFlatValidations', true);

				if (!($attribute->load(array('main_attribute' => 1, 'product_id' => $this->product_id)) && $attribute->id != $this->id))
				{
					$this->main_attribute = 1;

					if ($isFromWebservice == false)
					{
						Factory::getApplication()->enqueueMessage(sprintf(Text::_('COM_REDSHOPB_ATTRIBUTE_FLAT_ATTRIBUTE'), $this->name));
					}
				}
			}
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$this->type_id                  = (int) $this->type_id;
		$this->name                     = trim($this->name);
		$this->main_attribute           = (int) $this->main_attribute;
		$this->enable_sku_value_display = (int) $this->enable_sku_value_display;

		// Set ordering
		if ($this->state < 0)
		{
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		}
		elseif (empty($this->ordering) && empty($this->id))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->qn('product_id') . '=' . $this->_db->q($this->product_id) . ' AND state >= 0');
		}

		// Check empty name
		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_NAME_CANNOT_BE_EMPTY'));

			return false;
		}

		// Check not already an attribute with this name for this product
		$attribute = clone $this;

		if ($attribute->load(array('name' => $this->name))
			&& $attribute->id != $this->id && $attribute->product_id == $this->product_id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_ATTRIBUTE_ALREADY_EXISTING', $this->name));

			return false;
		}

		return true;
	}
}
