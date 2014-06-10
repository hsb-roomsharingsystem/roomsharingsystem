<?php

/**
 * Class ilRoomSharingBookGUI
 * @author Michael Dazjuk
 * @version $Id$
 */
class ilRoomSharingBookGUI {

//    protected $bookings;
    protected $ref_id;
    protected $pool_id;

//    protected $selected_item = array();

    /**
     * Constructur for ilRoomSharingBookGUI
     * @param	object	$a_parent_obj
     */
    function __construct(ilObjRoomSharingGUI $a_parent_obj) {
        global $ilCtrl, $lng, $tpl;

//include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBook.php';
//	$this->bookings = new ilRoomSharingBook();
        $this->ref_id = $a_parent_obj->ref_id;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
//        $this->addItems();
    }

    /**
     * Main switch for command execution.
     */
    function executeCommand() {
        global $ilCtrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("render");

        switch ($next_class) {
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
    function renderObject() {
        global $tpl, $ilAccess;

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        }

        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
        $plink = new ilPermanentLinkGUI('book', $this->ref_id);

        $tpl->setContent($this->initForm()->getHTML() . $plink->getHTML());
    }

    /**
     * Initialize form to boo
     */
    function initForm() {
        global $lng, $ilCtrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("room_book"));

// text input
        $thread = new ilTextInputGUI($lng->txt("thread"), "thread");
        $thread->setRequired(true);
        $thread->setSize(40);
        $thread->setMaxLength(120);
        $form->addItem($thread);
        $form->addCommandButton("room_book", $lng->txt("room_book"));
        $form->addCommandButton("book_reset", $lng->txt("reset"));
        $form->addCommandButton("book_cancel", $lng->txt("cancel"));
        
        include('class.ilRoomSharingBookings.php');
        $ilBookings = new ilRoomSharingBookings($pool_id);
        foreach($ilBookings->getAdditionalBookingInfos() as $attr => $attr_key) {
            $formding = new ilTextInputGUI($attr, $attr);
            $formding->setSize(40);
            $formding->setMaxLength(120);
            $form->addItem($formding);
        }
        
        include_once("class.ilRoomSharingDateTimeInputGUI.php");
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");

        $time_range = new ilCombinationInputGUI($this->lng->txt("assessment_log_datetime"), "time_range");
        $time_range->setRequired(true);

        $dt_prop = new ilDateTimeInputGUI($lng->txt("of"), "datetime5");
        $time_range->addCombinationItem("of", $dt_prop, $lng->txt("of"));
        $dt_prop->setShowTime(true);

        $dt_prop1 = new ilDateTimeInputGUI($lng->txt("to"), "datetime6");
        $time_range->addCombinationItem("to", $dt_prop1, $lng->txt("to"));
        $dt_prop1->setShowTime(true);
        $form->addItem($time_range);


        // checkbox to confirm the room use agreement       
        $cb_prop = new ilCheckboxInputGUI("Raumnutzungsvereinbarung akzeptieren", "cbox1");
        $cb_prop->setValue("1");
        $cb_prop->setChecked(false);
        $cb_prop->setRequired(true);
        $form->addItem($cb_prop);

//        foreach ($array as $key => $value) {
//            
//        }

        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookInputGUI.php';
        $participants = new ilRoomSharingBookInputGUI($lng->txt("participants"), "participants");
        $participants->setRequired(true);
        $form->addItem($participants);

        return $form;
    }

    /**
     * Render creation form
     */
    function createObject() {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('room_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

//    function getSelectedItems()
//	{
//		$scol = array();
//		foreach ($this->selected_item as $k => $v)
//		{
//			if ($v)
//			{
//				$scol[$k] = $k;
//			}
//		}
//		return $scol;
//	}
//     private function addItems()
//    {
//        // Add the selected optional columns to the table
//        foreach ($this->getSelectedItems() as $c)
//		{
//			$this->addItems($c, $c);
//		}
//        $this->addItems($this->lng->txt(''),'optional');
//    }
//      function getSelectableItems()
//    {
//        return $this->bookings->getBookingAddenda();
//    }

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

?>
