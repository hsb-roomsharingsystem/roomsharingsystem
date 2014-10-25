<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");

/**
 * Class for database uqeries.
 *
 * @author Malte Ahlering
 */
class ilRoomsharingDatabase
{
	protected $pool_id;

	/**
	 * constructor ilRoomsharingDatabase
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

	public function setPoolId(
	$a_poolId)
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
		global $ilDB;
		$st = $ilDB->prepare('SELECT room_id, att.name, count FROM ' .
			ilRoomsharingDBConstants::ROOM_TO_ATTRIBUTE . ' as rta LEFT JOIN ' .
			ilRoomsharingDBConstants::ROOM_ATTRIBUTES . ' as att ON att.id = rta.att_id WHERE '
			. $ilDB->in("room_id", $room_ids) . ' ORDER BY room_id, att.name');
		$set = $ilDB->execute($st, $room_ids);
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
		global $ilDB;
		$valueSet = $ilDB->query('SELECT MAX(max_alloc) AS value FROM ' .
			ilRoomsharingDBConstants::ROOMS . ' WHERE pool_id = ' .
			$ilDB->quote($this->pool_id, 'integer'));
		$valueRow = $ilDB->fetchAssoc($valueSet);
		$value = $valueRow ['value'];
		return $value;
	}

	public function getQueryStringForRoomsWithMathcingAttribute($attribute, $count)
	{
		global $ilDB;
		return 'SELECT room_id FROM ' . ilRoomsharingDBConstants::ROOM_TO_ATTRIBUTE . ' ra ' .
			'LEFT JOIN ' . ilRoomsharingDBConstants::ROOM_ATTRIBUTES .
			' attr ON ra.att_id = attr.id WHERE name = ' . $ilDB->quote($attribute, 'text') .
			' AND count >= ' . $ilDB->quote($count, 'integer') .
			' AND pool_id = ' . $ilDB->quote($this->pool_id, 'integer') . ' ';
	}

	public function getAllRoomIds()
	{
		return 'SELECT id FROM ' . ilRoomsharingDBConstants::ROOMS;
	}

	public function getMatchingRooms($roomsToCheck, $room_name, $room_seats)
	{
		global $ilDB;
		$where_part = ' AND room.pool_id = ' . $ilDB->quote($this->pool_id, 'integer') . ' ';

		if ($room_name || $room_name === "0")
		{
			$where_part = $where_part . ' AND name LIKE ' .
				$ilDB->quote('%' . $room_name . '%', 'text') . ' ';
		}
		if ($room_seats || $room_seats === 0.0)
		{
			$where_part = $where_part . ' AND max_alloc >= ' .
				$ilDB->quote($room_seats, 'integer') . ' ';
		}

		$st = $ilDB->prepare('SELECT room.id, name, max_alloc FROM ' .
			ilRoomsharingDBConstants::ROOMS . ' room WHERE ' .
			$ilDB->in("room.id", array_keys($roomsToCheck)) . $where_part .
			' ORDER BY name');
		return $ilDB->execute($st, array_keys($roomsToCheck));
	}

	public function getAllAttributeNames()
	{
		global $ilDB;
		$set = $ilDB->query('SELECT name FROM ' . ilRoomsharingDBConstants::ROOM_ATTRIBUTES .
			' ORDER BY name');
		$attributes = array();
		while ($row = $ilDB->fetchAssoc($set))
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
		global $ilDB;
		// get the id of the attribute in this pool
		$attributIdSet = $ilDB->query('SELECT id FROM ' . ilRoomsharingDBConstants::ROOM_ATTRIBUTES .
			' WHERE name =' . $ilDB->quote($a_room_attribute, 'text') . ' AND pool_id = ' .
			$ilDB->quote($this->pool_id, 'integer'));
		$attributIdRow = $ilDB->fetchAssoc($attributIdSet);
		$attributID = $attributIdRow ['id'];

		// get the max value of the attribut in this pool
		$valueSet = $ilDB->query('SELECT MAX(count) AS value FROM ' .
			ilRoomsharingDBConstants::ROOM_TO_ATTRIBUTE . ' rta LEFT JOIN ' .
			ilRoomsharingDBConstants::ROOMS . ' as room ON room.id = rta.room_id ' .
			' WHERE att_id =' . $ilDB->quote($attributID, 'integer') .
			' AND pool_id =' . $ilDB->quote($this->pool_id, 'integer'));
		$valueRow = $ilDB->fetchAssoc($valueSet);
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
		global $ilDB;
		$roomNameSet = $ilDB->query(' SELECT name FROM ' . ilRoomsharingDBConstants::ROOMS .
			' WHERE id = ' . $ilDB->quote($a_room_id, 'integer'));
		$roomNameRow = $ilDB->fetchAssoc($roomNameSet);
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
		global $ilDB;

		$roomQuery = '';
		if ($room_id)
		{
			$roomQuery = ' room_id = ' .
				$ilDB->quote($room_id, 'text') . ' AND ';
		}

		$query = 'SELECT DISTINCT room_id FROM ' . ilRoomsharingDBConstants::BOOKINGS . ' WHERE ' .
			$roomQuery . ' (' . $ilDB->quote($date_from, 'timestamp') .
			' BETWEEN date_from AND date_to OR ' . $ilDB->quote($date_to, 'timestamp') .
			' BETWEEN date_from AND date_to OR date_from BETWEEN ' .
			$ilDB->quote($date_from, 'timestamp') . ' AND ' . $ilDB->quote($date_to, 'timestamp') .
			' OR date_to BETWEEN ' . $ilDB->quote($date_from, 'timestamp') .
			' AND ' . $ilDB->quote($date_to, 'timestamp') . ')';

		$set = $ilDB->query($query);
		$res_room = array();
		while ($row = $ilDB->fetchAssoc($set))
		{
			$res_room [] = $row ['room_id'];
		}
		return $res_room;
	}

	/**
	 * Insert a bboking into the database.
	 *
	 * @param integer $room_id
	 * @param integer $user_id
	 * @param string $subject
	 * @param timestamp $date_from
	 * @param timestamp $date_to
	 * @return integer id of inserted booking
	 */
	public function insertBooking($room_id, $user_id, $subject, $date_from, $date_to)
	{
		global $ilDB;
		$ilDB->insert(ilRoomsharingDBConstants::BOOKINGS,
			array(
			'id' => array('integer', $ilDB->nextID(ilRoomsharingDBConstants::BOOKINGS)),
			'date_from' => array('timestamp', $date_from),
			'date_to' => array('timestamp', $date_to),
			'room_id' => array('integer', $room_id),
			'pool_id' => array('integer', $this->pool_id),
			'user_id' => array('integer', $user_id),
			'subject' => array('text', $subject)
			)
		);

		return $ilDB->getLastInsertId();
	}

