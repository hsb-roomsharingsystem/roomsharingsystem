<?php

require_once("Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once('Services/Form/classes/class.ilPropertyFormGUI.php');
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/" .
	"RoomSharing/classes/utils/class.ilRoomSharingTimeInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/" .
	"RoomSharing/classes/utils/class.ilRoomSharingCalendar.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingPermissionUtils.php");

/**
 * User Interface class for RoomSharing repository object.
 *
 * User interface classes process GET and POST parameter and call
 * application classes to fullfill certain tasks.
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 * @author Tim Röhrig <troehrig@stud.hs-bremen.de>
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilRoomSharingSearchGUI
 *
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilRoomSharingAppointmentsGUI, ilRoomSharingRoomsGUI, ilRoomSharingFloorplansGUI, ilPublicUserProfileGUI, ilRoomSharingBookGUI
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilRoomsharingRoomGUI, ilRoomSharingPrivilegesGUI
 *
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilCalendarDayGUI, ilCalendarAppointmentGUI
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarInboxGUI
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilConsultationHoursGUI, ilCalendarBlockGUI, ilColumnGUI
 *
 *
 * @ilCtrl_isCalledBy ilObjRoomSharingGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_IsCalledBy ilObjRoomSharingGUI: ilColumnGUI
 *
 * @property ilObjRoomSharing $object
 * @property ilPropertyFormGUI $settingsForm
 * @property ilLanguage $lng
 */
class ilObjRoomSharingGUI extends ilObjectPluginGUI
{
	var $object;
	var $lng;
	private $permission;
	protected $settingsForm;
	protected $pool_id;
	protected $pl_obj;
	protected $cal;
	protected $seed;

	/**
	 * Initialization.
	 */
	protected function afterConstructor()
	{
		if ($this->object != null)
		{
			//Cannot initialize the user-calendar and permission utils before the actual object is created because of missing poolID
			$this->initCalendar();
			$this->permission = new ilRoomSharingPermissionUtils($this->object->getPoolId());
		}
	}

	/**
	 * Get type.
	 * @return string type of this ilObjRoomSharingGUI
	 */
	final function getType()
	{
		return "xrs";
	}

