<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBookException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingMailer.php");

/**
 * Backend-Class for the booking form.
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Christopher Marks <deamp_marks@yahoo.d>
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 */
class ilRoomSharingBook
{
	protected $pool_id;
	private $ilRoomsharingDatabase;
	private $date_from;
	private $date_to;
	private $room_id;
	private $participants;

	public function __construct($a_pool_id)
	{
		global $lng, $ilUser;

		$this->lng = $lng;
		$this->user = $ilUser;
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Method to add a new booking into the database
	 *
	 * @param type $booking_values Array with the values of the booking
	 * @param type $booking_attr_values Array with the values of the booking-attributes
	 * @param type $booking_participants Array with the values of the participants
	 * @throws ilRoomSharingBookException
	 */
	public function addBooking($booking_values, $booking_attr_values, $booking_participants)
	{
		$this->date_from = $booking_values ['from'] ['date'] . " " . $booking_values ['from'] ['time'];
		$this->date_to = $booking_values ['to'] ['date'] . " " . $booking_values ['to'] ['time'];
		$this->room_id = $booking_values ['room'];
		$this->participants = $booking_participants;

		$this->validateBookingInput();
		$success = $this->insertBooking($booking_attr_values, $booking_values, $booking_participants);

		if ($success)
		{
			$this->sendBookingNotification();
		}
		else
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_booking_add_error'));
		}
	}

	/**
	 * Checks if the given booking input is valid (e.g. valid dates, already booked rooms, ...)
	 *
	 * @throws ilRoomSharingBookException
	 */
	private function validateBookingInput()
	{
		if ($this->isBookingInPast())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_is_earlier_than_now"));
		}
		if ($this->checkForInvalidDateConditions())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_bigger_dateto"));
		}
		if ($this->isAlreadyBooked())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_room_already_booked"));
		}
		if ($this->isRoomOverbooked())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_room_max_allocation_exceeded"));
		}
	}

	/**
	 * Method to check whether the booking date is in the past
	 */
	private function isBookingInPast()
	{
		return (strtotime($this->date_from) <= time());
	}

	/**
	 * Method to check whether the date is valid
	 * date_to must be higher or equal than the date_from
	 */
	private function checkForInvalidDateConditions()
	{
		return ($this->date_from >= $this->date_to);
	}

	/**
	 * Method to check if the selected room is already booked in the given time range
	 *
	 */
	private function isAlreadyBooked()
	{
		$temp = $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($this->date_from,
			$this->date_to, $this->room_id);
		return ($temp !== array());
	}

	/**
	 * Method that checks if the max allocation of a room is exceeded.
	 */
	private function isRoomOverbooked()
	{
		$room = new ilRoomSharingRoom($this->pool_id, $this->room_id);
		$max_alloc = $room->getMaxAlloc();
		$filtered_participants = array_filter($this->participants, array($this, "filterValidParticipants"));
		$overbooked = count($filtered_participants) >= $max_alloc;

		return $overbooked;
	}

	/**
	 * Callback function which is used for existing and therefore valid participants.
	 * Also it filters out the booker itself, if he is in the list of participants.
	 *
	 * @param string $a_participant
	 * return boolean/integer id of the participant if participant exists; false otherwise
	 */
	private function filterValidParticipants($a_participant)
	{
		return (empty($a_participant) || $this->user->getLogin() === $a_participant) ? false : ilObjUser::_lookupId($a_participant);
	}

	/**
	 * Method to insert the booking
	 *
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @return type -1 failed insert, 1 successful insert
	 */
	private function insertBooking($booking_attr_values, $booking_values, $booking_participants)
	{
		return $this->ilRoomsharingDatabase->insertBooking($booking_attr_values, $booking_values,
				$booking_participants);
	}

	/**
	 * Sets the pool-id
	 *
	 * @param integer $pool_id
	 *        	The pool id which should be set
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}

	/**
	 * Generate a booking acknowledgement via mail.
	 *
	 * @return array $recipient_ids List of recipients.
	 */
	private function sendBookingNotification()
	{
		$mail_message = $this->createMailMessage();
		$mailer = new ilRoomSharingMailer();
		$mailer->setRawSubject($this->lng->txt('rep_robj_xrs_mail_booking_creator_subject'));
		$mailer->setRawMessage($mail_message);

		$mailer->sendMail(array($this->user->getId()));
	}

	private function createMailMessage()
	{
		$room_name = $this->ilRoomsharingDatabase->getRoomName($this->room_id);
		$message = $this->lng->txt('rep_robj_xrs_mail_booking_creator_message') . "\n";
		$message .= "----------------------\n";
		$message .= $room_name . " ";
		$message .= $this->lng->txt('rep_robj_xrs_from') . " ";
		$message .= $this->date_from . " ";
		$message .= $this->lng->txt('rep_robj_xrs_to') . " ";
		$message .= $this->date_to;

		return $message;
	}

	/**
	 * Returns the room user agreement file id.
	 */
	public function getRoomAgreementFileId()
	{
		$agreement_file_id = $this->ilRoomsharingDatabase->getRoomAgreementIdFromDatabase();

		return $agreement_file_id;
	}

}

?>
