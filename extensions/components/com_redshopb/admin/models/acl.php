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
use Joomla\CMS\Table\Table;

/**
 * Redshop ACL Model
 *
 * @package     Redshop.Component
 * @subpackage  Models.ACL
 * @since       2.0
 *
 */
class RedshopbModelACL extends RedshopbModelAdmin
{
	/**
	 * Store access rights for specific rule
	 *
	 * @var array
	 */
	public static $userAccessList = array();

	/**
	 * Store effective rules
	 *
	 * @var array
	 */
	public static $effectiveRules = array();

	/**
	 * Gets the company/department record with the asset record
	 *
	 * @param   int  $id  Asset Id to recover
	 *
	 * @return  array  Array with asset/company/department object: id, level, company_id, department_id
	 */
	public function getAsset($id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
			array(
					$db->qn('a.id'), $db->qn('a.level'), $db->qn('a.lft'), $db->qn('a.rgt'),
					$db->qn('c.id', 'company_id'), $db->qn('c.type', 'company_type'),
					$db->qn('d.id', 'department_id')
				)
		)
			->from($db->qn('#__assets', 'a'))
			->leftJoin('#__redshopb_company AS c ON c.asset_id = a.id AND ' . $db->qn('c.deleted') . ' = 0')
			->leftJoin('#__redshopb_department AS d ON d.asset_id = a.id AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1')
			->where('(' . $db->qn('c.id') . ' IS NULL or ' . $db->qn('c.id') . ' > 1)')
			->where('(' . $db->qn('d.id') . ' IS NULL or ' . $db->qn('d.id') . ' > 1)')
			->where('NOT (' . $db->qn('c.id') . ' IS NULL AND ' . $db->qn('d.id') . ' IS NULL)')
			->where($db->qn('a.id') . ' = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Gets the effective rule for an access, role and asset Id
	 *
	 * @param   int  $accessId  Access Id
	 * @param   int  $roleId    Role Id
	 * @param   int  $assetId   Asset Id
	 *
	 * @return  object  Rule object
	 */
	public function getEffectiveRule($accessId, $roleId, $assetId)
	{
		$key = $accessId . '_' . $roleId . '_' . $assetId;

		if (!array_key_exists($key, self::$effectiveRules))
		{
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true);
			$query2 = $db->getQuery(true);

			$query2->select(Array('max(' . $db->qn('ap2.level') . ')'))
				->from($db->qn('#__assets', 'ap2'))
				->innerJoin($db->qn('#__redshopb_acl_rule', 'r2') . ' ON ' . $db->qn('ap2.id') . ' = ' . $db->qn('r2.joomla_asset_id'))
				->where($db->qn('ap2.level') . ' <= ' . $db->qn('a.level'))
				->where($db->qn('ap2.lft') . ' <= ' . $db->qn('a.lft'))
				->where($db->qn('ap2.rgt') . ' >= ' . $db->qn('a.rgt'))
				->where($db->qn('r2.role_id') . ' = ' . $db->qn('r.role_id'))
				->where($db->qn('r2.access_id') . ' = ' . $db->qn('r.access_id'));

			$query->select(array($db->qn('r.id'),$db->qn('r.access_id'),$db->qn('r.role_id'),$db->qn('r.joomla_asset_id'),$db->qn('r.granted')))
				->from($db->qn('#__assets', 'a'))
				->innerJoin(
					$db->qn('#__assets', 'ap') . ' ON ' .
					$db->qn('ap.level') . ' <= ' . $db->qn('a.level') .
					' AND ' . $db->qn('ap.lft') . ' <= ' . $db->qn('a.lft') .
					' AND ' . $db->qn('ap.rgt') . ' >= ' . $db->qn('a.rgt')
				)
				->innerJoin($db->qn('#__redshopb_acl_rule', 'r') . ' ON ' . $db->qn('ap.id') . ' = ' . $db->qn('r.joomla_asset_id'))
				->where($db->qn('r.access_id') . ' = ' . (int) $accessId)
				->where($db->qn('r.role_id') . ' = ' . (int) $roleId)
				->where($db->qn('a.id') . ' = ' . (int) $assetId)
				->where($db->qn('ap.level') . ' = (' . $query2->__toString() . ')');

			self::$effectiveRules[$key] = $db->setQuery($query)->loadObject();
		}

		return self::$effectiveRules[$key];
	}

