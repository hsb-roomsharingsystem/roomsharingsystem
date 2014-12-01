<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingRoomException.php");

/**
 * Class ilRoomSharingRoom.
 * Loads data for a room with the given room_id.
 * Frequently the room properties can be edited and saved.
 * If the second argument of the constructor is true (bool),
 * you can create an room.
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 * @author Thomas Wolscht <twolscht@stud.hs-bremen.de>
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
	// Associative. Contains arrays with id, name,
	private $allAvailableAttributes = array();
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
		$this->allAvailableAttributes = $this->ilRoomsharingDatabase->getAllRoomAttributes();

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
		if ($this->hasValidId())
		{
			$row = $this->ilRoomsharingDatabase->getRoom($this->id);
			$this->setName($row['name']);
			$this->setType($row['type']);
			$this->setMinAlloc($row['min_alloc']);
			$this->setMaxAlloc($row['max_alloc']);
			$this->setFileId($row['file_id']);
			$this->setBuildingId($row['building_id']);
			$this->setPoolId($row['pool_id']);

			$this->loadAttributesFromDB();
			$this->loadBookedTimes();
		}
	}

	/**
	 * Saves edited data of an room.
	 * If room id is not set, nothing happens.
	 *
	 * @throws ilRoomSharingRoomException
	 */
	public function save()
	{
		$this->checkMinMaxAlloc();
		$this->updateMainProperties();
		$this->updateAttributes();
	}

	/**
	 * Creates an room with given information through setter methods.
	 * Make sure the name, min_alloc, max_alloc and pool_id are set.
	 *
	 * @return integer The room id of the new room, if everything went fine
	 * 		 (check!).
	 *
	 * @throws ilRoomSharingRoomException
	 */
	public function create()
	{
		$numbersToCheck[] = $this->min_alloc;
		$numbersToCheck[] = $this->max_alloc;
		$numbersToCheck[] = $this->pool_id;

		$numsValid = ilRoomSharingNumericUtils::allNumbersPositive($numbersToCheck, true);

		$this->checkMinMaxAlloc();

		$rtrn = '';
		if ($numsValid && !empty($this->name) && strlen($this->name) > 0)
		{
			$rtrn = $this->ilRoomsharingDatabase->insertRoom($this->name, $this->type, $this->min_alloc,
				$this->max_alloc, $this->file_id, $this->building_id);
			$this->id = $rtrn;
			$this->insertAttributes();
		}
		else
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_room_create_failed');
		}

		return $rtrn;
	}

	/**
	 * Deletes an room and all associations to it.
	 *
	 * @return integer Amount of deleted bookings
	 *
	 * @throws ilRoomSharingRoomException
	 */
	public function delete()
	{
		// Check permission after permissions were implemented.
		if (false)
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_deletion_not_allowed');
		}
		$this->ilRoomsharingDatabase->deleteRoom($this->id);
		$this->ilRoomsharingDatabase->deleteAttributesForRoom($this->id);
		return $this->ilRoomsharingDatabase->deleteBookingsUsesRoom($this->id);
	}

	/**
	 * Returns amount of affected bookings when the user wants to delete a room.
	 *
	 * @return integer
	 */
	public function getAffectedAmountBeforeDelete()
	{
		return count($this->ilRoomsharingDatabase->getActualBookingsForRoom($this->id));
	}

	/**
	 * Adds an attribute to the room.
	 *
	 * @param int $a_attr_id
	 * @param int $a_count
	 *
	 * @throws ilRoomSharingRoomException
	 */
	public function addAttribute($a_attr_id, $a_count)
	{
		// Check arguments
		if (!ilRoomSharingNumericUtils::isPositiveNumber($a_attr_id, true) || !$this->isRealAttribute($a_attr_id))
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_add_wrong_attribute');
		}
		if (!ilRoomSharingNumericUtils::isPositiveNumber($a_count, true))
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_add_wrong_attribute_count');
		}
		// Attribute can be added
		$this->attributes[] = array(
			'id' => $a_attr_id,
			'name' => $this->getAttributeName($a_attr_id),
			'count' => $a_count
		);
	}

	/**
	 * Returns true if the given attribute exists in the database.
	 *
	 * @param integer $a_attr_id
	 * @return boolean
	 */
	private function isRealAttribute($a_attr_id)
	{
		$rVal = FALSE;
		foreach ($this->allAvailableAttributes as $availableAttribute)
		{
			if ($availableAttribute['id'] == $a_attr_id)
			{
				$rVal = TRUE;
				break;
			}
		}
		return $rVal;
	}

	/**
	 * Returns the name of the attribute.
	 *
	 * @param integer $a_attr_id
	 * @return string
	 */
	private function getAttributeName($a_attr_id)
	{
		foreach ($this->allAvailableAttributes as $availableAttribute)
		{
			if ($availableAttribute['id'] == $a_attr_id)
			{
				return $availableAttribute['name'];
			}
		}
	}

	/**
	 * Resets the attributes of the room internaly.
	 * This method does not affects the database.
	 */
	public function resetAttributes()
	{
		$this->attributes = array();
	}

	/**
	 * Get all attributes referenced by the room.
	 * If room id is not set it returns empty array.
	 *
	 * @return array attributes which were assigned to the room.
	 */
	private function loadAttributesFromDB()
	{
		if ($this->hasValidId())
		{
			$this->attributes = $this->ilRoomsharingDatabase->getAttributesForRoom($this->id);
		}
	}

	/**
	 * Loads booking times of the given room.
	 */
	private function loadBookedTimes()
	{
		if ($this->hasValidId())
		{
			$this->booked_times = $this->ilRoomsharingDatabase->getBookingsForRoom($this->id);
		}
	}

	/**
	 * Returns all available attribtes for rooms.
	 *
	 * @return assoc array
	 */
	public function getAllAvailableAttributes()
	{
		return $this->allAvailableAttributes;
	}

	/**
	 * Update main properties of a room.
	 */
	private function updateMainProperties()
	{
		if ($this->hasValidId())
		{
			$this->ilRoomsharingDatabase->updateRoomProperties($this->getId(), $this->getName(),
				$this->getType(), $this->getMinAlloc(), $this->getMaxAlloc(), $this->getFileId(),
				$this->getBuildingId());
		}
	}

	/**
	 * Updates attributes of a room.
	 */
	private function updateAttributes()
	{
		if ($this->hasValidId())
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
		if ($this->hasValidId())
		{
			foreach ($this->attributes as $attr)
			{
				$this->ilRoomsharingDatabase->insertAttributeForRoom($this->id, $attr['id'], $attr['count']);
			}
		}
	}

	/**
	 * Checks whether the room id is valid.
	 *
	 * @return bool True if the room id is set and the room exists in the
	 * 		 database.
	 */
	private function hasValidId()
	{
		return ilRoomSharingNumericUtils::isPositiveNumber($this->id, true);
	}

	/**
	 * Checks min and max allocation of the room.
	 * Throws an exception on illigal values.
	 *
	 * @throws ilRoomSharingRoomException
	 */
	private function checkMinMaxAlloc()
	{
		if (empty($this->min_alloc) || empty($this->max_alloc))
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_illigal_room_min_max_alloc');
		}

		$allocs = array($this->min_alloc, $this->max_alloc);
		if (!ilRoomSharingNumericUtils::allNumbersPositive($allocs, true))
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_illigal_room_min_max_alloc');
		}

		if (((int) $this->min_alloc) > ((int) $this->max_alloc))
		{
			throw new ilRoomSharingRoomException('rep_robj_xrs_illigal_room_min_max_alloc');
		}
	}

	/**
	 * Get all floorplan title.
	 *
	 * @return assoc array with all floorplans
	 */
	public function getAllFloorplans()
	{
		$options = array();
		$options["title"] = " - " . $this->lng->txt("rep_robj_xrs_room_no_assign") . " - ";

		foreach ($this->ilRoomsharingDatabase->getAllFloorplans() as $fplans)
		{
			$options[$fplans["file_id"]] = $fplans["title"];
		}
		return $options;
	}

	/**
	 * 	Searches for an attribute in already defined attributes of an room and returns its amount.
	 *
	 * @param integer $a_attribute_id
	 * @return integer amount
	 */
	public function findAttributeAmount($a_attribute_id)
	{
		foreach ($this->getAttributes() as $attr)
		{
			if ($attr['id'] == $a_attribute_id)
			{
				$rVal = $attr['count'];
				break;
			}
		}
		return $rVal;
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