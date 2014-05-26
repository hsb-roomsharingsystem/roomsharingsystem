<?php

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
* ListGUI implementation for Example object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*
* @author 		Alex Killing <alex.killing@gmx.de>
*/
class ilObjRoomSharingListGUI extends ilObjectPluginListGUI
{
	
	/**
	* Init type
	*/
	function initType()
	{
		$this->setType("xrsp");
		
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
		return "ilObjRoomSharingGUI";
	}
	
	/**
	* Get commands
	*/
	function initCommands()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		
		$this->gui_class_name = "ilobjroomsharinggui";
		
		// general commands array
		include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilObjRoomSharingAccess.php');
		$this->commands = ilObjRoomSharingAccess::_getCommands();
		return $this->commands;
		
	}
}
?>
