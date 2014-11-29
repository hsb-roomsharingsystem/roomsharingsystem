<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

use ilRoomSharingDBConstants as dbc;

/**
 * Class for database queries.
 *
 * @author Malte Ahlering
 *
 * @property ilDB $ilDB
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
		global $ilDB; // Database-Access-Class
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
		$res_attribute = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$res_attribute [] = $row;
		}
		return $res_attribute;
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

	public function getRoomIdsWithMatchingAttribute($a_attribute, $a_count)
	{
		$queryString = 'SELECT room_id FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE . ' ra ' .
			'LEFT JOIN ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' attr ON ra.att_id = attr.id WHERE name = ' . $this->ilDB->quote($a_attribute, 'text') .
			' AND count >= ' . $this->ilDB->quote($a_count, 'integer') .
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' ';

		$matching = array();
		$resAttr = $this->ilDB->query($queryString);
		while ($row = $this->ilDB->fetchAssoc($resAttr))
		{
			$matching[] = $row['room_id'];
		}
		return $matching;
	}

	public function getAllRoomIds()
	{
		$resRoomIds = $this->ilDB->query('SELECT id FROM ' . dbc::ROOMS_TABLE);
		$room_ids = array();
		while ($row = $this->ilDB->fetchAssoc($resRoomIds))
		{
			$room_ids[] = $row['room_id'];
		}
		return $room_ids;
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
		$set = $this->ilDB->execute($st, array_keys($a_roomsToCheck));

		$res_room = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$res_room [] = $row;
		}
		return $res_room;
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
			'public_booking' => array('boolean', $a_booking_values ['book_public'] == '1'),
			'bookingcomment' => array('text', $a_booking_values ['comment'])
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
				$this->insertBookingAttributeAssign($a_insertedId, $booking_attr_key, $booking_attr_value);
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
	public function insertBookingAttributeAssign($a_insertedId, $a_booking_attr_key,
		$a_booking_attr_value)
	{
		$this->ilDB->insert(dbc::BOOKING_TO_ATTRIBUTE_TABLE,
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
		$this->ilDB->manipulate('DELETE FROM ' . dbc::BOOK_USER_TABLE .
			' WHERE booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Delete bookings from the database.
	 *
	 * @param array $booking_ids
	 */
	public function deleteBookings($booking_ids)
	{
		$st = $this->ilDB->prepareManip('DELETE FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE ' . $this->ilDB->in("id", $booking_ids));
		$this->ilDB->execute($st, $booking_ids);
		$st2 = $this->ilDB->prepareManip('DELETE FROM ' . dbc::USER_TABLE .
			' WHERE ' . $this->ilDB->in("booking_id", $booking_ids));
		$this->ilDB->execute($st2, $booking_ids);
	}

	/**
	 * Get all bookings related to a given sequence.
	 *
	 * @param integer $a_seq_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingIdsForSequence($a_seq_id)
	{
		$set = $this->ilDB->query('SELECT id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE seq = ' . $this->ilDB->quote($a_seq_id, 'integer') .
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));

		$booking_ids = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$booking_ids[] = $row;
		}
		return $booking_ids;
	}

	/**
	 * Gets User and Sequence for a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getSequenceAndUserForBooking($a_booking_id)
	{
		$set = $this->ilDB->query('SELECT seq_id, user_id  FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
		if ($this->ilDB->numRows($set) > 0)
		{
			$result = $this->ilDB->fetchAssoc($set);
		}
		else
		{
			$result = NULL;
		}
		return $result;
	}

	/**
	 * Gets all bookings for a user.
	 *
	 * @param integer $a_user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBookingsForUser($a_user_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
			' AND user_id = ' . $this->ilDB->quote($a_user_id, 'integer') .
			' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
			' OR date_to >= "' . date('Y-m-d H:i:s') . '")' . ' ORDER BY date_from ASC');
		$bookings = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$bookings[] = $row;
		}
		return $bookings;
	}

	/**
	 * Gets all Participants of a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipantsForBooking($a_booking_id)
	{
		$set = $this->ilDB->query('SELECT users.firstname AS firstname,' .
			' users.lastname AS lastname, users.login AS login,' .
			' users.usr_id AS id FROM ' . dbc::BOOK_USER_TABLE . ' user ' .
			' LEFT JOIN usr_data AS users ON users.usr_id = user.user_id' .
			' WHERE booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' ORDER BY users.lastname, users.firstname ASC');
		$participants = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$participants[] = $row;
		}
		return $participants;
	}

	/*
	 * Gets only the usernames of the participants of a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipantsForBookingShort($a_booking_id)
	{
		$set = $this->ilDB->query('SELECT  users.login AS login' .
			' FROM ' . dbc::USER_TABLE . ' participants ' .
			' INNER JOIN usr_data AS users ON users.usr_id = participants.user_id' .
			' WHERE booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' ORDER BY users.lastname, users.firstname ASC');
		$participants = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$participants[] = $row['login'];
		}
		return $participants;
	}

	/**
	 * Gets all attributes of a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAttributesForBooking($a_booking_id)
	{
		$set = $this->ilDB->query('SELECT value, attr.name AS name' .
			' FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE . ' bta ' .
			' LEFT JOIN ' . dbc::BOOKING_ATTRIBUTES_TABLE . ' attr ' .
			' ON attr.id = bta.attr_id' . ' WHERE booking_id = ' .
			$this->ilDB->quote($a_booking_id, 'integer'));

		$attributes = array();
		while ($attributesRow = $this->ilDB->fetchAssoc($set))
		{
			$attributes[] = $attributesRow;
		}
		return $attributes;
	}

	/**
	 * Gets all booking attributes.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingAttributes()
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer')
			. ' ORDER BY name ASC');

		$attributesRows = array();
		while ($attributesRow = $this->ilDB->fetchAssoc($set))
		{
			$attributesRows [] = $attributesRow;
		}

		return $attributesRows;
	}

	/**
	 * Gets all floorplans.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllFloorplans()
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::FLOORPLANS_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
			' order by file_id DESC');

		$floorplans = array();
		$row = $this->ilDB->fetchAssoc($set);
		while ($row)
		{
			$mobj = new ilObjMediaObject($row['file_id']);
			$row["title"] = $mobj->getTitle();
			$floorplans [] = $row;
			$row = $this->ilDB->fetchAssoc($set);
		}

		return $floorplans;
	}

	/**
	 * Gets a floorplan.
	 *
	 * @param integer $a_file_id
	 * @return type return of $this->ilDB->query
	 */
	public function getFloorplan($a_file_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::FLOORPLANS_TABLE .
			' WHERE file_id = ' . $this->ilDB->quote($a_file_id, 'integer') . ' AND pool_id = '
			. $this->ilDB->quote($this->pool_id, 'integer'));

		$floorplan = array();
		$row = $this->ilDB->fetchAssoc($set);
		while ($row)
		{
			$floorplan [] = $row;
			$row = $this->ilDB->fetchAssoc($set);
		}
		return $floorplan;
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
	 * Delete floorplan - room association if floorplan will be deleted.
	 * @param floorplan_id
	 * @return type
	 */
	public function deleteFloorplanRoomAssociation($a_file_id)
	{
		return $this->ilDB->manipulate('UPDATE ' . dbc::ROOMS_TABLE .
				' SET building_id = 0 WHERE building_id = ' .
				$this->ilDB->quote($a_file_id, 'integer'));
	}

	public function getRoomsWithFloorplan($a_file_id)
	{
		$set = $this->ilDB->query('SELECT name FROM ' . dbc::ROOMS_TABLE .
			' WHERE building_id = ' . $this->ilDB->quote($a_file_id, 'integer'));
		$rooms = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$rooms[] = $row;
		}
		return $rooms;
		//return $this->ilDB->fetchAssoc($set);
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
				'DELETE FROM ' . dbc::BOOK_USER_TABLE . ' WHERE user_id = ' .
				$this->ilDB->quote($a_user_id(), 'integer') .
				' AND booking_id = ' . $this->ilDB->quote($a_booking_id, 'integer'));
	}

	/**
	 * Deletes a participation from the database.
	 *
	 * @param integer $a_user_id
	 * @param array $a_booking_ids
	 */
	public function deleteParticipations($a_user_id, $a_booking_ids)
	{
		$st = $this->ilDB->prepareManip('DELETE FROM ' . dbc::USER_TABLE .
			' WHERE user_id = ' . $this->ilDB->quote($a_user_id, 'integer') .
			' AND ' . $this->ilDB->in("booking_id", $a_booking_ids));
		$this->ilDB->execute($st, $a_booking_ids);
	}

	/**
	 * Gets participation for a user.
	 *
	 * @param integer $a_user_id
	 * @return type return of $this->ilDB->query
	 */
	public function getParticipationsForUser($a_user_id)
	{
		$set = $this->ilDB->query(
			'SELECT booking_id FROM ' . dbc::BOOK_USER_TABLE .
			' WHERE user_id = ' . $this->ilDB->quote($a_user_id, 'integer'));

		$participations = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$participations[] = $row;
		}
		return $participations;
	}

	/**
	 * Gets a booking.
	 *
	 * @param integer $a_booking_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBooking($a_booking_id)
	{
		$set = $this->ilDB->query('SELECT *' . ' FROM ' . dbc::BOOKINGS_TABLE . ' WHERE id = ' .
			$this->ilDB->quote($a_booking_id, 'integer') .
			' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
			' OR date_to >= "' . date('Y-m-d H:i:s') . '")' .
			' ORDER BY date_from ASC');

		$booking = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$booking[] = $row;
		}
		return $booking;
	}

	/**
	 * Gets a user by its id.
	 *
	 * @param integer $a_user_id
	 * @return type return of $ilDB->query
	 */
	public function getUserById($a_user_id)
	{
		$set = $this->ilDB->query('SELECT firstname, lastname, login' . ' FROM usr_data' .
			' WHERE usr_id = ' . $this->ilDB->quote($a_user_id, 'integer'));
		return $this->ilDB->fetchAssoc($set);
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
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::ROOMS_TABLE . ' WHERE id = ' .
			$this->ilDB->quote($a_room_id, 'integer'));

		return $this->ilDB->fetchAssoc($set);
	}

	/**
	 * Get a room attribute.
	 *
	 * @param integer $a_attribute_id
	 * @return type return of $this->ilDB->query
	 */
	public function getRoomAttribute($a_attribute_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_attribute_id, 'integer'));
		return $this->ilDB->fetchAssoc($set);
	}

	/**
	 * Gets attributes for a room.
	 *
	 * @param integer $a_room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getAttributesForRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT id, att.name, count FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
			dbc::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id' .
			' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') . ' ORDER BY att.name');
		$attributes = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$attributes[] = $row;
		}
		return $attributes;
	}

	/**
	 * Gets all bookings for a room.
	 *
	 * @param integer $a_room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getBookingsForRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc:: BOOKINGS_TABLE .
			' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer'));
		$bookings = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$bookings[] = $row;
		}
		return $bookings;
	}

	/**
	 * Gets all actual bookings for a room.
	 *
	 * @param integer $a_room_id
	 * @return type return of $this->ilDB->query
	 */
	public function getActualBookingsForRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc:: BOOKINGS_TABLE .
			' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') .
			' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
			' OR date_to >= "' . date('Y-m-d H:i:s') . '")' . ' ORDER BY date_from ASC');
		$bookings = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$bookings[] = $row;
		}
		return $bookings;
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
	 * Returns the file id for the room agreement of a certain pool.
	 *
	 * @return type return of $ilDB->query
	 */
	public function getRoomAgreementId()
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::POOLS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' order by rooms_agreement DESC');

		$row = $this->ilDB->fetchAssoc($set);
		return $row["rooms_agreement"];
	}

	/**
	 * Gets the calendar-id of the current RoomSharing-Pool
	 *
	 * @return integer calendar-id
	 */
	public function getCalendarId()
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

	/**
	 * Updates room properties in the database.
	 *
	 * @param integer $a_id
	 * @param text $a_name
	 * @param text $a_type
	 * @param integer $a_min_alloc
	 * @param integer $a_max_alloc
	 * @param integer $a_file_id
	 * @param integer $a_building_id
	 * @return integer return of $this->ilDB->update
	 */
	public function updateRoomProperties($a_id, $a_name, $a_type, $a_min_alloc, $a_max_alloc,
		$a_file_id, $a_building_id)
	{
		$fields = array(
			"name" => array("text", $a_name),
			"type" => array("text", $a_type),
			"min_alloc" => array("integer", $a_min_alloc),
			"max_alloc" => array("integer", $a_max_alloc),
			"file_id" => array("integer", $a_file_id),
			"building_id" => array("integer", $a_building_id)
		);
		$where = array(
			"id" => array("integer", $a_id)
		);
		return $this->ilDB->update(dbc::ROOMS_TABLE, $fields, $where);
	}

	public function getClasses()
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASSES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
		$classes = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$classes[] = $row;
		}
		return $classes;
	}

	public function getClassById($a_class_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASSES_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_class_id, 'integer'));
		$row = $this->ilDB->fetchAssoc($set);

		return $row;
	}

	public function getPrivilegesOfClass($a_class_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASS_PRIVILEGES_TABLE .
			' WHERE class_id = ' . $this->ilDB->quote($a_class_id, 'integer'));

		$row = $this->ilDB->fetchAssoc($set);
		//Remove class_id from resultlist, so that only the privileges are in the array
		unset($row['class_id']);
		return $row;
	}

	public function setLockedClasses($a_class_ids)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber(count($a_class_ids)))
		{
			$positive_where = "";
			$negative_where = "";
			foreach ($a_class_ids as $class_id => $val)
			{
				$positive_where .= " OR id = " . $this->ilDB->quote($class_id, 'integer');
				$negative_where .= " AND id != " . $this->ilDB->quote($class_id, 'integer');
			}
			//Cut the where strings, to avoid e.g. WHERE OR id=1 OR id=2
			if (strlen($positive_where) > 0)
			{
				$positive_where = substr($positive_where, 3);
			}
			if (strlen($negative_where) > 0)
			{
				$negative_where = substr($negative_where, 4);
			}
			$this->ilDB->manipulate('UPDATE ' . dbc::CLASSES_TABLE .
				' SET locked = 1 WHERE ' . $positive_where);
			$this->ilDB->manipulate('UPDATE ' . dbc::CLASSES_TABLE .
				' SET locked = 0 WHERE ' . $negative_where);
		}
		else
		{
			$this->ilDB->manipulate('UPDATE ' . dbc::CLASSES_TABLE .
				' SET locked = 0');
		}
	}

	public function getAssignedClassesForUser($a_user_id, $a_user_role_ids)
	{
		$set = $this->ilDB->query('SELECT class_id FROM ' . dbc::CLASS_USER_TABLE .
			' WHERE user_id = ' . $this->ilDB->quote($a_user_id, 'integer'));

		$class_ids = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$class_ids[] = $row['class_id'];
		}

		$where = "";
		foreach ($a_user_role_ids as $user_role_id)
		{
			$where .= " OR role_id = " . $this->ilDB->quote($user_role_id);
		}
		if (strlen($where) > 3)
		{
			$where = substr($where, 3);
		}

		$set = $this->ilDB->query('SELECT id FROM ' . dbc::CLASSES_TABLE .
			' WHERE ' . $where);
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$class_ids[] = $row['id'];
		}
		return array_unique($class_ids);
	}

	public function getLockedClasses()
	{
		$set = $this->ilDB->query('SELECT id FROM ' . dbc::CLASSES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
			' AND locked = 1');
		$locked_class_ids = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$locked_class_ids[] = $row['id'];
		}
		return $locked_class_ids;
	}

	public function getUnlockedClasses()
	{
		$set = $this->ilDB->query('SELECT id FROM ' . dbc::CLASSES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
			' AND locked = 0');
		$unlocked_class_ids = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$unlocked_class_ids[] = $row['id'];
		}
		return $unlocked_class_ids;
	}

	public function getPriorityOfClass($a_class_id)
	{
		$set = $this->ilDB->query('SELECT priority FROM ' . dbc::CLASSES_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_class_id, 'integer'));
		$row = $this->ilDB->fetchAssoc($set);
		return $row['priority'];
	}

	public function setPrivilegesForClass($a_class_id, $a_privileges, $a_no_privileges)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber(count($a_class_id)))
		{
			$positive_set = "";
			$negative_set = "";
			foreach ($a_privileges as $privilege)
			{
				$positive_set .= "," . strtolower($privilege) . " = 1";
			}
			foreach ($a_no_privileges as $no_privilege)
			{
				$negative_set .= "," . strtolower($no_privilege) . " = 0";
			}
			if (strlen($positive_set) > 0)
			{
				$positive_set = substr($positive_set, 1);
			}
			if (strlen($negative_set) > 0 && strlen($positive_set) == 0)
			{
				$negative_set = substr($negative_set, 1);
			}
			$this->ilDB->manipulate('UPDATE ' . dbc::CLASS_PRIVILEGES_TABLE .
				' SET ' . $positive_set . $negative_set . ' WHERE class_id = ' . $this->ilDB->quote($a_class_id,
					'integer'));
		}
	}

	public function insertClass($a_name, $a_description, $a_role_id, $a_priority, $a_copy_class_id)
	{
		$this->ilDB->insert(dbc::CLASSES_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextId(dbc::CLASSES_TABLE)),
			'name' => array('text', $a_name),
			'description' => array('text', $a_description),
			'priority' => array('integer', $a_priority),
			'role_id' => array('integer', $a_role_id),
			'pool_id' => array('integer', $this->pool_id)
		));
		$insertedID = $this->ilDB->getLastInsertId();

		if (ilRoomSharingNumericUtils::isPositiveNumber($insertedID))
		{
			//Should privileges of another class should be copied?
			if (ilRoomSharingNumericUtils::isPositiveNumber($a_copy_class_id))
			{
				$privilege_array = array('class_id' => array('integer', $insertedID));

				//Get privileges of the class, which should be copied
				$copied_privileges = $this->getPrivilegesOfClass($a_copy_class_id);
				foreach ($copied_privileges as $privilege_key => $privilege_value)
				{
					$privilege_array[$privilege_key] = array('integer', $privilege_value);
				}
				$this->ilDB->insert(dbc::CLASS_PRIVILEGES_TABLE, $privilege_array);
			}
			//else add empty privileges
			else
			{
				$this->ilDB->insert(dbc::CLASS_PRIVILEGES_TABLE,
					array('class_id' => array('integer', $insertedID)));
			}
		}

		return $insertedID;
	}

	public function updateClass($a_class_id, $a_name, $a_description, $a_role_id, $a_priority)
	{
		$fields = array('name' => array('text', $a_name),
			'description' => array('text', $a_description),
			'priority' => array('integer', $a_priority),
			'role_id' => array('integer', $a_role_id),
			'pool_id' => array('integer', $this->pool_id));
		$where = array('id' => array('integer', $a_class_id));
		return $this->ilDB->update(dbc::CLASSES_TABLE, $fields, $where);
	}

	public function assignUserToClass($a_class_id, $a_user_id)
	{
		if (!$this->isUserInClass($a_class_id, $a_user_id))
		{
			$this->ilDB->insert(dbc::CLASS_USER_TABLE,
				array(
				'class_id' => array('integer', $a_class_id),
				'user_id' => array('integer', $a_user_id)
			));
		}
	}

	public function getUsersForClass($a_class_id)
	{
		$set = $this->ilDB->query('SELECT user_id FROM ' . dbc::CLASS_USER_TABLE .
			' WHERE class_id = ' . $this->ilDB->quote($a_class_id, 'integer'));
		$assigned_user_ids = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$assigned_user_ids[] = $row['user_id'];
		}
		return $assigned_user_ids;
	}

	public function deassignUserFromClass($a_class_id, $a_user_id)
	{
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASS_USER_TABLE .
			" WHERE class_id = " . $this->ilDB->quote($a_class_id, 'integer') .
			" AND user_id = " . $this->ilDB->quote($a_user_id, 'integer'));
	}

	public function clearUsersInClass($a_class_id)
	{
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASS_USER_TABLE .
			" WHERE class_id = " . $this->ilDB->quote($a_class_id, 'integer'));
	}

	public function deleteClassPrivileges($a_class_id)
	{
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASS_PRIVILEGES_TABLE .
			" WHERE class_id = " . $this->ilDB->quote($a_class_id, 'integer'));
	}

	public function deleteClass($a_class_id)
	{
		$this->clearUsersInClass($a_class_id);
		$this->deleteClassPrivileges($a_class_id);
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASSES_TABLE .
			" WHERE id = " . $this->ilDB->quote($a_class_id, 'integer'));
	}

	public function isUserInClass($a_class_id, $a_user_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASS_USER_TABLE .
			' WHERE class_id = ' . $this->ilDB->quote($a_class_id, 'integer') .
			' AND user_id = ' . $this->ilDB->quote($a_user_id, 'integer'));
		return ($this->ilDB->numRows($set) != 0);
	}

	/**
	 * Gets all available attributes for rooms.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllRoomAttributes()
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer')
			. ' ORDER BY name ASC');

		$attributesRows = array();
		while ($attributesRow = $this->ilDB->fetchAssoc($set))
		{
			$attributesRows [] = $attributesRow;
		}

		return $attributesRows;
	}

	/**
	 * Deletes an room attribute with given id.
	 *
	 * @param integer $a_attribute_id
	 * @return integer Affected rows
	 */
	public function deleteRoomAttribute($a_attribute_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_attribute_id, 'integer'));
	}

	/**
	 * Inserts new room attribute.
	 *
	 * @param string $a_attribute_name
	 */
	public function insertRoomAttribute($a_attribute_name)
	{
		$this->ilDB->insert(dbc::ROOM_ATTRIBUTES_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextID(dbc::ROOM_ATTRIBUTES_TABLE)),
			'name' => array('text', $a_attribute_name),
			'pool_id' => array('integer', $this->pool_id),
			)
		);
	}

	/**
	 * Deletes all assignments of an attribute to the rooms.
	 *
	 * @param integer $a_attribute_id
	 * @return integer Affected rows
	 */
	public function deleteAttributeRoomAssign($a_attribute_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE .
				' WHERE att_id = ' . $this->ilDB->quote($a_attribute_id, 'integer'));
	}

	/**
	 * Deletes all assignments of an attribute to the bookings.
	 *
	 * @param integer $a_attribute_id
	 * @return integer Affected rows
	 */
	public function deleteAttributeBookingAssign($a_attribute_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE .
				' WHERE attr_id = ' . $this->ilDB->quote($a_attribute_id, 'integer'));
	}

	/**
	 * Deletes an booking attribute with given id.
	 *
	 * @param integer $a_attribute_id
	 * @return integer Affected rows
	 */
	public function deleteBookingAttribute($a_attribute_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_attribute_id, 'integer'));
	}

	/**
	 * Deletes all bookings which uses the given room.
	 *
	 * @param integer $a_room_id
	 * @return integer Affected rows
	 */
	public function deleteBookingsUsesRoom($a_room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::BOOKINGS_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer'));
	}

	/**
	 * Deletes a room with given room id.
	 *
	 * @param integer $a_room_id
	 * @return integer Affected rows
	 */
	public function deleteRoom($a_room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::ROOMS_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_room_id, 'integer'));
	}

	/**
	 * Inserts new booking attribute.
	 *
	 * @param string $a_attribute_name
	 */
	public function insertBookingAttribute($a_attribute_name)
	{
		$this->ilDB->insert(dbc::BOOKING_ATTRIBUTES_TABLE,
			array(
			'id' => array('integer', $this->ilDB->nextID(dbc::BOOKING_ATTRIBUTES_TABLE)),
			'name' => array('text', $a_attribute_name),
			'pool_id' => array('integer', $this->pool_id),
			)
		);
	}

	/**
	 * Renames an room attribute with given id.
	 *
	 * @param integer $a_attribute_id
	 * @param string $a_changed_attribute_name
	 */
	public function renameRoomAttribute($a_attribute_id, $a_changed_attribute_name)
	{
		$fields = array(
			'name' => array('text', $a_changed_attribute_name),
		);
		$where = array(
			'id' => array("integer", $a_attribute_id)
		);
		$this->ilDB->update(dbc::ROOM_ATTRIBUTES_TABLE, $fields, $where);
	}

	/**
	 * Renames an booking attribute with given id.
	 *
	 * @param integer $a_attribute_id
	 * @param string $a_changed_attribute_name
	 */
	public function renameBookingAttribute($a_attribute_id, $a_changed_attribute_name)
	{
		$fields = array(
			'name' => array('text', $a_changed_attribute_name),
		);
		$where = array(
			'id' => array("integer", $a_attribute_id)
		);
		$this->ilDB->update(dbc::BOOKING_ATTRIBUTES_TABLE, $fields, $where);
	}

}
