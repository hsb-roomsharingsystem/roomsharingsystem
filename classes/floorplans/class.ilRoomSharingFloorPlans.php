<?php

include_once '/../database/class.ilRoomSharingDatabase.php';
include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");

/**
 * Class ilRoomSharingFloorPlans
 *
 * This class represents the backend of the RoomSharing floor plans.
 *
 * @author Thomas Wolscht <t.wolscht@googlemail.com>
 */
class ilRoomSharingFloorPlans
{
	protected $pool_id;
	protected $ilRoomsharingDatabase;

	/**
	 * Constructor of ilRoomSharingFloorPlans.
	 * @param type $a_pool_id the pool id of the plugin instance
	 */
	public function __construct($a_pool_id = 1)
	{
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Get an array that contains all floor plans.
	 *
	 * @global type $ilDB the database instance
	 * @return type array containing all of the floor plans
	 */
	public function getAllFloorPlans()
	{
		global $ilDB;

		$set = $this->ilRoomsharingDatabase->getAllFloorplans();
		$floorplans = array();
		$row = $ilDB->fetchAssoc($set);
		while ($row)
		{
			$floorplans [] = $row;
			$row = $ilDB->fetchAssoc($set);
		}
		return $floorplans;
	}

	/**
	 * Returns an array that contains all information to a floor plan.
	 * @global type $ilDB the ilias db instance
	 * @param type $a_file_id the id of the floor plan
	 * @return type the result
	 */
	public function getFloorPlanInfo($a_file_id)
	{
		global $ilDB;
		$set = $this->ilRoomsharingDatabase->getFloorplan($a_file_id);
		$floorplan = array();
		$row = $ilDB->fetchAssoc($set);
		while ($row)
		{
			$floorplan [] = $row;
			$row = $ilDB->fetchAssoc($set);
		}
		return $floorplan;
	}

	/**
	 * Inserts the file id of the uploaded image file to the database.
	 * @global type $ilDB the ilias database instance
	 * @param type $a_file_id the file id of the floor plan image
	 * @return type the result of the database manipulation
	 */
	public function fileToDatabase($a_file_id)
	{
		if ($a_file_id)
		{
			return $this->ilRoomsharingDatabase->insertFloorplan($a_file_id);
		}
	}

	/**
	 * Deletes a floor plan by file id.
	 * @global type $ilDB ilias db instance
	 * @param type $a_file_id the file id of the floor plan
	 * @return the result of the manipulation
	 */
	public function deleteFloorPlan($a_file_id)
	{
		$res = null;
		if ($a_file_id)
		{
			$mediaObj = new ilObjMediaObject($a_file_id);
			$mediaObj->removeAllMediaItems();
			$mediaObj->delete();
			$res = $this->ilRoomsharingDatabase->deleteFloorplan($a_file_id);
		}
		return $res;
	}

	/**
	 * This function updates the information of a floor plan, which means
	 * that a new title and a new description will be added. The old floor plan
	 * (file) will be kept.
	 *
	 * @param type $a_file_id the id of the floor plan
	 * @param type $a_title the new title of the floor plan
	 * @param type $a_desc the new description for the floor plan
	 */
	public function updateFpInfos($a_file_id, $a_title, $a_desc)
	{
		$mediaObj = new ilObjMediaObject($a_file_id);
		$mediaObj->setTitle($a_title);
		$mediaObj->setDescription($a_desc);
		$mediaObj->update();
	}

	/**
	 * This function updates the information of a floor plan, which means
	 * that a new title and a new description will be added. The old floor plan
	 * will be removed in order to be replaced by the newly provided one.
	 *
	 * @param type $a_file_id the floor plan id
	 * @param type $a_title the new title
	 * @param type $a_desc the new description
	 * @param type $a_newfile the new image
	 */
	public function updateFpInfosAndFile($a_file_id, $a_title, $a_desc, $a_newfile = null)
	{
		$mediaObj = new ilObjMediaObject($a_file_id);
		$mediaObj->setTitle($a_title);
		$mediaObj->setDescription($a_desc);
		$mediaObj->removeAllMediaItems();

		$mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());
		$media_item = new ilMediaItem();
		$mediaObj->addMediaItem($media_item);
		$media_item->setPurpose("Standard");

		$file_name = ilUtil::getASCIIFilename($a_newfile["name"]);
		$file_name_mod = str_replace(" ", "_", $file_name);
		$file = $mob_dir . "/" . $file_name_mod; // construct file path
		ilUtil::moveUploadedFile($a_newfile["tmp_name"], $file_name_mod, $file);
		ilUtil::renameExecutables($mob_dir);
		$format = ilObjMediaObject::getMimeType($file);

		if ($this->checkImageType($format) == false)
		{
			return false;
		}

		$media_item->setFormat($format);
		$media_item->setLocation($file_name_mod);
		$media_item->setLocationType("LocalFile");
		$mediaObj->update();

		return true;
	}

	/**
	 * Creates a new floor plan by using the ILIAS MediaObject Service
	 * and leaves a database entry.
	 * @param type $a_title the title of the floor plan
	 * @param type $a_desc the floor plan description
	 * @param type $a_newfile an array containing the input values of the form
	 * @return type success or failure
	 */
	public function addFloorPlan($a_title, $a_desc, $a_newfile)
	{
		$mediaObj = new ilObjMediaObject();
		$mediaObj->setTitle($a_title);
		$mediaObj->setDescription($a_desc);
		$mediaObj->create();
		$mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());
		if (!is_dir($mob_dir))
		{  // if the directory doesn't exist, create one
			$mediaObj->createDirectory();
		}
		$media_item = new ilMediaItem();
		$mediaObj->addMediaItem($media_item);
		$media_item->setPurpose("Standard");

		$file_name = ilUtil::getASCIIFilename($a_newfile["name"]);
		$file_name_mod = str_replace(" ", "_", $file_name);
		$file = $mob_dir . "/" . $file_name_mod;
		ilUtil::moveUploadedFile($a_newfile["tmp_name"], $file_name_mod, $file);
		ilUtil::renameExecutables($mob_dir);
		$format = ilObjMediaObject::getMimeType($file);

		if ($this->checkImageType($format) == false)
		{
			return false;
		}

		$media_item->setFormat($format);
		$media_item->setLocation($file_name_mod);
		$media_item->setLocationType("LocalFile");
		$mediaObj->update();

		$result = $this->fileToDatabase($mediaObj->getId());
		return $result;
	}

	public function checkImageType($a_mimeType)
	{
		//Check for image format
		switch ($a_mimeType)
		{
			//Formats for type ".bmp"
			case "image/bmp":
			case "image/x-bmp":
			case "image/x-bitmap":
			case "image/x-xbitmap":
			case "image/x-win-bitmap":
			case "image/x-windows-bmp":
			case "image/x-ms-bmp":
			case "application/bmp":
			case "application/x-bmp":
			case "application/x-win-bitmap":
			//Formats for type ".png"
			case "image/png":
			case "application/png":
			case "application/x-png":
			//Formats for type ".jpg/.jpeg"
			case "image/jpeg":
			case "image/jpg":
			case "image/jp_":
			case "application/jpg":
			case "application/x-jpg":
			case "image/pjpeg":
			case "image/pipeg":
			case "image/vnd.swiftview-jpeg":
			case "image/x-xbitmap":
			//Formats for type ".gif"
			case "image/gif":
			case "image/x-xbitmap":
			case "image/gi_":
				return true;
			default:
				return false;
		}
	}

}

?>