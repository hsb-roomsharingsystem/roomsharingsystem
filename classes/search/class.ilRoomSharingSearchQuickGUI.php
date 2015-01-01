<?php

require_once("./Customizing/global/plugins/Services/Repository/" .
	"RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once('./Services/Calendar/classes/class.ilCalendarRecurrence.php');
require_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');

/**
 * Class ilRoomSharingSearchQuickGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @version $Id$
 */
class ilRoomSharingSearchQuickGUI
{
	protected $rooms;
	protected $ref_id;
	private $pool_id;
	protected $rec;

	/**
	 * Constructor for the class ilRoomSharingSearchQuickGUI
	 * @param object $a_parent_obj
	 */
	public function __construct(ilRoomSharingSearchGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;

		$this->parent_obj = $a_parent_obj;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->rooms = new ilRoomSharingRooms($this->pool_id,
			new ilRoomsharingDatabase($a_parent_obj->getPoolID()));
	}

	/**
	 * Execute the command given.
	 * @return Returns true if command was successful
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		// the default command, if none is set
		$cmd = $ilCtrl->getCmd("showSearchQuick");

		switch ($next_class)
		{
			default:
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Show a quick search form.
	 */
	public function showSearchQuickObject()
	{
		global $tpl;
		$qsearch_form = $this->initForm();

		$tpl->setContent($qsearch_form->getHTML());
	}

	/**
	 * Function which is called when the search results need to be applied.
	 * @global type $tpl
	 */
	public function applySearchObject()
	{
		global $tpl;
		$qsearch_form = $this->initForm();

		// continue only if the input data is correct
		if ($qsearch_form->checkInput())
		{
			$qsearch_form->writeInputsToSession();
			$this->showSearchResultsObject();

			// otherwise return to the form and display an error messages if needed
		}
		else
		{
			$qsearch_form->setValuesByPost();
			$tpl->setContent($qsearch_form->getHTML());
		}
	}

	/**
	 * Resets the search form
	 */
	public function resetSearchObject()
	{
		$qsearch_form = $this->initForm();

		$qsearch_form->resetFormInputs();
		$this->showSearchQuickObject();
	}

	/**
	 * Displays the results for the given input.
	 */
	public function showSearchResultsObject()
	{
		global $tpl;
		$qsearch_form = $this->initForm();

		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/" .
			"classes/rooms/class.ilRoomSharingRoomsTableGUI.php");
		$roomsTable = new ilRoomSharingRoomsTableGUI($this, 'showSearchResults', $this->ref_id);
		$roomsTable->setTitle($this->lng->txt("rep_robj_xrs_search_results"));
		$roomsTable->getItems($this->getFormInput($qsearch_form));

		$roomsTable->addHeaderCommand($this->ctrl->getLinkTargetByClass('ilobjroomsharinggui',
				'showSearchQuick'), $this->lng->txt("rep_robj_xrs_back_to_search"));

		$tpl->setContent($roomsTable->getHTML());
	}

	/**
	 * Puts together an array which contains the search criterias for the
	 * search results. The standard procedure is to get those values from
	 * POST, but here it is actually coming from the SESSION.
	 * @return returns the filter array
	 * @param object the search form
	 */
	protected function getFormInput($a_qsearch_form)
	{
		$filter = array();
		$room = $a_qsearch_form->getInputFromSession("room_name");

		// "Room"
		// make sure that "0"-strings are not ignored
		if ($room || $room === "0")
		{
			$filter["room_name"] = $room;
		}

		// "Seats"
		$seats = $a_qsearch_form->getInputFromSession("room_seats");
		if ($seats)
		{
			$filter["room_seats"] = $seats;
		}

		// "Date" and "Time"
		$date = $a_qsearch_form->getInputFromSession("date");
		$filter["date"] = $date["date"];
		$time_from = $a_qsearch_form->getInputFromSession("time_from");
		$filter["time_from"] = $time_from["time"];
		$time_to = $a_qsearch_form->getInputFromSession("time_to");
		$filter["time_to"] = $time_to["time"];

		// "Room Attributes"
		$room_attributes = $this->rooms->getAllAttributes();
		foreach ($room_attributes as $room_attribute)
		{
			$attr_value = $a_qsearch_form->getInputFromSession("attribute_" . $room_attribute .
				"_amount", false);

			if ($attr_value)
			{
				$filter["attributes"][$room_attribute] = $attr_value;
			}
		}

		//Recurrence
		$filter["recurrence"] = $_SESSION['form_qsearchform'];

		return $filter;
	}

	/**
	 * Creates and returns the quick search form.
	 * @return \ilRoomSharingSearchFormGUI the customized quick search form
	 */
	protected function initForm()
	{
		global $ilCtrl, $lng;
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingTextInputGUI.php");
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/search/class.ilRoomSharingSearchFormGUI.php");
		$qsearch_form = new ilRoomSharingSearchFormGUI();
		// include of YAHOO Library (by ILIAS). Needed to initialize Recurrence GUI
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDomEvent();
		$qsearch_form->setId("qsearchform");

		$this->createRoomFormItem($qsearch_form);
		$this->createSeatsFormItem($qsearch_form);
		$this->createDateFormItem($qsearch_form);
		$this->createTimeRangeFormItem($qsearch_form);
		$this->createRecurrenceFormItem($qsearch_form);
		$this->createRoomAttributeFormItem($qsearch_form);
		$qsearch_form->setTitle($lng->txt("rep_robj_xrs_quick_search"));
		$qsearch_form->addCommandButton("applySearch", $lng->txt("rep_robj_xrs_search"));
		$qsearch_form->addCommandButton("resetSearch", $lng->txt("reset"));
		$qsearch_form->setFormAction($ilCtrl->getFormAction($this));

		return $qsearch_form;
	}

	/**
	 * Creates an input item which allows you to type in a room name.
	 * @param object the search form
	 */
	protected function createRoomFormItem($a_qsearch_form)
	{
		$room_name_input = new ilRoomSharingTextInputGUI($this->lng->txt("rep_robj_xrs_room"), "room_name");
		$room_name_input->setParent($a_qsearch_form);
		$room_name_input->setMaxLength(14);
		$room_name_input->setSize(14);

		$room_get_value = $_GET["room"];
		//if the user was redirected from the room list, set the value for the room accordingly
		if ($room_get_value)
		{
			$room_name_input->setValue($room_get_value);
		}
		else // otherwise use the input that has been set before
		{
			$room_name_input->readFromSession();
		}
		$a_qsearch_form->addItem($room_name_input);
	}

	/**
	 * Creates a combination input item containing a number input field for
	 * the desired seat amount.
	 * @param object the search form
	 */
	protected function createSeatsFormItem($a_qsearch_form)
	{
		// Seats
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once("./Customizing/global/plugins/Services/Repository/" .
			"RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
		$room_seats_input = new ilRoomSharingNumberInputGUI($this->lng->txt("rep_robj_xrs_needed_seats") .
			" (" . $this->lng->txt("rep_robj_xrs_amount") . ")", "room_seats");
		$room_seats_input->setParent($a_qsearch_form);
		$room_seats_input->setMaxLength(8);
		$room_seats_input->setSize(8);
		$room_seats_input->setMinValue(0);
		$room_seats_input->setMaxValue($this->rooms->getMaxSeatCount());
		$room_seats_input->readFromSession();
		$a_qsearch_form->addItem($room_seats_input);
	}

	/**
	 * Used to create form item for the date.
	 * @param object the search form
	 */
	protected function createDateFormItem($a_qsearch_form)
	{
		// Date
		include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$date_comb = new ilCombinationInputGUI($this->lng->txt("date"), "date");
		$date = new ilDateTimeInputGUI("", "date");

		$date_given = unserialize($_SESSION ["form_qsearchform"] ["date"]);
		if (!empty($date_given['date']))
		{
			$date->setDate(new ilDate($date_given['date'], IL_CAL_DATE));
		}

		$date_comb->setRequired(true);
		$date_comb->addCombinationItem("date", $date, $this->lng->txt("rep_robj_xrs_on"));
		$a_qsearch_form->addItem($date_comb);
	}

	/**
	 * Creates recurrence gui.
	 * Includes some settings to modify initial recurrence gui.
	 * @param type $a_qsearch_form
	 */
	protected function createRecurrenceFormItem($a_qsearch_form)
	{
		$this->getRecurrence();
		$rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
		// set possible frequence types (IL_CAL_FREQ_YEARLY not needed)
		$subforms = array(IL_CAL_FREQ_DAILY, IL_CAL_FREQ_WEEKLY, IL_CAL_FREQ_MONTHLY);
		$rec->setRecurrence($this->rec);
		$rec->setEnabledSubForms($subforms);
		// no unlimited recurrences
		$rec->allowUnlimitedRecurrences(false);
		$a_qsearch_form->addItem($rec);
	}

	/**
	 * Read recurrence from Session
	 */
	protected function getRecurrence()
	{
		$this->rec = new ilCalendarRecurrence();
		$fre = unserialize($_SESSION ["form_qsearchform"] ["frequence"]);
		$this->rec->setFrequenceType($fre);
		switch ($fre)
		{
			case "NONE":
				break;
			case "DAILY":
				break;
			case "WEEKLY":
				$days = unserialize($_SESSION ["form_qsearchform"] ["weekdays"]);
				$d = array();
				if (is_array($days))
				{
					foreach ($days as $day)
					{
						$d[] = $day;
					}
				}
				$this->rec->setBYDAY(implode(",", $d));
				break;
			case "MONTHLY":
				$start_type = unserialize($_SESSION ["form_qsearchform"] ["start_type"]);
				if ($start_type == "weekday")
				{
					$w1 = unserialize($_SESSION ["form_qsearchform"] ["weekday_1"]);
					$w2 = unserialize($_SESSION ["form_qsearchform"] ["weekday_2"]);
					if ($w2 == 8)
					{
						$this->rec->setBYSETPOS($w1);
						$this->rec->setBYDAY('MO,TU,WE,TH,FR');
					}
					elseif ($w2 == 9)
					{
						$this->rec->setBYMONTHDAY($w1);
					}
					else
					{
						$this->rec->setBYDAY($w1 . $w2);
					}
				}
				elseif ($start_type == "monthday")
				{
					$this->rec->setBYMONTHDAY(unserialize($_SESSION ["form_qsearchform"] ["monthday"]));
				}
				break;
			default:
				break;
		}
		$repeat_type = unserialize($_SESSION ["form_qsearchform"] ["repeat_type"]);
		$this->rec->setInterval(unserialize($_SESSION ["form_qsearchform"] ["repeat_amount"]));
		if ($repeat_type == "max_amount")
		{
			$this->rec->setFrequenceUntilCount(unserialize($_SESSION ["form_qsearchform"] ["repeat_until"]));
		}
		elseif ($repeat_type == "max_date")
		{
			$date = unserialize($_SESSION ["form_qsearchform"] ["repeat_until"]);
			$date2 = date('Y-m-d H:i:s',
				mktime(0, 0, 0, $date['date']['m'], $date['date']['d'], $date['date']['y']));
			echo $date2;
			$this->rec->setFrequenceUntilDate(new ilDateTime($date2, IL_CAL_DATETIME));
		}
	}

	/**
	 * Creates a time range form item which consists of an ilCombinationGUI
	 * containing two customized ilDateTimeInputGUIs in the shape of
	 * an ilRoomSharingTimeInputGUI.
	 * @param type $a_qsearch_form
	 */
	protected function createTimeRangeFormItem($a_qsearch_form)
	{
		// Time Range
		global $ilUser;
		include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingTimeInputGUI.php");

		$time_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_time_range"), "time");
		$time_from = new ilRoomSharingTimeInputGUI("", "time_from");
		$time_from->setShowTime(true);
		$time_from->setShowDate(false);
		$time_from->setMinuteStepSize(5);


		$time_from_given = unserialize($_SESSION ["form_qsearchform"] ["time_from"]);
		$time_to_given = unserialize($_SESSION ["form_qsearchform"] ["time_to"]);

		if ($time_from_given['time'] == '00:00:00')
		{
			//set controls according to current time
			//
			//get current time and add leading 0
			$hr_from = (date('H') + 1 < 10 ? "0" . (date('H') + 1) : (date('H') + 1));

			//add leading 0
			$hr_to = ($hr_from + 1 < 10 ? "0" . ($hr_from + 1) : ($hr_from + 1));

			$time_from_given['time'] = $hr_from . ':00:00';
			$time_to_given['time'] = $hr_to . ':00:00';
		}

		if (!empty($time_from_given['date']) && !empty($time_from_given['time']))
		{
			$time_from->setDate(new ilDate($time_from_given['date'] . ' ' . $time_from_given['time'],
				IL_CAL_DATETIME, $ilUser->getTimeZone()));
		}

		$time_comb->addCombinationItem("time_from", $time_from, $this->lng->txt("rep_robj_xrs_between"));
		$time_to = new ilRoomSharingTimeInputGUI("", "time_to");
		$time_to->setShowTime(true);
		$time_to->setShowDate(false);
		$time_to->setMinuteStepSize(5);

		if (!empty($time_to_given['date']) && !empty($time_to_given['time']))
		{
			$time_to->setDate(new ilDate($time_to_given['date'] . ' ' . $time_to_given['time'],
				IL_CAL_DATETIME, $ilUser->getTimeZone()));
		}

		$time_comb->addCombinationItem("time_to", $time_to, $this->lng->txt("and"));
		$time_comb->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
		$time_comb->setRequired(true);
		$a_qsearch_form->addItem($time_comb);
	}

	/**
	 * If room attributes are present, display some input fields for the desired
	 * amount of those attributes.
	 * @param object the search form
	 */
	protected function createRoomAttributeFormItem($a_qsearch_form)
	{
		// Room Attributes
		include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
		$room_attributes = $this->rooms->getAllAttributes();
		foreach ($room_attributes as $room_attribute)
		{
			// setup an ilRoomSharingNumberInputGUI for the room attributes
			$room_attribute_input = new ilRoomSharingNumberInputGUI($room_attribute . " (" .
				$this->lng->txt("rep_robj_xrs_amount") . ")", "attribute_" .
				$room_attribute . "_amount");
			$room_attribute_input->setParent($a_qsearch_form);
			$room_attribute_input->setMaxLength(8);
			$room_attribute_input->setSize(8);
			$room_attribute_input->setMinValue(0);
			$max = $this->rooms->getMaxCountForAttribute($room_attribute);
			$max_num = isset($max) ? $max : 0;
			$room_attribute_input->setMaxValue($max_num);

			$room_attribute_input->readFromSession();
			$a_qsearch_form->addItem($room_attribute_input);
		}
	}

	/**
	 * Set the poolID of bookings
	 *
	 * @param integer $pool_id
	 *        	poolID
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}

	/**
	 * Get the PoolID of bookings
	 *
	 * @return integer PoolID
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

}

?>
