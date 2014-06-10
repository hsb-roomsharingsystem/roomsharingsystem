<?php

/**
 * Class ilRoomSharingRoomsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 * 
 */
class ilRoomSharingRoomsGUI
{

    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor for the class ilRoomSharingRoomsGUI
     * @param object $a_parent_obj
     */
    public function __construct(ilObjRoomSharingGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->parent_obj = $a_parent_obj;
        $this->ref_id = $a_parent_obj->ref_id;
        $this->pool_id = $a_parent_obj->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    /**
     * Execute the command given.
     */
    public function executeCommand()
    {
        global $ilCtrl;

        // the default command, if none is set
        $cmd = $ilCtrl->getCmd("showRooms");

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
     * Show a list of all rooms.
     */
    public function showRoomsObject()
    {
        global $tpl, $ilAccess;

        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomsTableGUI.php");
        $roomsTable = new ilRoomSharingRoomsTableGUI($this, 'showRooms', $this->ref_id);
        $roomsTable->initFilter();
        $roomsTable->getItems($roomsTable->getCurrentFilter());
        
        if ($ilAccess->checkAccess('write', '', $this->ref_id))
        {
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
            $toolbar = new ilToolbarGUI;
            $toolbar->addButton($this->lng->txt('rep_robj_xrs_add_room'), $this->ctrl->getLinkTarget($this, "showRooms"));
            $bar = $toolbar->getHTML();
        }

        // the commands (functions) to be called when the correspondent buttons are clicked
        $roomsTable->setResetCommand("resetRoomFilter");
        $roomsTable->setFilterCommand("applyRoomFilter");
        $tpl->setContent($bar . $roomsTable->getHTML());
    }

    /**
     * Creates a new table for the  rooms and writes all the input 
     * values to the session, so that a filter can be applied.
     */
    public function applyRoomFilterObject()
    {
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomsTableGUI.php");
        $roomsTable = new ilRoomSharingRoomsTableGUI($this, 'showRooms', $this->ref_id);
        $roomsTable->initFilter();
        $roomsTable->writeFilterToSession();    // writes filter to session
        $roomsTable->resetOffset();             // set the record offset to 0 (first page)
        $this->showRoomsObject();
    }

    /**
     * Resets all the input fields.
     */
    public function resetRoomFilterObject()
    {
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomsTableGUI.php");
        $roomsTable = new ilRoomSharingRoomsTableGUI($this, 'showRooms', $this->ref_id);
        $roomsTable->initFilter();
        $roomsTable->resetFilter();
        $roomsTable->resetOffset();             // set the record offset to 0 (first page)
        $this->showRoomsObject();
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
