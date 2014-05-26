<?php

/**
* Class ilRoomSharingRoomAdministrationGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingRoomAdministrationGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* 
*/
class ilRoomSharingRoomAdministrationGUI
{
	protected $ref_id; 
	protected $pool_id; 
	
	/**
	 * Constructor for the class ilRoomSharingRoomAdministrationGUI
	 * @param	object	$a_parent_obj
	 */
	function __construct(ilRoomSharingRoomPlansGUI $a_parent_obj)
	{   
        global $ilCtrl, $lng, $tpl;
        
        $this->ref_id = $a_parent_obj->ref_id;
        
        $this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;	
	}

	/**
	 * Execute the command given.
	 */
	function executeCommand()
	{
		global $ilCtrl;

        // the default command, if none is set
        $cmd = $ilCtrl->getCmd("showBookableRooms");
		
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
	 * Show all manageable rooms.
	 */
	function showRoomAdministrationObject()
	{
		global $tpl, $ilCtrl;
        

		include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$bar = new ilToolbarGUI;
		$bar->addButton($this->lng->txt("room_room_add"), $ilCtrl->getLinkTarget($this, 'showRoomAdministration'));
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomAdministrationTableGUI.php");
        $roomAdministrationTable = new ilRoomSharingRoomAdministrationTableGUI($this, 'showRoomAdministration', $this->ref_id);
		     
        // the commands (functions) to be called when the correspondent buttons are clicked
        $roomAdministrationTable->setResetCommand("resetRoomFilter");  
		$roomAdministrationTable->setFilterCommand("applyRoomFilter");
        
        $tpl->setContent($bar->getHTML().$roomAdministrationTable->getHTML());
	}
    
    /**
     * Applies the given filter options to the room table.
     */
    function applyRoomFilterObject() 
    {
		global $tpl;
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomAdministrationTableGUI.php");
		$roomAdministrationTable = new ilRoomSharingRoomAdministrationTableGUI($this, 'showRoomAdministration', $this->ref_id);
		$roomAdministrationTable->writeFilterToSession();    // writes filter to session
		$roomAdministrationTable->resetOffset();             // set the record offset to 0 (first page)
        $tpl->setContent($roomAdministrationTable->getHTML());
        $this->showRoomAdministrationObject();
    }
    
    function resetRoomFilterObject() 
    {
        global $tpl;
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomAdministrationTableGUI.php");
		$roomAdministrationTable = new ilRoomSharingRoomAdministrationTableGUI($this, 'showRoomAdministration', $this->ref_id);
        $roomAdministrationTable->resetFilter();
        $roomAdministrationTable->resetOffset();             // set the record offset to 0 (first page)
        $tpl->setContent($roomAdministrationTable->getHTML());
        $this->showRoomAdministrationObject();
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
