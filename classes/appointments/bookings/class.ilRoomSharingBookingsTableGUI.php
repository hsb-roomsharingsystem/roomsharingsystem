<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/" .
	"RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");

/**
 * Class ilRoomSharingBookingsTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 *
 * @property ilLanguage $lng
 * @property ilCtrl $ctrl
 * @property ilRoomSharingBookings $bookings
 * @property ilRoomSharingAppointmentsGUI $parent_obj
 */
class ilRoomSharingBookingsTableGUI extends ilTable2GUI
{
	protected $lng;
	protected $ctrl;
	protected $parent_obj;
	private $bookings;
	private $ref_id;

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param integer $a_ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;

		$this->parent_obj = $a_parent_obj;
		$this->ref_id = $a_ref_id;
		$this->setId("roomobj");

		$this->bookings = new ilRoomSharingBookings($a_parent_obj->getPoolId());
		$this->bookings->setPoolId($a_parent_obj->getPoolId());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("rep_robj_xrs_bookings"));
		$this->setLimit(10); // data sets per page
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		// add columns and column headings
		$this->addColumns();

		// checkboxes labeled with "bookings" get affected by the "Select All"-Checkbox
		$this->setSelectAllCheckbox('bookings');
		$this->setRowTemplate("tpl.room_appointment_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
		// command for cancelling bookings
		$this->addMultiCommand('showBookings', $this->lng->txt('rep_robj_xrs_booking_cancel'));

		$this->getItems();
	}

	/**
	 * Gets all the items that need to be populated into the table.
	 */
	public function getItems()
	{
		$data = $this->bookings->getList();

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	/**
	 * Adds columns and column headings to the table.
	 */
	private function addColumns()
	{
		$this->addColumn('', 'f', '1'); // checkboxes
		$this->addColumn('', 'f', '1'); // icons
		$this->addColumn($this->lng->txt("rep_robj_xrs_date"), "date");
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_subject"), "subject");
		$this->addColumn($this->lng->txt("rep_robj_xrs_participants"), "participants");
		$this->addColumn($this->lng->txt("comment"), "comment");

		// Add the selected optional columns to the table
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($c, $c);
		}
		$this->addColumn($this->lng->txt(''), 'optional');
	}

	/**
	 * Fills an entire table row with the given set.
	 *
	 * (non-PHPdoc)
	 * @see ilTable2GUI::fillRow()
	 * @param $a_rowData data set for that row
	 */
	public function fillRow($a_rowData)
	{
		// the "CHECKBOX_NAME" has to match with the label set in the
		// setSelectAllCheckbox()-function in order to be affected when the
		// "Select All" Checkbox is checked
		$this->tpl->setVariable('CHECKBOX_NAME', 'bookings');

		$this->setRecurrence($a_rowData);

		$this->setAppointment($a_rowData);

		$this->setRoom($a_rowData);

		$this->setSubject($a_rowData);

		$this->setParticipants($a_rowData);

		$this->setComment($a_rowData);

		$this->setAdditionalItems($a_rowData);

		$this->setActions($a_rowData);

		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Can be used to add additional columns to the bookings table.
	 *
	 * (non-PHPdoc)
	 * @see ilTable2GUI::getSelectableColumns()
	 * @return additional information for bookings
	 */
	public function getSelectableColumns()
	{
		return $this->bookings->getAdditionalBookingInfos();
	}

	/**
	 * Sets recurrence value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setRecurrence($a_rowData)
	{
		if ($a_rowData ['recurrence'])
		{
			// icon for the recurrence date
			$this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
		}
		$this->tpl->setVariable('IMG_RECURRENCE_TITLE',
			$this->lng->txt("rep_robj_xrs_room_date_recurrence"));
	}

	/**
	 * Sets appointment value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setAppointment($a_rowData)
	{
		// ### Appointment ###
		$this->tpl->setVariable('TXT_DATE', $a_rowData ['date']);
		// link for the date overview
		// $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'booking_id', $a_set['id']);
		// $this->tpl->setVariable('HREF_DATE', $this->ctrl->getLinkTargetByClass(
		// 'ilobjroomsharinggui', 'showBooking'));
		// $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'booking_id', '');
	}

	/**
	 * Sets room values in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setRoom($a_rowData)
	{
		// ### Room ###
		$this->tpl->setVariable('TXT_ROOM', $a_rowData ['room']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_rowData ['room_id']);
		$this->tpl->setVariable('HREF_ROOM',
			$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');
	}

	/**
	 * Sets comment value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setComment($a_rowData)
	{
		$this->tpl->setVariable('TXT_COMMENT',
			($a_rowData ['comment'] === null ? '' : $a_rowData ['comment']));
	}

	/**
	 * Sets subject value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setSubject($a_rowData)
	{
		$this->tpl->setVariable('TXT_SUBJECT',
			($a_rowData ['subject'] === null ? '' : $a_rowData ['subject']));
	}

	/**
	 * Sets participants value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setParticipants($a_rowData)
	{
		$participant_count = count($a_rowData ['participants']);
		for ($i = 0; $i < $participant_count; ++$i)
		{
			$this->tpl->setCurrentBlock("participants");
			$this->tpl->setVariable("TXT_USER", $a_rowData ['participants'] [$i]);

			// put together a link for the user profile view
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id',
				$a_rowData ['participants_ids'] [$i]);
			$this->tpl->setVariable('HREF_PROFILE',
				$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showProfile'));
			// unset the parameter for safety purposes
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', '');

			if ($i < $participant_count - 1)
			{
				$this->tpl->setVariable('TXT_SEPARATOR', ',');
			}
			$this->tpl->parseCurrentBlock();
		}
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
			$this->tpl->setVariable("TXT_ADDITIONAL", $a_rowData [$c] === null ? "" : $a_rowData [$c]);
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
		$this->tpl->setCurrentBlock("actions");
		$this->tpl->setVariable('LINK_ACTION',
			$this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
		$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('edit'));
		$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
		$this->tpl->parseCurrentBlock();

		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_id', $a_rowData ['id']);
		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject',
			$a_rowData ['subject']);
		$this->tpl->setVariable('LINK_ACTION',
			$this->ctrl->getLinkTargetByClass('ilroomsharingbookingsgui', 'confirmCancel'));
		$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_booking_cancel'));
		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_id', '');
		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', '');
	}

}
