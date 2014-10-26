<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * Backend-Class for booking-mask
 * @author Robert Heimsoth
 */
class ilRoomSharingBook
{
	protected $pool_id;

	/**
	 * Method to add a new booking into the database
	 *
	 * @global type $ilDB
	 * @global type $ilUser
	 * @global type $pool_id
	 * @param array $booking_values
	 *        	Array with the values of the booking
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @param
	 *        	ilRoomSharingRooms Object of ilRoomSharingRooms
	 * @return type
	 */
	public function addBooking($booking_values, $booking_attr_values, $ilRoomSharingRooms)
	{
		global $ilDB, $ilUser;
		$ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);

		$subject = $booking_values ['subject'];
		$date_from = $booking_values ['from'] ['date'] . " " . $booking_values ['from'] ['time'];
		$date_to = $booking_values ['to'] ['date'] . " " . $booking_values ['to'] ['time'];

		// Check whether the date_from is earlier than now
		if (strtotime($date_from) <= time())
		{
			return - 4;
		}

		// Check whether the date_to is earlier or equal than the date_from
		if ($date_from >= $date_to)
		{
			return - 3;
		}

		$room_id = $booking_values ['room'];
		$user_id = $ilUser->getId();

		// Check if the selected room is already booked in the given time range
		if ($ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($date_from, $date_to, $room_id) !==
			array())
		{
			return - 2;
		}

		// Insert the booking
		$insertedId = $ilRoomsharingDatabase->insertBooking($room_id, $user_id, $subject, $date_from,
			$date_to);

		// Check whether the insert failed
		if ($insertedId === - 1)
		{
			return - 1;
		}

		// Insert the attributes for the booking in the conjunction table
		foreach ($booking_attr_values as $booking_attr_key => $booking_attr_value)
		{
			// Only insert the attribute value, if a value was submitted by the user
			if ($booking_attr_value !== "")
			{
				$ilRoomsharingDatabase->insertBookingAttribute($insertedId, $booking_attr_key,
					$booking_attr_value);
			}
		}
		return 1;
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
