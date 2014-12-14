<?php

require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookingsGUI.php");
require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/participations/class.ilRoomSharingParticipationsGUI.php");

/**
 * Class ilRoomSharingAppointmentsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilRoomSharingAppointmentsGUI: ilRoomSharingBookingsGUI, ilRoomSharingShowAndEditBookGUI, ilRoomSharingParticipationsGUI, ilCommonActionDispatcherGUI
 *
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 */
class ilRoomSharingAppointmentsGUI
{
	public $ref_id;
	protected $ctrl;
	protected $lng;
	private $pool_id;

	/**
	 * Constructor of ilRoomSharingAppointmentsGUI
	 *
	 * @param object $a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		global $ilCtrl, $lng;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
	}

	/**
	 * Main switch for command execution.
	 *
	 * @return Returns always true.
	 */
	function executeCommand()
	{
		// set cmd to 'showBookings' if no cmd can be found
		$cmd = $this->ctrl->getCmd("showBookings");

		switch ($cmd)
		{
			case 'render':
			case 'showContent':
			case 'saveEditBook':
			case 'cancelBooking':
			case 'confirmMultipleCancels':
			case 'cancelMultipleBookings':
			case 'confirmCancel':
				$cmd = 'showBookings';
				break;
			case 'confirmLeaveParticipation':
			case 'confirmLeaveMultipleParticipations':
			case 'leaveMultipleParticipations':
				$cmd = 'showParticipations';
				break;
			default:
				break;
		}
		$this->$cmd();
		return true;
	}

	/**
	 * Adds SubTabs for the MainTab "appointments".
	 *
	 * @param type $a_active
	 *        	SubTab which should be activated after method call.
	 */
	protected function setSubTabs($a_active)
	{
		global $ilTabs;
		$ilTabs->setTabActive('appointments');
		// Bookings
		$ilTabs->addSubTab('bookings', $this->lng->txt('rep_robj_xrs_bookings'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingbookingsgui', 'showBookings'));

		// Participations
		$ilTabs->addSubTab('participations', $this->lng->txt('rep_robj_xrs_participations'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingparticipationsgui', 'showParticipations'));
		$ilTabs->activateSubTab($a_active);
	}

	/**
	 * Shows all bookings.
	 */
	function showBookings()
	{
		$this->setSubTabs('bookings');
		$object_gui = & new ilRoomSharingBookingsGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

	/**
	 * Shows one booking.
	 */
	function showBooking()
	{
		global $ilTabs;
		$ilTabs->clearTargets();
		$this->parent_obj->setTabs();
		$this->ctrl->setCmd("showBooking");
		$object_gui = & new ilRoomSharingBookingsGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

	/**
	 * Show all participations.
	 */
	function showParticipations()
	{
		$this->setSubTabs('participations');
		$object_gui = & new ilRoomSharingParticipationsGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return Room-ID
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
