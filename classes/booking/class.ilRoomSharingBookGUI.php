<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/detail/class.ilRoomSharingRoom.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBookException.php");
require_once("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/User/classes/class.ilUserAutoComplete.php");

/**
 * Class ilRoomSharingBookGUI
 *
 * @author Michael Dazjuk <mdazjuk@stud.hs-bremen.de>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *
 * @version $Id$
 */
class ilRoomSharingBookGUI
{
	private $parent_obj;
	private $pool_id;
	private $room_id;
	private $date_from;
	private $date_to;

	const NUM_PERSON_RESPONSIBLE = 1;
	const BOOK_CMD = "book";

	/**
	 * Constructur for ilRoomSharingBookGUI
	 *
	 * @param ilObjRoomSharingGUI $a_parent_obj
	 * @param string $a_room_id
	 * @param string $a_date_from
	 * @param string $a_date_to
	 */
	public function __construct(ilObjRoomSharingGUI $a_parent_obj, $a_room_id = null,
		$a_date_from = "", $a_date_to = "")
	{
		global $ilCtrl, $lng, $tpl;

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->room_id = $a_room_id;
		$this->date_from = $a_date_from;
		$this->date_to = $a_date_to;
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
	 * Renders the booking form as HTML.
	 */
	public function renderBookingForm()
	{
		$booking_form = $this->createForm();
		$this->tpl->setContent($booking_form->getHTML());
	}

	/**
	 * Creates a booking form.
	 *
	 * @return ilform
	 */
	private function createForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->getFormTitle());
		$form->addCommandButton(self::BOOK_CMD, $this->lng->txt("rep_robj_xrs_room_book"));

		$form_items = $this->createFormItems();
		foreach ($form_items as $item)
		{
			$form->addItem($item);
		}

		return $form;
	}

	private function getFormTitle()
	{
		$title = $this->lng->txt('rep_robj_xrs_room_book') . ': ' . $this->lng->txt('rep_robj_xrs_room');
		$title = $title . " " . $this->getRoomFromId();

		return $title;
	}

	private function getRoomFromId()
	{
		$room_id = empty($this->room_id) ? $_POST['room_id'] : $this->room_id;
		$this->room_id = $room_id;

		$rooms = new ilRoomSharingRooms();
		return $rooms->getRoomName($room_id);
	}

	private function createFormItems()
	{
		$form_items = array();
		$form_items[] = $this->createSubjectTextInput();
		$booking_attributes = $this->createBookingAttributeTextInputs();
		$form_items = array_merge($form_items, $booking_attributes);
		$form_items[] = $this->createTimeRangeInput();
		$form_items[] = $this->createPublicBookingCheckBox();
		$form_items[] = $this->createUserAgreementCheckBox();
		$form_items[] = $this->createRoomIdHiddenInputField();
		$form_items[] = $this->createParticipantsSection();
		$form_items[] = $this->createParticipantsMultiTextInput();

		return $form_items;
	}

	private function createSubjectTextInput()
	{
		$subject = new ilTextInputGUI($this->lng->txt("subject"), "subject");
		$subject->setRequired(true);
		$subject->setSize(40);
		$subject->setMaxLength(120);

		return $subject;
	}

	private function createBookingAttributeTextInputs()
	{
		$text_input_items = array();
		$booking_attributes = $this->getBookingAttributes();
		foreach ($booking_attributes as $attr)
		{
			$text_input_items[] = $this->createSingleBookingAttributeTextInput($attr);
		}
		return $text_input_items;
	}

	private function getBookingAttributes()
	{
		$ilBookings = new ilRoomSharingBookings();
		$ilBookings->setPoolId($this->pool_id);
		return $ilBookings->getAdditionalBookingInfos();
	}

	private function createSingleBookingAttributeTextInput($a_attribute)
	{
		$attr = new ilTextInputGUI($a_attribute['txt'], $a_attribute['id']);
		$attr->setSize(40);
		$attr->setMaxLength(120);

		return $attr;
	}

	private function createTimeRangeInput()
	{
		$time_range = new ilCombinationInputGUI($this->lng->txt("assessment_log_datetime"), "time_range");

		$from_id = "from";
		$to_id = "to";
		$from_transl = $this->lng->txt($from_id);
		$to_transl = $this->lng->txt($to_id);

		$time_input_from = $this->createDateTimeInput($from_transl, $from_id, $this->date_from);
		$time_input_to = $this->createDateTimeInput($to_transl, $to_id, $this->date_to);

		$time_range->addCombinationItem($from_id, $time_input_from, $from_transl);
		$time_range->addCombinationItem($to_id, $time_input_to, $to_transl);

		return $time_range;
	}

	private function createDateTimeInput($a_title, $a_postvar, $a_date)
	{
		$date_time_input = new ilDateTimeInputGUI($a_title, $a_postvar);
		if (isset($a_date))
		{
			$date_time_input->setDate(new ilDateTime($a_date, IL_CAL_DATETIME));
		}
		$date_time_input->setMinuteStepSize(5);
		$date_time_input->setShowTime(true);

		return $date_time_input;
	}

	private function createPublicBookingCheckBox()
	{
		$checkbox_public = new ilCheckboxInputGUI($this->lng->txt("rep_robj_xrs_room_public_booking"),
			"book_public");

		return $checkbox_public;
	}

	private function createUserAgreementCheckBox()
	{
		$checkbox_agreement = new ilCheckboxInputGUI($this->lng->txt("rep_robj_xrs_room_user_agreement"),
			"accept_room_rules");
		$checkbox_agreement->setRequired(true);

		return $checkbox_agreement;
	}

	private function createRoomIdHiddenInputField()
	{
		$hidden_room_id = new ilHiddenInputGUI("room_id");
		$hidden_room_id->setValue($this->room_id);
		$hidden_room_id->setRequired(true);

		return $hidden_room_id;
	}

	private function createParticipantsMultiTextInput()
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
	 * Function that performs the booking procedure.
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
		return $a_form->checkInput() && $a_form->getInput('accept_room_rules') == 1;
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
		$book = new ilRoomSharingBook();
		$book->setPoolId($this->getPoolId());
		$book->addBooking($a_common_entries, $a_attribute_entries, $a_participant_entries);
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
