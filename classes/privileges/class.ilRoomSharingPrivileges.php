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
	private $groups_privileges;
	private $lng;

	/**
	 * Constructor of ilRoomSharingPrivileges
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id = 1)
	{
		global $lng;

		$this->pool_id = $a_pool_id;
		$this->lng = $lng;
		$this->ilRoomsharingDatabase = new ilRoomSharingDatabase($this->pool_id);
		$this->groups_privileges = $this->getAllGroupsPrivileges();
	}

	public function getPrivilegesMatrix()
	{
		$privilegesMatrix = array();

		// Lock Group
		$privilegesMatrix[] = array("show_lock_row" => "lock", "locked_groups" => $this->getLockedGroups());

		// ### Appointments ###
		$privilegesMatrix[] = $this->addNewSection("rep_robj_xrs_appointments",
			"rep_robj_xrs_appointments_privileges_description");
		$privilegesMatrix[] = $this->addPrivilege("accessAppointments",
			"rep_robj_xrs_access_appointments", "rep_robj_xrs_access_appointments_description");
		$privilegesMatrix[] = $this->addPrivilege("accessSearch", "rep_robj_xrs_access_search",
			"rep_robj_xrs_access_search_description");
		$privilegesMatrix[] = $this->addPrivilege("addOwnBookings", "rep_robj_xrs_create_edit_delete",
			"rep_robj_xrs_create_edit_delete_description");
		$privilegesMatrix[] = $this->addPrivilege("addParticipants", "rep_robj_xrs_add_participant",
			"rep_robj_xrs_add_participant_description");
		$privilegesMatrix[] = $this->addPrivilege("addSequenceBookings", "rep_robj_xrs_sequence_bookings",
			"rep_robj_xrs_sequence_bookings_addable");
		$privilegesMatrix[] = $this->addPrivilege("cancelBookingLowerPriority",
			"rep_robj_xrs_cancel_lower_priority", "rep_robj_xrs_cancel_lower_priority_description");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("bookings",
			array("accessAppointments", "accessSearch", "addParticipants",
			"addOwnBookings", "addSequenceBookings", "cancelBookingLowerPriority"));

		// ### Rooms ###
		$privilegesMatrix[] = $this->addNewSection("rep_robj_xrs_rooms",
			"rep_robj_xrs_rooms_privileges_description");
		$privilegesMatrix[] = $this->addPrivilege("accessRooms", "rep_robj_xrs_access_rooms",
			"rep_robj_xrs_access_rooms_description");
		$privilegesMatrix[] = $this->addPrivilege("seeBookingsOfRooms",
			"rep_robj_xrs_see_booking_of_rooms", "rep_robj_xrs_see_booking_of_rooms_description");
		$privilegesMatrix[] = $this->addPrivilege("addRooms", "rep_robj_xrs_create",
			"rep_robj_xrs_create_rooms_description");
		$privilegesMatrix[] = $this->addPrivilege("editRooms", "rep_robj_xrs_edit",
			"rep_robj_xrs_create_edit_rooms_description");
		$privilegesMatrix[] = $this->addPrivilege("deleteRooms", "rep_robj_xrs_delete",
			"rep_robj_xrs_create_delete_rooms_description");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("rooms",
			array("accessRooms", "seeBookingsOfRooms",
			"addRooms", "editRooms", "deleteRooms"));

		// ### Floorplans ###
		$privilegesMatrix[] = $this->addNewSection("rep_robj_xrs_floorplans",
			"rep_robj_xrs_floorplans_privileges_description");
		$privilegesMatrix[] = $this->addPrivilege("accessFloorplans", "rep_robj_xrs_access_floorplans",
			"rep_robj_xrs_access_floorplans_description");
		$privilegesMatrix[] = $this->addPrivilege("addFloorplans", "rep_robj_xrs_create",
			"rep_robj_xrs_create_floorplans_description");
		$privilegesMatrix[] = $this->addPrivilege("editFloorplans", "rep_robj_xrs_edit",
			"rep_robj_xrs_edit_floorplans_description");
		$privilegesMatrix[] = $this->addPrivilege("deleteFloorplans", "rep_robj_xrs_delete",
			"rep_robj_xrs_delete_floorplans_description");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("floorplans",
			array("accessFloorplans",
			"addFloorplans", "editFloorplans", "deleteFloorplans"));

		// ### Privileges ###
		$privilegesMatrix[] = $this->addNewSection("rep_robj_xrs_general_privileges",
			"rep_robj_xrs_general_privileges_description");
		$privilegesMatrix[] = $this->addPrivilege("accessSettings", "rep_robj_xrs_access_settings",
			"rep_robj_xrs_access_settings_description");
		$privilegesMatrix[] = $this->addPrivilege("accessPrivileges", "rep_robj_xrs_access_privileges",
			"rep_robj_xrs_access_privileges_description");
		$privilegesMatrix[] = $this->addPrivilege("addGroup", "rep_robj_xrs_create_group",
			"rep_robj_xrs_create_group_description");
		$privilegesMatrix[] = $this->addPrivilege("editPrivileges", "rep_robj_xrs_edit_privileges",
			"rep_robj_xrs_edit_privileges_description");
		$privilegesMatrix[] = $this->addPrivilege("lockPrivileges", "rep_robj_xrs_lock_privileges",
			"rep_robj_xrs_lock_privileges_description");
		$privilegesMatrix[] = $this->addSelectMultipleCheckbox("privileges",
			array("accessSettings",
			"accessPrivileges", "addGroup", "editPrivileges", "lockPrivileges"));
		return $privilegesMatrix;
	}

	public function getPrivileges()
	{
		$priv = array();
		$priv[] = 'accessAppointments';
		$priv[] = 'accessSearch';
		$priv[] = 'addOwnBookings';
		$priv[] = 'addParticipants';
		$priv[] = 'addSequenceBookings';
		$priv[] = 'cancelBookingLowerPriority';
		$priv[] = 'accessRooms';
		$priv[] = 'seeBookingsOfRooms';
		$priv[] = 'addRooms';
		$priv[] = 'editRooms';
		$priv[] = 'deleteRooms';
		$priv[] = 'accessFloorplans';
		$priv[] = 'addFloorplans';
		$priv[] = 'editFloorplans';
		$priv[] = 'deleteFloorplans';
		$priv[] = 'accessSettings';
		$priv[] = 'accessPrivileges';
		$priv[] = 'addGroup';
		$priv[] = 'editPrivileges';
		$priv[] = 'lockPrivileges';

		return $priv;
	}

	public function getGroups()
	{
		$grp = array();
		$groups = $this->ilRoomsharingDatabase->getGroups();
		foreach ($groups as $group)
		{
			$grp_values = $group;
			$grp_values['role'] = $this->getGlobalRoleTitle($group['role_id']);
			$grp[] = $grp_values;
		}

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

	public function editGroup($a_groupData)
	{
		if (!ilRoomSharingNumericUtils::isPositiveNumber($a_groupData['id']))
		{
			throw new ilRoomSharingPrivilegesException("rep_robj_xrs_group_id_incorrect");
		}
		$this->ilRoomsharingDatabase->updateGroup($a_groupData['id'], $a_groupData['name'],
			$a_groupData['description'], $a_groupData['role_id']);
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

	public function setPrivileges($a_privileges)
	{

		foreach ($a_privileges as $group_id => $given_privileges)
		{
			$privileges = array();
			foreach ($given_privileges as $given_privilege_key => $val)
			{
				$privileges[] = $given_privilege_key;
			}
			$no_privileges = array_diff($this->getPrivileges(), $privileges);
			$this->ilRoomsharingDatabase->setPrivilegesForGroup($group_id, $privileges, $no_privileges);
		}
	}

	public function setLockedGroups($a_group_ids)
	{
		$this->ilRoomsharingDatabase->setLockedGroups($a_group_ids);
	}

	public function getLockedGroups()
	{
		return $this->ilRoomsharingDatabase->getLockedGroups();
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
			array("title" => $this->lng->txt($a_section_title_lng_key),
				"description" => $this->lng->txt($a_section_description_lng_key)
			)
		);
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
				"name" => $this->lng->txt($a_name_lng_key),
				"description" => $this->lng->txt($a_description_lng_key)),
			"groups" => $this->getGroupsPrivilegeValue($a_id)
		);
	}

	private function getAllGroupsPrivileges()
	{
		$privileges = array();
		$groups = $this->ilRoomsharingDatabase->getGroups();
		foreach ($groups as $group)
		{
			$privileges[$group['id']] = array();
			$grp_privileges = $this->ilRoomsharingDatabase->getPrivilegesOfGroup($group['id']);
			foreach ($grp_privileges as $privilege_id => $privilege_value)
			{
				if ($privilege_value == 1)
				{
					$privileges[$group['id']][] = $privilege_id;
				}
			}
		}
		return $privileges;
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
		$privilegesArray = array();
		foreach ($this->groups_privileges as $group_id => $group_privileges_ids)
		{
			if (in_array(strtolower($a_privilege_id), $group_privileges_ids))
			{
				$privilegesArray[] = array("id" => $group_id, "privilege_set" => true);
			}
			else
			{
				$privilegesArray[] = array("id" => $group_id, "privilege_set" => false);
			}
		}
		return $privilegesArray;
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
