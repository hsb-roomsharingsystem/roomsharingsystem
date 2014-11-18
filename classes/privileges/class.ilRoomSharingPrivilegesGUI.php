<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingClassPrivilegesTableGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingClassGUI.php");
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
 * @ilCtrl_Calls ilRoomSharingPrivilegesGUI: ilRoomSharingClassGUI, ilRepositorySearchGUI
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
    private $user;
    private $rssPermission;

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
        global $ilCtrl, $lng, $tpl, $ilTabs, $ilAccess, $ilUser, $rssPermission;

        $this->parent = $a_parent_obj;
        $this->ref_id = $this->parent->ref_id;
        $this->pool_id = $this->parent->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->access = $ilAccess;
        $this->user = $ilUser;
        $this->privileges = new ilRoomSharingPrivileges();
        $this->permission = $rssPermission;
    }

    /**
     * Executes the command given by ilCtrl.
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class)
        {
            case "ilroomsharingclassgui":
                $class_id = (int) $_GET["class_id"];
                $this->ctrl->setReturn($this, "showPrivileges");
                $this->class_gui = new ilRoomSharingClassGUI($this->parent, $class_id);
                $this->ctrl->forwardCommand($this->class_gui);
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

        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $target = $this->ctrl->getLinkTarget($this, "renderAddClassForm");
            $toolbar->addButton($this->lng->txt("rep_robj_xrs_privileges_class_new"), $target);
        }

        $class_privileges_table = new ilRoomSharingClassPrivilegesTableGUI($this, "showPrivileges", $this->ref_id);

        $this->tpl->setContent($toolbar->getHTML() . $class_privileges_table->getHTML());
    }

    private function renderAddClassForm()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $this->tabs->clearTargets();
            $class_form = $this->createAddClassForm();
            $this->tpl->setContent($class_form->getHTML());
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    public function showConfirmedDeletion()
    {
        ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_class_deletion_successful"));
        $this->showPrivileges();
    }

    private function createAddClassForm()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this));
            $form->setTitle($this->lng->txt("rep_robj_xrs_privileges_class_new"));
            $form->addCommandButton("addClass", $this->lng->txt("rep_robj_xrs_privileges_class_new"));
            $form->addCommandButton("showPrivileges", $this->lng->txt("cancel"));

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

            // Priority
            $priority = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_class_priority"), "priority");
            $priority_levels = range(0, 9);
            $priority->setOptions($priority_levels);
            $form->addItem($priority);

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

            // Copy Class Privileges
            $class_to_copy = new ilRadioGroupInputGUI($this->lng->txt("rep_robj_xrs_privileges_copy_privileges"), "copied_class_privileges");
            $empty_option = new ilRadioOption($this->lng->txt("none"), 0);
            $class_to_copy->addOption($empty_option);

            $classes = $this->privileges->getClasses();

            foreach ($classes as $class)
            {
                $copy_option = new ilRadioOption($class["name"], $class["id"], $class["description"]);
                $class_to_copy->addOption($copy_option);
            }
            $form->addItem($class_to_copy);

            return $form;
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function addClass()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $class_form = $this->createAddClassForm();
            if ($class_form->checkInput())
            {
                $this->evaluateClassFormEntries($class_form);
                $this->showPrivileges();
            }
            else
            {
                $this->tabs->clearTargets();
                $class_form->setValuesByPost();
                $this->tpl->setContent($class_form->getHTML());
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
        $entries["name"] = $a_class_form->getInput("name");
        $entries["description"] = $a_class_form->getInput("description");
        $entries["priority"] = $a_class_form->getInput("priority");
        $entries["role_id"] = $this->getRoleIdFromSelectionInput($a_class_form);
        $entries["copied_class_privileges"] = $a_class_form->getInput("copied_class_privileges");

        $this->saveFormEntries($entries);
    }

    private function getRoleIdFromSelectionInput($a_class_form)
    {
        $selection = $a_class_form->getInput("role_assignment");
        $global_roles = $this->privileges->getGlobalRoles();

        return $global_roles[$selection - self::SELECT_INPUT_NONE_OFFSET]["id"];
    }

    private function saveFormEntries($a_entries)
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            try
            {
                $this->privileges->addClass($a_entries);
            }
            catch (ilRoomSharingPrivilegesException $exc)
            {
                ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
            }
            ilUtil::sendSuccess("NEW CLASS FORM ENTRIES: " . implode(", ", $a_entries), true);
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function savePrivileges()
    {
        if ($this->permission->checkPrivilege("editPrivileges"))
        {
            $classes_with_ticked_locks = $_POST["lock"];
            $privileges_post_exists = !empty($_POST["priv"]);
            $lock_post_exists = !empty($classes_with_ticked_locks);

            if ($privileges_post_exists || $lock_post_exists)
            {
                if ($privileges_post_exists)
                {

                    $class_ids = array_keys($_POST["priv"]);
                    foreach ($class_ids as $class_id)
                    {
                        $classes_with_ticked_privileges[$class_id] = $_POST["priv"][$class_id];
                        $classes_with_ticked_privileges_message = "CLASSES AND TICKED PRIVILEGES: " . print_r($classes_with_ticked_privileges, true);
                    }
                    $this->privileges->setPrivileges($classes_with_ticked_privileges);
                }

                if ($lock_post_exists && $this->hasLockBeenSet())
                {
                    $this->showConfirmLock($classes_with_ticked_locks);
                    $locked_classes_message = "; LOCKED CLASSES: " . implode(", ", array_keys($classes_with_ticked_locks));
                }
                else
                {
                    ilUtil::sendSuccess($classes_with_ticked_privileges_message . $locked_classes_message, true);
                    if ($this->permission->checkPrivilege("lockPrivileges"))
                    {
                        $this->privileges->setLockedClasses($classes_with_ticked_locks);
                    }
                    $this->ctrl->redirect($this);
                }
            }
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function hasLockBeenSet()
    {
        $classes_with_ticked_locks = array_keys($_POST["lock"]);
        $unlocked_classes = $this->privileges->getUnlockedClasses();

        foreach ($classes_with_ticked_locks as $ticked_class)
        {
            if (in_array($ticked_class, $unlocked_classes))
            {
                return true;
            }
        }
        return false;
    }

    private function showConfirmLock($a_classes_to_be_locked)
    {
        if ($this->permission->checkPrivilege("editPrivileges") &&
            $this->permission->checkPrivilege("lockPrivileges"))
        {
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges_lock_confirm_back"), $this->ctrl->getLinkTarget($this, "showPrivileges"));
            // create the confirmation GUI
            $confirmation = new ilConfirmationGUI();
            $confirmation->setFormAction($this->ctrl->getFormAction($this));
            $confirmation->setHeaderText($this->lng->txt("rep_robj_xrs_privileges_confirm_class_lock_question"));

            foreach ($a_classes_to_be_locked as $class_key => $class)
            {
                $confirmation->addItem("locked_class_ids", implode(",", array_keys($a_classes_to_be_locked)), $this->privileges->getClassById($class_key)["name"]);
            }
            $confirmation->setConfirm($this->lng->txt("rep_robj_xrs_privileges_confirm_class_lock"), "lockClasses");
            $confirmation->setCancel($this->lng->txt("cancel"), "showPrivileges");
            $this->tpl->setContent($confirmation->getHTML());

            if ($this->checkForOwnLockedClass($a_classes_to_be_locked))
            {
                $assigned_user_classes = $this->privileges->getAssignedClassesForUser($this->user->getId());
                $assigned_user_class_names = array();

                foreach ($assigned_user_classes as $class_id)
                {
                    $assigned_user_class_names[] = $this->privileges->getClassById($class_id)["name"];
                }

                ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_privileges_confirm_class_self_lock_of") . " " . implode(", ", $assigned_user_class_names));
            }
            ilUtil::sendInfo($this->lng->txt("rep_robj_xrs_privileges_confirm_class_lock_info"));
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    public function lockClasses()
    {
        if ($this->permission->checkPrivilege("editPrivileges") &&
            $this->permission->checkPrivilege("lockPrivileges"))
        {
            $classes_to_be_locked = explode(",", $_POST["locked_class_ids"]);
            $this->privileges->setLockedClasses(array_flip($classes_to_be_locked));
            $this->ctrl->redirect($this);
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission"));
        }
    }

    private function checkForOwnLockedClass($a_classes_to_be_locked)
    {
        $assigned_user_classes = $this->privileges->getAssignedClassesForUser($this->user->getId());
        $in_class = false;
        foreach ($assigned_user_classes as $assigned_user_class)
        {
            if (in_array($assigned_user_class, array_keys($a_classes_to_be_locked)))
            {
                $in_class = true;
                break;
            }
        }
        return $in_class;
    }

    /**
     * Returns roomsharing pool id.
     *
     * @return integer Pool-ID
     */
    public
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
    public
        function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }

}
?>
