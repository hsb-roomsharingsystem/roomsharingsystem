<?php
include_once ('./Services/Table/classes/class.ilTable2GUI.php');
include_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRooms.php');

/**
 * Class ilRoomSharingRoomsTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *         
 */
class ilRoomSharingRoomsTableGUI extends ilTable2GUI
{
	protected $rooms;
	
	/**
	 * Constructor for the class ilRoomSharingRoomsTableGUI
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
		
		$this->rooms = new ilRoomSharingRooms($a_parent_obj->getPoolID());
		$this->lng->loadLanguageModule("form");
		
		$this->setTitle($this->lng->txt("rep_robj_xrs_rooms"));
		$this->setLimit(10); // datasets that are displayed per page
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setEnableHeader(true);
		$this->addColumns(); // add columns and column headings
		$this->setEnableHeader(true);
		$this->setRowTemplate("tpl.room_rooms_row.html", 
				"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");
	}
	
	/**
	 * Gets all the items that need to populated into the table.
	 */
	public function getItems(array $filter)
	{
		$data = $this->rooms->getList($filter);
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}
	
	/**
	 * Adds columns and column headings to the table.
	 */
	private function addColumns()
	{
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_seats"));
		$this->addColumn($this->lng->txt("rep_robj_xrs_room_attributes")); // not sortable
		$this->addColumn("", "action");
	}
	
	/**
	 * Fills an entire table row with the given set.
	 * The corresponding array
	 * has the following shape:
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
		for($i = 0; $i < $attribute_count; ++ $i)
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
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'last_cmd', $this->parent_cmd);
		
		// only display a booking form if a search was initialized beforehand
		if ($this->parent_cmd == "showSearchResults")
		{
			// if this class is used to display search results, the input made
			// must be transported to the book form
			$date = unserialize($_SESSION ["form_qsearchform"] ["date"]);
			$time_from = unserialize($_SESSION ["form_qsearchform"] ["time_from"]);
			$time_to = unserialize($_SESSION ["form_qsearchform"] ["time_to"]);
			
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'date', $date ['date']);
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_from', $time_from ['time']);
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_to', $time_to ['time']);
			$this->tpl->setVariable('LINK_ACTION', 
					$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'book'));
			// free those parameters, since we don't need them anymore
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'date', "");
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_from', "");
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'time_to', "");
		} else
		{
			// the user is linked to the quick search form if he is trying to book
			// a room when the normal room list is displayed
			$this->tpl->setVariable('LINK_ACTION', 
					$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showSearchQuick'));
		}
		
		// unset the parameters; just in case
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', "");
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'last_cmd', "");
		
		// allow administrators to edit and delete rooms, but only if the room list and not the search results are displayed
		if ($ilAccess->checkAccess('write', '', $this->ref_id) && $this->parent_cmd == "showRooms")
		{
			$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable('LINK_ACTION', 
					$this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
			$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('edit'));
			$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable('LINK_ACTION', 
					$this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
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
			foreach ( $this->filter ["attributes"] as $key => $value )
			{
				if ($value ["amount"])
				{
					$filter ["attributes"] [$key] = $value ["amount"];
				}
			}
		}
		
		return $filter;
	}
	
	/**
	 * Initialize a search filter for ilRoomSharingRoomsTableGUI.
	 */
	public function initFilter()
	{
		// Room
		$this->createRoomFormItem();
		// Seats
		$this->createSeatsFormItem();
		// Room Attributes
		$this->createRoomAttributeFormItem();
	}
	
	/**
	 * Creates a combination input item which allows you to type in a room name.
	 */
	protected function createRoomFormItem()
	{
		// Room Name
		include_once ("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once ("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingTextInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
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
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
		$seats_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_seats"), "seats");
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
	}
	
	/**
	 * If room attributes are present, display some input fields for the desired
	 * amount of those attributes.
	 */
	protected function createRoomAttributeFormItem()
	{
		include_once ("./Services/Form/classes/class.ilCombinationInputGUI.php");
		include_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
		$room_attributes = $this->rooms->getAllAttributes();
		foreach ( $room_attributes as $room_attribute )
		{
			// setup an ilCombinationInputGUI for the room attributes
			$room_attribute_comb = new ilCombinationInputGUI($room_attribute, 
					"attribute_" . $room_attribute);
			$room_attribute_input = new ilRoomSharingNumberInputGUI("", 
					"attribute_" . $room_attribute . "_amount");
			$room_attribute_input->setMaxLength(8);
			$room_attribute_input->setSize(8);
			$room_attribute_input->setMinValue(0);
			$room_attribute_input->setMaxValue(
					$this->rooms->getMaxCountForAttribute($room_attribute));
			$room_attribute_comb->addCombinationItem("amount", $room_attribute_input, 
					$this->lng->txt("rep_robj_xrs_amount"));
			
			$this->addFilterItem($room_attribute_comb);
			$room_attribute_comb->readFromSession();
			
			$this->filter ["attributes"] [$room_attribute] = $room_attribute_comb->getValue();
		}
	}
}

?>
