<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * Class ilRoomSharingRooms
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Dan Soergel <dansoergel@t-online.de>
 * @author Malte Ahlering <mahlering@stud.hs-bremen.de>
 * @author Bernd Hitzelberger <bhitzelberger@stud.hs-bremen.de>
 * @author Christopher Marks <Deamp_devyahoo.de>
 */
class ilRoomSharingRooms
{
	protected $pool_id;
	protected $ilRoomsharingDatabase;
	private $filter;
	private $roomsMatchingAttributeFilters;
	private $debug = false;

	/**
	 * constructor ilRoomSharingRooms
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id, $ilRoomsharingDatabase)
	{
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = $ilRoomsharingDatabase;
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
		$this->filter = $filter;

		if ($this->debug)
		{
			echo "<br />";
			echo "Entered getList() with filter-array:";
			echo "<br />";
			print_r($this->filter);
			echo "<br />";
		}

		if ($this->filter ['attributes'])
		{
			$this->roomsMatchingAttributeFilters = $this->getRoomsWithMatchingAttributes();
		}
		else
		{
			$this->roomsMatchingAttributeFilters = $this->getAllRooms();
		}

		if ($this->debug)
		{
			echo "<br />";
			echo "roomsMatchingAttributeFilters Array after getAllRooms/getFilteredRooms";
			echo "<br />";
			print_r($this->roomsMatchingAttributeFilters);
			echo "<br />";
		}

		if ($this->filter ["date"] && $this->filter ["time_from"] && $this->filter ["time_to"])
		{
			$this->removeRoomsNotInTimeRange();
		}

		if ($this->debug)
		{
			echo "<br />";
			echo "roomsMatchingAttributeFilters Array after remove Rooms not in time range sdasdasd";
			echo "<br />";
			print_r($this->roomsMatchingAttributeFilters);
			echo "<br />";
		}

		$this->roomsMatchingAttributeFilters = $this->removeRoomsNotMatchingNameAndSeats();

		$res_attribute = $this->getAttributes($this->roomsMatchingAttributeFilters[0]);
		$res = $this->formatDataForGui($this->roomsMatchingAttributeFilters[1], $res_attribute);

		if ($this->debug)
		{
			echo "<br />";
			echo "output:";
			echo "<br />";
			print_r($res);
			echo "<br />";
		}

		return $res;
	}

	/**
	 * Get Rooms with matching Attributes
	 *
	 * @param type $a_attribute_filter
	 * @return array()
	 */
	private function getRoomsWithMatchingAttributes()
	{
		$count = 0;
		$roomsWithAttrib = array();
		$roomsMatchingAttributeFilters = array();

		foreach ($this->filter ['attributes'] as $attribute => $attribute_count)
		{
			$count = $count + 1;
			$roomsWithAttrib = $this->getMatchingRoomsForAttributeAsArray($attribute, $attribute_count,
				$roomsWithAttrib);
		}
		foreach ($roomsWithAttrib as $room_id => $match_count)
		{
			if ($match_count == $count)
			{
				$roomsMatchingAttributeFilters [$room_id] = $match_count;
			}
		}
		return $roomsMatchingAttributeFilters;
	}

	/**
	 *
	 * @param type $a_attribute
	 * @param type $a_count
	 * @param type $roomsWithAttrib
	 * @return type
	 */
	private function getMatchingRoomsForAttributeAsArray($a_attribute, $a_count, $roomsWithAttrib)
	{
		$matching = $this->ilRoomsharingDatabase->
			getRoomIdsWithMatchingAttribute($a_attribute, $a_count);

		if ($this->debug)
		{
			echo "<br />";
			echo "MATCHING";
			echo "<br />";
			print_r($matching);
			echo "<br />";
		}
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

	/**
	 * Get all Rooms
	 *
	 */
	private function getAllRooms()
	{
		$rooms = array();
		$room_ids = $this->ilRoomsharingDatabase->getAllRoomIds();
		$rooms [] = 0;
		foreach ($room_ids as $room_id)
		{
			$rooms [] = $room_id;
		}
		return $rooms;
	}

	/**
	 * Remove all rooms that are booked in time range
	 */
	private function removeRoomsNotInTimeRange()
	{
		$date_from = $this->filter ['date'] . ' ' . $this->filter ['time_from'];
		$date_to = $this->filter ['date'] . ' ' . $this->filter ['time_to'];
		$roomsBookedInTimeRange = $this->getRoomsBookedInDateTimeRange($date_from, $date_to);
		$roomsMatchingAttributeFilters_Temp = $this->roomsMatchingAttributeFilters;
		$this->roomsMatchingAttributeFilters = array();

		if ($this->debug)
		{
			echo "<br />";
			echo "remove RoomsNot in Time Range";
			echo "<br />";
			print_r($roomsBookedInTimeRange);
			echo "<br />";
		}
		foreach ($roomsMatchingAttributeFilters_Temp as $key => $value)
		{
			if (array_search($key, $roomsBookedInTimeRange) > -1)
			{
				//nocht nicht vollständig?
			}
			else
			{
				$this->roomsMatchingAttributeFilters [$key] = 1;
			}
		}
	}

	private function removeRoomsNotMatchingNameAndSeats()
	{
		$res_room = $this->ilRoomsharingDatabase->getMatchingRooms($this->roomsMatchingAttributeFilters,
			$this->filter ["room_name"], $this->filter ["room_seats"]);

		if ($this->debug)
		{
			echo "<br />";
			echo "get Matching Rooms response";
			echo "<br />";
			print_r($res_room);
			echo "<br />";
		}

		foreach ($res_room as $key => $value)
		{
			$room_ids [] = $value ['id'];
		}

		if ($this->debug)
		{
			echo "<br />";
			echo "get Matching Rooms response";
			echo "<br />";
			print_r(array(
				$room_ids, $res_room));
			echo "<br />";
		}

		return array(
			$room_ids,
			$res_room
		);
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
	protected function getAttributes(array $room_ids = null)
	{
		$res_attribute = $this->ilRoomsharingDatabase->getAttributesForRooms($room_ids);
		if ($this->debug)
		{
			echo "<br />";
			echo "room atts";
			echo "<br />";
			print_r($res_attribute);
			echo "<br />";
		}
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

}

?>