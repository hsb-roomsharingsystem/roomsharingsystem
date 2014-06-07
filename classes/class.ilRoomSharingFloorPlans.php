<?php

/**
 * Class ilRilRoomSharingFloorPlans
 * Stores all available data to an floor plan.
 *
 * @author T. Wolscht
 */
class ilRoomSharingFloorPlans {

    protected $id;   // int
    protected $pool_id;  // int
    protected $title;  // string
    protected $description; // string
    protected $floorplan_file; // floorplan file

    function __construct($a_id = NULL) {
        $this->id = (int) $a_id;
    }

    /**
     * Set FloorPlan title
     * @param	string	$a_title
     */
    function setTitle($a_title) {
        $this->title = $a_title;
    }

    /**
     * Set floorplan description
     * @param	string	$a_title
     */
    function setDescription($a_desc) {
        $this->description = $a_desc;
    }

    /**
     * Set floorplan file
     * @param	string	$a_value
     */
    function setFile($a_value) {
        $this->floorplan_file = $a_value;
    }
    
    function setPoolID($pool_id){
        $this->pool_id = $pool_id;
    }

    /**
     * Upload new roomsharing file
     * 
     * @param array $a_upload
     * @return bool
     */
    function uploadFile(array $a_upload) {
//        if (!$this->id) {
//            echo "hihihihi";
//            return false;
//        }

        $this->deleteFile();

        $path = $this->initStorage($this->id, "file");
        $original = $a_upload["name"];

        if (@move_uploaded_file($a_upload["tmp_name"], $path . $original)) {
            chmod($path . $original, 0770);

            $this->setFile($original);
            return true;
        }
        return false;
    }

    /**
     * remove existing floorplan file
     */
    public function deleteFile() {
        if ($this->id) {
            $path = $this->getFileFullPath();
            if ($path) {
                @unlink($path);
                $this->setFile(null);
            }
        }
    }

    /**
     * Get path to info file
     */
    function getFileFullPath() {
        if ($this->id && $this->floorplan_file) {
            $path = $this->initStorage($this->id, "file");
            return $path . $this->floorplan_file;
        }
    }

    /**
     * Get Array of all floorplans
     */
    function getAllFloorPlans() {
        global $ilDB;

        $set = $ilDB->query('SELECT * FROM rep_robj_xrs_fplans order by file_id DESC');

        $floorplans = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $floorplans [] = $row;
        }
        //$res = $this->formatDataForGui ( $rooms );
        //return $res;
        return $floorplans;
    }

    /**
     * Init file system storage
     * 
     * @param type $a_id
     * @param type $a_subdir
     * @return string 
     */
    public static function initStorage($a_id, $a_subdir = null) {
        include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilFSStorageRoomPlan.php";
        $storage = new ilFSStorageRoomPlan($a_id);
        $storage->create();
        $path = $storage->getAbsolutePath() . "/";
        $path2 = $storage->getAbsolutePath() . "/file";
        $allfiles = scandir($path2);
//        foreach($allfiles as $file){
//            echo $file."<br>";
//          //  echo "$storage->getAbsolutePath() ."/file/"";
//          //  echo $path2;
//        }
        if ($a_subdir) {
            $path .= $a_subdir . "/";

            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        return $path;
    }

    /**
     * Returns all available rooms
     *
     * @return string
     */
    public function getAllRooms() {
        global $ilDB;

        $set = $ilDB->query('SELECT name, file_id FROM roomsharing_rooms ORDER BY name');

        $rooms = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $rooms [] = $row;
        }
        //$res = $this->formatDataForGui ( $rooms );
        //return $res;
        return $rooms;
    }

    /**
     * Inserts the just uploaded file to Roomsharing database
     */
    public function fileToDatabase($file_id) {
        global $ilDB;
        if ($file_id) {
          //  $next_id = $ilDB->nextId('roomsharing_floorplans');
            return $ilDB->manipulate('INSERT INTO rep_robj_xrs_fplans'.
			' (file_id, pool_id)'.
			' VALUES ('.$ilDB->quote($file_id, 'integer').
			','.$ilDB->quote(99, 'integer').')');
            
        } 
    }
    
    /**
     * Deletes a floorplan by id
     * 
     * @global type $ilDB
     * @param type $fid
     * @return type
     */
    public function deleteFloorPlan($fid){
        global $ilDB;
        if($fid){
            return $ilDB->manipulate('DELETE FROM rep_robj_xrs_fplans'.
                        ' WHERE file_id = '.$ilDB->quote($fid, 'integer'));
        }
        else{
            return 0;
            echo "Keine ID angegeben";
        }
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mediaObj = new ilObjMediaObject($fid);
        $mediaObj->delete();
    }

}

?>