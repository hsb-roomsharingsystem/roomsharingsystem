<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRooms.php');

/**
* Class ilRoomSharingBookableRoomsTableGUI
* 
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingBookableRoomsTableGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
*
*/

class ilRoomSharingBookableRoomsTableGUI extends ilTable2GUI
{
    protected $bookable_rooms;
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
        parent::__construct($a_parent_obj, $a_parent_cmd);
		// in order to keep filter settings, table ordering etc.  set an ID
        // this is better to be unset for debug sessions
//        $this->setId("roomtable");        
        $this->setNoEntriesText($lng->txt("no_items"));
        $this->bookable_rooms = new ilRoomSharingBookableRooms();

		$this->setLimit(20);      // datasets that are displayed per page
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->initFilter();
        $this->setEnableHeader(true);
        $this->addColumns();    // add columns and column headings
		$this->setEnableHeader(true);
		$this->setRowTemplate("tpl.room_bookable_rooms_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");

		$this->getItems($this->getCurrentFilter());
    }
	
    /**
     * Gets all the items that need to populated into the table.
     */
    function getItems(array $filter)
    {
        $data = $this->bookable_rooms->getList($filter);
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
    }
    
    /**
     * Adds columns and column headings to the table.
     */
    private function addColumns()
    {
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_seats"), "seats");
		$this->addColumn($this->lng->txt("rep_robj_xrs_room_attributes"));   // not sortable
        $this->addColumn("", "action");
    }
   
    /**
     * Fills an entire table row with the given set. The corresponding array
     * has the following shape:
     * 
     * $res[] =  array('room' => "012",
     *                   'seats' => 120,
     *                   'attributes' => array('Beamer' => 1, 
     *                                         'Overhead Projector' => 1, 
     *                                         'Whiteboard' => 1,
     *                                         'Sound System' => 1)
     *                   );
     *
     */
    public function fillRow($a_set)
    {
           
        $this->tpl->setVariable('TXT_ROOM',$a_set['room']);
        $this->tpl->setVariable('TXT_SEATS',$a_set['seats']);
        
        // Room Attributes     
        $attribute_keys = array_keys($a_set['attributes']);
        $attribute_count = count($attribute_keys); 
        for($i = 0; $i < $attribute_count; ++$i) 
        {
            $this->tpl->setCurrentBlock('attributes');
            $attribute = $attribute_keys[$i];
            
            if($i < $attribute_count - 1)
            { 
                $this->tpl->setVariable('TXT_BREAK', '<br>');
            }
            $this->tpl->setVariable('TXT_AMOUNT', $a_set['attributes'][$attribute]);
            $this->tpl->setVariable('TXT_ATTRIBUTE', $attribute);
            $this->tpl->parseCurrentBlock();
        }   
        
        // Aktionen
        $this->tpl->setVariable('LINK_BOOK', $this->ctrl->getLinkTarget($this->parent_obj, 'showBookableRooms'));
        $this->tpl->setVariable('LINK_BOOK_TXT',$this->lng->txt('rep_robj_xrs_room_book'));
    }
    
    /**
     * Removes all unset filter entries in order to returns a filter for the 
     * database-queries.
     * 
     * @return array the filter 
     */
    function getCurrentFilter()
    {
        $filter = array();
        if($this->filter["room"]["room_name"])
		{
            $filter["room_name"] = $this->filter["room"]["room_name"];
		}
        if($this->filter["room"]["room_seats"])
		{
            $filter["room_seats"] = $this->filter["room"]["room_seats"];
		}
        
        if($this->filter["date_range"]["from"] && $this->filter["date_range"]["to"]) 
        {
            $date_range_from = $this->filter["date_range"]["from"]->get(IL_CAL_UNIX);
            $date_range_to = $this->filter["date_range"]["to"]->get(IL_CAL_UNIX);
            $filter["date_from"] = $date_range_from;
            $filter["date_to"] = $date_range_to;
            
            if($this->filter["time_duration"]["time_duration_continuous"] && $this->filter["time_duration"]["time_duration_continuous"]->get(IL_CAL_UNIX) > 0)
            {
               $filter["time_duration"] = $this->filter["time_duration"]["time_duration_continuous"]->get(IL_CAL_UNIX);
            } 
            elseif($this->filter["time_range"]["from"] && $this->filter["time_range"]["to"])
            {
                $time_range_from = $this->filter["time_range"]["from"]->get(IL_CAL_UNIX);
                $time_range_to = $this->filter["time_range"]["to"]->get(IL_CAL_UNIX);
                
                $filter["date_from"] += $time_range_from;
                $filter["date_to"] += $time_range_to;
            }   
        }
        
        if($this->filter["attributes"]) 
        {
            foreach ($this->filter["attributes"] as $key => $value)
            {
                if($value["amount"]) 
                {
                    $filter["attributes"][$key] = $value["amount"];
                }
            }
        }
        
        return $filter;
    }
    
    /**
     * Initialize a search filter for ilRoomSharingBookableRoomsTableGUI.
     * 
     */
	function initFilter()
	{
        global $lng;
        // "Room"
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
        $room_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_room"), "room");
		$room_name_input = new ilTextInputGUI("", "room_name");
        $room_name_input->setMaxLength(14);
        $room_name_input->setSize(14);
        $room_comb->addCombinationItem("room_name", $room_name_input, $this->lng->txt("rep_robj_xrs_room_name"));
        // TODO: Implement own ilNumberInputGUI for your very own needs
        $room_seats_input = new ilNumberInputGUI("", "room_seats");     
        $room_seats_input->setMaxLength(8);
        $room_seats_input->setSize(8);
        $room_comb->addCombinationItem("room_seats", $room_seats_input, $this->lng->txt("rep_robj_xrs_seats"));
        $this->addFilterItem($room_comb);
	    $room_comb->readFromSession();     // get the value that was submitted
        $this->filter["room"] = $room_comb->getValue();
        
        // "Date Range"
        $date_range = $this->addFilterItemByMetaType("date_range", ilTable2GUI::FILTER_DATE_RANGE, false, $this->lng->txt("rep_robj_xrs_date_range"));
        $this->filter["date_range"] = $date_range->getDate();
        
        // "Time Range"
        // the time range is of the type ilCombinationInputGUI and holds a
        // "from" and "to" text input with special date abilities   
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingDateTimeInputGUI.php");
		$time_range = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_time_range"), "time_range");
		$time_range_from = new ilRoomSharingDateTimeInputGUI("", "time_range_from");
        $time_range_from->setShowTime(true);
        $time_range_from->setShowDate(false);
        $time_range->addCombinationItem("from", $time_range_from, $lng->txt("from"));
        $time_range_to = new ilRoomSharingDateTimeInputGUI("", "time_range_to");
        $time_range_to->setShowTime(true);
        $time_range_to->setShowDate(false);
        $time_range->addCombinationItem("to", $time_range_to, $lng->txt("to"));
        $time_range->setMode(ilDateTimeInputGUI::MODE_INPUT);
        $this->addFilterItem($time_range);
	    $time_range->readFromSession();     // get the value that was submitted
        $this->filter["time_range"] = $time_range->getDate();
        
        // "Time Duration"   
        $time_duration_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_time_duration"), "time_duration");
        $time_duration_input = new ilRoomSharingDateTimeInputGUI("", "time_duration");
        $time_duration_input->setMode(ilDateTimeInputGUI::MODE_INPUT);
        $time_duration_comb->addCombinationItem("time_duration_continuous", $time_duration_input, $this->lng->txt("rep_robj_xrs_time_duration_continuous"));
        $time_duration_input->setShowTime(true);
        $time_duration_input->setShowDate(false);
        $this->addFilterItem($time_duration_comb);
		$time_duration_comb->readFromSession();    // get the value that was submitted
        $this->filter["time_duration"] = $time_duration_comb->getDate();
         

        // "Room Attributes" 
        // as optional filters
        include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRooms.php');
        $lng->loadLanguageModule("form");
        $room_attributes = $this->bookable_rooms->getAllAttributes();
        foreach ($room_attributes as $room_attribute)
        {
            // setup an ilCombinationInputGUI for the amount of the room attributes
            $room_attribute_comb = new ilCombinationInputGUI($room_attribute, "attribute_".$room_attribute);
			$room_attribute_input = new ilNumberInputGUI("", "attribute_".$room_attribute."_amount");
            // TODO: Should be determined by the room with the lowest amount of seats
            $room_attribute_input->setMaxLength(8);
            $room_attribute_input->setSize(8);
			$room_attribute_comb->addCombinationItem("amount", $room_attribute_input, $this->lng->txt("rep_robj_xrs_amount"));
            
            $this->addFilterItem($room_attribute_comb, true);   // true, since this is an optional filter
            $room_attribute_comb->readFromSession();
            $this->filter["attributes"][$room_attribute] = $room_attribute_comb->getValue();
            
            // set a default value of 1 if no value has been set before
            $room_attribute_value = $room_attribute_input->getValue();
            if (!$room_attribute_value || $room_attribute_value == "") {
                $room_attribute_input->setValue("1");
            }
        }
    }
}
?>
