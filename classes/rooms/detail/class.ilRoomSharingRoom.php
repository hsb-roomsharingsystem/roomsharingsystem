<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");

/**
 * Class ilRoomSharingRoom.
 * Loads data for a room with the given room_id.
 * Frequently the room properties can be edited and saved.
 * If the second argument of the constructor is true (bool),
 * you can create an room.
 *
 * @author Thomas Matern
 *
 * @property ilRoomsharingDatabase $ilRoomsharingDatabase
 */
class ilRoomSharingRoom
{
	private $id;
	private $name;
	private $type;
	private $min_alloc;
	private $max_alloc;
	private $file_id;
	private $building_id;
	private $pool_id;
	// Associative. Contains arrays with id, name, count.
	private $attributes = array();
	// Associative. Contains arrays with id, date_from, date_to...
	private $booked_times = array();
	private $ilRoomsharingDatabase;
	private $lng;

	/**
	 * Constructor for ilRoomSharingRoom.
	 * Reads data from db if an room id is given.
	 * Can be used to create an room. After all informaton is set, call the
	 * method create(). It returns the room id of the new room.
	 *
	 * @param int $a_room_id
	 * @param bool $a_create
	 * 			Set true if you want to create an room.
	 */
	public function __construct($pool_id, $a_room_id, $a_create = false)
	{
		global $lng;

		$this->pool_id = $pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
		if ($a_create == false)
		{
			$this->id = $a_room_id;
			$this->read();
		}
		$this->lng = $lng;
	}

	/**
	 * Get all data from db.
	 * If room id is not given, nothing happens.
	 */
	public function read()
	{
		if ($this->checkId())
		{
			$row = $this->ilRoomsharingDatabase->getRoom($this->id);
			$this->setName($row['name']);
			$this->setType($row['type']);
			$this->setMinAlloc($row['min_alloc']);
			$this->setMaxAlloc($row['max_alloc']);
			$this->setFileId($row['file_id']);
			$this->setBuildingId($row['building_id']);
			$this->setPoolId($row['pool_id']);

			$this->attributes = $this->getAttributesFromDB();
			$this->loadBookedTimes();
		}
	}

	/**
	 * Saves edited data of an room.
	 * If room id is not set, nothing happens.
	 */
	public function save()
	{
		$this->updateMainProperties();
		$this->updateAttributes();
	}

	/**
	 * Creates an room with given information through setter methods.
	 * Make sure the name, min_alloc, max_alloc and pool_id are set.
	 *
	 * @return integer The room id of the new room, if everything went fine
	 * 		 (check!).
	 */
	function create()
	{
		global $lng;
		$numsValid = $this->checkNumProps(
			array(
				$this->min_alloc,
				$this->max_alloc,
				$this->pool_id
		));

		$rtrn = '';
		if ($numsValid && !empty($this->name))
		{
			$rtrn = $this->ilRoomsharingDatabase->insertRoom($this->name, $this->type, $this->min_alloc,
				$this->max_alloc, $this->file_id, $this->building_id);
		}
		else
		{
			ilUtil::sendFailure($lng->txt('rep_robj_xrs_room_create_failed'), true);
		}

		return $rtrn;
	}

