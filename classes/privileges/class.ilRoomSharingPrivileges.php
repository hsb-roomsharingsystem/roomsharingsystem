<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

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

	public function getPrivileges()
	{
		$priv[] = array(
			"book" => true,
		);

		return $priv;
	}

	public function getRoles()
	{

		$roles = array("User", "Student");

		return $roles;
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
		//TODO ERROR CATCHING
		$this->ilRoomsharingDatabase->insertGroup($a_groupData['name'], $a_groupData['description'],
			$a_groupData['role_id']);
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