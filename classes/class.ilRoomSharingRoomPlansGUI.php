<?php

/**
* Class ilRoomSharingRoomPlansGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_Calls ilRoomSharingRoomPlansGUI: ilRoomSharingBookableRoomsGUI
* @ilCtrl_IsCalledBy ilRoomSharingRoomPlansGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
*/
class ilRoomSharingRoomPlansGUI
{
	/**
	 * Constructor of ilRoomSharingRoomPlansGUI
	 * @param	object	$a_parent_obj
	 */
	public function __construct(ilObjRoomSharingPoolGUI $a_parent_obj)
	{   
        global $ilCtrl, $lng, $tpl;
        
//         $this->parent_obj = $a_parent_obj;
//         $this->ref_id = $a_parent_obj->ref_id;
// 		$this->pool_id = $a_parent_obj->object->getId();	
        
        $this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;	
	}

	/**
	 * Main switch for command execution.
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("showBookableRooms");
		
		switch($next_class)
		{
			// Bookable rooms
            case 'ilroomsharingbookableroomsgui':
				$this->showBookableRoomsObject();
				break;
            default:
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}
    
	/**
	 * Display a list of all bookable rooms.
	 */
	public function showBookableRoomsObject()
	{
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsGUI.php");
		$object_gui =& new ilRoomSharingBookableRoomsGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

    /**
     * Returns roomsharing pool id.
     */
    public function getPoolId() 
    {
        return $this->pool_id;
    }
    
    /**
     * Sets roomsharing pool id.
     */
    public function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }
}
?>