	/**
	 * Get title.
	 *
	 * @return string title of RoomSharing-Object
	 */
	public function getTitle()
	{
		return $this->object->getTitle();
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 * @param string $cmd Given Command to Execute
	 * @return boolean true
	 */
	function performCommand($cmd)
	{
		global $ilTabs, $ilCtrl, $tpl, $ilNavigationHistory, $cmd;
		$next_class = $ilCtrl->getNextClass($this);
		$this->pl_obj = new ilRoomSharingPlugin();
		$this->pl_obj->includeClass("class.ilObjRoomSharing.php");
		// Set pool id
		$this->pool_id = $this->object->getPoolID();
		$cmd = $ilCtrl->getCmd();
		if ($cmd === 'edit' || $cmd === 'editSettings' || $cmd === 'updateSettings')
		{
			$ilTabs->setTabActive('settings');
			// In case the edit button was clicked in the repository
			if ($cmd === 'edit')
			{
				$cmd = 'editSettings';
			}
			$this->$cmd();
			return true;
		}
		/*
		 * The special handling of the commands showSearchQuick and
		 * showSearchResults is needed because otherwise the wrong $next_class
		 * would be called
		 */
		else if ($cmd === 'showSearchQuick' || $cmd === 'showSearchResults')
		{
			$next_class = ilroomsharingsearchgui;
		}
		// the special handling of the commands addRoom and editRoom
		else if ($cmd === 'addRoom' || $cmd === 'editRoom')
		{
			$next_class = ilroomsharingroomgui;
		}

		// Extend list of last visited objects by this pool.
		$ilNavigationHistory->addItem($this->ref_id, "./goto.php?target=xrs_" . $this->ref_id, "xrs");

		// Main switch for cmdClass.
		switch ($next_class)
		{
			// Appointments
			case 'ilroomsharingappointmentsgui':
				$this->tabs_gui->setTabActive('appointments');
				$this->pl_obj->includeClass("appointments/class.ilRoomSharingAppointmentsGUI.php");
				$object_gui = & new ilRoomSharingAppointmentsGUI($this);
				$ret = & $this->ctrl->forwardCommand($object_gui);
				break;
			// Info
			case 'ilinfoscreengui':
				$this->infoScreen();
				break;
			// Search
			case 'ilroomsharingsearchgui':
				$this->tabs_gui->setTabActive('search');
				$this->pl_obj->includeClass("search/class.ilRoomSharingSearchGUI.php");
				$object_gui = & new ilRoomSharingSearchGUI($this);
				$ret = & $this->ctrl->forwardCommand($object_gui);
				break;
			// Rooms, Called for a list of rooms
			case 'ilroomsharingroomsgui':
				$this->tabs_gui->setTabActive('rooms');
				$this->pl_obj->includeClass("rooms/class.ilRoomSharingRoomsGUI.php");
				$object_gui = & new ilRoomSharingRoomsGUI($this);
				$ret = & $this->ctrl->forwardCommand($object_gui);
				break;
			// Room, Called for display a single room
			case 'ilroomsharingroomgui':
				$this->tabs_gui->setTabActive('rooms');
				$room_id = (int) $_GET['room_id'];
				$this->pl_obj->includeClass("rooms/detail/class.ilRoomSharingRoomGUI.php");
				$object_gui = & new ilRoomSharingRoomGUI($this, $room_id);
				$ret = & $this->ctrl->forwardCommand($object_gui);
				break;
			// Book
			case 'ilroomsharingbookgui':
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget(
					$this->lng->txt("rep_robj_xrs_search_back"), $ilCtrl->getLinkTarget($this, "showSearchResults")
				);
				$this->pl_obj->includeClass("booking/class.ilRoomSharingBookGUI.php");
				$book_gui = & new ilRoomSharingBookGUI($this);
				$ret = & $this->ctrl->forwardCommand($book_gui);
				break;
			// Floorplans
			case 'ilroomsharingfloorplansgui':
				$this->tabs_gui->setTabActive('floor_plans');
				$this->pl_obj->includeClass("floorplans/class.ilRoomSharingFloorPlansGUI.php");
				$schedule_gui = & new ilRoomSharingFloorPlansGUI($this);
				$ret = & $this->ctrl->forwardCommand($schedule_gui);
				break;
			// Permissions
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = & new ilPermissionGUI($this);
				$ret = & $this->ctrl->forwardCommand($perm_gui);
				break;
			// Privileges
			case 'ilroomsharingprivilegesgui':
				$this->tabs_gui->setTabActive('privileges');
				$this->pl_obj->includeClass("privileges/class.ilRoomSharingPrivilegesGUI.php");
				$privileges_gui = & new ilRoomSharingPrivilegesGUI($this);
				$ret = & $this->ctrl->forwardCommand($privileges_gui);
				break;
			// Userprofile GUI
			case 'ilpublicuserprofilegui':
				$ilTabs->clearTargets();
				include_once("Services/User/classes/class.ilPublicUserProfileGUI.php");
				$profile = new ilPublicUserProfileGUI((int) $_GET["user_id"]);
				$profile->setBackUrl($this->ctrl->getLinkTarget($this, 'log'));
				$ret = $this->ctrl->forwardCommand($profile);
				$tpl->setContent($ret);
				break;
			// Standard dispatcher GUI
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
			// Various CalendarGUIs
			case "ilcalendardaygui":
				include_once("./Services/Calendar/classes/class.ilCalendarDayGUI.php");
				$day = new ilCalendarDayGUI(new ilDate($_GET["seed"], IL_CAL_DATE));
				$this->ctrl->forwardCommand($day);
				break;
			case "ilcalendarmonthgui":
				include_once("./Services/Calendar/classes/class.ilCalendarMonthGUI.php");
				$month = new ilCalendarMonthGUI(new ilDate($_GET["seed"], IL_CAL_DATE));
				$this->ctrl->forwardCommand($month);
				break;
			case "ilcalendarweekgui":
				include_once("./Services/Calendar/classes/class.ilCalendarWeekGUI.php");
				$week = new ilCalendarweekGUI(new ilDate($_GET["seed"], IL_CAL_DATE));
				$this->ctrl->forwardCommand($week);
				break;
			case "ilcalendarblockgui":
				$this->ctrl->forwardCommand($this->cal);
				break;
			// Standard cmd handling if cmd is none of the above. In that case, the next page is
			// appointments.
			default:
				$cmd = $ilCtrl->getCmd('render');
				$this->$cmd();
				break;
		}

		// Action menu (top right corner of the module)
		$this->addHeaderAction();

		include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
		if (ilCalendarSettings::_getInstance()->isEnabled())
		{

			//adds Minicalendar to the right if active
			$tpl->setRightContent($this->cal->getHTML());
			$tpl->addCss(ilUtil::getStyleSheetLocation('filesystem', 'delos.css', 'Services/Calendar'));
		}
		return true;
	}

	/**
	 * Default command that is executed if no "nextClass" can be determined.
	 * @param boolean true
	 */
	public function render()
	{
		global $ilTabs, $ilCtrl;
		$ilTabs->setTabActive('appointments');
		$this->pl_obj->includeClass("appointments/class.ilRoomSharingAppointmentsGUI.php");
		$object_gui = & new ilRoomSharingAppointmentsGUI($this);
		$ilCtrl->forwardCommand($object_gui);
		return true;
	}

	/**
	 * After object has been created, jump to this command.
	 * @return string Next Command after Creation
	 */
	function getAfterCreationCmd()
	{
		return "edit";
	}

	/**
	 * Get standard command.
	 * @return string Standard Command
	 */
	function getStandardCmd()
	{
		return "render";
	}

	/**
	 * Set tabs for other GUIs in the main GUI.
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		$userPrivileges = $this->permission->getAllUserPrivileges();

		if (in_array("accessAppointments", $userPrivileges))
		{
			// Appointments
			$ilTabs->addTab(
				"appointments", $this->txt("appointments"),
				$ilCtrl->getLinkTargetByClass('ilroomsharingappointmentsgui', "showBookings")
			);
		}
		// Standard info screen tab
		$this->addInfoTab();

		if (in_array("accessSearch", $userPrivileges))
		{
			// Search
			$this->tabs_gui->addTab(
				"search", $this->lng->txt("search"),
				$this->ctrl->getLinkTargetByClass('ilroomsharingsearchgui', "showSearchQuick")
			);
		}

		if (in_array("accessRooms", $userPrivileges))
		{
			// Rooms
			$this->tabs_gui->addTab(
				"rooms", $this->txt("rooms"),
				$this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms")
			);
		}

		if (in_array("accessFloorplans", $userPrivileges))
		{
			// Floorplans
			$this->tabs_gui->addTab(
				"floor_plans", $this->txt("room_floor_plans"),
				$this->ctrl->getLinkTargetByClass("ilroomsharingfloorplansgui", "render")
			);
		}

		if (in_array("accessSettings", $userPrivileges))
		{
			// Settings
			$this->tabs_gui->addTab(
				'settings', $this->txt('settings'), $this->ctrl->getLinkTarget($this, 'editSettings')
			);
		}

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			// Permission
			$this->addPermissionTab();
		}

		if (in_array("accessPrivileges", $userPrivileges))
		{
			// Privileges
			$this->tabs_gui->addTab(
				"privileges", $this->txt("privileges"),
				$this->ctrl->getLinkTargetByClass("ilroomsharingprivilegesgui", "showPrivileges")
			);
		}

		if (in_array("accessAppointments", $userPrivileges))
		{
			//show first tab per default
			$this->tabs_gui->activateTab('appointments');
		}
		else
		{
			//TODO GEHT DAS SO??
			$this->tabs_gui->activateTab("info");
		}
	}

	/**
	 * Show content
	 */
	function showContent()
	{
		$this->tabs_gui->activateTab('appointments');
	}

	/**
	 * Edit settings.
	 * This command uses the form class to display an input form.
	 */
	protected function editSettings()
	{
		$this->tabs_gui->activateTab('settings');
		$this->initSettingsForm();
		$this->getSettingsValues();
		$html = $this->settingsForm->getHTML();
		$this->tpl->setContent($html);
	}

	/**
	 * Update settings.
	 * This command uses the form class to display an input form.
	 */
	protected function updateSettings()
	{
		$this->tabs_gui->activateTab('settings');
		$this->initSettingsForm();

		if ($this->settingsForm->checkInput())
		{
			// Title and description (Standard)
			$this->object->setTitle($this->settingsForm->getInput('title'));
			$this->object->setDescription($this->settingsForm->getInput('desc'));

			// Online flag
			$this->object->setOnline($this->settingsForm->getInput('online'));

			// Max book time
			$date = $this->settingsForm->getInput('max_book_time')['date'];
			$time = $this->settingsForm->getInput('max_book_time')['time'];
			$this->object->setMaxBookTime($date . " " . $time);

			// Rooms agreement
			$agreementFile = $this->settingsForm->getInput('rooms_agreement');
			if ($agreementFile['size'] != 0)
			{
				$uploadFileId = $this->object->uploadRoomsAgreement($agreementFile,
					$this->object->getRoomsAgreementFileId());
				$this->object->setRoomsAgreementFileId($uploadFileId);
			}

			// Start update
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, 'editSettings');
		}

		$this->settingsForm->setValuesByPost();
		$this->tpl->setContent($this->settingsForm->getHtml());
	}

