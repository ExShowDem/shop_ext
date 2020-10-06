<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

require_once __DIR__ . '/base.php';

/**
 * SetCollection function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetCollection extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.collection';

	/**
	 * Send data in Set webservice, then store
	 *
	 * @param   object   $table        Table class from current item
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function store(&$table = null, $updateNulls = false)
	{
		$db = Factory::getDbo();

		try
		{
			// We do not want to translate
			$db->translate = false;

			$collectionNo = '';

			$subQuery = $db->getQuery(true)
				->select('parent.customer_number')
				->from($db->qn('#__redshopb_company', 'parent'))
				->where($db->qn('parent.deleted') . ' = 0')
				->leftJoin(
					$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
				)
				->where('parent.type = ' . $db->q('customer'))
				->where('node.id = c.id')
				->group('parent.id')
				->order('parent.lft DESC');

			$query = $db->getQuery(true)
				->select(
					array(
						'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
						'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), c.customer_number) AS customerNo'
					)
				)
				->from($db->qn('#__redshopb_company', 'c'))
				->where($db->qn('c.deleted') . ' = 0')
				->where('c.id = ' . (int) $table->get('company_id'));

			$customer = $db->setQuery($query)->loadObject();

			if (!$customer)
			{
				$customer                = new stdClass;
				$customer->endCustomerNo = '';
				$customer->customerNo    = '';
			}

			if ($customer->endCustomerNo)
			{
				$companyNo = $customer->endCustomerNo;
			}
			else
			{
				$companyNo = $customer->customerNo;
			}

			if ($table->get('id'))
			{
				$query->clear()
					->select(array($db->qn('s.remote_key')))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.local_id = ' . (int) $table->get('id'))
					->where('s.remote_parent_key = ' . $db->q($companyNo));

				$collectionNo = $db->setQuery($query)->loadResult();

				if ($collectionNo)
				{
					$setXml = '<status>update</status>';
				}
				else
				{
					$setXml = '<status>create</status>';
				}
			}
			else
			{
				$setXml = '<status>create</status>';
			}

			$departmentsString = '';
			$query             = $this->getDepartments($table->get('department_ids'));

			$departments = $db->setQuery($query)->loadObjectList('id');

			if ($departments)
			{
				foreach ($departments as $department)
				{
					$departmentsString .=
						'<Department>'
							. '<CustomerNo>' . $department->customerNo . '</CustomerNo>'
							. '<EndCustomer>' . $department->endCustomerNo . '</EndCustomer>'
							. '<DepartmentNo>' . $department->department_number . '</DepartmentNo>'
							. '<DepartmentID>' . $department->DepartmentID . '</DepartmentID>'
							. '<ActiveFromDate/>'
							. '<ActiveToDate/>'
						. '</Department>';
				}
			}

			$cloneTable = clone $table;
			$cloneTable->load((int) $table->get('id'), true);

			$productsString = '';

			if (!$table->get('product_ids') || !is_array($table->get('product_ids')))
			{
				$table->loadProductXref();
			}

			if (!$table->get('product_item_ids') || !is_array($table->get('product_item_ids')))
			{
				$table->loadProductItemXref();
			}
			elseif ($table->getOption('product_items.store', false) == true && $table->getOption('product_items.update_only_state', false) == true)
			{
				if (!$table->storeProductItemXref())
				{
					throw new Exception($table->getError());
				}

				$table->loadProductItemXref();

				$table->setOption('product_items.store', false);
			}

			if ($table->get('product_ids') && is_array($table->get('product_ids')) && count($table->get('product_ids')) > 0)
			{
				$productIds = array();

				foreach ($table->get('product_ids') as $productIdArray)
				{
					$productIds[] = $productIdArray['id'];
				}

				$query->clear()
					->select(array('p.*', 's.serialize'))
					->from($db->qn('#__redshopb_product', 'p'))
					->innerJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = p.id AND s.reference = ' . $db->q('fengel.product'))
					->where('p.id IN (' . implode(',', $productIds) . ')');

				$products = $db->setQuery($query)->loadObjectList('id');

				if ($products)
				{
					if ($table->get('product_item_ids') && is_array($table->get('product_item_ids')) && count($table->get('product_item_ids')) > 0)
					{
						$productItemIds = array();

						foreach ($table->get('product_item_ids') as $productItemIdArray)
						{
							$productItemIds[] = $productItemIdArray['id'];
						}

						// Select colors
						$query->clear()
							->select(
								array('pav.*', 'pi.product_id', $db->qn('wpix.state', 'collection_product_item_state'),
									'wpix.price', $db->qn('wpix.product_item_id'))
							)
							->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
							->leftJoin(
								$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON pav.id = piavx.product_attribute_value_id'
							)
							->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = piavx.product_item_id')
							->innerJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
							->leftJoin(
								$db->qn('#__redshopb_collection_product_item_xref', 'wpix')
								. 'ON wpix.product_item_id = pi.id AND wpix.collection_id = ' . (int) $table->get('id')
							)
							->innerJoin(
								$db->qn('#__redshopb_sync', 's') . ' ON s.local_id = wpix.product_item_id AND s.reference = ' .
								$db->q('fengel.item_related')
							)
							->where('pi.id IN (' . implode(',', $productItemIds) . ')')
							->where('pa.main_attribute = 1')
							->group('pav.id');

						$colors = $db->setQuery($query)->loadObjectList();

						if ($colors)
						{
							foreach ($colors as $color)
							{
								if (!isset($products[$color->product_id]->colors))
								{
									$products[$color->product_id]->colors = array();
								}

								$products[$color->product_id]->colors[$color->id] = $color;
							}

							if ($table->get('id'))
							{
								$query->clear()
									->select(array('pia.*', 'p.sku', 'pa.product_id'))
									->from($db->qn('#__redshopb_product_item_accessory', 'pia'))
									->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = pia.accessory_product_id')
									->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = pia.attribute_value_id')
									->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
									->where('collection_id = ' . (int) $table->get('id'));

								$accessories = $db->setQuery($query)->loadObjectList();

								if ($accessories)
								{
									foreach ($accessories as $accessory)
									{
										if (!isset($products[$accessory->product_id]->colors[$accessory->attribute_value_id]->accessories))
										{
											$products[$accessory->product_id]->colors[$accessory->attribute_value_id]->accessories = array();
										}

										$products[$accessory->product_id]->colors[$accessory->attribute_value_id]->accessories[] = $accessory;
									}
								}
							}

							$productItemIds = $table->get('product_item_ids');

							foreach ($products as $product)
							{
								$serialize  = RedshopbHelperSync::mbUnserialize($product->serialize);
								$colorTable = '';

								if (isset($serialize['Colour']))
								{
									$colorTable = $serialize['Colour'];
								}

								if (isset($product->colors) && count($product->colors) > 0)
								{
									foreach ($product->colors as $color)
									{
										$colorPrice = '0';

										if ($table->getOption('product_items.update_only_price', false))
										{
											if (isset($productItemIds[$color->product_item_id]))
											{
												$color->price = $productItemIds[$color->product_item_id]['price'];
											}
										}

										if (!is_null($color->price))
										{
											$colorPrice = number_format($color->price, '2', ',', '.');
										}

										$colorActive = 'true';

										if (!is_null($color->collection_product_item_state))
										{
											$colorActive = $color->collection_product_item_state ? 'true' : 'false';
										}

										$productsString .=
											'<CollectionItem>'
												. '<ItemNo>' . $product->sku . '</ItemNo>'
												. '<ColorCode>' . $color->sku . '</ColorCode>'
												. '<ColorTable>' . $colorTable . '</ColorTable>'
												. '<Active>' . $colorActive . '</Active>'
												. '<PointPrice>' . $colorPrice . '</PointPrice>';

										if (isset($color->accessories) && count($color->accessories) > 0)
										{
											$productsString .= '<CollectionItemServices>';

											foreach ($color->accessories as $accessory)
											{
												$price = number_format($accessory->price, '2', ',', '.');

												switch ($accessory->selection)
												{
													case 'require':
														$selection = 'Påkrævet';
														break;
													case 'proposed':
														$selection = 'Foreslået';
														break;
													case 'optional':
														$selection = 'Valgfri';
														break;
													default:
														$selection = $accessory->selection;
												}

												$description     = $accessory->description;
												$productsString .=
												'<CollectionItemService>'
												. '<ServiceItemNo>' . $accessory->sku . '</ServiceItemNo>'
												. '<LineNo/>'
												. '<Description>' . $description . '</Description>'
												. '<Selection>' . $selection . '</Selection>'
												. '<HideOnCollection>' . ($accessory->hide_on_collection ? 'true' : 'false') . '</HideOnCollection>'
												. '<ServiceText001/>'
												. '<ServicePrice>' . $price . '</ServicePrice>'
												. '</CollectionItemService>';
											}

											$productsString .= '</CollectionItemServices>';
										}

										$productsString .= '</CollectionItem>';
									}
								}
							}
						}
					}
				}
			}

			$query->clear()
				->select('alpha3')
				->from($db->qn('#__redshopb_currency'))
				->where('id = ' . (int) $table->get('currency_id'));
			$db->setQuery($query);
			$currencyCode = $db->loadResult();

			$setXml .= '<CollectionNo>' . $collectionNo . '</CollectionNo>'
				. '<Name>' . $table->get('name') . '</Name>'
				. '<CollectionPriceGroup/>'
				. '<CollectionCurrency>' . $currencyCode . '</CollectionCurrency>'
				. '<Active>' . ($table->get('state') ? 'true' : 'false') . '</Active>'
				. '<CollectionItems>'
					. $productsString
				. '</CollectionItems>';

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><Collections><Collection>' . $setXml . '</Collection></Collections>';
			$xml    = $this->client->SetCollection($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
				}

				$table->setOption('disableOnBeforeRedshopb', true);

				if (!$table->store($updateNulls))
				{
					throw new Exception;
				}

				$table->setOption('disableOnBeforeRedshopb', false);

				if (isset($xml->Collections->CollectionNo) && !$this->findSyncedId($this->syncName, $xml->Collections->CollectionNo, $companyNo))
				{
					// Insert new item in sync table
					$this->recordSyncedId($this->syncName, $xml->Collections->CollectionNo, $table->get('id'), $companyNo);
				}

				if (isset($xml->Collections->CollectionNo))
				{
					$setXml         = '';
					$oldDepartments = $cloneTable->get('department_ids');

					if ($oldDepartments)
					{
						foreach ($oldDepartments as $oldDepartment)
						{
							if (!isset($departments[$oldDepartment]))
							{
								$query = $this->getDepartments(array((int) $oldDepartment));

								$objectOldDepartment = $db->setQuery($query)->loadObject();

								if ($objectOldDepartment)
								{
									$setXml .= $this->setCollectionLink(
										$customer, $objectOldDepartment, (string) $xml->Collections->CollectionNo, 'delete'
									);
								}
							}
							else
							{
								unset($departments[$oldDepartment]);
							}
						}
					}

					if ($departments && count($departments) > 0)
					{
						foreach ($departments as $department)
						{
							$setXml .= $this->setCollectionLink($customer, $department, (string) $xml->Collections->CollectionNo, 'create');
						}
					}

					if ($setXml)
					{
						$setXml = '<?xml version="1.0" encoding="UTF-8"?><CollectionLinks>' . $setXml . '</CollectionLinks>';
						$xml    = $this->client->SetCollectionLink($setXml);

						if (!$xml)
						{
							throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
						}
						else
						{
							if (isset($xml->Errottext))
							{
								throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
							}
						}
					}
				}
			}

			// We put translation check back on
			$db->translate = true;
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * getDepartments
	 *
	 * @param   array  $departments  Array departments
	 *
	 * @return JDatabaseQuery
	 */
	public function getDepartments($departments)
	{
		$departments = ArrayHelper::toInteger($departments);
		$db          = Factory::getDbo();
		$subQuery    = $db->getQuery(true)
			->select('parent.customer_number')
			->from($db->qn('#__redshopb_company', 'parent'))
			->where($db->qn('parent.deleted') . ' = 0')
			->leftJoin(
				$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
			)
			->where('parent.type = ' . $db->q('customer'))
			->where('d.company_id = node.id')
			->group('parent.id')
			->order('parent.lft DESC');

		$query = $db->getQuery(true)
			->select(
				array(
					'd.*',
					$db->qn('s4.remote_key', 'DepartmentID'),
					'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
					'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), c.customer_number) AS customerNo'
				)
			)
			->from($db->qn('#__redshopb_department', 'd'))
			->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1')
			->leftJoin($db->qn('#__redshopb_sync', 's4') . ' ON s4.local_id = d.id AND s4.reference = ' . $db->q('fengel.department'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = d.company_id AND ' . $db->qn('c.deleted') . ' = 0')
			->where('d.id IN (' . implode(',', $departments) . ')');

		return $query;
	}

	/**
	 * Set collection Link
	 *
	 * @param   object  $customer      Customer data
	 * @param   object  $department    Department data
	 * @param   string  $collectionNo  No current collection
	 * @param   string  $status        Command for execute
	 *
	 * @return string
	 */
	public function setCollectionLink($customer, $department, $collectionNo, $status)
	{
		return '<CollectionLink>'
			. '<status>' . $status . '</status>'
			. '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
			. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>'
			. '<DepartmentNo>' . $department->department_number . '</DepartmentNo>'
			. '<DepartmentID>' . $department->DepartmentID . '</DepartmentID>'
			. '<CollectionNo>' . $collectionNo . '</CollectionNo>'
			. '</CollectionLink>';
	}

	/**
	 * Send query to delete item in Set webservice, then delete in DB
	 *
	 * @param   object   $table  Table class from current item
	 * @param   integer  $pk     The primary key of the node to delete.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($table, $pk = null)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();
			$setXml = '';

			$subQuery = $db->getQuery(true)
				->select('parent.customer_number')
				->from($db->qn('#__redshopb_company', 'parent'))
				->where($db->qn('parent.deleted') . ' = 0')
				->leftJoin(
					$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
				)
				->where('parent.type = ' . $db->q('customer'))
				->where('node.id = c.id')
				->group('parent.id')
				->order('parent.lft DESC');

			$query = $db->getQuery(true)
				->select(
					array(
						'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
						'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), c.customer_number) AS customerNo'
					)
				)
				->from($db->qn('#__redshopb_company', 'c'))
				->where($db->qn('c.deleted') . ' = 0')
				->where('c.id = ' . (int) $table->get('company_id'));

			$customer = $db->setQuery($query)->loadObject();

			if (!$customer)
			{
				$customer                = new stdClass;
				$customer->endCustomerNo = '';
				$customer->customerNo    = '';
			}

			if ($customer->endCustomerNo)
			{
				$companyNo = $customer->endCustomerNo;
			}
			else
			{
				$companyNo = $customer->customerNo;
			}

			$query->clear()
				->select(array($db->qn('s.remote_key')))
				->from($db->qn('#__redshopb_sync', 's'))
				->where('s.reference = ' . $db->q($this->syncName))
				->where('s.local_id = ' . (int) $table->get('id'))
				->where('s.remote_parent_key = ' . $db->q($companyNo));

			$collectionNo = $db->setQuery($query)->loadResult();

			if ($collectionNo)
			{
				$setXml .= '<status>delete</status>'
					. '<CollectionNo>' . $collectionNo . '</CollectionNo>';

				$setXml = '<?xml version="1.0" encoding="UTF-8"?><Collections><Collection>' . $setXml . '</Collection></Collections>';
				$xml    = $this->client->SetCollection($setXml);

				if (!$xml)
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
				}
				else
				{
					if (isset($xml->Errottext))
					{
						throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
					}
				}
			}

			$this->deleteSyncedId($this->syncName, $collectionNo, $companyNo);

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
	}
}
