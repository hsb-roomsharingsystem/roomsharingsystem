<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * Class ilRoomSharingRooms
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Dan Soergel <dansoergel@t-online.de>
 * @author Malte Ahlering <mahlering@stud.hs-bremen.de>
 * @author Bernd Hitzelberger <bhitzelberger@stud.hs-bremen.de>
 */
class ilRoomSharingRooms
{
	protected $pool_id;
	protected $ilRoomsharingDatabase;

	/**
	 * constructor ilRoomSharingRooms
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id = 1)
	{
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Get the rooms for a given pool_id from database
	 *
	 * @param array $filter optional room-filter
	 * @return array Rooms and Attributes in the following format:
	 *         array (
	 *         array (
	 *         'room' => <string>, Name of the room
	 *         'seats' => <int>, Amout of seats
	 *         'beamer' => <bool>, true, if a beamer exists
	 *         'overhead_projector' => <bool>, true, if a overhead projector exists
	 *         'whiteboard' => <bool>, true, if a whiteboard exists
	 *         'sound_system' => <bool>, true, if a sound system exists
	 *         )
	 *         )
	 */
	public function getList(array $filter = null)
	{
		/*
		 * Get all room ids where attributes match the filter (if any).
		 */
		$count = 0;
		$roomsWithAttrib = array();
		$roomsMatchingAttributeFilters = array();
		if ($filter ['attributes'])
		{
			foreach ($filter ['attributes'] as $attribute => $attribute_count)
			{
				$count = $count + 1;
				$roomsWithAttrib = $this->getMatchingRoomsForAttributeAndAddToArray($attribute,
					$attribute_count, $roomsWithAttrib);
			}
			foreach ($roomsWithAttrib as $room_id => $match_count)
			{
				if ($match_count == $count)
				{
					$roomsMatchingAttributeFilters [$room_id] = $match_count;
				}
			}
		}
		else
		{ // when no filter set, get all
			$room_ids = $this->ilRoomsharingDatabase->getAllRoomIds();
			foreach ($room_ids as $room_id => $value)
			{
				$roomsMatchingAttributeFilters [$room_id] = 1;
			}
		}

		/*
		 * Remove all rooms that are booked in time range
		 */
		if ($filter ["date"] && $filter ["time_from"] && $filter ["time_to"])
		{
			$date_from = $filter ['date'] . ' ' . $filter ['time_from'];
			$date_to = $filter ['date'] . ' ' . $filter ['time_to'];
			$roomsBookedInTimeRange = $this->getRoomsBookedInDateTimeRange($date_from, $date_to);
			$roomsMatchingAttributeFilters_Temp = $roomsMatchingAttributeFilters;
			$roomsMatchingAttributeFilters = array();
			foreach ($roomsMatchingAttributeFilters_Temp as $key => $value)
			{
				if (array_search($key, $roomsBookedInTimeRange) > -1)
				{
					//nocht nicht vollständig?
				}
				else
				{
					$roomsMatchingAttributeFilters [$key] = 1;
				}
			}
		}

		$res_room = $this->ilRoomsharingDatabase->getMatchingRooms($roomsMatchingAttributeFilters,
			$filter ["room_name"], $filter ["room_seats"]);

		foreach ($res_room as $key => $value)
		{
			// Remember the ids in order to filter for room attributes within the number of room ids
			$room_ids [] = $value ['id'];
		}

		/*
		 * Bring data in a specific format for display purposes
		 */
		$res_attribute = $this->getAttributes($room_ids);
		$res = $this->formatDataForGui($res_room, $res_attribute);
		return $res;
	}

	/**
	 * Returns all available room attributes that appear in the optional
	 * filter list.
	 *
	 * @return string
	 */
	public function getAllAttributes()
	{
		return $this->ilRoomsharingDatabase->getAllAttributeNames();
	}

	/**
	 * Gets all attributes referenced by the rooms given by the ids.
	 *
	 * @param array $room_ids
	 *        	ids of the rooms
	 * @return array room_id, att.name, count
	 */
	protected function getAttributes(array $room_ids)
	{
		$res_attribute = $this->ilRoomsharingDatabase->getAttributesForRooms($room_ids);
		return $res_attribute;
	}

	/**
	 * Formats the loaded data for the gui.
	 *
	 *
	 * @param array $res_room
	 *        	list of rooms
	 * @param array $res_attribute
	 *        	list of attributes
	 * @return array Rooms and Attributes in the following format:
	 *         array (
	 *         array (
	 *         'room' => <string>, Name of the room
	 *         'seats' => <int>, Amout of seats
	 *         'beamer' => <bool>, true, if a beamer exists
	 *         'overhead_projector' => <bool>, true, if a overhead projector exists
	 *         'whiteboard' => <bool>, true, if a whiteboard exists
	 *         'sound_system' => <bool>, true, if a sound system exists
	 *         )
	 *         )
	 */
	protected function formatDataForGui(array $res_room, array $res_attribute)
	{
		$res = array();
		foreach ($res_room as $room)
		{
			$attr = array();
			foreach ($res_attribute as $attribute)
			{
				if ($attribute ['room_id'] == $room ['id'])
				{
					$attr [$attribute ['name']] = $attribute ['count'];
				}
			}

			$row = array(
				'room' => $room ['name'],
				'room_id' => $room ['id'],
				'seats' => $room ['max_alloc'],
				'attributes' => $attr
			);
			$res [] = $row;
		}
		return $res;
	}

	/**
	 * Returns the maximum amount of seats of all available rooms in the current pool, so that the
	 * the user can be notified about it in the filter options.
	 *
	 * @return integer $value maximum seats
	 */
	public function getMaxSeatCount()
	{
		return $this->ilRoomsharingDatabase->getMaxSeatCount();
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
		return $this->ilRoomsharingDatabase->getMaxCountForAttribute($a_room_attribute);
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
		return $this->ilRoomsharingDatabase->getRoomName($a_room_id);
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
		return $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($date_from, $date_to,
				$room_id = null);
	}

	private function getMatchingRoomsForAttributeAndAddToArray($a_attribute, $a_count, $roomsWithAttrib)
	{
		$matching = $this->ilRoomsharingDatabase->
			getRoomIdsWithMatchingAttribute($a_attribute, $a_count);
		foreach ($matching as $key => $room_id)
		{
			if (!array_key_exists($room_id, $roomsWithAttrib))
			{
				$roomsWithAttrib [$room_id] = 1;
			}
			else
			{
				$roomsWithAttrib [$room_id] = $roomsWithAttrib [$room_id] + 1;
			}
		}

		return $roomsWithAttrib;
	}

}

?>