	/**
	 * Init settings form.
	 * This command uses the form class to display an input form.
	 */
	protected function initSettingsForm()
	{
		$this->settingsForm = new ilPropertyFormGUI();

		// Title and description (Standard)
		$titleField = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$titleField->setRequired(true);
		$this->settingsForm->addItem($titleField);
		$descField = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
		$this->settingsForm->addItem($descField);

		// Online flag
		$onlineField = new ilCheckboxInputGUI($this->lng->txt('online'), 'online');
		$this->settingsForm->addItem($onlineField);

		// Max booking time
		$maxtimeField = new ilRoomSharingTimeInputGUI(
			$this->lng->txt('rep_robj_xrs_max_book_time'), 'max_book_time');
		$maxtimeField->setShowTime(true);
		$maxtimeField->setMinuteStepSize(5);
		$maxtimeField->setShowDate(false);
		$this->settingsForm->addItem($maxtimeField);

		// Rooms agreement
		$roomsAgrField = new ilFileInputGUI($this->lng->txt('rep_robj_xrs_rooms_user_agreement'),
			"rooms_agreement");
		$roomsAgrField->setSize(50);
		$roomsAgrField->setRequired(false);
		$this->settingsForm->addItem($roomsAgrField);

		$this->settingsForm->addCommandButton('updateSettings', $this->lng->txt('save'));
		$this->settingsForm->setTitle($this->lng->txt('edit_properties'));
		$this->settingsForm->setFormAction($this->ctrl->getFormAction($this));
	}

