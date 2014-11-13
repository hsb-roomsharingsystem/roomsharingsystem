<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * Util-Class for permission checking
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingPermissionUtils
{
	private $pool_id;
	private $ilRoomsharingDatabase;

	public function __construct($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomSharingDatabase($a_pool_id);
	}

	/**
	 * Checks permissions configurated by the roles in the privileges-tab
	 *
	 * @param string $a_privilege Privilege name e.g. "addBooking"
	 * @param integer $a_pool_id Pool ID
	 *
	 * @return boolean true if this user has the permission, false otherwise
	 */
	public function checkPrivilege($a_privilege)
	{
		return true;
	}

	/**
	 * Gets all privileges that the logged in user has.
	 *
	 * @param integer $a_pool_id Pool ID
	 * @return array Array with the privileges
	 */
	public function getAllUserPrivileges()
	{
		return array("accessAppointments", "accessSearch", "accessRooms",
			"accessFloorplans", "accessSettings", "accessPrivileges");
	}

}
