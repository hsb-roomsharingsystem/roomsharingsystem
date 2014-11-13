<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");

/**
 * Class ilRoomSharingRolePrivilegesTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 */
class ilRoomSharingRolePrivilegesTableGUI extends ilTable2GUI
{
	private $ctrl;
	private $privileges;

	/**
	 * Constructor
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;

		$this->privileges = new ilRoomSharingPrivileges($a_parent_obj->getPoolId());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("rep_robj_xrs_privileges_settings"));
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.obj_role_perm_row.html", "Services/AccessControl");
		$this->setLimit(100);
		$this->setShowRowsSelector(false);
		$this->addCommandButton('savePrivileges', $this->lng->txt('save'));

		$this->addColumns();
//		$this->populateTable();
	}

	private function populateTable()
	{
		$this->tpl->addJavaScript("Services/AccessControl/js/ilPermSelect.js");
		$data = $this->privileges->getPrivileges();

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	private function addColumns()
	{
		$roles = $this->privileges->getGlobalRoles();

		foreach ($roles as $role)
		{
			$this->addColumn($this->createTitle($role["title"]), "", "", "", false,
				$this->createTooltip($role["title"]));
		}
	}

	private function createTooltip($a_role)
	{
		return "HARDCODED LINK FOR " . $a_role;
	}

	private function createTitle($a_role)
	{
		$role_id = 255; // Guest
		$this->ctrl->setParameterByClass('ilroomsharingrolegui', "role_id", $role_id);

		return '<a class="tblheader" href="' . $this->ctrl->getLinkTargetByClass("ilroomsharingrolegui",
				"") . '" >' . $a_role . '</a>';
	}

}

?>