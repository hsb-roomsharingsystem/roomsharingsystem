<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBookException.php");

/**
 * Backend-Class for booking-mask
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

	public function __construct()
	{
		global $lng;
		$this->lng = $lng;
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
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
		$this->date_from = $booking_values ['from'] ['date'] . " " . $booking_values ['from'] ['time'];
		$this->date_to = $booking_values ['to'] ['date'] . " " . $booking_values ['to'] ['time'];
		$this->room_id = $booking_values ['room'];

		$this->checkForBookingPreconditions();
		$success = $this->insertBooking($booking_attr_values, $booking_values, $booking_participants);

		if (!$success)
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_booking_add_error'));
		}
	}

	/**
	 * Checks if certain conditions for the booking are met.
	 *
	 * @throws ilRoomSharingBookException
	 */
	private function checkForBookingPreconditions()
	{
		if ($this->isBookingInPast())
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_datefrom_is_earlier_than_now'));
		}
		if ($this->checkForInvalidDateConditions())
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_datefrom_bigger_dateto'));
		}
		if ($this->isAlreadyBooked())
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_room_already_booked'));
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
	 * @param $ilRoomSharingRooms Object of ilRoomSharingRooms
	 */
	private function isAlreadyBooked()
	{
		$temp = $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($this->date_from,
			$this->date_to, $this->room_id);
		return ($temp !== array());
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

}

?>
