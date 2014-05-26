<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
 * User Interface class for RoomSharing repository object.
 *
 * User interface classes process GET and POST parameter and call
 * application classes to fulfil certain tasks.
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author tmatern
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
 * @ilCtrl_isCalledBy ilObjRoomSharingGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 *
*/
class ilObjRoomSharingGUI extends ilObjectPluginGUI
{
	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
	}

	/**
	 * Get type.
	 */
	final function getType()
	{
		return "xrsp";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd)
	{
		echo "Command:".$cmd;
		
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
				//case "...":
				$this->checkPermission("write");
				$this->$cmd();
				break;
					
			case "showContent":			// list all commands that need read permission here
				$this->checkPermission("read");
				$this->$cmd();
				break;

/*
 * Kann so nicht übernommen werden, da hier $cmd anstelle von $next_class verwendet wird
 * 
			 // Overview
			case 'ilroomsharingoverviewgui':
				$this->tabs_gui->setTabActive('overview');
				include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingOverviewGUI.php");
				$object_gui = & new ilRoomSharingOverviewGUI($this);
				$ret = & $this->ctrl->forwardCommand($object_gui);
				break;

				// Info.
			case 'ilinfoscreengui':
				$this->infoScreen();
				break;

				// Roomplans.
			case 'ilroomsharingroomplansgui':
				$this->tabs_gui->setTabActive('room_plans');
				include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoomPlansGUI.php");
				$object_gui = & new ilRoomSharingRoomPlansGUI($this);
				$ret = & $this->ctrl->forwardCommand($object_gui);
				break;

				// Floorplan.
			case 'ilroomsharingfloorplansgui':
				$this->tabs_gui->setTabActive('floor_plans');
				include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlansGUI.php");
				$schedule_gui = & new ilRoomSharingFloorPlansGUI($this);
				$ret = & $this->ctrl->forwardCommand($schedule_gui);
				break;

				// Permissions.
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = & new ilPermissionGUI($this);
				$ret = & $this->ctrl->forwardCommand($perm_gui);
				break;

				//Userprofile GUI.
			case 'ilpublicuserprofilegui':
				$ilTabs->clearTargets();
				include_once("Services/User/classes/class.ilPublicUserProfileGUI.php");
				$profile = new ilPublicUserProfileGUI((int) $_GET["user_id"]);
				$profile->setBackUrl($this->ctrl->getLinkTarget($this, 'log'));
				$ret = $this->ctrl->forwardCommand($profile);
				$tpl->setContent($ret);
				break;

				// Standard dispatcher GUI.
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

				// Copy GUI. Not supported yet.
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("roomsharing");
				$this->ctrl->forwardCommand($cp);
				break;
	*/

		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	 * Get standard command
	 */
	function getStandardCmd()
	{
		return "showContent";
	}


	//
	// Edit properties form
	//

	/**
	 * Edit Properties. This commands uses the form class to display an input form.
	 */
	function editProperties()
	{

	}
	//
	// DISPLAY TABS
	//
	/**
	* Set tabs for other GUIs in the main GUI.
	*/
	function setTabs()
	{
		global  $ilTabs, $ilCtrl,$ilAccess, $ilHelp;

		$ilHelp->setScreenIdComponent("room");
		 
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}
		// standard info screen tab
		$this->addInfoTab();

		// Overview.
		$this->tabs_gui->addTab("overview", // Sub GUI ID.
				$this->txt("overview"), // Translation key.
				$this->ctrl->getLinkTargetByClass('ilroomsharingoverviewgui', "showBookings")); // Linked class with execution command.
		// Info.
		$this->tabs_gui->addTab("info", $this->lng->txt("info_short"), $this->ctrl->getLinkTargetByClass(array('ilobjroomsharingpoolgui', 'ilinfoscreengui'), "showSummary"));

		// Roomplans.
		$this->tabs_gui->addTab("room_plans", $this->lng->txt("room_plans"), $this->ctrl->getLinkTargetByClass('ilroomsharingroomplansgui', "showBookableRooms"));

		// Floorplans.
		$this->tabs_gui->addTab("floor_plans", $this->lng->txt("room_floor_plans"), $this->ctrl->getLinkTargetByClass("ilroomsharingfloorplansgui", "render"));

		// Show permissions and settings tabs if the user has write permissions.
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			// Settings.
			$this->tabs_gui->addTab("settings", $this->lng->txt("settings"), $this->ctrl->getLinkTarget($this, "edit"));

			// Permission.
			$this->addPermissionTab();
		}
	}


	//
	// Show content
	//

	/**
	 * Show content
	 */
	function showContent()
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab("content");
		$tpl->setContent("Hello World.");
	}


	/**
	 * Returns roomsharing pool id.
	 */
	function getPoolId() {
		return $this->pool_id;
	}
	/**
	 * Sets roomsharing pool id.
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}



}
?>
