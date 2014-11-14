<?php

require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/participations/class.ilRoomSharingParticipationsTableGUI.php");

/**
 * Class ilRoomSharingParticipationsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilLanguage $lng
 * @property ilCtrl $ctrl
 * @property ilTemplate $tpl
 */
class ilRoomSharingParticipationsGUI
{
	protected $ref_id;
	protected $pool_id;
	private $ctrl;
	private $lng;
	private $tpl;

	/**
	 * Constructor of ilRoomSharingParticipationsGUI.
	 *
	 * @param ilRoomSharingAppointmentsGUI $a_parent_obj
	 */
	function __construct(ilRoomSharingAppointmentsGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
	}

	/**
	 * Main switch for command execution.
	 *
	 * @return bool whether the command execution was successful.
	 */
	function executeCommand()
	{
		//$this->showParticipations();
		$cmd = $this->ctrl->getCmd("showParticipations");

		if ($cmd == 'render')
		{
			$cmd = 'showParticipations';
		}

		$cmd .= 'Object';
		$this->$cmd();
		return true;
	}

	public function leaveMultipleParticipationsObject()
	{
		$bookings = new ilRoomSharingParticipations($this->pool_id);
		// the canceling has to be confirmed via a form, which is why we get the id from POST
		try
		{
			foreach ($_POST["booking_ids"] as $a_id)
			{
				$bookings->removeParticipation($a_id);
			}
		}
		catch (ilRoomSharingBookingsException $exc)
		{
			ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
			$this->showParticipationsObject();
		}
		$this->showParticipationsObject();
	}

	function confirmLeaveMultipleParticipationsObject()
	{
		$this->showConfirmLeaveDialog($_POST['participations']);
	}

	function confirmLeaveParticipationObject()
	{
		$this->showConfirmLeaveDialog(array($_GET['booking_id']));
	}

	private function showConfirmLeaveDialog(array $a_ids)
	{
		global $ilTabs;
		if (!empty($a_ids))
		{
			$ilTabs->clearTargets();
			$ilTabs->setBackTarget($this->lng->txt('rep_robj_xrs_participations_back'),
				$this->ctrl->getLinkTarget($this, 'showParticipations'));

			// create the confirmation GUI
			$confirmation = new ilConfirmationGUI();
			$confirmation->setFormAction($this->ctrl->getFormAction($this));
			$confirmation->setHeaderText($this->lng->txt('rep_robj_xrs_participations_confirm'));

			foreach ($a_ids as $num => $a_id)
			{
				$confirmation->addItem('booking_ids[' . $num . ']', $a_id, 'Test: ' . $a_id);
			}

			$confirmation->setConfirm($this->lng->txt('rep_robj_xrs_participations_confirm_leave'),
				'leaveMultipleParticipations'); // cancel the bookings
			$confirmation->setCancel($this->lng->txt('cancel'), 'showParticipations'); // cancel the confirmation dialog

			$this->tpl->setContent($confirmation->getHTML()); // display
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_participations_no_leave_ids'));
			$this->showBookingsObject();
		}
	}

	/**
	 * Show all participations.
	 */
	function showParticipationsObject()
	{
		$participationsTable = new ilRoomSharingParticipationsTableGUI($this, 'showParticipations',
			$this->ref_id);
		$this->tpl->setContent($participationsTable->getHTML());
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return int pool id
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer $a_pool_id current pool id.
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
