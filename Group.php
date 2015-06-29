<?php
/**
 * HUBzero CMS
 *
 * Copyright 2009-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Nicholas J. Kisseberth <nkissebe@purdue.edu>
 * @copyright Copyright 2009-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\User;

use Hubzero\Base\Object;
use Hubzero\Utility\Validate;
use Hubzero\Utility\String;

/**
 * Group model
*/
class Group extends Object
{
	/**
	 * Group ID
	 *
	 * @var integer
	 */
	private $gidNumber = null;

	/**
	 * Group alias
	 *
	 * @var string
	 */
	private $cn = null;

	/**
	 * Group title
	 *
	 * @var string
	 */
	private $description = null;

	/**
	 * Description for 'published'
	 *
	 * @var unknown
	 */
	private $published = null;

	/**
	 * Description for 'approved'
	 *
	 * @var integer
	 */
	private $approved = null;

	/**
	 * Group type
	 *
	 * @var integer
	 */
	private $type = null;

	/**
	 * Description for 'public_desc'
	 *
	 * @var unknown
	 */
	private $public_desc = null;

	/**
	 * Description for 'private_desc'
	 *
	 * @var unknown
	 */
	private $private_desc = null;

	/**
	 * Description for 'restrict_msg'
	 *
	 * @var unknown
	 */
	private $restrict_msg = null;

	/**
	 * Description for 'join_policy'
	 *
	 * @var unknown
	 */
	private $join_policy = null;

	/**
	 * Description for 'discoverability'
	 *
	 * @var unknown
	 */
	private $discoverability = null;

	/**
	 * Description for 'discussion_email_autosubscribe'
	 *
	 * @var tinyint
	 */
	private $discussion_email_autosubscribe = 0;

	/**
	 * Description for 'logo'
	 *
	 * @var unknown
	 */
	private $logo = null;

	/**
	 * Description for 'plugins'
	 *
	 * @var unknown
	 */
	private $plugins = null;

	/**
	 * Description for 'created'
	 *
	 * @var unknown
	 */
	private $created = null;

	/**
	 * Description for 'created_by'
	 *
	 * @var unknown
	 */
	private $created_by = null;

	/**
	 * Description for 'params'
	 *
	 * @var unknown
	 */
	private $params = null;

	/**
	 * Description for 'members'
	 *
	 * @var array
	 */
	private $members = array();

	/**
	 * Description for 'managers'
	 *
	 * @var array
	 */
	private $managers = array();

	/**
	 * Description for 'applicants'
	 *
	 * @var array
	 */
	private $applicants = array();

	/**
	 * Description for 'invitees'
	 *
	 * @var array
	 */
	private $invitees = array();

	/**
	 * Description for '_list_keys'
	 *
	 * @var array
	 */
	static $_list_keys = array('members', 'managers', 'applicants', 'invitees');

	/**
	 * Description for '_updatedkeys'
	 *
	 * @var array
	 */
	private $_updatedkeys = array();

	/**
	 * Short description for '__construct'
	 * Long description (if any) ...
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Short description for 'clear'
	 * Long description (if any) ...
	 *
	 * @return boolean Return description (if any) ...
	 */
	public function clear()
	{
		$cvars = get_class_vars(__CLASS__);

		$this->_updatedkeys = array();

		foreach ($cvars as $key=>$value)
		{
			if ($key{0} != '_')
			{
				unset($this->$key);

				if (!in_array($key, self::$_list_keys))
				{
					$this->$key = null;
				}
				else
				{
					$this->$key = array();
				}
			}
		}

		$this->_updatedkeys = array();

		return true;
	}

	/**
	 * Returns a reference to a group object
	 *
	 * @param     mixed $group A string (cn) or integer (ID)
	 * @return    mixed Object if instance found, false if not
	 */
	static public function getInstance($group)
	{
		static $instances;

		// Set instances array
		if (!isset($instances))
		{
			$instances = array();
		}

		// Do we have a matching instance?
		if (!isset($instances[$group]))
		{
			// If an ID is passed, check for a match in existing instances
			if (is_numeric($group))
			{
				foreach ($instances as $instance)
				{
					if ($instance && $instance->get('gidNumber') == $group)
					{
						// Match found
						return $instance;
						break;
					}
				}
			}

			// No matches
			// Create group object
			$hzg = new self();

			if ($hzg->read($group) === false)
			{
				$instances[$group] = false;
			}
			else
			{
				$instances[$group] = $hzg;
			}
		}

		// Return instance
		return $instances[$group];
	}

