<?php

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

	public function
	__construct($a_pool_id)
	{
	$this->pool_id = $a_pool_id;
		}

		public function setPoolId(
	$a_poolId)
	{
	$this->pool_id =  $a_poolId;
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
	$st = $ilDB->prepare('SELECT room_id, att.name, count FROM rep_robj_xrs_room_attr '.
		' LEFT JOIN rep_robj_xrs_rattr as att ON att.id = rep_robj_xrs_room_attr.att_id' .
		' WHERE ' . $ilDB->in("room_id", $room_ids) .
		' ORDER BY room_id, att.name' );
			$set =  $ilDB->execute ( $st, $room_ids);
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
	$valueSet = $ilDB->query
('SELECT MAX(max_alloc) AS value FROM rep_robj_xrs_rooms ' .
	' WHERE pool_id = ' . $ilDB->quote($this->pool_id, 'integer'));
		$valueRow = $ilDB->fetchAssoc($valueSet );
				$value = $valueRow ['value'];
		return $value;
		}

		public function getQueryStringForRoomsWithMathcingAttribute($attribute, $count)
	{
	global $ilDB;
	return 'SELECT room_id FROM rep_robj_xrs_room_attr ra ' .
	'LEFT JOIN rep_robj_xrs_rattr attr ON ra.att_id = attr.id WHERE name = ' .
	$ilDB-> quote($attribute, 'text') .
	' AND count >= ' . $ilDB->quote($count, 'integer') .
	' AND pool_id = ' . $ilDB->quote($this->pool_id, 'integer') . ' ';
	}

	public function getAllRoomIds()
	{
	return   'SELECT id FROM rep_robj_xrs_rooms';
	}
	public function getMatchingRooms($roomsToCheck, $room_name, $room_seats)
	{
	global $ilDB;
	$where_part = ' AND room.pool_id = ' . $ilDB-> quote($this ->pool_id, 'integer') . ' ';

	if ($room_name || $room_name === "0")
		{
		$where_part  = $where_part . ' AND name LIKE ' .
		$ilDB->quote ( '%' . $room_name . '%', 'text') . ' ';
		}
		if ($room_seats || $room_seats === 0.0)
			{
				$where_part = $where_part . ' AND max_alloc >= ' .
		$ilDB->quote ( $room_seats, 'integer') . ' ';
		}

		$st = $ilDB->prepare ( 'SELECT room.id, name, max_alloc FROM rep_robj_xrs_rooms room WHERE '  .
		$ilDB->in("room.id",  array_keys($roomsToCheck)) . $where_part .
		' ORDER BY name');
		return $ilDB->execute($st, array_keys($roomsToCheck));
		}

		public function getAllAttributeNames()
	{
	global $ilDB;
	$set = $ilDB->query('SELECT name FROM rep_robj_xrs_rattr ORDER BY name'
);
	$attributes = array();
	while($row = $ilDB->fetchAssoc($set))
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
		$attributIdSet = $ilDB->query('SELECT id FROM rep_robj_xrs_rattr WHERE name =' .
		$ilDB->quote($a_room_attribute, 'text') .
		' AND pool_id = ' . $ilDB->quote($this->pool_id, 'integer'));
		$attributIdRow = $ilDB->fetchAssoc($attributIdSet);
		$attributID = $attributIdRow ['id'];

	// get the max value of the attribut in this pool
	$valueSet = $ilDB->query(  'SELECT MAX(count) AS value FROM rep_robj_xrs_room_attr ' .
	' LEFT JOIN rep_robj_xrs_rooms as room ON room.id = rep_robj_xrs_room_attr.room_id ' .
		' WHERE att_id =' . $ilDB->quote ( $attributID, 'integer') .
		' AND pool_id =' . $ilDB->quote($this->pool_id, 'integer' ) );
		$valueRow = $ilDB->fetchAssoc( $valueSet);
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
	$roomNameSet = $ilDB->query(' SELECT name FROM rep_robj_xrs_rooms' .
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

	$query =   'SELECT DISTINCT room_id FROM rep_robj_xrs_bookings WHERE ' . $roomQuery .
		' (' . $ilDB->quote($date_from, 'timestamp' ) . ' BETWEEN date_from AND date_to OR '  .
	$ilDB->quote($date_to, 'timestamp') .
		' BETWEEN date_from AND date_to OR date_from BETWEEN ' . $ilDB->quote( $date_from, 'timestamp' ) .
		' AND ' . $ilDB->quote($date_to, 'timestamp') . ' OR date_to BETWEEN ' . $ilDB->quote($date_from, 'timestamp') .
		' AND ' . $ilDB->quote($date_to, 'timestamp') . ')';

		$set = $ilDB->query($query); $res_room =  array ( );
		while ($row = $ilDB->fetchAssoc($set))
			{ $res_room [] = $row ['room_id'];
		}
			return $res_room;
			}

		public function insertBooking($room_id, $user_id, $subject, $date_from, $date_to)
		{
		global $ilDB;
		$nextId = $ilDB->nextID('rep_robj_xrs_bookings');
		$addBookingQuery = "INSERT INTO rep_robj_xrs_bookings" .
		" (id,date_from, date_to, room_id, pool_id, user_id, subject)" . " VALUES (" .
		$nextId . "," . " " . $ilDB->quote($date_from, 'timestamp') . "," . " " .
		$ilDB->quote($date_to, 'timestamp') . "," . " " . $ilDB->quote($room_id, 'integer') .
		"," . " " . $ilDB->quote($this->pool_id, 'integer') . "," . " " .
		$ilDB->quote($user_id, 'integer') . "," . " " . $ilDB->quote($subject, 'text') . ")";

		if($ilDB->manipulate($addBookingQuery) === - 1)
		{
		$nextId = - 1;
		}

		return $nextId;
		}

		public function insertBookingAttribute($insertedId, $booking_attr_key, $booking_attr_value)
		{
		global $ilDB;
		$ilDB->manipulate(
		"INSERT INTO rep_robj_xrs_book_attr" . " (booking_id, attr_id, value)" .
		" VALUES (" . $ilDB->quote($insertedId, 'integer') . "," . " " .
		$ilDB->quote($booking_attr_key, 'integer') . "," . " " .
		$ilDB->quote($booking_attr_value, 'text') . ")");
		}

		public function deleteBooking($a_booking_id)
		{
		global $ilDB;
		$ilDB->query('DELETE FROM rep_robj_xrs_bookings' .
		' WHERE id = ' . $ilDB->quote($a_booking_id, 'integer'));
		$ilDB->query('DELETE FROM rep_robj_xrs_book_user' .
		' WHERE booking_id = ' . $ilDB->quote($a_booking_id, 'integer'));
		}

		public function getAllBookingIdsForSequence($seq_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT id FROM rep_robj_xrs_bookings' .
		' WHERE seq = ' . $ilDB->quote($seq_id, 'integer') .
		' AND pool_id = ' . $ilDB->quote($this->pool_id, 'integer'));
		}

		public function getSequenceAndUserForBooking($a_booking_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT seq_id, user_id  FROM rep_robj_xrs_bookings' .
		' WHERE id = ' . $ilDB->quote($a_booking_id, 'integer'));
		}

		public function getBookingsForUser($user_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT * FROM rep_robj_xrs_bookings' .
		' WHERE pool_id = ' . $ilDB->quote($this->pool_id, 'integer') .
		' AND user_id = ' . $ilDB->quote($user_id, 'integer') .
		' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
		' OR date_to >= "' . date('Y-m-d H:i:s') . '")' . ' ORDER BY date_from ASC');
		}

		public function getParticipantsForBooking($booking_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT users.firstname AS firstname,' .
		' users.lastname AS lastname, users.login AS login,' .
		' users.usr_id AS id FROM rep_robj_xrs_book_user' .
		' LEFT JOIN usr_data AS users ON users.usr_id = rep_robj_xrs_book_user.user_id' .
		' WHERE booking_id = ' . $ilDB->quote($booking_id, 'integer') .
		' ORDER BY users.lastname, users.firstname ASC');
		}

		public function getAttributesForBooking($booking_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT value, attr.name AS name' .
		' FROM rep_robj_xrs_book_attr' .
		' LEFT JOIN rep_robj_xrs_battr AS attr' .
		' ON attr.id = rep_robj_xrs_book_attr.attr_id' .
		' WHERE booking_id = ' . $ilDB->quote($booking_id, 'integer'));
		}

		public function getAllBookingAttributes()
		{
		global $ilDB;
		return $ilDB->query('SELECT * FROM rep_robj_xrs_battr' .
		' WHERE pool_id = ' . $ilDB->quote($this->pool_id, 'integer'));
		}

		public function getAllFloorplans()
		{
		global $ilDB;
		return $ilDB->query('SELECT * FROM rep_robj_xrs_fplans WHERE pool_id = '
		. $ilDB->quote($this->pool_id, 'integer') . ' order by file_id DESC');
		}

		public function getFloorplan($a_file_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT * FROM rep_robj_xrs_fplans WHERE file_id = '
		. $ilDB->quote($a_file_id, 'integer') . ' AND pool_id = '
		. $ilDB->quote($this->pool_id, 'integer'));
		}

		public function insertFloorplan($a_file_id)
		{
		global $ilDB;
		return $ilDB->manipulate('INSERT INTO rep_robj_xrs_fplans'
		. ' (file_id, pool_id)' . ' VALUES (' . $ilDB->quote($a_file_id, 'integer') . ','
		. $ilDB->quote($this->pool_id, 'integer') . ')');
		}

		public function deleteFloorplan($a_file_id)
		{
		global $ilDB;
		return $ilDB->manipulate('DELETE FROM rep_robj_xrs_fplans' .
		' WHERE file_id = ' . $ilDB->quote($a_file_id, 'integer'));
		}

		public function deleteParticipation($user_id, $a_booking_id)
		{
		global $ilDB;
		return $ilDB->manipulate(
		'DELETE FROM rep_robj_xrs_book_user' . ' WHERE user_id = ' .
		$ilDB->quote($user_id(), 'integer') .
		' AND booking_id = ' . $ilDB->quote($a_booking_id, 'integer'));
		}

		public function getParticipationsForUser($user_id)
		{
		global $ilDB;
		return $ilDB->query(
		'SELECT booking_id FROM rep_robj_xrs_book_user' .
		' WHERE user_id = ' . $ilDB->quote($user_id, 'integer'));
		}

		public function getBooking($booking_id)
		{
		global $ilDB;
		return $ilDB->query(
		'SELECT *' . ' FROM rep_robj_xrs_bookings' . ' WHERE id = ' .
		$ilDB->quote($booking_id, 'integer') .
		' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
		' OR date_to >= "' . date('Y-m-d H:i:s') . '")' .
		' ORDER BY date_from ASC');
		}

		public function getUser($user_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT firstname, lastname, login' . ' FROM usr_data' .
		' WHERE usr_id = ' . $ilDB->quote($user_id, 'integer'));
		}

		public function getRoom($room_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT * FROM rep_robj_xrs_rooms WHERE id = ' .
		$ilDB->quote($room_id, 'integer'));
		}

		public function getRoomAttribute($attribute_id)
		{
		global $ilDB;
		return $ilDB->query('SELECT * FROM rep_robj_xrs_rattr WHERE id = ' .
		$ilDB->quote($attribute_id, 'integer'));
		}

		public function getAttributesForRoom($room_id)
		{
		global $ilDB;
		return $ilDB->query(
		'SELECT id, att.name, count FROM rep_robj_xrs_room_attr ' .
		' LEFT JOIN rep_robj_xrs_rattr as att' .
		' ON att.id = rep_robj_xrs_room_attr.att_id' .
		' WHERE room_id = ' . $ilDB->quote($room_id, 'integer') .
		' ORDER BY att.name');
		}

		public function getBookingsForRoom($room_id)
		{
		global $ilDB;
		return $ilDB->query(
		'SELECT * FROM rep_robj_xrs_bookings WHERE room_id = ' .
		$ilDB->quote($room_id, 'integer'));
		}

		public function deleteAttributesForRoom($room_id)
		{
		global $ilDB;
		return $ilDB->manipulate(
		'DELETE FROM rep_robj_xrs_room_attr WHERE room_id = ' .
		$ilDB->quote($room_id, 'integer'));
		}

		}




















