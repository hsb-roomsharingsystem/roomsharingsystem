<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
* Access/Condition checking for Example object
*
* Please do not create instances of large application classes (like ilObjExample)
* Write small methods within this class to determin the status.
*
* @author tmatern
* 
* @version $Id$
*/
class ilObjRoomSharingAccess extends ilObjectPluginAccess
{
	
	/**
	 * get commands
	 *
	 * this method returns an array of all possible commands/permission combinations
	 *
	 * example:
	 * $commands = array
	 * 	(
	 * 		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 * 		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 * 	);
	 */
	function _getCommands()
	{
		$commands = array();
		$commands[] = array("permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true);
		$commands[] = array("permission" => "write", "cmd" => "render", "lang_var" => "edit_content");
		$commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "settings");
	
		return $commands;
	}
	
	/**
	 * check whether goto script will succeed
	 */
	function _checkGoto($a_target)
	{
		global $ilAccess;
	
		$t_arr = explode("_", $a_target);
	
		if ($t_arr[0] != "room" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}
	
		if ($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
	
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $rbacsystem;

        if ($a_user_id == "")
        {
            $a_user_id = $ilUser->getId();
        }

        // add no access info item and return false if access is not granted
        // $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $a_text, $a_data = "");
        // for all RBAC checks use checkAccessOfUser instead the normal checkAccess-method:
        // $rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id)

        if ($a_permission == "visible" && !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))
        {
            include_once "Modules/RoomSharing/classes/class.ilObjRoomSharingPool.php";
            $pool = new ilObjRoomSharingPool($a_ref_id);
            if (!$pool->isOnline())
            {
                return false;
            }
        }

        return true;
	}
	
}

?>