	/**
	 * Creates a new group in the CMS.
	 * Creates a new entry under the #__xgroups table.
	 *
	 * @return mixed Returns false if error or gid upon success.
	 */
	public function create()
	{
		$db = \App::get('db');

		if (empty($db))
		{
			return false;
		}

		$cn = $this->cn;
		$gidNumber = $this->gidNumber;

		if (empty($cn) && empty($gidNumber))
		{
			return false;
		}

		if (is_numeric($gidNumber))
		{
			if (empty($cn))
			{
				$cn = '_gid' . $gidNumber;
			}

			$query = "INSERT INTO `#__xgroups` (gidNumber,cn) VALUES (" . $db->quote($gidNumber) . "," . $db->quote($cn) . ");";

			$db->setQuery($query);

			$result = $db->query();

			if ($result === false && $db->getErrorNum() != 1062)
			{
				return false;
			}
		}
		else
		{
			$query = "INSERT INTO `#__xgroups` (cn) VALUES (" . $db->quote($cn) . ");";

			$db->setQuery($query);

			$result = $db->query();

			if ($result === false && $db->getErrorNum() == 1062) // row exists
			{
				$query = "SELECT gidNumber FROM `#__xgroups` WHERE cn=" . $db->quote($cn) . ";";

				$db->setQeury($query);

				$result = $db->loadResult();

				if (empty($result))
				{
					return false;
				}

				$this->__set('gidNumber', $result);
			}
			else if ($result !== false)
			{
				$this->__set('gidNumber', $db->insertid());
			}
			else
			{
				return false;
			}
		}

		//trigger the onAfterStoreGroup event
		\Event::trigger('user.onAfterStoreGroup', array($this));

		return $this->gidNumber;
	}

	/**
	 * Short description for 'read'
	 * Long description (if any) ...
	 *
	 * @param unknown $name Parameter description (if any) ...
	 * @param string $storage Parameter description (if any) ...
	 * @return boolean Return description (if any) ...
	 */
	public function read($name = null)
	{
		$this->clear();

		$db = \App::get('db');

		if (empty($db))
		{
			return false;
		}

		if (!is_null($name))
		{
			if (Validate::positiveInteger($name))
			{
				$this->gidNumber = $name;
			}
			else
			{
				$this->cn = $name;
			}
		}

		$result = true;
		$lazyloading = false;

		if (is_numeric($this->gidNumber))
		{
			$query = "SELECT * FROM `#__xgroups` WHERE gidNumber = " . $db->quote($this->gidNumber) . ";";
		}
		else
		{
			$query = "SELECT * FROM `#__xgroups` WHERE cn = " . $db->quote($this->cn) . ";";
		}

		$db->setQuery($query);

		$result = $db->loadAssoc();

		if (empty($result))
		{
			$this->clear();
			return false;
		}

		foreach ($result as $key=>$value)
		{
			if (property_exists(__CLASS__, $key) && $key{0} != '_')
			{
				$this->__set($key, $value);
			}
		}

		$this->__unset('members'); // we unset the lists so we can detect whether they have been loaded or not
		$this->__unset('invitees');
		$this->__unset('applicants');
		$this->__unset('managers');

		if (!$lazyloading)
		{
			$this->__get('members');
			$this->__get('invitees');
			$this->__get('applicants');
			$this->__get('managers');
		}

		$this->_updatedkeys = array();

		return true;
	}