	/**
	 * Adds an attribute to the room.
	 *
	 * @param int $a_attr_id
	 * @param int $a_count
	 * @return bool True if the attribute was added successful.
	 */
	protected function addAttribute($a_attr_id, $a_count)
	{
		global $lng;
		// Check arguments
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_attr_id) &&
			ilRoomSharingNumericUtils::isPositiveNumber($a_count))
		{
			// Check whether the attribute is real/exist.
			$attrDB = $this->ilRoomsharingDatabase->getRoomAttribute($a_attr_id);

			if (array_count_values($attrDB) == 0)
			{
				ilUtil::sendFailure($lng->txt('add_wrong_attribute'), true);
				return false;
			}
			// Attribute can be added
			$attrName = $attrDB['name'];
			$this->attributes[] = array(
				'id' => $a_attr_id,
				'name' => $attrName,
				'count' => $a_count
			);
		}
		ilUtil::sendFailure($lng->txt('add_wrong_attribute'), true);
		return false;
	}

	/**
	 * Get all attributes referenced by the room.
	 * If room id is not set it returns empty array.
	 *
	 * @return array attributes which were assigned to the room.
	 */
	protected function getAttributesFromDB()
	{
		$attributes = array();
		if ($this->checkId())
		{
			$attributes = $this->ilRoomsharingDatabase->getAttributesForRoom($this->id);
		}
		return $attributes;
	}

	/**
	 * Loads booking times of the given room.
	 */
	protected function loadBookedTimes()
	{
		if ($this->checkId())
		{
			$this->booked_times = $this->ilRoomsharingDatabase->getBookingsForRoom($this->id);
		}
	}

	public function getAllAvailableAttributes()
	{
		return $this->ilRoomsharingDatabase->getAllRoomAttributes();
	}

	/**
	 * Update main properties of a room.
	 */
	private function updateMainProperties()
	{
		if ($this->checkId())
		{
			$this->ilRoomsharingDatabase->updateRoomProperties($this->getId(), $this->getName(),
				$this->getType(), $this->getMinAlloc(), $this->getMaxAlloc(), $this->getFileId(),
				$this->getBuildingId());
		}
	}

	/**
	 * Updates attributes of a room if such were changed.
	 */
	private function updateAttributes()
	{
		if ($this->checkId() && $this->compareAttributes() &&
			$this->checkAttributes())
		{
			//Delete old attribute associations
			$this->ilRoomsharingDatabase->deleteAttributesForRoom($this->id);
			//Insert the new associations
			$this->insertAttributes();
		}
	}

	/**
	 * Insert attributes if such are set in the room object.
	 */
	private function insertAttributes()
	{
		if ($this->checkId() && $this->checkAttributes())
		{
			foreach ($this->attributes as $row)
			{
				$this->ilRoomsharingDatabase->insertAttributeForRoom($this->id, $row['id'], $row['count']);
			}
		}
	}

	/**
	 * Checks the attributes of a room object.
	 *
	 * @return bool true if attributes are valid (data can be inserted into the
	 * 		 database).
	 */
	private function checkAttributes()
	{
		global $lng;
		if (!empty($this->attributes))
		{
			foreach ($this->attributes as $attr_value)
			{
				// Check whether the number values are numeric.
				if (!ilRoomSharingNumericUtils::isPositiveNumber($attr_value['id']) ||
					!ilRoomSharingNumericUtils::isPositiveNumber($attr_value['count'], true))
				{
					ilUtil::sendFailure($lng->txt('incorrect_attributes'), true);
					return false;
				}
				// Check whether the attributes are real/exist.
				$attrDB = $this->ilRoomsharingDatabase->getRoomAttribute($attr_value['id']);
				if (array_count_values($attrDB) === 0)
				{
					ilUtil::sendFailure($lng->txt('incorrect_attributes'), true);
					return false;
				}
			}
			// All attributes checked and they are fine.
			return true;
		}
		ilUtil::sendFailure($lng->txt('incorrect_attributes'), true);
		return false;
	}

	/**
	 * Compares room attributes set and the room attributes of the database.
	 *
	 * @return bool true if room attributes of the object have no difference
	 * 		 with the database.
	 */
	private function compareAttributes()
	{
		if ($this->attributes == $this->getAttributesFromDB())
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the room id is valid.
	 *
	 * @return bool True if the room id is set and the room exists in the
	 * 		 database.
	 */
	private function checkId()
	{
		$rtrn = FALSE;
		if (ilRoomSharingNumericUtils::isPositiveNumber($this->id))
		{
			$room = $this->ilRoomsharingDatabase->getRoom($this->id);
			if (count($room) > 0)
			{
				$rtrn = TRUE;
			}
		}
		return $rtrn;
	}

	/**
	 * Checks the given array
	 *
	 * @param array $a_props
	 * 			Array with properties to check.
	 * @return bool True if all properties are not empty and numeric.
	 */
	private function checkNumProps($a_props)
	{
		foreach ($a_props as $prop)
		{
			if (empty($prop) || !is_numeric($prop))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Get all floorplan title.
	 * @return array with all floorplan title
	 */
	public function getAllFloorplans()
	{
		$options = array();
		$options["title"] = " - " . $this->lng->txt("rep_robj_xrs_room_no_assign") . " - ";

		foreach ($this->ilRoomsharingDatabase->getAllFloorplans() as $fplans)
		{
			$options[$fplans["title"]] = $fplans["title"];
		}
		return $options;
	}

	/**
	 * Get floorplan file-id by name.
	 * @param name
	 * @return file-id
	 */
	public function getFloorplanIdByName($name)
	{
		foreach ($this->ilRoomsharingDatabase->getAllFloorplans() as $fplans)
		{
			if ($name == $fplans["title"])
			{
				return (int) $fplans["file_id"];
			}
		}
	}

	/**
	 * Get floorplan name by file-id.
	 * @param file-id
	 * @return name
	 */
	public function getFloorplanNameById($id)
	{
		foreach ($this->ilRoomsharingDatabase->getAllFloorplans() as $fplans)
		{
			if ($id == $fplans["file_id"])
			{
				return $fplans["title"];
			}
		}
	}

	/**
	 * Get the id of the room.
	 *
	 * @return int RoomID
	 */
	public function getId()
	{
		return (int) $this->id;
	}

	/**
	 * Set the room-id
	 *
	 * @param int $a_id ID which should be set
	 */
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get the name of the room.
	 *
	 * @return string RoomName
	 */
	public function getName()
	{
		return (string) $this->name;
	}

	/**
	 * Set the name of the room
	 *
	 * @param int $a_name Room-Name
	 */
	public function setName($a_name)
	{
		$this->name = $a_name;
	}

	/**
	 * Get the type of the room
	 *
	 * @return string Room-Type
	 */
	public function getType()
	{
		return (string) $this->type;
	}

	/**
	 * Set the type of the room
	 *
	 * @param string $a_type Room-Type
	 */
	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	 * Get the mininmal allocation of the room.
	 *
	 * @return int Mininmal-Allocation
	 */
	public function getMinAlloc()
	{
		return (int) $this->min_alloc;
	}

	/**
	 * Set the minimal allocation of the room
	 *
	 * @param integer $a_min_alloc Minimal-Allocation
	 */
	public function setMinAlloc($a_min_alloc)
	{
		$this->min_alloc = $a_min_alloc;
	}

	/**
	 * Get the maximum allocation of the room
	 *
	 * @return integer Maximum-Allocation
	 */
	public function getMaxAlloc()
	{
		return (int) $this->max_alloc;
	}

	/**
	 * Set the maximal allocation of the room
	 *
	 * @param integer $a_max_alloc Maximal-Allocation
	 */
	public function setMaxAlloc($a_max_alloc)
	{
		$this->max_alloc = $a_max_alloc;
	}

	/**
	 * Get the FileID of the room
	 *
	 * @return FileID
	 */
	public function getFileId()
	{
		return (int) $this->fileId;
	}

	/**
	 * Set the FileID of the room
	 *
	 * @param int $a_fileId FileID
	 */
	public function setFileId($a_fileId)
	{
		$this->file_id = $a_fileId;
	}

	/**
	 * Get the BuildingID of the room
	 *
	 * @return integer BuildingID
	 */
	public function getBuildingId()
	{
		return (int) $this->building_id;
	}

	/**
	 * Set the BuildingID of the room
	 *
	 * @param int $a_buildingId BuildingID
	 */
	public function setBuildingId($a_buildingId)
	{
		$this->building_id = $a_buildingId;
	}

	/**
	 * Get the PoolID of the room
	 *
	 * @return integer PoolID
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

	/**
	 * Set the PoolID of the room
	 *
	 * @param integer $a_poolId PoolID
	 */
	public function setPoolId($a_poolId)
	{
		$this->pool_id = $a_poolId;
		$this->ilRoomsharingDatabase->setPoolId($a_poolId);
	}

	/**
	 * Get attributes of the room
	 * Contains an associative array with id, name, count.
	 *
	 * @return array Attributes as associative array
	 */
	public function getAttributes()
	{
		return (array) $this->attributes;
	}

	/**
	 * Set attributes of the room
	 *
	 * @param array $a_attributes Associative array with attributes
	 */
	public function setAttributes($a_attributes)
	{
		$this->attributes = $a_attributes;
	}

	/**
	 * Get booked times.
	 * Contains an associative array with id, date_from, date_to...
	 *
	 * @return array Booked Times as associative array
	 */
	public function getBookedTimes()
	{
		return $this->booked_times;
	}

	/**
	 * Set booked times.
	 * Associative. Contains arrays with id, date_from, date_to...
	 *
	 * @param array $a_booked_times
	 */
	public function setBookedTimes($a_booked_times)
	{
		$this->booked_times = $a_booked_times;
	}

}

?>