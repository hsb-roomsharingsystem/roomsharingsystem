<?php

require_once("Services/AccessControl/classes/class.ilObjRoleGUI.php");

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
        ;
    }

    /**
     * Executes the command given by ilCtrl.
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class)
        {
            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $this->ctrl->getCmd("renderEditRoleForm");
                $this->$cmd();
                break;
        }
        return true;
    }

    private function renderEditRoleForm()
    {
//        $this->tabs->clearTargets();
        $role_form = $this->createEditRoleForm();
        $this->tpl->setContent($role_form->getHTML());
    }

    private function createEditRoleForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('role_edit'));
        $form->addCommandButton("editRole", $this->lng->txt("save"));

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