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

	/**
	 * Constructor of ilRoomSharingPrivileges
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id = 1)
	{
		$this->pool_id = $a_pool_id;
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

}

?>