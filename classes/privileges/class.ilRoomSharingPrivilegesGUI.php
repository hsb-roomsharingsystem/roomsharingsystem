<?php

require_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingRolePrivilegesTableGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingRoleGUI.php");
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
 * @ilCtrl_Calls ilRoomSharingPrivilegesGUI: ilRoomSharingRoleGUI, ilRepositorySearchGUI
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
    }

    /**
     * Executes the command given by ilCtrl.
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class)
        {
            case "ilroomsharingrolegui":
                $role_id = (int) $_GET["role_id"];
                $this->ctrl->setReturn($this, "showPrivileges");
                $this->role_gui = new ilRoomSharingRoleGUI($this->parent, $role_id);
                $this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges"), $this->ctrl->getLinkTarget($this, "showPrivileges"));
                $this->ctrl->forwardCommand($this->role_gui);
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
            $target = $this->ctrl->getLinkTarget($this, "renderAddRoleForm");
            $toolbar->addButton($this->lng->txt("role_new"), $target);
        }


        $role_privileges_table = new ilRoomSharingRolePrivilegesTableGUI($this, "showPrivileges", $this->ref_id);

        $this->tpl->setContent($toolbar->getHTML() . $role_privileges_table->getHTML());
    }

    private function renderAddRoleForm()
    {
        $this->tabs->clearTargets();
        $role_form = $this->createRoleForm();
        $this->tpl->setContent($role_form->getHTML());
    }

    private function createRoleForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('role_new'));
        $form->addCommandButton("addRole", $this->lng->txt('role_new'));
        $form->addCommandButton("showPrivileges", $this->lng->txt('cancel'));

        $name = new ilTextInputGUI($this->lng->txt("name"), "name");
        $name->setSize(40);
        $name->setMaxLength(70);
        $name->setRequired(true);
        $form->addItem($name);

        $description = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $description->setCols(40);
        $description->setRows(3);
        $form->addItem($description);

        return $form;
    }

    private function addRole()
    {
        $role_form = $this->createRoleForm();
        if ($role_form->checkInput())
        {
            $this->showPrivileges();
        }
        else
        {
            $this->tabs->clearTargets();
            $role_form->setValuesByPost();
            $this->tpl->setContent($role_form->getHTML());
        }
    }

    private function savePrivileges()
    {
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
