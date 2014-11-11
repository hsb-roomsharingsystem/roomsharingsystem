<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");

/**
 * Class ilRoomSharingRolePrivilegesTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
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

		$this->setTitle($this->lng->txt('rep_robj_xrs_privileges_settings'));
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
		$roles = $this->privileges->getRoles();

		foreach ($roles as $role)
		{
			$this->addColumn($this->createTitle($role), "", "", "", false, $this->createTooltip($role));
		}
	}

	private function createTooltip($a_role)
	{
		return "toller " . $a_role;
	}

	private function createTitle($a_role)
	{
		return '<a class="tblheader" href="' . $this->ctrl->getLinkTargetByClass('ilroomsharingprivilegesgui',
				'') . '" >' . $a_role . '</a>';
	}

}

?>