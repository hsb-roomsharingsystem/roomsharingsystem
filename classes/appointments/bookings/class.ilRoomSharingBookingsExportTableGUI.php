<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author albert
 */
class ilRoomSharingBookingsExportTableGUI extends ilTable2GUI
{
	protected $bookings;
	protected $pool_id;

	/**
	 * Constructor
	 *
	 * @param unknown $a_parent_obj
	 * @param unknown $a_parent_cmd
	 * @param unknown $a_ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng;

		$this->parent_obj = $a_parent_obj;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->ref_id = $a_ref_id;
		$this->setId("roomobj");

		include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/' .
			'RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php';
		$this->bookings = new ilRoomSharingBookings($a_parent_obj->getPoolId());
		$this->bookings->setPoolId($a_parent_obj->getPoolId());
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("rep_robj_xrs_bookings"));
		$this->setLimit(10); // data sets per page
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		// add columns and column headings
		$this->_addColumns();

		// checkboxes labeled with "bookings" get affected by the "Select All"-Checkbox
		//$this->setSelectAllCheckbox('bookings');
		$this->setRowTemplate("tpl.room_appointment_export_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
		// command for cancelling bookings
		//$this->addMultiCommand('showBookings', $this->lng->txt('rep_robj_xrs_booking_cancel'));
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
	private function _addColumns()
	{
		//$this->addColumn('', 'f', '1'); // checkboxes
		$this->addColumn('', 'f', '1'); // icons
		$this->addColumn($this->lng->txt("rep_robj_xrs_date"));
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"));
		$this->addColumn($this->lng->txt("rep_robj_xrs_subject"));
		$this->addColumn($this->lng->txt("rep_robj_xrs_participants"));

		// Add the selected optional columns to the table
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($c);
		}
		$this->addColumn($this->lng->txt(''), 'optional');
	}

	/**
	 * Fills an entire table row with the given set.
	 *
	 * (non-PHPdoc)
	 * @see ilTable2GUI::fillRow()
	 * @param $a_set data set for that row
	 */
	public function fillRow($a_set)
	{
		// the "CHECKBOX_NAME" has to match with the label set in the
		// setSelectAllCheckbox()-function in order to be affected when the
		// "Select All" Checkbox is checked
		//Auskommentiert, weil checkbox?
		//$this->tpl->setVariable('CHECKBOX_NAME', 'bookings');
		// ### Recurrence ###
		if ($a_set ['recurrence'])
		{
			// icon for the recurrence date
			$this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
		}
		//$this->tpl->setVariable('IMG_RECURRENCE_TITLE',
		//	$this->lng->txt("rep_robj_xrs_room_date_recurrence"));
		// ### Appointment ###
		$this->tpl->setVariable('TXT_DATE', $a_set ['date']);
		// link for the date overview
		// $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'booking_id', $a_set['id']);
		// $this->tpl->setVariable('HREF_DATE', $this->ctrl->getLinkTargetByClass(
		// 'ilobjroomsharinggui', 'showBooking'));
		// $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'booking_id', '');
		// ### Room ###
		$this->tpl->setVariable('TXT_ROOM', $a_set ['room']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_set ['room_id']);
		//$this->tpl->setVariable('HREF_ROOM',
		//	$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');

		$this->tpl->setVariable('TXT_SUBJECT', ($a_set ['subject'] === null ? '' : $a_set ['subject']));

		// ### Participants ###
		$participant_count = count($a_set ['participants']);
		for ($i = 0; $i < $participant_count; ++$i)
		{
			$this->tpl->setCurrentBlock("participants");
			$this->tpl->setVariable("TXT_USER", $a_set ['participants'] [$i]);

			// put together a link for the user profile view
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id',
				$a_set ['participants_ids'] [$i]);
			//$this->tpl->setVariable('HREF_PROFILE',
			//	$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showProfile'));
			// unset the parameter for safety purposes
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', '');

			if ($i < $participant_count - 1)
			{
				$this->tpl->setVariable('TXT_SEPARATOR', ',');
			}
			$this->tpl->parseCurrentBlock();
		}

		// Populate the selected additional table cells
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->tpl->setCurrentBlock("additional");
			$this->tpl->setVariable("TXT_ADDITIONAL", $a_set [$c] === null ? "" : $a_set [$c]);
			$this->tpl->parseCurrentBlock();
		}
		/*
		  // actions
		  bärbern
		  $this->tpl->setCurrentBlock("actions");
		  $this->tpl->setVariable('LINK_ACTION',
		  $this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
		  $this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('edit'));
		  $this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
		  $this->tpl->parseCurrentBlock();
		 */
		/*
		  $this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_id', $a_set ['id']);
		  $this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', $a_set ['subject']);
		  $this->tpl->setVariable('LINK_ACTION',
		  $this->ctrl->getLinkTargetByClass('ilroomsharingbookingsgui', 'confirmCancel'));
		  $this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_booking_cancel'));
		  $this->ctrl->setParameterByClass('ilroomsharingbookingssgui', 'booking_id', '');
		  $this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', '');

		  $this->tpl->parseCurrentBlock();
		 */
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

}
