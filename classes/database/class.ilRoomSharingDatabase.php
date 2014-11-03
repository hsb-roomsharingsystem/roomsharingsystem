<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");
include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

use ilRoomSharingDBConstants as dcb;

/**
 * Class for database queries.
 *
 * @author Malte Ahlering
 */
class ilRoomsharingDatabase
{
	protected $pool_id;
	protected $ilDB;

	/**
	 * constructor ilRoomsharingDatabase
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id)
	{
		global $ilDB;
		$this->ilDB = $ilDB;
		$this->pool_id = $a_pool_id;
	}

	public function setPoolId($a_poolId)
	{
		$this->pool_id = $a_poolId;
	}

	/**
	 * Gets all attributes referenced by the rooms given by the ids.
	 *
	 * @param array $room_ids
	 *        	ids of the rooms
	 * @return array room_id, att.name, count
	 */
	public function getAttributesForRooms(array $room_ids)
	{
		$st = $this->ilDB->prepare('SELECT room_id, att.name, count FROM ' .
			dcb::ROOM_TO_ATTRIBUTE_TABLE . ' as rta LEFT JOIN ' .
			dcb::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id WHERE '
			. $this->ilDB->in("room_id", $room_ids) . ' ORDER BY room_id, att.name');
		$set = $this->ilDB->execute($st, $room_ids);
		return $set;
	}

