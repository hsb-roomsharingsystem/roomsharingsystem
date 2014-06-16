<?php

include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRooms.php');

/**
 * Class ilRoomSharingSearchQuickGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 */
class ilRoomSharingSearchQuickGUI
{

    protected $rooms;
    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor for the class ilRoomSharingSearchQuickGUI
     * @param object $a_parent_obj
     */
    public function __construct(ilRoomSharingSearchGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->parent_obj = $a_parent_obj;
        $this->ref_id = $a_parent_obj->ref_id;
        $this->pool_id = $a_parent_obj->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->rooms = new ilRoomSharingRooms($this->pool_id);
    }

    /**
     * Execute the command given.
     */
    public function executeCommand()
    {
        global $ilCtrl;

        // the default command, if none is set
        $cmd = $ilCtrl->getCmd("showSearchQuick");

        switch ($next_class)
        {
            default:
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Show a quick search form.
     */
    public function showSearchQuickObject()
    {
        global $tpl;
        $qsearch_form = $this->initForm();

        $tpl->setContent($qsearch_form->getHTML());
    }

    /**
     * Function which is called when the search results need to be applied.
     * @global type $tpl
     */
    public function applySearchObject() 
    {
        global $tpl;
        $qsearch_form = $this->initForm();

        // continue only if the input data is correct
        if ($qsearch_form->checkInput())
        {
            $qsearch_form->writeInputsToSession();
            $this->showSearchResultsObject();
        }
        // otherwise return to the form and display error messages if needed
        else 
        {
           $qsearch_form->setValuesByPost();
           $tpl->setContent($qsearch_form->getHTML()); 
        }     
    }
    
    /**
     * Displays the results for the given input.
     */
    public function showSearchResultsObject()
    {
        global $tpl;
        $qsearch_form = $this->initForm();
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomsTableGUI.php");
        $roomsTable = new ilRoomSharingRoomsTableGUI($this, 'showSearchResults', $this->ref_id);
        $roomsTable->setTitle($this->lng->txt("rep_robj_xrs_search_results"));
        $roomsTable->getItems($this->getFormInput($qsearch_form));
        $tpl->setContent($roomsTable->getHTML());
    }

    /**
     * Puts together an array which contains the search criterias for the 
     * search results. The standard procedure is to get those values from
     * POST, but here it is actually coming from the SESSION.
     */
    protected function getFormInput($a_qsearch_form)
    {
        $filter = array();
        $room = $a_qsearch_form->getInputFromSession("room_name");

        // "Room"
        // make sure that "0"-strings are not ignored  
        if ($room || $room === "0")
        {
            $filter["room_name"] = $room;
        }

        // "Seats"
        $seats = $a_qsearch_form->getInputFromSession("room_seats");
        if ($seats)
        {
            $filter["room_seats"] = $seats;
        }

        // "Date" and "Time"
        $date = $a_qsearch_form->getInputFromSession("date");
        $filter["date"] = $date["date"];
        $time_from = $a_qsearch_form->getInputFromSession("time_from");   
        $filter["time_from"] = $time_from["time"];
        $time_to = $a_qsearch_form->getInputFromSession("time_to");
        $filter["time_to"] = $time_to["time"];

        // "Room Attributes"
        $room_attributes = $this->rooms->getAllAttributes();
        foreach ($room_attributes as $room_attribute)
        {
            $attr_value = $a_qsearch_form->getInputFromSession("attribute_" . $room_attribute . "_amount", false);

            if ($attr_value)
            {
                $filter["attributes"][$room_attribute] = $attr_value;
            }
        }
        return $filter;
    }
 
    /**
     * Creates and returns the quick search form.
     * @return \ilPropertyFormGUI the quick search form
     */
    protected function initForm()
    {
        global $ilCtrl, $lng;
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingTextInputGUI.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingSearchFormGUI.php");
        $qsearch_form = new ilRoomSharingSearchFormGUI();
        $qsearch_form->setId("qsearchform");

        $this->createRoomFormItem($qsearch_form);
        $this->createSeatsFormItem($qsearch_form);
        $this->createDateFormItem($qsearch_form);
        $this->createTimeRangeFormItem($qsearch_form);
        $this->createRoomAttributeFormItem($qsearch_form);

        $qsearch_form->setTitle($lng->txt("rep_robj_xrs_quick_search"));
        $qsearch_form->addCommandButton("applySearch", $lng->txt("rep_robj_xrs_search"));
        $qsearch_form->setFormAction($ilCtrl->getFormAction($this));
        
        return $qsearch_form;
    }

    /**
     * Creates an input item which allows you to type in a room name.
     */
    protected function createRoomFormItem($a_qsearch_form)
    {
        $room_name_input = new ilRoomSharingTextInputGUI($this->lng->txt("rep_robj_xrs_room"), "room_name");
        $room_name_input->setParent($a_qsearch_form);
        $room_name_input->setMaxLength(14);
        $room_name_input->setSize(14);
        // if the input has been set before, set it with this very value
        $room_get_value = $_GET["room"];
        // if the user was redirected from the room list set the value for the room accordingly
        if ($room_get_value) 
        {
            $room_name_input->setValue($room_get_value);
        }
        else
        {
            $room_name_input->readFromSession(); 
        }
        $a_qsearch_form->addItem($room_name_input);
    }

    /**
     * Creates a combination input item consisting of a number input field for 
     * the desired seat amount.
     */
    protected function createSeatsFormItem($a_qsearch_form)
    {
        // Seats
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
        $room_seats_input = new ilRoomSharingNumberInputGUI($this->lng->txt("rep_robj_xrs_seats") . " (" . $this->lng->txt("rep_robj_xrs_amount") . ")", "room_seats");
        $room_seats_input->setParent($a_qsearch_form);
        $room_seats_input->setMaxLength(8);
        $room_seats_input->setSize(8);
        $room_seats_input->setMinValue(0);
        $room_seats_input->setMaxValue($this->rooms->getMaxSeatCount());
        $room_seats_input->readFromSession(); 
        $a_qsearch_form->addItem($room_seats_input);
    }

    protected function createDateFormItem($a_qsearch_form)
    {
        // Date
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $date_comb = new ilCombinationInputGUI($this->lng->txt("date"), "date");
        $date = new ilDateTimeInputGUI("", "date");
        $date_comb->setRequired(true);
        $date_comb->addCombinationItem("date", $date, $this->lng->txt("rep_robj_xrs_on"));
        $a_qsearch_form->addItem($date_comb);
    }

    protected function createTimeRangeFormItem($a_qsearch_form)
    {
        // Time Range
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingTimeInputGUI.php");
        $time_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_time_range"), "time");
        $time_from = new ilRoomSharingTimeInputGUI("", "time_from");
        $time_from->setShowTime(true);
        $time_from->setShowDate(false);
        $time_from->setMinuteStepSize(5);
        $time_comb->addCombinationItem("time_from", $time_from, $this->lng->txt("rep_robj_xrs_between"));
        $time_to = new ilRoomSharingTimeInputGUI("", "time_to");
        $time_to->setShowTime(true);
        $time_to->setShowDate(false);
        $time_to->setMinuteStepSize(5);
        $time_comb->addCombinationItem("time_to", $time_to, $this->lng->txt("and"));
        $time_comb->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
        $time_comb->setRequired(true);
        $a_qsearch_form->addItem($time_comb);
    }

    /**
     * If room attributes are present, display some input fields for the desired 
     * amount of those attributes. 
     */
    protected function createRoomAttributeFormItem($a_qsearch_form)
    {
        // Room Attributes
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
        $room_attributes = $this->rooms->getAllAttributes();
        foreach ($room_attributes as $room_attribute)
        {
            // setup an ilRoomSharingNumberInputGUI for the room attributes
            $room_attribute_input = new ilRoomSharingNumberInputGUI($room_attribute . " (" . $this->lng->txt("rep_robj_xrs_amount") . ")", "attribute_" . $room_attribute . "_amount");
            $room_attribute_input->setParent($a_qsearch_form);
            $room_attribute_input->setMaxLength(8);
            $room_attribute_input->setSize(8);
            $room_attribute_input->setMinValue(0);
            $room_attribute_input->setMaxValue($this->rooms->getMaxCountForAttribute($room_attribute));

            $room_attribute_input->readFromSession();
            $a_qsearch_form->addItem($room_attribute_input);
        }
    }
    
    /**
     * Returns the Roomsharing Pool ID.
     */
    public function getPoolId()
    {
        return $this->pool_id;
    }

    /**
     * Sets the Roomsharing Pool ID.
     */
    public function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }

}

?>
