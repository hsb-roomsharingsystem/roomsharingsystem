<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");
include_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* Access/Condition checking for RoomSharingPool object.
*
* @author tmatern
* 
* @version $Id$
*/
class ilObjRoomSharingAccess extends ilObjectPluginAccess
{
	
	/**
	 * Get commands.
	 *
	 * this method returns an array of all possible commands/permission combinations.
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
	 * Check whether goto script will succeed.
	 */
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);
		
		if ($t_arr[0] != "xrs" || ((int) $t_arr[1]) <= 0)
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
		global $ilUser, $ilAccess;
		
			// Check whether the user has write permissions (owner has always write permissions).
		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId ();
		}
		if ( $ilAccess->checkAccessOfUser ( $a_user_id, 'write', '', $a_ref_id ))
		{
			return true;
		}
			
			// Check whether user should see the pool.
		if ($a_permission == "read")
		{
			$plugin = ilPlugin::getPluginObject ( 'Services', 'Repository', 'robj', 'RoomSharing' );
			$plugin->includeClass ( 'class.ilObjRoomSharing.php' );
			$pool = new ilObjRoomSharing ( $a_ref_id );
			$pool->doRead ();
			if (! $pool->isOnline ())
			{
				return false;
			}
		}

        return true;
	}
	
}

?>
