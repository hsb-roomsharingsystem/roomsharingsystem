<?php

/**
 * Class ilRoomSharingBookGUI
 * @author Michael Dazjuk
 * @author Robert Heimsoth
 * @author Bernd Hitzelberger
 * @version $Id$
 */
class ilRoomSharingBookGUI
{
	protected $parent_obj;
	protected $ref_id;
	protected $pool_id;
	protected $room_id;
	protected $ilRoomSharingRooms;
	protected $date_from;
	protected $date_to;

	/**
	 * Constructur for ilRoomSharingBookGUI
	 *
	 * @param object $a_parent_obj
	 * @param coded values	$a_room_id, $a_date_from, $a_date_to
	 */
	function __construct(ilObjRoomSharingGUI $a_parent_obj, $a_room_id = null, $a_date_from = "",
		$a_date_to = "")
	{
		global $ilCtrl, $lng, $tpl;

		$this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->ref_id = $a_parent_obj->ref_id;
		$this->room_id = $a_room_id;
		$this->date_from = $a_date_from;
		$this->date_to = $a_date_to;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;

		// Get an instance of ilRoomSharingRooms which is used in more than 1 function
		include ('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php');
		$this->ilRoomSharingRooms = new ilRoomSharingRooms();
	}

	/**
	 * Main switch for command execution.
	 *
	 * @return Returns always true.
	 */
	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("render");

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
	 * Render list of booking objects
	 *
	 * uses ilBookingObjectsTableGUI
	 */
	function renderObject()
	{
		global $tpl, $ilAccess;

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		}

		include_once ('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
		$plink = new ilPermanentLinkGUI('book', $this->ref_id);

		$tpl->setContent($this->initForm()->getHTML() . $plink->getHTML());
	}

	/**
	 * Form for booking
	 *
	 * @return Returns the GUI
	 */
	function initForm()
	{
		global $lng, $ilCtrl;

		include_once ("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		// Set form frame title
		$form->setTitle($lng->txt('rep_robj_xrs_room_book') . ': ' . $lng->txt('rep_robj_xrs_room')
			. ' ' . $this->ilRoomSharingRooms->getRoomName((empty($this->room_id)) ? $_POST ['room_id'] : $this->room_id));

		// Input for the subject of the booking
		$subject = new ilTextInputGUI($lng->txt("subject"), "subject");
		$subject->setRequired(true);
		$subject->setSize(40);
		$subject->setMaxLength(120);
		$form->addItem($subject);

		$form->addCommandButton("save", $lng->txt("rep_robj_xrs_room_book"));

		// List the booking-attributes
		include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/'
			. 'classes/appointments/bookings/class.ilRoomSharingBookings.php');
		$ilBookings = new ilRoomSharingBookings();
		$ilBookings->setPoolId($this->pool_id);
		foreach ($ilBookings->getAdditionalBookingInfos() as $attr_key => $attr_value)
		{
			$formattr = new ilTextInputGUI($attr_value ['txt'], $attr_value ['id']);
			$formattr->setSize(40);
			$formattr->setMaxLength(120);
			$form->addItem($formattr);
		}

		include_once ("./Services/Form/classes/class.ilCombinationInputGUI.php");
		$time_range = new ilCombinationInputGUI($this->lng->txt("assessment_log_datetime"), "time_range");

		// Datetime Input for "Of"
		$dt_prop = new ilDateTimeInputGUI($lng->txt("of"), "from");
		if (!empty($this->date_from))
		{
			$dt_prop->setDate(new ilDateTime($this->date_from, IL_CAL_DATETIME));
			$this->date_from = "";
		}
		$dt_prop->setMinuteStepSize(5);
		$time_range->addCombinationItem("of", $dt_prop, $lng->txt("of"));
		$dt_prop->setShowTime(true);

		// Datetime Input for "To"
		$dt_prop1 = new ilDateTimeInputGUI($lng->txt("to"), "to");
		if (!empty($this->date_to))
		{
			$dt_prop1->setDate(new ilDateTime($this->date_to, IL_CAL_DATETIME));
			$this->date_to = "";
		}
		$dt_prop1->setMinuteStepSize(5);
		$time_range->addCombinationItem("to", $dt_prop1, $lng->txt("to"));
		$dt_prop1->setShowTime(true);
		$form->addItem($time_range);

		// checkbox to make username public
		$cb_pub = new ilCheckboxInputGUI($lng->txt("rep_robj_xrs_room_public_booking"), "book_public");
		$cb_pub->setValue("1");
		$cb_pub->setChecked(false);
		$cb_pub->setRequired(false);
		$form->addItem($cb_pub);

		// checkbox to confirm the room use agreement
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php");
		$RoomAgreement = new ilRoomSharingBook();
		$RoomAgreement->setPoolId($this->getPoolId());
		$RoomAgreementInfo = $RoomAgreement->getRoomAgreement();
		if ($RoomAgreementInfo['rooms_agreement'] !== '0')
		{
			$path = $this->getPfad($RoomAgreementInfo);
			$msg = $lng->txt("rep_robj_xrs_room_user_agreement");
			$cb_prop = new ilCheckboxInputGUI($msg, "accept_room_rules");

			$cb_prop->setValue("1");
			$cb_prop->setChecked(false);
			$cb_prop->setRequired(true);
			$form->addItem($cb_prop);
		}

		// Save room-id in a hidden input field
		$room_id_prop = new ilHiddenInputGUI("room_id");
		$room_id_prop->setValue($this->room_id);
		$room_id_prop->setRequired(true);
		$form->addItem($room_id_prop);

		// checkbox to confirm the room use agreement
		include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBookInputGUI.php';

		return $form;
	}

