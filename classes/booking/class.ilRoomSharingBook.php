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
	private $booking_id;
	private $date_from_old;
	private $date_to_old;

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
	 * Method to show a booking and get his data
	 *
	 * @param type $a_booking_id
	 */
	public function showBooking($a_booking_id)
	{
		$booking = $this->ilRoomsharingDatabase->getBooking($a_booking_id);
		$booking_attributes = $this->ilRoomsharingDatabase->getBookingAttributeValues($a_booking_id);
		$booking_participants = $this->ilRoomsharingDatabase->getParticipantsForBooking($a_booking_id);

		$this->date_from = $booking ['from'] ['date'] . " " . $booking ['from'] ['time'];
		$this->date_to = $booking ['to'] ['date'] . " " . $booking ['to'] ['time'];
		$this->room_id = $booking ['room'];
		$this->attribut = $booking_attributes;
		$this->participants = $booking_participants;
	}

	/**
	 * Method to edit a booking and update database entry
	 *
	 * @param type $a_booking_values
	 * @param type $a_booking_attr_values
	 * @param type $a_booking_participants
	 * @throws ilRoomSharingBookException
	 */
	public function updateEditBooking($a_booking_id, $a_old_booking_values, $a_old_booking_attr_values,
		$a_old_booking_participants, $a_booking_values, $a_booking_attr_values, $a_booking_participants)
	{
		$this->date_from = $a_booking_values ['from'] ['date'] . " " . $a_booking_values ['from'] ['time'];
		$this->date_to = $a_booking_values ['to'] ['date'] . " " . $a_booking_values ['to'] ['time'];
		$this->room_id = $a_booking_values ['room'];
		$booking_participants = $this->deleteEmptyUser($a_booking_participants);
		$newFromDate = $a_booking_values['from'] ['date'] . " " . $a_booking_values ['from'] ['time'];
		$newToDate = $a_booking_values['to'] ['date'] . " " . $a_booking_values ['to'] ['time'];
		$oldFromDate = $a_old_booking_values['date_from'];
		$oldToDate = $a_old_booking_values['date_to'];
		$this->participants = $booking_participants;
		$this->booking_id = $a_booking_id;
		$this->date_from_old = $oldFromDate;
		$this->date_to_old = $oldToDate;


		$this->validateEditBookingInput();
		$success = $this->updateBooking($a_booking_id, $a_booking_attr_values, $a_old_booking_attr_values,
			$a_booking_values, $a_old_booking_values, $booking_participants, $a_old_booking_participants);

		$dateChange = $oldFromDate != $newFromDate || $oldToDate != $newToDate;
		$participantsChange = $a_old_booking_participants != $booking_participants;
		if ($success)
		{
			$deletedUser = $this->getDeletedUser($booking_participants, $a_old_booking_participants);
			$newUser = $this->getNewUser($booking_participants, $a_old_booking_participants);
			if ($participantsChange && $dateChange)
			{
				$this->sendBookingUpdatedNotification($booking_participants);
				if ($deletedUser != array())
				{
					$this->sendBookingUpdatedNotificationToCanceldUser($deletedUser);
				}
				if ($newUser != array())
				{
					$this->sendBookingNotificationToNewUser($newUser);
				}
			}
			else if ($participantsChange)
			{
				if ($deletedUser != array())
				{
					$this->sendBookingUpdatedNotificationToCanceldUser($deletedUser);
				}
				if ($newUser != array())
				{
					$this->sendBookingNotificationToNewUser($newUser);
				}
			}
			else if ($dateChange)
			{
				//Send a email notifications to the creator and participants
				$this->sendBookingUpdatedNotification($booking_participants);
			}
		}
		else
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_booking_add_error'));
		}
	}

	/**
	 * Return a array with the all deleted participants.
	 * If no user user is deleted, a empty array will be returned.
	 *
	 * @param array $a_booking_participants = new participants
	 * @param array $a_old_booking_participants = old participants
	 * @return array
	 */
	private function getDeletedUser($a_booking_participants, $a_old_booking_participants)
	{
		$deletedUser = array();
		foreach ($a_old_booking_participants as $user)
		{
			if (!in_array($user, $a_booking_participants))
			{

				$deletedUser[] = $user;
			}
		}
		return $deletedUser;
	}

	/**
	 * Return all new User from the $a_booking_participants array.
	 *
	 * @param array $a_booking_participants with the new participants
	 * @param array $a_old_booking_participants with the old participants
	 * @return array
	 * 			If there are no new user, return a empty array if no new user found.
	 */
	private function getNewUser($a_booking_participants, $a_old_booking_participants)
	{
		$newUser = array();
		foreach ($a_booking_participants as $user)
		{
			if (!in_array($user, $a_old_booking_participants))
			{
				$newUser[] = $user;
			}
		}
		return $newUser;
	}

	/**
	 * Removing all empty user entry from the $a_booking_participants array.
	 *
	 * @param array $a_booking_participants with the participants
	 * @return array
	 * 			the new array without empty users.
	 */
	private function deleteEmptyUser($a_booking_participants)
	{
		$booking_booking_participants = array();
		foreach ($a_booking_participants as $user)
		{
			if ($user != '')
			{
				$booking_booking_participants[] = $user;
			}
		}

		return $booking_booking_participants;
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
	 * Checks if the edit booking input is valid (e.g. valid dates, already booked rooms, ...)
	 *
	 * @throws ilRoomSharingBookException
	 */
	private function validateEditBookingInput()
	{
		if ($this->isBookingInPast())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_is_earlier_than_now"));
		}
		if ($this->checkForInvalidDateConditions())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_bigger_dateto"));
		}
		if ($this->isAlreadyBookedForEdits())
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
	 * Method to check if the selected room is already booked in the given time range
	 *
	 * Return false if the room is free.
	 */
	private function isAlreadyBookedForEdits()
	{
		$intDateFrom = $this->stringTimeToInt($this->date_from);
		$intDateFromOld = $this->stringTimeToInt($this->date_from_old);
		$intDateTo = $this->stringTimeToInt($this->date_to);
		$intDateToOld = $this->stringTimeToInt($this->date_to_old);
		$newTimeEqualsOldTime = $this->date_from == $this->date_from_old && $this->date_to == $this->date_to_old;
		$newTimeBetweenOldTime = $intDateFrom > $intDateFromOld && $intDateTo < $intDateToOld;
		$newTimeBeforeOldTime = $intDateFrom < $intDateFromOld;
		$newTimeAfterOldTime = $intDateTo > $intDateToOld;
		$newTimeBeforeAndAfterOldTime = $newTimeBeforeOldTime && $newTimeAfterOldTime;
		if ($newTimeEqualsOldTime || $newTimeBetweenOldTime)
		{
			return false;
		}
		else if ($newTimeBeforeOldTime || $newTimeAfterOldTime || $newTimeBeforeAndAfterOldTime)
		{
			$temp = $this->ilRoomsharingDatabase->getBookingIdForRoomInDateTimeRange($this->date_from,
				$this->date_to, $this->room_id, $this->booking_id);
			return ($temp !== array());
		}
	}

	/**
	 * Convert the given string time to a integer value.
	 *
	 * @param string $a_date
	 * @return integer
	 */
	private function stringTimeToInt($a_date)
	{
		return strtotime($a_date);
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
	 * Method to updated a booking in the database.
	 *
	 * @param integer $booking_id
	 * @param array $booking_attr_values
	 * @param array $booking_values
	 * @param array $booking_participants
	 *
	 * @return boolean true if the update was successful
	 */
	private function updateBooking($a_booking_id, $a_booking_attr_values, $a_old_booking_attr_values,
		$a_booking_values, $a_old_booking_values, $a_booking_participants, $a_old_booking_participants)
	{
		return $this->ilRoomsharingDatabase->updateBooking($a_booking_id, $a_booking_attr_values,
				$a_old_booking_attr_values, $a_booking_values, $a_old_booking_values, $a_booking_participants,
				$a_old_booking_participants);
	}

	/** Generates a booking acknowledgement via mail.
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
	 * Generates a booking acknowledgement via mail to given new Users.
	 *
	 * @param array $newUser
	 */
	private function sendBookingNotificationToNewUser($newUser)
	{
		$room_name = $this->ilRoomsharingDatabase->getRoomName($this->room_id);

		$mailer = new ilRoomSharingMailer($this->lng);
		$mailer->setRoomname($room_name);
		$mailer->setDateStart($this->date_from);
		$mailer->setDateEnd($this->date_to);
		$mailer->sendBookingMailToNewUser($newUser);
	}

	/**
	 * Generates a update booking acknowledgement via mail.
	 *
	 * @parm array $a_participants with the user-ids
	 */
	private function sendBookingUpdatedNotification($a_participants)
	{
		$room_name = $this->ilRoomsharingDatabase->getRoomName($this->room_id);

		$mailer = new ilRoomSharingMailer($this->lng);
		$mailer->setRoomname($room_name);
		$mailer->setDateStart($this->date_from);
		$mailer->setDateEnd($this->date_to);
		$mailer->sendUpdateBookingMail($this->user->getId(), $a_participants);
	}

	/**
	 * Generates a booking acknowledgement about a booking cancel for the users via mail.
	 *
	 * @param array $a_deletedUser
	 * 			user-id who get the mail
	 */
	private function sendBookingUpdatedNotificationToCanceldUser($a_deletedUser)
	{
		$room_name = $this->ilRoomsharingDatabase->getRoomName($this->room_id);

		$mailer = new ilRoomSharingMailer($this->lng);
		$mailer->setRoomname($room_name);
		$mailer->setDateStart($this->date_from);
		$mailer->setDateEnd($this->date_to);
		$mailer->sendCancellationMailToParticipants($deletedUser);
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
