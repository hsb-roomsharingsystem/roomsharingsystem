<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");
require_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");

/**
 * Class ilRoomSharingClassPrivilegesTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 *
 * @version $Id$
 */
class ilRoomSharingClassPrivilegesTableGUI extends ilTable2GUI
{
	private $ctrl;
	private $privileges;
	private $ref_id;
	private $permission;
	private $class_array;

	/**
	 * Constructor of ilRoomSharingClassPrivilegesTableGUI
	 *
	 * @global type $ilCtrl
	 * @global type $lng
	 * @global type $rssPermission for retrieving user privilege information
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id = 1)
	{
		global $ilCtrl, $lng, $rssPermission;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->ref_id = $a_ref_id;
		$this->permission = $rssPermission;
		$this->privileges = new ilRoomSharingPrivileges($a_parent_obj->getPoolId());
		$this->class_array = $this->privileges->getClasses();

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->initTableProperties();
		$this->renderSaveButton();
		$this->addColumns();
		$this->fetchPrivilegeTableData();
	}

	private function initTableProperties()
	{
		global $tpl;

		$this->setId('class_priv_' . $this->ref_id);
		$this->setTitle($this->lng->txt("rep_robj_xrs_privileges_settings"));
		$this->setNoEntriesText($this->lng->txt("rep_robj_xrs_privileges_class_not_available"));
		$this->setEnableHeader(true);
		$this->setLimit(100);
		$this->setShowRowsSelector(false);
		$this->setRowTemplate("tpl.room_class_privileges_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/js/ilPrivilegesSelect.js");
	}

	private function renderSaveButton()
	{
		if ($this->isSavingAllowed())
		{
			$this->addCommandButton("savePrivilegeSettings", $this->lng->txt('save'));
		}
	}

	private function isSavingAllowed()
	{
		return !empty($this->class_array) && $this->permission->checkPrivilege("editPrivileges");
	}

	private function addColumns()
	{
		foreach ($this->class_array as $class_row)
		{
			$this->addColumn($this->createColumnHeader($class_row), "", "", "", false);
		}
	}

	private function createColumnHeader($a_class_row)
	{
		$assigned_role = $this->createAssignedRoleText($a_class_row["role"]);
		$class_name = $a_class_row["name"];
		$column_header = $class_name . $assigned_role;
		$this->ctrl->setParameterByClass("ilroomsharingclassgui", "class_id", $a_class_row["id"]);

		return $this->createClassLink($column_header);
	}

	private function createAssignedRoleText($a_role_name)
	{
		$right_double_arrow = " &#8658; ";
		if (isset($a_role_name))
		{
			return " &#8658; " . $a_role_name;
		}
	}

	private function createClassLink($a_class_text)
	{
		if ($this->permission->checkPrivilege("editClass"))
		{
			$link_target = $this->ctrl->getLinkTargetByClass("ilroomsharingclassgui", "");
			return '<a class="tblheader" href="' . $link_target . '" >' . $a_class_text . "</a>";
		}
		else
		{
			return $a_class_text;
		}
	}

	private function fetchPrivilegeTableData()
	{
		$data = $this->privileges->getPrivilegesMatrix();

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	/**
	 * Overriden method of ilTable2GUI. Is required to force upon tooltips for the column headers.
	 */
	public function fillHeader()
	{
		parent::fillHeader();
		$this->addTooltips();
	}

	private function addTooltips()
	{
		$tbl_header_id = 1;
		foreach ($this->class_array as $class_row)
		{
			$tooltip_text = $this->createTooltipText($class_row);
			ilTooltipGUI::addTooltip("thc_" . $this->getId() . "_" . $tbl_header_id, $tooltip_text, "",
				"bottom center", "top center", false);
			$tbl_header_id++;
		}
	}

	private function createTooltipText($a_class_row)
	{
		$carriage_return = "&#13;&#10;";
		$class_text = $this->createTooltipClassText($a_class_row["name"]);
		$role_text = $this->createTooltipRoleText($a_class_row["role"]);
		$class_priority_text = $this->createTooltipClassPriorityText($a_class_row["priority"]);

		$tooltip_text = "<pre>"
			. $class_text . $carriage_return
			. $role_text . $carriage_return
			. $class_priority_text . "</pre>";

		return $tooltip_text;
	}

	private function createTooltipClassText($a_class_name)
	{
		return $this->lng->txt("rep_robj_xrs_class") . ": " . $a_class_name;
	}

	private function createTooltipRoleText($a_assigned_role)
	{
		$role_assignment_text = $this->lng->txt("rep_robj_xrs_privileges_role_assignment");
		if (empty($a_assigned_role))
		{
			return $role_assignment_text . ": " . $this->lng->txt("none");
		}
		else
		{
			return $role_assignment_text . ": " . $a_assigned_role;
		}
	}

	private function createTooltipClassPriorityText($a_class_priority)
	{
		return $this->lng->txt("rep_robj_xrs_class_priority") . ": " . $a_class_priority;
	}

