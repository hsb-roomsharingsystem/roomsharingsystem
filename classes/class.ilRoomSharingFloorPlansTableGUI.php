<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class ilRoomSharingFloorPlansTableGUI
 * Represents all floor plans.
 *
 * @author T. Wolscht
 * 
 * @ilCtrl_IsCalledBy ilRoomSharingFloorPlansTableGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * 
 */
class ilRoomSharingFloorPlansTableGUI extends ilTable2GUI {

    /**
     * Constructor of ilRoomSharingFloorPlansTableGUI
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

        $this->setTitle($lng->txt("room_floor_plans_show"));
        $this->setLimit(20);      // Paging limit

        $this->addColumns();    // Add columns including translations
        $this->setSelectAllCheckbox('participations');   // set column with checkboxes which
        												 // should be selected on "select all" event
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.room_participations_row.html", "Modules/RoomSharing");
        // deletion command for items with selected checkboxes
        $this->addMultiCommand('showParticipations', $this->lng->txt('room_floor_plans_delete'));

        $this->getItems();
    }

    /**
     * Gets all existing floor plans and loads the data for representation.
     */
    function getItems() {
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php';
        $floorplans = new ilRoomSharingFloorPlans();
        $data = $floorplans->getAllFloorPlans() ;

        $this->setMaxCount(sizeof($data));
        $this->setData($data);
        echo $data;
    }

    /**
     * Adds columns with translations.
     */
    private function addColumns() {
        $this->addColumn($this->lng->txt("room_floor_plans"), "date");
        $this->addColumn("GebÃ¤udeplan");
        $this->addColumn("Titel");
        $this->addColumn("Beschreibung");
        //$this->addColumn($this->lng->txt("room_module"), "module");
    }

    /**
     * Fills each row with given data.
     *
     */
    public function fillRow($a_set) {
        // Checkbox-Name muss mit dem aus setSelectAllCheckbox Ã¼bereinstimmen
        $this->tpl->setVariable('CHECKBOX_NAME', 'bookings');

//        if ($a_set['recurrence']) {
//            // Bild fÃ¼r Serientermin
//            $this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
//        }
        //$a_set
     //   $this->tpl->setVariable('IMG_RECURRENCE_TITLE', $this->lng->txt("room_date_recurrence"));

        $this->tpl->setVariable('TXT_DATE', $a_set['pic']);
        $this->tpl->setVariable('TXT_SUBJECT', $a_set['title']);
        $this->tpl->setVariable('TXT_COURSE', $a_set['description']);
//        $this->tpl->setVariable('TXT_MODULE', ($a_set['module'] == null ? '' : $a_set['module']));
//        $this->tpl->setVariable('TXT_SUBJECT', ($a_set['subject'] == null ? '' : $a_set['subject']));
//        $this->tpl->setVariable('TXT_COURSE', ($a_set['course'] == null ? '' : $a_set['course']));
//      $this->tpl->setVariable('TXT_SEMESTER', ($a_set['semester'] == null ? '' : $a_set['semester']));
//        $this->tpl->setVariable('TXT_ROOM', $a_set['room']);

        // Teilnehmer
//        $participant_count = count($a_set['participants']);
//        for ($i = 0; $i < $participant_count; ++$i) {
//            $this->tpl->setCurrentBlock("participants");
//            $participant = $a_set['participants'][$i];
//
//            if ($i < $participant_count - 1) {
//                $this->tpl->setVariable('TXT_COMMA', ',');
//            }
//            $this->tpl->setVariable('TXT_PARTICIPANT', $participant);
//            $this->tpl->parseCurrentBlock();
//        }
        // Aktionen
//        $this->tpl->setVariable('LINK_EDIT', $this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
//        $this->tpl->setVariable('LINK_EDIT_TXT', $this->lng->txt('edit'));
//        $this->tpl->setVariable('LINK_CANCEL', $this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
//        $this->tpl->setVariable('LINK_CANCEL_TXT', $this->lng->txt('room_cancel'));
    }

}
