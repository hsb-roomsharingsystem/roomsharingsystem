<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingBookingsException.php");
require_once ("Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php");
require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookingsTableGUI.php");
require_once ("Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
require_once ("Services/Utilities/classes/class.ilConfirmationGUI.php");
require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");

/**
 * Class ilRoomSharingBookingsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 */
class ilRoomSharingBookingsGUI
{
	protected $ref_id;
	protected $pool_id;
	private $ctrl;
	private $lng;
	private $tpl;

	/**
	 * Constructor of ilRoomSharingBookingsGUI
	 *
	 * @global type $ilCtrl
	 * @global type $lng
	 * @global type $tpl
	 * @param ilRoomSharingAppointmentsGUI $a_parent_obj
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

		$cmd .= 'Object';
		$this->$cmd();

		return true;
	}

	/**
	 * Shows all made bookings.
	 *
	 * @global type $tpl
	 */
	function showBookingsObject()
	{
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_booking_add'),
			$this->ctrl->getLinkTargetByClass("ilobjroomsharinggui", "showSearchQuick"));

		$bookingsTable = new ilRoomSharingBookingsTableGUI($this, 'showBookings', $this->ref_id);

		$plink = new ilPermanentLinkGUI('room', $this->ref_id);

		$this->tpl->setContent($toolbar->getHTML() . $bookingsTable->getHTML() . $plink->getHTML());
	}

	/**
	 * Used for deleting bookings.
	 */
	public function cancelBookingObject()
	{
		$bookings = new ilRoomSharingBookings($this->pool_id);
		// the canceling has to be confirmed via a form, which is why we get the id from POST
		try
		{
			$bookings->removeBooking($_POST ["booking_id"]);
		}
		catch (ilRoomSharingBookingsException $exc)
		{
			ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
			$this->showBookingsObject();
		}
		$this->showBookingsObject();
	}

	/**
	 * Displays a confirmation dialog, in which the user is given the chance
	 * to decline or confirm his decision.
	 *
	 * @global ilTabs $ilTabs
	 */
	public function confirmCancelObject()
	{
		global $ilTabs;

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

		$this->tpl->setContent($confirmation->getHTML()); // display
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
