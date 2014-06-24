<?php

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

	/**
	 * Constructor of ilRoomSharingFloorPlans.
	 * @param type $a_pool_id the pool id of the plugin instance
	 */
	public function __construct($a_pool_id = NULL)
	{
		$this->pool_id = $a_pool_id;
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

		$set = $ilDB->query('SELECT * FROM rep_robj_xrs_fplans WHERE pool_id = '
				. $ilDB->quote($this->pool_id, 'integer') . ' order by file_id DESC');
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
		$set = $ilDB->query('SELECT * FROM rep_robj_xrs_fplans WHERE file_id = '
				. $ilDB->quote($a_file_id, 'integer') . ' AND pool_id = '
				. $ilDB->quote($this->pool_id, 'integer'));
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
		global $ilDB;
		if ($a_file_id)
		{
			return $ilDB->manipulate('INSERT INTO rep_robj_xrs_fplans'
							. ' (file_id, pool_id)' . ' VALUES (' . $ilDB->quote($a_file_id, 'integer') . ','
							. $ilDB->quote($this->pool_id, 'integer') . ')');
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
		global $ilDB;
		$res = null;
		if ($a_file_id)
		{
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mediaObj = new ilObjMediaObject($a_file_id);
			$mediaObj->removeAllMediaItems();
			$mediaObj->delete();
			$res = $ilDB->manipulate('DELETE FROM rep_robj_xrs_fplans' .
					' WHERE file_id = ' . $ilDB->quote($a_file_id, 'integer'));
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
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
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
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
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

		$media_item->setFormat($format);
		$media_item->setLocation($file_name_mod);
		$media_item->setLocationType("LocalFile");
		$mediaObj->update();
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
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
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

		$media_item->setFormat($format);
		$media_item->setLocation($file_name_mod);
		$media_item->setLocationType("LocalFile");
		$mediaObj->update();

		$result = $this->fileToDatabase($mediaObj->getId());
		return $result;
	}

}

?>