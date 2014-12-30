<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
require_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingPermissionUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivilegesConstants.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingFloorplanException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");

use ilRoomSharingPrivilegesConstants as PRIVC;

/**
 * Class ilRoomSharingFloorPlans
 *
 * This class represents the backend of the RoomSharing floor plans.
 *
 * @author Thomas Wolscht <t.wolscht@googlemail.com>
 * @author Christopher Marks <Deamp_dev@yahoo.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 * @property ilRoomSharingPermissionUtils $permission
 * @property ilRoomsharingDatabase $ilRoomsharingDatabase
 */
class ilRoomSharingFloorPlans
{
	private $pool_id;
	private $ilRoomsharingDatabase;
	private $permission;
	private $ctrl;
	private $lng;

	/**
	 * Constructor of ilRoomSharingFloorPlans.
	 *
	 * @param type $a_pool_id the pool id of the plugin instance
	 * @param type $a_ilRoomsharingDatabase the Database
	 */
	public function __construct($a_pool_id, $a_ilRoomsharingDatabase)
	{
		global $ilCtrl, $rssPermission, $lng;
		$this->ctrl = $ilCtrl;
		$this->permission = $rssPermission;
		$this->lng = $lng;
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = $a_ilRoomsharingDatabase;
	}

	/**
	 * Gets an array that contains all floor plans.
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
		if (!$this->permission->checkPrivilege(PRIVC::DELETE_FLOORPLANS))
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission_for_action"), true);
			$this->ctrl->redirectByClass('ilinfoscreengui', 'showSummary', 'showSummary');
			return FALSE;
		}
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

	/**
	 * Gets the Rooms with the specific floorplan
	 *
	 * @param type $a_floorplan_id
	 * @return type
	 */
	public function getRoomsWithFloorplan($a_floorplan_id)
	{
		return $this->ilRoomsharingDatabase->getRoomsWithFloorplan($a_floorplan_id);
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
		if (!$this->permission->checkPrivilege(PRIVC::EDIT_FLOORPLANS))
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission_for_action"), true);
			$this->ctrl->redirectByClass('ilinfoscreengui', 'showSummary', 'showSummary');
			return FALSE;
		}
		if ($this->isTitleAlreadyTaken($a_title))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floorplan_title_is_already_taken");
		}
		$media_obj = new ilObjMediaObject($a_file_id);
		$media_obj->setTitle($a_title);
		$media_item = $media_obj->getMediaItem("Standard");
		$media_item->setCaption($a_desc);
		$media_obj->update();
	}

	/**
	 * This function updates the information of a floor plan, which means
	 * that a new title and a new description will be added. The old floor plan
	 * will be removed in order to be replaced by the newly provided one.
	 *
	 * @param integer $a_file_id the floor plan id
	 * @param string $a_title the new title
	 * @param string $a_desc the new description
	 * @param array $a_newfile the new image
	 */
	public function updateFloorPlanInfosAndFile($a_file_id, $a_title, $a_desc, $a_newfile = null)
	{
		if (!$this->permission->checkPrivilege(PRIVC::EDIT_FLOORPLANS))
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission_for_action"), true);
			$this->ctrl->redirectByClass('ilinfoscreengui', 'showSummary', 'showSummary');
			return FALSE;
		}
		if ($this->isTitleAlreadyTaken($a_title))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floorplan_title_is_already_taken");
		}
		$mediaObj = $this->createMediaObject($a_title, $a_desc, $a_file_id);
		$fileinfo = $this->configureFile($mediaObj, $a_newfile);

		if (!$this->checkImageType($fileinfo["format"]))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floor_plans_upload_error");
		}

		if ($a_newfile != null && !ilRoomSharingNumericUtils::isPositiveNumber($a_newfile['size']))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floor_plans_upload_error");
		}

		$this->updateMediaObject($mediaObj, $fileinfo);
	}

	/**
	 * Creates a new floor plan by using the ILIAS MediaObject Service
	 * and leaves a database entry.
	 *
	 * @param string $a_title the title of the floor plan
	 * @param string $a_desc the floor plan description
	 * @param array $a_newfile an array containing the input values of the form
	 * @return boolean success or failure
	 */
	public function addFloorPlan($a_title, $a_desc, $a_newfile)
	{
		if (!$this->permission->checkPrivilege(PRIVC::ADD_FLOORPLANS))
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission_for_action"), true);
			$this->ctrl->redirectByClass('ilinfoscreengui', 'showSummary', 'showSummary');
			return FALSE;
		}

		if ($this->isTitleAlreadyTaken($a_title))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floorplan_title_is_already_taken");
		}

		$mediaObj = $this->createMediaObject($a_title, $a_desc, null);
		$fileinfo = $this->configureFile($mediaObj, $a_newfile);

		if (!$this->checkImageType($fileinfo["format"]))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floor_plans_upload_error");
		}

		if (!ilRoomSharingNumericUtils::isPositiveNumber($a_newfile['size']))
		{
			throw new ilRoomSharingFloorplanException("rep_robj_xrs_floor_plans_upload_error");
		}

		$this->updateMediaObject($mediaObj, $fileinfo);
		$this->fileToDatabase($mediaObj->getId());
	}

	/**
	 * Returns true if the given title is already taken.
	 *
	 * @param string $a_title
	 * @return boolean
	 */
	private function isTitleAlreadyTaken($a_title)
	{
		$rVal = false;
		$allFloorplansTitles = $this->getAllFloorplansTitles();
		foreach ($allFloorplansTitles as $floorplansTitle)
		{
			if ($floorplansTitle == $a_title)
			{
				$rVal = true;
				break;
			}
		}
		return $rVal;
	}

	/**
	 * Returns all existing floorplans titles.
	 *
	 * @return array with titles
	 */
	private function getAllFloorplansTitles()
	{
		$allFloorplanIds = $this->ilRoomsharingDatabase->getAllFloorplanIds();
		$floorplansTitles = array();
		foreach ($allFloorplanIds as $floorplanId)
		{
			if (ilRoomSharingNumericUtils::isPositiveNumber($floorplanId))
			{
				$mobj = new ilObjMediaObject($floorplanId);
				$floorplansTitles[] = $mobj->getTitle();
			}
		}
		return $floorplansTitles;
	}

	/**
	 * Creates the media object for the updateFloorPlanInfosWithFile and addFloorPlan function.
	 *
	 * @param string $a_title
	 * @param string $a_desc
	 * @param string $a_file_id
	 * @return ilObjMediaObject
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
		$mediaObj->removeAllMediaItems();

		$media_item = new ilMediaItem();
		$media_item->setPurpose("Standard");
		$media_item->setCaption($a_desc);
		$mediaObj->addMediaItem($media_item);

		return $mediaObj;
	}

	/**
	 * Configures the file for the updateFloorPlanInfosWithFile and addFloorPlan function.
	 *
	 * @param ilObjMediaObject $a_mediaObj
	 * @param array $a_newfile
	 * @return array with format and filename
	 */
	private function configureFile($a_mediaObj, $a_newfile = null)
	{
		$mob_dir = ilObjMediaObject::_getDirectory($a_mediaObj->getId());

		if (!is_dir($mob_dir))
		{
			$a_mediaObj->createDirectory();
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
	 * Checks if the ImageType is valid.
	 *
	 * @param string $a_mimeType
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
	 * and addFloorPlan function.
	 *
	 * @param ilObjMediaObject $a_mediaObj
	 * @param array $a_fileinfo with format and filename
	 */
	private function updateMediaObject($a_mediaObj, $a_fileinfo)
	{
		$media_item = $a_mediaObj->getMediaItem("Standard");

		$media_item->setFormat($a_fileinfo["format"]);
		$media_item->setLocation($a_fileinfo["filename"]);
		$media_item->setLocationType("LocalFile");
		$a_mediaObj->update();
	}

	/**
	 * Set the poolID of bookings
	 *
	 * @param integer $pool_id
	 *        	poolID
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}

	/**
	 * Get the PoolID of bookings
	 *
	 * @return integer PoolID
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

}

?>