	/**
	 * Determines if a certain grant exists inside a given asset
	 *
	 * @param   int  $accessId  Access Id
	 * @param   int  $roleId    Role Id
	 * @param   int  $assetId   Asset Id
	 *
	 * @return  object  Rule object
	 */
	public function grantExistsInside($accessId, $roleId, $assetId)
	{
		static $grants = array();

		$key = md5($accessId . '.' . $roleId . '.' . $assetId);

		if (array_key_exists($key, $grants))
		{
			return $grants[$key];
		}

		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$query2 = $db->getQuery(true);

		$query2->select($db->qn('a.id'))
			->from($db->qn('#__assets', 'a'))
			->innerJoin(
				$db->qn('#__assets', 'ap') . ' ON ' .
					$db->qn('ap.level') . ' <= ' . $db->qn('a.level') .
					' AND ' . $db->qn('ap.lft') . ' <= ' . $db->qn('a.lft') .
					' AND ' . $db->qn('ap.rgt') . ' >= ' . $db->qn('a.rgt')
			)
			->where($db->qn('ap.id') . ' = ' . (int) $assetId);

		$query->select($db->qn('r.granted'))
			->from($db->qn('#__redshopb_acl_rule', 'r'))
			->where($db->qn('r.access_id') . ' = ' . (int) $accessId)
			->where($db->qn('r.role_id') . ' = ' . (int) $roleId)
			->where($db->qn('r.granted') . ' = 1')
			->where($db->qn('r.joomla_asset_id') . ' IN (' . $query2->__toString() . ')');
		$db->setQuery($query);

		$res = $db->loadResult();

		$grants[$key] = ($res == 1);

		return $grants[$key];
	}

