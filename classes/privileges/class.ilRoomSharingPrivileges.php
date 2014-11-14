<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingPrivilegesException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");

/**
 * Class ilRoomSharingPrivileges
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 */
class ilRoomSharingPrivileges
{
	private $pool_id;
	private $ilRoomsharingDatabase;

	/**
	 * Constructor of ilRoomSharingPrivileges
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id = 1)
	{
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomSharingDatabase($this->pool_id);
	}

	public function getPrivilegesMatrix()
	{
		// Lock Group
		$privileges[] = array("show_lock_row" => 1, "locked_groups" => array(1001));

		// ### Buchungen ###
		// Info-Spalte
		$privileges[] = array("show_section_info" => 1, "section" =>
			array("title" => "Buchungen", "description" => "Hier sind die Privilegien für die Buchungen aufgeführt")); // Table Section
		// Privilegien
		$privileges[] = array("privilege" =>
			array("id" => 1, "name" => "Stornieren", "description" => "Fremde Buchungen stornieren"),
			"groups" => array(array("id" => 1001, "privilege_set" => false), array("id" => 1002, "privilege_set" => true)));
		$privileges[] = array("privilege" =>
			array("id" => 2, "name" => "Serienbuchungen", "description" => "Serienbuchungen sind anlegbar"),
			"groups" => array(array("id" => 1001, "privilege_set" => true), array("id" => 1002, "privilege_set" => true)));
		// Select all
		$privileges[] = array("show_select_all" => 1, "type" => "booking", "privileges" => array(1, 2));

		// ### Räume ###
		// Info-Spalte
		$privileges[] = array("show_section_info" => 1, "section" =>
			array("title" => "Räume", "description" => "Hier sind die Privilegien für die Räume aufgeführt")); // Table Section
		// Privilegien
		$privileges[] = array("privilege" =>
			array("id" => 3, "name" => "Erstellen", "description" => "Neue Räume erstellbar"),
			"groups" => array(array("id" => 1001, "privilege_set" => false), array("id" => 1002, "privilege_set" => true)));
		$privileges[] = array("privilege" =>
			array("id" => 4, "name" => "Editieren", "description" => "Vorhandene Räume können editiert werden"),
			"groups" => array(array("id" => 1001, "privilege_set" => false), array("id" => 1002, "privilege_set" => true)));
		$privileges[] = array("privilege" =>
			array("id" => 5, "name" => "Löschen", "description" => "Vorhandene Räume können gelöscht werden"),
			"groups" => array(array("id" => 1001, "privilege_set" => false), array("id" => 1002, "privilege_set" => true)));
		// Select all
		$privileges[] = array("show_select_all" => 1, "type" => "room", "privileges" => array(3, 4, 5));

		return $privileges;
	}

	public function getPrivileges()
	{
		$priv = array();
		$priv[] = 'locked';
		$priv[] = 'accessAppointments';
		$priv[] = 'accessSearch';
		$priv[] = 'accessRooms';
		$priv[] = 'accessFloorplans';
		$priv[] = 'accessSettings';
		$priv[] = 'accessPrivileges';
		$priv[] = 'addBooking';
		$priv[] = 'addSequenceBooking';
		$priv[] = 'addUnlimitedBooking';
		$priv[] = 'editBooking';
		$priv[] = 'deleteBooking';
		$priv[] = 'addParticipantsToBooking';
		$priv[] = 'deleteParticipation';
		$priv[] = 'seeAllBookingData';
		$priv[] = 'deleteBookingWithLowerPriority';
		$priv[] = 'deleteAllExistingBookings';
		$priv[] = 'bookMultipleRooms';
		$priv[] = 'seeBookingsOfRooms';
		$priv[] = 'administrateRooms';
		$priv[] = 'administrateFloorplans';
		$priv[] = 'administratePrivileges';

		return $priv;
	}

	public function getGroups()
	{
		$groups[] = array("id" => 1001, "name" => "HARDCODED USER", "description" => "HARDCODED DESCRIPTION",
			"role" => "HARDCODED ROLE");
		$groups[] = array("id" => 1002, "name" => "HARDCODED ADMIN", "description" => "HARDCODED DESCRIPTION",
			"role" => "");

		return $groups;
	}

	public function getGroupFromId($a_group_id)
	{
		return array("name" => "HARDCODED GROUP " . $a_group_id, "description" => "HARDCODED DESCRIPTION",
			"role" => 1);
	}

	public function getAssignedUsersForGroup($a_group_id)
	{
		$assigned_users[] = array("login" => "plustig", "firstname" => "Peter", "lastname" => "Lustig", "id" => 2001);
		$assigned_users[] = array("login" => "mmustermann", "firstname" => "Max", "lastname" => "Mustermann",
			"id" => 2002);

		return $assigned_users;
	}

	public function getGlobalRoles()
	{
		global $rbacreview;

		$roles = $rbacreview->getParentRoleIds($_GET['ref_id']);

		$global_roles = array();
		foreach ($roles as $role)
		{
			$global_roles[] = array('id' => $role['id'], 'title' => $role['title']);
		}
		return $global_roles;
	}

	public function addGroup($a_groupData)
	{
		$insertedID = $this->ilRoomsharingDatabase->insertGroup($a_groupData['name'],
			$a_groupData['description'], $a_groupData['role_id']);
		if (!ilRoomSharingNumericUtils::isPositiveNumber($insertedID))
		{
			throw new ilRoomSharingPrivilegesException("rep_robj_xrs_group_not_created");
		}
	}

	public function updateGroup($a_groupData)
	{
		//TODO ERROR CATCHING
		$this->ilRoomsharingDatabase->updateGroup($a_groupData['id'], $a_groupData['name'],
			$a_groupData['description'], $a_groupData['role_id']);
	}

	public function addUserToGroup($a_group_id, $a_user_id)
	{
		//TODO ERROR CATCHING
		$this->ilRoomsharingDatabase->addUserToGroup($a_group_id, $a_user_id);
	}

}

?>
