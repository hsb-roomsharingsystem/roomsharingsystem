<?php

/**
 * Class ilRoomSharingParticipationsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 * 
 */
class ilRoomSharingParticipationsGUI
{

    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor of ilRoomSharingParticipationsGUI
     * @param	object	$a_parent_obj
     */
    function __construct(ilRoomSharingAppointmentsGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->ref_id = $a_parent_obj->ref_id;
        $this->pool_id = $a_parent_obj->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    /**
     * Main switch for command execution.
     */
    function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showParticipations");

        if ($cmd == 'render')
        {
        	$cmd = 'showParticipations';
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
     * Show all participations.
     */
    function showParticipationsObject()
    {
        global $tpl;

        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
        $plink = new ilPermanentLinkGUI('room', $this->ref_id);

        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingParticipationsTableGUI.php");
        $participationsTable = new ilRoomSharingParticipationsTableGUI($this, 'showParticipations', $this_ref_id);

        $tpl->setContent($participationsTable->getHTML() . $plink->getHTML());
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
