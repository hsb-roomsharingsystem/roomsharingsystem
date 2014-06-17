<?php

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
 * This class is used, if the manually file-upload of a floorplan is used.
 * At the moment the MediaFile Service of ILIAS is used, so that this class
 * is actually not used.
 * 
 * This class extends the FSStorage of ILIAS, to store uploaded floorplans
 * to a path named 'floorplan'
 *
 * @author T. Wolscht <t.wolscht@googlemail.com>
 */
class ilFSStorageRoomPlan extends ilFileSystemStorage {
	public function __construct($a_container_id = 0)
	{
		parent::__construct(self::STORAGE_WEB, true, $a_container_id);
	}
	
	protected function getPathPostfix()
	{
	 	return 'floorplans';
	}
	
	protected function getPathPrefix()
	{
	 	return 'ilRoomSharing';
	}
}