	/**
	 * Short description for 'update'
	 * Long description (if any) ...
	 *
	 * @param unknown $gidNumber Parameter description (if any) ...
	 * @param array $data Parameter description (if any) ...
	 * @return boolean Return description (if any) ...
	 */
	public function update()
	{
		$db = \App::get('db');

		if (empty($db))
		{
			return false;
		}

		if (!is_numeric($this->gidNumber))
		{
			return false;
		}

		if (empty($this->_updatedkeys))
		{
			return true;
		}

		$query = "UPDATE `#__xgroups` SET ";

		$classvars = get_class_vars(__CLASS__);

		$first = true;
		$affected = 0;

		foreach ($classvars as $property=>$value)
		{
			if (($property{0} == '_') || in_array($property, self::$_list_keys))
			{
				continue;
			}

			if (!in_array($property, $this->_updatedkeys))
			{
				continue;
			}

			if (!$first)
			{
				$query .= ',';
			}
			else
			{
				$first = false;
			}

			$value = $this->$property;

			if ($value === null)
			{
				$query .= "`$property`=NULL";
			}
			else
			{
				$query .= "`$property`=" . $db->quote($value);
			}
		}

		$query .= " WHERE `gidNumber`=" . $db->quote($this->gidNumber) . ";";

		if (($first != true) && !empty($query))
		{
			$db->setQuery($query);

			$result = $db->query();

			if ($result === false)
			{
				return false;
			}

			$affected = $db->getAffectedRows();
		}

		$aNewUserGroupEnrollments = array();

		foreach (self::$_list_keys as $property)
		{
			if (!in_array($property, $this->_updatedkeys))
			{
				continue;
			}

			$aux_table = "#__xgroups_" . $property;

			$list = $this->$property;

			if (!is_null($list) && !is_array($list))
			{
				$list = array($list);
			}

			$ulist = null;
			$tlist = null;

			foreach ($list as $value)
			{
				if (!is_null($ulist))
				{
					$ulist .= ',';
					$tlist .= ',';
				}

				$ulist .= $db->quote($value);
				$tlist .= '(' . $db->quote($this->gidNumber) . ',' . $db->quote($value) . ')';
			}

			// @FIXME: I don't have a better solution yet. But the next refactoring of this class
			// should eliminate the ability to read the entire member table due to problems with
			// scale on a large (thousands of members) groups. The add function should track the members
			// being added to a group, but would need to be verified to handle adding members
			// already in group. *njk*

			// @FIXME: Not neat, but because all group membership is resaved every time even for single additions
			// there is no nice way to detect only *new* additions without this check. I don't want to
			// fire off an 'onUserGroupEnrollment' event for users unless they are really being enrolled. *drb*

			if (in_array($property, array('members', 'managers')))
			{
				$query = "SELECT uidNumber FROM `#__xgroups_members` WHERE gidNumber=" . $this->gidNumber;
				$db->setQuery($query);

				// compile current list of members in this group
				$aExistingUserMembership = array();

				if (($results = $db->loadAssoc()))
				{
					foreach ($results as $uid)
					{
						$aExistingUserMembership[] = $uid;
					}
				}

				// see who is new, merge with previous additions so we have a complete list after we are done
				$aNewUserGroupEnrollments = array_merge($aNewUserGroupEnrollments, array_diff($list, $aExistingUserMembership));

			}

			if (is_array($list) && count($list) > 0)
			{
				if (in_array($property, array('members', 'managers', 'applicants', 'invitees')))
				{
					$query = "REPLACE INTO $aux_table (gidNumber,uidNumber) VALUES $tlist;";
				}

				$db->setQuery($query);

				if ($db->query())
				{
					$affected += $db->getAffectedRows();
				}
			}

			if (!is_array($list) || count($list) == 0)
			{
				if (in_array($property, array('members', 'managers', 'applicants', 'invitees')))
				{
					$query = "DELETE FROM $aux_table WHERE gidNumber=" . $db->quote($this->gidNumber) . ";";
				}
			}
			else
			{
				if (in_array($property, array('members', 'managers', 'applicants', 'invitees')))
				{
					$query = "DELETE m FROM `#__xgroups_$property` AS m WHERE " . " m.gidNumber=" .
						$db->quote($this->gidNumber) . " AND m.uidNumber NOT IN (" . $ulist . ");";
				}
			}

			$db->setQuery($query);

			if ($db->query())
			{
				$affected += $db->getAffectedRows();
			}
		}

		// After SQL is done and has no errors, fire off onGroupUserEnrolledEvents
		// for every user added to this group
		foreach ($aNewUserGroupEnrollments as $userid)
		{
			\Event::trigger('groups.onGroupUserEnrollment', array($this->gidNumber, $userid));
		}

		if ($affected > 0)
		{
			\Event::trigger('user.onAfterStoreGroup', array($this));
		}

		return true;
	}

