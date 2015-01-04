<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingPermissionUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivilegesConstants.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingTextInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
require_once("Services/User/classes/class.ilUserAutoComplete.php");

use ilRoomSharingPrivilegesConstants as PRIVC;

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
 * @property ilRoomSharingPermissionUtils $permission
 */
class ilRoomSharingBookingsTableGUI extends ilTable2GUI
{
	protected $lng;
	protected $ctrl;
	protected $parent_obj;
	private $permission;
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
		global $ilCtrl, $lng, $rssPermission;
		$this->permission = $rssPermission;
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
		$this->setRowTemplate("tpl.room_appointment_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
		// command for cancelling bookings
		if ($this->permission->checkPrivilege(PRIVC::ADD_OWN_BOOKINGS) || $this->permission->checkPrivilege(PRIVC::CANCEL_BOOKING_LOWER_PRIORITY))
		{
			$this->addMultiCommand('confirmMultipleCancels', $this->lng->txt('rep_robj_xrs_booking_cancel'));
		}
	}

	/**
	 * Initialize a search filter for ilRoomSharingRoomsTableGUI.
	 */
	public function initFilter()
	{
		$this->createUserFormItem();
		$this->createRoomFormItem();
		$this->createSubjectFormItem();
		$this->createCommentFormItem();
		$this->createAttributeFormItem();
	}

	private function createUserFormItem()
	{
		if (!$this->permission->checkPrivilege(PRIVC::SEE_NON_PUBLIC_BOOKINGS_INFORMATION))
		{
			global $ilUser;
			$this->filter ["user"] ['user_id'] = $ilUser->getId();
			return;
		}

		$user_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_user"), "user");
		$user_name_input = new ilRoomSharingTextInputGUI("", "login");
		$user_name_input->setMaxLength(14);
		$user_name_input->setSize(14);
		$ajax_datasource = $this->ctrl->getLinkTarget($this, 'doUserAutoComplete', '', true);
		$user_name_input->setDataSource($ajax_datasource);
		$user_comb->addCombinationItem("user_id", $user_name_input, $this->lng->txt("rep_robj_xrs_user_id"));
		$this->addFilterItem($user_comb);
		$user_comb->readFromSession(); // get the value that was submitted
		$this->filter ["user"] = $user_comb->getValue();
	}

	private function createRoomFormItem()
	{
		$room_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_room"), "room");
		$room_name_input = new ilRoomSharingTextInputGUI("", "room_name");
		$room_name_input->setMaxLength(14);
		$room_name_input->setSize(14);
		$room_comb->addCombinationItem("room_name", $room_name_input, $this->lng->txt("rep_robj_xrs_room_name"));
		$this->addFilterItem($room_comb);
		$room_comb->readFromSession(); // get the value that was submitted
		$this->filter ["room"] = $room_comb->getValue();
	}

	private function createSubjectFormItem()
	{
		$subject_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_subject"), "subject");
		$subject_input = new ilRoomSharingTextInputGUI("", "booking_subject");
		$subject_input->setMaxLength(14);
		$subject_input->setSize(14);
		$subject_comb->addCombinationItem("booking_subject", $subject_input, $this->lng->txt("rep_robj_xrs_subject"));
		$this->addFilterItem($subject_comb);
		$subject_comb->readFromSession(); // get the value that was submitted
		$this->filter ["subject"] = $subject_comb->getValue();
	}

	private function createCommentFormItem()
	{
		$comment_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_comment"), "comment");
		$comment_input = new ilRoomSharingTextInputGUI("", "booking_comment");
		$comment_input->setMaxLength(14);
		$comment_input->setSize(14);
		$comment_comb->addCombinationItem("booking_comment", $comment_input, $this->lng->txt("rep_robj_xrs_comment"));
		$this->addFilterItem($comment_comb);
		$comment_comb->readFromSession(); // get the value that was submitted
		$this->filter ["comment"] = $comment_comb->getValue();
	}

	private function createAttributeFormItem()
	{
		$attributes = $this->bookings->getAllAttributes();
		foreach ($attributes as $room_attribute)
		{
			// setup an ilCombinationInputGUI for the room attributes
			$room_attribute_comb = new ilCombinationInputGUI($room_attribute, "attribute_" . $room_attribute);
			$room_attribute_input = new ilRoomSharingTextInputGUI("", "attribute_" . $room_attribute . "_value");
			$room_attribute_input->setMaxLength(14);
			$room_attribute_input->setSize(14);
			$room_attribute_comb->addCombinationItem("amount", $room_attribute_input, $this->lng->txt("rep_robj_xrs_value"));

			$this->addFilterItem($room_attribute_comb);
			$room_attribute_comb->readFromSession();

			$this->filter ["attributes"] [$room_attribute] = $room_attribute_comb->getValue();
		}
	}

