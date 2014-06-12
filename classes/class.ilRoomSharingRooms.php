<?php

/**
 * Class ilRoomSharingRooms
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Dan Soergel <dansoergel@t-online.de>
 * @author Malte Ahlering <mahlering@stud.hs-bremen.de>
 * @author Bernd Hitzelberger <bhitzelberger@stud.hs-bremen.de>
 */
class ilRoomSharingRooms {
	
	protected $pool_id;
	public function __construct($a_pool_id = 1) {
		$this->pool_id = $a_pool_id;
	}
	
	/**
	 * Get the rooms for a given pool_id from database
	 *
	 * @global type $ilDB
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
	public function getList(array $filter = null) {
		global $ilDB;
		
		// $debug = true;
		$debug = false;
		
		if ($debug) {
			echo "<br />";
			echo "Entered getList() with filter-array:";
			echo "<br />";
			print_r ( $filter );
			echo "<br />";
		}
		
		/*
		 * Get all room ids where attributes match the filter (if any).
		 */
		$count = 0;
		$roomsWithAttrib = array ();
		$roomsMatchingAttributeFilters = array ();
		if ($filter ['attributes']) {
			foreach ( $filter ['attributes'] as $key => $value ) {
				$count = $count + 1;
				$q = 'SELECT room_id FROM rep_robj_xrs_room_attr ra LEFT JOIN rep_robj_xrs_rattr attr ON ra.att_id = attr.id WHERE name = ' . $ilDB->quote ( $key, 'text' ) . ' AND count >= ' . $ilDB->quote ( $value, 'integer' ) . ' AND pool_id = ' . $ilDB->quote ( $this->pool_id, 'integer' ) . ' ';
				if ($debug) {
					echo "<br />";
					echo $q;
					echo "<br />";
				}
				$resAttr = $ilDB->query ( $q );
				while ( $row = $ilDB->fetchAssoc ( $resAttr ) ) {
					if (! array_key_exists ( $row ['room_id'], $roomsWithAttrib )) {
						$roomsWithAttrib [$row ['room_id']] = 1;
					} else {
						$roomsWithAttrib [$row ['room_id']] = $roomsWithAttrib [$row ['room_id']] + 1;
					}
				}
			}
			if ($debug) {
				echo "<br />";
				echo "count: " . $count;
				echo "<br />";
			}
			foreach ( $roomsWithAttrib as $key => $value ) {
				if ($value == $count) {
					$roomsMatchingAttributeFilters [$key] = $value;
				}
			}
		} else { // when no filter set, get all
			$query = 'SELECT id FROM rep_robj_xrs_rooms';
			$resRoomIds = $ilDB->query ( $query );
			while ( $row = $ilDB->fetchAssoc ( $resRoomIds ) ) {
				$roomsMatchingAttributeFilters [$row ['id']] = 1;
			}
		}
		
		if ($debug) {
			echo "<br />";
			print_r ( $roomsMatchingAttributeFilters );
			echo "<br />";
			print_r ( array_keys ( $roomsMatchingAttributeFilters ) );
			echo "<br />";
		}
		$select_query = 'SELECT room.id, name, max_alloc FROM rep_robj_xrs_rooms room ';
		$order_by_name = ' ORDER BY name ';
		$join_part = ' ';
		$where_part = ' WHERE room.pool_id = ' . $ilDB->quote ( $this->pool_id, 'integer' ) . ' ';
		
		/*
		 * Add remaining filters to query string
		 */
		$where_part = ' AND room.pool_id = ' . $ilDB->quote ( $this->pool_id, 'integer' ) . ' ';
		
		if ($filter ["room_name"] || $filter ["room_name"] === "0") {
			$where_part = $where_part . ' AND name LIKE ' . $ilDB->quote ( '%' . $filter ["room_name"] . '%', 'text' ) . ' ';
		}
		if ($filter ["room_seats"] || $filter ["room_seats"] === 0.0) {
			$where_part = $where_part . ' AND max_alloc >= ' . $ilDB->quote ( $filter ["room_seats"], 'integer' ) . ' ';
		}
		if ($filter ["date_from"] && $filter ["date_to"]) { // search for date and time not implemented
			ilUtil::sendInfo ( "Filtern nach Datum nicht implementiert", false );
			
			if ($filter ["time_duration"]) {
				ilUtil::sendInfo ( "Filtern nach Zeitspanne nicht implementiert", false );
			}
		}
		
