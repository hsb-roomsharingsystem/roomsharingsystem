<?php

/**
 * Class ilRoomSharingBookGUI
 * @author Michael Dazjuk
 * @version $Id$
 */
class ilRoomSharingBookGUI
{

    protected $ref_id;
    protected $pool_id;

    /**
     * Constructur for ilRoomSharingBookGUI
     * @param	object	$a_parent_obj
     */
    function __construct(ilObjRoomSharingGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->pool_id = $a_parent_obj->getPoolId();
        $this->ref_id = $a_parent_obj->ref_id;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    /**
     * Main switch for command execution.
     */
    function executeCommand()
    {
        global $ilCtrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("render");

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
     * Render list of booking objects
     *
     * uses ilBookingObjectsTableGUI
     */
    function renderObject()
    {
        global $tpl, $ilAccess;

        if ($ilAccess->checkAccess('write', '', $this->ref_id))
        {
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        }

        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
        $plink = new ilPermanentLinkGUI('book', $this->ref_id);

        $tpl->setContent($this->initForm()->getHTML() . $plink->getHTML());
    }

    /**
     * Form for booking
     * @return Returns the GUI
     */
    function initForm()
    {
        global $lng, $ilCtrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("room_book"));
        
        // text input
        $subject = new ilTextInputGUI($lng->txt("subject"), "subject");
        $subject->setRequired(true);
        $subject->setSize(40);
        $subject->setMaxLength(120);
        $form->addItem($subject);
        
        $form->addCommandButton("save", $lng->txt("rep_robj_xrs_room_book"));
        $form->addCommandButton("book_reset", $lng->txt("reset"));
        $form->addCommandButton("book_cancel", $lng->txt("cancel"));
        
        include_once('class.ilRoomSharingBookings.php');
        $ilBookings = new ilRoomSharingBookings();
        $ilBookings->setPoolId($this->pool_id);
        foreach ($ilBookings->getAdditionalBookingInfos() as $attr_key => $attr_value) {
            $formattr = new ilTextInputGUI($attr_value['txt'], $attr_value['id']);
            $formattr->setSize(40);
            $formattr->setMaxLength(120);
            $form->addItem($formattr);
        }
        
        include_once("class.ilRoomSharingDateTimeInputGUI.php");
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");

        $time_range = new ilCombinationInputGUI($this->lng->txt("assessment_log_datetime"), "time_range");
        //$time_range->setRequired(true);

        $dt_prop = new ilDateTimeInputGUI($lng->txt("of"), "from");
        $time_range->addCombinationItem("of", $dt_prop, $lng->txt("of"));
        $dt_prop->setShowTime(true);

        $dt_prop1 = new ilDateTimeInputGUI($lng->txt("to"), "to");
        $time_range->addCombinationItem("to", $dt_prop1, $lng->txt("to"));
        $dt_prop1->setShowTime(true);
        $form->addItem($time_range);

        // checkbox to confirm the room use agreement       
        $cb_prop = new ilCheckboxInputGUI($lng->txt("rep_robj_xrs_room_use_agreement"), "accept_room_rules");
        $cb_prop->setValue("1");
        $cb_prop->setChecked(false);
        $cb_prop->setRequired(true);
        $form->addItem($cb_prop);
        
        // checkbox to confirm the room use agreement   
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookInputGUI.php';
        //$participants = new ilRoomSharingBookInputGUI($lng->txt("participants"), "participants");
        //$participants->setRequired(true);
        //$form->addItem($participants);

        return $form;
    }
    
    function saveObject() {
        global $tpl;
        $form = $this->initForm();
        //print_r($form->getInputItemsRecursive());
        if($form->checkInput()) {
            include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBook.php");
            $book = new ilRoomSharingBook();
            $book->setPoolId($this->getPoolId());
            $booking_values_array = array();
            $booking_values_array['subject'] = $form->getInput('subject');
            $booking_values_array['from'] = $form->getInput('from');
            $booking_values_array['to'] = $form->getInput('to');
            $booking_values_array['accept_room_rules'] = $form->getInput('accept_room_rules');
            
            $booking_attr_values_array = array();
            include_once('class.ilRoomSharingBookings.php');
            $ilBookings = new ilRoomSharingBookings();
            $ilBookings->setPoolId($this->pool_id);
            foreach($ilBookings->getAdditionalBookingInfos() as $attr_key => $attr_value) {
                $booking_attr_values_array[$attr_value['id']] = $form->getInput($attr_value['id']);
            }
            
            $result = $book->addBooking($booking_values_array, $booking_attr_values_array);
            if($result == 1) {
                ilUtil::sendSuccess($this->lng->txt('booking_added'), true);
            } else {
                ilUtil::sendFailure($this->lng->txt('booking_add_error'), true);
            }
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }
    
    /**
     * Returns roomsharing pool id.
     */
    function getPoolId()
    {
        return $this->pool_id;
    }

    /**
     * Sets roomsharing pool id.
     */
    function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }

}

?>
