<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingAssignedUsersTableGUI.php");
require_once ("Services/Utilities/classes/class.ilConfirmationGUI.php");

/**
 * Class ilRoomSharingGroupGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilRoomSharingGroupGUI: ilRepositorySearchGUI
 */
class ilRoomSharingGroupGUI
{
    private $parent;
    private $group_id;
    private $pool_id;
    private $ctrl;
    private $lng;
    private $tpl;
    private $tabs;
    private $privileges;

    public function __construct($a_parent, $a_group_id)
    {
        global $ilCtrl, $lng, $tpl, $ilTabs;

        $this->parent = $a_parent;
        $this->pool_id = $this->parent->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->group_id = $a_group_id ? $a_group_id : $_GET["group_id"];
        $this->ctrl->saveParameter($this, "group_id");
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
                $rep_search->setCallback($this, "addUsersToGroup");

                // Tabs
                $this->tabs->setTabActive("user_assignment");
                $this->ctrl->setReturn($this, "renderUserAssignment");
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $this->ctrl->getCmd("renderEditGroupForm");
                $this->$cmd();
                break;
        }
        return true;
    }

    private function renderPage()
    {
        $this->tabs->clearTargets();
        $group_info = $this->privileges->getGroupById($this->group_id);

        // Title
        $this->tpl->setTitle($group_info["name"]);
        // Description
        $this->tpl->setDescription($group_info["description"]);
        // Icon
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role_b.png"), "HARDCODED GROUPSYMBOL");
        $this->setTabs();
    }

    private function setTabs()
    {
        // Back-Link
        $this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges"), $this->ctrl->getLinkTargetByClass("ilroomsharingprivilegesgui", "showPrivileges"));

        // Edit Group
        $this->tabs->addTab("edit_properties", $this->lng->txt("edit_properties"), $this->ctrl->getLinkTarget($this, "renderEditGroupForm"));

        // User Assignment
        $this->tabs->addTab("user_assignment", $this->lng->txt("user_assignment"), $this->ctrl->getLinkTarget($this, "renderUserAssignment"));
    }

    private function renderEditGroupForm()
    {
        $this->tabs->setTabActive("edit_properties");

        $toolbar = new ilToolbarGUI();
        $toolbar->addButton($this->lng->txt("rep_robj_xrs_group_confirm_deletion"), $this->ctrl->getLinkTarget($this, "confirmGroupDeletion"));

        $group_form = $this->createEditGroupForm();
        $this->tpl->setContent($toolbar->getHTML() . $group_form->getHTML());
    }

    private function createEditGroupForm()
    {
        $group_info = $this->privileges->getGroupById($this->group_id);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("rep_robj_xrs_privileges_group_edit"));
        $form->addCommandButton("saveEditedGroupForm", $this->lng->txt("save"));

        // Name
        $name = new ilTextInputGUI($this->lng->txt("name"), "name");
        $name->setSize(40);
        $name->setMaxLength(70);
        $name->setRequired(true);
        $name->setValue($group_info["name"]);
        $form->addItem($name);

        // Description
        $description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
        $description->setCols(40);
        $description->setRows(3);
        $description->setValue($group_info["description"]);
        $form->addItem($description);

        // Role assignment
        $role_assignment = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_privileges_role_assignment"), "role_assignment");
        $role_names = array($this->lng->txt("none"));
        $global_roles = $this->privileges->getGlobalRoles();

        foreach ($global_roles as $role_info)
        {
            $role_names[] = $role_info["title"];
        }

        $role_assignment->setOptions($role_names);
        $selection_index = $this->getSelectionIndexForRoleAssignment($group_info);
        $role_assignment->setValue($selection_index + ilRoomSharingPrivilegesGUI::SELECT_INPUT_NONE_OFFSET);
        $form->addItem($role_assignment);

        return $form;
    }

    private function getSelectionIndexForRoleAssignment($a_group_info)
    {
        $global_roles = $this->privileges->getGlobalRoles();
        $selection_index = -1;

        foreach ($global_roles as $role_index => $role_info)
        {
            if ($role_info["id"] == $a_group_info["role_id"])
            {
                $selection_index = $role_index;
                break;
            }
        }

        return $selection_index;
    }

    private function saveEditedGroupForm()
    {
        $group_form = $this->createEditGroupForm();
        if ($group_form->checkInput())
        {
            $this->evaluateGroupFormEntries($group_form);
            $this->renderEditGroupForm();
        }
        else
        {
            $this->renderEditGroupForm();
            $group_form->setValuesByPost();
        }
    }

    private function evaluateGroupFormEntries($a_group_form)
    {
        $entries = array();
        $entries["id"] = $this->group_id;
        $entries["name"] = $a_group_form->getInput("name");
        $entries["description"] = $a_group_form->getInput("description");
        $entries["role_id"] = $this->getRoleIdFromSelectionInput($a_group_form);

        $this->saveFormEntries($entries);
    }

    private function getRoleIdFromSelectionInput($a_group_form)
    {
        $selection = $a_group_form->getInput("role_assignment");
        $global_roles = $this->privileges->getGlobalRoles();

        return $global_roles[$selection - ilRoomSharingPrivilegesGUI::SELECT_INPUT_NONE_OFFSET]["id"];
    }

    private function saveFormEntries($a_entries)
    {
        try
        {
            $this->privileges->editGroup($a_entries);
        }
        catch (ilRoomSharingPrivilegesException $exc)
        {
            ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
        }
        ilUtil::sendSuccess("EDITED GROUP FORM ENTRIES: " . implode(", ", $a_entries), true);
    }

    private function renderUserAssignment()
    {
        $this->tabs->setTabActive("user_assignment");

        // Toolbar
        $toolbar = new ilToolbarGUI();
        ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $toolbar, array(
            "auto_complete_name" => $this->lng->txt('user'),
            "submit_name" => $this->lng->txt("add")
            )
        );

        $toolbar->addSpacer();

        $toolbar->addButton(
            $this->lng->txt("search_user"), $this->ctrl->getLinkTargetByClass("ilRepositorySearchGUI", "start")
        );

        // Assigned Users Table
        $table = new ilRoomSharingAssignedUsersTableGUI($this, "renderUserAssignment", $this->group_id);
        $this->tpl->setContent($toolbar->getHTML() . $table->getHTML());
    }

    public function confirmGroupDeletion()
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_group_back"), $this->ctrl->getLinkTarget($this, "renderEditGroupForm"));

        // create the confirmation GUI
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->lng->txt("rep_robj_xrs_group_confirm_deletion_header"));

        $group = $this->privileges->getGroupById($this->group_id);
        $confirmation->addItem("group_id", $this->group_id, $group["name"]);
        $confirmation->setConfirm($this->lng->txt("rep_robj_xrs_group_confirm_deletion"), "deleteGroup");
        $confirmation->setCancel($this->lng->txt("cancel"), "renderEditGroupForm");

        $this->tpl->setContent($confirmation->getHTML()); // display
    }

    public function deleteGroup()
    {
        $this->privileges->deleteGroup($this->group_id);
        $this->ctrl->redirectByClass("ilroomsharingprivilegesgui", "showConfirmedDeletion");
    }

    public function addUsersToGroup($a_user_ids)
    {
        $this->privileges->assignUsersToGroup($this->group_id, $a_user_ids);
        ilUtil::sendSuccess($this->lng->txt("ADDED USER IDS: " . implode(", ", $a_user_ids)), true);
        $this->ctrl->redirect($this, "renderuserassignment");
    }

    public function deassignUsers()
    {
        $selected_users = ($_POST["user_id"]) ? $_POST["user_id"] : array($_GET["user_id"]);
        $this->privileges->deassignUsersFromGroup($this->group_id, $selected_users);
        ilUtil::sendSuccess($this->lng->txt("DEASSIGNED USERS: " . implode(", ", $selected_users)), true);
        $this->renderUserAssignment();
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