	/**
	 * Gets the rule collection for a role, asset and optionally an access section
	 *
	 * @param   int     $roleId       Rold Id to select the rules from
	 * @param   int     $assetId      Asset Id to check the rules on
	 * @param   string  $sectionName  (optional)  Name of the section to limit results from
	 * @param   string  $simpleUX     (optional)  'true' if it has to search for the simple UX
	 * @param   int     $accessId     (optional)  Limit to a specific access ID to test (single record)
	 *
	 * @return  array  Array of effective rules objects: section_name, access_id,
	 *                 access_name, access_title, access_description, granted
	 */
	public function getRuleCollection($roleId, $assetId, $sectionName = '', $simpleUX = '', $accessId = 0)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(
			array(
					$db->qn('s.name', 'section_name'),
					$db->qn('ac.id', 'access_id'),
					$db->qn('ac.name', 'access_name'),
					$db->qn('ac.title', 'access_title'),
					$db->qn('ac.description', 'access_description'),
					'0 AS granted'
					)
		)
			->from($db->qn('#__redshopb_acl_access', 'ac'))
			->innerJoin($db->qn('#__redshopb_acl_section', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ac.section_id'))
			->order(array('ac.name'));

		if ($sectionName != '')
		{
			$query->where($db->qn('s.name') . ' = ' . $db->q($sectionName));
		}

		if ($simpleUX == 'true')
		{
			$query->where($db->qn('ac.simple') . ' = 1');
		}

		if ($accessId)
		{
			$query->where($db->qn('ac.id') . ' = ' . $accessId);
		}

		$db->setQuery($query);

		$rules = $db->loadObjectList();

		if ($rules)
		{
			foreach ($rules as $id => $rule)
			{
				$granted            = 1;
				$scope              = '';
				$wholeRuleUndefined = true;

				$query->clear()
					->select(array($db->qn('eac.id'), $db->qn('sax.scope')))
					->from($db->qn('#__redshopb_acl_access', 'ac'))
					->join(
						'inner', $db->qn('#__redshopb_acl_simple_access_xref', 'sax')
						. ' ON ' . $db->qn('ac.id') . ' = ' . $db->qn('sax.simple_access_id')
					)
					->join('inner', $db->qn('#__redshopb_acl_access', 'eac') . ' ON ' . $db->qn('eac.id') . ' = ' . $db->qn('sax.access_id'))
					->join('inner', $db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('sax.role_type_id'))
					->where($db->qn('ac.id') . ' = ' . $rule->access_id)
					->where($db->qn('r.id') . ' = ' . $roleId);
				$db->setQuery($query);

				$effectiveAccesses = $db->loadObjectList();

				if ($effectiveAccesses)
				{
					foreach ($effectiveAccesses as $effectiveAccess)
					{
						$ruleUndefined = false;
						$query->clear();

						switch ($effectiveAccess->scope)
						{
							case 'global':
								$query2 = $db->getQuery(true);
								$query2->select($db->qn('rb2b') . '.*')
									->from($db->qn('#__redshopb_acl_rule', 'rb2b'))
									->join(
										'inner', $db->qn('#__assets', 'ab2b') . ' ON ' . $db->qn('rb2b.joomla_asset_id') . ' = ' . $db->qn('ab2b.id')
									)
									->where($db->qn('ab2b.name') . ' = ' . $db->q('com_redshopb'));

								$query->select('IFNULL(' . $db->qn('r.granted') . ', 0) AS granted')
									->from($db->qn('#__redshopb_acl_access', 'ac'))
									->join(
										'left', '(' . $query2->__toString() . ') AS r' .
											' ON ' . $db->qn('r.access_id') . ' = ' . $db->qn('ac.id') .
											' AND ' . $db->qn('r.role_id') . ' = ' . $roleId
									)
									->where($db->qn('ac.id') . ' = ' . $effectiveAccess->id);

								$scope = 'global';
								break;

							case 'company':
								$query->select('IFNULL(' . $db->qn('r.granted') . ', 0) AS granted')
									->from($db->qn('#__redshopb_acl_access', 'ac'))
									->join(
										'left', $db->qn('#__redshopb_acl_rule', 'r')
											. ' ON ' . $db->qn('r.access_id') . ' = ' . $db->qn('ac.id')
											. ' AND ' . $db->qn('r.joomla_asset_id') . ' = ' . $assetId
											. ' AND ' . $db->qn('r.role_id') . ' = ' . $roleId
									)
									->where($db->qn('ac.id') . ' = ' . $effectiveAccess->id);

								if ($scope != 'global')
								{
									$scope = 'company';
								}
								break;

							case 'department':
								$query->select('ad.id')
									->from($db->qn('#__assets', 'ad'))
									->join('inner', $db->qn('#__redshopb_department', 'd') . ' ON ' .
										$db->qn('d.asset_id') . ' = ' . $db->qn('ad.id') . ' AND ' . $db->qn('d.deleted') . ' = 0'
									)
									->join(
										'inner', $db->qn('#__assets', 'a')
											. ' ON ' . $db->qn('a.lft') . ' < ' . $db->qn('ad.lft')
											. ' AND ' . $db->qn('a.rgt') . ' > ' . $db->qn('ad.rgt')
											. ' AND ' . $db->qn('a.level') . ' = ' . $db->qn('ad.level') . ' - 1'
									)
									->where($db->qn('a.id') . ' = ' . $assetId);
								$db->setQuery($query);

								if ($db->loadResult())
								{
									$query2 = $db->getQuery(true);
									$query2->select('1')
										->from($db->qn('#__assets', 'ad'))
										->join('inner', $db->qn('#__redshopb_department', 'd') . ' ON ' .
											$db->qn('d.asset_id') . ' = ' . $db->qn('ad.id') . ' AND ' . $db->qn('d.deleted') . ' = 0'
										)
										->join(
											'left', $db->qn('#__redshopb_acl_rule', 'r')
												. ' ON ' . $db->qn('r.access_id') . ' = ' . $effectiveAccess->id
												. ' AND ' . $db->qn('r.joomla_asset_id') . ' = ' . $db->qn('ad.id')
												. ' AND ' . $db->qn('r.role_id') . ' = ' . $roleId
										)
										->where($db->qn('a.lft') . ' < ' . $db->qn('ad.lft'))
										->where($db->qn('a.rgt') . ' > ' . $db->qn('ad.rgt'))
										->where($db->qn('a.level') . ' = ' . $db->qn('ad.level') . ' - 1')
										->where('(' . $db->qn('r.granted') . ' = 0 OR ' . $db->qn('r.granted') . ' IS NULL)');

									$query->clear()
										->select('1 AS granted')
										->from($db->qn('#__assets', 'a'))
										->where('NOT EXISTS (' . $query2->__toString() . ')')
										->where($db->qn('a.id') . ' = ' . $assetId);
								}
								else
								{
									$ruleUndefined = true;
								}

								if ($scope != 'global' && $scope != 'company')
								{
									$scope = 'department';
								}
						}

						if ($ruleUndefined && $wholeRuleUndefined)
						{
							$granted = -1;
						}
						else
						{
							$wholeRuleUndefined = false;

							$db->setQuery($query);
							$grantedER = $db->loadResult();

							// If one of the effective rules is not granted, the whole rule is considered as not granted
							if (!$grantedER)
							{
								$granted = 0;
							}

							if ($granted == -1)
							{
								$granted = $grantedER;
							}
						}
					}
				}
				else
				{
					$granted = 0;
				}

				if ($wholeRuleUndefined)
				{
					$granted = -1;
				}

				$rules[$id]->granted = $granted;
				$rules[$id]->scope   = $scope;
			}
		}

		return $rules;
	}

	/**
	 * Gets the access set, setting optionally an access section
	 *
	 * @param   int  $sectionName  (optional)  Name of the section to limit results from
	 *
	 * @return  array  Array of access objects: section_name, access_id, access_name, access_title, access_description
	 */
	public function getAccessSet($sectionName = '')
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('s.name', 'section_name'),
				$db->qn('ac.id', 'access_id'),
				$db->qn('ac.name', 'access_name'),
				$db->qn('ac.title', 'access_title'),
				$db->qn('ac.description', 'access_description')
				)
		)
			->from($db->qn('#__redshopb_acl_access', 'ac'))
			->innerJoin($db->qn('#__redshopb_acl_section', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ac.section_id'))
			->order(array('ac.section_id', 'ac.name'));

		if ($sectionName != '')
		{
			$query->where($db->qn('s.name') . ' = ' . $db->quote($sectionName));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Gets the company/department record with the asset record
	 *
	 * @param   int     $id     Asset Id to recover childs from
	 * @param   int     $level  Levels to recover childs from (0 means all levels above)
	 * @param   string  $type   (optional) Asset type: C = company, D = department
	 *
	 * @return  array  Array with asset/company/department object: id, level, company_id, department_id
	 */
	public function getChildAssets($id, $level, $type = '')
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
			array(
					$db->qn('a.id'), $db->qn('a.level'), $db->qn('a.lft'), $db->qn('a.rgt'),
					$db->qn('c.id', 'company_id'), $db->qn('c.type', 'company_type'),
					$db->qn('d.id', 'department_id')
				)
		)
			->from($db->qn('#__assets', 'a'));

		if ($level)
		{
			// Limits to n levels below
			$query->innerJoin(
				$db->qn('#__assets', 'pa') . ' ON '
				. $db->qn('pa.lft') . ' < ' . $db->qn('a.lft') . ' AND ' . $db->qn('pa.rgt') . ' > ' . $db->qn('a.rgt')
				. ' AND ' . $db->qn('pa.level') . ' = ' . $db->qn('a.level') . ' - ' . (int) $level
			);
		}
		else
		{
			// Does not limit levels below
			$query->innerJoin(
				$db->qn('#__assets', 'pa') . ' ON ' . $db->qn('pa.level') . ' < ' . $db->qn('a.level') .
						' AND ' . $db->qn('pa.lft') . ' < ' . $db->qn('a.lft') . ' AND ' . $db->qn('pa.rgt') . ' > ' . $db->qn('a.rgt')
			);
		}

		switch ($type)
		{
			case 'C':
				$query->innerJoin('#__redshopb_company AS c ON c.asset_id = a.id AND ' . $db->qn('c.deleted') . ' = 0')
					->leftJoin('#__redshopb_department AS d ON d.asset_id = a.id AND ' . $db->qn('d.deleted') . ' = 0');
				break;
			case 'D':
				$query->leftJoin('#__redshopb_company AS c ON c.asset_id = a.id AND ' . $db->qn('c.deleted') . ' = 0')
					->innerJoin('#__redshopb_department AS d ON d.asset_id = a.id AND ' . $db->qn('d.deleted') . ' = 0');
				break;
			default:
				$query->leftJoin('#__redshopb_company AS c ON c.asset_id = a.id AND ' . $db->qn('c.deleted') . ' = 0')
					->leftJoin('#__redshopb_department AS d ON d.asset_id = a.id AND ' . $db->qn('d.deleted') . ' = 0');
		};

		$query->where('(' . $db->qn('c.id') . ' IS NULL or ' . $db->qn('c.id') . ' > 1)')
			->where('(' . $db->qn('d.id') . ' IS NULL or ' . $db->qn('d.id') . ' > 1)')
			->where('NOT (c.id IS NULL AND ' . $db->qn('d.id') . ' IS NULL)')
			->where($db->qn('pa.id') . ' = ' . (int) $id)
			->where($db->qn('a.level') . ' > 0');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Gets all company roles
	 *
	 * @return  array    Array with B2B roles found (mixed with asset tree): id, joomla_group_id, allowed_rules,
	 *                   allowed_rules_main_company, allowed_rules_customers, allowed_rules_company,
	 *                   allowed_rules_own_company, allowed_rules_department, asset_id, level, company_id
	 */
	public function getCompanyRolesTree()
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
			array(
					$db->qn('r.id'),
					$db->qn('r.joomla_group_id'),
					$db->qn('rt.allowed_rules'),
					$db->qn('rt.allowed_rules_main_company'),
					$db->qn('rt.allowed_rules_customers'),
					$db->qn('rt.allowed_rules_company'),
					$db->qn('rt.allowed_rules_own_company'),
					$db->qn('rt.allowed_rules_department'),
					$db->qn('a.id', 'asset_id'),
					$db->qn('a.level'),
					$db->qn('a.lft'),
					$db->qn('a.rgt'),
					$db->qn('c.id', 'company_id'))
		)
			->from($db->qn('#__assets', 'ra'))
			->from($db->qn('#__assets', 'a'))
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.asset_id')
				. ' = ' . $db->qn('a.id') . ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.company_id') . ' = ' . $db->qn('c.id'))
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
			->where($db->qn('ra.name') . ' = ' . $db->quote('com_redshopb'))
			->where($db->qn('a.lft') . ' > ' . $db->qn('ra.lft'))
			->where($db->qn('a.rgt') . ' < ' . $db->qn('ra.rgt'))
			->where($db->qn('c.id') . ' > 1')
			->where($db->qn('rt.company_role') . ' = 0')
			->order($db->qn('a.lft'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Gets access list
	 *
	 * @param   string  $accessList   (optional) JSON filter of access list
	 * @param   string  $sectionName  (optional) section to filter from
	 *
	 * @return  array    Array with ACL access objects: id, name, title, description, section_name
	 */
	public function getAccessList($accessList = 'all', $sectionName = '')
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(array($db->qn('a.id'), $db->qn('a.name'), $db->qn('a.title'), $db->qn('a.description'), $db->qn('s.name')))
			->from($db->qn('#__redshopb_acl_access', 'a'))
			->innerJoin($db->qn('#__redshopb_acl_section', 's') . ' ON s.id = a.section_id');

		// If access list filter is set, filters
		if ($accessList != 'all' && $accessList != '')
		{
			$query->where($db->qn('a.name') . " IN ('" . implode("','", json_decode($accessList)) . "')");
		}
		// If access list filter is set empty, filters
		elseif ($accessList == '')
		{
			$query->where($db->qn('a.name') . ' = ' . $db->quote($accessList));
		}

		if ($sectionName != '')
		{
			$query->where($db->qn('s.name') . ' = ' . $db->quote($sectionName));
		}

		$query->order($db->qn('s.id') . ', ' . $db->qn('a.name'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Gets single access (object)
	 *
	 * @param   string  $access  Name of the access to get
	 *
	 * @return  object  ACL access object: id, name, title, description, section_name
	 */
	public function getSingleAccess($access)
	{
		if (empty(self::$userAccessList))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array($db->qn('a.id'), $db->qn('a.name'), $db->qn('a.title'), $db->qn('a.description'), $db->qn('s.name', 'section_name'))
				)
				->from($db->qn('#__redshopb_acl_access', 'a'))
				->innerJoin($db->qn('#__redshopb_acl_section', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('a.section_id'));
			$db->setQuery($query);

			self::$userAccessList = $db->loadObjectList('name');
		}

		return array_key_exists($access, self::$userAccessList) ? self::$userAccessList[$access] : null;
	}

	/**
	 * Resets (denies) all access rules for a role
	 *
	 * @param   integer  $roleId  ID of the role to reset
	 *
	 * @return  void
	 */
	public function resetAccessRules($roleId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->qn('#__redshopb_acl_rule'))
			->set($db->qn('granted') . ' = 0')
			->where($db->qn('role_id') . ' = ' . (int) $roleId);
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Empties all ACL Rules
	 *
	 * @return  void
	 */
	public function emptyACLRules()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->delete('#__redshopb_acl_rule');
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Gets all companies regardless of state
	 *
	 * @return  array  Companies IDs
	 */
	public function getAllCompanies()
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(Array($db->qn('id')))
			->from('#__redshopb_company')
			->where($db->qn('id') . ' > 1')
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);

		return $db->loadColumn(0);
	}

	/**
	 * Gets company record
	 *
	 * @param   int  $id  Company ID
	 *
	 * @return  array  Company object: id, asset_id
	 */
	public function getCompany($id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(Array($db->qn('id'), $db->qn('asset_id')))
			->from('#__redshopb_company')
			->where($db->qn('id') . ' = ' . (int) $id)
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Gets all departments regardless of state
	 *
	 * @return  array  Departments IDs
	 */
	public function getAllDepartments()
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(Array($db->qn('id')))
			->from('#__redshopb_department')
			->where($db->qn('id') . ' > 1')
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);

		return $db->loadColumn(0);
	}

	/**
	 * Gets department record
	 *
	 * @param   int  $id  Department ID
	 *
	 * @return  array  Department object: id, asset_id
	 */
	public function getDepartment($id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select(Array($db->qn('id'), $db->qn('asset_id')))
			->from('#__redshopb_department')
			->where($db->qn('id') . ' = ' . (int) $id)
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'Acl_rule', $prefix = '', $config = array())
	{
		// Simple line just to avoid Travis warning because useless override.  It is not useless since it's re-defining the default $name parameter
		$i = 0;

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		// Process saved ruleset before table saving (as it could fail if locked by erp)
		if (isset($data['acl_ruleset']))
		{
			$res = true;

			foreach ($data['acl_ruleset'] as $roleId => $rulesAccess)
			{
				foreach ($rulesAccess as $accessId => $roleAccess)
				{
					$permission = ($roleAccess == '' ? null : $roleAccess);
					$res        = $res && RedshopbHelperACL::grantACLRule($accessId, $roleId, $data['asset_id'], $permission);
				}
			}

			return $res;
		}

		return true;
	}
}
