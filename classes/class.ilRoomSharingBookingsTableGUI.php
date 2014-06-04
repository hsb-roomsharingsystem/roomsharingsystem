<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* Class ilRoomSharingBookingsTableGUI
* 
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
*
*@ilCtrl_IsCalledBy ilRoomSharingBookingsTableGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
*
*/

class ilRoomSharingBookingsTableGUI extends ilTable2GUI
{   
    protected $bookings;
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
        
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookings.php';
		$this->bookings = new ilRoomSharingBookings();
		parent::__construct($a_parent_obj, $a_parent_cmd);
        
		$this->setTitle($lng->txt("rep_robj_xrs_bookings"));
		$this->setLimit(10);      // data sets per page
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		
        $this->addColumns();    // add columns and column headings
        $this->setSelectAllCheckbox('bookings');   // checkboxes labeled with "bookings" get
                                                   // get affected by the "Select All"-Checkbox
		$this->setRowTemplate("tpl.room_bookings_row.html", "Modules/RoomSharing");
		// command for cancelling bookings
        $this->addMultiCommand('showBookings', $this->lng->txt('rep_robj_xrs_cancel'));
        
		$this->getItems();
    }
	
    /**
     * Gets all the items that need to populated into the table.
     */
    function getItems()
    {
        $data = $this->bookings->getList();
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
    }
    
    /**
     * Adds columns and column headings to the table.
     */
    private function addColumns()
    {
        $this->addColumn('','f','1');   // checkboxes
        $this->addColumn('','f','1');   // icons 
		$this->addColumn($this->lng->txt("rep_robj_xrs_date"), "date");
        $this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_subject"), "subject");
		$this->addColumn($this->lng->txt("rep_robj_xrs_participants"), "participants");
        
        // Add the selected optional columns to the table
        foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($c, $c);
		}
        $this->addColumn($this->lng->txt(''),'optional');
    }
    
    /**
     * Fills an entire table row with the given set.
     */
    public function fillRow($a_set)
    {
        // the "CHECKBOX_NAME" has to match with the label set in the 
        // setSelectAllCheckbox()-function in order to be affected when the
        // "Select All" Checkbox is checked
        $this->tpl->setVariable('CHECKBOX_NAME', 'bookings');   

        if ($a_set['recurrence'])
        {   
            // icon for the recurrence date
            $this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
        } 
        $this->tpl->setVariable('IMG_RECURRENCE_TITLE', $this->lng->txt("rep_robj_xrs_room_date_recurrence"));
        
        $this->tpl->setVariable('TXT_DATE', $a_set['date']);
        $this->tpl->setVariable('TXT_ROOM', $a_set['room']);
        $this->tpl->setVariable('TXT_SUBJECT', $a_set['subject']);
        
        // Teilnehmer
        $participant_count = count($a_set['participants']);
        for($i = 0; $i < $participant_count; ++$i) 
        {
            $this->tpl->setCurrentBlock("participants");
            $participant = ilObjUser::_lookupFullName($a_set['participants'][$i]);
            
            // if the user doesn't exist
            if(!trim($participant))
            {
                $participant = "[".$this->lng->txt("user_deleted")."]";
            }
            else        // put together a link for the user profile view
            {			
                $this->ctrl->setParameterByClass('ilobjroomsharingpoolgui', 'user_id', $a_set['participants'][$i]);
                $this->tpl->setVariable('HREF_PROFILE', $this->ctrl->getLinkTargetByClass('ilobjroomsharingpoolgui', 'showprofile'));
                // unset the parameter for safety purposes
                $this->ctrl->setParameterByClass('ilobjroomsharingpoolgui', 'user_id', '');
            }
            
            $this->tpl->setVariable("TXT_PARTICIPANT", $participant);
            
            if($i < $participant_count - 1)
            { 
                $this->tpl->setVariable('TXT_COMMA', ',');
            }
            $this->tpl->parseCurrentBlock();
        }    
 
        // Populate the selected additional table cells
        foreach ($this->getSelectedColumns() as $c)
		{
            $this->tpl->setCurrentBlock("additional");
            $this->tpl->setVariable("TXT_ADDITIONAL", $a_set[$c] == null ? "" : $a_set[$c]);
            $this->tpl->parseCurrentBlock();
        }
        
        // actions

        $this->tpl->setCurrentBlock("actions");
        $this->tpl->setVariable('LINK_ACTION',$this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
        $this->tpl->setVariable('LINK_ACTION_TXT',$this->lng->txt('edit'));
        $this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable('LINK_ACTION',$this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
        $this->tpl->setVariable('LINK_ACTION_TXT',$this->lng->txt('rep_robj_xrs_cancel'));
        $this->tpl->parseCurrentBlock();
    }
       
    /**
     * Can be used to add additional columns to the bookings table.
     * @return boolean
     */
    function getSelectableColumns()
    {
        return $this->bookings->getBookingAddenda();
    }
}
?>
