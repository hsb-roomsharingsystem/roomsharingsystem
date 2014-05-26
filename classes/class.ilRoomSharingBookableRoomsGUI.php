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
	 * @param	object	$a_parent_obj
	 */
	function __construct()
	{   
        global $ilCtrl, $lng, $tpl;
        
//         $this->ref_id = $a_parent_obj->ref_id;
        
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
	 * Show all bookable rooms.
	 */
	function showBookableRoomsObject()
	{
		global $tpl;
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php");
        $bookableRoomsTable = new ilRoomSharingBookableRoomsTableGUI($this, 'showBookableRooms', $this->ref_id);
		     
        // the commands (functions) to be called when the correspondent buttons are clicked
        $bookableRoomsTable->setResetCommand("resetRoomFilter");  
		$bookableRoomsTable->setFilterCommand("applyRoomFilter");
        
        $tpl->setContent($bookableRoomsTable->getHTML());
       
        // Kalenderintegration
//		$tpl =  new ilTemplate('tpl.roomsharing_calendar.html',true,true,'Modules/RoomSharing');
//
//        include_once('./Services/Calendar/classes/class.ilDate.php');
//        $date = new ilDate();
//
//		include_once('./Services/Calendar/classes/class.ilMiniCalendarGUI.php');
//		$mini = new ilMiniCalendarGUI($date, $this);
//		$tpl->setVariable('MINICAL', $mini->getHTML());
//		$this->tpl->setRightContent($tpl->get());
	}
    
    /**
     * Applies the given filter options to the room table.
     */
    function applyRoomFilterObject() 
    {
		global $tpl;
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php");
		$bookableRoomsTable = new ilRoomSharingBookableRoomsTableGUI($this, 'showBookableRooms', $this->ref_id);
		$bookableRoomsTable->writeFilterToSession();    // writes filter to session
		$bookableRoomsTable->resetOffset();             // set the record offset to 0 (first page)
        $tpl->setContent($bookableRoomsTable->getHTML());
        $this->showBookableRoomsObject();
    }
    
    function resetRoomFilterObject() 
    {
        global $tpl;
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookableRoomsTableGUI.php");
		$bookableRoomsTable = new ilRoomSharingBookableRoomsTableGUI($this, 'showBookableRooms', $this->ref_id);
        $bookableRoomsTable->resetFilter();
        $bookableRoomsTable->resetOffset();             // set the record offset to 0 (first page)
        $tpl->setContent($bookableRoomsTable->getHTML());
        $this->showBookableRoomsObject();
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