	/**
	 * Insert a booking attribute into the database.
	 *
	 * @param integer $insertedId
	 * @param integer $booking_attr_key
	 * @param string $booking_attr_value
	 * @return integer id of inserted booking attribute
	 */
	public function insertBookingAttribute($insertedId, $booking_attr_key, $booking_attr_value)
	{
		global $ilDB;
		$ilDB->insert(ilRoomsharingDBConstants::BOOKING_TO_ATTRIBUTE,
			array(
			'booking_id' => array('integer', $insertedId),
			'attr_id' => array('integer', $booking_attr_key),
			'value' => array('text', $booking_attr_value)
			)
		);

		return $ilDB->getLastInsertId();
	}

	/**
	 * Delete a booking from the database.
	 *
	 * @param integer $a_booking_id
	 */
	public function deleteBooking($a_booking_id)
	{
		global $ilDB;
		$ilDB->manipulate('DELETE FROM ' . ilRoomsharingDBConstants::BOOKINGS .
			' WHERE id = ' . $ilDB->quote($a_booking_id, 'integer'));
		$ilDB->manipulate('DELETE FROM ' . ilRoomsharingDBConstants::USER .
			' WHERE booking_id = ' . $ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Get all bookings related to a given sequence.
	 *
	 * @param integer $seq_id
	 * @return type return of $ilDB->query
	 */
	public function getAllBookingIdsForSequence($seq_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT id FROM ' . ilRoomsharingDBConstants::BOOKINGS .
				' WHERE seq = ' . $ilDB->quote($seq_id, 'integer') .
				' AND pool_id = ' . $ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Gets User and Sequence for a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $ilDB->query
	 */
	public function getSequenceAndUserForBooking($a_booking_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT seq_id, user_id  FROM ' . ilRoomsharingDBConstants::BOOKINGS .
				' WHERE id = ' . $ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets all bookings for a user.
	 *
	 * @param integer $user_id
	 * @return type return of $ilDB->query
	 */
	public function getBookingsForUser($user_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::BOOKINGS .
				' WHERE pool_id = ' . $ilDB->quote($this->pool_id, 'integer') .
				' AND user_id = ' . $ilDB->quote($user_id, 'integer') .
				' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
				' OR date_to >= "' . date('Y-m-d H:i:s') . '")' . ' ORDER BY date_from ASC');
	}

	/**
	 * Gets all Participants of a booking.
	 *
	 * @param integer $booking_id
	 * @return type return of $ilDB->query
	 */
	public function getParticipantsForBooking($booking_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT users.firstname AS firstname,' .
				' users.lastname AS lastname, users.login AS login,' .
				' users.usr_id AS id FROM ' . ilRoomsharingDBConstants::USER . ' user ' .
				' LEFT JOIN usr_data AS users ON users.usr_id = user.user_id' .
				' WHERE booking_id = ' . $ilDB->quote($booking_id, 'integer') .
				' ORDER BY users.lastname, users.firstname ASC');
	}

	/**
	 * Gets all attributes of a booking.
	 *
	 * @param integer $booking_id
	 * @return type return of $ilDB->query
	 */
	public function getAttributesForBooking($booking_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT value, attr.name AS name' .
				' FROM ' . ilRoomsharingDBConstants::BOOKING_TO_ATTRIBUTE . ' bta ' .
				' LEFT JOIN ' . ilRoomsharingDBConstants::BOOKING_ATTRIBUTES . ' attr ' .
				' ON attr.id = bta.attr_id' . ' WHERE booking_id = ' .
				$ilDB->quote($booking_id, 'integer'));
	}

	/**
	 * Gets all booking attributes.
	 *
	 * @return type return of $ilDB->query
	 */
	public function getAllBookingAttributes()
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::BOOKING_ATTRIBUTES .
				' WHERE pool_id = ' . $ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Gets all floorplans.
	 *
	 * @return type return of $ilDB->query
	 */
	public function getAllFloorplans()
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::FLOORPLANS .
				' WHERE pool_id = ' . $ilDB->quote($this->pool_id, 'integer') .
				' order by file_id DESC');
	}

	/**
	 * Gets a floorplan.
	 *
	 * @param integer $a_file_id
	 * @return type return of $ilDB->query
	 */
	public function getFloorplan($a_file_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::FLOORPLANS .
				' WHERE file_id = ' . $ilDB->quote($a_file_id, 'integer') . ' AND pool_id = '
				. $ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Inserts a floorplan into the database.
	 *
	 * @param integer $a_file_id
	 * @return type return of $ilDB->manipulate
	 */
	public function insertFloorplan($a_file_id)
	{
		global $ilDB;
		$ilDB->insert(ilRoomsharingDBConstants::FLOORPLANS,
			array(
			'file_id' => array('integer', $a_file_id),
			'pool_id' => array('integer', $this->pool_id)
			)
		);

		return $ilDB->getLastInsertId();
	}

	/**
	 * Deletes a floorplan from the database.
	 *
	 * @param integer $a_file_id
	 * @return type return of $ilDB->manipulate
	 */
	public function deleteFloorplan($a_file_id)
	{
		global $ilDB;
		return $ilDB->manipulate('DELETE FROM ' . ilRoomsharingDBConstants::FLOORPLANS .
				' WHERE file_id = ' . $ilDB->quote($a_file_id, 'integer'));
	}

	/**
	 * Deletes a participation from the database.
	 *
	 * @param integer $user_id
	 * @param integer $a_booking_id
	 * @return type return of $ilDB->manipulate
	 */
	public function deleteParticipation($user_id, $a_booking_id)
	{
		global $ilDB;
		return $ilDB->manipulate(
				'DELETE FROM ' . ilRoomsharingDBConstants::USER . ' WHERE user_id = ' .
				$ilDB->quote($user_id(), 'integer') .
				' AND booking_id = ' . $ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets participation for a user.
	 *
	 * @param integer $user_id
	 * @return type return of $ilDB->query
	 */
	public function getParticipationsForUser($user_id)
	{
		global $ilDB;
		return $ilDB->query(
				'SELECT booking_id FROM ' . ilRoomsharingDBConstants::USER .
				' WHERE user_id = ' . $ilDB->quote($user_id, 'integer'));
	}

	/**
	 * Gets a booking.
	 *
	 * @param integer $booking_id
	 * @return type return of $ilDB->query
	 */
	public function getBooking($booking_id)
	{
		global $ilDB;
		return $ilDB->query(
				'SELECT *' . ' FROM ' . ilRoomsharingDBConstants::BOOKINGS . ' WHERE id = ' .
				$ilDB->quote($booking_id, 'integer') .
				' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
				' OR date_to >= "' . date('Y-m-d H:i:s') . '")' .
				' ORDER BY date_from ASC');
	}

	/**
	 * Gets a user.
	 *
	 * @param integer $user_id
	 * @return type return of $ilDB->query
	 */
	public function getUser($user_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT firstname, lastname, login' . ' FROM usr_data' .
				' WHERE usr_id = ' . $ilDB->quote($user_id, 'integer'));
	}

	/**
	 * Gets a room.
	 *
	 * @param integer $room_id
	 * @return type return of $ilDB->query
	 */
	public function getRoom($room_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::ROOMS . ' WHERE id = ' .
				$ilDB->quote($room_id, 'integer'));
	}

	/**
	 * Get a room attribute.
	 *
	 * @param integer $attribute_id
	 * @return type return of $ilDB->query
	 */
	public function getRoomAttribute($attribute_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants::ROOM_ATTRIBUTES .
				' WHERE id = ' . $ilDB->quote($attribute_id, 'integer'));
	}

	/**
	 * Gets attributes for a room.
	 *
	 * @param integer $room_id
	 * @return type return of $ilDB->query
	 */
	public function getAttributesForRoom($room_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT id, att.name, count FROM ' .
				ilRoomsharingDBConstants::ROOM_TO_ATTRIBUTE . ' rta LEFT JOIN ' .
				ilRoomsharingDBConstants::ROOM_ATTRIBUTES . ' as att ON att.id = rta.att_id' .
				' WHERE room_id = ' . $ilDB->quote($room_id, 'integer') . ' ORDER BY att.name');
	}

	/**
	 * Gets all bookings for a room.
	 *
	 * @param integer $room_id
	 * @return type return of $ilDB->query
	 */
	public function getBookingsForRoom($room_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT * FROM ' . ilRoomsharingDBConstants:: BOOKINGS .
				' WHERE room_id = ' . $ilDB->quote($room_id, 'integer'));
	}

	/**
	 * Deletes attributes for a room.
	 *
	 * @param type $room_id
	 * @return type
	 */
	public function deleteAttributesForRoom($room_id)
	{
		global $ilDB;
		return $ilDB->manipulate('DELETE FROM ' . ilRoomsharingDBConstants::ROOM_TO_ATTRIBUTE .
				' WHERE room_id = ' . $ilDB->quote($room_id, 'integer'));
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
		global $ilDB;
		$ilDB->insert(ilRoomsharingDBConstants::ROOMS,
			array(
			'id' => array('integer', $ilDB->nextId(ilRoomsharingDBConstants::ROOMS)),
			'name' => array('text', $name),
			'type' => array('text', $type),
			'min_alloc' => array('integer', $min_alloc),
			'max_alloc' => array('integer', $max_alloc),
			'file_id' => array('integer', $file_id),
			'building_id' => array('integer', $building_id),
			'pool_id' => array('integer', $this->pool_id)
		));
		return $ilDB->getLastInsertId();
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
		global $ilDB;
		$ilDB->insert(ilRoomSharingDBConstants::ROOM_TO_ATTRIBUTE,
			array(
			'room_id' => array('integer', $room_id),
			'att_id' => array('integer', $attribute_id),
			'count' => array('integer', $count)
		));
	}

}