	private function getPfad($a_set)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobj = new ilObjMediaObject($a_set['file_id']);
		$med = $mobj->getMediaItem("Standard");
		//$title = $med->getLocation();
		$Pfad = ilUtil::getWebspaceDir() . "/mobs/mm_" . $mobj->getId() . "/" . $title;
		return $Pfad;
	}

	/**
	 * Function to the validate and save the form data
	 *
	 * @global type $tpl
	 * @global type $ilCtrl
	 * @global type $ilTabs
	 */
	function saveObject()
	{
		global $tpl, $ilCtrl, $ilTabs;
		$form = $this->initForm();

		// Check if the form is valid
		$isInputOkay = $form->checkInput();
		$isRoomAgreementCheckboxChecked = $form->getInput('accept_room_rules') == 1;
		$isRoomAgreementAvailable = $this->isRoomAgreementAvailable();
		$roomAgreement = !$isRoomAgreementAvailable || $isRoomAgreementCheckboxChecked;
		if ($isInputOkay && ($roomAgreement))
		{
			// Save the room_id in case of error for the next form
			$this->room_id = $form->getInput('room_id');

			include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php");

			// Build array with the standard-values for a booking
			$book = new ilRoomSharingBook();
			$book->setPoolId($this->getPoolId());
			$booking_values_array = array();
			$booking_values_array ['subject'] = $form->getInput('subject');
			$booking_values_array ['from'] = $form->getInput('from');
			$booking_values_array ['to'] = $form->getInput('to');
			$booking_values_array ['book_public'] = $form->getInput('book_public');
			if ($isRoomAgreementAvailable)
			{
				$booking_values_array ['accept_room_rules'] = $form->getInput('accept_room_rules');
			}
			$booking_values_array ['public'] = $form->getInput('book_public');
			$booking_values_array ['room'] = $this->room_id;
			$booking_values_array ['room_name'] = $this->ilRoomSharingRooms->getRoomName($this->room_id);

			// Build array with the booking-attribute-values for a booking
			$booking_attr_values_array = array();
			include_once ('Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php');
			$ilBookings = new ilRoomSharingBookings();
			$ilBookings->setPoolId($this->pool_id);
			foreach ($ilBookings->getAdditionalBookingInfos() as $attr_key => $attr_value)
			{
				$booking_attr_values_array[$attr_value['id']] = $form->getInput($attr_value['id']);
			}

			// Execute the database operations and check for return value
			$result = $book->addBooking($booking_values_array, $booking_attr_values_array,
				$this->ilRoomSharingRooms);
			if ($result === 1)
			{
				$this->parent_obj->addBookingAppointment($booking_values_array);
				$ilTabs->clearTargets();
				$this->parent_obj->setTabs();
				$ilCtrl->setCmd("render");
				$this->parent_obj->performCommand("");
				ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_booking_added'), true);
			}
			elseif ($result < 0)
			{
				if ($result === - 1)
				{
					ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_booking_add_error'), true);
				}
				elseif ($result === - 2)
				{
					ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_room_already_booked'), true);
				}
				elseif ($result === - 3)
				{
					ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_datefrom_bigger_dateto'), true);
				}
				elseif ($result === - 4)
				{
					ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_datefrom_is_earlier_than_now'), true);
				}
				$form->setValuesByPost();
				$ilTabs->setBackTarget($this->lng->txt('rep_robj_xrs_search_back'),
					$ilCtrl->getLinkTarget($this->parent_obj, 'showSearchResults'));
				$tpl->setContent($form->getHTML());
			}
		}
		else
		{
			$this->room_id = $form->getInput('room_id');
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_missing_required_entries'), true);
			$form->setValuesByPost();
			$ilTabs->setBackTarget($this->lng->txt('rep_robj_xrs_search_back'),
				$ilCtrl->getLinkTarget($this->parent_obj, 'showSearchResults'));
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Checks if a Roomagreement exists.
	 * Return true if one exists, otherwise false
	 *
	 * @return boolean
	 */
	private function isRoomAgreementAvailable()
	{
		$RoomAgreement = new ilRoomSharingBook();
		$RoomAgreement->setPoolId($this->getPoolId());
		$RoomAgreementInfo = $RoomAgreement->getRoomAgreement();
		if ($RoomAgreementInfo['rooms_agreement'] !== '0')
		{
			return true;
		}
		return false;
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

	/**
	 * Room-IDReturns the room id
	 *
	 * @return integer Room-ID
	 */
	public function getRoomId()
	{
		return $this->room_id;
	}

	/**
	 * Sets the room ID
	 *
	 * @param integer $a_room_id
	 *        	Room Id which should be set
	 */
	public function setRoomId($a_room_id)
	{
		$this->room_id = $a_room_id;
	}

}

?>
