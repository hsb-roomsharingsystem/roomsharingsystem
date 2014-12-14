<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/detail/class.ilRoomSharingRoom.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingBookException.php");
require_once("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/User/classes/class.ilUserAutoComplete.php");
include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * Class ilRoomSharingShowAndEditBookGUI
 *
 * @author Michael Dazjuk <mdazjuk@stud.hs-bremen.de>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 */
class ilRoomSharingShowAndEditBookGUI
{
	private $parent_obj;
	private $pool_id;
	private $room_id;
	private $date_from;
	private $date_to;
	private $book;

	const NUM_PERSON_RESPONSIBLE = 1;
	const EDIT_BOOK_CMD = "editBook";
	const SAVE_BOOK_CMD = "saveEditBook";

	/**
	 * Constructur for ilRoomSharingBookGUI
	 *
	 * @param ilObjRoomSharingGUI $a_parent_obj
	 * @param string $a_room_id
	 * @param string $a_date_from
	 * @param string $a_date_to
	 */
	public function __construct($a_parent_obj, $a_booking_id)
	{
		global $ilCtrl, $lng, $tpl;

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->booking_id = $a_booking_id;

		$this->book = new ilRoomSharingBook($this->pool_id);
	}

	/**
	 * Executes the command given by ilCtrl.
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd("renderBookingForm");
		$this->$cmd();
	}

	/**
	 *
	 * Renders the booking form as HTML.
	 *
	 * @param type $mode default is 'create'
	 * 				possibles modes {'create',''edit', 'show'}
	 * @param type $a_booking_id
	 */
	public function renderBookingForm($mode = 'show', $a_booking_id = null)
	{
		$booking_form = $this->createForm($mode, $a_booking_id);
		$this->tpl->setContent($booking_form->getHTML());
	}

