<?php

/**
* Class ilRoomSharingRoomSearchGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingRoomSearchGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* 
*/
class ilRoomSharingRoomSearchGUI
{
	protected $ref_id; 
	protected $pool_id; 
	
	/**
	 * Constructor of ilRoomSharingRoomSearchGUI
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
	 * Main switch for command execution.
	 */
	function executeCommand()
	{
		global $ilCtrl;
// Auskommentiert lassen, sonst kracht das Programm. Warum, wird noch erforscht.
//		$next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("showRoomSearch");
		
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
	 * Show roomsearch.
	 */
	function showRoomSearchObject()
	{
		global $tpl;
        $tpl->setContent("Raumsuche");
        // Show 'not_implemented' message
        ilUtil::sendInfo($this->lng->txt("room_not_yet_implemented"), false);
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
