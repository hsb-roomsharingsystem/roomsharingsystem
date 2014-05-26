<?php

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for RoomSharing repository object.
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*/
class ilObjRoomSharing extends ilObjectPlugin
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
	}
	

	/**
	* Get type.
	*/
	final function initType()
	{
		$this->setType("xrsp");
	}
	
	/**
	* Create object
	*/
	function doCreate()
	{
		
	}
	
	/**
	* Read data from db
	*/
	function doRead()
	{
		
	}
	
	/**
	* Update data;
	*	called when Object is updated
	*/
	function doUpdate()
	{
		
	}
	
	/**
	* Delete data from db
	*/
	function doDelete()
	{
		
	}
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{

	}
}
?>