	/**
	 * Returns the maximum amount of seats of all available rooms in the current pool, so that the
	 * the user can be notified about it in the filter options.
	 *
	 * @return integer $value maximum seats
	 */
	public function getMaxSeatCount()
	{
		$valueSet = $this->ilDB->query('SELECT MAX(max_alloc) AS value FROM ' .
			dcb::ROOMS_TABLE . ' WHERE pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer'));
		$valueRow = $this->ilDB->fetchAssoc($valueSet);
		$value = $valueRow ['value'];
		return $value;
	}

	public function getQueryStringForRoomsWithMathcingAttribute($attribute, $count)
	{
		return 'SELECT room_id FROM ' . dcb::ROOM_TO_ATTRIBUTE_TABLE . ' ra ' .
			'LEFT JOIN ' . dcb::ROOM_ATTRIBUTES_TABLE .
			' attr ON ra.att_id = attr.id WHERE name = ' . $this->ilDB->quote($attribute, 'text') .
			' AND count >= ' . $this->ilDB->quote($count, 'integer') .
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' ';
	}

	public function getAllRoomIds()
	{
		return 'SELECT id FROM ' . dcb::ROOMS_TABLE;
	}

	public function getMatchingRooms($roomsToCheck, $room_name, $room_seats)
	{
		$where_part = ' AND room.pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' ';

		if ($room_name || $room_name === "0")
		{
			$where_part = $where_part . ' AND name LIKE ' .
				$this->ilDB->quote('%' . $room_name . '%', 'text') . ' ';
		}
		if ($room_seats || $room_seats === 0.0)
		{
			$where_part = $where_part . ' AND max_alloc >= ' .
				$this->ilDB->quote($room_seats, 'integer') . ' ';
		}

		$st = $this->ilDB->prepare('SELECT room.id, name, max_alloc FROM ' .
			dcb::ROOMS_TABLE . ' room WHERE ' .
			$this->ilDB->in("room.id", array_keys($roomsToCheck)) . $where_part .
			' ORDER BY name');
		return $this->ilDB->execute($st, array_keys($roomsToCheck));
	}

	public function getAllAttributeNames()
	{
		$set = $this->ilDB->query('SELECT name FROM ' . dcb::ROOM_ATTRIBUTES_TABLE .
			' ORDER BY name');
		$attributes = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$attributes [] = $row ['name'];
		}
		return $attributes;
	}

	/**
	 * Determines the maximum amount of a given room attribute and returns it.
	 *
	 * @param type $a_room_attribute
	 *        	the attribute for which the max count
	 *        	should be determined
	 * @return type the max value of the attribute
	 */
	public function getMaxCountForAttribute($a_room_attribute)
	{
// get the id of the attribute in this pool
		$attributIdSet = $this->ilDB->query('SELECT id FROM ' . dcb::ROOM_ATTRIBUTES_TABLE .
			' WHERE name =' . $this->ilDB->quote($a_room_attribute, 'text') . ' AND pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer'));
		$attributIdRow = $this->ilDB->fetchAssoc($attributIdSet);
		$attributID = $attributIdRow ['id'];

		// get the max value of the attribut in this pool
		$valueSet = $this->ilDB->query('SELECT MAX(count) AS value FROM ' .
			dcb::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
			dcb::ROOMS_TABLE . ' as room ON room.id = rta.room_id ' .
			' WHERE att_id =' . $this->ilDB->quote($attributID, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
		$valueRow = $this->ilDB->fetchAssoc($valueSet);
		$value = $valueRow ['value'];
		return $value;
	}

	/**
	 * Get's the room name by a given room id
	 *
	 * @param integer $a_room_id
	 *        	Room id of the room which name is unknown
	 *
	 * @return Room Name
	 */
	public function getRoomName($a_room_id)
	{
		$roomNameSet = $this->ilDB->query(' SELECT name FROM ' . dcb::ROOMS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_room_id, 'integer'));
		$roomNameRow = $this->ilDB->fetchAssoc($roomNameSet);
		return $roomNameRow ['name'];
	}

	/**
	 * Get the room-ids from all rooms that are booked in the given timerange.
	 * A specific room_id can be given if a single room should be queried (used for bookings).
	 *
	 * @param string $date_from
	 * @param string $date_to
	 * @param string $room_id
	 *        	(optional)
	 * @return array values = room ids booked in given range
	 */
	public function getRoomsBookedInDateTimeRange($date_from, $date_to, $room_id = null)
	{
		$roomQuery = '';
		if ($room_id)
		{
			$roomQuery = ' room_id = ' .
				$this->ilDB->quote($room_id, 'text') . ' AND ';
		}

		$query = 'SELECT DISTINCT room_id FROM ' . dcb::BOOKINGS_TABLE . ' WHERE ' .
			$roomQuery . ' (' . $this->ilDB->quote($date_from, 'timestamp') .
			' BETWEEN date_from AND date_to OR ' . $this->ilDB->quote($date_to, 'timestamp') .
			' BETWEEN date_from AND date_to OR date_from BETWEEN ' .
			$this->ilDB->quote($date_from, 'timestamp') . ' AND ' . $this->ilDB->quote($date_to, 'timestamp') .
			' OR date_to BETWEEN ' . $this->ilDB->quote($date_from, 'timestamp') .
			' AND ' . $this->ilDB->quote($date_to, 'timestamp') . ')';

		$set = $this->ilDB->query($query);
		$res_room = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$res_room [] = $row ['room_id'];
		}
		return $res_room;
	}

	/**
	 * Insert a bboking into the database.
	 *
	 * @global type $this->ilDB
	 * @global type $ilUser
	 * @param array $booking_values
	 *        	Array with the values of the booking
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @return integer 1 = successful, -1 not successful
	 */
	public function insertBooking($booking_attr_values, $booking_values)
	{
		global $ilUser;

		$this->ilDB->insert(dcb::BOOKINGS_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextID(dcb::BOOKINGS_TABLE)),
			'date_from' => array('timestamp', $booking_values ['from'] ['date'] . " " . $booking_values ['from'] ['time']),
			'date_to' => array('timestamp', $booking_values ['to'] ['date'] . " " . $booking_values ['to'] ['time']),
			'room_id' => array('integer', $booking_values ['room']),
			'pool_id' => array('integer', $this->pool_id),
			'user_id' => array('integer', $ilUser->getId()),
			'subject' => array('text', $booking_values ['subject']),
			'public_booking' => array('boolean', $booking_values ['public'] == 1)
			)
		);

		$insertedId = $this->ilDB->getLastInsertId();

		if ($insertedId == - 1)
		{
			return - 1;
		}

		$this->insertBookingAttributes($insertedId, $booking_attr_values);

		return 1;
	}

	/**
	 * Method to insert booking attributes into the database.
	 *
	 * @param integer $insertedId
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 */
	private function insertBookingAttributes($insertedId, $booking_attr_values)
	{
		// Insert the attributes for the booking in the conjunction table
		foreach ($booking_attr_values as $booking_attr_key => $booking_attr_value)
		{
			// Only insert the attribute value, if a value was submitted by the user
			if ($booking_attr_value !== "")
			{
				$this->insertBookingAttribute($insertedId, $booking_attr_key, $booking_attr_value);
			}
		}
	}

	/**
	 * Inserts a booking attribute into the database.
	 *
	 * @param integer $insertedId
	 * @param integer $booking_attr_key
	 * @param string $booking_attr_value
	 */
	public function insertBookingAttribute($insertedId, $booking_attr_key, $booking_attr_value)
	{
		$this->ilDB->insert(dcb::BOOKING_TO_ATTRIBUTE_TABLE,
			array(
			'booking_id' => array('integer', $insertedId),
			'attr_id' => array('integer', $booking_attr_key),
			'value' => array('text', $booking_attr_value)
			)
		);
	}

	/**
	 * Delete a booking from the database.
	 *
	 * @param integer $a_booking_id
	 */
	public function deleteBooking($a_booking_id)
	{
		$this->ilDB->manipulate('DELETE FROM ' . dcb::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
		$this->ilDB->manipulate('DELETE FROM ' . dcb::USER_TABLE .
			' WHERE booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Get all bookings related to a given sequence.
	 *
	 * @param integer $seq_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingIdsForSequence($seq_id)
	{
		return $this->ilDB->query('SELECT id FROM ' . dcb::BOOKINGS_TABLE .
				' WHERE seq = ' . $this->ilDB->quote($seq_id, 'integer') .
				' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Gets User and Sequence for a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getSequenceAndUserForBooking($a_booking_id)
	{
		return $this->ilDB->query('SELECT seq_id, user_id  FROM ' . dcb::BOOKINGS_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets all bookings for a user.
	 *
	 * @param integer $user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBookingsForUser($user_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb::BOOKINGS_TABLE .
				' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
				' AND user_id = ' . $this->ilDB->quote($user_id, 'integer') .
				' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
				' OR date_to >= "' . date('Y-m-d H:i:s') . '")' . ' ORDER BY date_from ASC');
	}

	/**
	 * Gets all Participants of a booking.
	 *
	 * @param integer $booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipantsForBooking($booking_id)
	{
		return $this->ilDB->query('SELECT users.firstname AS firstname,' .
				' users.lastname AS lastname, users.login AS login,' .
				' users.usr_id AS id FROM ' . dcb::USER_TABLE . ' user ' .
				' LEFT JOIN usr_data AS users ON users.usr_id = user.user_id' .
				' WHERE booking_id = ' . $this->ilDB->quote($booking_id, 'integer') .
				' ORDER BY users.lastname, users.firstname ASC');
	}

	/**
	 * Gets all attributes of a booking.
	 *
	 * @param integer $booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAttributesForBooking($booking_id)
	{
		return $this->ilDB->query('SELECT value, attr.name AS name' .
				' FROM ' . dcb::BOOKING_TO_ATTRIBUTE_TABLE . ' bta ' .
				' LEFT JOIN ' . dcb::BOOKING_ATTRIBUTES_TABLE . ' attr ' .
				' ON attr.id = bta.attr_id' . ' WHERE booking_id = ' .
				$this->ilDB->quote($booking_id, 'integer'));
	}

	/**
	 * Gets all booking attributes.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingAttributes()
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb::BOOKING_ATTRIBUTES_TABLE .
				' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Gets all floorplans.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllFloorplans()
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb::FLOORPLANS_TABLE .
				' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
				' order by file_id DESC');
	}

	/**
	 * Gets a floorplan.
	 *
	 * @param integer $a_file_id
	 * @return type return of $this->ilDB->query
	 */
	public function getFloorplan($a_file_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb::FLOORPLANS_TABLE .
				' WHERE file_id = ' . $this->ilDB->quote($a_file_id, 'integer') . ' AND pool_id = '
				. $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Inserts a floorplan into the database.
	 *
	 * @param integer $a_file_id
	 * @return type return of $this->ilDB->manipulate
	 */
	public function insertFloorplan($a_file_id)
	{
		$this->ilDB->insert(dcb::FLOORPLANS_TABLE,
			array(
			'file_id' => array('integer', $a_file_id),
			'pool_id' => array('integer', $this->pool_id)
			)
		);

		return $this->ilDB->getLastInsertId();
	}

	/**
	 * Deletes a floorplan from the database.
	 *
	 * @param integer $a_file_id
	 * @return type return of $this->ilDB->manipulate
	 */
	public function deleteFloorplan($a_file_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dcb::FLOORPLANS_TABLE .
				' WHERE file_id = ' . $this->ilDB->quote($a_file_id, 'integer'));
	}

	/**
	 * Deletes a participation from the database.
	 *
	 * @param integer $user_id
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->manipulate
	 */
	public function deleteParticipation($user_id, $a_booking_id)
	{
		return $this->ilDB->manipulate(
				'DELETE FROM ' . dcb::USER_TABLE . ' WHERE user_id = ' .
				$this->ilDB->quote($user_id(), 'integer') .
				' AND booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets participation for a user.
	 *
	 * @param integer $user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipationsForUser($user_id)
	{
		return $this->ilDB->query(
				'SELECT booking_id FROM ' . dcb::USER_TABLE .
				' WHERE user_id = ' . $this->ilDB->quote($user_id, 'integer'));
	}

	/**
	 * Gets a booking.
	 *
	 * @param integer $booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBooking($booking_id)
	{
		return $this->ilDB->query(
				'SELECT *' . ' FROM ' . dcb::BOOKINGS_TABLE . ' WHERE id = ' .
				$this->ilDB->quote($booking_id, 'integer') .
				' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
				' OR date_to >= "' . date('Y-m-d H:i:s') . '")' .
				' ORDER BY date_from ASC');
	}

	/**
	 * Gets a user.
	 *
	 * @param integer $user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getUser($user_id)
	{
		return $this->ilDB->query('SELECT firstname, lastname, login' . ' FROM usr_data' .
				' WHERE usr_id = ' . $this->ilDB->quote($user_id, 'integer'));
	}

	/**
	 * Gets a room.
	 *
	 * @param integer $room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getRoom($room_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb::ROOMS_TABLE . ' WHERE id = ' .
				$this->ilDB->quote($room_id, 'integer'));
	}

	/**
	 * Get a room attribute.
	 *
	 * @param integer $attribute_id
	 * @return type return of $this->ilDB->query
	 */
	public function getRoomAttribute($attribute_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb::ROOM_ATTRIBUTES_TABLE .
				' WHERE id = ' . $this->ilDB->quote($attribute_id, 'integer'));
	}

	/**
	 * Gets attributes for a room.
	 *
	 * @param integer $room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAttributesForRoom($room_id)
	{
		return $this->ilDB->query('SELECT id, att.name, count FROM ' .
				dcb::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
				dcb::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id' .
				' WHERE room_id = ' . $this->ilDB->quote($room_id, 'integer') . ' ORDER BY att.name');
	}

	/**
	 * Gets all bookings for a room.
	 *
	 * @param integer $room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBookingsForRoom($room_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dcb:: BOOKINGS_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($room_id, 'integer'));
	}

	/**
	 * Deletes attributes for a room.
	 *
	 * @param type $room_id
	 * @return type
	 */
	public function deleteAttributesForRoom($room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dcb::ROOM_TO_ATTRIBUTE_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($room_id, 'integer'));
	}

	/**
	 * Inserts a room into the database.
	 *
	 * @param string $name
	 * @param string $type
	 * @param integer $min_alloc
	 * @param integer $max_alloc
	 * @param integer $file_id
	 * @param integer $building_id
	 * @return integer id of the room
	 */
	public function insertRoom($name, $type, $min_alloc, $max_alloc, $file_id, $building_id)
	{
		$this->ilDB->insert(dcb::ROOMS_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextId(dcb::ROOMS_TABLE)),
			'name' => array('text', $name),
			'type' => array('text', $type),
			'min_alloc' => array('integer', $min_alloc),
			'max_alloc' => array('integer', $max_alloc),
			'file_id' => array('integer', $file_id),
			'building_id' => array('integer', $building_id),
			'pool_id' => array('integer', $this->pool_id)
		));
		return $this->ilDB->getLastInsertId();
	}

	/**
	 * Inserts an attribute to room relation into the database.
	 *
	 * @param integer $room_id
	 * @param integer $attribute_id
	 * @param integer $count
	 */
	public function insertAttributeForRoom($room_id, $attribute_id, $count)
	{
		$this->ilDB->insert(ilRoomSharingDBConstants::ROOM_TO_ATTRIBUTE_TABLE,
			array(
			'room_id' => array('integer', $room_id),
			'att_id' => array('integer', $attribute_id),
			'count' => array('integer', $count)
		));
	}

	/**
	 * Gets all Room Agrements from the Database
	 *
	 * @global type $ilDB
	 * @return type return of $ilDB->query
	 */
	public function getRoomAgrementFromDatabase()
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::POOLS_TABLE .
				' WHERE id = ' . $ilDB->quote($this->pool_id, 'integer') . ' order by rooms_agreement DESC');
	}

	/**
	 * Gets the calendar-id of the current RoomSharing-Pool
	 *
	 * @global type $ilDB
	 * @return integer calendar-id
	 */
	public function getCalendarIdFromDatabase()
	{
		global $ilDB;
		$set = $ilDB->query('SELECT calendar_id FROM ' . ilRoomsharingDBConstants::POOLS_TABLE .
			' WHERE id = ' . $ilDB->quote($this->pool_id, 'integer'));
		$row = $ilDB->fetchAssoc($set);
		return $row["calendar_id"];
	}

	/**
	 * Updates rep_robj_xrs_pools with an new calendar-id.
	 *
	 * Typically only called once per pool.
	 *
	 * @global type $ilDB
	 * @param type $cal_id
	 * @return type
	 */
	public function setCalendarId($cal_id)
	{
		global $ilDB;
		return $this->ilDB->manipulate('UPDATE ' . ilRoomsharingDBConstants::POOLS_TABLE .
				' SET calendar_id = ' . $ilDB->quote($cal_id, 'integer') .
				' WHERE id = ' . $ilDB->quote($this->pool_id, 'integer'));
	}

}
