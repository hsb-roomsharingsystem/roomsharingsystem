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

    /**
     * Constructor of ilRoomSharingClassPrivilegesTableGUI
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

        $this->setId('class_priv_' . $this->ref_id);
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

        $this->setRowTemplate("tpl.room_class_privileges_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
        $tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/js/ilPrivilegesSelect.js");

        $data = $this->privileges->getPrivilegesMatrix();

        $this->setMaxCount(count($data));
        $this->setData($data);
    }

    private function addColumns()
    {
        $classes = $this->privileges->getClasses();

        foreach ($classes as $class)
        {
            $this->addColumn($this->createTitle($class), "", "", "", false);
        }
    }

    public function fillHeader()
    {
        parent::fillHeader();
        $this->addTooltips();
    }

    public function fillRow($a_table_row)
    {
        // Lock Class
        if (isset($a_table_row["show_lock_row"]))
        {
            $classes = $this->privileges->getClasses();
            foreach ($classes as $class)
            {
                $this->tpl->setCurrentBlock("class_lock");
                $this->tpl->setVariable("LOCK_CLASS_ID", $class["id"]);
                $this->tpl->setVariable("TXT_LOCK", $this->lng->txt("rep_robj_xrs_privileges_lock"));
                $this->tpl->setVariable("TXT_LOCK_LONG", $this->lng->txt("rep_robj_xrs_privileges_lock_desc"));

                if (in_array($class["id"], $a_table_row["locked_classes"]))
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
            $this->tpl->setVariable("SECTION_TITLE", $a_table_row["section"]["title"]);
            $this->tpl->setVariable("SECTION_DESC", $a_table_row["section"]["description"]);
            $this->tpl->parseCurrentBlock();

            return true;
        }

        // Select all
        if (isset($a_table_row['show_select_all']))
        {
            $class = $this->privileges->getClasses();

            foreach ($class as $class)
            {
                $this->tpl->setCurrentBlock("classes_select_all");
                $this->tpl->setVariable("JS_CLASS_ID", $class["id"]);
                $this->tpl->setVariable("JS_FORM_NAME", $this->getFormName());
                $this->tpl->setVariable("JS_SUBID", $a_table_row["type"]);
                $this->tpl->setVariable("JS_ALL_PRIVS", "['" . implode("','", $a_table_row["privileges"]) . "']");
                $this->tpl->setVariable("TXT_SEL_ALL", $this->lng->txt("select_all"));
                $this->tpl->parseCurrentBlock();
            }
            return true;
        }

        // Privileges
        foreach ($a_table_row["classes"] as $class)
        {
            $this->tpl->setCurrentBlock("class_td");
            $this->tpl->setVariable("PRIV_CLASS_ID", $class["id"]);
            $this->tpl->setVariable("PRIV_ID", $a_table_row["privilege"]["id"]);

            $this->tpl->setVariable("TXT_PRIV", $a_table_row["privilege"]["name"]);

            $this->tpl->setVariable("TXT_PRIV_LONG", $a_table_row["privilege"]["description"]);

            if ($class["privilege_set"])
            {
                $this->tpl->setVariable("PRIV_CHECKED", 'checked="checked"');
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    private function addTooltips()
    {
        $classes = $this->privileges->getClasses();

        $cnt = 1;
        foreach ($classes as $class)
        {
            $role_text = $this->isClassAssignedToRole($class) ? $class["role"] : $this->lng->txt("none");

            ilTooltipGUI::addTooltip("thc_" . $this->getId() . "_" . $cnt, "<pre>" . $this->lng->txt("class") . ": " . $class["name"] . "&#13;&#10;"
                . $this->lng->txt("rep_robj_xrs_privileges_role_assignment") . ": " . $role_text . "</pre>", "", "bottom center", "top center", false);
            $cnt++;
        }
    }

    private function createTitle($a_class_set)
    {
        // &#8658; = Unicode double arrow to the right
        $assigned_role = $this->isClassAssignedToRole($a_class_set) ? " &#8658; " . $a_class_set["role"] : null;
        $table_head = $a_class_set["name"] . $assigned_role;

        $this->ctrl->setParameterByClass("ilroomsharingclassgui", "class_id", $a_class_set["id"]);

        return '<a class="tblheader" href="' . $this->ctrl->getLinkTargetByClass("ilroomsharingclassgui", "") . '" >' . $table_head . "</a>";
    }

    private function isClassAssignedToRole($a_class_set)
    {
        return !empty($a_class_set["role"]);
    }

}
?>
