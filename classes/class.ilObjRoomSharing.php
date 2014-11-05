<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");

/**
 * Mainclass for roomsharing system module. Pool id is the object id
 * and will be stored in the db table "rep_robj_xrs_pools".
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 * @author mdazjuk
 * @author troehrig
 * @version $Id$
 * @property ilDB $ilDB
 */
class ilObjRoomSharing extends ilObjectPlugin
{
	protected $ilDB;
	protected $pool_id;
	protected $online;
	protected $max_book_time;
	/* File id of the rooms agreement */
	protected $rooms_agreement;

	/**
	 * Constructor of ilObjRoomSharing
	 *
	 * @access	public
	 * @param integer $a_ref_id
	 */
	function __construct($a_ref_id = 0)
	{
		global $ilDB; // needed for db-creation
		$this->ilDB = $ilDB;
		parent::__construct($a_ref_id);
		$this->pool_id = $this->getId();
		$this->doRead();
	}

	/**
	 * Get type.
	 */
	final function initType()
	{
		$this->setType("xrs");
	}

	/**
	 * Parse properties for sql statements
	 *
	 * 	@return array $fields
	 */
	protected function getDBFields()
	{
		$fields = array(
			"pool_online" => array("integer", $this->isOnline()),
			"max_book_time" => array("timestamp", $this->getMaxBookTime()),
			"rooms_agreement" => array("integer", $this->getRoomsAgreementFileId()));
		return $fields;
	}

	/**
	 * Create object
	 *
	 * @return integer with the pool_id
	 */
	protected function doCreate()
	{
		parent::doCreate();
		$this->pool_id = $this->getId();
		$fields = $this->getDBFields();
		$fields["id"] = array("integer", $this->pool_id);
		$fields["max_book_time"] = array("timestamp", "1970-01-01 03:00:00");
		$fields["rooms_agreement"] = array("integer", "0");
		$this->ilDB->insert("rep_robj_xrs_pools", $fields);
		return parent::getId();
	}

	/**
	 * Read data of an RoomSharing-Object from database.
	 *
	 */
	protected function doRead()
	{
		$objId = $this->getId();

		if ($objId)
		{
			$this->pool_id = $objId;
			$set = $this->ilDB->query('SELECT * FROM rep_robj_xrs_pools' .
				' WHERE id = ' . $this->ilDB->quote($this->getId(), 'integer'));
			$row = $this->ilDB->fetchAssoc($set);
			$this->setOnline($row['pool_online']);
			$this->setMaxBookTime($row['max_book_time']);
			$this->setRoomsAgreementFileId($row['rooms_agreement']);
		}
	}

	/**
	 * Update data;
	 *
	 * @return bool whether the Update was successful
	 */
	protected function doUpdate()
	{
		if ($this->getId())
		{
			$this->ilDB->update("rep_robj_xrs_pools", $this->getDBFields(),
				array("id" => array("integer", $this->getId())));
		}
		return true;
	}

	/**
	 * Delete data from the database.
	 *
	 * @return bool whether the Delete was successful
	 */
	protected function doDelete()
	{
		//		$id = $this->getId();
		// always call parent delete function first
		// example - delete old db data
		return true;
	}

	/**
	 * Clones the roomsharing object.
	 *
	 * @param ilObjRoomSharing $new_obj The clone.
	 * @param int $a_target_id
	 * @param int $a_copy_id
	 */
	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		$new_obj->setOnline($this->isOnline());
		$new_obj->setMaxBookTime($this->getMaxBookTime());
	}

	/**
	 * Check object status
	 *
	 * @param int $a_obj_id
	 * @return boolean
	 */
	public static function _lookupOnline($a_obj_id)
	{
		$set = $this->ilDB->query("SELECT pool_online" .
			" FROM rep_robj_xrs_pools" .
			" WHERE id = " . $this->ilDB->quote($a_obj_id, "integer"));
		$row = $this->ilDB->fetchAssoc($set);
		return (bool) $row["pool_online"];
	}

	/**
	 * Get online property.
	 *
	 * @return bool
	 */
	public function isOnline()
	{
		return (bool) $this->online;
	}

	/**
	 * Toggle online property.
	 *
	 * @param bool $a_value
	 */
	public function setOnline($a_value = true)
	{
		$this->online = (bool) $a_value;
	}

	/**
	 * Get max booking time.
	 *
	 * @return time 'H:i'
	 */
	public function getMaxBookTime()
	{
		return $this->max_book_time;
	}

	/**
	 * Set max booking time.
	 *
	 * @param datetime $a_max_book_time
	 */
	public function setMaxBookTime($a_max_book_time)
	{
		$this->max_book_time = $a_max_book_time;
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return int pool id
	 */
	public function getPoolId()
	{
		return 1;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer $a_pool_id current pool id.
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

	/**
	 * Returns roomsharing rooms agreement.
	 *
	 * @return int file id
	 */
	public function getRoomsAgreementFileId()
	{
		return $this->rooms_agreement;
	}

	/**
	 * Sets roomsharing rooms agreement.
	 *
	 * @param integer $a_file_id current file id
	 */
	public function setRoomsAgreementFileId($a_file_id)
	{
		$this->rooms_agreement = $a_file_id;
	}

	/**
	 * Uploads a new rooms agreement by using the ILIAS MediaObject Service.
	 * If the old file id is given, the old file will be deleted.
	 *
	 * @param array $a_newfile an array containing the input values of the form
	 * @param string $a_oldFileId to delete trash
	 * @return string uploaded file id
	 */
	public function uploadRoomsAgreement($a_newfile, $a_oldFileId = "0")
	{
		if (!empty($a_oldFileId) && $a_oldFileId != "0")
		{
			$agreementFile = new ilObjMediaObject($a_oldFileId);
			$agreementFile->delete();
		}
		$mediaObj = new ilObjMediaObject();
		$mediaObj->setTitle("RoomSharingRoomsAgreement");
		$mediaObj->setDescription("RoomSharingRoomsAgreement");
		$mediaObj->create();
		$mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());
		if (!is_dir($mob_dir))
		{
			$mediaObj->createDirectory();
		}
		$file_name = ilUtil::getASCIIFilename($a_newfile["name"]);
		$file_name_mod = str_replace(" ", "_", $file_name);
		$file = $mob_dir . "/" . $file_name_mod;
		ilUtil::moveUploadedFile($a_newfile["tmp_name"], $file_name_mod, $file);
		ilUtil::renameExecutables($mob_dir);
		$format = ilObjMediaObject::getMimeType($file);

		$media_item = new ilMediaItem();
		$mediaObj->addMediaItem($media_item);
		$media_item->setPurpose("Agreement");
		$media_item->setFormat($format);
		$media_item->setLocation($file_name_mod);
		$media_item->setLocationType("LocalFile");
		$mediaObj->update();

		return $mediaObj->getId();
	}

}
