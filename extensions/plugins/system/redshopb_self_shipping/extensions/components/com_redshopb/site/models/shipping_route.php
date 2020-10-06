<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

/**
 * Shipping Route Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.36
 */
class RedshopbModelShipping_Route extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'address';

	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   mixed    $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		RedshopbForm::addFormPath(JPATH_PLUGINS . '/system/redshopb_self_shipping/extensions/components/com_redshopb/site/models/forms');

		return parent::loadForm($name, $source, $options, $clear, $xpath);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 * @throws Exception
	 */
	public function getItem($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);

		$item = ArrayHelper::toObject($properties, 'JObject');

		// Set the defaults
		$item->addresses = array();

		// This is needed because toObject will transform
		// the addresses ids array to an object.
		if (!empty($properties['addresses']))
		{
			$item->addresses = $properties['addresses'];
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		if (!$item->company_id && !RedshopbHelperACL::isSuperAdmin())
		{
			$item->company_id = RedshopbEntityUser::getCompanyIdForCurrentUser();
		}

		return $item;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		if (!isset($data['weekday_1']))
		{
			$data['weekday_1'] = 0;
		}

		if (!isset($data['weekday_2']))
		{
			$data['weekday_2'] = 0;
		}

		if (!isset($data['weekday_3']))
		{
			$data['weekday_3'] = 0;
		}

		if (!isset($data['weekday_4']))
		{
			$data['weekday_4'] = 0;
		}

		if (!isset($data['weekday_5']))
		{
			$data['weekday_5'] = 0;
		}

		if (!isset($data['weekday_6']))
		{
			$data['weekday_6'] = 0;
		}

		if (!isset($data['weekday_7']))
		{
			$data['weekday_7'] = 0;
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 * @throws Exception
	 */
	public function save($data)
	{
		$db = Factory::getDbo();
		$db->transactionStart();

		$returned = parent::save($data);

		// If this is new shipping route then we will create Debtor Group and shipping rate for it
		if ($returned && $this->getState($this->getName() . '.new', false))
		{
			$company = RedshopbEntityCompany::getInstance($data['company_id'])->loadItem();

			// New Debtor Group
			$debtorGroup = RTable::getInstance('Customer_Price_Group', 'RedshopbTable');

			$debtorGroupCodeTable = clone $debtorGroup;
			$codeBase             = Text::sprintf('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_NEW_DEBTOR_GROUP', $company->get('name'));
			$code                 = $codeBase;
			$codeCounter          = 0;

			// We will go through the items until we get unique SKU
			while ($debtorGroupCodeTable->load(array('code' => $code)))
			{
				$codeCounter++;
				$code                 = $codeBase . '-' . $codeCounter;
				$debtorGroupCodeTable = clone $debtorGroup;
			}

			if (!($debtorGroup->save(
				array(
					'name'  => Text::sprintf('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_NEW_DEBTOR_GROUP', $company->get('name')),
					'code' => $code,
					'company_id' => $company->get('parent_id'),
					'customer_ids' => array($data['company_id']),
					'show_stock_as' => 'not_set',
				)
			)))
			{
				$db->transactionRollback();

				throw new Exception($debtorGroup->getError());
			}

			// New shipping configuration for the debtor group
			$shippingConfiguration = RTable::getInstance('Shipping_Configuration', 'RedshopbTable');
			$plugin                = PluginHelper::getPlugin('system', 'redshopb_self_shipping');
			$params                = new Registry($plugin->params);
			$params->set('shipping_route_id', $this->getState($this->getName() . '.id', 0));
			$params->set('shipping_title', Text::sprintf('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_NEW_DEBTOR_GROUP', $company->get('name')));
			$params->set('shipping_folder', 'system');

			if (!($shippingConfiguration->save(
				array(
					'extension_name' => 'com_redshopb',
					'owner_name' => $debtorGroup->get('id'),
					'shipping_name' => 'redshopb_self_shipping',
					'params' => $params->toArray()
				)
			)))
			{
				$db->transactionRollback();

				throw new Exception($shippingConfiguration->getError());
			}

			// We need to add new shipping rate for this configuration
			$shippingRate = RTable::getInstance('Shipping_Rate', 'RedshopbTable');

			if (!($shippingRate->save(
				array(
					'shipping_configuration_id' => $shippingConfiguration->get('id'),
					'name' => Text::sprintf('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_NEW_DEBTOR_GROUP', $company->get('name')),
				)
			)))
			{
				$db->transactionRollback();

				throw new Exception($shippingRate->getError());
			}
		}

		$db->transactionCommit();

		return $returned;
	}
}
