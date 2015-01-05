<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/participations/class.ilRoomSharingParticipations.php");

/**
 * Class ilRoomSharingParticipationsTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Bernd Hitzelberger <bhitzelberger@stud.hs-bremen.de>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 *
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 * @property ilRoomSharingParticipations $participations
 * @property ilRoomSharingAppointmentsGUI $parent_obj
 *
 */
class ilRoomSharingParticipationsTableGUI extends ilTable2GUI
{
	protected $participations;
	protected $tpl;
	protected $lng;
	protected $parent_obj;
	private $ctrl;
	private $ref_id;

	/**
	 * Constructor of ilRoomSharingParticipationsTableGUI.
	 *
	 * @param object $a_parent_obj parent object.
	 * @param object $a_parent_cmd parent command.
	 * @param object $a_ref_id reference id.
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng;

		$this->parent_obj = $a_parent_obj;
		$this->lng = $lng;

		$this->ctrl = $ilCtrl;
		$this->ref_id = $a_ref_id;
		$this->setId("roomobj");

		$this->participations = new ilRoomSharingParticipations(
			$a_parent_obj->getPoolId());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("rep_robj_xrs_participations"));
		$this->setLimit(10); // data sets per page
		$this->setFormAction(
			$ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->addColumns(); // add columns and column headings
		// checkboxes labeled with "participations" get
		// affected by the "Select All"-Checkbox
		$this->setSelectAllCheckbox('participations');
		$this->setRowTemplate("tpl.room_appointment_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");
		// command for leaving
		$this->addMultiCommand('confirmLeaveMultipleParticipations', $this->lng->txt('rep_robj_xrs_leave'));

		$this->getItems();
	}

	/**
	 * Gets all participations for representation.
	 */
	private function getItems()
	{
		$participationList = $this->participations->getList();

		$this->setMaxCount(count($participationList));
		$this->setData($participationList);
	}

	/**
	 * Adds columns with translations.
	 */
	private function addColumns()
	{
		$this->addColumn('', 'f', '1'); // checkboxes
		$this->addColumn('', 'f', '1'); // icons
		$this->addColumn($this->lng->txt("rep_robj_xrs_date"), "date");
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_subject"), "subject");
		$this->addColumn($this->lng->txt("rep_robj_xrs_person_responsible"), "person_responsible");

		// Add the selected optional columns to the table
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($c, $c);
		}
		$this->addColumn($this->lng->txt(''), 'optional');
	}

	/**
	 * Fills each row with given data.
	 *
	 * @param array $a_rowData with data to be filled in.
	 */
	public function fillRow($a_rowData)
	{
		// Checkbox-Name must be the same which was set in setSelectAllCheckbox.
		$this->tpl->setVariable('CHECKBOX_NAME', 'participations');
		$this->tpl->setVariable('CHECKBOX_ID', $a_rowData['booking_id'] . '_' . $a_rowData['subject']);

		$this->setRecurrence($a_rowData);

		$this->setAppointment($a_rowData);

		$this->setRoom($a_rowData);

		$this->setSubject($a_rowData);

		$this->setResponsible($a_rowData);

		$this->setAdditionalItems($a_rowData);

		$this->setActions($a_rowData);

		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Sets recurrence value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setRecurrence($a_rowData)
	{
		if ($a_rowData['recurrence'])
		{
			// Picture for recurrent appointment.
			$this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
		}
		$this->tpl->setVariable('IMG_RECURRENCE_TITLE', $this->lng->txt("rep_robj_xrs_room_date_recurrence"));
	}

	/**
	 * Sets appointment value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setAppointment($a_rowData)
	{
		$this->tpl->setVariable('TXT_DATE', $a_rowData['date']);
	}

	/**
	 * Sets room values in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setRoom($a_rowData)
	{
		$this->tpl->setVariable('TXT_ROOM', $a_rowData['room']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_rowData['room_id']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'last_cmd', 'showParticipations');
		$this->tpl->setVariable('HREF_ROOM', $this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');
	}

	/**
	 * Sets subject value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setSubject($a_rowData)
	{
		$this->tpl->setVariable('TXT_SUBJECT', ($a_rowData['subject'] == null ? '' : $a_rowData['subject']));
	}

	/**
	 * Sets responsible value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setResponsible($a_rowData)
	{
		$this->tpl->setVariable('TXT_USER', $a_rowData['person_responsible']);
		// put together a link for the profile view
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', $a_rowData['person_responsible_id']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'last_cmd', 'showParticipations');
		$this->tpl->setVariable('HREF_PROFILE', $this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showProfile'));
		// unset the parameter for safety purposes
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', '');
	}

	/**
	 * Sets additional values in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setAdditionalItems($a_rowData)
	{
		// Populate the selected additional table cells
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->tpl->setCurrentBlock("additional");
			$this->tpl->setVariable("TXT_ADDITIONAL", $a_rowData[$c] === null ? "" : $a_rowData[$c]);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * Sets action parameters in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setActions($a_rowData)
	{
		$this->ctrl->setParameterByClass('ilroomsharingparticipationsgui', 'booking_id', $a_rowData ['booking_id']);
		$this->ctrl->setParameterByClass('ilroomsharingparticipationsgui', 'booking_subject', $a_rowData ['subject']);
		$this->tpl->setVariable('LINK_ACTION', $this->ctrl->getLinkTarget($this->parent_obj, 'confirmLeaveParticipation'));
		$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_leave'));
	}

	/**
	 * Can be used to add additional columns to the participations table.
	 *
	 * @return array See
	 *         ilRoomSharingParticipations::getAdditionalBookingInfos().
	 */
	public function getSelectableColumns()
	{
		return $this->participations->getAdditionalBookingInfos();
	}

}
?>
