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

    /**
     * Save floorplan informations
     */
    function save() {
        
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
    function getAllFloorPlans(){
        $allplans = array();
        $path2 = $this->initStorage($this->id, "file");
        //echo $path2;
        $allplans[] =  array( 
                      'pic'   => "Plan (in klein)", 
                      'title'  => "Plan1",
                      'description' => "Dies ist die Beschreibung fuer Plan 1");
        return $allplans;
        
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

}

?>