	/**
	 * Short description for 'delete'
	 * Long description (if any) ...
	 *
	 * @param unknown $group Parameter description (if any) ...
	 * @return boolean Return description (if any) ...
	 */
	public function delete()
	{
		$db = \App::get('db');

		if (empty($db))
		{
			return false;
		}

		if (!is_numeric($this->gidNumber))
		{
			$db->setQuery("SELECT gidNumber FROM `#__xgroups` WHERE cn=" . $db->quote($this->cn) . ";");

			$gidNumber = $db->loadResult();

			if (!is_numeric($this->gidNumber))
			{
				return false;
			}

			$this->gidNumber = $gidNumber;
		}

		$db->setQuery("DELETE FROM `#__xgroups` WHERE gidNumber=" . $db->quote($this->gidNumber) . ";");

		$result = $db->query();

		if ($result === false)
		{
			return false;
		}

		$db->setQuery("DELETE FROM `#__xgroups_applicants` WHERE gidNumber=" . $db->quote($this->gidNumber) . ";");
		$db->query();
		$db->setQuery("DELETE FROM `#__xgroups_invitees` WHERE gidNumber=" . $db->quote($this->gidNumber) . ";");
		$db->query();
		$db->setQuery("DELETE FROM `#__xgroups_managers` WHERE gidNumber=" . $db->quote($this->gidNumber) . ";");
		$db->query();
		$db->setQuery("DELETE FROM `#__xgroups_members` WHERE gidNumber=" . $db->quote($this->gidNumber) . ";");
		$db->query();

		//trigger the onAfterStoreGroup event
		\Event::trigger('user.onAfterStoreGroup', array($this));

		return true;
	}

