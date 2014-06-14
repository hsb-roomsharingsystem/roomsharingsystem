<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class ilRoomSharingParticipationsTableGUI
 * 
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Bernd Hitzelberger <bhitzelberger@stud.hs-bremen.de>
 * @version $Id$
 *
 */
class ilRoomSharingParticipationsTableGUI extends ilTable2GUI
{

	protected $participations;
	
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

        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingParticipations.php';
        $this->participations = new ilRoomSharingParticipations($a_parent_obj->getPoolId());
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("rep_robj_xrs_participations"));
        $this->setLimit(10);      // data sets per page
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->addColumns();    // add columns and column headings
        // checkboxes labeled with "participations" get affected by the "Select All"-Checkbox
        $this->setSelectAllCheckbox('participations');
        $this->setRowTemplate("tpl.room_appointment_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");
        // command for leaving 
        $this->addMultiCommand('showParticipations', $this->lng->txt('rep_robj_xrs_leave'));

        $this->getItems();
    }

    /**
     * Gets all participations for representation.
     */
    function getItems()
    {
        $data = $this->participations->getList();

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
        
        // Add the selected optional columns to the table
        foreach ($this->getSelectedColumns() as $c)
        {
        	$this->addColumn($c, $c);
        }
        $this->addColumn($this->lng->txt(''), 'optional');
        
    }

    /**
     * Fills each row with given data.
     */
    public function fillRow($a_set)
    {
        // Checkbox-Name must be the same which was set in setSelectAllCheckbox.
        $this->tpl->setVariable('CHECKBOX_NAME', 'participations');
		
        // ### Recurrence ###
        if ($a_set['recurrence'])
        {
            // Picture for recursive appointment.
            $this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
        }
        $this->tpl->setVariable('IMG_RECURRENCE_TITLE', $this->lng->txt("rep_robj_xrs_room_date_recurrence"));

        // ### Date ###
        $this->tpl->setVariable('TXT_DATE', $a_set['date']);
        
		// ### Room ###
        $this->tpl->setVariable('TXT_ROOM', $a_set['room']);
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_set['id']);
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'last_cmd', 'showParticipations');
        $this->tpl->setVariable('HREF_ROOM', $this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');
        
        $this->tpl->setVariable('TXT_SUBJECT', ($a_set['subject'] == null ? '' : $a_set['subject']));
        
        // ### Person Responsible ###
        $this->tpl->setVariable('TXT_USER', $a_set['person_responsible']);
        // put together a link for the profile view
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', $a_set['person_responsible_id']);
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'last_cmd', 'showParticipations');
        $this->tpl->setVariable('HREF_PROFILE', $this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showProfile'));
        // unset the parameter for safety purposes
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', '');

        // Populate the selected additional table cells
        foreach ($this->getSelectedColumns() as $c)
        {
        	$c = strtolower($c);
        	$this->tpl->setCurrentBlock("additional");
        	$this->tpl->setVariable("TXT_ADDITIONAL", $a_set[$c] == null ? "" : $a_set[$c]);
        	$this->tpl->parseCurrentBlock();
        }
        
        // actions
        $this->tpl->setVariable('LINK_ACTION', $this->ctrl->getLinkTarget($this->parent_obj, 'showParticipations'));
        $this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_leave'));
	}
	
	/** 
     * Can be used to add additional columns to the participations table.
	 */
	public function getSelectableColumns()
	{
		return $this->participations->getAdditionalBookingInfos();
	}
}

?>
