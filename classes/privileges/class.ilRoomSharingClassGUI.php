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

    public function __construct($a_parent, $a_class_id)
    {
        global $ilCtrl, $lng, $tpl, $ilTabs, $rssPermission;

        $this->parent = $a_parent;
        $this->pool_id = $this->parent->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->class_id = $a_class_id ? $a_class_id : $_GET["class_id"];
        $this->ctrl->saveParameter($this, "class_id");
        $this->privileges = new ilRoomSharingPrivileges();
        $this->permission = $rssPermission;
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
                $rep_search->setCallback($this, "addUsersToClass");

                // Tabs
                $this->tabs->setTabActive("user_assignment");
                $this->ctrl->setReturn($this, "renderUserAssignment");
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $this->ctrl->getCmd("renderEditClassForm");
                $this->$cmd();
                break;
        }
        return true;
    }

    private function renderPage()
    {
        $this->tabs->clearTargets();
        $class_info = $this->privileges->getClassById($this->class_id);

        // Title
        $this->tpl->setTitle($class_info["name"]);
        // Description
        $this->tpl->setDescription($class_info["description"]);
        // Icon
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role_b.png"), $this->lng->txt("rep_robj_xrs_class"));
        $this->setTabs();
    }

    private function setTabs()
    {
        // Back-Link
        $this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges"), $this->ctrl->getLinkTargetByClass("ilroomsharingprivilegesgui", "showPrivileges"));

        // Edit Class
        $this->tabs->addTab("edit_properties", $this->lng->txt("edit_properties"), $this->ctrl->getLinkTarget($this, "renderEditClassForm"));

        // User Assignment
        $this->tabs->addTab("user_assignment", $this->lng->txt("user_assignment"), $this->ctrl->getLinkTarget($this, "renderUserAssignment"));
    }

    private function renderEditClassForm()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $this->tabs->setTabActive("edit_properties");

            $toolbar = new ilToolbarGUI();
            $toolbar->addButton($this->lng->txt("rep_robj_xrs_class_confirm_deletion"), $this->ctrl->getLinkTarget($this, "confirmClassDeletion"));

            $class_form = $this->createEditClassForm();
            $this->tpl->setContent($toolbar->getHTML() . $class_form->getHTML());
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function createEditClassForm()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $class_info = $this->privileges->getClassById($this->class_id);

            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this));
            $form->setTitle($this->lng->txt("rep_robj_xrs_privileges_class_edit"));
            $form->addCommandButton("saveEditedClassForm", $this->lng->txt("save"));

            // Name
            $name = new ilTextInputGUI($this->lng->txt("name"), "name");
            $name->setSize(40);
            $name->setMaxLength(70);
            $name->setRequired(true);
            $name->setValue($class_info["name"]);
            $form->addItem($name);

            // Description
            $description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
            $description->setCols(40);
            $description->setRows(3);
            $description->setValue($class_info["description"]);
            $form->addItem($description);

            // Priority
            $priority = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_class_priority"), "priority");
            $priority_levels = range(0, 9);
            $priority->setOptions($priority_levels);
            $priority->setValue($class_info["priority"]);
            $form->addItem($priority);

            // Role assignment
            $role_assignment = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_privileges_role_assignment"), "role_assignment");
            $role_names = array($this->lng->txt("none"));
            $global_roles = $this->privileges->getGlobalRoles();

            foreach ($global_roles as $role_info)
            {
                $role_names[] = $role_info["title"];
            }

            $role_assignment->setOptions($role_names);
            $selection_index = $this->getSelectionIndexForRoleAssignment($class_info);
            $role_assignment->setValue($selection_index + ilRoomSharingPrivilegesGUI::SELECT_INPUT_NONE_OFFSET);
            $form->addItem($role_assignment);

            return $form;
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function getSelectionIndexForRoleAssignment($a_class_info)
    {
        $global_roles = $this->privileges->getGlobalRoles();
        $selection_index = -1;

        foreach ($global_roles as $role_index => $role_info)
        {
            if ($role_info["id"] == $a_class_info["role_id"])
            {
                $selection_index = $role_index;
                break;
            }
        }

        return $selection_index;
    }

    private function saveEditedClassForm()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $class_form = $this->createEditClassForm();
            if ($class_form->checkInput())
            {
                $this->evaluateClassFormEntries($class_form);
                $this->renderPage();
                $this->renderEditClassForm();
            }
            else
            {
                $this->renderEditClassForm();
                $class_form->setValuesByPost();
            }
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function evaluateClassFormEntries($a_class_form)
    {
        $entries = array();
        $entries["id"] = $this->class_id;
        $entries["name"] = $a_class_form->getInput("name");
        $entries["priority"] = $a_class_form->getInput("priority");
        $entries["description"] = $a_class_form->getInput("description");
        $entries["role_id"] = $this->getRoleIdFromSelectionInput($a_class_form);

        $this->saveFormEntries($entries);
    }

    private function getRoleIdFromSelectionInput($a_class_form)
    {
        $selection = $a_class_form->getInput("role_assignment");
        $global_roles = $this->privileges->getGlobalRoles();

        return $global_roles[$selection - ilRoomSharingPrivilegesGUI::SELECT_INPUT_NONE_OFFSET]["id"];
    }

    private function saveFormEntries($a_entries)
    {
        try
        {
            $this->privileges->editClass($a_entries);
        }
        catch (ilRoomSharingPrivilegesException $exc)
        {
            ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
        }
        ilUtil::sendSuccess("EDITED CLASS FORM ENTRIES: " . implode(", ", $a_entries), true);
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
        $table = new ilRoomSharingAssignedUsersTableGUI($this, "renderUserAssignment", $this->class_id);
        $this->tpl->setContent($toolbar->getHTML() . $table->getHTML());
    }

    public function confirmClassDeletion()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_class_back"), $this->ctrl->getLinkTarget($this, "renderEditClassForm"));

            // create the confirmation GUI
            $confirmation = new ilConfirmationGUI();
            $confirmation->setFormAction($this->ctrl->getFormAction($this));
            $confirmation->setHeaderText($this->lng->txt("rep_robj_xrs_class_confirm_deletion_header"));

            $class = $this->privileges->getClassById($this->class_id);
            $confirmation->addItem("class_id", $this->class_id, $class["name"]);
            $confirmation->setConfirm($this->lng->txt("rep_robj_xrs_class_confirm_deletion"), "deleteClass");
            $confirmation->setCancel($this->lng->txt("cancel"), "renderEditClassForm");

            $this->tpl->setContent($confirmation->getHTML()); // display
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    public function deleteClass()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $this->privileges->deleteClass($this->class_id);
            $this->ctrl->redirectByClass("ilroomsharingprivilegesgui", "showConfirmedDeletion");
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    public function addUsersToClass($a_user_ids)
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $this->privileges->assignUsersToClass($this->class_id, $a_user_ids);
            ilUtil::sendSuccess($this->lng->txt("ADDED USER IDS: " . implode(", ", $a_user_ids)), true);
            $this->ctrl->redirect($this, "renderuserassignment");
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    public function deassignUsers()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $selected_users = ($_POST["user_id"]) ? $_POST["user_id"] : array($_GET["user_id"]);
            $this->privileges->deassignUsersFromClass($this->class_id, $selected_users);
            ilUtil::sendSuccess($this->lng->txt("DEASSIGNED USERS: " . implode(", ", $selected_users)), true);
            $this->renderUserAssignment();
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
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
