<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

use ilRoomSharingDBConstants as dbc;

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
	 * @param array $a_room_ids
	 *        	ids of the rooms
	 * @return array room_id, att.name, count
	 */
	public function getAttributesForRooms($a_room_ids)
	{
		$st = $this->ilDB->prepare('SELECT room_id, att.name, count FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' as rta LEFT JOIN ' .
			dbc::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id WHERE '
			. $this->ilDB->in("room_id", $a_room_ids) . ' ORDER BY room_id, att.name');
		$set = $this->ilDB->execute($st, $a_room_ids);
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
			dbc::ROOMS_TABLE . ' WHERE pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer'));
		$valueRow = $this->ilDB->fetchAssoc($valueSet);
		$value = $valueRow ['value'];
		return $value;
	}

	public function getQueryStringForRoomsWithMathcingAttribute($a_attribute, $a_count)
	{
		return 'SELECT room_id FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE . ' ra ' .
			'LEFT JOIN ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' attr ON ra.att_id = attr.id WHERE name = ' . $this->ilDB->quote($a_attribute, 'text') .
			' AND count >= ' . $this->ilDB->quote($a_count, 'integer') .
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' ';
	}

	public function getAllRoomIds()
	{
		return 'SELECT id FROM ' . dbc::ROOMS_TABLE;
	}

	public function getMatchingRooms($a_roomsToCheck, $a_room_name, $a_room_seats)
	{
		$where_part = ' AND room.pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' ';

		if ($a_room_name || $a_room_name === "0")
		{
			$where_part = $where_part . ' AND name LIKE ' .
				$this->ilDB->quote('%' . $a_room_name . '%', 'text') . ' ';
		}
		if ($a_room_seats || $a_room_seats === 0.0)
		{
			$where_part = $where_part . ' AND max_alloc >= ' .
				$this->ilDB->quote($a_room_seats, 'integer') . ' ';
		}

		$st = $this->ilDB->prepare('SELECT room.id, name, max_alloc FROM ' .
			dbc::ROOMS_TABLE . ' room WHERE ' .
			$this->ilDB->in("room.id", array_keys($a_roomsToCheck)) . $where_part .
			' ORDER BY name');
		return $this->ilDB->execute($st, array_keys($a_roomsToCheck));
	}

	public function getAllAttributeNames()
	{
		$set = $this->ilDB->query('SELECT name FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
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
		$attributIdSet = $this->ilDB->query('SELECT id FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE name =' . $this->ilDB->quote($a_room_attribute, 'text') . ' AND pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer'));
		$attributIdRow = $this->ilDB->fetchAssoc($attributIdSet);
		$attributID = $attributIdRow ['id'];

		// get the max value of the attribut in this pool
		$valueSet = $this->ilDB->query('SELECT MAX(count) AS value FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
			dbc::ROOMS_TABLE . ' as room ON room.id = rta.room_id ' .
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
		$roomNameSet = $this->ilDB->query(' SELECT name FROM ' . dbc::ROOMS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_room_id, 'integer'));
		$roomNameRow = $this->ilDB->fetchAssoc($roomNameSet);
		return $roomNameRow ['name'];
	}

	/**
	 * Get the room-ids from all rooms that are booked in the given timerange.
	 * A specific room_id can be given if a single room should be queried (used for bookings).
	 *
	 * @param string $a_date_from
	 * @param string $a_date_to
	 * @param string $a_room_id
	 *        	(optional)
	 * @return array values = room ids booked in given range
	 */
	public function getRoomsBookedInDateTimeRange($a_date_from, $a_date_to, $a_room_id = null)
	{
		$roomQuery = '';
		if ($a_room_id)
		{
			$roomQuery = ' room_id = ' .
				$this->ilDB->quote($a_room_id, 'text') . ' AND ';
		}

		$query = 'SELECT DISTINCT room_id FROM ' . dbc::BOOKINGS_TABLE . ' WHERE ' .
			$roomQuery . ' (' . $this->ilDB->quote($a_date_from, 'timestamp') .
			' BETWEEN date_from AND date_to OR ' . $this->ilDB->quote($a_date_to, 'timestamp') .
			' BETWEEN date_from AND date_to OR date_from BETWEEN ' .
			$this->ilDB->quote($a_date_from, 'timestamp') . ' AND ' . $this->ilDB->quote($a_date_to,
				'timestamp') .
			' OR date_to BETWEEN ' . $this->ilDB->quote($a_date_from, 'timestamp') .
			' AND ' . $this->ilDB->quote($a_date_to, 'timestamp') . ')';

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
	 * @param array $a_booking_values
	 *        	Array with the values of the booking
	 * @param array $a_booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @return integer 1 = successful, -1 not successful
	 */
	public function insertBooking($a_booking_attr_values, $a_booking_values, $a_booking_participants)
	{
		global $ilUser;

		$this->ilDB->insert(dbc::BOOKINGS_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextID(dbc::BOOKINGS_TABLE)),
			'date_from' => array('timestamp', $a_booking_values ['from'] ['date'] . " " . $a_booking_values ['from'] ['time']),
			'date_to' => array('timestamp', $a_booking_values ['to'] ['date'] . " " . $a_booking_values ['to'] ['time']),
			'room_id' => array('integer', $a_booking_values ['room']),
			'pool_id' => array('integer', $this->pool_id),
			'user_id' => array('integer', $ilUser->getId()),
			'subject' => array('text', $a_booking_values ['subject']),
			'public_booking' => array('boolean', $a_booking_values ['public'] == 1)
			)
		);

		$insertedId = $this->ilDB->getLastInsertId();

		if ($insertedId == - 1)
		{
			return - 1;
		}

		$this->insertBookingAttributes($insertedId, $a_booking_attr_values);

		$this->insertBookingParticipants($insertedId, $a_booking_participants);

		return 1;
	}

	/**
	 * Method to insert booking attributes into the database.
	 *
	 * @param integer $a_insertedId
	 * @param array $a_booking_attr_values
	 *        	Array with the values of the booking-attributes
	 */
	private function insertBookingAttributes($a_insertedId, $a_booking_attr_values)
	{
		// Insert the attributes for the booking in the conjunction table
		foreach ($a_booking_attr_values as $booking_attr_key => $booking_attr_value)
		{
			// Only insert the attribute value, if a value was submitted by the user
			if ($booking_attr_value !== "")
			{
				$this->insertBookingAttribute($a_insertedId, $booking_attr_key, $booking_attr_value);
			}
		}
	}

	/**
	 * Method to insert booking participants into the database.
	 *
	 * @global ilUser $ilUser
	 * @param integer $a_insertedId
	 * @param array $a_booking_participants
	 *        	Array with the values of the booking-participants
	 */
	private function insertBookingParticipants($a_insertedId, $a_booking_participants)
	{
		global $ilUser;

		$booked_participants = array();

		// Insert the attributes for the booking in the conjunction table
		foreach ($a_booking_participants as $booking_participant_value)
		{
			// Only insert the attribute value, if a value was submitted by the user
			if ($booking_participant_value !== "" && $booking_participant_value != $ilUser->getLogin())
			{
				//Check if the user is already a participant of this booking
				//(avoids duplicate participations for one user in one booking)
				if (in_array($booking_participant_value, $booked_participants))
				{
					continue;
				}

				$booked_participants[] = $booking_participant_value;

				//Get the id of the participant (user) by the given username
				$booking_participant_id = $this->getUserIdByUsername($booking_participant_value);

				//Check if the id has a correct format
				if (ilRoomSharingNumericUtils::isPositiveNumber($booking_participant_id))
				{

					$this->insertBookingParticipant($a_insertedId, $booking_participant_id);
				}
			}
		}
	}

	/**
	 * Inserts a booking participant into the database.
	 *
	 * @param integer $a_insertedId
	 * @param integer $a_participantId
	 */
	public function insertBookingParticipant($a_insertedId, $a_participantId)
	{
		$this->ilDB->insert(dbc::BOOKING_TO_USER_TABLE,
			array(
			'booking_id' => array('integer', $a_insertedId),
			'user_id' => array('integer', $a_participantId)
			)
		);
	}

	/**
	 * Inserts a booking attribute into the database.
	 *
	 * @param integer $a_insertedId
	 * @param integer $a_booking_attr_key
	 * @param string $a_booking_attr_value
	 */
	public function insertBookingAttribute($a_insertedId, $a_booking_attr_key, $a_booking_attr_value)
	{
		global $ilDB;
		$ilDB->insert(dbc::BOOKING_TO_ATTRIBUTE_TABLE,
			array(
			'booking_id' => array('integer', $a_insertedId),
			'attr_id' => array('integer', $a_booking_attr_key),
			'value' => array('text', $a_booking_attr_value)
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
		$this->ilDB->manipulate('DELETE FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
		$this->ilDB->manipulate('DELETE FROM ' . dbc::USER_TABLE .
			' WHERE booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Get all bookings related to a given sequence.
	 *
	 * @param integer $a_seq_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingIdsForSequence($a_seq_id)
	{
		return $this->ilDB->query('SELECT id FROM ' . dbc::BOOKINGS_TABLE .
				' WHERE seq = ' . $this->ilDB->quote($a_seq_id, 'integer') .
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
		return $this->ilDB->query('SELECT seq_id, user_id  FROM ' . dbc::BOOKINGS_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets all bookings for a user.
	 *
	 * @param integer $a_user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBookingsForUser($a_user_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc::BOOKINGS_TABLE .
				' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
				' AND user_id = ' . $this->ilDB->quote($a_user_id, 'integer') .
				' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
				' OR date_to >= "' . date('Y-m-d H:i:s') . '")' . ' ORDER BY date_from ASC');
	}

	/**
	 * Gets all Participants of a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipantsForBooking($a_booking_id)
	{
		return $this->ilDB->query('SELECT users.firstname AS firstname,' .
				' users.lastname AS lastname, users.login AS login,' .
				' users.usr_id AS id FROM ' . dbc::USER_TABLE . ' user ' .
				' LEFT JOIN usr_data AS users ON users.usr_id = user.user_id' .
				' WHERE booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
				' ORDER BY users.lastname, users.firstname ASC');
	}

	/**
	 * Gets all attributes of a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAttributesForBooking($a_booking_id)
	{
		return $this->ilDB->query('SELECT value, attr.name AS name' .
				' FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE . ' bta ' .
				' LEFT JOIN ' . dbc::BOOKING_ATTRIBUTES_TABLE . ' attr ' .
				' ON attr.id = bta.attr_id' . ' WHERE booking_id = ' .
				$this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets all booking attributes.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingAttributes()
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
				' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Gets all floorplans.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllFloorplans()
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc::FLOORPLANS_TABLE .
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
		return $this->ilDB->query('SELECT * FROM ' . dbc::FLOORPLANS_TABLE .
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
		$this->ilDB->insert(dbc::FLOORPLANS_TABLE,
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
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::FLOORPLANS_TABLE .
				' WHERE file_id = ' . $this->ilDB->quote($a_file_id, 'integer'));
	}

	/**
	 * Deletes a participation from the database.
	 *
	 * @param integer $a_user_id
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->manipulate
	 */
	public function deleteParticipation($a_user_id, $a_booking_id)
	{
		return $this->ilDB->manipulate(
				'DELETE FROM ' . dbc::USER_TABLE . ' WHERE user_id = ' .
				$this->ilDB->quote($a_user_id(), 'integer') .
				' AND booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Gets participation for a user.
	 *
	 * @param integer $a_user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipationsForUser($a_user_id)
	{
		return $this->ilDB->query(
				'SELECT booking_id FROM ' . dbc::USER_TABLE .
				' WHERE user_id = ' . $this->ilDB->quote($a_user_id, 'integer'));
	}

	/**
	 * Gets a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBooking($a_booking_id)
	{
		return $this->ilDB->query(
				'SELECT *' . ' FROM ' . dbc::BOOKINGS_TABLE . ' WHERE id = ' .
				$this->ilDB->quote($a_booking_id, 'integer') .
				' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
				' OR date_to >= "' . date('Y-m-d H:i:s') . '")' .
				' ORDER BY date_from ASC');
	}

	/**
	 * Gets a user by its id.
	 *
	 * @param integer $a_user_id
	 * @return type return of $ilDB->query
	 */
	public function getUserById($a_user_id)
	{
		global $ilDB;
		return $ilDB->query('SELECT firstname, lastname, login' . ' FROM usr_data' .
				' WHERE usr_id = ' . $ilDB->quote($a_user_id, 'integer'));
	}

	/**
	 * Gets a user-id by its user-name.
	 *
	 * @param string $a_user_name
	 * @return type return of $ilDB->query
	 */
	public function getUserIdByUsername($a_user_name)
	{
		return ilObjUser::_lookupId($a_user_name);
	}

	/**
	 * Gets a room.
	 *
	 * @param integer $a_room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getRoom($a_room_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc::ROOMS_TABLE . ' WHERE id = ' .
				$this->ilDB->quote($a_room_id, 'integer'));
	}

	/**
	 * Get a room attribute.
	 *
	 * @param integer $a_attribute_id
	 * @return type return of $this->ilDB->query
	 */
	public function getRoomAttribute($a_attribute_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_attribute_id, 'integer'));
	}

	/**
	 * Gets attributes for a room.
	 *
	 * @param integer $a_room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAttributesForRoom($a_room_id)
	{
		return $this->ilDB->query('SELECT id, att.name, count FROM ' .
				dbc::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
				dbc::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id' .
				' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') . ' ORDER BY att.name');
	}

	/**
	 * Gets all bookings for a room.
	 *
	 * @param integer $a_room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBookingsForRoom($a_room_id)
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc:: BOOKINGS_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer'));
	}

	/**
	 * Deletes attributes for a room.
	 *
	 * @param type $a_room_id
	 * @return type
	 */
	public function deleteAttributesForRoom($a_room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer'));
	}

	/**
	 * Inserts a room into the database.
	 *
	 * @param string $a_name
	 * @param string $a_type
	 * @param integer $a_min_alloc
	 * @param integer $a_max_alloc
	 * @param integer $a_file_id
	 * @param integer $a_building_id
	 * @return integer id of the room
	 */
	public function insertRoom($a_name, $a_type, $a_min_alloc, $a_max_alloc, $a_file_id, $a_building_id)
	{
		$this->ilDB->insert(dbc::ROOMS_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextId(dbc::ROOMS_TABLE)),
			'name' => array('text', $a_name),
			'type' => array('text', $a_type),
			'min_alloc' => array('integer', $a_min_alloc),
			'max_alloc' => array('integer', $a_max_alloc),
			'file_id' => array('integer', $a_file_id),
			'building_id' => array('integer', $a_building_id),
			'pool_id' => array('integer', $this->pool_id)
		));
		return $this->ilDB->getLastInsertId();
	}

	/**
	 * Inserts an attribute to room relation into the database.
	 *
	 * @param integer $a_room_id
	 * @param integer $a_attribute_id
	 * @param integer $a_count
	 */
	public function insertAttributeForRoom($a_room_id, $a_attribute_id, $a_count)
	{
		$this->ilDB->insert(ilRoomSharingDBConstants::ROOM_TO_ATTRIBUTE_TABLE,
			array(
			'room_id' => array('integer', $a_room_id),
			'att_id' => array('integer', $a_attribute_id),
			'count' => array('integer', $a_count)
		));
	}

	/**
	 * Gets all Room Agrements from the Database
	 *
	 * @return type return of $ilDB->query
	 */
	public function getRoomAgreementFromDatabase()
	{
		return $this->ilDB->query('SELECT * FROM ' . dbc::POOLS_TABLE .
				' WHERE id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' order by rooms_agreement DESC');
	}

	/**
	 * Gets the calendar-id of the current RoomSharing-Pool
	 *
	 * @return integer calendar-id
	 */
	public function getCalendarIdFromDatabase()
	{
		$set = $this->ilDB->query('SELECT calendar_id FROM ' . dbc::POOLS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
		$row = $this->ilDB->fetchAssoc($set);
		return $row["calendar_id"];
	}

	/**
	 * Updates rep_robj_xrs_pools with an new calendar-id.
	 *
	 * Typically only called once per pool.
	 *
	 * @param type $a_cal_id
	 * @return type
	 */
	public function setCalendarId($a_cal_id)
	{
		return $this->ilDB->manipulate('UPDATE ' . dbc::POOLS_TABLE .
				' SET calendar_id = ' . $this->ilDB->quote($a_cal_id, 'integer') .
				' WHERE id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

}
