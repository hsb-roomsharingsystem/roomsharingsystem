<?php

/**
 * Class ilRoomSharingBookingsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 */
class ilRoomSharingBookingsGUI
{
	protected $ref_id;
	protected $pool_id;

	/**
	 * Constructor of ilRoomSharingBookingsGUI
	 *
	 * @global type $ilCtrl
	 * @global type $lng
	 * @global type $ilCtrl
	 * @param object $a_parent_obj
	 */
	function __construct(ilRoomSharingAppointmentsGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;

		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}

	/**
	 * Main switch for command execution.
	 *
	 * @return Returns always true.
	 */
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd("showBookings");

		if ($cmd == 'render')
		{
			$cmd = 'showBookings';
		}

		switch ($next_class)
		{
			default :
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Shows all made bookings.
	 *
	 * @global type $tpl
	 */
	function showBookingsObject()
	{
		global $tpl;

		include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_booking_add'),
			$this->ctrl->getLinkTargetByClass("ilobjroomsharinggui", "showSearchQuick"));
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookingsTableGUI.php");
		$bookingsTable = new ilRoomSharingBookingsTableGUI($this, 'showBookings', $this->ref_id);

		include_once ('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
		$plink = new ilPermanentLinkGUI('room', $this->ref_id);

		$tpl->setContent($toolbar->getHTML() . $bookingsTable->getHTML() . $plink->getHTML());
	}

	/**
	 * Used for deleting bookings.
	 */
	public function cancelBookingObject()
	{
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");
		$bookings = new ilRoomSharingBookings($this->pool_id);
		// the canceling has to be confirmed via a form, which is why we get the id from POST
		$bookings->removeBooking($_POST ["booking_id"]);
		$this->showBookingsObject();
	}

	/**
	 * Displays a confirmation dialog, in which the user is given the chance
	 * to decline or confirm his decision.
	 *
	 * @global type $tpl
	 * @global type $ilTabs
	 */
	public function confirmCancelObject()
	{
		global $tpl, $ilTabs;
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt('rep_robj_xrs_booking_back'),
			$this->ctrl->getLinkTarget($this, 'showBookings'));

		// create the confirmation GUI
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this));
		$confirmation->setHeaderText($this->lng->txt('rep_robj_xrs_booking_confirm'));

		$booking_id = $_GET ["booking_id"];
		$booking_subject = $_GET ["booking_subject"];

		$confirmation->addItem('booking_id', $booking_id, $booking_subject);
		$confirmation->setConfirm($this->lng->txt('rep_robj_xrs_booking_confirm_cancel'), 'cancelBooking'); // cancel the booking
		$confirmation->setCancel($this->lng->txt('cancel'), 'showBookings'); // cancel the confirmation dialog

		$tpl->setContent($confirmation->getHTML()); // display
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return integer Pool-ID
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 *
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
