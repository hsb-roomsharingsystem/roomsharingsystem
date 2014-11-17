<?php

require_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingGroupPrivilegesTableGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingGroupGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Search/classes/class.ilRepositorySearchGUI.php");

/**
 * Class ilRoomSharingPrivilegesGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 *
 *
 * @version $Id$
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 *
 * @ilCtrl_Calls ilRoomSharingPrivilegesGUI: ilRoomSharingGroupGUI, ilRepositorySearchGUI
 */
class ilRoomSharingPrivilegesGUI
{
    protected $ref_id;
    protected $pool_id;
    private $parent;
    private $ctrl;
    private $lng;
    private $tpl;
    private $tabs;
    private $access;
    private $privileges;

    CONST SELECT_INPUT_NONE_OFFSET = 1;

    /**
     * Constructor of ilRoomSharingPrivilegesGUI
     *
     * @global type $ilCtrl
     * @global type $lng
     * @global type $tpl
     * @param ilRoomSharingAppointmentsGUI $a_parent_obj
     */
    public function __construct(ilObjRoomSharingGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl, $ilTabs, $ilAccess;

        $this->parent = $a_parent_obj;
        $this->ref_id = $this->parent->ref_id;
        $this->pool_id = $this->parent->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->access = $ilAccess;
        $this->privileges = new ilRoomSharingPrivileges();
    }

    /**
     * Executes the command given by ilCtrl.
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class)
        {
            case "ilroomsharinggroupgui":
                $group_id = (int) $_GET["group_id"];
                $this->ctrl->setReturn($this, "showPrivileges");
                $this->group_gui = new ilRoomSharingGroupGUI($this->parent, $group_id);
                $this->ctrl->forwardCommand($this->group_gui);
                break;

            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $this->ctrl->getCmd("showPrivileges");
                $this->$cmd();
                break;
        }
        return true;
    }

    public function showPrivileges()
    {
        $toolbar = new ilToolbarGUI();

        if ($this->access->checkAccess('write', '', $this->ref_id))
        {
            $target = $this->ctrl->getLinkTarget($this, "renderAddGroupForm");
            $toolbar->addButton($this->lng->txt("rep_robj_xrs_privileges_group_new"), $target);
        }

        $group_privileges_table = new ilRoomSharingGroupPrivilegesTableGUI($this, "showPrivileges", $this->ref_id);

        $this->tpl->setContent($toolbar->getHTML() . $group_privileges_table->getHTML());
    }

    private function renderAddGroupForm()
    {
        $this->tabs->clearTargets();
        $group_form = $this->createAddGroupForm();
        $this->tpl->setContent($group_form->getHTML());
    }

    public function showConfirmedDeletion()
    {
        ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_group_deletion_successful"));
        $this->showPrivileges();
    }

    private function createAddGroupForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("rep_robj_xrs_privileges_group_new"));
        $form->addCommandButton("addGroup", $this->lng->txt("rep_robj_xrs_privileges_group_new"));
        $form->addCommandButton("showPrivileges", $this->lng->txt('cancel'));

        // Name
        $name = new ilTextInputGUI($this->lng->txt("name"), "name");
        $name->setSize(40);
        $name->setMaxLength(70);
        $name->setRequired(true);
        $form->addItem($name);

        // Description
        $description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
        $description->setCols(40);
        $description->setRows(3);
        $form->addItem($description);

        // Role assignment
        $role_assignment = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_privileges_role_assignment"), "role_assignment");
        $role_names = array($this->lng->txt("none"));
        $global_roles = $this->privileges->getGlobalRoles();

        foreach ($global_roles as $role)
        {
            $role_names[] = $role["title"];
        }

        $role_assignment->setOptions($role_names);
        $form->addItem($role_assignment);

        // Copy Group Privileges
        $group_to_copy = new ilRadioGroupInputGUI($this->lng->txt("rep_robj_xrs_privileges_copy_privileges"), "copied_group_privileges");
        $empty_option = new ilRadioOption($this->lng->txt("none"), 0);
        $group_to_copy->addOption($empty_option);

        $groups = $this->privileges->getClasses();

        foreach ($groups as $group)
        {
            $copy_option = new ilRadioOption($group["name"], $group["id"], $group["description"]);
            $group_to_copy->addOption($copy_option);
        }
        $form->addItem($group_to_copy);

        return $form;
    }

    private function addGroup()
    {
        $group_form = $this->createAddGroupForm();
        if ($group_form->checkInput())
        {
            $this->evaluateGroupFormEntries($group_form);
            $this->showPrivileges();
        }
        else
        {
            $this->tabs->clearTargets();
            $group_form->setValuesByPost();
            $this->tpl->setContent($group_form->getHTML());
        }
    }

    private function evaluateGroupFormEntries($a_group_form)
    {
        $entries = array();
        $entries["name"] = $a_group_form->getInput("name");
        $entries["description"] = $a_group_form->getInput("description");
        $entries["role_id"] = $this->getRoleIdFromSelectionInput($a_group_form);
        $entries["copied_group_privileges"] = $a_group_form->getInput("copied_group_privileges");

        $this->saveFormEntries($entries);
    }

    private function getRoleIdFromSelectionInput($a_group_form)
    {
        $selection = $a_group_form->getInput("role_assignment");
        $global_roles = $this->privileges->getGlobalRoles();

        return $global_roles[$selection - self::SELECT_INPUT_NONE_OFFSET]["id"];
    }

    private function saveFormEntries($a_entries)
    {
        try
        {
            $this->privileges->addClass($a_entries);
        }
        catch (ilRoomSharingPrivilegesException $exc)
        {
            ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
        }
        ilUtil::sendSuccess("NEW GROUP FORM ENTRIES: " . implode(", ", $a_entries), true);
    }

    private function savePrivileges()
    {
        $privileges_post_exists = !empty($_POST["priv"]);
        $lock_post_exists = !empty($_POST["lock"]);

        if ($privileges_post_exists || $lock_post_exists)
        {
            if ($privileges_post_exists)
            {

                $group_ids = array_keys($_POST["priv"]);
                foreach ($group_ids as $group_id)
                {
                    $groups_with_ticked_privileges[$group_id] = $_POST["priv"][$group_id];
                    $groups_with_ticked_privileges_message = "GROUPS AND TICKED PRIVILEGES: " . print_r($groups_with_ticked_privileges, true);
                }
                $this->privileges->setPrivileges($groups_with_ticked_privileges);
            }

            if ($lock_post_exists)
            {
                $groups_with_ticked_locks = $_POST["lock"];
                $locked_groups_message = "; LOCKED GROUPS: " . implode(", ", array_keys($groups_with_ticked_locks));
            }
            $this->privileges->setLockedClasses($groups_with_ticked_locks);

            ilUtil::sendSuccess($groups_with_ticked_privileges_message . $locked_groups_message, true);
        }

        $this->showPrivileges();
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
