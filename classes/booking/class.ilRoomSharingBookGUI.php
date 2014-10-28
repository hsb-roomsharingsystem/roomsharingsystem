<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php");
require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php");
require_once ("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

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
	protected $parent_obj;
	protected $pool_id;
	protected $room_id;
	protected $ilRoomSharingRooms;
	protected $date_from;
	protected $date_to;

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

		$this->ilRoomSharingRooms = new ilRoomSharingRooms();
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
	 * Renders the booking form including all of its properties.
	 */
	public function renderBookingForm()
	{
		$this->tpl->setContent($this->createForm()->getHTML());
	}

	/**
	 * Creates a booking form.
	 *
	 * @return the form
	 */
	protected function createForm()
	{

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));

		// Set form frame title
		$form->setTitle($this->lng->txt('rep_robj_xrs_room_book') . ': ' . $this->lng->txt('rep_robj_xrs_room')
			. ' ' . $this->ilRoomSharingRooms->getRoomName((empty($this->room_id)) ? $_POST ['room_id'] : $this->room_id));

		// Input for the subject of the booking
		$subject = new ilTextInputGUI($this->lng->txt("subject"), "subject");
		$subject->setRequired(true);
		$subject->setSize(40);
		$subject->setMaxLength(120);
		$form->addItem($subject);

		$form->addCommandButton("book", $this->lng->txt("rep_robj_xrs_room_book"));

		// List the booking-attributes
		$ilBookings = new ilRoomSharingBookings();
		$ilBookings->setPoolId($this->pool_id);
		foreach ($ilBookings->getAdditionalBookingInfos() as $attr_key => $attr_value)
		{
			$formattr = new ilTextInputGUI($attr_value ['txt'], $attr_value ['id']);
			$formattr->setSize(40);
			$formattr->setMaxLength(120);
			$form->addItem($formattr);
		}


		$time_range = new ilCombinationInputGUI($this->lng->txt("assessment_log_datetime"), "time_range");

		// Datetime Input for "Of"
		$dt_prop = new ilDateTimeInputGUI($this->lng->txt("of"), "from");
		if (!empty($this->date_from))
		{
			$dt_prop->setDate(new ilDateTime($this->date_from, IL_CAL_DATETIME));
			$this->date_from = "";
		}
		$dt_prop->setMinuteStepSize(5);
		$time_range->addCombinationItem("of", $dt_prop, $this->lng->txt("of"));
		$dt_prop->setShowTime(true);

		// Datetime Input for "To"
		$dt_prop1 = new ilDateTimeInputGUI($this->lng->txt("to"), "to");
		if (!empty($this->date_to))
		{
			$dt_prop1->setDate(new ilDateTime($this->date_to, IL_CAL_DATETIME));
			$this->date_to = "";
		}
		$dt_prop1->setMinuteStepSize(5);
		$time_range->addCombinationItem("to", $dt_prop1, $this->lng->txt("to"));
		$dt_prop1->setShowTime(true);
		$form->addItem($time_range);

		// checkbox to make username public
		$cb_pub = new ilCheckboxInputGUI($this->lng->txt("rep_robj_xrs_room_public_booking"),
			"book_public");
		$cb_pub->setValue("1");
		$cb_pub->setChecked(false);
		$cb_pub->setRequired(false);
		$form->addItem($cb_pub);

		// checkbox to confirm the room use agreement
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt("rep_robj_xrs_room_user_agreement"),
			"accept_room_rules");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(false);
		$cb_prop->setRequired(true);
		$form->addItem($cb_prop);

		// input for
		$participants_input = new ilTextInputGUI($this->lng->txt("rep_robj_xrs_room_participants"),
			"participants");
		$participants_input->setMulti(true);
		$form->addItem($participants_input);

		// Save room-id in a hidden input field
		$room_id_prop = new ilHiddenInputGUI("room_id");
		$room_id_prop->setValue($this->room_id);
		$room_id_prop->setRequired(true);
		$form->addItem($room_id_prop);

		return $form;
	}

	/**
	 * Function to the validate and save the form data
	 *
	 * @global type $ilTabs
	 */
	protected function book()
	{
		global $ilTabs;
		$form = $this->createForm();

		// Check if the form is valid
		if ($form->checkInput() && $form->getInput('accept_room_rules') == 1)
		{
			// Save the room_id in case of error for the next form
			$this->room_id = $form->getInput('room_id');


			// Build array with the standard-values for a booking
			$book = new ilRoomSharingBook();
			$book->setPoolId($this->getPoolId());
			$booking_values_array = array();
			$booking_values_array ['subject'] = $form->getInput('subject');
			$booking_values_array ['from'] = $form->getInput('from');
			$booking_values_array ['to'] = $form->getInput('to');
			$booking_values_array ['book_public'] = $form->getInput('book_public');
			$booking_values_array ['accept_room_rules'] = $form->getInput('accept_room_rules');
			$booking_values_array ['room'] = $this->room_id;

			// Build array with the booking-attribute-values for a booking
			$booking_attr_values_array = array();
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
				$ilTabs->clearTargets();
				$this->parent_obj->setTabs();
				$this->ctrl->setCmd("render");
				$this->parent_obj->performCommand("");
				ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_booking_added'), true);
			}
			elseif ($result < 0)
			{
				$this->displayErrorMessage($result);
				$form->setValuesByPost();
				$this->tpl->setContent($form->getHTML());
			}
		}
		else
		{
			$this->room_id = $form->getInput('room_id');
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_missing_required_entries'), true);
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHTML());
		}
	}

	private function displayErrorMessage($a_error_code)
	{
		if ($a_error_code == - 1)
		{
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_booking_add_error'), true);
		}
		elseif ($a_error_code == - 2)
		{
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_room_already_booked'), true);
		}
		elseif ($a_error_code == - 3)
		{
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_datefrom_bigger_dateto'), true);
		}
		elseif ($a_error_code == - 4)
		{
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_datefrom_is_earlier_than_now'), true);
		}
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
