<?php

/**
 * Class ilRoomSharingBookingsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 * 
 */
class ilRoomSharingBookingsGUI
{

    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor of ilRoomSharingBookingsGUI
     * @param	object	$a_parent_obj
     */
    function __construct(ilRoomSharingAppointmentsGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->ref_id = $a_parent_obj->ref_id;

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    function performCommand($cmd)
    {
//		echo "Perform CMD: ".$cmd;
    }

    /**
     * Main switch for command execution.
     */
    function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showBookings");

        if ($cmd == 'render')
        {
            $cmd = 'showBookings';
        }

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
     * Shows all made bookings.
     */
    function showBookingsObject()
    {
        global $tpl;

        include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        $toolbar = new ilToolbarGUI;
        $toolbar->addButton($this->lng->txt('rep_robj_xrs_booking_add'), $this->ctrl->getLinkTargetByClass("ilobjroomsharinggui", "showSearchQuick"));
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
