<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingAssignedUsersTableGUI.php");
require_once ("Services/Utilities/classes/class.ilConfirmationGUI.php");

/**
 * Class ilRoomSharingClassGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilRoomSharingClassGUI: ilRepositorySearchGUI
 */
class ilRoomSharingClassGUI
{
	private $parent;
	private $class_id;
	private $pool_id;
	private $ctrl;
	private $lng;
	private $tpl;
	private $tabs;
	private $privileges;
	private $permission;

	/**
	 * Constructor of ilRoomSharingClassGUI
	 *
	 * @global type $ilCtrl
	 * @global type $lng
	 * @global type $tpl
	 * @global type $ilTabs needed since this class represents its own distinct gui with unique tabs
	 * @global type $rssPermission for retrieving privilege information of a user
	 * @param type $a_parent
	 * @param type $a_class_id id of the class for which this GUI is generated
	 */
	public function __construct($a_parent, $a_class_id)
	{
		global $ilCtrl, $lng, $tpl, $ilTabs, $rssPermission;

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->parent = $a_parent;
		$this->tabs = $ilTabs;
		$this->permission = $rssPermission;
		$this->pool_id = $this->parent->getPoolId();
		$this->class_id = $a_class_id ? $a_class_id : $_GET["class_id"];
		$this->ctrl->saveParameter($this, "class_id");
		$this->privileges = new ilRoomSharingPrivileges();
	}

	/**
	 * Executes the command given by ilCtrl.
	 */
	public function executeCommand()
	{
		$this->renderPageWithTabs();
		$next_class = $this->ctrl->getNextClass($this);

		if ($next_class == "ilrepositorysearchgui")
		{
			$this->renderRepositorySearch();
		}
		else
		{
			$this->executeDefaultCommand();
		}
	}

