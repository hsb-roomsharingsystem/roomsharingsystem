<?php

/**
* Class ilRoomSharingOverviewGUI
*
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_Calls ilRoomSharingOverviewGUI: ilRoomSharingBookingsGUI, ilRoomSharingParticipationsGUI, ilCommonActionDispatcherGUI
* @ilCtrl_isCalledBy ilRoomSharingOverviewGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* 
*/


class ilRoomSharingOverviewGUI {
	/**
	 * Constructor of ilRoomSharingOverviewGUI
	 * 
	 * @param object $a_parent_obj        	
	 */
	function __construct($a_parent_obj) {
		global $ilCtrl, $lng, $tpl;
		
		$this->parent_obj = $a_parent_obj;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->object->getId ();
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
	}
	function performCommand($cmd) {
		echo $cmd;
	}

	
	/**
	 * Main switch for command execution.
	 */
	function executeCommand() {
		global $ilCtrl, $tpl;
		
		$cmd = $ilCtrl->getCmd ( "showBookings" );
		echo "<br>CMD: RoomSharingOverview." . $cmd;
		
		if ($cmd == 'render') {
			$cmd = 'showBookings';
		} else {
			$next_class = $ilCtrl->getNextClass ( $this );
			echo "<br>next_Class: " . $next_class;
		}
		
		$ilCtrl->setReturn ( $this, "showBookings" );
		
		switch ($next_class) {
			// Bookings
			case 'ilroomsharingbookingsgui' :
				$this->showBookings ();
				break;
			
			// Participations
			case 'ilroomsharingparticipationsgui' :
				$this->showParticipationsObject ();
				break;
			
			default :
				$this->$cmd ();
				break;
		}
		
		return true;
	}
	
	/**
	 * Adds SubTabs for the MainTab "overview".
	 *
	 * @param type $a_active
	 *        	SubTab which should be activated after method call.
	 */
	protected function setSubTabs($a_active) {
		global $ilTabs, $lng;
		$ilTabs->setTabActive ( 'overview' );
		// Buchungen
		$ilTabs->addSubTab ( 'bookings',  $lng->txt ( 'rep_robj_xrs_bookings' ), $this->ctrl->getLinkTargetByClass ( 'ilroomsharingbookingsgui', 'showBookings' ) );
		
		// Teilnahmen
		$ilTabs->addSubTab ( 'participations', $this->lng->txt ( 'rep_robj_xrs_participations' ), $this->ctrl->getLinkTargetByClass ( 'ilroomsharingparticipationsgui', 'showParticipations' ) );
		
		$ilTabs->activateSubTab ( $a_active );
	}
	
	/**
	 * Shows all bookings.
	 */
	function showBookings() {
		$this->setSubTabs ( 'bookings' );
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingBookingsGUI.php");
		$object_gui = & new ilRoomSharingBookingsGUI ( $this );
		$this->ctrl->forwardCommand ( $object_gui );
	}
	
	/**
	 * Show all participations.
	 */
	function showParticipationsObject() {
		$this->setSubTabs ( 'participations' );
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingParticipationsGUI.php");
		$object_gui = & new ilRoomSharingParticipationsGUI ( $this );
		$this->ctrl->forwardCommand ( $object_gui );
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
	function setPoolId($a_pool_id) {
		$this->pool_id = $a_pool_id;
	}
}
?>
