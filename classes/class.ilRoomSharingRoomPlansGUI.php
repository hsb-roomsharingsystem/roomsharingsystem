<?php

/**
* Class ilRoomSharingRoomPlansGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_Calls ilRoomSharingRoomPlansGUI: ilRoomSharingBookableRoomsGUI
* @ilCtrl_Calls ilRoomSharingRoomPlansGUI: ilRoomSharingRoomSearchGUI
* @ilCtrl_Calls ilRoomSharingRoomPlansGUI: ilRoomSharingRoomAdministrationGUI
* @ilCtrl_IsCalledBy ilRoomSharingRoomPlansGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
*/
class ilRoomSharingRoomPlansGUI
{
	/**
	 * Constructor of ilRoomSharingRoomPlansGUI
	 * @param	object	$a_parent_obj
	 */
	function __construct()
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
	function executeCommand()
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
            
//			// Roomsearch
//            case 'ilroomsharingroomsearchgui':
//				$this->showRoomSearchObject();
//				break;
            
			// Room administration
            case 'ilroomsharingroomadministrationgui':
				$this->showRoomAdministrationObject();
				break;
            
            default:
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}

    /**
     * Add SubTabs to MainTab "room_plans".
     * @param type $a_active SubTab which should be activated after method call.
     */
    protected function setSubTabs($a_active) 
    {
        global $ilTabs, $ilAccess;
        
        $ilTabs->setTabActive('room_plans');
        // Bookable rooms.
        $ilTabs->addSubTab('bookable_rooms',
			$this->lng->txt('room_view'),
				$this->ctrl->getLinkTargetByClass('ilroomsharingbookableroomsgui', 'showBookableRooms'));
        
        // Roomsearch.
//        $ilTabs->addSubTab('room_search',
//			$this->lng->txt('room_search'),
//				$this->ctrl->getLinkTargetByClass('ilroomsharingroomsearchgui', 'showRoomSearch'));
        
        if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
            // Roomadministration.
            $ilTabs->addSubTab('room_administration',
                $this->lng->txt('room_manage'),
                    $this->ctrl->getLinkTargetByClass('ilroomsharingroomadministrationgui', 'showRoomAdministration'));
        
            $ilTabs->activateSubTab($a_active);
        }
    }
    
	/**
	 * Show bookable rooms.
	 */
	function showBookableRoomsObject()
	{
        $this->setSubTabs('bookable_rooms');
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsGUI.php");
		$object_gui =& new ilRoomSharingBookableRoomsGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

    /**
	 * Show roomsearch tab.
	 */
//	function showRoomSearchObject()
//	{
//		$this->setSubTabs('room_search');
//		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomSearchGUI.php");
//		$object_gui =& new ilRoomSharingRoomSearchGUI($this);
//		$this->ctrl->forwardCommand($object_gui);
//	}
    
    /**
	 * Show room administration.
	 */
	function showRoomAdministrationObject()
	{
		$this->setSubTabs('room_administration');
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomAdministrationGUI.php");
		$object_gui =& new ilRoomSharingRoomAdministrationGUI($this);
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
