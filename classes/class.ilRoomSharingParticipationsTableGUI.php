<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class ilRoomSharingParticipationsTableGUI
 * 
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 */
class ilRoomSharingParticipationsTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     * @param	object	$a_parent_obj
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
    {
        global $ilCtrl, $lng;

        $this->parent_obj = $a_parent_obj;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->ref_id = $a_ref_id;
        $this->setId("roomobj");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("rep_robj_xrs_participations"));
        $this->setLimit(20);      // Anzahl der Datensätze pro Seite

        $this->addColumns();    // Spalten(-überschriften) hinzufügen
        $this->setSelectAllCheckbox('participations');   // zum Auswählen aller Checkboxes

        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.room_participations_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");
        // zum Stornieren der ausgewählten Checkboxes
        $this->addMultiCommand('showParticipations', $this->lng->txt('rep_robj_xrs_leave'));

        $this->getItems();
    }

    /**
     * Gets all participations for representation.
     */
    function getItems()
    {
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingParticipations.php';
        $participations = new ilRoomSharingParticipations();
        $data = $participations->getList();

        $this->setMaxCount(sizeof($data));
        $this->setData($data);
    }

    /**
     * Adds columns with translations.
     */
    private function addColumns()
    {
        $this->addColumn('', 'f', '1');   // checkboxes
        $this->addColumn('', 'f', '1');   // icons 
        $this->addColumn($this->lng->txt("rep_robj_xrs_date"), "date");
        $this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
        $this->addColumn($this->lng->txt("rep_robj_xrs_subject"), "subject");
        $this->addColumn($this->lng->txt("rep_robj_xrs_person_responsible"), "person_responsible");
        $this->addColumn($this->lng->txt(''), 'optional');
    }

    /**
     * Fills each row with given data.
     */
    public function fillRow($a_set)
    {
        // Checkbox-Name must be the same which was set in setSelectAllCheckbox.
        $this->tpl->setVariable('CHECKBOX_NAME', 'participations');

        if ($a_set['recurrence'])
        {
            // Picture for recursive appointment.
            $this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
        }
        $this->tpl->setVariable('IMG_RECURRENCE_TITLE', $this->lng->txt("rep_robj_xrs_room_date_recurrence"));

        $this->tpl->setVariable('TXT_DATE', $a_set['date']);
        $this->tpl->setVariable('TXT_SUBJECT', ($a_set['subject'] == null ? '' : $a_set['subject']));
        $this->tpl->setVariable('TXT_ROOM', $a_set['room']);
        $this->tpl->setVariable('TXT_PERSON_RESPONSIBLE', $a_set['person_responsible']);

        // Set actions.
        $this->tpl->setVariable('LINK_LEAVE', $this->ctrl->getLinkTarget($this->parent_obj, 'showParticipations'));
        $this->tpl->setVariable('LINK_LEAVE_TXT', $this->lng->txt('rep_robj_xrs_leave'));
    }

}

?>
