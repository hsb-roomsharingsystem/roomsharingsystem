<?php

require_once("Services/AccessControl/classes/class.ilObjRoleGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingAssignedUsersTableGUI.php");

/**
 * Class ilRoomSharingRoleGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilRoomSharingRoleGUI: ilRepositorySearchGUI
 */
class ilRoomSharingRoleGUI
{
	private $parent;
	private $role_id;
	private $pool_id;
	private $ctrl;
	private $lng;
	private $tpl;
	private $tabs;
	private $privileges;

	public function __construct($parent, $role_id)
	{
		global $ilCtrl, $lng, $tpl, $ilTabs;

		$this->parent = $parent;
		$this->role_id = $role_id;
		$this->pool_id = $this->parent->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->privileges = new ilRoomSharingPrivileges();
	}

	/**
	 * Executes the command given by ilCtrl.
	 */
	public function executeCommand()
	{
		$this->renderPage();
		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class)
		{
			case 'ilrepositorysearchgui':
				$rep_search = & new ilRepositorySearchGUI();
				$rep_search->setTitle($this->lng->txt("role_add_user"));
				$rep_search->setCallback($this, "addUsersToRole");

				// Tabs
				$this->tabs->setTabActive("user_assignment");
				$this->ctrl->setReturn($this, "renderUserAssignment");
				$this->ctrl->forwardCommand($rep_search);
				break;

			default:
				$cmd = $this->ctrl->getCmd("renderEditRoleForm");
				$this->$cmd();
				break;
		}
		return true;
	}

	private function renderPage()
	{
		$this->tabs->clearTargets();

		// Title
		$this->tpl->setTitle("HARDCODED ROLE");
		// Description
		$this->tpl->setDescription("HARDCODED DESCRIPTION");
		// Icon
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role_b.png"), "HARCODED ROLESYMBOL");
		$this->setTabs();
	}

	private function setTabs()
	{
		// Back-Link
		$this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges"),
			$this->ctrl->getLinkTargetByClass("ilroomsharingprivilegesgui", "showPrivileges"));

		// Edit Role
		$this->tabs->addTab("edit_properties", $this->lng->txt("edit_properties"),
			$this->ctrl->getLinkTarget($this, "renderEditRoleForm"));

		// User Assignment
		$this->tabs->addTab("user_assignment", $this->lng->txt("user_assignment"),
			$this->ctrl->getLinkTarget($this, "renderUserAssignment"));
	}

	private function renderEditRoleForm()
	{
		$this->tabs->setTabActive("edit_properties");

		$role_form = $this->createEditRoleForm();
		$this->tpl->setContent($role_form->getHTML());
	}

	private function createEditRoleForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("role_edit"));
		$form->addCommandButton("saveEditedRoleForm", $this->lng->txt("save"));

		// Name
		$name = new ilTextInputGUI($this->lng->txt("name"), "name");
		$name->setSize(40);
		$name->setMaxLength(70);
		$name->setRequired(true);
		$name->setValue("HARDCODED ROLE");
		$form->addItem($name);

		// Description
		$description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		$description->setCols(40);
		$description->setRows(3);
		$description->setValue("HARDCODED DESCRIPTION");
		$form->addItem($description);

		// Role assignment
		$role_assignment = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_privileges_role_assignment"),
			"role_assignment");
		$role_names = array();
		$global_roles = $this->privileges->getGlobalRoles();

		foreach ($global_roles as $role)
		{
			$role_names[] = $role["title"];
		}

		$role_assignment->setOptions($role_names);
		$form->addItem($role_assignment);

		return $form;
	}

	private function saveEditedRoleForm()
	{
		$role_form = $this->createEditRoleForm();
		if ($role_form->checkInput())
		{
			$this->evaluateRoleFormEntries($role_form);
			$this->renderEditRoleForm();
		}
		else
		{
			$role_form->setValuesByPost();
			$this->renderEditRoleForm();
		}
	}

	private function evaluateRoleFormEntries($a_role_form)
	{
		$entries = array();
		$entries["name"] = $a_role_form->getInput("name");
		$entries["description"] = $a_role_form->getInput("description");
		$entries["role_assignment"] = $a_role_form->getInput("role_assignment");

		$this->saveFormEntries($entries);
	}

	private function saveFormEntries($a_entries)
	{
		ilUtil::sendSuccess("HARDCODED MESSAGE FOR NOT HARDCODED ROLE FORM ENTRIES " . implode(", ",
				$a_entries), true);
	}

	private function renderUserAssignment()
	{
		$this->tabs->setTabActive("user_assignment");

		// Toolbar
		$toolbar = new ilToolbarGUI();
		ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $toolbar,
			array(
			"auto_complete_name" => $this->lng->txt('user'),
			"submit_name" => $this->lng->txt("add")
			)
		);

		$toolbar->addSpacer();

		$toolbar->addButton(
			$this->lng->txt("search_user"),
			$this->ctrl->getLinkTargetByClass("ilRepositorySearchGUI", "start")
		);

		// Assigned Users Table
		$table = new ilRoomSharingAssignedUsersTableGUI($this, "renderUserAssignment", $this->pool_id);
		$this->tpl->setContent($toolbar->getHTML() . $table->getHTML());
	}

	public function addUsersToRole($a_user_ids)
	{
		ilUtil::sendSuccess($this->lng->txt("HARDCODED MESSAGE FOR NOT HARDCODED ADDED USER IDS " . implode(", ",
					$a_user_ids)), true);
		$this->ctrl->redirect($this, "renderuserassignment");
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return integer Pool-ID
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 *
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>