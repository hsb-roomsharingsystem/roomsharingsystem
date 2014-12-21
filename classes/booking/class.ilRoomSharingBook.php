<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingBookException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingMailer.php");

/**
 * Backend-Class for the booking form.
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Christopher Marks <deamp_marks@yahoo.de>
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 */
class ilRoomSharingBook
{
	private $pool_id;
	private $ilRoomsharingDatabase;
	private $date_from;
	private $date_to;
	private $room_id;
	private $participants;

	/**
	 * Constructor
	 *
	 * @global type $lng
	 * @global type $ilUser
	 * @param type $a_pool_id
	 */
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
	 * @param type $a_booking_values Array with the values of the booking
	 * @param type $a_booking_attr_values Array with the values of the booking-attributes
	 * @param type $a_booking_participants Array with the values of the participants
	 * @throws ilRoomSharingBookException
	 */
	public function addBooking($a_booking_values, $a_booking_attr_values, $a_booking_participants)
	{
		$this->date_from = $a_booking_values ['from'] ['date'] . " " . $a_booking_values ['from'] ['time'];
		$this->date_to = $a_booking_values ['to'] ['date'] . " " . $a_booking_values ['to'] ['time'];
		$this->room_id = $a_booking_values ['room'];
		$this->participants = $a_booking_participants;

		$this->validateBookingInput();
		$success = $this->insertBooking($a_booking_attr_values, $a_booking_values, $a_booking_participants);

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
		if ($this->isRoomUnderbooked())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_room_min_allocation_not_reached"));
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
	 * Method that checks if the min allocation of a room is not reached.
	 */
	private function isRoomUnderbooked()
	{
		$room = new ilRoomSharingRoom($this->pool_id, $this->room_id);
		$min_alloc = $room->getMinAlloc();
		$filtered_participants = array_filter($this->participants, array($this, "filterValidParticipants"));
		$underbooked = count($filtered_participants) < $min_alloc;

		return $underbooked;
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
	 * Generates a booking acknowledgement via mail.
	 *
	 * @return array $recipient_ids List of recipients.
	 */
	private function sendBookingNotification()
	{
		$room_name = $this->ilRoomsharingDatabase->getRoomName($this->room_id);

		$mailer = new ilRoomSharingMailer($this->lng);
		$mailer->setRoomname($room_name);
		$mailer->setDateStart($this->date_from);
		$mailer->setDateEnd($this->date_to);
		$mailer->sendBookingMail($this->user->getId(), $this->participants);
	}

	/**
	 * Returns the room user agreement file id.
	 */
	public function getRoomAgreementFileId()
	{
		$agreement_file_id = $this->ilRoomsharingDatabase->getRoomAgreementId();

		return $agreement_file_id;
	}

	/**
	 * Set the poolID of bookings
	 *
	 * @param integer $pool_id
	 *        	poolID
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}

	/**
	 * Get the PoolID of bookings
	 *
	 * @return integer PoolID
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

}

?>
