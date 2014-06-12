<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Table-GUI for the uploaded Roomsharing floorplans
 *
 * This class is used to show a table with all uploaded floorplans.
 * A Thumbnail shows a small picture of the floorplan next to the 
 * title and the description. In this table it is possible to edit 
 * or remove a plan.
 * 
 * @author T. Wolscht <t.wolscht@googlemail.com>
 */
class ilRoomSharingFloorPlansTableGUI extends ilTable2GUI {

    protected $pool_id;

    /**
     * Constructor
     * @param	object	$a_parent_obj
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id) {
        global $ilCtrl, $lng, $ilAccess;

        $this->parent_obj = $a_parent_obj;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->ref_id = $a_ref_id;
        $this->setId("roomobj");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("rep_robj_xrs_floor_plans_show"));
        $this->setLimit(20);      // Anzahl der Datensätze pro Seite

        $this->addColumns();    // Spalten(-überschriften) hinzufügen
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.room_floorplans.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");
        $this->getItems();
    }

    /**
     * returns all informations to the uploaded floorplans from Roomsharing DB
     */
    function getItems() {
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php';
        $floorplans = new ilRoomSharingFloorPlans();
        $floorplans->setPoolID($this->getPoolId());
        $data = $floorplans->getAllFloorPlans();
        $this->setMaxCount(sizeof($data));
        $this->setData($data);
    }

    /**
     * add columns to the floorplan-table
     */
    private function addColumns() {
        global $lng;
        $this->addColumn($this->lng->txt("rep_robj_xrs_plan"));
        $this->addColumn($lng->txt("title"));
        $this->addColumn($lng->txt("desc"));
        //   $this->addColumn("POOL-ID");
        $this->addColumn($lng->txt("actions"));
    }

    /**
     * fills the rows of the table
     *
     */
    public function fillRow($a_set) {
        global $ilCtrl, $lng, $ilAccess;
        
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mobj = new ilObjMediaObject($a_set['file_id']);
        $med = $mobj->getMediaItem("Standard");
        $target = $med->getThumbnailTarget();
        if ($target != "") {
            $this->tpl->setVariable("IMG", ilUtil::img($target));
        } else {
            $this->tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_" . $a_set["type"] . ".png")));
        }
        $this->tpl->setVariable('TXT_TITLE', $mobj->getTitle());
        //$this->tpl->setVariable('TXT_TITLE', $a_set['title']);
        $this->tpl->setVariable('TXT_DESCRIPTION', $mobj->getDescription());
        // $this->tpl->setVariable('TXT_POOL_ID', $a_set['pool_id']);
        $this->tpl->setVariable("LINK_VIEW", $mobj->getDataDirectory() . "/" . $med->getLocation());
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $alist = new ilAdvancedSelectionListGUI();
        $alist->setId($a_set['file_id']);
        $alist->setListTitle($lng->txt("actions"));
        if ($ilAccess->checkAccess('write', '', $this->ref_id))
        {     
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'file_id', $a_set['file_id']);
        $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTarget($this->parent_obj, 'editFloorplan')); // #12306

        $alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
        }
        $this->tpl->setVariable("LAYER", $alist->getHTML());
    }

    /**
     * Returns roomsharing pool id.
     */
    function getPoolId() {
        return $this->pool_id;
    }

    /**
     * Sets roomsharing pool id.
     */
    function setPoolId($a_pool_id) {
        $this->pool_id = $a_pool_id;
    }

}
