<?php
/**
 * Class ilRoomSharingBookableRooms
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Dan Soergel <dansoergel@t-online.de>
 * @author Malte Ahlering <mahlering@stud.hs-bremen.de>
 */
class ilRoomSharingBookableRooms {
	/**
	 * Get the rooms for a given ppol_id from database
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
			print_r ( $filter );
			echo "<br />";
		}
		
		/*
		 * Add filters to query strings
		 */
		
		$select_query = 'SELECT room.id, name, max_alloc FROM roomsharing_rooms room ';
		$order_by_name = ' ORDER BY name ';
		$join_part = ' ';
		$where_part = ' WHERE room.pool_id = ' . $ilDB->quote ( 1, 'integer' ) . ' '; // 1 until yet
		
		if ($filter ["room_name"]) {
			$where_part = $where_part . ' AND name LIKE ' . $ilDB->quote ( '%' . $filter ["room_name"] . '%', 'text' ) . ' ';
		}
		if ($filter ["room_seats"]) {
			$where_part = $where_part . ' AND max_alloc >= ' . $ilDB->quote ( $filter ["room_seats"], 'integer' ) . ' ';
		}
		if ($filter ["date_from"] && $filter ["date_to"]) {
			
			ilUtil::sendInfo ( "Filtern nach Datum noch nicht implementiert", false );
			
			if ($filter ["time_duration"]) {
				ilUtil::sendInfo ( "Filtern nach Zeitspanne noch nicht implementiert", false );
			}
		}
		
		$query = $select_query . $join_part . $where_part . $order_by_name;
		
		if ($debug) {
			echo "<br />";
			echo $query;
			echo "<br />";
		}
		
		$set = $ilDB->query ( $query );
		
		$res_room = array ();
		$room_ids = array ();
		while ( $row = $ilDB->fetchAssoc ( $set ) ) {
			$res_room [] = $row;
			$room_ids [] = $row ['id']; // Remember the ids in order to filter for room attributes within the number of room ids
		}
		
		if ($debug) {
			echo "<br />";
			print_r ( $res_room );
			echo "<br />";
		}
		
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
		
		$set = $ilDB->query ( 'SELECT name FROM roomsharing_attributes ORDER BY name' );
		
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
		
		$st = $ilDB->prepare ( 'SELECT room_id, att.name, count FROM roomsharing_room_attr ' . ' LEFT JOIN roomsharing_attributes as att ON att.id = roomsharing_room_attr.att_id' . ' WHERE ' . $ilDB->in ( "room_id", $room_ids ) . ' ORDER BY room_id, att.name', $ilDB->addTypesToArray ( $types, "integer", count ( $room_ids ) ) );
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
}
