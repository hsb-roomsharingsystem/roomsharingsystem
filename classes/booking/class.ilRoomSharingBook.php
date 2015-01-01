<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingBookException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingMailer.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingSequenceBookingUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivilegesConstants.php");

use ilRoomSharingSequenceBookingUtils as seqUtils;

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
	private $permission;
	private $recurrence;

	/**
	 * Constructor
	 *
	 * @global type $lng
	 * @global type $ilUser
	 * @param type $a_pool_id
	 */
	public function __construct($a_pool_id)
	{
		global $lng, $ilUser, $rssPermission;

		$this->lng = $lng;
		$this->user = $ilUser;
		$this->pool_id = $a_pool_id;
		$this->permission = $rssPermission;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Method to add a new booking into the database
	 *
	 * @param type $a_booking_values Array with the values of the booking
	 * @param type $a_booking_attr_values Array with the values of the booking-attributes
	 * @param type $a_booking_participants Array with the values of the participants
	 * @param type $a_recurrence_entries Array with recurrence information
	 * @throws ilRoomSharingBookException
	 */
	public function addBooking($a_booking_values, $a_booking_attr_values, $a_booking_participants,
		$a_recurrence_entries)
	{
		$this->date_from = $a_booking_values ['from'] ['date'] . " " . $a_booking_values ['from'] ['time'];
		$this->date_to = $a_booking_values ['to'] ['date'] . " " . $a_booking_values ['to'] ['time'];
		$this->room_id = $a_booking_values ['room'];
		$this->participants = $a_booking_participants;
		$this->recurrence = $a_recurrence_entries;
		$datetimes = $this->generateDatetimesForBooking();

		$this->validateBookingInput($datetimes['from'], $datetimes['to']);

		$a_booking_values['from'] = $datetimes['from'];
		$a_booking_values['to'] = $datetimes['to'];

		$success = $this->ilRoomsharingDatabase->insertBookingRecurrence($a_booking_attr_values,
			$a_booking_values, $a_booking_participants);

		if ($success)
		{
			$this->sendBookingNotification();
		}
		else
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_booking_add_error'));
		}
	}

	private function generateDatetimesForBooking()
	{
		$time_from = date('H:i:s', strtotime($this->date_from));
		$time_to = date('H:i:s', strtotime($this->date_to));
		$date_from = date('Y-m-d', strtotime($this->date_from));
		$date_to = date('Y-m-d', strtotime($this->date_to));
		if ($date_from != $date_to)
		{
			$date1 = strtotime($date_from);
			$date2 = strtotime($date_to);
			$day_difference = ceil(abs($date1 - $date2) / 86400);
		}
		else
		{
			$day_difference = null;
		}

		$datetimes_from = array();
		$datetimes_to = array();
		switch ($this->recurrence['frequence'])
		{
			case "DAILY":
				$days = seqUtils::getDailyFilteredData($date_from, $this->recurrence['repeat_type'],
						$this->recurrence['repeat_amount'], $this->recurrence['repeat_until'], $time_from, $time_to,
						$day_difference);
				$datetimes_from = $days['from'];
				$datetimes_to = $days['to'];
				break;
			case "WEEKLY":
				$days = seqUtils::getWeeklyFilteredData($date_from, $this->recurrence['repeat_type'],
						$this->recurrence['repeat_amount'], $this->recurrence['repeat_until'],
						$this->recurrence['weekdays'], $time_from, $time_to, $day_difference);
				$datetimes_from = $days['from'];
				$datetimes_to = $days['to'];
				break;
			case "MONTHLY":
				$days = seqUtils::getMonthlyFilteredData($date_from, $this->recurrence['repeat_type'],
						$this->recurrence['repeat_amount'], $this->recurrence['repeat_until'],
						$this->recurrence['start_type'], $this->recurrence['monthday'],
						$this->recurrence['weekday_1'], $this->recurrence['weekday_2'], $time_from, $time_to,
						$day_difference);
				$datetimes_from = $days['from'];
				$datetimes_to = $days['to'];
				break;
			default:
				$datetimes_from[] = $date_from . " " . $time_from;
				$datetimes_to[] = $date_to . " " . $time_to;
				break;
		}

		return array("from" => $datetimes_from, "to" => $datetimes_to);
	}

	/**
	 * Checks if the given booking input is valid (e.g. valid dates, already booked rooms, ...)
	 *
	 * @throws ilRoomSharingBookException
	 */
	private function validateBookingInput($a_datetimes_from, $a_datetimes_to)
	{
		if ($this->isBookingInPast())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_is_earlier_than_now"));
		}
		if ($this->checkForInvalidDateConditions())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_bigger_dateto"));
		}
		if ($this->isAlreadyBooked($a_datetimes_from, $a_datetimes_to))
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
	private function isAlreadyBooked($a_datetimes_from, $a_datetimes_to)
	{
		if ($this->permission->checkPrivilege(ilRoomSharingPrivilegesConstants::CANCEL_BOOKING_LOWER_PRIORITY))
		{
			$temp = $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($a_datetimes_from,
				$a_datetimes_to, $this->room_id, $this->permission->getUserPriority());
		}
		else
		{
			$temp = $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($a_datetimes_from,
				$a_datetimes_to, $this->room_id);
		}

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
