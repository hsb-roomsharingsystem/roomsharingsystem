<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");

/**
 * Util-Class for permission checking
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingPermissionUtils
{
	private $pool_id;
	private $ilRoomsharingDatabase;
	private $privileges;
	private $user;

	/**
	 *
	 * @global type $ilUser
	 * @param type $a_pool_id
	 */
	public function __construct($a_pool_id)
	{
		global $ilUser;

		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomSharingDatabase($a_pool_id);
		$this->privileges = new ilRoomsharingPrivileges();
		$this->user = $ilUser;
	}

	/**
	 * Checks permissions configurated by the roles in the privileges-tab
	 *
	 * @param string $a_privilege privilege name e.g. "addBooking"
	 * @param integer $a_pool_id Pool ID
	 *
	 * @return boolean true if this user has the permission, false otherwise
	 */
	public function checkPrivilege($a_privilege)
	{
		return in_array(strtolower($a_privilege), $this->getAllUserPrivileges());
	}

	/**
	 * Gets the priority of a user
	 *
	 * @param integer $a_user_id optional user-id
	 * @return integer user-priority of current logged in user (if parameter was not set) or user-priority of the user with the id given in the param
	 */
	public function getUserPriority($a_user_id = null)
	{
		if ($a_user_id === null)
		{
			$a_user_id = $this->user->getId();
		}
		return $this->privileges->getPriorityOfUser($a_user_id);
	}

	/**
	 * Gets all privileges that the logged in user has
	 *
	 * @param integer $a_pool_id Pool ID
	 * @return array Array with the privileges
	 */
	public function getAllUserPrivileges()
	{
		return $this->privileges->getPrivilegesForUser($this->user->getId());
	}

}