	/**
	 * Fills an entire table row with a given data row. The row comes in four different flavours:
	 * 1. a row that displays checkboxes for locking privileges
	 * 2. a section info row, which groups its underlying privilieges
	 * 3. a row for displaying a "select all" checkbox for selecting grouped privileges
	 * 4. a checkbox row for a privilege
	 *
	 * @see ilTable2GUI::fillRow()
	 * @param $a_table_row data set for that row
	 */
	public function fillRow($a_table_row)
	{
		if (isset($a_table_row["show_lock_row"]))
		{
			$this->fillLockRow();
			return true;
		}

		if (isset($a_table_row["show_section_info"]))
		{
			$this->fillSectionInfoRow($a_table_row["section"]);
			return true;
		}

		if (isset($a_table_row["show_select_all"]))
		{
			$this->fillSelectAllRow($a_table_row["type"], $a_table_row["privileges"]);
			return true;
		}

		$this->fillPrivilegeRow($a_table_row["privilege"], $a_table_row["classes"]);
	}

	private function fillLockRow()
	{
		foreach ($this->class_array as $class_row)
		{
			$this->fillPrivilegeLockData($class_row["id"]);
		}
	}

	private function fillPrivilegeLockData($a_class_id)
	{
		$this->tpl->setCurrentBlock("class_lock");
		$this->tpl->setVariable("LOCK_CLASS_ID", $a_class_id);
		$this->tpl->setVariable("TXT_LOCK", $this->lng->txt("rep_robj_xrs_privileges_lock"));
		$this->tpl->setVariable("TXT_LOCK_LONG", $this->lng->txt("rep_robj_xrs_privileges_lock_desc"));

		if ($this->isClassLocked($a_class_id))
		{
			$this->tpl->setVariable("LOCK_CHECKED", "checked='checked'");
		}

		if ($this->isLockingDisallowed())
		{
			$this->tpl->setVariable("LOCK_DISABLED", "disabled='disabled'");
		}

		$this->privileges->getLockedClasses();
		$this->tpl->parseCurrentBlock();
	}

	private function isClassLocked($a_class_id)
	{
		return in_array($a_class_id, $this->privileges->getLockedClasses());
	}

	private function isLockingDisallowed()
	{
		return !$this->permission->checkPrivilege("lockPrivileges") || !$this->permission->checkPrivilege("editPrivileges");
	}

	private function fillSectionInfoRow($a_section_info_row)
	{
		$this->tpl->setCurrentBlock("section_info");
		$this->tpl->setVariable("SECTION_TITLE", $a_section_info_row["title"]);
		$this->tpl->setVariable("SECTION_DESC", $a_section_info_row["description"]);
		$this->tpl->parseCurrentBlock();
	}

	private function fillSelectAllRow($a_type, $a_privileges_for_type)
	{
		foreach ($this->class_array as $class_row)
		{
			$this->fillSelectAllData($class_row["id"], $a_type, $a_privileges_for_type);
		}
	}

	private function fillSelectAllData($a_class_id, $a_type, $a_privileges_for_type)
	{
		$this->tpl->setCurrentBlock("class_select_all");
		$this->tpl->setVariable("JS_CLASS_ID", $a_class_id);
		$this->tpl->setVariable("JS_FORM_NAME", $this->getFormName());
		$this->tpl->setVariable("JS_SUBID", $a_type);
		$this->tpl->setVariable("JS_ALL_PRIVS", "['" . implode("','", $a_privileges_for_type) . "']");
		$this->tpl->setVariable("TXT_SEL_ALL", $this->lng->txt("select_all"));

		if (!$this->permission->checkPrivilege("editPrivileges"))
		{
			$this->tpl->setVariable("PRIV_DISABLED", 'disabled="disabled"');
		}

		$this->tpl->parseCurrentBlock();
	}

	private function fillPrivilegeRow($a_privilege_row, $a_class_array)
	{
		foreach ($a_class_array as $class_row)
		{
			$this->fillPrivilegeData($a_privilege_row, $class_row);
		}
	}

	private function fillPrivilegeData($a_privilege_row, $a_class_row)
	{
		$this->tpl->setCurrentBlock("class_td");
		$this->tpl->setVariable("PRIV_CLASS_ID", $a_class_row["id"]);
		$this->tpl->setVariable("PRIV_ID", $a_privilege_row["id"]);

		$this->tpl->setVariable("TXT_PRIV", $a_privilege_row["name"]);

		$this->tpl->setVariable("TXT_PRIV_LONG", $a_privilege_row["description"]);

		if ($a_class_row["privilege_set"])
		{
			$this->tpl->setVariable("PRIV_CHECKED", 'checked="checked"');
		}

		if (!$this->permission->checkPrivilege("editPrivileges"))
		{
			$this->tpl->setVariable("PRIV_DISABLED", 'disabled="disabled"');
		}

		$this->tpl->parseCurrentBlock();
	}

}

?>
