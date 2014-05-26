<?php

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
 * Description of class
 *
 * @author T. Wolscht
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
