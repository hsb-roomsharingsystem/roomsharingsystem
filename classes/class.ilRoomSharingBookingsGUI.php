<?php

/**
* Class ilRoomSharingBookingsGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingBookingsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* 
*/
class ilRoomSharingBookingsGUI
{

    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor of ilRoomSharingOverviewGUI
     * @param	object	$a_parent_obj
     */
    function __construct(ilRoomSharingOverviewGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->ref_id = $a_parent_obj->ref_id;

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }
	
	
	function performCommand($cmd)
	{
		echo "Perform CMD: ".$cmd;
	}

    /**
     * Main switch for command execution.
     */
    function executeCommand()
    {
        // Auskommentiert lassen, sonst kracht das Programm. Warum, wird noch erforscht.
//		$next_class = $ilCtrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("showBookings");





          // Auskommentiert lassen, sonst kracht das Programm. Warum, wird noch erforscht.
//		$next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("showBookings");

        if ($cmd == 'render') {
        	$cmd = 'showBookings';
        }
        
		switch($next_class)
		{
			default:
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}
	    /**
     * Shows all made bookings.
     */
    function showBookingsObject()
    {
        global $tpl;

        include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        $toolbar = new ilToolbarGUI;
        $toolbar->addButton($this->lng->txt('room_booking_add'), $this->ctrl->getLinkTarget($this, "showBookings"));
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookingsTableGUI.php");
        $bookingsTable = new ilRoomSharingBookingsTableGUI($this, 'showBookings', $this->ref_id);

        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
        $plink = new ilPermanentLinkGUI('room', $this->ref_id);

        $tpl->setContent($toolbar->getHTML() . $bookingsTable->getHTML() . $plink->getHTML());
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
