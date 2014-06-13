<?php

/**
 * Class ilRilRoomSharingFloorPlans
 * Stores all available data to an floor plan.
 *
 * @author Thomas Wolscht <t.wolscht@googlemail.com>
 */
class ilRoomSharingFloorPlans {

    protected $id;   // int

    function __construct($a_id = NULL) {
        $this->id = (int) $a_id;
    }

    /**
     * Set floorplan file
     * @param	string	$a_value
     */
    function setFile($a_value) {
        $this->floorplan_file = $a_value;
    }

    function setPoolID($pool_id) {
        $this->pool_id = $pool_id;
    }

    /**
     * This function is used, if the upload of a file will be done manually
     * and not by using the ILIAS-Service
     * 
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
     * This function is used, if the upload of a file will be done manually
     * and not by using the ILIAS-Service
     * remove existing floorplan file
     */
    function deleteFile() {
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
     * Get an Array of all floorplans
     */
    function getAllFloorPlans() {
        global $ilDB;

        $set = $ilDB->query('SELECT * FROM rep_robj_xrs_fplans order by file_id DESC');
        $floorplans = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $floorplans [] = $row;
//            $floorplans['file_id']
//            $floorplans['title'] = "meintitel";
            //echo $row['file_id']." ";
        }
        return $floorplans;
    }

    /**
     * Get Array of infos of a floorplan
     * 
     * @global type $ilDB
     * @param type $id
     * @return type
     */
    function getFloorPlan($id) {
        global $ilDB;
        $set = $ilDB->query('SELECT * FROM rep_robj_xrs_fplans WHERE file_id = ' . $ilDB->quote($id, 'integer'));
        $floorplan = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $floorplan [] = $row;
        }
        //$res = $this->formatDataForGui ( $rooms );
        //return $res;
        return $floorplan;
    }

    /**
     * This function is used, if the upload of a file will be done manually
     * and not by using the ILIAS-Service
     * 
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
        if ($a_subdir) {
            $path .= $a_subdir . "/";

            if (!is_dir($path)) {
                mkdir($path);
            }
        }
        return $path;
    }

    /**
     * Inserts the just uploaded file to Roomsharing database
     */
    public function fileToDatabase($file_id) {
        global $ilDB;
        if ($file_id) {
            //  $next_id = $ilDB->nextId('roomsharing_floorplans');
            return $ilDB->manipulate('INSERT INTO rep_robj_xrs_fplans' .
                            ' (file_id, pool_id)' .
                            ' VALUES (' . $ilDB->quote($file_id, 'integer') .
                            ',' . $ilDB->quote(99, 'integer') . ')');
        }
    }

    /**
     * Deletes a floorplan by id
     * 
     * @global type $ilDB
     * @param type $fid
     * @return type
     */
    function deleteFloorPlan($fid) {
        global $ilDB;
        if ($fid) {
            return $ilDB->manipulate('DELETE FROM rep_robj_xrs_fplans' .
                            ' WHERE file_id = ' . $ilDB->quote($fid, 'integer'));
        } else {
            // no id given
            return 0;
        }
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mediaObj = new ilObjMediaObject($fid);
        $mediaObj->removeAllMediaItems();
        $mediaObj->delete();
    }

    /**
     * Updates Floorplan-Infos (Title, Desc.)
     * This function updates a floorplan and sets a new title and a
     * new description. The floorplan (file) won't be replaced.
     * 
     * @param type $id
     * @param type $title
     * @param type $desc
     * @param type $newfile
     */
    function updateFpInfos($id, $title, $desc) {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mediaObj = new ilObjMediaObject($id);
        $mediaObj->setTitle($title);
        $mediaObj->setDescription($desc);
        $mediaObj->update();
    }

    /**
     * Updates Floorplan-Infos (Title, Desc., File)
     * This function updates a floorplan and sets a new title,
     * new description and adds a new file. The old file will be
     * removed.
     * 
     * @param type $id
     * @param type $title
     * @param type $desc
     * @param type $newfile
     */
    function updateFpInfosAndFile($id, $title, $desc, $newfile) {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
        $mediaObj = new ilObjMediaObject($id);
        $mediaObj->setTitle($title);
        $mediaObj->setDescription($desc);
        $mediaObj->removeAllMediaItems();
        $mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());
        $media_item = new ilMediaItem();
        $mediaObj->addMediaItem($media_item);
        $media_item->setPurpose("Standard");
        $file_name = ilUtil::getASCIIFilename($newfile["name"]);
        $file_name = str_replace(" ", "_", $file_name);
        $file = $mob_dir . "/" . $file_name;
        ilUtil::moveUploadedFile($newfile["tmp_name"], $file_name, $file);
        ilUtil::renameExecutables($mob_dir);
        $format = ilObjMediaObject::getMimeType($file);
        $media_item->setFormat($format);
        $media_item->setLocation($file_name);
        $media_item->setLocationType("LocalFile");
        $mediaObj->update();
    }

    function addFloorPlan($title, $desc, $newfile) {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
        $mediaObj = new ilObjMediaObject();
        $mediaObj->setTitle($title);
        $mediaObj->setDescription($desc);
        $mediaObj->create();
        $mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());
        if (!is_dir($mob_dir)) {
            $mediaObj->createDirectory();
        }
        $media_item = new ilMediaItem();
        $mediaObj->addMediaItem($media_item);
        $media_item->setPurpose("Standard");


        $file_name = ilUtil::getASCIIFilename($newfile["name"]);
        $file_name = str_replace(" ", "_", $file_name);
        $file = $mob_dir . "/" . $file_name;
        ilUtil::moveUploadedFile($newfile["tmp_name"], $file_name, $file);
        ilUtil::renameExecutables($mob_dir);
        $format = ilObjMediaObject::getMimeType($file);
        $media_item->setFormat($format);
        $media_item->setLocation($file_name);
        $media_item->setLocationType("LocalFile");
        $mediaObj->update();
        $result = $this->fileToDatabase($mediaObj->getId());
        return 1;
    }

}

?>