	/**
	 * Get values to edit settings form.
	 */
	protected function getSettingsValues()
	{
		// Title and description (Standard)
		$values ['title'] = $this->object->getTitle();
		$values ['desc'] = $this->object->getDescription();

		// Online flag
		$values ['online'] = $this->object->isOnline();

		// Max book time
		$timestamp = strtotime($this->object->getMaxBookTime());
		$maxDate = date("Y-m-d", $timestamp);
		$maxTime = date("H:i:s", $timestamp);
		$values ['max_book_time'] = array('date' => $maxDate, 'time' => $maxTime);

		/* Rooms agreement */
		$fileId = $this->object->getRoomsAgreementFileId();
		if (!empty($fileId) && $fileId != "0")
		{
			$agreementFile = new ilObjMediaObject($this->object->getRoomsAgreementFileId());
			$media = $agreementFile->getMediaItem("Agreement");
			$source = $agreementFile->getDataDirectory() . "/" . $media->getLocation();

			$linkPresentation = "<p> <a target=\"_blank\" href=\"" . $source . "\">" .
				$this->lng->txt('rep_robj_xrs_current_rooms_user_agreement') . "</a></p>";
			$values ['rooms_agreement'] = $linkPresentation;
		}

		$this->settingsForm->setValuesByArray($values);
	}

