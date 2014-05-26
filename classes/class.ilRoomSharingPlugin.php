<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* RoomSharing repository object plugin
*
* @author Tim Röhrig 
* @version $Id$
*
*/
class ilRoomSharingPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "RoomSharing";
	}
}
?>