	/**
	 * Short description for '__get'
	 * Long description (if any) ...
	 *
	 * @param string $property Parameter description (if any) ...
	 * @return string Return description (if any) ...
	 */
	public function __get($property = null)
	{
		if (!property_exists(__CLASS__, $property) || $property{0} == '_')
		{
			if (empty($property))
			{
				$property = '(null)';
			}

			$this->_error("Cannot access property " . __CLASS__ . "::$" . $property, E_USER_ERROR);
			die();
		}

		if (in_array($property, self::$_list_keys))
		{
			if (!array_key_exists($property, get_object_vars($this)))
			{
				$db = \App::get('db');

				if (is_object($db))
				{
					$groups = array('applicants'=>array(), 'invitees'=>array(), 'members'=>array(), 'managers'=>array());

					foreach ($groups as $key=>$data)
					{
						$this->__set($key, $data);
					}

					$query = "(select uidNumber, 'invitees' AS role from #__xgroups_invitees where gidNumber=" . $db->quote($this->gidNumber) . ")
						UNION
							(select uidNumber, 'applicants' AS role from #__xgroups_applicants where gidNumber=" . $db->quote($this->gidNumber) . ")
						UNION
							(select uidNumber, 'members' AS role from #__xgroups_members where gidNumber=" . $db->quote($this->gidNumber) . ")
						UNION
							(select uidNumber, 'managers' AS role from #__xgroups_managers where gidNumber=" . $db->quote($this->gidNumber) . ")";

					$db->setQuery($query);

					if (($results = $db->loadObjectList()))
					{
						foreach ($results as $result)
						{
							if (isset($groups[$result->role]))
							{
								$groups[$result->role][] = $result->uidNumber;
							}
						}

						foreach ($groups as $key=>$data)
						{
							$this->__set($key, $data);
						}
					}
				}
			}
		}

		if (isset($this->$property))
		{
			return $this->$property;
		}

		if (array_key_exists($property, get_object_vars($this)))
		{
			return null;
		}

		$this->_error("Undefined property " . __CLASS__ . "::$" . $property, E_USER_NOTICE);

		return null;
	}

	/**
	 * Short description for '__set'
	 * Long description (if any) ...
	 *
	 * @param string $property Parameter description (if any) ...
	 * @param unknown $value Parameter description (if any) ...
	 * @return void
	 */
	public function __set($property = null, $value = null)
	{
		if (!property_exists(__CLASS__, $property) || $property{0} == '_')
		{
			if (empty($property))
			{
				$property = '(null)';
			}

			$this->_error("Cannot access property " . __CLASS__ . "::$" . $property, E_USER_ERROR);
			die();
		}

		if (in_array($property, self::$_list_keys))
		{
			$value = array_diff((array) $value, array(''));

			if (in_array($property, array('managers', 'members', 'applicants', 'invitees')))
			{
				$value = $this->_userids($value);
			}

			$value = array_unique($value);
			$value = array_values($value);
			$this->$property = $value;
		}
		else
		{
			$this->$property = $value;
		}

		if (!in_array($property, $this->_updatedkeys))
		{
			$this->_updatedkeys[] = $property;
		}
	}

	/**
	 * Short description for '__isset'
	 * Long description (if any) ...
	 *
	 * @param string $property Parameter description (if any) ...
	 * @return string Return description (if any) ...
	 */
	public function __isset($property = null)
	{
		if (!property_exists(__CLASS__, $property) || $property{0} == '_')
		{
			if (empty($property))
			{
				$property = '(null)';
			}

			$this->_error("Cannot access property " . __CLASS__ . "::$" . $property, E_USER_ERROR);
			die();
		}

		return isset($this->$property);
	}

	/**
	 * Short description for '__unset'
	 * Long description (if any) ...
	 *
	 * @param string $property Parameter description (if any) ...
	 * @return void
	 */
	public function __unset($property = null)
	{
		if (!property_exists(__CLASS__, $property) || $property{0} == '_')
		{
			if (empty($property))
			{
				$property = '(null)';
			}

			$this->_error("Cannot access property " . __CLASS__ . "::$" . $property, E_USER_ERROR);
			die();
		}

		$this->_updatedkeys = array_diff($this->_updatedkeys, array($property));

		unset($this->$property);
	}

	/**
	 * Short description for '_error'
	 * Long description (if any) ...
	 *
	 * @param string $message Parameter description (if any) ...
	 * @param integer $level Parameter description (if any) ...
	 * @return void
	 */
	private function _error($message, $level = E_USER_NOTICE)
	{
		$caller = next(debug_backtrace());

		switch ($level)
		{
			case E_USER_NOTICE:
				echo "Notice: ";
				break;
			case E_USER_ERROR:
				echo "Fatal error: ";
				break;
			default:
				echo "Unknown error: ";
				break;
		}

		echo $message . ' in ' . $caller['file'] . ' on line ' . $caller['line'] . "\n";
	}

	/**
	 * Short description for 'get'
	 * Long description (if any) ...
	 *
	 * @param unknown $key Parameter description (if any)
	 * @param  unknown $default value when no match is found
	 * @return unknown Return description (if any) ...
	 */
	public function get($key, $default=null)
	{
		return $this->__get($key);
	}

	/**
	 * Short description for 'set'
	 * Long description (if any) ...
	 *
	 * @param unknown $key Parameter description (if any) ...
	 * @param unknown $value Parameter description (if any) ...
	 * @return unknown Return description (if any) ...
	 */
	public function set($key, $value)
	{
		return $this->__set($key, $value);
	}

	/**
	 * Short description for '_userids'
	 * Long description (if any) ...
	 *
	 * @param array $users Parameter description (if any) ...
	 * @return mixed Return description (if any) ...
	 */
	private function _userids($users)
	{
		$db = \App::get('db');

		if (empty($db))
		{
			return false;
		}

		$usernames = array();
		$userids = array();

		if (!is_array($users))
		{
			$users = array($users);
		}

		foreach ($users as $u)
		{
			if (is_numeric($u))
			{
				$userids[] = $u;
			}
			else
			{
				$usernames[] = $db->quote($u);
			}
		}

		if (empty($usernames))
		{
			return $userids;
		}

		$set = implode($usernames, ",");

		$sql = "SELECT id FROM `#__users` WHERE username IN ($set);";

		$db->setQuery($sql);

		$result = $db->loadColumny();

		if (empty($result))
		{
			$result = array();
		}

		$result = array_merge($result, $userids);

		return $result;
	}

	/**
	 * Short description for 'add'
	 * Long description (if any) ...
	 *
	 * @param unknown $key Parameter description (if any) ...
	 * @param array $value Parameter description (if any) ...
	 * @return void
	 */
	public function add($key = null, $value = array())
	{
		$users = $this->_userids($value);

		$this->__set($key, array_merge($this->__get($key), $users));
	}

	/**
	 * Short description for 'remove'
	 * Long description (if any) ...
	 *
	 * @param unknown $key Parameter description (if any) ...
	 * @param array $value Parameter description (if any) ...
	 * @return void
	 */
	public function remove($key = null, $value = array())
	{
		$users = $this->_userids($value);

		$this->__set($key, array_diff($this->__get($key), $users));
	}

	/**
	 * Short description for 'iterate'
	 * Long description (if any) ...
	 *
	 * @param unknown $func Parameter description (if any) ...
	 * @param string $storage Parameter description (if any) ...
	 * @return boolean Return description (if any) ...
	 */
	static function iterate($func)
	{
		$db = \App::get('db');

		$query = "SELECT cn FROM `#__xgroups`;";

		$db->setQuery($query);

		$result = $db->loadColumny();

		if ($result === false)
		{
			return false;
		}

		foreach ($result as $row)
		{
			call_user_func($func, $row);
		}

		return true;
	}

	/**
	 * Check if a group exists.
	 * Given the group id, returns true if group exists.
	 *
	 * @param integer $group the group id number (GID) of the group being verified.
	 * @param boolean $check_system boolean for checking against POSIX user.
	 * @return boolean returns false if group does not exist; true if group exists.
	 */
	static public function exists($group, $check_system = false)
	{
		$db = \App::get('db');

		if (empty($group))
		{
			return false;
		}

		if ($check_system)
		{
			if (is_numeric($group) && posix_getgrgid($group))
			{
				return true;
			}

			if (!is_numeric($group) && posix_getgrnam($group))
			{
				return true;
			}
		}

		// check reserved
		if (Validate::reserved('group', $group))
		{
			return true;
		}

		if (is_numeric($group))
		{
			$query = 'SELECT gidNumber FROM `#__xgroups` WHERE gidNumber=' . $db->quote($group);
		}
		else
		{
			$query = 'SELECT gidNumber FROM `#__xgroups` WHERE cn=' . $db->quote($group);
		}

		$db->setQuery($query);

		if (!$db->query())
		{
			return false;
		}

		if ($db->loadResult() > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Short description for 'find'
	 * Long description (if any) ...
	 *
	 * @param array $filters Parameter description (if any) ...
	 * @return boolean Return description (if any) ...
	 */
	static function find($filters = array())
	{
		$db = \App::get('db');

		// Type 0 - System Group
		// Type 1 - HUB Group
		// Type 2 - Project Group
		// Type 3 - Partner "Special" Group
		// Type 4 - Course group
		$gTypes = array('all', 'system', 'hub', 'project', 'super', 'course', '0', '1', '2', '3', '4');

		$types = !empty($filters['type']) ? $filters['type'] : array('all');

		foreach ($types as $type)
		{
			if (!in_array($type, $gTypes))
			{
				return false;
			}
		}

		$where = array();

		if (!in_array('all', $types))
		{
			$t = implode(",", $types);

			//replace group type names with group type id
			$t = str_replace('hub', 1, $t);
			$t = str_replace('project', 2, $t);
			$t = str_replace('super', 3, $t);
			$t = str_replace('course', 4, $t);
			$t = str_replace('system', 0, $t);

			$where[] = 'type IN (' . $t . ')';
		}

		if (isset($filters['search']) && $filters['search'] != '')
		{
			if (is_numeric($filters['search']))
			{
				$where[] = "gidNumber=" . $db->quote($filters['search']);
			}
			else
			{
				$where[] = "(LOWER(description) LIKE " . $db->quote('%' . strtolower($filters['search']) . '%') . " OR LOWER(cn) LIKE " . $db->quote('%' . strtolower($filters['search']) . '%') . ")";
			}
		}

		if (isset($filters['index']) && $filters['index'] != '')
		{
			$where[] = "(LOWER(description) LIKE " . $db->quote(strtolower($filters['index']) . '%') . ")";
		}

		if (isset($filters['authorized']) && $filters['authorized'] === 'admin')
		{
			if (isset($filters['discoverability']) && $filters['discoverability'] != '')
			{
				switch ($filters['discoverability'])
				{
					case 0:
						$where[] = "discoverability=0";
						break;
					case 1:
						$where[] = "discoverability=1";
						break;
				}
			}
		}
		else
		{
			$where[] = "discoverability=0";
		}

		if (isset($filters['policy']) && $filters['policy'])
		{
			switch ($filters['policy'])
			{
				case 'closed':
					$where[] = "join_policy=3";
					break;
				case 'invite':
					$where[] = "join_policy=2";
					break;
				case 'restricted':
					$where[] = "join_policy=1";
					break;
				case 'open':
				default:
					$where[] = "join_policy=0";
					break;
			}
		}

		if (isset($filters['published']) && $filters['published'] != '')
		{
			$where[] = "published=" . $filters['published'];
		}

		if (isset($filters['approved']) && $filters['approved'] != '')
		{
			$where[] = "approved=" . $filters['approved'];
		}

		if (isset($filters['created']) && $filters['created'] != '')
		{
			if ($filters['created'] == 'pastday')
			{
				$pastDay = date("Y-m-d H:i:s", strtotime('-1 DAY'));
				$where[] = "created >= '" . $pastDay . "'";
			}
		}


		if (empty($filters['fields']))
		{
			$filters['fields'][] = 'cn';
		}

		$field = implode(',', $filters['fields']);

		$query = "SELECT $field FROM `#__xgroups`";
		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		if (isset($filters['sortby']) && $filters['sortby'] != '')
		{
			$query .= " ORDER BY ";

			switch ($filters['sortby'])
			{
				case 'alias':
					$query .= 'cn ASC';
					break;
				case 'title':
					$query .= 'description ASC';
					break;
				default:
					$query .= $filters['sortby'];
					break;
			}
		}

		if (isset($filters['limit']) && $filters['limit'] != 'all')
		{
			$query .= " LIMIT " . $filters['start'] . "," . $filters['limit'];
		}

		$query .= ";";

		$db->setQuery($query);

		if (!in_array('COUNT(*)', $filters['fields']))
		{
			$result = $db->loadObjectList();
		}
		else
		{
			$result = $db->loadResult();
		}

		if (empty($result))
		{
			return false;
		}

		return $result;
	}

	/**
	 * Short description for 'is_member_of'
	 * Long description (if any) ...
	 *
	 * @param string $table Parameter description (if any) ...
	 * @param unknown $uid Parameter description (if any) ...
	 * @return boolean Return description (if any) ...
	 */
	public function is_member_of($table, $uid)
	{
		if (!in_array($table, array('applicants', 'members', 'managers', 'invitees')))
		{
			return false;
		}

		if (!is_numeric($uid))
		{
			$uidNumber = \JUserHelper::getUserId($uid);
		}
		else
		{
			$uidNumber = $uid;
		}

		return in_array($uidNumber, $this->get($table));
	}

	/**
	 * Short description for 'isMember'
	 * Long description (if any) ...
	 *
	 * @param unknown $uid Parameter description (if any) ...
	 * @return string Return description (if any) ...
	 */
	public function isMember($uid)
	{
		return $this->is_member_of('members', $uid);
	}

	/**
	 * Short description for 'isApplicant'
	 * Long description (if any) ...
	 *
	 * @param unknown $uid Parameter description (if any) ...
	 * @return string Return description (if any) ...
	 */
	public function isApplicant($uid)
	{
		return $this->is_member_of('applicants', $uid);
	}

	/**
	 * Short description for 'isManager'
	 * Long description (if any) ...
	 *
	 * @param unknown $uid Parameter description (if any) ...
	 * @return string Return description (if any) ...
	 */
	public function isManager($uid)
	{
		return $this->is_member_of('managers', $uid);
	}

	/**
	 * Short description for 'isInvitee'
	 * Long description (if any) ...
	 *
	 * @param unknown $uid Parameter description (if any) ...
	 * @return string Return description (if any) ...
	 */
	public function isInvitee($uid)
	{
		return $this->is_member_of('invitees', $uid);
	}

	/**
	 * Short description for 'getEmails'
	 * Long description (if any) ...
	 *
	 * @param string $key Parameter description (if any) ...
	 * @return array Return description (if any) ...
	 */
	public function getEmails($tbl = 'managers')
	{
		if (!in_array($tbl, array('applicants', 'members', 'managers', 'invitees')))
		{
			return false;
		}

		$db = \App::get('db');

		if (empty($db))
		{
			return false;
		}

		$query = 'SELECT u.email FROM #__users AS u, #__xgroups_' . $tbl . ' AS gm WHERE gm.gidNumber=' . $db->quote($this->gidNumber) . ' AND u.id=gm.uidNumber;';

		$db->setQuery($query);

		$emails = $db->loadColumny();

		return $emails;
	}

	/**
	 * Short description for 'search'
	 * Long description (if any) ...
	 *
	 * @param string $tbl Parameter description (if any) ...
	 * @param string $q Parameter description (if any) ...
	 * @return mixed Return description (if any) ...
	 */

	// @FIXME: next refactoring this might be getMembers(), getInvitees(), getApplicaants(),
	// getManagers() with a filter and limit/offset option  *njk*
	public function search($tbl = '', $q = '')
	{
		if (!in_array($tbl, array('applicants', 'members', 'managers', 'invitees')))
		{
			return false;
		}

		if ($q == '')
		{
			return false;
		}

		$table = '#__xgroups_' . $tbl;
		$user_table = '#__users';

		$db = \App::get('db');

		$query = "SELECT u.id FROM {$table} AS t, {$user_table} AS u
					WHERE t.gidNumber={$db->quote($this->gidNumber)}
					AND u.id=t.uidNumber
					AND LOWER(u.name) LIKE '%" . strtolower($q) . "%';";
		$db->setQuery($query);
		return $db->loadColumny();
	}

	/**
	 * Is a group a super group?
	 *
	 * @return BOOL
	 */
	public function isSuperGroup()
	{
		return ($this->get('type') == 3) ? true : false;
	}

	/**
	 * Return a groups logo
	 *
	 * @param   string $what What data to return?
	 * @return  mixed
	 */
	public function getLogo($what='')
	{
		//default logo
		static $default_logo;

		if (!$default_logo)
		{
			$default_logo = '/core/components/com_groups/site/assets/img/group_default_logo.png';
		}

		//logo link - links to group overview page
		$link = \Route::url('index.php?option=com_groups&cn=' . $this->get('cn'));

		//path to group uploaded logo
		$path = '/site/groups/' . $this->get('gidNumber') . DS . 'uploads' . DS . $this->get('logo');

		//if logo exists and file is uploaded use that logo instead of default
		$src = ($this->get('logo') != '' && is_file(PATH_APP . $path)) ? $path : $default_logo;

		//check to make sure were a member to show logo for hidden group
		$members_and_invitees = array_merge($this->get('members'), $this->get('invitees'));
		if ($this->get('discoverability') == 1
		 && !in_array(\User::get('id'), $members_and_invitees))
		{
			$src = $default_logo;
		}

		$what = strtolower($what);
		if ($what == 'size')
		{
			return getimagesize(PATH_APP . $src);
		}

		if ($what == 'path')
		{
			return $src;
		}

		return \Request::base(true) . $src;
	}

	/**
	 * Get groups path
	 *
	 * @return [type] [description]
	 */
	public function getBasePath()
	{
		$groupParams = \Component::params('com_groups');
		$uploadPath  = $groupParams->get('uploadpath', '/site/groups');
		return $uploadPath . DS . $this->get('gidNumber');
	}

	/**
	 * Return serve up path
	 *
	 * @param  string $path [description]
	 * @return [type]       [description]
	 */
	public function downloadLinkForPath($base = 'uploads', $path = '', $type = 'file')
	{
		// get base path
		$groupFolder = $this->getBasePath();

		// split segments by directory separator
		$segments = array_map('trim', explode(DS, $path));

		// prepend base segments to original segments
		if ($base != 'uploads')
		{
			$baseSegments = array_map('trim', explode(DS, $base));
			$segments = array_merge($baseSegments, $segments);
		}

		// build link
		$link  = \Route::url('index.php?option=com_groups&cn=' . $this->get('cn'));
		$link .= '/' . ucfirst($type) . ':' . implode('/', $segments);

		// return link
		return $link;
	}

	/**
	 * Get the content of the entry
	 *
	 * @param      string  $as      Format to return state in [text, number]
	 * @param      integer $shorten Number of characters to shorten text to
	 * @param      string  $type    Type to get [public, private]
	 * @return     string
	 */
	public function getDescription($as='parsed', $shorten=0, $type='public')
	{
		$options = array();

		// get description before parsing
		$before = $this->get($type . '_desc');

		switch (strtolower($as))
		{
			case 'parsed':
				$config = array(
					'option'   => 'com_groups',
					'scope'    => '', //$this->get('cn') . DS . 'wiki',
					'pagename' => $this->get('cn'),
					'pageid'   => 0, //$this->get('gidNumber'),
					'filepath' => \Component::params('com_groups')->get('uploadpath', '/site/groups') . DS . $this->get('gidNumber') . DS . 'uploads',
					'domain'   => $this->get('cn'),
					'camelcase' => 0
				);

				\Event::trigger('content.onContentPrepare', array(
					'com_groups.group.' . $type . '_desc',
					&$this,
					&$config
				));
				$content = $this->get($type . '_desc');

				$options = array('html' => true);
			break;

			case 'clean':
				$content = strip_tags($this->getDescription('parsed', 0, $type));
			break;

			case 'raw':
			default:
				$content = stripslashes($this->get($type . '_desc'));
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;
		}

		if ($shorten)
		{
			$content = String::truncate($content, $shorten, $options);
		}

		// set our descriptions to be html
		if ($before != $content && $as == 'parsed')
		{
			$this->set($type . '_desc', trim($content));
			//$this->update();
		}

		return $content;
	}
}