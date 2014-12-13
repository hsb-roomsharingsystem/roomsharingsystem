<?php

require_once ('./Services/Table/classes/class.ilTable2GUI.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php');
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php');

/**
 * Class ilRoomSharingRoomsTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 */
class ilRoomSharingRoomsTableGUI extends ilTable2GUI
{
	private $rooms;
	private $message = '';
	private $messageNeeded = false;
	private $messagePlural = false;
	private $book;

	/**
	 * Constructor for the class ilRoomSharingRoomsTableGUI
	 *
	 * @param unknown $a_parent_obj
	 * @param unknown $a_parent_cmd
	 * @param unknown $a_ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng;

		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->ref_id = $a_ref_id;
		// in order to keep filter settings, table ordering etc. set an ID
		// this is better to be unset for debug sessions
		// $this->setId("roomtable");
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->book = new ilRoomSharingBook($a_parent_obj->getPoolID());
		$this->rooms = new ilRoomSharingRooms($a_parent_obj->getPoolID(),
			new ilRoomsharingDatabase($a_parent_obj->getPoolID()));
		$this->lng->loadLanguageModule("form");

		$this->setTitle($this->lng->txt("rep_robj_xrs_rooms"));
		$this->setLimit(10); // datasets that are displayed per page
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setEnableHeader(true);
		$this->_addColumns(); // add columns and column headings
		$this->setEnableHeader(true);
		$this->setRowTemplate("tpl.room_rooms_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");
	}

	/**
	 * Gets all the items that need to populated into the table.
	 *
	 * @param array $filter
	 */
	public function getItems(array $filter)
	{
		$data = $this->getFilteredData($filter);

		$old_name = $filter["room_name"];
		$new_name = preg_replace('/\D/', '', filter_var($filter["room_name"], FILTER_SANITIZE_NUMBER_INT));

		if (count($data) == 0 && ($new_name || $new_name === "0") && ($old_name || $old_name === "0") && $old_name
			!== $new_name)
		{
			$filter["room_name"] = $new_name;
			//Hier sind die Daten der Räume drin, die extra gefiltert werden
			$data = $this->getFilteredData($filter);

			$message = $this->lng->txt('rep_robj_xrs_no_match_for') . " $old_name " .
				$this->lng->txt('rep_robj_xrs_found') . ". " .
				$this->lng->txt('rep_robj_xrs_results_for') . " $new_name.";

			ilUtil::sendInfo($message);
		}
		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	//Eigene Implementation für das Prüfen der Raumverfügbarkeit
	//für jedes Datum, welches generiert wird
	public function getFilteredData(array $filter)
	{
		echo "<br>OriginalFilter:<br>";
		print_r($filter);

		switch (unserialize($filter['recurrence']['frequence']))
		{
			case "DAILY":
				$data = $this->getDailyFilteredData($filter);
				break;
			case "WEEKLY":
				$data = $this->getWeeklyFilteredData($filter);
				break;
			case "MONTHLY":
				$data = $this->getMonthlyFilteredData($filter);
				break;
			default:
				$data = $this->rooms->getList($filter);
				break;
		}

		echo "<br><br>";
		print_r($data);

		return $data;
	}

	//Diese Funktion ist noch nicht fertig und diente nur zum Test der
	//Tagesgenerator Funktionen für Monate
	private function getMonthlyFilteredData(array $filter)
	{
		$repeat_until = unserialize($filter['recurrence']['repeat_until']);
		$rpt_amount = unserialize($filter['recurrence']['repeat_amount']);
		$weekday_1 = unserialize($filter['recurrence']['weekday_1']);
		$weekday_2 = unserialize($filter['recurrence']['weekday_2']);

		$repeat_until = $repeat_until['date']['y'] . "-" . $repeat_until['date']['m'] . "-" . $repeat_until['date']['d'];
		$this->book->generateMonthlyDaysAtVariableDateWithCount($filter['date'], $weekday_1, $weekday_2,
			$repeat_until, $rpt_amount);
	}

	// Diese Funktion ist noch nicht fertig und diente nur zum Test
	// der Tagesgenerator Funktionen für Wochen
	private function getWeeklyFilteredData(array $filter)
	{
		$repeat_until = unserialize($filter['recurrence']['repeat_until']);
		$rpt_amount = unserialize($filter['recurrence']['repeat_amount']);
		$weekdays = unserialize($filter['recurrence']['weekdays']);
		$repeat_until = $repeat_until['date']['y'] . "-" . $repeat_until['date']['m'] . "-" . $repeat_until['date']['d'];
		$this->book->generateWeeklyDaysWithEndDate($filter['date'], $repeat_until, $weekdays, $rpt_amount);
	}

	//So kompliziert sieht derzeit die Funktion aus, wenn täglich gewählt ist
	private function getDailyFilteredData(array $filter)
	{
		$datas = array();
		$until_date = $this->createDateFormatBySerializedDate($filter['recurrence']['date_until']);
		$until_count = unserialize($filter['recurrence']['count_until']);
		if (unserialize($filter['recurrence']['repeat_type']) == "max_date" || isset($until_count))
		{
			$days = $this->book->generateDailyDaysWithCount($filter['date'], $until_count);
			foreach ($days as $day)
			{
				$filter['date'] = $day;
				$datas[] = $this->rooms->getList($filter);
			}
		}
		elseif (unserialize($filter['recurrence']['repeat_type']) == "max_amount")
		{
			$this->book->generateDailyDaysWithCount($filter['date'],
				unserialize($filter['recurrence']['count_until']));
		}

		$return_data = array();

		//$datas beinhaltet an dieser Stelle alle Räume
		//kannst bei bedarf ja mal echo'n oder print_r'n
		//Jetzt muss irgendwie rausgefiltert werden,
		//ob ein Raum wirklich an jedem Datum frei ist
		//und das gespeichert werden, dadran arbeite ich noch
		foreach ($datas as $data)
		{
			foreach ($data as $available_room)
			{

			}
		}
	}

	private function createDateFormatBySerializedDate($serialized_date)
	{
		$until_day = unserialize($serialized_date)['date']['d'];
		$until_month = unserialize($serialized_date)['date']['m'];
		$until_year = unserialize($serialized_date)['date']['y'];
		return $until_year . "-" . $until_month . "-" . $until_day;
	}

	/**
	 * Adds columns and column headings to the table.
	 */
	private function _addColumns()
	{
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_seats"));
		$this->addColumn($this->lng->txt("rep_robj_xrs_room_attributes")); // not sortable
		$this->addColumn("", "action");
	}

	/**
	 * Fills an entire table row with the given set.
	 * The corresponding array has the following shape:
	 *
	 * @see ilTable2GUI::fillRow()
	 * @param $a_set data set for that row
	 */
	public function fillRow($a_set)
	{
		global $ilAccess;

		// ### Room ###
		$this->tpl->setVariable('TXT_ROOM', $a_set ['room']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_set ['room_id']);
		$this->tpl->setVariable('HREF_ROOM',
			$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');

		// ### Seats ###
		$this->tpl->setVariable('TXT_SEATS', $a_set ['seats']);

		// ### Room Attributes ###
		$attribute_keys = array_keys($a_set ['attributes']);
		$attribute_count = count($attribute_keys);
		for ($i = 0; $i < $attribute_count; ++$i)
		{
			$this->tpl->setCurrentBlock('attributes');
			$attribute = $attribute_keys [$i];

			// make sure that the last room attribute has no break at the end
			if ($i < $attribute_count - 1)
			{
				$this->tpl->setVariable('TXT_SEPARATOR', '<br>');
			}
			$this->tpl->setVariable('TXT_AMOUNT', $a_set ['attributes'] [$attribute]);
			$this->tpl->setVariable('TXT_ATTRIBUTE', $attribute);
			$this->tpl->parseCurrentBlock();
		}

		// actions
		$this->tpl->setCurrentBlock("actions");
		$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_room_book'));
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room', $a_set ['room']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_set ['room_id']);
		$_SESSION['last_cmd'] = $this->parent_cmd;

		// only display a booking form if a search was initialized beforehand
		if ($this->parent_cmd === "showSearchResults")
		{
			// if this class is used to display search results, the input made
			// must be transported to the book form
			$date = unserialize($_SESSION ["form_qsearchform"] ["date"]);
			$time_from = unserialize($_SESSION ["form_qsearchform"] ["time_from"]);
			$time_to = unserialize($_SESSION ["form_qsearchform"] ["time_to"]);
			// infos of book series
			$this->handleBookSeriesAttributes();

			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'date', $date ['date']);
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_from', $time_from ['time']);
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_to', $time_to ['time']);
			$this->tpl->setVariable('LINK_ACTION',
				$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'book'));
			// free those parameters, since we don't need them anymore
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'date', "");
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_from', "");
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_to', "");
		}
		else
		{
			// the user is linked to the quick search form if he is trying to book
			// a room when the normal room list is displayed
			$this->tpl->setVariable('LINK_ACTION',
				$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showSearchQuick'));
		}

		// unset the parameters; just in case
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', "");

		// allow administrators to edit and delete rooms, but only if the room list and not the
		// search results are displayed
		if ($ilAccess->checkAccess('write', '', $this->ref_id) && $this->parent_cmd === "showRooms")
		{
			$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable('LINK_ACTION',
				$this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_set ['room_id']);
			$this->tpl->setVariable('LINK_ACTION',
				$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'editRoom'));
			$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('edit'));
			$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable('LINK_ACTION',
				$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'confirmDeleteRoom'));
			$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Build a filter that can used for database-queries.
	 *
	 * @return array the filter
	 */
	public function getCurrentFilter()
	{
		$filter = array();
		// make sure that "0"-strings are not ignored
		if ($this->filter ["room"] ["room_name"] || $this->filter ["room"] ["room_name"] === "0")
		{
			$filter ["room_name"] = $this->filter ["room"] ["room_name"];
		}
		if ($this->filter ["seats"] ["room_seats"] || $this->filter ["seats"] ["room_seats"] === 0.0)
		{
			$filter ["room_seats"] = $this->filter ["seats"] ["room_seats"];
		}

		if ($this->filter ["attributes"])
		{
			foreach ($this->filter ["attributes"] as $key => $value)
			{
				if ($value ["amount"])
				{
					$filter ["attributes"] [$key] = $value ["amount"];
				}
			}
		}

		return $filter;
	}

	public function handleBookSeriesAttributes()
	{
		echo "handle";
		$freq = unserialize($_SESSION ["form_qsearchform"] ["frequence"]);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'frequence', $freq);
		switch ($freq)
		{
			case 'NONE':
				break;
			case 'DAILY':
				$this->handleBookSeriesRepeatType();
				break;
			case 'WEEKLY':
				$this->handleBookSeriesRepeatType();
				$weekdays = unserialize($_SESSION ["form_qsearchform"] ["weekdays"]);
				$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'weekdays', $weekdays);
				break;
			case 'MONTHLY':
				$this->handleBookSeriesRepeatType();
				$start_type = unserialize($_SESSION ["form_qsearchform"] ["start_type"]);
				$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'start_type', $start_type);
				if ($start_type == "weekday")
				{
					$w1 = unserialize($_SESSION ["form_qsearchform"] ["weekday_1"]);
					$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'weekday_1', $w1);
					$w2 = unserialize($_SESSION ["form_qsearchform"] ["weekday_2"]);
					$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'weekday_2', $w2);
				}
				elseif ($start_type == "monthday")
				{
					$md = unserialize($_SESSION ["form_qsearchform"] ["monthday"]);
					$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'monthday', $md);
				}
				break;
			default:
				break;
		}
	}

	private function handleBookSeriesRepeatType()
	{
		$repeat_amount = unserialize($_SESSION ["form_qsearchform"] ["repeat_amount"]);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'repeat_amount', $repeat_amount);
		$type = unserialize($_SESSION ["form_qsearchform"] ["repeat_type"]);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'repeat_type', $type);
		$repeat_until = unserialize($_SESSION ["form_qsearchform"] ["repeat_until"]);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'repeat_until', $repeat_until);
	}

	/**
	 * Initialize a search filter for ilRoomSharingRoomsTableGUI.
	 */
	public function initFilter()
	{
		$this->message = '';
		$this->messageNeeded = false;
		$this->messagePlural = false;
		// Room
		$this->createRoomFormItem();
		// Seats
		$this->createSeatsFormItem();
		// Room Attributes
		$this->createRoomAttributeFormItem();
		// generate info Message if needed
		$this->generateMessageIfNeeded();
	}

	/**
	 * Creates a combination input item which allows you to type in a room name.
	 */
	protected function createRoomFormItem()
	{
		// Room Name
		include_once ("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once ("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingTextInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
		$room_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_room"), "room");
		$room_name_input = new ilRoomSharingTextInputGUI("", "room_name");
		$room_name_input->setMaxLength(14);
		$room_name_input->setSize(14);
		$room_comb->addCombinationItem("room_name", $room_name_input,
			$this->lng->txt("rep_robj_xrs_room_name"));
		$this->addFilterItem($room_comb);
		$room_comb->readFromSession(); // get the value that was submitted
		$this->filter ["room"] = $room_comb->getValue();
	}

	/**
	 * Creates a combination input item consisting of a number input field for
	 * the desired seat amount.
	 */
	protected function createSeatsFormItem()
	{
		// Seats
		include_once ("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once ("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
		$seats_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_needed_seats"), "seats");
		$room_seats_input = new ilRoomSharingNumberInputGUI("", "room_seats");
		$room_seats_input->setMaxLength(8);
		$room_seats_input->setSize(8);
		$room_seats_input->setMinValue(0);
		$room_seats_input->setMaxValue($this->rooms->getMaxSeatCount());
		$seats_comb->addCombinationItem("room_seats", $room_seats_input,
			$this->lng->txt("rep_robj_xrs_amount"));
		$this->addFilterItem($seats_comb);
		$seats_comb->readFromSession(); // get the value that was submitted
		$this->filter ["seats"] = $seats_comb->getValue();

		$value = $_POST[$room_seats_input->getPostVar()];
		if ($value !== "" && $value > $room_seats_input->getMaxValue())
		{
			$this->message = $this->message . $this->lng->txt("rep_robj_xrs_needed_seats");

			if ($this->messagePlural == false && $this->messageNeeded == true)
			{
				$this->messagePlural = true;
			}
			$this->messageNeeded = true;
		}
	}

	/**
	 * If room attributes are present, display some input fields for the desired
	 * amount of those attributes.
	 */
	protected function createRoomAttributeFormItem()
	{
		include_once ("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/" .
			"RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
		$room_attributes = $this->rooms->getAllAttributes();
		foreach ($room_attributes as $room_attribute)
		{
			// setup an ilCombinationInputGUI for the room attributes
			$room_attribute_comb = new ilCombinationInputGUI($room_attribute, "attribute_" . $room_attribute);
			$room_attribute_input = new ilRoomSharingNumberInputGUI("",
				"attribute_" . $room_attribute . "_amount");
			$room_attribute_input->setMaxLength(8);
			$room_attribute_input->setSize(8);
			$room_attribute_input->setMinValue(0);
			$max_count = $this->rooms->getMaxCountForAttribute($room_attribute);
			$max_count_num = isset($max_count) ? $max_count : 0;
			$room_attribute_input->setMaxValue($max_count_num);
			$room_attribute_comb->addCombinationItem("amount", $room_attribute_input,
				$this->lng->txt("rep_robj_xrs_amount"));

			$this->addFilterItem($room_attribute_comb);
			$room_attribute_comb->readFromSession();

			$this->filter ["attributes"] [$room_attribute] = $room_attribute_comb->getValue();

			$value = $_POST[$room_attribute_input->getPostVar()];
			if ($value !== "" && $value > $room_attribute_input->getMaxValue())
			{
				if ($this->message != '')
				{
					$this->message = $this->message . ', ' . $room_attribute;
				}
				else
				{
					$this->message = $this->message . $room_attribute;
				}

				if ($this->messagePlural == false && $this->messageNeeded == true)
				{
					$this->messagePlural = true;
				}
				$this->messageNeeded = true;
			}
		}
	}

	/**
	 * Generate and show a infomessage if the private variables $message and $messageNeeded are set.
	 * They are set if one input value is bigger then the maxvalue.
	 */
	private function generateMessageIfNeeded()
	{
		if ($this->messageNeeded)
		{
			if (!$this->messagePlural)
			{
				$msg = $this->lng->txt('rep_robj_xrs_singular_field_input_value_too_high_begin');
				$msg = $msg . ' "' . $this->message;
				$msg = $msg . '" ' . $this->lng->txt('rep_robj_xrs_singular_field_input_value_too_high_end');
			}
			else
			{
				$msg = $this->lng->txt('rep_robj_xrs_plural_field_input_value_too_high_begin');
				$msg = $msg . ' "' . $this->message;
				$msg = $msg . '" ' . $this->lng->txt('rep_robj_xrs_plural_field_input_value_too_high_end');
			}
			ilUtil::sendInfo($msg);
		}
	}

}