	/**
	 * Creates a booking form.
	 *
	 * @return ilform
	 */
	private function createForm($a_booking_id, $mode = 'show')
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		if ($mode == 'show')
		{
			$form->setTitle($this->getFormTitle($mode));
			$form->addCommandButton(self::EDIT_BOOK_CMD, $this->lng->txt("rep_robj_xrs_booking_edit"));

			$form_items = $this->createAndSetFormItems($mode, $a_booking_id);
			foreach ($form_items as $item)
			{
				$form->addItem($item);
			}
		}
		elseif ($mode == 'edit')
		{
			$form->setTitle($this->getFormTitle($mode));
			$form->addCommandButton(self::SAVE_BOOK_CMD, $this->lng->txt("rep_robj_xrs_booking_save"));

			$form_items = $this->createAndSetFormItems($mode, $a_booking_id);
			foreach ($form_items as $item)
			{
				$form->addItem($item);
			}
		}
		return $form;
	}

	private function getFormTitle($mode = 'create')
	{
		$title = '';
		if ($mode == 'show')
		{
			$title = $title . $this->lng->txt('rep_robj_xrs_booking_in show');
		}
		elseif ($mode == 'edit')
		{
			$title = $title . $this->lng->txt('rep_robj_xrs_booking_in_edit');
		}

		return $title;
	}

	private function createAndSetFormItems($mode, $a_booking_id)
	{
		$booking = new ilRoomSharingBookings($this->pool_id);
		$bookingData = array();
		$bookingData = $booking->getBookingData($a_booking_id);
		$form_items = array();
		$form_items[] = $this->createAndSetSubjectTextInput($mode, $bookingData['booking_values']);
		$form_items[] = $this->createAndSetCommentTextInput($mode, $bookingData['booking_values']);
		$booking_attributes = $this->createAndSetBookingAttributeTextInputs($mode,
			$bookingData['attr_values']);
		$form_items = array_merge($form_items, $booking_attributes);
		$form_items[] = $this->createAndSetTimeRangeInput($mode, $bookingData['booking_values']);
		$form_items[] = $this->createAndSetPublicBookingCheckBox($mode, $bookingData['booking_values']);
		$form_items[] = $this->createAndSetUserAgreementCheckBoxIfPossible();
		$form_items[] = $this->createRoomIdHiddenInputField();
		$form_items[] = $this->createParticipantsSection();
		$form_items[] = $this->createAndSetParticipantsMultiTextInput($mode, $bookingData['participants']);

		return array_filter($form_items);
	}

	private function createAndSetCommentTextInput($mode, $bookingData)
	{
		$comment = new ilTextInputGUI($this->lng->txt("comment"), "comment");
		$comment->setRequired(false);
		$comment->setSize(40);
		$comment->setMaxLength(4000);
		$comment->setValue($bookingData['comment']);
		if ($mode == 'show')
		{
			$comment->setDisabled(true);
		}

		return $comment;
	}

	private function createAndSetSubjectTextInput($mode, $bookingData)
	{
		$subject = new ilTextInputGUI($this->lng->txt("subject"), "subject");
		$subject->setRequired(true);
		$subject->setSize(40);
		$subject->setMaxLength(120);
		$subject->setValue($bookingData['subject']);
		if ($mode == 'show')
		{
			$subject->setDisabled(true);
		}

		return $subject;
	}

	private function createAndSetBookingAttributeTextInputs($mode, $bookingData)
	{
		$text_input_items = array();
		$booking_attributes = $this->getBookingAttributes();
		foreach ($booking_attributes as $attr)
		{
			$text_input_items[] = $this->createSingleBookingAttributeTextInput($attr, $mode, $bookingData);
		}
		return $text_input_items;
	}

	private function getBookingAttributes()
	{
		$ilBookings = new ilRoomSharingBookings();
		$ilBookings->setPoolId($this->pool_id);
		return $ilBookings->getAdditionalBookingInfos();
	}

	private function createSingleBookingAttributeTextInput($a_attribute, $mode, $a_bookingdata)
	{
		$attr_id = $a_attribute['id'];
		$attr_txt = $a_attribute['txt'];
		$attr = new ilTextInputGUI($attr_txt, $attr_id);
		$attr->setSize(40);
		$attr->setMaxLength(120);
		$attr->setValue($a_bookingdata[$attr_id][value]);
		if ($mode == 'show')
		{
			$attr->setDisabled(true);
		}

		return $attr;
	}

	private function createAndSetTimeRangeInput($mode, $a_bookingData)
	{
		$time_range = new ilCombinationInputGUI($this->lng->txt("assessment_log_datetime"), "time_range");

		$from_id = "from";
		$to_id = "to";
		$from_transl = $this->lng->txt($from_id);
		$to_transl = $this->lng->txt($to_id);

		$time_input_from = $this->createAndSetDateTimeInput($from_transl, $from_id, $this->date_from,
			$a_bookingData['date_from']);
		$time_input_to = $this->createAndSetDateTimeInput($to_transl, $to_id, $this->date_to,
			$a_bookingData['date_to'], $mode);

		$time_range->addCombinationItem($from_id, $time_input_from, $from_transl);
		$time_range->addCombinationItem($to_id, $time_input_to, $to_transl);

		return $time_range;
	}

	private function createAndSetDateTimeInput($a_title, $a_postvar, $a_date, $a_value, $mode)
	{
		$date_time_input = new ilDateTimeInputGUI($a_title, $a_postvar);
		if (isset($a_date))
		{
			$date_time_input->setDate(new ilDateTime($a_date, IL_CAL_DATETIME));
		}
		$date_time_input->setMinuteStepSize(5);
		$date_time_input->setShowTime(true);
		$date_time_input->setValueByArray($a_value);
		if ($mode == 'show')
		{
			$date_time_input->setDisabled(true);
		}

		return $date_time_input;
	}

	private function createAndSetPublicBookingCheckBox($mode, $a_bookingData)
	{
		$checkbox_public = new ilCheckboxInputGUI($this->lng->txt("rep_robj_xrs_room_public_booking"),
			"book_public");
		$checkbox_public->setValue($a_bookingData['book_public']);
		if ($mode == 'show')
		{
			$checkbox_public->setDisabled(true);
		}

		return $checkbox_public;
	}

	private function createAndSetUserAgreementCheckBoxIfPossible()
	{
		if ($this->isRoomAgreementIdAvailable())
		{
			return $this->createUserAgreementCheckBox();
		}
	}

	private function isRoomAgreementIdAvailable()
	{
		$agreement_id = $this->book->getRoomAgreementFileId();

		return !empty($agreement_id);
	}

	private function createUserAgreementCheckBox()
	{
		$agreement_id = $this->book->getRoomAgreementFileId();
		$link = $this->getFileLinkForUserAgreementId($agreement_id);
		$title = $this->lng->txt("rep_robj_xrs_rooms_user_agreement_accept");
		$checkbox_agreement = new ilCheckboxInputGUI($title, "accept_room_rules");
		$checkbox_agreement->setRequired(true);
		$checkbox_agreement->setOptionTitle($link);
		$checkbox_agreement->setValue(true);
		$checkbox_agreement->setDisabled(true);

		return $checkbox_agreement;
	}

	private function getFileLinkForUserAgreementId($a_file_id)
	{
		$agreement_file = new ilObjMediaObject($a_file_id);
		$media = $agreement_file->getMediaItem("Agreement");
		$source = $agreement_file->getDataDirectory() . "/" . $media->getLocation();

		$link = "<p> <a target=\"_blank\" href=\"" . $source . "\">" .
			$this->lng->txt('rep_robj_xrs_current_rooms_user_agreement') . "</a></p>";
		return $link;
	}

	private function createRoomIdHiddenInputField()
	{
		$hidden_room_id = new ilHiddenInputGUI("room_id");
		$hidden_room_id->setValue($this->room_id);
		$hidden_room_id->setRequired(true);

		return $hidden_room_id;
	}

	private function createAndSetParticipantsMultiTextInput($mode, $a_bookingData)
	{
		$participants_input = new ilTextInputGUI($this->lng->txt("rep_robj_xrs_participants_list"),
			"participants");
		$participants_input->setMulti(true);
		$ajax_datasource = $this->ctrl->getLinkTarget($this, 'doUserAutoComplete', '', true);
		$participants_input->setDataSource($ajax_datasource);
		$participants_input->setInfo($this->getMaxRoomAllocationInfo());

		return $participants_input;
	}

	/**
	 * Method that realizes the auto-completion for the participants list.
	 */
	private function doUserAutoComplete()
	{
		$search_fields = array("login", "firstname", "lastname", "email");
		$result_field = "login";

		$auto = new ilUserAutoComplete();
		$auto->setSearchFields($search_fields);
		$auto->setResultField($result_field);
		$auto->enableFieldSearchableCheck(true);

		echo $auto->getList($_REQUEST['term']);
		exit();
	}

	private function getMaxRoomAllocationInfo()
	{
		$room = new ilRoomSharingRoom($this->pool_id, $this->room_id);
		$max_alloc = $this->lng->txt("rep_robj_xrs_at_most") . ": " . ($room->getMaxAlloc() - self::NUM_PERSON_RESPONSIBLE);

		return $max_alloc;
	}

	private function createParticipantsSection()
	{
		$participant_section = new ilFormSectionHeaderGUI();
		$participant_section->setTitle($this->lng->txt("rep_robj_xrs_participants"));

		return $participant_section;
	}

	/**
	 * Function to the validate and save the form data
	 *
	 * @global type $ilTabs
	 */
	private function book()
	{
		$form = $this->createForm();
		if ($this->isFormValid($form))
		{
			$this->evaluateFormEntries($form);
		}
		else
		{
			$this->handleInvalidForm($form);
		}
	}

	private function isFormValid($a_form)
	{
		return $a_form->checkInput() && (!$this->isRoomAgreementIdAvailable() ||
			$a_form->getInput('accept_room_rules') == 1);
	}

	private function evaluateFormEntries($a_form)
	{
		$common_entries = $this->fetchCommonFormEntries($a_form);
		$attribute_entries = $this->fetchAttributeFormEntries($a_form);
		$participant_entries = $a_form->getInput('participants');

		$this->saveFormEntries($a_form, $common_entries, $attribute_entries, $participant_entries);
	}

	private function fetchCommonFormEntries($a_form)
	{
		$common_entries = array();
		$common_entries['subject'] = $a_form->getInput('subject');
		$common_entries['from'] = $a_form->getInput('from');
		$common_entries['to'] = $a_form->getInput('to');
		$common_entries['book_public'] = $a_form->getInput('book_public');
		$common_entries['accept_room_rules'] = $a_form->getInput('accept_room_rules');
		$common_entries['room'] = $a_form->getInput('room_id');
		$common_entries['comment'] = $a_form->getInput('comment');

		return $common_entries;
	}

	private function fetchAttributeFormEntries($a_form)
	{
		$attribute_entries = array();
		$booking_attributes = $this->getBookingAttributes();
		foreach ($booking_attributes as $attr)
		{
			$attribute_entries[$attr['id']] = $a_form->getInput($attr['id']);
		}

		return $attribute_entries;
	}

	private function saveFormEntries($a_form, $a_common_entries, $a_attribute_entries,
		$a_participant_entries)
	{
		try
		{
			$this->addBooking($a_common_entries, $a_attribute_entries, $a_participant_entries);
		}
		catch (ilRoomSharingBookException $ex)
		{
			$this->handleException($a_form, $ex);
		}
	}

	private function addBooking($a_common_entries, $a_attribute_entries, $a_participant_entries)
	{
		//adds current calendar-id to booking information
		$a_common_entries['cal_id'] = $this->parent_obj->getCalendarId();
		$this->book->addBooking($a_common_entries, $a_attribute_entries, $a_participant_entries);
		$this->cleanUpAfterSuccessfulSave();
	}

	private function handleException($a_form, $a_exception)
	{
		ilUtil::sendFailure($a_exception->getMessage(), true);
		$this->resetInvalidForm($a_form);
	}

	private function cleanUpAfterSuccessfulSave()
	{
		global $ilTabs;

		$ilTabs->clearTargets();
		$this->parent_obj->setTabs();
		$this->ctrl->setCmd("render");
		$this->parent_obj->performCommand("");
		ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_booking_added'), true);
	}

	private function handleInvalidForm($a_form)
	{
		ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_missing_required_entries'), true);
		$this->resetInvalidForm($a_form);
	}

	private function resetInvalidForm($a_form)
	{
		$a_form->setValuesByPost();
		$this->tpl->setContent($a_form->getHTML());
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return integer Pool-ID
	 */
	public function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
