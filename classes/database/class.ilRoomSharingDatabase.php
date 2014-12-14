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
class ilRoomSharingDatabase
{
	private $pool_id;
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
			. $this->ilDB->in("room_id", $a_room_ids) . ' AND pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer') . ' ORDER BY room_id, att.name');
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
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer');

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
		$resRoomIds = $this->ilDB->query('SELECT id FROM ' . dbc::ROOMS_TABLE . ' WHERE pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer') . ' ');
		$room_ids = array();
		while ($row = $this->ilDB->fetchAssoc($resRoomIds))
		{
			$room_ids[] = $row['id'];
		}
		return $room_ids;
	}

	/**
	 * Returns all rooms assigned to the roomsharing pool.
	 *
	 * @return assoc array with all found rooms
	 */
	public function getAllRooms()
	{
		$resRooms = $this->ilDB->query('SELECT * FROM ' . dbc::ROOMS_TABLE . ' WHERE pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer'));
		$rooms = array();
		$row = $this->ilDB->fetchAssoc($resRooms);
		while ($row)
		{
			$rooms[] = $row;
			$row = $this->ilDB->fetchAssoc($resRooms);
		}
		return $rooms;
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
		$set = $this->ilDB->query('SELECT name FROM ' . dbc::ROOM_ATTRIBUTES_TABLE . ' WHERE pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer') . ' ORDER BY name');
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
			' WHERE id = ' . $this->ilDB->quote($a_room_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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

		$query = 'SELECT DISTINCT room_id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE pool_id =' . $this->ilDB->quote($this->pool_id, 'integer') . ' AND ' .
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

		$this->insertBookingAppointment($insertedId, $a_booking_values);

		return 1;
	}

	/*
	 * Creates an appointment in the RoomSharing-Calendar and save id in booking-table.
	 *
	 * @param $title string appointment-title
	 * @param $time_start start-time
	 * @param $time_end end-time
	 */
	private function insertBookingAppointment($insertedId, $a_booking_values)
	{
		//create appointment first
		include_once('Services/Calendar/classes/class.ilDate.php');
		$time_start = new ilDateTime($a_booking_values ['from']['date'] . ' ' . $a_booking_values ['from']['time'],
			1);
		$time_end = new ilDateTime($a_booking_values ['to']['date'] . ' ' . $a_booking_values ['to']['time'],
			1);
		$title = $a_booking_values['subject'];

		$room_name = $this->getRoomName($a_booking_values ['room']);

		$cal_cat_id = $a_booking_values['cal_id'];

		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
		//use original ilCalendarEntry and let ILIAS do the work
		$app = new ilCalendarEntry();
		$app->setStart($time_start);
		$app->setEnd($time_end);
		$app->setFullday(false);
		$app->setTitle($title);
		$app->setDescription($a_booking_values ['comment']);
		$app->setAutoGenerated(true);
		$app->enableNotification(false);
		$app->setLocation($room_name);
		$app->validate();
		$app->save();

		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		$ass = new ilCalendarCategoryAssignments($app->getEntryId());
		$ass->addAssignment($cal_cat_id);

		//update bookings-table afterwards
		$this->ilDB->manipulate('UPDATE ' . dbc::BOOKINGS_TABLE .
			' SET calendar_entry_id = ' . $this->ilDB->quote($app->getEntryId(), 'integer') .
			' WHERE id = ' . $this->ilDB->quote($insertedId, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
	 * Delete  calendar entries of a booking from the database.
	 *
	 * @param integer $a_booking_id
	 */
	public function deleteCalendarEntryOfBooking($a_booking_id)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		$set = $this->ilDB->query('SELECT calendar_entry_id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

		while ($a_entry_id = $this->ilDB->fetchAssoc($set))
		{
			ilCalendarEntry::_delete($a_entry_id['calendar_entry_id']);
		}
	}

	/**
	 * Delete calendar entries of bookings from the database.
	 *
	 * @param array $a_booking_ids
	 */
	public function deleteCalendarEntriesOfBookings($a_booking_ids)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		$set = $this->ilDB->prepare('SELECT calendar_entry_id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE ' . $this->ilDB->in("id", $a_booking_ids) .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

		$result = $this->ilDB->execute($set, $a_booking_ids);
		while ($a_entry_id = $this->ilDB->fetchAssoc($result))
		{
			ilCalendarEntry::_delete($a_entry_id['calendar_entry_id']);
		}
	}

	/**
	 * Delete a booking from the database.
	 *
	 * @param integer $a_booking_id
	 */
	public function deleteBooking($a_booking_id)
	{
		$this->ilDB->manipulate('DELETE FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
			' WHERE ' . $this->ilDB->in("id", $booking_ids) .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
		$this->ilDB->execute($st, $booking_ids);
		$st2 = $this->ilDB->prepareManip('DELETE FROM ' . dbc::BOOK_USER_TABLE .
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
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
	 * Gets all bookings filtered by given criteria.
	 *
	 * @param array $filter filter criteria
	 * @return array
	 */
	public function getFilteredBookings(array $filter)
	{
		$query = 'SELECT b.id, b.user_id, b.subject, b.bookingcomment,' .
			' r.id AS room_id, b.date_from, b.date_to FROM ' . dbc::BOOKINGS_TABLE . ' b ' .
			' JOIN ' . dbc::ROOMS_TABLE . ' r ON b.room_id = r.id ' .
			' WHERE b.pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer');

		if ($filter['user_id'] || $filter['user_id'])
		{
			$query .= ' AND b.user_id = ' . $this->ilDB->quote($filter['user_id'], 'integer') . ' ';
		}

		if ($filter['room_name'] || $filter['room_name'])
		{
			$query .= ' AND r.name LIKE ' .
				$this->ilDB->quote('%' . $filter['room_name'] . '%', 'text') . ' ';
		}

		if ($filter['subject'] || $filter['subject'])
		{
			$query .= ' AND b.subject LIKE ' .
				$this->ilDB->quote('%' . $filter['subject'] . '%', 'text') . ' ';
		}

		if ($filter['comment'] || $filter['comment'])
		{
			$query .= ' AND b.bookingcomment LIKE ' .
				$this->ilDB->quote('%' . $filter['comment'] . '%', 'text') . ' ';
		}

		if ($filter['attributes'])
		{
			foreach ($filter['attributes'] as $attribute => $value)
			{
				$query .= ' AND EXISTS (SELECT * FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE . ' ba ' .
					' LEFT JOIN ' . dbc::BOOKING_ATTRIBUTES_TABLE . ' a ON a.id = ba.attr_id ' .
					' WHERE booking_id = b.id AND name = ' .
					$this->ilDB->quote($attribute, 'text') . ' AND value LIKE ' .
					$this->ilDB->quote('%' . $value . '%', 'text') . ' ) ';
			}
		}

		$set = $this->ilDB->query($query);
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
			' FROM ' . dbc::BOOK_USER_TABLE . ' participants ' .
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
			$this->ilDB->quote($a_booking_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

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

	public function getAllBookingAttributeNames()
	{
		$set = $this->ilDB->query('SELECT name FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') . ' ORDER BY name');
		$attributes = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$attributes [] = $row ['name'];
		}
		return $attributes;
	}

	/**
	 * Gets all floorplans.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllFloorplans()
	{
		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
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
			' WHERE file_id = ' . $this->ilDB->quote($a_file_id, 'integer') .
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));

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
	 * Gets a floorplans ids.
	 *
	 * @return type return of $this->ilDB->query
	 */
	public function getAllFloorplanIds()
	{
		$set = $this->ilDB->query('SELECT file_id FROM ' . dbc::FLOORPLANS_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));

		$floorplans_ids = array();
		$row = $this->ilDB->fetchAssoc($set);
		while ($row)
		{
			$floorplans_ids [] = $row ['file_id'];
			$row = $this->ilDB->fetchAssoc($set);
		}
		return $floorplans_ids;
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
				' WHERE file_id = ' . $this->ilDB->quote($a_file_id, 'integer') .
				' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
				$this->ilDB->quote($a_file_id, 'integer') .
				' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	public function getRoomsWithFloorplan($a_file_id)
	{
		$set = $this->ilDB->query('SELECT name FROM ' . dbc::ROOMS_TABLE .
			' WHERE building_id = ' . $this->ilDB->quote($a_file_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
				$this->ilDB->quote($a_user_id, 'integer') .
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
		$st = $this->ilDB->prepareManip('DELETE FROM ' . dbc::BOOK_USER_TABLE .
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
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer') .
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
	 * Gets room information by id.
	 *
	 * @param integer $a_room_id the id for the room whose information should be returne
	 * @return array room information consisting of name, type, min allocation, ...
	 */
	public function getRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::ROOMS_TABLE . ' WHERE id = ' .
			$this->ilDB->quote($a_room_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

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
			' WHERE id = ' . $this->ilDB->quote($a_attribute_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
		return $this->ilDB->fetchAssoc($set);
	}

	/**
	 * Gets all attributes that are assigned to a room.
	 *
	 * @param integer $a_room_id the id of the room for which the attributes should be returned
	 * @return array an array containing information about the assigned room attributes
	 */
	public function getAttributesForRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT id, att.name, count FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
			dbc::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id' .
			' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer') . ' ORDER BY att.name');
		$attributes = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$attributes[] = $row;
		}
		return $attributes;
	}

	/**
	 * Gets and returns all bookings that have been made; even the ones in the past.
	 *
	 * @param integer $a_room_id the id of the room for which the bookings should be returnd
	 * @return array an array containing information about bookings for the specified room
	 */
	public function getAllBookingsForRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
		$bookings = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$bookings[] = $row;
		}
		return $bookings;
	}
        
        /**
         * Gets all bookings for a room which are in present or future.
         * 
         * @param integer $a_room_id the room id
         * @return array list of bookings
         */
        public function getBookingsForRoomThatAreValid($a_room_id)
        {
            $set = $this->ilDB->query('SELECT * FROM ' . dbc:: BOOKINGS_TABLE .
            ' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') .
            ' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer') .
            ' AND (date_from >= "' . date('Y-m-d H:i:s') . '"' .
            ' OR date_to >= "' . date('Y-m-d H:i:s') . '")');
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
	 * @return type return of $this->ilDB->query
	 */
	public function getAllBookingsIds()
	{
		$set = $this->ilDB->query('SELECT id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer'));

		$bookingsIds = array();
		while ($bookingRow = $this->ilDB->fetchAssoc($set))
		{
			$bookingsIds [] = $bookingRow['id'];
		}

		return $bookingsIds;
	}

	public function getInfoForBooking($booking_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::BOOKINGS_TABLE . ' b LEFT JOIN ' .
			dbc::ROOMS_TABLE . ' r ON r.id = b.room_id LEFT JOIN usr_data u ON u.usr_id = b.user_id WHERE b.id = ' .
			$this->ilDB->quote($booking_id, 'integer'));
		$info = array();
		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$info['title'] = $row['subject'];
			$info['user'] = $row['public_booking'] == 1 ? 'Gebucht von ' . $row['firstname'] . ' ' . $row['lastname'] . '<BR>'
					: '';
			$info['description'] = $row['bookingcomment'];
			$info['room'] = $row['name'];
			$info['start'] = new ilDateTime($row['date_from'], IL_CAL_DATETIME);
			$info['end'] = new ilDateTime($row['date_to'], IL_CAL_DATETIME);
		}
		return $info;
	}

	/**
	 * Gathers all current bookings that have been made for this room.
	 *
	 * @param integer $a_room_id the id of the room for which the current bookings should be returned
	 * @return array an array containing information regarding the bookings
	 */
	public function getCurrentBookingsForRoom($a_room_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc:: BOOKINGS_TABLE .
			' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer') .
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
	 * Deletes all attributes for a room.
	 *
	 * @param type $a_room_id the id of the room whose attributes should be deleted
	 * @return int the number of affected rows
	 */
	public function deleteAllAttributesForRoom($a_room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer'));
	}

	/**
	 * Inserts room information into the database.
	 *
	 * @param string $a_name
	 * @param string $a_type
	 * @param integer $a_min_alloc
	 * @param integer $a_max_alloc
	 * @param integer $a_file_id
	 * @param integer $a_building_id
	 * @return integer the id of the room for which the information has been inserted
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
	 * Inserts an attribute with its amount to a room specified room.
	 *
	 * @param integer $a_room_id the id room for which the attribute should be inserted
	 * @param integer $a_attribute_id the id of the attribute to be inserted
	 * @param integer $a_amount the amount specified for the attribute
	 */
	public function insertAttributeForRoom($a_room_id, $a_attribute_id, $a_amount)
	{
		$this->ilDB->insert(ilRoomSharingDBConstants::ROOM_TO_ATTRIBUTE_TABLE,
			array(
			'room_id' => array('integer', $a_room_id),
			'att_id' => array('integer', $a_attribute_id),
			'count' => array('integer', $a_amount)
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
	 * @return integer number of affected rows
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
			"id" => array("integer", $a_id),
			"pool_id" => array("integer", $this->pool_id)
		);
		return $this->ilDB->update(dbc::ROOMS_TABLE, $fields, $where);
	}

	/**
	 * Gets all classes for the pool-id
	 *
	 * @return array Array with classes
	 */
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

	/**
	 * Gets a specific class
	 *
	 * @param integer $a_class_id Class-ID
	 * @return array Array with the class data of the selected class
	 */
	public function getClassById($a_class_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASSES_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_class_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
		$row = $this->ilDB->fetchAssoc($set);

		return $row;
	}

	/**
	 * Gets all privileges of a class
	 *
	 * @param integer $a_class_id Class-ID
	 * @return array Array with the setted privileges of the selectec class
	 */
	public function getPrivilegesOfClass($a_class_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASS_PRIVILEGES_TABLE .
			' WHERE class_id = ' . $this->ilDB->quote($a_class_id, 'integer'));

		$row = $this->ilDB->fetchAssoc($set);
		//Remove class_id from resultlist, so that only the privileges are in the array
		unset($row['class_id']);
		return $row;
	}

	/**
	 * Sets the locked classes
	 *
	 * @param array $a_class_ids Array with the class ids which should be locked. Classes which are not in the array will be unlocked
	 */
	public function setLockedClasses($a_class_ids)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber(count($a_class_ids)))
		{
			$st = $this->ilDB->prepareManip('UPDATE ' . dbc::CLASSES_TABLE .
				' SET locked = 1 WHERE ' . $this->ilDB->in('id', $a_class_ids));

			$this->ilDB->execute($st, array_keys($a_class_ids));

			$st2 = $this->ilDB->prepareManip('UPDATE ' . dbc::CLASSES_TABLE .
				' SET locked = 0 WHERE ' . $this->ilDB->in('id NOT', $a_class_ids));

			$this->ilDB->execute($st2, array_keys($a_class_ids));
		}
		else
		{
			$this->ilDB->manipulate('UPDATE ' . dbc::CLASSES_TABLE .
				' SET locked = 0');
		}
	}

	/**
	 * Get all assigned classes (directly or over role-assignment) for a user
	 *
	 * @param integer $a_user_id User-ID
	 * @param array $a_user_role_ids Role-Ids which the user is assigned to
	 * @return array Array with class ids the user is assigned to
	 */
	public function getAssignedClassesForUser($a_user_id, $a_user_role_ids)
	{
		$class_ids = array();
		$st = $this->ilDB->prepare('SELECT id FROM ' . dbc::CLASSES_TABLE . ' LEFT JOIN ' .
			dbc::CLASS_USER_TABLE . ' ON id = class_id WHERE pool_id = ' .
			$this->ilDB->quote($this->pool_id, 'integer') . ' AND (user_id = ' .
			$this->ilDB->quote($a_user_id, 'integer') . ' OR ' .
			$this->ilDB->in("role_id", $a_user_role_ids) . ')');

		$set = $this->ilDB->execute($st, $a_user_role_ids);

		while ($row = $this->ilDB->fetchAssoc($set))
		{
			$class_ids[] = $row['id'];
		}

		return array_unique($class_ids);
	}

	/**
	 * Gets all classes that are currently locked
	 *
	 * @return array Array with class ids currently locked
	 */
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

	/**
	 * Gets all classes that are currently unlocked
	 *
	 * @return array Array with class ids currently unlocked
	 */
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

	/**
	 * Gets the priority of a specific class
	 *
	 * @param integer $a_class_id Class-ID
	 * @return integer Priority of the class
	 */
	public function getPriorityOfClass($a_class_id)
	{
		$set = $this->ilDB->query('SELECT priority FROM ' . dbc::CLASSES_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_class_id, 'integer'));
		$row = $this->ilDB->fetchAssoc($set);
		return $row['priority'];
	}

	/**
	 * Sets every privilege of a specific class
	 *
	 * @param integer $a_class_id Class-ID
	 * @param array $a_privileges Array with privileges which should be assigned
	 * @param array $a_no_privileges Array with privileges that are deassigned
	 */
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

	/**
	 * Adds a new class to the database
	 *
	 * @param string $a_name Name of the class
	 * @param string $a_description Description of the class
	 * @param integer $a_role_id Role-ID of a possible assigned role
	 * @param integer $a_priority Priority of the class
	 * @param integer $a_copy_class_id Possible class-ID of which the privileges should be copied
	 * @return integer New ID of the inserted class
	 */
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

	/**
	 * Edits the values of an already created class
	 *
	 * @param integer $a_class_id Class-ID which should be edited
	 * @param string $a_name New name
	 * @param string $a_description New description
	 * @param string $a_role_id New role-id of the possible assigned role
	 * @param integer $a_priority New priority
	 */
	public function updateClass($a_class_id, $a_name, $a_description, $a_role_id, $a_priority)
	{
		$fields = array('name' => array('text', $a_name),
			'description' => array('text', $a_description),
			'priority' => array('integer', $a_priority),
			'role_id' => array('integer', $a_role_id),
			'pool_id' => array('integer', $this->pool_id));
		$where = array('id' => array('integer', $a_class_id));
		$this->ilDB->update(dbc::CLASSES_TABLE, $fields, $where);
	}

	/**
	 * Assign a user directly to a class
	 *
	 * @param integer $a_class_id Class-ID
	 * @param integer $a_user_id User-ID of the user which should be assigned
	 */
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

	/**
	 * Gets all users directly assigned to a class
	 *
	 * @param integer $a_class_id Class-ID
	 * @return array Array with the assigned user-ids
	 */
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

	/**
	 * Deassign a user from a class
	 *
	 * @param integer $a_class_id Class-ID
	 * @param integer $a_user_id User-ID which should be deassigned from the class
	 */
	public function deassignUserFromClass($a_class_id, $a_user_id)
	{
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASS_USER_TABLE .
			" WHERE class_id = " . $this->ilDB->quote($a_class_id, 'integer') .
			" AND user_id = " . $this->ilDB->quote($a_user_id, 'integer'));
	}

	/**
	 * Deassign all directly assigned users from a class
	 *
	 * @param integer $a_class_id Class-ID
	 */
	public function clearUsersInClass($a_class_id)
	{
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASS_USER_TABLE .
			" WHERE class_id = " . $this->ilDB->quote($a_class_id, 'integer'));
	}

	/**
	 * Delete all privileges of a specific class
	 *
	 * @param integer $a_class_id Class-ID of which the privileges should be deleted
	 */
	public function deleteClassPrivileges($a_class_id)
	{
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASS_PRIVILEGES_TABLE .
			" WHERE class_id = " . $this->ilDB->quote($a_class_id, 'integer'));
	}

	/**
	 * Deletes a class with all its privileges and assignments
	 *
	 * @param integer $a_class_id Class-ID of the class which should be deleted
	 */
	public function deleteClass($a_class_id)
	{
		$this->clearUsersInClass($a_class_id);
		$this->deleteClassPrivileges($a_class_id);
		$this->ilDB->manipulate("DELETE FROM " . dbc::CLASSES_TABLE .
			" WHERE id = " . $this->ilDB->quote($a_class_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Checks if a specific user is in a specific class
	 *
	 * @param integer $a_class_id Class-ID
	 * @param integer $a_user_id User-ID
	 *
	 * @return boolean true if user is in class, false otherwise
	 */
	public function isUserInClass($a_class_id, $a_user_id)
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::CLASS_USER_TABLE .
			' WHERE class_id = ' . $this->ilDB->quote($a_class_id, 'integer') .
			' AND user_id = ' . $this->ilDB->quote($a_user_id, 'integer'));
		return ($this->ilDB->numRows($set) > 0);
	}

	/**
	 * Gets a priority of a specific user
	 *
	 * @param integer $a_user_id User-ID
	 * @return integer Priority of the user
	 */
	public function getUserPriority($a_user_id)
	{
		$set = $this->ilDB->query('SELECT MAX(priority) AS max_priority FROM ' .
			dbc::CLASSES_TABLE . ' JOIN ' . dbc::CLASS_USER_TABLE .
			' ON id = class_id WHERE user_id = ' . $this->ilDB->quote($a_user_id, 'integer'));

		$userPriorityRow = $this->ilDB->fetchAssoc($set);

		return $userPriorityRow ['max_priority'];
	}

	/**
	 * Gets all available attributes for rooms.
	 *
	 * @return array associative array for all available attributes with id, name
	 */
	public function getAllRoomAttributes()
	{
		$set = $this->ilDB->query('SELECT * FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer')
			. ' ORDER BY name ASC');

		$attributes_rows = array();
		while ($attributes_row = $this->ilDB->fetchAssoc($set))
		{
			$attributes_rows [] = $attributes_row;
		}

		return $attributes_rows;
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
				' WHERE id = ' . $this->ilDB->quote($a_attribute_id, 'integer') .
				' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Inserts new room attribute.
	 *
	 * @param string $a_attribute_name
	 * @return integer id of the inserted attribute
	 */
	public function insertRoomAttribute($a_attribute_name)
	{
		$next_insert_id = $this->ilDB->nextID(dbc::ROOM_ATTRIBUTES_TABLE);
		$this->ilDB->insert(dbc::ROOM_ATTRIBUTES_TABLE,
			array(
			'id' => array('integer', $next_insert_id),
			'name' => array('text', $a_attribute_name),
			'pool_id' => array('integer', $this->pool_id),
			)
		);
		return $next_insert_id;
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
	 * Deletes all bookings that are assigned to a room.
	 *
	 * @param integer $a_room_id the id of the room for which the bookings should be deleted
	 * @return integer the amount of bookings that are affected by the deletion
	 */
	public function deleteAllBookingsAssignedToRoom($a_room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::BOOKINGS_TABLE .
				' WHERE room_id = ' . $this->ilDB->quote($a_room_id, 'integer') .
				' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Deletes a room with given room id.
	 *
	 * @param integer $a_room_id the id of the room that should be deleted
	 * @return integer affected rows
	 */
	public function deleteRoom($a_room_id)
	{
		return $this->ilDB->manipulate('DELETE FROM ' . dbc::ROOMS_TABLE .
				' WHERE id = ' . $this->ilDB->quote($a_room_id, 'integer') .
				' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
			'id' => array("integer", $a_attribute_id),
			'pool_id' => array("integer", $this->pool_id)
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
			'id' => array("integer", $a_attribute_id),
			'pool_id' => array("integer", $this->pool_id)
		);
		$this->ilDB->update(dbc::BOOKING_ATTRIBUTES_TABLE, $fields, $where);
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

	public function getBookingsForRoomInTimeSpan($room_id, $start, $end, $type)
	{
		$query = 'SELECT b.id id FROM ' . dbc::BOOKINGS_TABLE . ' b';

		if ($type != 4)
		{
			$query .= ' WHERE ((date_from <= ' .
				$this->ilDB->quote($end->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') .
				' AND date_to >= ' .
				$this->ilDB->quote($start->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') .
				') OR (date_from <= ' .
				$this->ilDB->quote($end->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') .
				' ))';
		}
		else
		{
			$date = new ilDateTime(mktime(0, 0, 0), IL_CAL_UNIX);
			$query .= ' WHERE date_from >= ' .
				$this->ilDB->quote($date->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp');
		}

		$query .= ' AND room_id = ' . $this->ilDB->quote($room_id, 'integer') .
			' AND pool_id = ' . $this->ilDB->quote($this->pool_id, 'integer') .
			' ORDER BY date_from';

		$res = $this->ilDB->query($query);

		$events = array();
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$events[] = $row;
		}
		return $events;
	}

	/**
	 * Deletes the db entry of the actual room sharing pool.
	 * If you are sure what you are doing, pass "SURE" as argument.
	 *
	 * @param string $a_confirmation pass "SURE"
	 */
	public function deletePoolEntry($a_confirmation)
	{
		if ($a_confirmation == "SURE")
		{
			$this->ilDB->manipulate("DELETE FROM " . dbc::POOLS_TABLE .
				" WHERE id = " . $this->ilDB->quote($this->pool_id, 'integer'));
		}
	}

}
