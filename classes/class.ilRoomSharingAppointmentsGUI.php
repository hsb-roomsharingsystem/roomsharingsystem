<?php

/**
 * Class ilRoomSharingAppointmentsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilRoomSharingAppointmentsGUI: ilRoomSharingBookingsGUI, ilRoomSharingParticipationsGUI, ilCommonActionDispatcherGUI
 * 
 */
class ilRoomSharingAppointmentsGUI
{
    /**
     * Constructor of ilRoomSharingAppointmentsGUI
     * 
     * @param object $a_parent_obj        	
     */
    function __construct($a_parent_obj)
    {
        global $ilCtrl, $lng;

        $this->parent_obj = $a_parent_obj;
        $this->ref_id = $a_parent_obj->ref_id;
        $this->pool_id = $a_parent_obj->getPoolId();

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
    }

    function performCommand($cmd)
    {
        echo $cmd;
    }

    /**
     * Main switch for command execution.
     */
    function executeCommand()
    {
        global $ilCtrl, $tpl;

        // set cmd to 'showBookings' if no cmd can be found
        $cmd = $ilCtrl->getCmd("showBookings");
//		echo "<br>CMD: RoomSharingAppointmentsGUI." . $cmd;

//        $next_class = $ilCtrl->getNextClass();
        // if the plugin is called
        if ($cmd == 'render' || $cmd == 'schowContent')
        {
            $cmd = 'showBookings';
        } 
        else if ($cmd == 'cancelBooking') 
        {
            $next_class = 'ilroomsharingbookingsgui';
        }
  
        $ilCtrl->setReturn($this, "showBookings");

        switch ($next_class)
        {
            // Bookings
            case 'ilroomsharingbookingsgui' :
                $this->showBookings();
                break;

            // Participations
            case 'ilroomsharingparticipationsgui' :
                $this->showParticipations();
                break;

            default :
                $this->$cmd();
                break;
        }

        return true;
    }

    /**
     * Adds SubTabs for the MainTab "appointments".
     *
     * @param type $a_active
     *        	SubTab which should be activated after method call.
     */
    protected function setSubTabs($a_active)
    {
        global $ilTabs, $lng;
        $ilTabs->setTabActive('appointments');
        // Bookings
        $ilTabs->addSubTab('bookings', $lng->txt('rep_robj_xrs_bookings'), $this->ctrl->getLinkTargetByClass('ilroomsharingbookingsgui', 'showBookings'));

        // Participations
        $ilTabs->addSubTab('participations', $this->lng->txt('rep_robj_xrs_participations'), $this->ctrl->getLinkTargetByClass('ilroomsharingparticipationsgui', 'showParticipations'));
        $ilTabs->activateSubTab($a_active);
    }

    /**
     * Shows all bookings.
     */
    function showBookings()
    {
        $this->setSubTabs('bookings');
        include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookingsGUI.php");
        $object_gui = & new ilRoomSharingBookingsGUI($this);
        $this->ctrl->forwardCommand($object_gui);
    }
    
    /**
     * Show all participations.
     */
    function showParticipations()
    {
        $this->setSubTabs('participations');
        include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingParticipationsGUI.php");
        $object_gui = & new ilRoomSharingParticipationsGUI($this);
        $this->ctrl->forwardCommand($object_gui);
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
