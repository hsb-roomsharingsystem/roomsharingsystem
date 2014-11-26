<?php

include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");

/**
 * Class ilRoomSharingFloorPlans
 *
 * This class represents the backend of the RoomSharing floor plans.
 *
 * @author Thomas Wolscht <t.wolscht@googlemail.com>
 * @author Christopher Marks <Deamp_dev@yahoo.de>
 */
class ilRoomSharingFloorPlans
{
	protected $pool_id;
	private $ilRoomsharingDatabase;

	/**
	 * Constructor of ilRoomSharingFloorPlans.
	 *
	 * @param type $a_pool_id the pool id of the plugin instance
	 */
	public function __construct($a_pool_id, $ilRoomsharingDatabase)
	{
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = $ilRoomsharingDatabase;
	}

	/**
	 * Get an array that contains all floor plans.
	 *
	 * @return type array containing all of the floor plans
	 */
	public function getAllFloorPlans()
	{
		$floorplans = $this->ilRoomsharingDatabase->getAllFloorplans();
		return $floorplans;
	}

	/**
	 * Returns an array that contains all information to a floor plan.
	 *
	 * @param type $a_file_id the id of the floor plan
	 * @return type the result
	 */
	public function getFloorPlanInfo($a_file_id)
	{
		$floorplan = $this->ilRoomsharingDatabase->getFloorplan($a_file_id);
		return $floorplan;
	}

	/**
	 * Inserts the file id of the uploaded image file to the database.
	 *
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
	 *
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
			if ($res = $this->ilRoomsharingDatabase->deleteFloorplan($a_file_id))
			{
				$this->ilRoomsharingDatabase->deleteFloorplanRoomAssociation($a_file_id);
			}
		}
		return $res;
	}

	public function getRoomsWithFloorplan($fplan_id)
	{
		return $this->ilRoomsharingDatabase->getRoomsWithFloorplan($fplan_id);
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
	public function updateFloorPlanInfos($a_file_id, $a_title, $a_desc)
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
	public function updateFloorPlanInfosAndFile($a_file_id, $a_title, $a_desc, $a_newfile = null)
	{
		$mediaObj = $this->createMediaObject($a_title, $a_desc, $a_file_id);
		$fileinfo = $this->configureFile($mediaObj, $a_newfile);

		if ($this->checkImageType($fileinfo["format"]) == false)
		{
			return false;
		}

		$this->updateMediaObject($mediaObj, $fileinfo);

		return true;
	}

	/**
	 * Creates a new floor plan by using the ILIAS MediaObject Service
	 * and leaves a database entry.
	 *
	 * @param type $a_title the title of the floor plan
	 * @param type $a_desc the floor plan description
	 * @param type $a_newfile an array containing the input values of the form
	 * @return type success or failure
	 */
	public function addFloorPlan($a_title, $a_desc, $a_newfile)
	{
		$mediaObj = $this->createMediaObject($a_title, $a_desc, null);
		$fileinfo = $this->configureFile($mediaObj, $a_newfile);

		if ($this->checkImageType($fileinfo["format"]) == false)
		{
			return false;
		}

		$this->updateMediaObject($mediaObj, $fileinfo);

		return $this->fileToDatabase($mediaObj->getId());
	}

	/**
	 * Creates the media object for the updateFloorPlanInfosWithFile and addFloorPlan function
	 *
	 * @param type $a_title
	 * @param type $a_desc
	 * @param type $a_file_id
	 * @return \ilObjMediaObject
	 */
	private function createMediaObject($a_title, $a_desc, $a_file_id = null)
	{
		if (is_null($a_file_id))
		{
			$mediaObj = new ilObjMediaObject();
			$mediaObj->create();
		}
		else
		{
			$mediaObj = new ilObjMediaObject($a_file_id);
		}

		$mediaObj->setTitle($a_title);
		$mediaObj->setDescription($a_desc);
		$mediaObj->removeAllMediaItems();

		$media_item = new ilMediaItem();
		$media_item->setPurpose("Standard");
		$mediaObj->addMediaItem($media_item);

		return $mediaObj;
	}

	/**
	 * Configures the file for the updateFloorPlanInfosWithFile and addFloorPlan function
	 *
	 * @param type $mediaObj
	 * @param type $a_newfile
	 * @return type
	 */
	private function configureFile($mediaObj, $a_newfile = null)
	{
		$mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());

		if (!is_dir($mob_dir))
		{
			$mediaObj->createDirectory();
		}
		$file_name = ilUtil::getASCIIFilename($a_newfile["name"]);
		$file_name_mod = str_replace(" ", "_", $file_name);
		$file = $mob_dir . "/" . $file_name_mod; // construct file path
		ilUtil::moveUploadedFile($a_newfile["tmp_name"], $file_name_mod, $file);
		ilUtil::renameExecutables($mob_dir);
		$format = ilObjMediaObject::getMimeType($file);

		return array(
			"format" => $format,
			"filename" => $file_name_mod
		);
	}

	/**
	 * Checks if the ImageType is valid
	 *
	 * @param type $a_mimeType
	 * @return boolean
	 */
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

	/**
	 * Updates the media object with the file informations for the updateFloorPlanInfosWithFile
	 * and addFloorPlan function
	 *
	 * @param type $mediaObj
	 * @param type $fileinfo
	 */
	private function updateMediaObject($mediaObj, $fileinfo)
	{
		$media_item = $mediaObj->getMediaItem("Standard");
		$media_item->setFormat($fileinfo["format"]);
		$media_item->setLocation($fileinfo["filename"]);
		$media_item->setLocationType("LocalFile");
		$mediaObj->update();
	}

}

?>