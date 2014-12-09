<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRoomsTableGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingTextInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingTimeInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/search/class.ilRoomSharingSearchFormGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingPermissionUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivilegesConstants.php");
require_once("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

use ilRoomSharingPrivilegesConstants as PRIVC;

/**
 * Class ilRoomSharingSearchGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 * @property ilRoomSharingPermissionUtils $permission
 */
class ilRoomSharingSearchGUI
{
	private $rooms;
	private $ref_id;
	private $pool_id;
	private $permission;
	private $tabs;
	private $search_form;

	/**
	 * Constructor for the class ilRoomSharingSearchGUI
	 *
	 * @param ilObjRoomSharingGUI $a_parent_obj the main GUI-object, which is needed for the pool id
	 */
	public function __construct(ilObjRoomSharingGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl, $ilTabs, $rssPermission;

		$this->parent_obj = $a_parent_obj;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->permission = $rssPermission;
		$this->rooms = new ilRoomSharingRooms($this->pool_id, new ilRoomsharingDatabase($this->pool_id));
	}

	/**
	 * Executes the command given by ilCtrl.
	 */
	public function executeCommand()
	{
		// the default command "showSearch" will be executed, if none is set
		$cmd = $this->ctrl->getCmd("showSearch");
		$this->$cmd();
	}

	/**
	 * Dispaly a search form if the required privileges are actually set.
	 */
	public function showSearch()
	{
		if ($this->permission->checkPrivilege(PRIVC::ACCESS_SEARCH))
		{
			$this->tabs->setTabActive("search");
			$search_form = $this->createForm();
			$this->tpl->setContent($search_form->getHTML());
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission_for_action"));
			$this->ctrl->redirectByClass('ilinfoscreengui', 'showSummary', 'showSummary');
		}
	}

	/**
	 * Function which is called when the search results need to be applied.
	 */
	public function applySearch()
	{
		$search_form = $this->createForm();

		// continue only if the input data is correct
		if ($search_form->checkInput())
		{
			$search_form->writeInputsToSession();
			$this->showSearchResults();
		}
		// otherwise return to the form and display an error messages if needed
		else
		{
			$search_form->setValuesByPost();
			$this->tpl->setContent($search_form->getHTML());
		}
	}

	/**
	 * Resets the inputs of search form.
	 */
	public function resetSearch()
	{
		$search_form = $this->createForm();

		$search_form->resetFormInputs();
		$this->showSearch();
	}

	/**
	 * Displays the results for the given input.
	 */
	public function showSearchResults()
	{
		$new_search_toolbar = $this->createNewSearchToolbar();
		$search_form = $this->createForm();

		$rooms_table = new ilRoomSharingRoomsTableGUI($this, "showSearchResults", $this->ref_id);
		$rooms_table->setTitle($this->lng->txt("search_results"));
		$rooms_table->getItems($this->getFormInput($search_form));
		$this->tpl->setContent($new_search_toolbar->getHTML() . $rooms_table->getHTML());
	}

	/**
	 * The toolbar is used for displaying a button, which allows the user to start a new search.
	 *
	 * @return \ilToolbarGUI
	 */
	private function createNewSearchToolbar()
	{
		$toolbar = new ilToolbarGUI();
		$target = $this->ctrl->getLinkTarget($this, "showSearch");
		$toolbar->addButton($this->lng->txt("search_new"), $target);

		return $toolbar;
	}

	/**
	 * Puts together an array which contains the search criterias for the search results. The
	 * standard procedure is to get those values from POST, but here it is actually coming from the
	 * SESSION.
	 *
	 * @return array returns the filter array
	 * @param ilRoomSharingSearchFormGUI the search form
	 */
	private function getFormInput($search_form)
	{
		$filtered_inputs = array();
		$room = $search_form->getInputFromSession("room_name");

		// "Room"
		// make sure that "0"-strings are not ignored
		if ($room || $room === "0")
		{
			$filtered_inputs["room_name"] = $room;
		}

		// "Seats"
		$seats = $search_form->getInputFromSession("room_seats");
		if ($seats)
		{
			$filtered_inputs["room_seats"] = $seats;
		}

		// "Date" and "Time"
		$date = $search_form->getInputFromSession("date");
		$filtered_inputs["date"] = $date["date"];
		$time_from = $search_form->getInputFromSession("time_from");
		$filtered_inputs["time_from"] = $time_from["time"];
		$time_to = $search_form->getInputFromSession("time_to");
		$filtered_inputs["time_to"] = $time_to["time"];

		// "Room Attributes"
		$room_attributes = $this->rooms->getAllAttributes();
		foreach ($room_attributes as $room_attribute)
		{
			$attr_value = $search_form->getInputFromSession("attribute_" . $room_attribute .
				"_amount", false);

			if ($attr_value)
			{
				$filtered_inputs["attributes"][$room_attribute] = $attr_value;
			}
		}
		return $filtered_inputs;
	}

	/**
	 * Creates and returns the uick search form.
	 *
	 * @return \ilRoomSharingSearchFormGUI the customized search form
	 */
	private function createForm()
	{
		$search_form = new ilRoomSharingSearchFormGUI();
		$search_form->setId("searchform");
		$search_form->setTitle($this->lng->txt("search"));
		$search_form->addCommandButton("applySearch", $this->lng->txt("rep_robj_xrs_search"));
		$search_form->addCommandButton("resetSearch", $this->lng->txt("reset"));
		$search_form->setFormAction($this->ctrl->getFormAction($this));

		$this->search_form = $search_form;
		$form_items = $this->createFormItems();
		foreach ($form_items as $item)
		{
			$search_form->addItem($item);
		}

		return $search_form;
	}

	private function createFormItems()
	{
		$form_items = array();

		$form_items[] = $this->createRoomFormItem();
		$form_items[] = $this->createSeatsFormItem();
		$form_items[] = $this->createDateFormItem();
		$form_items[] = $this->createTimeRangeFormItem();
		$room_attribute_items = $this->createRoomAttributeFormItems();
		$form_items = array_merge($form_items, $room_attribute_items);

		return array_filter($form_items);
	}

	/**
	 * Creates an input item which allows to type in a room name.
	 */
	private function createRoomFormItem()
	{
		$room_name_input = new ilRoomSharingTextInputGUI($this->lng->txt("rep_robj_xrs_room"), "room_name");
		$room_name_input->setParent($this->search_form);
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

		return $room_name_input;
	}

	/**
	 * Creates a combination input item containing a number input field for the desired seat amount.
	 */
	private function createSeatsFormItem()
	{
		$rooms_seats_text = $this->lng->txt("rep_robj_xrs_needed_seats") . " (" . $this->lng->txt("rep_robj_xrs_amount") . ")";
		$room_seats_input = new ilRoomSharingNumberInputGUI($rooms_seats_text, "room_seats");
		$room_seats_input->setParent($this->search_form);
		$room_seats_input->setMaxLength(8);
		$room_seats_input->setSize(8);
		$room_seats_input->setMinValue(0);
		$room_seats_input->setMaxValue($this->rooms->getMaxSeatCount());
		$room_seats_input->readFromSession();

		return $room_seats_input;
	}

	/**
	 * Used to create form item for the date.
	 */
	private function createDateFormItem()
	{
		// Date
		$date_comb = new ilCombinationInputGUI($this->lng->txt("date"), "date");
		$date = new ilDateTimeInputGUI("", "date");

		$date_given = unserialize($_SESSION ["form_searchform"] ["date"]);
		if (!empty($date_given['date']))
		{
			$date->setDate(new ilDate($date_given['date'], IL_CAL_DATE));
		}

		$date_comb->setRequired(true);
		$date_comb->addCombinationItem("date", $date, $this->lng->txt("rep_robj_xrs_on"));

		return $date_comb;
	}

	/**
	 * Creates a time range form item which consists of an ilCombinationGUI containing two
	 * customized ilDateTimeInputGUIs in the shape of an ilRoomSharingTimeInputGUI.
	 */
	private function createTimeRangeFormItem()
	{
		global $ilUser;

		$time_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_time_range"), "time");
		$time_from = new ilRoomSharingTimeInputGUI("", "time_from");
		$time_from->setShowTime(true);
		$time_from->setShowDate(false);
		$time_from->setMinuteStepSize(5);

		$time_from_given = unserialize($_SESSION ["form_searchform"] ["time_from"]);
		$time_to_given = unserialize($_SESSION ["form_searchform"] ["time_to"]);

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

		return $time_comb;
	}

	/**
	 * If room attributes are present, display some input fields for the desired amount of those
	 * attributes.
	 */
	private function createRoomAttributeFormItems()
	{
		$room_attribute_items = array();
		$room_attributes = $this->rooms->getAllAttributes();
		foreach ($room_attributes as $room_attribute)
		{
			// setup an ilRoomSharingNumberInputGUI for the room attributes
			$room_attribute_title = $room_attribute . " (" . $this->lng->txt("rep_robj_xrs_amount") . ")";
			$room_attribute_postvar = "attribute_" . $room_attribute . "_amount";
			$room_attribute_input = new ilRoomSharingNumberInputGUI($room_attribute_title,
				$room_attribute_postvar);
			$room_attribute_input->setParent($this->search_form);
			$room_attribute_input->setMaxLength(8);
			$room_attribute_input->setSize(8);
			$room_attribute_input->setMinValue(0);
			$max = $this->rooms->getMaxCountForAttribute($room_attribute);
			$max_num = isset($max) ? $max : 0;
			$room_attribute_input->setMaxValue($max_num);
			$room_attribute_input->readFromSession();

			$room_attribute_items[] = $room_attribute_input;
		}

		return $room_attribute_items;
	}

	/**
	 * Set the pool id.
	 *
	 * @param integer $pool_id the pool id to be set
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}

	/**
	 * Get the pool id.
	 *
	 * @return integer the pool id of this class
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

}

?>
