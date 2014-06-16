<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
include_once('Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * User Interface class for RoomSharing repository object.
 *
 * User interface classes process GET and POST parameter and call
 * application classes to fulfill certain tasks.
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author tmatern
 * @author tröhrig
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilRoomSharingSearchGUI

 * @ilCtrl_Calls ilObjRoomSharingGUI: ilRoomSharingAppointmentsGUI, ilRoomSharingRoomsGUI, ilRoomSharingFloorplansGUI, ilPublicUserProfileGUI, ilRoomSharingBookGUI
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilRoomsharingRoomGUI
 * 
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilCalendarDayGUI, ilCalendarAppointmentGUI
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarInboxGUI
 * @ilCtrl_Calls ilObjRoomSharingGUI: ilConsultationHoursGUI, ilCalendarBlockGUI, ilColumnGUI 
 *

 * @ilCtrl_isCalledBy ilObjRoomSharingGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI 
 * @ilCtrl_IsCalledBy ilObjRoomSharingGUI: ilColumnGUI
 *
 */
class ilObjRoomSharingGUI extends ilObjectPluginGUI
{

    //Settings form gui
    protected $settingsForm;
    //Pool id
    protected $pool_id;
    protected $pl_obj;
    protected $cal, $seed;

    /**
     * Initialization.
     */
    protected function afterConstructor()
    {
    		//Initialize the Calendar
        include_once("./Services/Calendar/classes/class.ilCalendarBlockGUI.php");
        $this->seed = new ilDate();
        $this->cal = new ilCalendarBlockGUI(true);
        $this->cal->setCurrentDetailLevel(1);
        $this->initCategories();
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
        global $ilTabs, $ilCtrl, $tpl, $ilNavigationHistory, $cmd;
        $next_class = $ilCtrl->getNextClass($this);
// 		echo "Command: ".$cmd;
// 		echo "<br>Next_Class: ".$next_class;

        $this->pl_obj = new ilRoomSharingPlugin();
    	$this->pl_obj->includeClass("class.ilObjRoomSharing.php");
    	// Set pool id.
    	$this->pool_id = $this->object->getPoolID();
        
    
        $cmd = $ilCtrl->getCmd();

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
        // the special handling of the commands showSearchQuick and 
        // showSearchResults is needed becauseotherwise the wrong $next_class 
        // would be called
        else if ($cmd == 'showSearchQuick'  || $cmd == 'showSearchResults') 
        {
            $next_class = ilroomsharingsearchgui;
        }
        // the special handling of the commands addRoom and editRoom 
        else if ($cmd == 'addRoom'  || $cmd == 'editRoom')
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
                $this->pl_obj->includeClass("class.ilRoomSharingAppointmentsGUI.php");
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
                $this->pl_obj->includeClass("class.ilRoomSharingSearchGUI.php");
                $object_gui = & new ilRoomSharingSearchGUI($this);
                $ret = & $this->ctrl->forwardCommand($object_gui);
                break;

            // Rooms
            case 'ilroomsharingroomsgui':
                $this->tabs_gui->setTabActive('rooms');
                $this->pl_obj->includeClass("class.ilRoomSharingRoomsGUI.php");
                $object_gui = & new ilRoomSharingRoomsGUI($this);
                $ret = & $this->ctrl->forwardCommand($object_gui);
                break;
                
            // Room
            case 'ilroomsharingroomgui':
                $this->tabs_gui->setTabActive('rooms');
                $room_id = (int) $_GET['room_id'];
                $this->pl_obj->includeClass("class.ilRoomSharingRoomGUI.php");
                $object_gui = & new ilRoomSharingRoomGUI($this, $room_id);
                $ret = & $this->ctrl->forwardCommand($object_gui);
                break;
            
            // Book.
            case 'ilroomsharingbookgui':
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget($this->lng->txt('back'), $ilCtrl->getLinkTarget($this, "showSearchResults"));
                $this->pl_obj->includeClass("class.ilRoomSharingBookGUI.php");
                $book_gui = & new ilRoomSharingBookGUI($this);
                $ret = & $this->ctrl->forwardCommand($book_gui);
                break;

            // Floorplans
            case 'ilroomsharingfloorplansgui':
                $this->tabs_gui->setTabActive('floor_plans');
                $this->pl_obj->includeClass("class.ilRoomSharingFloorPlansGUI.php");
                $schedule_gui = & new ilRoomSharingFloorPlansGUI($this);
                $ret = & $this->ctrl->forwardCommand($schedule_gui);
                //$this->tpl->setContent("Die Ansicht der Pläne ist noch nicht an die neue Plugin-Ordnerstruktur angepasst.");
                break;

            // Permissions
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = & new ilPermissionGUI($this);
                $ret = & $this->ctrl->forwardCommand($perm_gui);
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
            // various CalendarGUIs
            case "ilcalendardaygui":
      	  		include_once("./Services/Calendar/classes/class.ilCalendarDayGUI.php");
            	$day = new ilCalendarDayGUI(new ilDate($_GET["seed"],IL_CAL_DATE));
            	$this->ctrl->forwardCommand($day);
            	break;
            case "ilcalendarmonthgui":
            	include_once("./Services/Calendar/classes/class.ilCalendarMonthGUI.php");
            	$month = new ilCalendarMonthGUI(new ilDate($_GET["seed"],IL_CAL_DATE));
            	$this->ctrl->forwardCommand($month);
            	break;
            case "ilcalendarweekgui":
            	include_once("./Services/Calendar/classes/class.ilCalendarWeekGUI.php");
            	$week = new ilCalendarweekGUI(new ilDate($_GET["seed"],IL_CAL_DATE));
            	$this->ctrl->forwardCommand($week);
            	break;
            case "ilcalendarblockgui":
            	$this->ctrl->forwardCommand($this->cal);
            	break;
            	
            // Standard cmd handling if cmd is not recognized.
            default:
                $cmd = $ilCtrl->getCmd('render');
//				echo "defaultcmd:".$cmd;
                $this->$cmd();
                break;
        }

        // Action menu (top right corner of the module)
// 		$this->addHeaderAction();

        //adds Minicalendar to the right 
        $tpl->setRightContent($this->cal->getHTML());
        return true;
    }

    /**
     * Default command that is executed if no "nextClass" can be determined.
     */
    public function render()
    {
        global $ilTabs, $ilCtrl;
        $ilTabs->setTabActive('appointments');
        $this->pl_obj->includeClass("class.ilRoomSharingAppointmentsGUI.php");
        $object_gui = & new ilRoomSharingAppointmentsGUI($this);
        $ilCtrl->forwardCommand($object_gui);
        return true;
    }

    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd()
    {
        return "edit";
    }

    /**
     * Get standard command
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

        // Appointments
        $ilTabs->addTab("appointments", $this->txt("appointments"), $ilCtrl->getLinkTargetByClass('ilroomsharingappointmentsgui', "showBookings"));

        // Standard info screen tab
        $this->addInfoTab();

        // Search
        $this->tabs_gui->addTab("search", $this->lng->txt("search"), $this->ctrl->getLinkTargetByClass('ilroomsharingsearchgui', "showSearchQuick"));

        // Rooms
        $this->tabs_gui->addTab("rooms", $this->txt("rooms"), $this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
	
        // Floorplans
        $this->tabs_gui->addTab("floor_plans", $this->txt("room_floor_plans"), $this->ctrl->getLinkTargetByClass("ilroomsharingfloorplansgui", "render"));

        // Show permissions and settings tabs if the user has write permissions.
        if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
        {
            // Settings
            $this->tabs_gui->addTab('settings', $this->txt('settings'), $this->ctrl->getLinkTarget($this, 'editSettings'));

            // Permission
            $this->addPermissionTab();
        }
        //show first tab per default
        $this->tabs_gui->activateTab('appointments');
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
            $this->object->setTitle($this->settingsForm->getInput('title'));
            $this->object->setDescription($this->settingsForm->getInput('desc'));
            $this->object->setOnline($this->settingsForm->getInput('online'));
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
        $this->settingsForm = new ilPropertyFormGUI ();

        // title
        $field = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $field->setRequired(true);
        $this->settingsForm->addItem($field);

        // description
        $field = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $this->settingsForm->addItem($field);

        // online
        $field = new ilCheckboxInputGUI($this->lng->txt('online'), 'online');
        $this->settingsForm->addItem($field);

        $this->settingsForm->addCommandButton('updateSettings', $this->lng->txt('save'));

        $this->settingsForm->setTitle($this->lng->txt('edit_properties'));
        $this->settingsForm->setFormAction($this->ctrl->getFormAction($this));
    }

    /**
     * Get values to edit settings form.
     */
    protected function getSettingsValues()
    {
        $values ['title'] = $this->object->getTitle();
        $values ['desc'] = $this->object->getDescription();
        $values ['online'] = $this->object->isOnline();

        $this->settingsForm->setValuesByArray($values);
    }

    /**
     * Forbids to import and to close an roomsharing pool.
     * @see ilObjectPluginGUI::initCreateForm()
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
     * !!! Landet später höchstwahrscheinlich noch in einer anderen Klasse !!!
     */
    public function showBooking()
    {
        global $ilCtrl;

        $booking_id = (int) $_GET['booking_id'];
        $ilCtrl->setCmd("showBookings");
        $this->render();
        echo "booking_id = " . $booking_id;
    }

    /**
     * Displays a page with room information.
     */
    public function showRoom()
    {
        $room_id = (int) $_GET['room_id'];
        $this->tabs_gui->setTabActive('rooms');
        $this->pl_obj->includeClass("class.ilRoomSharingRoomGUI.php");
        $room_gui = new ilRoomSharingRoomGUI($this, $room_id);
        $room_gui->showRoomObject();
    }
    
    /**
     * Displays a booking form, where the user can book a specific room.
     */
    public function book()
    {
        global $tpl, $ilCtrl, $lng;
        $this->tabs_gui->clearTargets();
        $room_id = $_GET['room_id'];
        $date = $_GET['date'];
        $time_from = $_GET['time_from'];
        $time_to = $_GET['time_to'];
        $last_cmd = empty($_GET['last_cmd']) ? "showRooms": $_GET['last_cmd'];
        $this->pl_obj->includeClass("class.ilRoomSharingBookGUI.php");
        $book = new ilRoomSharingBookGUI($this, $room_id, $date." ".$time_from, $date." ".$time_to);
        $book->renderObject();
        // the back button which links to where the user came from
        $this->tabs_gui->setBackTarget($lng->txt('rep_robj_xrs_search_back'), $ilCtrl->getLinkTarget($this, $last_cmd));
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
        $last_cmd = empty($_GET['last_cmd']) ? "showBookings": (String) $_GET['last_cmd'];
        include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
        $profile = new ilPublicUserProfileGUI($user_id);
        $profile->setBackUrl($this->ctrl->getLinkTargetByClass('ilroomsharingappointmentsgui', $last_cmd));
        $tpl->setContent($ilCtrl->getHTML($profile));
    }

    /**
     * Returns roomsharing pool id.
     */
    function getPoolId()
    {
        return $this->pool_id;
    }

    /**
     * Sets roomsharing pool id.
     */
    function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }

    /**
     * init categories of Calendar
     * 
     * Used to display personal appointments in the minicalendar
     * copied from ilPDBlockCalendar
     *
     * @access protected
     * @param
     * @return
     */
    protected function initCategories()
    {
    	include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
    	if(ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
    	{
    		$mode = ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP;
    	}
    	else
    	{
    		$mode = ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS;
    	}
    
    	include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
    	ilCalendarCategories::_getInstance()->initialize($mode,(int)$_GET['ref_id'],true);
    }
}

?>
