<?php

/**
* Class ilRoomSharingBookableRoomsGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingBookableRoomsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* 
*/
class ilRoomSharingBookableRoomsGUI
{
    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor for the class ilRoomSharingBookableRoomsGUI
     * @param object $a_parent_obj
     */
    public function __construct(ilRoomSharingRoomPlansGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->parent_obj = $a_parent_obj;
//         $this->ref_id = $a_parent_obj->ref_id;
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
        $cmd = $ilCtrl->getCmd("showBookableRooms");

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
     * Show a list of all bookable rooms.
     */
    public function showBookableRoomsObject()
    {
        global $tpl, $ilAccess;

        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php");
        $bookableRoomsTable = new ilRoomSharingBookableRoomsTableGUI($this, 'showBookableRooms', $this->ref_id);

        if ($ilAccess->checkAccess('write', '', $this->ref_id))
        {
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
            $toolbar = new ilToolbarGUI;
            $toolbar->addButton($this->lng->txt('room_room_add'), $this->ctrl->getLinkTarget($this, "showBookableRooms"));
            $bar = $toolbar->getHTML();
        }

        // the commands (functions) to be called when the correspondent buttons are clicked
        $bookableRoomsTable->setResetCommand("resetRoomFilter");
        $bookableRoomsTable->setFilterCommand("applyRoomFilter");
        $tpl->setContent($bar.$bookableRoomsTable->getHTML());
    }

    /**
     * Creates a new table for the bookable rooms and writes all the input 
     * values to the session, so that a filter can be applied.
     */
    public function applyRoomFilterObject()
    {
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php");
        $bookableRoomsTable = new ilRoomSharingBookableRoomsTableGUI($this, 'showBookableRooms', $this->ref_id);
        $bookableRoomsTable->writeFilterToSession();    // writes filter to session
        $bookableRoomsTable->resetOffset();             // set the record offset to 0 (first page)
        $this->showBookableRoomsObject();
    }

    /**
     * Resets all the input fields.
     */
    public function resetRoomFilterObject()
    {
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php");
        $bookableRoomsTable = new ilRoomSharingBookableRoomsTableGUI($this, 'showBookableRooms', $this->ref_id);
        $bookableRoomsTable->resetFilter();
        $bookableRoomsTable->resetOffset();             // set the record offset to 0 (first page)
        $this->showBookableRoomsObject();
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
