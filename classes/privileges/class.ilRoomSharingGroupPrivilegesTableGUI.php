<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");

/**
 * Class ilRoomSharingGroupPrivilegesTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 *
 * @version $Id$
 */
class ilRoomSharingGroupPrivilegesTableGUI extends ilTable2GUI
{
	private $ctrl;
	private $privileges;
	private $ref_id;

	/**
	 * Constructor of ilRoomSharingGroupPrivilegesTableGUI
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id = 1)
	{
		global $ilCtrl, $lng;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->ref_id = $a_ref_id;

		$this->privileges = new ilRoomSharingPrivileges($a_parent_obj->getPoolId());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId('group_priv_' . $this->ref_id);
		$this->setTitle($this->lng->txt("rep_robj_xrs_privileges_settings"));
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setLimit(100);
		$this->setShowRowsSelector(false);
		$this->addCommandButton('savePrivileges', $this->lng->txt('save'));

		$this->addColumns();
		$this->populateTable();
	}

	private function populateTable()
	{
		global $tpl;

		$this->setRowTemplate("tpl.room_group_privileges_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/js/ilPrivilegesSelect.js");

		$data = $this->privileges->getPrivilegesMatrix();

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	private function addColumns()
	{
		$groups = $this->privileges->getGroups();

		foreach ($groups as $group)
		{
			$this->addColumn($this->createTitle($group), "", "", "", false, $this->createTooltip($group));
		}
	}

	public function fillRow($a_table_row)
	{
		// Lock Group
		if (isset($a_table_row["show_lock_row"]))
		{
			$groups = $this->privileges->getGroups();
			foreach ($groups as $group)
			{
				$this->tpl->setCurrentBlock("group_lock");
				$this->tpl->setVariable("LOCK_GROUP_ID", $group["id"]);
				$this->tpl->setVariable("TXT_LOCK", $this->lng->txt("rep_robj_xrs_privileges_lock"));
				$this->tpl->setVariable("TXT_LOCK_LONG", $this->lng->txt("rep_robj_xrs_privileges_lock_desc"));

				if (in_array($group["id"], $a_table_row["locked_groups"]))
				{
					$this->tpl->setVariable("LOCK_CHECKED", "checked='checked'");
				}

				$this->tpl->parseCurrentBlock();
			}
			return true;
		}

		// Section info
		if (isset($a_table_row["show_section_info"]))
		{
			$this->tpl->setCurrentBlock("section_info");
			$this->tpl->setVariable("SECTION_TITLE", $this->lng->txt($a_table_row["section"]["title"]));
			$this->tpl->setVariable("SECTION_DESC", $this->lng->txt($a_table_row["section"]["description"]));
			$this->tpl->parseCurrentBlock();

			return true;
		}

		// Select all
		if (isset($a_table_row['show_select_all']))
		{
			$groups = $this->privileges->getGroups();

			foreach ($groups as $group)
			{
				$this->tpl->setCurrentBlock("group_select_all");
				$this->tpl->setVariable("JS_GROUP_ID", $group["id"]);
				$this->tpl->setVariable("JS_FORM_NAME", $this->getFormName());
				$this->tpl->setVariable("JS_SUBID", $a_table_row["type"]);
				$this->tpl->setVariable("JS_ALL_PRIVS", "['" . implode("','", $a_table_row["privileges"]) . "']");
				$this->tpl->setVariable("TXT_SEL_ALL", $this->lng->txt("select_all"));
				$this->tpl->parseCurrentBlock();
			}
			return true;
		}

		// Privileges
		foreach ($a_table_row["groups"] as $group)
		{
			$this->tpl->setCurrentBlock("group_td");
			$this->tpl->setVariable("PRIV_GROUP_ID", $group["id"]);
			$this->tpl->setVariable("PRIV_ID", $a_table_row["privilege"]["id"]);

			$this->tpl->setVariable("TXT_PRIV", $this->lng->txt($a_table_row["privilege"]["name"]));

			$this->tpl->setVariable("TXT_PRIV_LONG",
				$this->lng->txt($a_table_row["privilege"]["description"]));

			if ($group["privilege_set"])
			{
				$this->tpl->setVariable("PRIV_CHECKED", 'checked="checked"');
			}

			$this->tpl->parseCurrentBlock();
		}
	}

	private function createTooltip($a_group_set)
	{
		$role_text = $this->isGroupAssignedToRole($a_group_set) ? $a_group_set["role"] : $this->lng->txt("none");

		return $this->lng->txt("group") . ": " . $a_group_set["name"] . " " . $this->lng->txt("rep_robj_xrs_privileges_role_assignment") . ": " . $role_text;
	}

	private function createTitle($a_group_set)
	{
		// &#8658; = Unicode double arrow to the right
		$assigned_role = $this->isGroupAssignedToRole($a_group_set) ? " &#8658; " . $a_group_set["role"] : null;
		$table_head = $a_group_set["name"] . $assigned_role;

		$this->ctrl->setParameterByClass("ilroomsharinggroupgui", "group_id", $a_group_set["id"]);

		return '<a class="tblheader" href="' . $this->ctrl->getLinkTargetByClass("ilroomsharinggroupgui",
				"") . '" >' . $table_head . "</a>";
	}

	private function isGroupAssignedToRole($a_group_set)
	{
		return !empty($a_group_set["role"]);
	}

}

?>