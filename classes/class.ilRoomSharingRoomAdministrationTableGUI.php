<?php
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRooms.php');
include_once('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php');

/**
* Class ilRoomSharingRoomAdministrationTableGUI
* 
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingRoomAdministrationTableGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* 
*/

class ilRoomSharingRoomAdministrationTableGUI extends ilRoomSharingBookableRoomsTableGUI
{
    /**
	 * Constructor
	 * @param	object	$a_parent_obj
	 */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_ref_id);
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
        $this->tpl->setVariable('LINK_BOOK', $this->ctrl->getLinkTarget($this->parent_obj, 'showRoomAdministration'));
        $this->tpl->setVariable('LINK_BOOK_TXT',$this->lng->txt('room_room_edit'));
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
        $room_comb = new ilCombinationInputGUI($this->lng->txt("room_room"), "room");
		$room_name_input = new ilTextInputGUI("", "room_name");
        $room_name_input->setMaxLength(14);
        $room_name_input->setSize(14);
        $room_comb->addCombinationItem("room_name", $room_name_input, $this->lng->txt("room_room_name"));
        // TODO: Implement own ilNumberInputGUI for your very own needs
        $room_seats_input = new ilNumberInputGUI("", "room_seats");     
        $room_seats_input->setMaxLength(8);
        $room_seats_input->setSize(8);
        $room_comb->addCombinationItem("room_seats", $room_seats_input, $this->lng->txt("room_seats"));
        $this->addFilterItem($room_comb);
	    $room_comb->readFromSession();     // get the value that was submitted
        $this->filter["room"] = $room_comb->getValue();

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
			$room_attribute_comb->addCombinationItem("amount", $room_attribute_input, $this->lng->txt("room_amount"));
            
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
