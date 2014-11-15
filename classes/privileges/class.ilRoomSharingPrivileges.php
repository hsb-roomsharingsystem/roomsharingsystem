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
		$privilegesMatrix = array();

		// Lock Group
		$privilegesMatrix[] = array("show_lock_row" => "lock", "locked_groups" => array(1001));

		// ### Appointments ###
		$privilegesMatrix[] = $this->addNewSection("Termine",
			"Hier sind die Privilegien für die Termine aufgeführt");
		$privilegesMatrix[] = $this->addPrivilege("accessAppointments", "Termine aufrufen",
			"Zugang zum Reiter &quot;Termine&quot;");
		$privilegesMatrix[] = $this->addPrivilege("accessSearch", "Suche aufrufen",
			"Zugang zum Reiter &quot;Suche&quot;");
		$privilegesMatrix[] = $this->addPrivilege("addOwnBookings", "Erstellen, Bearbeiten, Stornieren",
			"Eigene Buchungen anleg-, bearbeit- und stornierbar");
		$privilegesMatrix[] = $this->addPrivilege("addParticipants", "Teilnahmer hinzufügen",
			"Teilnehmer zu einer Buchung hinzufügbar");
		$privilegesMatrix[] = $this->addPrivilege("addSequenceBookings", "Serienbuchungen",
			"Serienbuchungen anlegbar");
		$privilegesMatrix[] = $this->addPrivilege("cancelBookingLowerPriority",
			"Niedrigere Priorität stornieren",
			"Fremde Buchungen, welche von Benutzern niedrigerer Priorität erstellt wurden, stornierbar");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("bookings",
			array("accessAppointments",
			"addOwnBookings", "addSequenceBookings", "cancelBookingWithLowerPriority"));

		// ### Rooms ###
		$privilegesMatrix[] = $this->addNewSection("Räume",
			"Hier sind die Privilegien für die Räume aufgeführt");
		$privilegesMatrix[] = $this->addPrivilege("accessRooms", "Räume aufrufen",
			"Zugang zum Reiter &quot;Räume&quot;");
		$privilegesMatrix[] = $this->addPrivilege("seeBookingsOfRooms", "Buchungen einsehen",
			"Buchungen von einzelnen Räumen in Detailansicht einsehbar");
		$privilegesMatrix[] = $this->addPrivilege("addRooms", "Erstellen", "Neue Räume erstellbar");
		$privilegesMatrix[] = $this->addPrivilege("editRooms", "Bearbeiten",
			"Vorhandene Räume können bearbeitet werden");
		$privilegesMatrix[] = $this->addPrivilege("deleteRooms", "Löschen",
			"Vorhandene Räume können gelöscht werden");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("rooms",
			array("accessRooms", "seeBookingsOfRooms",
			"addRooms", "editRooms", "deleteRooms"));

		// ### Floorplans ###
		$privilegesMatrix[] = $this->addNewSection("Gebäudepläne",
			"Hier sind die Privilegien für die Gebäudepläne aufgeführt");
		$privilegesMatrix[] = $this->addPrivilege("accessFloorplans", "Gebäudepläne aufrufen",
			"Zugang zum Reiter &quot;Gebäudepläne&quot;");
		$privilegesMatrix[] = $this->addPrivilege("addFloorplans", "Erstellen",
			"Neue Gebäudepläne erstellbar");
		$privilegesMatrix[] = $this->addPrivilege("editFloorplans", "Bearbeiten",
			"Vorhandene Gebäudepläne können bearbeitet werden");
		$privilegesMatrix[] = $this->addPrivilege("deleteFloorplans", "Löschen",
			"Vorhandene Gebäudepläne können gelöscht werden");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("floorplans",
			array("accessFloorplans",
			"addFloorplans", "editFloorplans", "deleteFloorplans"));

		// ### Privileges ###
		$privilegesMatrix[] = $this->addNewSection("Allgemeine Privilegien",
			"Hier sind die allgemeinen Privilegien aufgeführt");
		$privilegesMatrix[] = $this->addPrivilege("accessSettings", "Einstellungen aufrufen",
			"Zugang zum Reiter &quot;Einstellungen&quot;");
		$privilegesMatrix[] = $this->addPrivilege("accessPrivileges", "Privilegien aufrufen",
			"Zugang zum Reiter &quot;Privilegien&quot;");
		$privilegesMatrix[] = $this->addPrivilege("addGroup", "Gruppe anlegen", "Neue Gruppe anlegbar");
		$privilegesMatrix[] = $this->addPrivilege("editPrivileges", "Privilegien bearbeiten",
			"Privilegien anderer Gruppen bearbeiten");
		$privilegesMatrix[] = $this->addPrivilege("lockPrivileges", "Privilegien sperren",
			"Privilegien anderer Gruppen sperrbar");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("privileges",
			array("accessSettings",
			"accessPrivileges", "addGroup", "lockPrivileges", "lockPrivileges"));

		return $privilegesMatrix;
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
		global $rbacreview;
		$grp = array();
		$groups = $this->ilRoomsharingDatabase->getGroups();
		foreach ($groups as $group)
		{
			$grp_values = $group;
			$grp_values['role'] = $this->getGlobalRoleTitle($group['role_id']);
			$grp[] = $grp_values;
		}

		$grp[] = array("id" => 1001, "name" => "HARDCODED USER", "description" => "HARDCODED DESCRIPTION",
			"role" => "HARDCODED ROLE");
		$grp[] = array("id" => 1002, "name" => "HARDCODED ADMIN", "description" => "HARDCODED DESCRIPTION",
			"role" => "");

		return $grp;
	}

	public function getGroupById($a_group_id)
	{
		return $this->ilRoomsharingDatabase->getGroupById($a_group_id);
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
			$global_roles[] = array('id' => $role['rol_id'], 'title' => $role['title']);
		}
		return $global_roles;
	}

	public function getGlobalRoleTitle($a_role_id)
	{
		$roles = $this->getGlobalRoles();
		$roleName = null;
		foreach ($roles as $role)
		{
			if ($role['id'] == $a_role_id)
			{
				$roleName = $role['title'];
			}
		}
		return $roleName;
	}

	public function addGroup($a_groupData)
	{
		$insertedID = $this->ilRoomsharingDatabase->insertGroup($a_groupData['name'],
			$a_groupData['description'], $a_groupData['role_id'], $a_groupData['copied_group_privileges']);
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

	/**
	 * Adds a new Table-Section Header
	 *
	 * @param string $a_section_title_lng_key Section-Title language key
	 * @param string $a_section_description_lng_key Optional Section-Description language key
	 *
	 * @return array Array with the new section-information for privilege matrix
	 */
	private function addNewSection($a_section_title_lng_key, $a_section_description_lng_key = null)
	{
		return array("show_section_info" => 1, "section" =>
			array("title" => $a_section_title_lng_key, "description" => $a_section_description_lng_key));
	}

	/**
	 * Adds a new privilege
	 *
	 * @param string $a_id Privilege-ID
	 * @param string $a_name_lng_key Privilege-Name Language Key
	 * @param string $a_description_lng_key Privilege-Description Language Key
	 *
	 * @return array Array with the new privilege information for privilege matrix
	 */
	private function addPrivilege($a_id, $a_name_lng_key, $a_description_lng_key)
	{
		return array("privilege" => array(
				"id" => $a_id,
				"name" => $a_name_lng_key,
				"description" => $a_description_lng_key),
			"groups" => $this->getGroupsPrivilegeValue($a_id)
		);
	}

	/**
	 * Get each group values to the specific privilege
	 *
	 * @param string $a_privilege_id Privilege ID
	 *
	 * @return array Array with the group-ids and if this privilege is set or not
	 */
	private function getGroupsPrivilegeValue($a_privilege_id)
	{
		return array(array("id" => 1001, "privilege_set" => false), array("id" => 1002, "privilege_set" => true));
	}

	/**
	 * Add the checkbox values for a multiple select checkbox to select more checkboxes with one
	 *
	 * @param string $a_type Used for ID of the checkbox
	 * @param array $a_privilege_ids Privilege IDs of privileges which should be checked by checking this checkbox
	 *
	 * @return array Checkbox Values for privelege matrix
	 */
	private function addSelectMultipleCheckbox($a_type, $a_privilege_ids)
	{
		return array("show_select_all" => 1, "type" => $a_type, "privileges" => $a_privilege_ids);
	}

}

?>
