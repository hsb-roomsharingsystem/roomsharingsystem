<?php

/**
* Class ilRoomSharingParticipationsGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingParticipationsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
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
	function __construct(ilRoomSharingOverviewGUI $a_parent_obj)
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
        $cmd = $ilCtrl->getCmd("showParticipations");
		
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
	 * Show all participations.
	 */
	function showParticipationsObject()
	{
		global $tpl;
        
        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
		$plink = new ilPermanentLinkGUI('room', $this->ref_id);
        
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingParticipationsTableGUI.php");
        $participationsTable = new ilRoomSharingParticipationsTableGUI($this, 'showParticipations', $this_ref_id);
		     
        $tpl->setContent($participationsTable->getHTML().$plink->getHTML());
       
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
        * Returns roomsharing pool id.
        */
       function getPoolId() {
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
