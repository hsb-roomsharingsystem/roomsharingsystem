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
	private $parent;

	/**
	 * Constructor for ilRoomSharingAssignedUsersTableGUI
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_group_id)
	{
		global $ilCtrl, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->group_id = $a_group_id;
		$this->parent = $a_parent_obj;

		$this->privileges = new ilRoomSharingPrivileges($a_parent_obj->getPoolId());



		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.room_user_assignment_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");

		$this->setEnableTitle(true);
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");

		$this->setShowRowsSelector(true);
		$this->setSelectAllCheckbox("user_id[]");
		$this->addMultiCommand("deassignUsers", $lng->txt("remove"));

		$this->addColumns();
		$this->populateTable();
	}

	private function populateTable()
	{
		$data = $this->privileges->getAssignedUsersForClass($this->group_id);

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	public function fillRow($a_user_data)
	{
		$this->tpl->setVariable("ID", $a_user_data["id"]);
		$this->tpl->setVariable("TXT_LOGIN", $a_user_data["login"]);
		$this->tpl->setVariable("TXT_FIRSTNAME", $a_user_data["firstname"]);
		$this->tpl->setVariable("TXT_LASTNAME", $a_user_data["lastname"]);
		$this->ctrl->setParameter($this->parent, "user_id", $a_user_data["id"]);
		$this->tpl->setVariable("LINK_ACTION", $this->ctrl->getLinkTarget($this->parent, "deassignUsers"));
		$this->tpl->setVariable("LINK_ACTION_TXT", $this->lng->txt("remove"));
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