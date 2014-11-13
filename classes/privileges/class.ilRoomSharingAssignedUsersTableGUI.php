<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");

/**
 * Class class.ilRoomSharingAssignedUsersTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 */
class ilRoomSharingAssignedUsersTableGUI extends ilTable2GUI
{
	private $ctrl;
	private $privileges;

	/**
	 * Constructor for ilRoomSharingAssignedUsersTableGUI
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_role_id)
	{
		global $ilCtrl, $lng;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->role_id = $a_role_id;

		$this->privileges = new ilRoomSharingPrivileges($a_parent_obj->getPoolId());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.user_assignment_row.html", "Services/AccessControl");

		$this->setEnableTitle(true);
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");

		$this->setShowRowsSelector(true);

		$this->addColumns();
//		$this->populateTable();
	}

	private function populateTable()
	{
		$data = $this->privileges->getPrivileges();

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	private function addColumns()
	{
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("login"), "login", "29%");
		$this->addColumn($this->lng->txt("firstname"), "firstname", "29%");
		$this->addColumn($this->lng->txt("lastname"), "lastname", "29%");
		$this->addColumn($this->lng->txt('actions'), '', '13%');
	}

}

?>