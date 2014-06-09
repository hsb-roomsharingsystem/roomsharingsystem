<?php

/**
 * Class ilRoomSharingRoom.
 * Loads data for an room with the given room_id. Frequently the room properties can be edited and saved.
 * If the second argument of the constructor is true (bool), you can create an room.
 *
 * @author tmatern
 * @version $Id$
 */
class ilRoomSharingRoom
{
    protected $id;
    protected $name;
    protected $type;
    protected $min_alloc;
    protected $max_alloc;
    protected $file_id;
    protected $building_id;
    protected $pool_id;
    // Associative. Contains arrays with id, name, count.
    protected $attributes = array();
    // Associative. Contains arrays with id, date_from, date_to...
    protected $booked_times = array();

    /**
     * Constructor for ilRoomSharingRoom.
     * Reads data from db if an room id is given.
     * Can be used to create an room. After all informaton is set, call the
     * method create(). It returns the room id of the new room.
     *
     * @param int $a_room_id            
     * @param bool $a_create
     *            Set true if you want to create an room.
     */
    function __construct ($a_room_id, $a_create = false)
    {
        if ($a_create == false) {
            $this->id = $a_room_id;
            $this->read();
        }
    }

    /**
     * Get all data from db.
     * If room id is not given, nothing happens.
     */
    public function read ()
    {
        global $ilDB;
        
        if ($this->checkId()) {
            $set = $ilDB->query(
                    'SELECT *' . ' FROM rep_robj_xrs_rooms' . ' WHERE id = ' .
                             $ilDB->quote($this->id, 'integer'));
            $row = $ilDB->fetchAssoc($set);
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
    public function save ()
    {
        $this->updateMainProperties();
        $this->updateAttributes();
    }

    /**
     * Creates an room with given information through setter methods.
     * Make sure the name, min_alloc, max_alloc and pool_id are set.
     *
     * @return integer The room id of the new room, if everything went fine
     *         (check!).
     */
    function create ()
    {
        global $ilDB, $lng;
        $numsValid = $this->checkNumProps(
                array(
                        $this->min_alloc,
                        $this->max_alloc,
                        $this->pool_id
                ));
        if ($numsValid && ! empty($this->name)) {
            $ilDB->insert('rep_robj_xrs_rooms', 
                    array(
                            'name' => array(
                                    'integer',
                                    $this->id
                            ),
                            'type' => array(
                                    'integer',
                                    $this->type
                            ),
                            'min_alloc' => array(
                                    'integer',
                                    $this->min_alloc
                            ),
                            'max_alloc' => array(
                                    'integer',
                                    $this->max_alloc
                            ),
                            'file_id' => array(
                                    'integer',
                                    $this->file_id
                            ),
                            'building_id' => array(
                                    'integer',
                                    $this->building_id
                            ),
                            'pool_id' => array(
                                    'integer',
                                    $this->pool_id
                            )
                    ));
            return $ilDB->getLastInsertId();
        } else {
            ilUtil::sendFailure($lng->txt('room_create_failed'), true);
            return '';
        }
    }

    /**
     * Adds an attribute to the room.
     *
     * @param int $a_attr_id            
     * @param int $a_count            
     * @return bool True if the attribute was added successful.
     */
    public function addAttribute ($a_attr_id, $a_count)
    {
        global $ilDB;
        // Check arguments
        if (! empty($a_attr_id) && is_numeric($a_attr_id) && ! empty($a_count) &&
                 is_numeric($a_count) && $a_count > 0) {
            // Check whether the attribute is real/exist.
            $attrDB = $ilDB->fetchAssoc(
                    $ilDB->query(
                            'SELECT * FROM rep_robj_xrs_rattr WHERE id = ' .
                             $ilDB->quote($attr['id'], 'integer')));
            if (array_count_values($attrDB) == 0) {
                ilUtil::sendFailure($lng->txt('add_wrong_attribute'), true);
                return false;
            }
            // Attribute can be added
            $result = $ilDB->fetchAssoc(
                    $ilDB->query(
                            'SELECT * FROM rep_robj_xrs_rattr WHERE id = ' .
                                     $ilDB->quote($a_attr_id, 'integer')));
            $attrName = $result['name'];
            $this->attributes[] = array(
                    'id' => $a_attr_id,
                    'name' => attrName,
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
    protected function getAttributesFromDB ()
    {
        global $ilDB;
        $result = array();
        if ($this->checkId()) {
            $attributes = $ilDB->query(
                    'SELECT id, att.name, count FROM rep_robj_xrs_room_attr ' .
                             ' LEFT JOIN rep_robj_xrs_rattr as att ON att.id = rep_robj_xrs_room_attr.att_id' .
                             ' WHERE room_id = ' . $ilDB->quote($this->id, 
                                    'integer') . ' ORDER BY att.name');
            while ($row = $ilDB->fetchAssoc($attributes)) {
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Loads booking times to the given room.
     */
    protected function loadBookedTimes ()
    {
        global $ilDB;
        
        $result = array();
        if ($this->checkId()) {
            $booked_times = $ilDB->query(
                    'SELECT * FROM rep_robj_xrs_bookings WHERE room_id = ' .
                             $ilDB->quote($this->id, 'integer'));
            while ($row = $ilDB->fetchAssoc($booked_times)) {
                $result[] = $row;
            }
        }
        $this->booked_times = $result;
    }

    /**
     * Update main properties of an room.
     */
    protected function updateMainProperties ()
    {
        global $ilDB;
        if ($this->checkId()) {
            $table = "rep_robj_xrs_rooms";
            $fields = array(
                    "name" => array(
                            "text",
                            $this->getName()
                    ),
                    "type" => array(
                            "text",
                            $this->getType()
                    ),
                    "min_alloc" => array(
                            "integer",
                            $this->getMinAlloc()
                    ),
                    "max_alloc" => array(
                            "integer",
                            $this->getMaxAlloc()
                    ),
                    "file_id" => array(
                            "integer",
                            $this->getFileId()
                    ),
                    "building_id" => array(
                            "integer",
                            $this->getBuildingId()
                    )
            );
            $where = array(
                    "id" => array(
                            "integer",
                            $this->id
                    )
            );
            $ilDB->update($table, $fields, $where);
        }
    }

    /**
     * Updates attributes of an room if such were changed.
     */
    protected function updateAttributes ()
    {
        global $ilDB;
        
        if ($this->checkId() && $this->compareAttributes() &&
                 $this->checkAttributes()) {
            
            $ilDB->query(
                    'DELETE FROM rep_robj_xrs_room_attr WHERE room_id = ' .
                     $ilDB->quote($this->id, 'integer'));
            $this->insertAttributes();
        }
    }

    /**
     * Inserts attributes if such are set in the room object.
     */
    protected function insertAttributes ()
    {
        if ($this->checkId() && $this->checkAttributes()) {
            foreach ($this->attributes as $rows => $row) {
                $ilDB->insert('rep_robj_xrs_room_attr', 
                        array(
                                'room_id' => array(
                                        'integer',
                                        $this->id
                                ),
                                'att_id' => array(
                                        'integer',
                                        $row['id']
                                ),
                                'count' => array(
                                        'integer',
                                        $row['count']
                                )
                        ));
            }
        }
    }

    /**
     * Checks the attributes of an room object.
     *
     * @return bool true if attributes are valid (data can be inserted into the
     *         database).
     */
    protected function checkAttributes ()
    {
        global $lng, $ilDB;
        if (! empty($this->attributes)) {
            foreach ($this->attributes as $attrs => $attr) {
                // Check whether the number values are numeric.
                if (! is_numeric($attr['id']) || ! is_numeric($attr['count'])) {
                    ilUtil::sendFailure($lng->txt('incorrect_attributes'), true);
                    return false;
                }
                // Check whether the attributes are real/exist.
                $attrDB = $ilDB->fetchAssoc(
                        $ilDB->query(
                                'SELECT * FROM rep_robj_xrs_rattr WHERE id = ' .
                                         $ilDB->quote($attr['id'], 'integer')));
                if (array_count_values($attrDB) == 0) {
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
     * Compares set room attributes and room attributes from the database.
     *
     * @return bool true if room attributes of the object have no differense
     *         with the database.
     */
    protected function compareAttributes ()
    {
        if ((0 != array_count_values(
                array_diff_assoc($this->attributes, 
                        $this->getAttributesFromDB())))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks whether the room id is valid.
     *
     * @return bool True if the room id is set and the room exists in the
     *         database.
     */
    protected function checkId ()
    {
        global $ilDB;
        if (isset($this->id) && (! empty($this->id)) && is_numeric($this->id)) {
            $room = $ilDB->fetchAssoc(
                    $ilDB->query(
                            'SELECT *' . ' FROM rep_robj_xrs_rooms' .
                                     ' WHERE id = ' .
                                     $ilDB->quote($this->id, 'integer')));
            if (count($room) != 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks the given array
     *
     * @param array $a_props
     *            Array with properties to check.
     * @return bool True if all properties are not empty and numeric.
     */
    protected function checkNumProps ($a_props)
    {
        foreach ($a_props as $prop) {
            if (empty($prop) && ! is_numeric($prop)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get id.
     */
    public function getId ()
    {
        return (int) $this->id;
    }

    /**
     * Set id.
     *
     * @param int $a_id            
     */
    public function setId ($a_id)
    {
        $this->id = $a_id;
    }

    /**
     * Get name.
     */
    public function getName ()
    {
        return (string) $this->name;
    }

    /**
     * Set name.
     *
     * @param int $a_name            
     */
    public function setName ($a_name)
    {
        $this->name = $a_name;
    }

    /**
     * Get type.
     */
    public function getType ()
    {
        return (string) $this->type;
    }

    /**
     * Set type.
     *
     * @param string $a_type            
     */
    public function setType ($a_type)
    {
        $this->type = $a_type;
    }

    /**
     * Get min allocation.
     */
    public function getMinAlloc ()
    {
        return (int) $this->min_alloc;
    }

    /**
     * Set min allocation.
     *
     * @param integer $a_min_alloc            
     */
    public function setMinAlloc ($a_min_alloc)
    {
        $this->min_alloc = $a_min_alloc;
    }

    /**
     * Get max allocation.
     */
    public function getMaxAlloc ()
    {
        return (int) $this->max_alloc;
    }

    /**
     * Set max allocation.
     *
     * @param integer $a_max_alloc            
     */
    public function setMaxAlloc ($a_max_alloc)
    {
        $this->max_alloc = $a_max_alloc;
    }

    /**
     * Get fileId.
     */
    public function getFileId ()
    {
        return (int) $this->fileId;
    }

    /**
     * Set fileId.
     *
     * @param int $a_fileId            
     */
    public function setFileId ($a_fileId)
    {
        $this->file_id = $a_fileId;
    }

    /**
     * Get buildingId.
     */
    public function getBuildingId ()
    {
        return (int) $this->building_id;
    }

    /**
     * Set buildingId.
     *
     * @param int $a_buildingId            
     */
    public function setBuildingId ($a_buildingId)
    {
        $this->building_id = $a_buildingId;
    }

    /**
     * Get poolId.
     */
    public function getPoolId ()
    {
        return (int) $this->pool_id;
    }

    /**
     * Set poolId.
     *
     * @param int $a_poolId            
     */
    public function setPoolId ($a_poolId)
    {
        $this->pool_id = $a_poolId;
    }

    /**
     * Get attributes.
     * Associative. Contains arrays with id, name, count.
     */
    public function getAttributes ()
    {
        return (array) $this->attributes;
    }

    /**
     * Set attributes.
     *
     * @param array $a_attributes            
     */
    public function setAttributes ($a_attributes)
    {
        $this->attributes = $a_attributes;
    }

    /**
     * Get booked times.
     * Associative. Contains arrays with id, date_from, date_to...
     */
    public function getBookedTimes ()
    {
        return $this->booked_times;
    }

    /**
     * Set booked times.
     * Associative. Contains arrays with id, date_from, date_to...
     *
     * @param array $a_booked_times            
     */
    public function setBookedTimes ($a_booked_times)
    {
        $this->booked_times = $a_booked_times;
    }
}

?>