	/**
	 * Forbids to import and to close an roomsharing pool.
	 * @see ilObjectPluginGUI::initCreateForm()
	 * @param string $a_new_type New type
	 * @return array Array with Creation methods. CFORM_CLONE and CFORM_IMPORT are ommited
	 */
	public function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);
		unset($forms[self::CFORM_CLONE]);
		unset($forms[self::CFORM_IMPORT]);
		return $forms;
	}

	/**
	 * Function that redirects to the overview of a made booking.
	 * !!! Will probably be moved to another class in further releases !!!
	 */
	public function showBooking()
	{
		global $ilCtrl;
		$booking_id = (int) $_GET['booking_id'];
		$ilCtrl->setCmd("showBookings");
		$this->render();
	}

	/**
	 * Displays a page with room information.
	 */
	public function showRoom()
	{
		$room_id = (int) $_GET['room_id'];
		$this->tabs_gui->setTabActive('rooms');
		$this->pl_obj->includeClass("rooms/detail/class.ilRoomSharingRoomGUI.php");
		$room_gui = new ilRoomSharingRoomGUI($this, $room_id);
		$room_gui->showRoomObject();
	}

	/**
	 * Displays a booking form where the user can book a given room.
	 */
	public function book()
	{
		$this->tabs_gui->clearTargets();
		$last_cmd = empty($_GET['last_cmd']) ? "showRooms" : $_GET['last_cmd'];
		$this->pl_obj->includeClass("booking/class.ilRoomSharingBookGUI.php");
		$book = new ilRoomSharingBookGUI(
			$this, $_GET['room_id'], $_GET['date'] . " " . $_GET['time_from'],
			$_GET['date'] . " " . $_GET['time_to']
		);
		$book->renderBookingForm();
		// the back button which links to where the user came from
		$this->tabs_gui->setBackTarget(
			$this->lng->txt("rep_robj_xrs_search_back"), $this->ctrl->getLinkTarget($this, $last_cmd)
		);
	}

	/**
	 * Used to show the user profile information.
	 * @global type $tpl
	 * @global type $ilCtrl
	 */
	public function showProfile()
	{
		global $tpl, $ilCtrl;
		$this->tabs_gui->clearTargets();
		$user_id = (int) $_GET['user_id'];
		$last_cmd = empty($_GET['last_cmd']) ? "showBookings" : (string) $_GET['last_cmd'];
		include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
		$profile = new ilPublicUserProfileGUI($user_id);
		$profile->setBackUrl(
			$this->ctrl->getLinkTargetByClass('ilroomsharingappointmentsgui', $last_cmd)
		);
		$tpl->setContent($ilCtrl->getHTML($profile));
	}

	/**
	 * Returns roomsharing pool id.
	 * @return int current pool_id of this booking pool
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 * @param int new pool id for this booking pool
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

	/**
	 * Initializes the date-seed for the calendar.
	 *
	 * @access private
	 * @return
	 */
	private function initSeed()
	{
		include_once('Services/Calendar/classes/class.ilDate.php');
		$this->seed = $_REQUEST['seed'] ? new ilDate($_REQUEST['seed'], IL_CAL_DATE) : new ilDate(date('Y-m-d',
				time()), IL_CAL_DATE);
		$_GET['seed'] = $this->seed->get(IL_CAL_DATE, '');
		$this->ctrl->saveParameter($this, array('seed'));
	}

	/**
	 * Initializes the calendar.
	 *
	 * @access private
	 * @return
	 */
	private function initCalendar()
	{
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
		$db = new ilRoomsharingDatabase($this->object->getPoolId());

		//Initialize the Calendar
		$this->initSeed();
		$cal_id = $db->getCalendarIdFromDatabase();
		$this->cal = new ilRoomSharingCalendar($this->seed, $cal_id, $this);
		if ($cal_id == 0 || $cal_id != $this->cal->getCalendarId())
		{
			//if calendar is new, save id in pools-table
			$cal_id = $this->cal->getCalendarId();
			$db->setCalendarId($cal_id);
		}
	}

	/**
	 *  Creates an appointment in the calendar based on an confirmed booking
	 */
	public function addBookingAppointment($booking_values_array)
	{
		$this->cal->addAppointment($booking_values_array);
	}

}

?>