	private function renderPageWithTabs()
	{
		$class_info = $this->privileges->getClassById($this->class_id);
		$this->tpl->setTitle($class_info["name"]);
		$description = $class_info["description"];
		$this->tpl->setDescription($description);
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role_b.png"),
			$this->lng->txt("rep_robj_xrs_class"));
		$this->setTabs();
	}

	private function setTabs()
	{
		$this->tabs->clearTargets();
		$this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges"),
			$this->ctrl->getLinkTargetByClass("ilroomsharingprivilegesgui", "showPrivileges"));

		$this->tabs->addTab("edit_properties", $this->lng->txt("edit_properties"),
			$this->ctrl->getLinkTarget($this, "renderEditClassForm"));

		$this->tabs->addTab("user_assignment", $this->lng->txt("user_assignment"),
			$this->ctrl->getLinkTarget($this, "renderUserAssignment"));
	}

	private function renderRepositorySearch()
	{
		$rep_search = & new ilRepositorySearchGUI();
		$rep_search->setTitle($this->lng->txt("role_add_user"));
		$rep_search->setCallback($this, "assignUsersToClass");
		$this->tabs->setTabActive("user_assignment");
		$this->ctrl->setReturn($this, "renderUserAssignment");
		$this->ctrl->forwardCommand($rep_search);
	}

	private function executeDefaultCommand()
	{
		$cmd = $this->ctrl->getCmd("renderEditClassForm");
		$this->$cmd();
	}

	private function renderEditClassForm()
	{
		$this->tabs->setTabActive("edit_properties");

		$toolbar = $this->createEditClassFormToolbar();
		$class_form = $this->createEditClassFormWithPrivilegeCheck();
		$this->tpl->setContent($toolbar->getHTML() . $class_form->getHTML());
	}

	private function createEditClassFormToolbar()
	{
		$toolbar = new ilToolbarGUI();

		if ($this->permission->checkPrivilege("deleteClass"))
		{
			$toolbar->addButton($this->lng->txt("rep_robj_xrs_class_confirm_deletion"),
				$this->ctrl->getLinkTarget($this, "renderConfirmClassDeletion"));
		}

		return $toolbar;
	}

	private function createEditClassFormWithPrivilegeCheck()
	{
		if ($this->permission->checkPrivilege("editClass"))
		{
			$form = $this->createEditClassForm();
			return $form;
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
		}
	}

	private function createEditClassForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("rep_robj_xrs_privileges_class_edit"));
		$form->addCommandButton("saveEditClassForm", $this->lng->txt("save"));
		$form_items = $this->createEditClassFormItems();

		foreach ($form_items as $item)
		{
			$form->addItem($item);
		}

		return $form;
	}

	private function createEditClassFormItems()
	{
		$class_info = $this->privileges->getClassById($this->class_id);

		$form_items = array();
		$form_items[] = $this->createClassNameTextInput($class_info["name"]);
		$form_items[] = $this->createClassDescriptionTextArea($class_info["description"]);
		$form_items[] = $this->createClassRoleAssignmentSelection($class_info["role_id"]);
		$form_items[] = $this->createClassPrioritySelection($class_info["priority"]);

		return $form_items;
	}

	private function createClassNameTextInput($a_class_name_value)
	{
		$name_input = new ilTextInputGUI($this->lng->txt("name"), "name");
		$name_input->setSize(45);
		$name_input->setMaxLength(70);
		$name_input->setRequired(true);
		$name_input->setValue($a_class_name_value);

		return $name_input;
	}

	private function createClassDescriptionTextArea($a_description_value)
	{
		$description_area = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		$description_area->setCols(40);
		$description_area->setRows(3);
		$description_area->setValue($a_description_value);

		return $description_area;
	}

	private function createClassRoleAssignmentSelection($a_assigned_role_id)
	{
		$role_assignment_selection = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_privileges_role_assignment"),
			"role_assignment");

		$role_options = $this->createRoleAssignmentOptions();
		$role_assignment_selection->setOptions($role_options);
		$selection_index = $this->determineSelectionIndex($a_assigned_role_id);
		$role_assignment_selection->setValue($selection_index);

		return $role_assignment_selection;
	}

	private function createRoleAssignmentOptions()
	{
		$role_names = array($this->lng->txt("none"));
		$global_roles = $this->privileges->getGlobalRoles();

		foreach ($global_roles as $role_info)
		{
			$role_names[] = $role_info["title"];
		}

		return $role_names;
	}

	private function determineSelectionIndex($a_assigned_role_id)
	{
		$selection_index = $this->getSelectionIndexByRoleId($a_assigned_role_id);
		$selection_index += ilRoomSharingPrivilegesGUI::SELECT_INPUT_NONE_OFFSET;

		return $selection_index;
	}

	private function getSelectionIndexByRoleId($a_assigned_role_id)
	{
		$global_roles = $this->privileges->getGlobalRoles();
		$selection_index = -1;

		foreach ($global_roles as $role_index => $role_info)
		{
			if ($role_info["id"] == $a_assigned_role_id)
			{
				$selection_index = $role_index;
				break;
			}
		}

		return $selection_index;
	}

	private function createClassPrioritySelection($a_class_priority_selection_value)
	{
		$priority_selection = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_class_priority"),
			"priority");
		$priority_levels = range(0, 9);
		$priority_selection->setOptions($priority_levels);
		$priority_selection->setValue($a_class_priority_selection_value);

		return $priority_selection;
	}

	private function saveEditClassForm()
	{
		$class_form = $this->createEditClassFormWithPrivilegeCheck();
		if ($class_form->checkInput())
		{
			$this->handleValidEditClassForm($class_form);
		}
		else
		{
			$this->handleInvalidEditClassForm($class_form);
		}
	}

	private function handleValidEditClassForm($a_class_form)
	{
		$class_form_entries = $this->getClassFormEntries($a_class_form);
		$this->saveFormEntries($class_form_entries);

		$this->renderPageWithTabs();
		$this->renderEditClassForm();
	}

	private function getClassFormEntries($a_class_form)
	{
		$entries = array();
		$entries["id"] = $this->class_id;
		$entries["name"] = $a_class_form->getInput("name");
		$entries["priority"] = $a_class_form->getInput("priority");
		$entries["description"] = $a_class_form->getInput("description");
		$entries["role_id"] = $this->getRoleIdFromSelectionInput($a_class_form->getInput("role_assignment"));

		return $entries;
	}

	private function getRoleIdFromSelectionInput($a_role_assignment_selection)
	{
		$global_roles = $this->privileges->getGlobalRoles();
		$role_array_index = $a_role_assignment_selection - ilRoomSharingPrivilegesGUI::SELECT_INPUT_NONE_OFFSET;

		return $global_roles[$role_array_index]["id"];
	}

	private function saveFormEntries($a_entries)
	{
		try
		{
			$this->privileges->editClass($a_entries);
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
		}
		catch (ilRoomSharingPrivilegesException $ex)
		{
			ilUtil::sendFailure($this->lng->txt($ex->getMessage()), true);
		}
	}

	private function handleInvalidEditClassForm($a_class_form)
	{
		$a_class_form->setValuesByPost();
		$this->tpl->setContent($a_class_form->getHTML());
		$this->tabs->setTabActive("edit_properties");
		ilUtil::sendFailure($this->lng->txt("err_check_input"));
	}

	/**
	 * Renders the confirmation dialog for a class deletion.
	 */
	public function renderConfirmClassDeletion()
	{
		$this->tabs->clearTargets();
		$this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_class_back"),
			$this->ctrl->getLinkTarget($this, "renderEditClassForm"));
		$confirm_dialog = $this->createClassDeletionConfirmationDialog();

		$this->tpl->setContent($confirm_dialog->getHTML());
	}

	private function createClassDeletionConfirmationDialog()
	{
		$confirm_dialog = new ilConfirmationGUI();
		$confirm_dialog->setFormAction($this->ctrl->getFormAction($this));
		$confirm_dialog->setHeaderText($this->lng->txt("rep_robj_xrs_class_confirm_deletion_header"));

		$class_name = $this->privileges->getClassById($this->class_id);
		$confirm_dialog->addItem("class_id", $this->class_id, $class_name["name"]);
		$confirm_dialog->setConfirm($this->lng->txt("rep_robj_xrs_class_confirm_deletion"), "deleteClass");
		$confirm_dialog->setCancel($this->lng->txt("cancel"), "renderEditClassForm");

		return $confirm_dialog;
	}

	/**
	 * Deletes a class after the confirmation dialog has been confirmed.
	 */
	public function deleteClass()
	{
		$this->privileges->deleteClass($this->class_id);
		$this->ctrl->redirectByClass("ilroomsharingprivilegesgui", "showConfirmedClassDeletion");
	}

	private function renderUserAssignment()
	{
		$this->tabs->setTabActive("user_assignment");
		$user_assignment_toolbar = $this->createUserAssignmentToolbar();
		$table = new ilRoomSharingAssignedUsersTableGUI($this, "renderUserAssignment", $this->class_id);

		$this->tpl->setContent($user_assignment_toolbar->getHTML() . $table->getHTML());
	}

	private function createUserAssignmentToolbar()
	{
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

		return $toolbar;
	}

	/**
	 * Assigns the given users to a class if said users aren't already assigned to that class.
	 *
	 * @param type $a_user_ids the ids of the users that should be assigned to the class
	 */
	public function assignUsersToClass($a_user_ids)
	{
		if ($this->areUsersAlreadyAssigned($a_user_ids))
		{
			ilUtil::sendInfo($this->lng->txt("rep_robj_xrs_class_user_already_assigned"), true);
		}
		else
		{
			$this->privileges->assignUsersToClass($this->class_id, $a_user_ids);
			ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_class_assignment_successful"), true);
		}

		$this->ctrl->redirect($this, "renderuserassignment");
	}

	private function areUsersAlreadyAssigned($a_user_ids)
	{
		$assigned_user_ids = $this->getAssignedUserIdsForClass($this->class_id);
		$unassigned_user_ids = array_intersect($a_user_ids, $assigned_user_ids);
		$new_assigned_user_ids = array_diff($a_user_ids, $unassigned_user_ids);

		return empty($new_assigned_user_ids);
	}

	private function getAssignedUserIdsForClass($a_class_id)
	{
		$assigned_user_ids = array();
		$assigned_users = $this->privileges->getAssignedUsersForClass($a_class_id);

		foreach ($assigned_users as $user)
		{
			$assigned_user_ids[] = $user["id"];
		}

		return $assigned_user_ids;
	}

	/**
	 * Deassigns users from a class. The ids of the users that should be deassigned are either
	 * delivered through POST (checkboxes: multiple users) or via GET (action link: single user).
	 */
	public function deassignUsersFromClass()
	{
		$user_ids_to_be_deassigned = $this->getUsersToBeUnassigned();
		$this->privileges->deassignUsersFromClass($this->class_id, $user_ids_to_be_deassigned);
		ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_class_deassignment_successful"), true);
		$this->renderUserAssignment();
	}

	private function getUsersToBeUnassigned()
	{
		$many_user_ids = $_POST["user_id"];

		if (isset($many_user_ids))
		{
			return $many_user_ids;
		}
		else
		{
			$single_user_id = array($_GET["user_id"]);
			return $single_user_id;
		}
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return integer Pool-ID
	 */
	public function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 *
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
