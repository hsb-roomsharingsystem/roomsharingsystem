<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
include_once('Services/Form/classes/class.ilPropertyFormGUI.php');

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
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilRoomSharingOverviewGUI, ilRoomsharingRoomplansGUI, ilRoomsharingFloorplansGUI, ilPublicUserProfileGUI
 * @ilCtrl_isCalledBy ilObjRoomSharingGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 *
*/
class ilObjRoomSharingGUI extends ilObjectPluginGUI
{
	//Settings form gui
	protected $settingsForm;
	//Pool id
	protected $pool_id;
	
	/**
	 * Initialisation.
	 */
	protected function afterConstructor()
	{
		// Set pool id.
		$this->pool_id = $this->object->getId();
	}

	/**
	 * Get type.
	 */
	final function getType()
	{
		return "xrs";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd)
	{
		global $ilTabs, $ilCtrl;
		$next_class = $ilCtrl->getNextClass($this);
		echo "Command: ".$cmd;
		echo "<br>Next_Class: ".$next_class;
		global $tpl, $ilTabs, $ilNavigationHistory, $cmd;
		
		$cmd = $ilCtrl->getCmd();
		
	
		// On initial call of the plugin.
		if (!$next_class && ($cmd == 'render'))
		{
			$ilTabs->setTabActive('overview');
			include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingOverviewGUI.php");
			$object_gui = & new ilRoomSharingOverviewGUI($this);
			$ilCtrl->forwardCommand($object_gui);
			return true;
		}
		
		if ($cmd == 'edit' || $cmd == 'editSettings' || $cmd == 'updateSettings')
		{
			$ilTabs->setTabActive('settings');
			// In case the edit button was clicked in the repository.
			if ($cmd == 'edit')
			{
				$cmd = 'editSettings';
			}
			$this->$cmd();
			return true;
		}
		
		// Extend list of last visited objects by this pool.
		$ilNavigationHistory->addItem($this->ref_id, "./goto.php?target=xrs_" . $this->ref_id, "xrs");
		
		
		// Main switch for cmdClass.
		switch ($next_class)
		{
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
// 				include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlansGUI.php");
// 				$schedule_gui = & new ilRoomSharingFloorPlansGUI($this);
// 				$ret = & $this->ctrl->forwardCommand($schedule_gui);
				$this->tpl->setContent("Die Ansicht der Pläne ist noch nicht an die neue Plugin-Ordnerstruktur angepasst.");
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
				// Standard cmd handling if cmd is not recognized.
			default:
				
				$cmd = $ilCtrl->getCmd();
				echo "defaultcmd:".$cmd;
				$this->$cmd();
				break;
		}
		
		// Action menue (top right corner of the module).
// 		$this->addHeaderAction();
		return true;
		
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

	/**
	* Set tabs for other GUIs in the main GUI.
	*/
	function setTabs()
	{
		global  $ilTabs, $ilCtrl, $ilAccess;
		
		// Overview.
		$ilTabs->addTab("overview", $this->txt("overview"), $ilCtrl->getLinkTargetByClass('ilroomsharingoverviewgui', "showBookings"));

		// Standard info screen tab.
		$this->addInfoTab();
		
		// Roomplans.
		$this->tabs_gui->addTab("room_plans", $this->txt("room_plans"), $this->ctrl->getLinkTargetByClass('ilroomsharingroomplansgui', "showBookableRooms"));

		// Floorplans.
		$this->tabs_gui->addTab("floor_plans", $this->txt("room_floor_plans"), $this->ctrl->getLinkTargetByClass("ilroomsharingfloorplansgui", "render"));


		
		// Show permissions and settings tabs if the user has write permissions.
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			// Settings.
			$this->tabs_gui->addTab('settings', $this->txt('settings'), $this->ctrl->getLinkTarget($this, 'editSettings'));
			
			// Permission.
			$this->addPermissionTab();
		}
	}


	/**
	 * Show content
	 */
	function showContent()
	{
		global $tpl, $ilTabs;
		
		$tpl->setContent("Hello World.");
	}
	
	/**
	 * Edit settings.
	 * This command uses the form class to display an input form.
	 */
	protected function editSettings()
	{
		$this->tabs_gui->activateTab ( 'settings' );
		$this->initSettingsForm ();
		$this->getSettingsValues ();
		$html = $this->settingsForm->getHTML ();
		$this->tpl->setContent ( $html );
	}
	
	/**
	 * Update settings.
	 * This command uses the form class to display an input form.
	 */
	protected function updateSettings()
	{
		$this->tabs_gui->activateTab ( 'settings' );
		$this->initSettingsForm ();
		
		if ($this->settingsForm->checkInput ())
		{
			$this->object->setTitle ( $this->settingsForm->getInput ( 'title' ) );
			$this->object->setDescription ( $this->settingsForm->getInput ( 'desc' ) );
			$this->object->setOnline ( $this->settingsForm->getInput ( 'online' ) );
			$this->object->update ();
			ilUtil::sendSuccess ( $this->lng->txt ( 'msg_obj_modified' ), true );
			$this->ctrl->redirect ( $this, 'editSettings' );
		}
		
		$this->settingsForm->setValuesByPost ();
		$this->tpl->setContent ( $this->settingsForm->getHtml () );
	}
	
	/**
	 * Init settings form.
	 * This command uses the form class to display an input form.
	 */
	protected function initSettingsForm()
	{
		$this->settingsForm = new ilPropertyFormGUI ();
		
		// title
		$field = new ilTextInputGUI ( $this->lng->txt ( 'title' ), 'title' );
		$field->setRequired ( true );
		$this->settingsForm->addItem ( $field );
		
		// description
		$field = new ilTextAreaInputGUI ( $this->lng->txt ( 'description' ), 'desc' );
		$this->settingsForm->addItem ( $field );
		
		// online
		$field = new ilCheckboxInputGUI ( $this->lng->txt ( 'online' ), 'online' );
		$this->settingsForm->addItem ( $field );
		
		$this->settingsForm->addCommandButton ( 'updateSettings', $this->lng->txt ( 'save' ) );
		
		$this->settingsForm->setTitle ( $this->lng->txt ( 'edit_settings' ) );
		$this->settingsForm->setFormAction ( $this->ctrl->getFormAction ( $this ) );
	}
	
	/**
	 * Get values to edit settings form.
	 */
	protected function getSettingsValues()
	{
		$values ['title'] = $this->object->getTitle ();
		$values ['desc'] = $this->object->getDescription ();
		$values ['online'] = $this->object->isOnline ();
		
		$this->settingsForm->setValuesByArray ( $values );
	}
	
	/**
	 * Forbids to import and to close an roomsharing pool.
	 * @see ilObjectPluginGUI::initCreateForm()
	 */
	public function initCreationForms($a_new_type) {
		$forms = parent::initCreationForms($a_new_type);
		unset($forms[self::CFORM_CLONE]);
		unset($forms[self::CFORM_IMPORT]);
		return $forms;
	
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