		/*
		 * Prepare and execute statement
		 */
		$st = $ilDB->prepare ( 'SELECT room.id, name, max_alloc FROM rep_robj_xrs_rooms room WHERE ' . $ilDB->in ( "room.id", array_keys ( $roomsMatchingAttributeFilters ) ) . $where_part . ' ORDER BY name', $ilDB->addTypesToArray ( $types, "integer", count ( $room . ids ) ) );
		$set = $ilDB->execute ( $st, array_keys ( $roomsMatchingAttributeFilters ) );
		
		$res_room = array ();
		$room_ids = array ();
		while ( $row = $ilDB->fetchAssoc ( $set ) ) {
			$res_room [] = $row;
			$room_ids [] = $row ['id']; // Remember the ids in order to filter for room attributes within the number of room ids
		}
		
		/*
		 * Bring data in a specific format for display purposes
		 */
		$res_attribute = $this->getAttributes ( $room_ids );
		$res = $this->formatDataForGui ( $res_room, $res_attribute );
		return $res;
	}
	
	/**
	 * Returns all available room attributes that appear in the optional
	 * filter list.
	 *
	 * @return string
	 */
	public function getAllAttributes() {
		global $ilDB;
		
		$set = $ilDB->query ( 'SELECT name FROM rep_robj_xrs_rattr ORDER BY name' );
		
		$attributes = array ();
		while ( $row = $ilDB->fetchAssoc ( $set ) ) {
			$attributes [] = $row ['name'];
		}
		
		return $attributes;
	}
	
	/**
	 * Gets all attributes referenced by the rooms given by the ids.
	 *
	 * @param array $room_ids
	 *        	ids of the rooms
	 * @return array room_id, att.name, count
	 */
	protected function getAttributes(array $room_ids) {
		global $ilDB;
		
		$st = $ilDB->prepare ( 'SELECT room_id, att.name, count FROM rep_robj_xrs_room_attr ' . ' LEFT JOIN rep_robj_xrs_rattr as att ON att.id = rep_robj_xrs_room_attr.att_id' . ' WHERE ' . $ilDB->in ( "room_id", $room_ids ) . ' ORDER BY room_id, att.name', $ilDB->addTypesToArray ( $types, "integer", count ( $room_ids ) ) );
		$set = $ilDB->execute ( $st, $room_ids );
		
		$res_attribute = array ();
		while ( $row = $ilDB->fetchAssoc ( $set ) ) {
			$res_attribute [] = $row;
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
	protected function formatDataForGui(array $res_room, array $res_attribute) {
		$res = array ();
		foreach ( $res_room as $room ) {
			$attr = array ();
			foreach ( $res_attribute as $attribute ) {
				if ($attribute ['room_id'] == $room ['id']) {
					$attr [$attribute ['name']] = $attribute ['count'];
				}
			}
			
			$row = array (
					'room' => $room ['name'],
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
	 */
	public function getMaxSeatCount() {
		global $ilDB;
		$valueSet = $ilDB->query ( 'SELECT MAX(max_alloc) AS value FROM rep_robj_xrs_rooms  WHERE pool_id = ' . $ilDB->quote ( $this->pool_id, 'integer' ) );
		$valueRow = $ilDB->fetchAssoc ( $valueSet );
		$value = $valueRow ['value'];
		return $value;
	}
	
	/**
	 * Determines the maximum amount of a given room attribute and returns it.
	 *
	 * @param type $a_room_attribute
	 *        	the attribute for which the max count
	 *        	should be determined
	 * @return type the max value of the attribute
	 */
	public function getMaxCountForAttribute($a_room_attribute) {
		global $ilDB;
		// get the id of the attribute in this pool
		$attributIdSet = $ilDB->query ( 'SELECT id FROM rep_robj_xrs_rattr WHERE name =' . $ilDB->quote ( $a_room_attribute, 'text' ) . ' AND pool_id = ' . $ilDB->quote ( $this->pool_id, 'integer' ) );
		$attributIdRow = $ilDB->fetchAssoc ( $attributIdSet );
		$attributID = $attributIdRow ['id'];
		
		// get the max value of the attribut in this pool
		$valueSet = $ilDB->query ( 'SELECT MAX(count) AS value FROM rep_robj_xrs_room_attr LEFT JOIN rep_robj_xrs_rooms as room
				 ON room.id = rep_robj_xrs_room_attr.room_id WHERE att_id =' . $ilDB->quote ( $attributID, 'integer' ) . ' AND pool_id =' . $ilDB->quote ( $this->pool_id, 'integer' ) );
		$valueRow = $ilDB->fetchAssoc ( $valueSet );
		$value = $valueRow ['value'];
		return $value;
	}
}