	/**
	 * Gets all the items that need to be populated into the table.
	 */
	public function getItems()
	{
		$data = $this->bookings->getList($this->getCurrentFilter());

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
		$this->tpl->setVariable('CHECKBOX_ID', $a_rowData['id'] . '_' . $a_rowData['subject']);

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
		$this->tpl->setVariable('IMG_RECURRENCE_TITLE', $this->lng->txt("rep_robj_xrs_room_date_recurrence"));
	}

	/**
	 * Sets appointment value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setAppointment($a_rowData)
	{
		$this->tpl->setVariable('TXT_DATE', $a_rowData ['date']);
		// link for the date overview
		if ($this->permission->checkPrivilege(PRIVC::ACCESS_APPOINTMENTS))
		{
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'booking_id', $a_rowData['id']);
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'room_id', $a_rowData['room_id']);
			$this->tpl->setVariable('HREF_DATE', $this->ctrl->getLinkTargetByClass('ilRoomSharingShowAndEditBookGUI', 'showBooking'));
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'booking_id', '');
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'room_id', '');
		}
	}

	/**
	 * Sets room values in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setRoom($a_rowData)
	{
		$this->tpl->setVariable('TXT_ROOM', $a_rowData ['room']);
		if ($this->permission->checkPrivilege(PRIVC::ACCESS_ROOMS))
		{
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_rowData ['room_id']);
			$this->tpl->setVariable('HREF_ROOM', $this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');
		}
	}

	/**
	 * Sets comment value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setComment($a_rowData)
	{
		$this->tpl->setVariable('TXT_COMMENT', ($a_rowData ['comment'] === null ? '' : $a_rowData ['comment']));
	}

	/**
	 * Sets subject value in the table row.
	 *
	 * @param array $a_rowData
	 */
	private function setSubject($a_rowData)
	{
		$this->tpl->setVariable('TXT_SUBJECT', ($a_rowData ['subject'] === null ? '' : $a_rowData ['subject']));
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
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', $a_rowData ['participants_ids'] [$i]);
			$this->tpl->setVariable('HREF_PROFILE', $this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showProfile'));
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
		if ($this->permission->checkPrivilege(PRIVC::ADD_OWN_BOOKINGS))
		{
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'booking_id', $a_rowData ['id']);
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'room_id', $a_rowData ['room_id']);
			$this->tpl->setVariable('LINK_ACTION', $this->ctrl->getLinkTargetByClass('ilRoomSharingShowAndEditBookGUI', 'editBooking'));
			$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_booking_edit'));
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'booking_id', '');
			$this->ctrl->setParameterByClass('ilRoomSharingShowAndEditBookGUI', 'room_id', '');
		}
		$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
		$this->tpl->parseCurrentBlock();


		if ($this->permission->checkPrivilege(PRIVC::ADD_OWN_BOOKINGS) || $this->permission->checkPrivilege(PRIVC::CANCEL_BOOKING_LOWER_PRIORITY))
		{
			$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', $a_rowData ['subject']);
			$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_id', $a_rowData ['id']);
			$this->tpl->setVariable('LINK_ACTION', $this->ctrl->getLinkTargetByClass('ilroomsharingbookingsgui', 'confirmCancel'));
			$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_booking_cancel'));
			$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', '');
			$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_id', '');
		}
	}

	/**
	 * Build a filter that can used for database-queries.
	 *
	 * @return array the filter
	 */
	private function getCurrentFilter()
	{
		$filter = array();
		// make sure that "0"-strings are not ignored
		if ($this->filter ["room"] ["room_name"] || $this->filter ["room"] ["room_name"] === "0")
		{
			$filter ["room_name"] = $this->filter ["room"] ["room_name"];
		}
		if ($this->filter ["subject"] ["booking_subject"] || $this->filter ["subject"] ["booking_subject"] === "0")
		{
			$filter ["subject"] = $this->filter ["subject"] ["booking_subject"];
		}
		if ($this->filter ["comment"] ["booking_comment"] || $this->filter ["comment"] ["booking_comment"] === "0")
		{
			$filter ["comment"] = $this->filter["comment"] ["booking_comment"];
		}

		if ($this->filter ["user"] ["user_id"] || $this->filter ["user"] ["user_id"] === 0.0)
		{
			$filter ["user_id"] = $this->filter ["user"] ["user_id"];
		}

		if ($this->filter ["attributes"])
		{
			foreach ($this->filter ["attributes"] as $key => $value)
			{
				if ($value ["amount"])
				{
					$filter ["attributes"] [$key] = $value ["amount"];
				}
			}
		}

		return $filter;
	}

}